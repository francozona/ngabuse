<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->get('/', 'Abuse::home');

$routes->get('/dashboard', 'Abuse::dashboard');
