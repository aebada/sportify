<?php

namespace App\Controllers;

use App\Core\App;
use App\Core\Controller;
use App\Core\Csrf;
use App\Models\PartnerLeadRepository;
use App\Services\PartnerInviteService;
use App\Services\PartnerSeedImporter;

class AdminPartnersController extends Controller
{
    protected function guard(): ?string
    {
        if ($r = $this->requirePermission('crm.access')) {
            return $r;
        }
        return $this->requireDb();
    }

    public function index(): string
    {
        if ($r = $this->guard()) {
            return $r;
        }
        PartnerLeadRepository::ensureSchema();
        $req = App::$request;
        $filters = [
            'q' => trim((string) $req->input('q', '')),
            'type' => (string) $req->input('type', ''),
            'invite_status' => (string) $req->input('invite_status', ''),
            'email_confidence' => (string) $req->input('email_confidence', ''),
            'country' => (string) $req->input('country', ''),
        ];
        if ($req->input('has_email') === '1') {
            $filters['has_email'] = 1;
        } elseif ($req->input('has_email') === '0') {
            $filters['has_email'] = 0;
        }

        return $this->view('admin.partners.index', [
            'title' => 'Potential Partners',
            'partners' => PartnerLeadRepository::all($filters),
            'filters' => $filters,
            'stats' => PartnerLeadRepository::stats(),
            'mail_from' => PartnerInviteService::fromAddress(),
            'mail_configured' => PartnerInviteService::mailConfigured(),
            'resource' => 'partners',
        ], 'admin');
    }

    public function show(string $id): string
    {
        if ($r = $this->guard()) {
            return $r;
        }
        $partner = PartnerLeadRepository::find((int) $id);
        if (!$partner) {
            return $this->abort(404, 'Partner not found');
        }
        return $this->view('admin.partners.show', [
            'title' => $partner['name'],
            'partner' => $partner,
            'resource' => 'partners',
        ], 'admin');
    }

    public function editForm(string $id): string
    {
        if ($r = $this->guard()) {
            return $r;
        }
        $partner = PartnerLeadRepository::find((int) $id);
        if (!$partner) {
            return $this->abort(404, 'Partner not found');
        }
        return $this->view('admin.partners.form', [
            'title' => 'Edit partner',
            'partner' => $partner,
            'resource' => 'partners',
        ], 'admin');
    }

    public function update(string $id): string
    {
        if ($r = $this->guard()) {
            return $r;
        }
        if (!Csrf::check()) {
            flash_once('error', 'Invalid CSRF token.');
            return $this->back();
        }
        $req = App::$request;
        $tags = array_filter(array_map('trim', explode(',', (string) $req->input('tags', ''))));
        PartnerLeadRepository::update((int) $id, [
            'name' => trim((string) $req->input('name', '')),
            'type' => (string) $req->input('type', 'media'),
            'subtype' => trim((string) $req->input('subtype', '')),
            'country' => trim((string) $req->input('country', '')),
            'city' => trim((string) $req->input('city', '')),
            'league' => trim((string) $req->input('league', '')),
            'website' => trim((string) $req->input('website', '')),
            'email' => trim((string) $req->input('email', '')),
            'email_confidence' => (string) $req->input('email_confidence', 'needs_research'),
            'source_url' => trim((string) $req->input('source_url', '')),
            'invite_status' => (string) $req->input('invite_status', 'pending'),
            'notes' => trim((string) $req->input('notes', '')),
            'tags' => $tags,
        ]);
        flash_once('success', 'Partner updated.');
        return $this->redirect(route('admin.partners.show', ['id' => $id]));
    }

    public function inviteAll(): string
    {
        if ($r = $this->guard()) {
            return $r;
        }
        if (!Csrf::check()) {
            flash_once('error', 'Invalid CSRF token.');
            return $this->redirect(route('admin.partners'));
        }
        $req = App::$request;
        $dryRun = (string) $req->input('dry_run', '0') === '1';
        $send = (string) $req->input('send', '0') === '1';
        $verifiedOnly = (string) $req->input('verified_only', '1') !== '0';
        $type = trim((string) $req->input('type', '')) ?: null;
        $limit = max(1, min(200, (int) $req->input('limit', 50)));

        $result = PartnerInviteService::inviteAll([
            'dry_run' => $dryRun,
            'send' => $send,
            'verified_only' => $verifiedOnly,
            'type' => $type,
            'limit' => $limit,
        ]);

        if ($dryRun) {
            flash_once('success', sprintf(
                'Dry run: %d candidates would be processed (from %s). Mail configured: %s',
                $result['candidates'],
                $result['from'],
                $result['mail_configured'] ? 'yes' : 'no — would queue'
            ));
        } elseif (!$result['mail_configured'] || !$send) {
            flash_once('success', sprintf(
                'Queued %d official partner invites from %s (mail not sending until MAIL_* configured and Send confirmed). Skipped %d.',
                $result['queued'],
                $result['from'],
                $result['skipped']
            ));
        } else {
            flash_once('success', sprintf(
                'Invite-all complete: sent %d, queued %d, skipped %d, failed %d (from %s).',
                $result['sent'],
                $result['queued'],
                $result['skipped'],
                $result['failed'],
                $result['from']
            ));
        }

        return $this->redirect(route('admin.partners'));
    }

    public function importSeeds(): string
    {
        if ($r = $this->guard()) {
            return $r;
        }
        if (!Csrf::check()) {
            flash_once('error', 'Invalid CSRF token.');
            return $this->redirect(route('admin.partners'));
        }
        $result = PartnerSeedImporter::import();
        flash_once('success', sprintf(
            'Imported partners: %d upserted, %d files (%s).',
            $result['upserted'],
            $result['files'],
            implode(', ', $result['file_names'] ?? [])
        ));
        return $this->redirect(route('admin.partners'));
    }
}
