<?php

use CodeIgniter\Router\RouteCollection;

/** @var RouteCollection $routes */


$routes->get('/', 'Home::index');


$routes->get('clients', 'ClientController::index', ['filter' => 'auth']); 


$routes->get('clients/create', 'ClientController::create', ['filter' => 'auth']); 


$routes->get('clients/edit/(:num)','ClientController::edit/$1', ['filter' => 'auth']);


$routes->get('clients/trash', 'ClientController::trash', ['filter' => 'auth']);


$routes->get('login', 'AuthController::login');


$routes->get(
    'change-password',
    'AuthController::changePassword',
    ['filter' => 'auth']
);

// 登入後才能讀取案主詳細資料
// (:num) 代表網址中的案主編號
// $1 會將該編號傳給 details() 方法
$routes->get(
    'clients/(:num)/details',
    'ClientController::details/$1',
    ['filter' => 'auth']
);

// 登入後才能讀取案主照片
// (:num) 是案主編號，$1 會傳給 photoPreview()
$routes->get(
    'clients/(:num)/photo-preview',
    'ClientController::photoPreview/$1',
    ['filter' => 'auth']
);

// 顯示指定案主的附件；網址中的兩個數字依序是案主編號與附件編號
$routes->get(
    'clients/(:num)/attachments/(:num)', // 附件預覽網址
    'ClientController::viewAttachment/$1/$2', // 將案主編號與附件編號傳給 Controller
    ['filter' => 'auth'] // 限制已登入的使用者才能查看
);

// 開啟 Smarty 測試頁
$routes->get('smarty-demo', 'SmartyDemoController::index');

// 登入後才可以看到送餐員列表
$routes->get(
    'delivery_staff',
    'DeliveryStaffController::index',
    ['filter' => 'auth']
);

// 新增送餐員
$routes->get(
    'delivery_staff/create',
    'DeliveryStaffController::create',
    ['filter' => 'auth']
);

// GET 用來查看指定送餐員的詳細資料
// (:num) 接收網址中的送餐員編號
// $1 將編號傳給 DeliveryStaffController::detail()
// auth 過濾器要求使用者先登入
$routes->get(
    'delivery_staff/(:num)/detail',
    'DeliveryStaffController::detail/$1',
    ['filter' => 'auth']
);

// GET 用來開啟指定送餐員的修改表單
$routes->get(
    'delivery_staff/(:num)/edit',
    'DeliveryStaffController::edit/$1',
    ['filter' => 'auth']
);

// GET 用來開啟指定送餐員的路線表單
$routes->get('delivery-routes', 'DeliveryRouteController::index');

// 顯示路線指派頁
$routes->get(
    'delivery-routes',
    'DeliveryRouteController::index',
    ['filter' => 'auth']
);


// post 操作
// 對應網址：POST clients
$routes->post('clients', 'ClientController::store'); 

// 接收指定案主送出的修改資料
// 例如 POST /clients/update/2
$routes->post('clients/update/(:num)', 'ClientController::update/$1');

//接收刪除案主的POST請求 因為會改變資料狀態 不使用GET
//(:num) 代表網址必須提供數字編號
//$1 會把網址中的編號傳給delete()方式
$routes->post('clients/delete/(:num)', 'ClientController::delete/$1');

//接收還原案主的POST請求 會改變資料庫
//(:num) 代表網址這個位置只能是數字
//$1 會將數字傳給restore()方法
$routes->post('clients/restore/(:num)', 'ClientController::restore/$1');

//接收永久刪除案主的POST請求
//(:num) 只能匹配數字
//$1 會將網址中的案主編號傳給forceDelete()
$routes->post('clients/force-delete/(:num)', 'ClientController::forceDelete/$1');

// 接收登入表單：表單用 POST 送到 /login
$routes->post('login', 'AuthController::attemptLogin');

// 登出會改變 Session，所以使用 POST
$routes->post('logout', 'AuthController::logout');

// 接收修改密碼表單，並要求使用者已登入
$routes->post(
    'change-password',
    'AuthController::updatePassword',
    ['filter' => 'auth']
);

// 接收案主詳細資料表單送出的 POST 請求
// (:num) 是案主編號，$1 會把編號傳給 updateDetails()
// auth Filter 確保只有登入使用者能修改案主資料
$routes->post(
    'clients/(:num)/details',
    'ClientController::updateDetails/$1',
    ['filter' => 'auth']
);

// 接收上傳案主附件的 POST 請求
// 網址中的 (:num) 代表案主編號
// 將案主編號傳給 uploadAttachment()
// 限制已登入的使用者才能上傳
$routes->post(
    'clients/(:num)/attachments',
    'ClientController::uploadAttachment/$1',
    ['filter' => 'auth'] 
);

// 永久刪除指定案主的附件
$routes->post(
    'clients/(:num)/attachments/(:num)/delete', // 附件刪除網址
    'ClientController::deleteAttachment/$1/$2', // 傳入案主編號與附件編號
    ['filter' => 'auth'] // 限制已登入的使用者才能刪除
);

// 登入後才能新增送餐員表單
$routes->post(
    'delivery_staff',
    'DeliveryStaffController::store',
    ['filter' => 'auth']
);

// POST 用來儲存指定送餐員的修改內容
$routes->post(
    'delivery_staff/update/(:num)',
    'DeliveryStaffController::update/$1',
    ['filter' => 'auth']
);

// POST 用來直接刪除指定送餐員
$routes->post(
    'delivery_staff/delete/(:num)',
    'DeliveryStaffController::delete/$1',
    ['filter' => 'auth']
);

// 儲存指定路線的送餐員
$routes->post(
    'delivery-routes/assign/(:num)',
    'DeliveryRouteController::assign/$1',
    ['filter' => 'auth']
);