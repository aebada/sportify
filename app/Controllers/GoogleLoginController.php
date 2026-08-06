<?php

namespace App\Controllers;

use App\Core\App;
use App\Core\Auth;
use App\Core\Controller;
use App\Services\GoogleOAuthStart;
use App\Services\SocialAuthSettings;

/**
 * Dedicated Google login entry (new class file to bypass stale OPcache).
 * deploy-marker: google-login-controller-20260720
 */
class GoogleLoginController extends Controller
{
    public function start(): string
    {
        if (Auth::check()) {
            return $this->redirect('/');
        }

        $req = App::$request;
        $from = (string) $req->input('from', 'login');
        $role = (string) $req->input('role', 'player');

        if ($from === 'register') {
            $_SESSION['oauth_registering'] = true;
            $_SESSION['oauth_pending_role'] = $role;
        } else {
            unset($_SESSION['oauth_registering'], $_SESSION['oauth_pending_role']);
        }

        if (!SocialAuthSettings::isVisible('google')) {
            flash('error', 'Google login is currently disabled.');
            return $this->redirect(route('login'));
        }

        try {
            return $this->redirect(GoogleOAuthStart::authUrl($from, $role));
        } catch (\Throwable $e) {
            flash('error', $e->getMessage());
            return $this->redirect(route('login'));
        }
    }

    public function returned(): string
    {
        $oauth = new OAuthController();
        return $oauth->googleCallback();
    }
}
