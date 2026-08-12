<?php

namespace App\Services;

use App\Core\App;
use App\Models\PartnerLeadRepository;

/**
 * Official Sportify partner invites via project MAIL_FROM / SMTP.
 * If mail is not configured, invites are queued (logged) and not blasted.
 */
class PartnerInviteService
{
    public const CAMPAIGN = 'official_sportify_partner';

    public static function fromAddress(): string
    {
        $mail = App::config('mail', []);
        return (string) ($mail['from']['address']
            ?? $mail['from_address']
            ?? getenv('MAIL_FROM_ADDRESS')
            ?: 'partners@sportifyplus.de');
    }

    public static function fromName(): string
    {
        $mail = App::config('mail', []);
        return (string) ($mail['from']['name']
            ?? $mail['from_name']
            ?? getenv('MAIL_FROM_NAME')
            ?: 'Sportify Plus');
    }

    public static function mailConfigured(): bool
    {
        $mail = App::config('mail', []);
        $host = (string) ($mail['host'] ?? getenv('MAIL_HOST') ?: '');
        $user = (string) ($mail['username'] ?? getenv('MAIL_USERNAME') ?: '');
        $driver = (string) ($mail['mailer'] ?? getenv('MAIL_MAILER') ?: 'smtp');
        if ($driver === 'log' || $driver === 'array') {
            return false;
        }
        return $host !== '' && $user !== '';
    }

    public static function subject(): string
    {
        return 'Official invitation: become a Sportify Plus partner';
    }

    public static function bodyHtml(array $partner): string
    {
        $name = htmlspecialchars((string) ($partner['name'] ?? 'Partner'), ENT_QUOTES, 'UTF-8');
        $type = htmlspecialchars((string) ($partner['type'] ?? 'partner'), ENT_QUOTES, 'UTF-8');
        $home = htmlspecialchars(rtrim((string) App::config('app.url', 'https://sportifyplus.de'), '/'), ENT_QUOTES, 'UTF-8');
        $contact = htmlspecialchars(self::fromAddress(), ENT_QUOTES, 'UTF-8');

        return <<<HTML
<div style="font-family:Georgia,'Times New Roman',serif;max-width:640px;margin:0 auto;color:#1f2937;line-height:1.65;">
  <div style="background:linear-gradient(135deg,#0b3d2e 0%,#14532d 100%);padding:28px 24px;text-align:center;">
    <p style="margin:0;color:#86efac;font-size:11px;letter-spacing:0.08em;text-transform:uppercase;font-family:sans-serif;">Sportify Plus · Official Partners</p>
    <h1 style="margin:10px 0 0;color:#ffffff;font-size:22px;font-weight:600;">Invite {$name} to become an official Sportify partner</h1>
  </div>
  <div style="padding:28px 24px;background:#ffffff;">
    <p>We are formally inviting <strong>{$name}</strong> ({$type}) to become an <strong>official partner</strong> of Sportify Plus (sportifyplus.de) — the football talent, scouting, and community platform.</p>
    <p style="background:#f0fdf4;border-left:3px solid #166534;padding:12px 14px;">As an official Sportify partner you collaborate on coverage, talent discovery, events, data, or brand activations with clear recognition on Sportify Plus.</p>
    <p><strong>Next step:</strong> reply to this email to accept, or write to <a href="mailto:{$contact}">{$contact}</a>.</p>
    <p style="margin:22px 0;">
      <a href="{$home}" style="display:inline-block;background:#14532d;color:#fff;padding:12px 18px;text-decoration:none;border-radius:4px;font-family:sans-serif;font-size:13px;">Visit Sportify Plus</a>
      <a href="mailto:{$contact}?subject=Accept%20Sportify%20partner%20invite%20-%20{$name}" style="display:inline-block;background:#0b3d2e;color:#fff;padding:12px 18px;text-decoration:none;border-radius:4px;font-family:sans-serif;font-size:13px;margin-left:8px;">Accept partnership</a>
    </p>
    <p style="margin-bottom:0;">Warm regards,<br>Sportify Plus Partnerships<br><a href="{$home}">sportifyplus.de</a></p>
  </div>
  <div style="padding:16px 24px;background:#f1f5f9;font-size:12px;color:#64748b;">
    Sent to a publicly listed press/partnership address for {$name}. Reply STOP or email {$contact} to opt out.
  </div>
</div>
HTML;
    }

    /**
     * @return array{sent:int,queued:int,skipped:int,failed:int,from:string,mail_configured:bool,dry_run:bool}
     */
    public static function inviteAll(array $options = []): array
    {
        $dryRun = !empty($options['dry_run']);
        $verifiedOnly = array_key_exists('verified_only', $options) ? (bool) $options['verified_only'] : true;
        $type = $options['type'] ?? null;
        $limit = max(1, (int) ($options['limit'] ?? 50));
        $forceSend = !empty($options['force_send']);

        $from = self::fromAddress();
        $configured = self::mailConfigured();
        $candidates = PartnerLeadRepository::inviteCandidates($verifiedOnly, $type);
        $candidates = array_slice($candidates, 0, $limit);

        $result = [
            'sent' => 0,
            'queued' => 0,
            'skipped' => 0,
            'failed' => 0,
            'from' => $from,
            'mail_configured' => $configured,
            'dry_run' => $dryRun,
            'candidates' => count($candidates),
        ];

        $subject = self::subject();

        foreach ($candidates as $partner) {
            $to = (string) ($partner['email'] ?? '');
            if ($to === '' || !filter_var($to, FILTER_VALIDATE_EMAIL)) {
                $result['skipped']++;
                continue;
            }
            if (in_array($partner['invite_status'], ['sent', 'accepted'], true)) {
                $result['skipped']++;
                continue;
            }

            if ($dryRun) {
                $result['queued']++;
                continue;
            }

            $shouldSend = $configured && ($forceSend || ($options['send'] ?? false));
            if (!$shouldSend) {
                PartnerLeadRepository::setInviteStatus((int) $partner['id'], 'queued');
                PartnerLeadRepository::logInvite((int) $partner['id'], $from, $to, $subject, 'queued', 'Mail not configured or send not confirmed — queued for later.', [
                    'mail_configured' => $configured,
                ]);
                $result['queued']++;
                continue;
            }

            $ok = self::sendMail($from, self::fromName(), $to, $subject, self::bodyHtml($partner));
            if ($ok) {
                PartnerLeadRepository::setInviteStatus((int) $partner['id'], 'sent');
                PartnerLeadRepository::logInvite((int) $partner['id'], $from, $to, $subject, 'sent');
                $result['sent']++;
            } else {
                PartnerLeadRepository::setInviteStatus((int) $partner['id'], 'failed');
                PartnerLeadRepository::logInvite((int) $partner['id'], $from, $to, $subject, 'failed', 'mail() / SMTP send failed');
                $result['failed']++;
            }
        }

        return $result;
    }

    private static function sendMail(string $from, string $fromName, string $to, string $subject, string $html): bool
    {
        $mail = App::config('mail', []);
        $host = (string) ($mail['host'] ?? getenv('MAIL_HOST') ?: '');
        $port = (int) ($mail['port'] ?? getenv('MAIL_PORT') ?: 587);
        $user = (string) ($mail['username'] ?? getenv('MAIL_USERNAME') ?: '');
        $pass = (string) ($mail['password'] ?? getenv('MAIL_PASSWORD') ?: '');
        $encryption = strtolower((string) ($mail['encryption'] ?? getenv('MAIL_ENCRYPTION') ?: 'tls'));

        // Prefer SMTP when configured; fall back to PHP mail().
        if ($host !== '' && $user !== '' && function_exists('fsockopen')) {
            $sent = self::smtpSend($host, $port, $user, $pass, $encryption, $from, $fromName, $to, $subject, $html);
            if ($sent) {
                return true;
            }
        }

        $headers = [
            'MIME-Version: 1.0',
            'Content-type: text/html; charset=UTF-8',
            'From: ' . sprintf('"%s" <%s>', addslashes($fromName), $from),
            'Reply-To: ' . $from,
            'X-Mailer: Sportify-PartnerInvite',
        ];
        return @mail($to, '=?UTF-8?B?' . base64_encode($subject) . '?=', $html, implode("\r\n", $headers));
    }

    private static function smtpSend(
        string $host,
        int $port,
        string $user,
        string $pass,
        string $encryption,
        string $from,
        string $fromName,
        string $to,
        string $subject,
        string $html
    ): bool {
        $remote = ($encryption === 'ssl' ? 'ssl://' : '') . $host;
        $fp = @fsockopen($remote, $port, $errno, $errstr, 20);
        if (!$fp) {
            return false;
        }
        stream_set_timeout($fp, 20);
        $read = static function () use ($fp): string {
            $data = '';
            while ($str = fgets($fp, 512)) {
                $data .= $str;
                if (isset($str[3]) && $str[3] === ' ') {
                    break;
                }
            }
            return $data;
        };
        $write = static function (string $cmd) use ($fp): void {
            fwrite($fp, $cmd . "\r\n");
        };

        $read();
        $write('EHLO sportifyplus.de');
        $read();
        if ($encryption === 'tls') {
            $write('STARTTLS');
            $read();
            if (!stream_socket_enable_crypto($fp, true, STREAM_CRYPTO_METHOD_TLS_CLIENT)) {
                fclose($fp);
                return false;
            }
            $write('EHLO sportifyplus.de');
            $read();
        }
        $write('AUTH LOGIN');
        $read();
        $write(base64_encode($user));
        $read();
        $write(base64_encode($pass));
        $auth = $read();
        if (!str_starts_with($auth, '235')) {
            fclose($fp);
            return false;
        }
        $write('MAIL FROM:<' . $from . '>');
        $read();
        $write('RCPT TO:<' . $to . '>');
        $read();
        $write('DATA');
        $read();
        $headers = 'From: "' . $fromName . '" <' . $from . ">\r\n"
            . 'To: <' . $to . ">\r\n"
            . 'Subject: =?UTF-8?B?' . base64_encode($subject) . "?=\r\n"
            . "MIME-Version: 1.0\r\n"
            . "Content-Type: text/html; charset=UTF-8\r\n\r\n";
        $write($headers . $html . "\r\n.");
        $dataResp = $read();
        $write('QUIT');
        fclose($fp);
        return str_starts_with($dataResp, '250');
    }
}
