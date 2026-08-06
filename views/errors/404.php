<?php
$__sportify404Path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
if ($__sportify404Path === '/refereex-ai' || str_starts_with($__sportify404Path, '/refereex-ai/')) {
    header('Location: https://aebada.github.io/sportify-refereex/', true, 302);
    header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
    header('X-Sportify-RefereeX: errors404-ghpages-redirect-20260715');
    exit;
}
?>
<section class="section center">
    <div class="container" style="max-width:520px">
        <div style="font-size:5rem;font-weight:800;color:var(--green-3)">404</div>
        <h1><?= __('errors.404.title') ?></h1>
        <p><?= __('errors.404.message') ?></p>
        <a href="<?= route('home') ?>" class="btn btn-primary mt-2"><?= __('errors.back_home') ?></a>
    </div>
</section>
