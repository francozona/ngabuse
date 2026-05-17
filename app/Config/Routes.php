<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */

/**Open */
$routes->get('/', 'AbuseController::home');
$routes->get('/report', 'AbuseController::report');

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
    $routes->get('/report/(:segment)/(:segment)', 'AbuseController::view_report/$1/$2');
    $routes->post('/admin/reports/(:num)/respond', 'AbuseController::add_response/$1');
    $routes->post('/admin/upload/response-image', 'AbuseController::upload_response_image');
    $routes->post('/admin/reports/(:num)/status', 'AbuseController::status/$1');
    $routes->get('admin/users',          'UserController::users');
    $routes->post('admin/users/save',    'UserController::save');
    $routes->get('admin/users/edit/(:num)', 'UserController::edit/$1');
    $routes->post('admin/users/delete/(:num)', 'UserController::delete/$1');

});


