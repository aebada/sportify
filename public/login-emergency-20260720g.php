<?php
declare(strict_types=1);
/**
 * Minimal login page so Google OAuth can be retried while main FC is broken.
 * deploy-marker: login-emergency-20260720g
 */
header('Content-Type: text/html; charset=UTF-8');
header('Cache-Control: no-store');
$error = (string) ($_GET['error'] ?? '');
$msg = $error === 'google'
    ? 'Google sign-in failed or was cancelled. Please try again.'
    : '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Log in · Sportify</title>
  <style>
    body{margin:0;min-height:100vh;display:grid;place-items:center;background:#07140f;color:#e8f5ef;font-family:system-ui,sans-serif}
    .box{width:min(420px,92vw);padding:28px;border:1px solid rgba(255,255,255,.12);border-radius:16px;background:rgba(8,28,20,.9)}
    h1{margin:0 0 8px;font-size:1.5rem}
    p{margin:0 0 18px;opacity:.85;line-height:1.45}
    .err{color:#ffb4b4;margin-bottom:14px}
    a.btn{display:inline-block;padding:12px 16px;border-radius:10px;background:#1f8f5f;color:#fff;text-decoration:none;font-weight:600}
    a.btn:hover{filter:brightness(1.08)}
    .muted{margin-top:16px;font-size:.9rem;opacity:.7}
  </style>
</head>
<body>
  <div class="box">
    <h1>Sportify</h1>
    <p>Sign in to continue.</p>
    <?php if ($msg !== ''): ?><p class="err"><?= htmlspecialchars($msg, ENT_QUOTES, 'UTF-8') ?></p><?php endif; ?>
    <a class="btn" href="/auth/google">Continue with Google</a>
    <p class="muted"><a href="/" style="color:#9fd9bc">Back to home</a></p>
  </div>
</body>
</html>
