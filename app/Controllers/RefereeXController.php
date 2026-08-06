<?php

namespace App\Controllers;

use App\Core\App;
use App\Core\Controller;

class RefereeXController extends Controller
{
    public function index(): string
    {
        $baseUrl = rtrim((string) App::config('app.url', 'https://sportifyplus.de'), '/');
        $pageUrl = $baseUrl . '/refereex-ai';
        $title = __('refereex.title') ?: 'RefereeX AI — Intelligent Officiating Platform';
        $metaDesc = __('refereex.meta') ?: 'RefereeX AI delivers real-time officiating intelligence, multi-camera fusion, and digital twin technology for modern football.';

        return $this->view('pages.refereex-ai.index', [
            'title'        => $title,
            'metaDesc'     => $metaDesc,
            'metaKeywords' => 'RefereeX AI, VAR, officiating intelligence, digital twin, computer vision, sports integrity, football AI, Sportify',
            'canonicalUrl' => $pageUrl,
            'ogTitle'      => 'RefereeX AI · Sportify',
            'ogDescription'=> $metaDesc,
            'ogImage'      => $baseUrl . '/assets/img/og/refereex-ai.png',
            'ogUrl'        => $pageUrl,
            'extraStyles'  => [asset('css/refereex-ai.css')],
            'extraScripts' => [asset('js/refereex-ai.js')],
            'jsonLd'       => [
                '@context'    => 'https://schema.org',
                '@type'       => 'SoftwareApplication',
                'name'        => 'RefereeX AI',
                'applicationCategory' => 'SportsApplication',
                'operatingSystem' => 'Web',
                'description' => $metaDesc,
                'url'         => $pageUrl,
                'provider'    => [
                    '@type' => 'Organization',
                    'name'  => 'Sportify',
                    'url'   => $baseUrl,
                ],
                'offers' => [
                    '@type' => 'Offer',
                    'category' => 'Enterprise',
                ],
                'featureList' => [
                    'Multi-camera fusion',
                    'Digital twin match state',
                    'Explainable AI decisions',
                    'Real-time VAR support',
                ],
            ],
        ]);
    }
}
