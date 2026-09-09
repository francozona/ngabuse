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

    /** This route will be called using the header NIRA-CRON-NETBEACON => 'Netbeacon_cron' from cron job at intervals 
     * to populate the database with new reports from netbeacon.
     * */
    $routes->get('check-netbeacon',  'NetbeaconController::get_incident_reports');

});

/**Auth */
$routes->get('admin/login',   'AuthController::login');
$routes->post('admin/login',  'AuthController::attempt');
$routes->get('admin/logout',  'AuthController::logout');

$routes->get('/domain-abuse/(:segment)/(:segment)', 'AbuseController::share_report/$1/$2');
$routes->get('/whois/(:segment)',  'Api\AbuseReportController::get_whois_abuse_email/$1');


$routes->post('upload-image', 'AbuseController::uploadImage');
$routes->post('registrar/login', 'AuthController::authRegistrar');
$routes->post('/registrar/reports/(:num)/respond', 'AbuseController::add_registrar_response/$1');

$routes->group('', ['namespace' => 'App\Controllers','filter' => 'auth'], function($routes) {
    /**Admin */
    $routes->get('/dashboard', 'AbuseController::dashboard');
    $routes->get('/reports', 'AbuseController::all_reports');
    $routes->get('/report/(:segment)/(:segment)', 'AbuseController::view_report/$1/$2');
    $routes->post('/admin/reports/(:num)/respond', 'AbuseController::add_response/$1');
    $routes->post('/admin/upload/response-image', 'AbuseController::upload_response_image');
    $routes->post('/admin/reports/(:num)/status', 'AbuseController::status/$1');
    $routes->get('/admin/refresh-abuse-registrar/(:num)', 'AbuseController::fetch_registrar_details/$1');
    $routes->get('admin/users',          'UserController::users');
    $routes->post('admin/users/save',    'UserController::save');
    $routes->get('admin/users/edit/(:num)', 'UserController::edit/$1');
    //$routes->post('admin/users/delete/(:num)', 'UserController::delete/$1');

});


