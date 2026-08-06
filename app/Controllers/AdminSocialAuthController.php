<?php

namespace App\Controllers;

use App\Core\App;
use App\Core\Controller;
use App\Core\Csrf;
use App\Services\SocialAuthService;
use App\Services\SocialAuthSettings;

class AdminSocialAuthController extends Controller
{
    public function index(): string
    {
        if ($r = $this->requirePermission('admin.access')) {
            return $r;
        }

        $visibility = SocialAuthSettings::all();

        return $this->view('admin.social-auth.index', [
            'title'      => __('admin.social_auth.title'),
            'resource'   => 'social_auth',
            'visibility' => $visibility,
            'configured' => [
                'google'   => SocialAuthService::isConfigured('google'),
                'facebook' => SocialAuthService::isConfigured('facebook'),
                'linkedin' => SocialAuthService::isConfigured('linkedin'),
            ],
        ], 'admin');
    }

    public function save(): string
    {
        if ($r = $this->requirePermission('admin.access')) {
            return $r;
        }

        if (!Csrf::verify(App::$request->input('_csrf'))) {
            flash('error', __('errors.csrf'));
            return $this->redirect(route('admin.social_auth'));
        }

        SocialAuthSettings::save([
            'facebook' => App::$request->input('show_facebook'),
            'linkedin' => App::$request->input('show_linkedin'),
        ]);

        flash('success', __('admin.social_auth.saved'));
        return $this->redirect(route('admin.social_auth'));
    }
}
