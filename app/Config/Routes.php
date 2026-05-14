<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */

/**Open */
$routes->get('/', 'AbuseController::home');

$routes->group('api', ['namespace' => 'App\Controllers\Api'], static function (RouteCollection $routes): void {
 
    $routes->post('abuse-reports/check', 'AbuseReportController::checkDomain');
    
    $routes->post('abuse-reports', 'AbuseReportController::store');
 
     $routes->get('abuse-reports', 'AbuseReportController::index');
 
    $routes->get('abuse-reports/(:segment)', 'AbuseReportController::show/$1');
});

/**Auth */
$routes->get('admin/login',   'AuthController::login');
$routes->post('admin/login',  'AuthController::attempt');
$routes->get('admin/logout',  'AuthController::logout');

$routes->group('', ['namespace' => 'App\Controllers','filter' => 'auth'], function($routes) {
    /**Admin */
    $routes->get('/dashboard', 'AbuseController::dashboard');
    $routes->get('/reports', 'AbuseController::all_reports');


    $routes->get('admin/users',          'UserController::users');
    $routes->post('admin/users/save',    'UserController::save');
    $routes->get('admin/users/edit/(:num)', 'UserController::edit/$1');
    $routes->post('admin/users/delete/(:num)', 'UserController::delete/$1');

});


