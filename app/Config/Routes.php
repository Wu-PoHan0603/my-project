<?php

use CodeIgniter\Router\RouteCollection;

/** @var RouteCollection $routes */


$routes->get('/', 'Home::index');


$routes->get('clients', 'ClientController::index', ['filter' => 'auth']); 


$routes->get('clients/create', 'ClientController::create', ['filter' => 'role:admin,staff']); 


$routes->get('clients/edit/(:num)','ClientController::edit/$1', ['filter' => 'role:admin,staff']);


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
    ['filter' => ['auth', 'role:admin,staff']]
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
    ['filter' => ['auth', 'role:admin,staff']]
);

// 顯示路線指派頁
$routes->get(
    'delivery-routes',
    'DeliveryRouteController::index',
    ['filter' => 'auth']
);


// POST 操作：會新增、修改或刪除資料的請求

// 新增案主
// 使用 POST 傳送表單；案主網址已有 auth Filter，這裡限制只有管理員和工作人員能新增
$routes->post(
    'clients',
    'ClientController::store',
    ['filter' => 'role:admin,staff']
);

// 修改指定案主
// (:num) 代表網址中的案主編號，$1 會將編號傳給 update()
// 案主網址已有 auth Filter，這裡限制只有管理員和工作人員能修改
$routes->post(
    'clients/update/(:num)',
    'ClientController::update/$1',
    ['filter' => 'role:admin,staff']
);

// 刪除指定案主
// 刪除會改變資料狀態，因此使用 POST
// 案主網址已有 auth Filter，這裡限制只有管理員和工作人員能刪除
$routes->post(
    'clients/delete/(:num)',
    'ClientController::delete/$1',
    ['filter' => 'role:admin,staff']
);

// 還原資源回收桶中的案主
// (:num) 代表案主編號，$1 會傳給 restore()
// 案主網址已有 auth Filter，這裡限制只有管理員和工作人員能還原
$routes->post(
    'clients/restore/(:num)',
    'ClientController::restore/$1',
    ['filter' => 'role:admin,staff']
);

// 永久刪除指定案主
// (:num) 代表案主編號，$1 會傳給 forceDelete()
// 案主網址已有 auth Filter，這裡限制只有管理員和工作人員能永久刪除
$routes->post(
    'clients/force-delete/(:num)',
    'ClientController::forceDelete/$1',
    ['filter' => 'role:admin,staff']
);

// 接收登入表單
// 登入前尚未有使用者角色，因此不套用 auth 或 role Filter
$routes->post(
    'login',
    'AuthController::attemptLogin'
);

// 登出目前登入者
// 登出會清除 Session，因此要求使用者先登入
$routes->post(
    'logout',
    'AuthController::logout',
    ['filter' => 'auth']
);

// 更新目前登入者的密碼
// 所有已登入角色都可以修改自己的密碼
$routes->post(
    'change-password',
    'AuthController::updatePassword',
    ['filter' => 'auth']
);

// 修改指定案主的詳細資料
// (:num) 代表案主編號，$1 會傳給 updateDetails()
// 案主網址已有 auth Filter，這裡限制只有管理員和工作人員能修改
$routes->post(
    'clients/(:num)/details',
    'ClientController::updateDetails/$1',
    ['filter' => 'role:admin,staff']
);

// 上傳指定案主的附件
// (:num) 代表案主編號，$1 會傳給 uploadAttachment()
// 案主網址已有 auth Filter，這裡限制只有管理員和工作人員能上傳
$routes->post(
    'clients/(:num)/attachments',
    'ClientController::uploadAttachment/$1',
    ['filter' => 'role:admin,staff']
);

// 刪除指定案主的附件
// 第一個 (:num) 是案主編號，第二個 (:num) 是附件編號
// $1、$2 會依序傳給 deleteAttachment()
// 案主網址已有 auth Filter，這裡限制只有管理員和工作人員能刪除附件
$routes->post(
    'clients/(:num)/attachments/(:num)/delete',
    'ClientController::deleteAttachment/$1/$2',
    ['filter' => 'role:admin,staff']
);

// 新增送餐員
// 要求使用者已登入，且角色是管理員或工作人員
$routes->post(
    'delivery_staff',
    'DeliveryStaffController::store',
    ['filter' => ['auth', 'role:admin,staff']]
);

// 修改指定送餐員
// (:num) 代表送餐員編號，$1 會傳給 update()
$routes->post(
    'delivery_staff/update/(:num)',
    'DeliveryStaffController::update/$1',
    ['filter' => ['auth', 'role:admin,staff']]
);

// 刪除指定送餐員
// (:num) 代表送餐員編號，$1 會傳給 delete()
$routes->post(
    'delivery_staff/delete/(:num)',
    'DeliveryStaffController::delete/$1',
    ['filter' => ['auth', 'role:admin,staff']]
);

// 儲存指定路線的送餐員指派
// (:num) 代表路線資料編號，$1 會傳給 assign()
$routes->post(
    'delivery-routes/assign/(:num)',
    'DeliveryRouteController::assign/$1',
    ['filter' => ['auth', 'role:admin,staff']]
);