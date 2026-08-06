<?php

namespace App\Core;

abstract class Controller
{
    /** Redirect to login if guest; returns redirect response or null when authenticated. */
    protected function requireAuth(): ?string
    {
        if (!Auth::check()) {
            flash_once('error', 'Please log in to continue.');
            return $this->redirect($this->loginUrlWithReturn());
        }
        return null;
    }

    /** @param string|array<int,string> $roles */
    protected function requireRole(string|array $roles): ?string
    {
        if ($r = $this->requireAuth()) {
            return $r;
        }
        if (!Auth::hasAnyRole($roles)) {
            return $this->abort(403, __('errors.403.message'));
        }
        return null;
    }

    protected function requirePermission(string $permission): ?string
    {
        return $this->authorize($permission);
    }

    protected function authorize(string $permission): ?string
    {
        if ($r = $this->requireAuth()) {
            return $r;
        }
        if (!Auth::can($permission)) {
            return $this->abort(403, __('errors.403.message'));
        }
        return null;
    }

    /** Abort unless DB is available (social features need persistence). */
    protected function requireDb(): ?string
    {
        if (!Database::available()) {
            return $this->abort(503, 'Member features require a database. Run: php sportify migrate');
        }
        return null;
    }

    protected function view(string $view, array $data = [], ?string $layout = 'app'): string
    {
        return View::render($view, $data, $layout);
    }

    protected function redirect(string $url, int $status = 0): string
    {
        $this->preventRedirectCaching();
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_write_close();
        }
        if ($status === 0) {
            $status = (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') ? 303 : 302;
        }
        header('Location: ' . $url, true, $status);
        $safe = htmlspecialchars($url, ENT_QUOTES, 'UTF-8');

        // Non-empty fallback avoids blank screens when Location is dropped by a proxy/CDN.
        return '<!DOCTYPE html><html lang="en"><head>'
            . '<meta charset="UTF-8">'
            . '<meta http-equiv="refresh" content="0;url=' . $safe . '">'
            . '<title>Redirecting…</title></head><body>'
            . '<p><a href="' . $safe . '">Continue</a></p></body></html>';
    }

    protected function preventRedirectCaching(): void
    {
        if (headers_sent()) {
            return;
        }
        header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
        header('Pragma: no-cache');
    }

    protected function loginUrlWithReturn(?string $returnPath = null): string
    {
        return sportify_login_redirect_url($returnPath);
    }

    protected function back(): string
    {
        $ref = $_SERVER['HTTP_REFERER'] ?? Router::base() . '/';
        return $this->redirect($ref);
    }

    protected function abort(int $code, string $message = ''): string
    {
        http_response_code($code);
        $view = $code === 403 ? 'pages.errors.403' : 'errors.' . $code;
        $layout = \App\Support\OnlyTalentsContext::isActive() ? 'onlytalents' : 'app';
        return View::render($view, ['title' => $message ?: 'Error'], $layout);
    }
}
