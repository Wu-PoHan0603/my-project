<?php

use CodeIgniter\Router\RouteCollection;

/** @var RouteCollection $routes */

//get
$routes->get('/', 'Home::index');
$routes->get('clients', 'ClientController::index'); // 對應網址：GET clients
$routes->get('clients/create', 'ClientController::create'); // 對應網址：GET clients/create

//post
$routes->post('clients', 'ClientController::store'); // 對應網址：POST clients