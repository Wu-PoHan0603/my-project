<?php

use CodeIgniter\Router\RouteCollection;

/** @var RouteCollection $routes */
$routes->get('/', 'Home::index');
$routes->get('clients', 'ClientController::index');
$routes->get('clients/create', 'ClientController::create');