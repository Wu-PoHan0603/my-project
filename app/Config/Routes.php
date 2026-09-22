<?php

use CodeIgniter\Router\RouteCollection;

/** @var RouteCollection $routes */

//get 顯示
$routes->get('/', 'Home::index');

// 對應網址：GET clients
$routes->get('clients', 'ClientController::index'); 

// 對應網址：GET clients/create
$routes->get('clients/create', 'ClientController::create'); 

//對應網址：GET clients/edit/:num取得的數字 
//(:num) 是 CI4 Route 的數字佔位符,只接受數字
// $1 代表網址中 (:num) 取得的數字
$routes->get('clients/edit/(:num)','ClientController::edit/$1');

//post 操作
// 對應網址：POST clients
$routes->post('clients', 'ClientController::store'); 

// 接收指定案主送出的修改資料
// 例如 POST /clients/update/2
$routes->post('clients/update/(:num)', 'ClientController::update/$1');