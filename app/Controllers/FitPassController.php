<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\FitPassRepository;

class FitPassController extends Controller
{
    public function index(): string
    {
        if (($_GET['view'] ?? '') === 'refereex') {
            header('X-Sportify-RefereeX: fitpass-bridge-20260707');
            return (new RefereeXController())->index();
        }

        return $this->view('pages.fitpass', [
            'title'      => 'FIT-Pass',
            'services'   => FitPassRepository::services(),
            'plans'      => FitPassRepository::plans(),
            'comparison' => FitPassRepository::comparison(),
            'membershipHub' => route('membership.index'),
        ]);
    }
}
