<?php
/**
 * RefereeX AI routes — supplemental load from production front controller.
 * deploy-marker: refereex-ai-v1
 */

use App\Controllers\RefereeXController;

/** @var \App\Core\Router $router */
$router->get('/refereex-ai', [RefereeXController::class, 'index'], 'refereex_ai');
