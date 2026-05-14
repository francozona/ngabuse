<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->get('/', 'Abuse::home');

$routes->get('/dashboard', 'Abuse::dashboard');
$routes->get('/reports', 'Abuse::all_reports');

$routes->group('api', ['namespace' => 'App\Controllers\Api'], static function (RouteCollection $routes): void {
 
    $routes->post('abuse-reports/check', 'AbuseReportController::checkDomain');
    
    $routes->post('abuse-reports', 'AbuseReportController::store');
 
     $routes->get('abuse-reports', 'AbuseReportController::index');
 
    $routes->get('abuse-reports/(:segment)', 'AbuseReportController::show/$1');
});

