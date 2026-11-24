<?php

namespace App\Controllers;

use App\Libraries\SmartyEngine;
use App\Models\DeliveryRouteModel;
use App\Models\DeliveryStaffModel;
use CodeIgniter\Controller;
use CodeIgniter\Exceptions\PageNotFoundException;
use App\Models\ClientModel;

// 處理路線列表及送餐員指派
class DeliveryRouteController extends Controller
{
    // 顯示所有路線及可指派的啟用中送餐員
    public function index()
    {
        // 建立路線與送餐員 Model
        $routeModel = new DeliveryRouteModel();
        $staffModel = new DeliveryStaffModel();

        // 查詢路線，以及目前被指派的送餐員姓名
        $routes = $routeModel
            ->select(
                'delivery_routes.id,
                 delivery_routes.route_no,
                 delivery_routes.delivery_staff_id,
                 delivery_staff.staff_name'
            )
            ->join(
                'delivery_staff',
                'delivery_staff.id = delivery_routes.delivery_staff_id',
                'left'
            )
            ->orderBy('delivery_routes.route_no', 'ASC')
            ->findAll();

        // 只讓啟用中的送餐員出現在指派選單
        $staffList = $staffModel
            ->where('is_active', 1)
            ->orderBy('staff_name', 'ASC')
            ->findAll();

        // 建立 Smarty 元件
        $smarty = new SmartyEngine();

        // 傳入側邊欄及頁面導覽網址
        $this->assignCommonData($smarty);

        // 傳入路線與送餐員資料
        $smarty->assign('routes', $routes);
        $smarty->assign('staffList', $staffList);

        // 傳入指派操作權限
        $currentRole = session()->get('role') ?? '';
        $canManageRoutes = in_array(
            $currentRole,
            ['admin', 'staff'],
            true
        );
        $smarty->assign('canManageRoutes', $canManageRoutes);

        // 傳入操作結果訊息
        $smarty->assign(
            'successMessage',
            session()->getFlashdata('success') ?? ''
        );
        $smarty->assign(
            'errorMessage',
            session()->getFlashdata('error') ?? ''
        );

        // 傳入指派表單需要的 CSRF 欄位
        $smarty->assign('csrfField', csrf_field());

        // 執行 Smarty 模板並取得 HTML
        $html = $smarty->fetch('delivery_routes_index.tpl');

        // 回傳 UTF-8 HTML 頁面
        return $this->response
            ->setContentType('text/html', 'UTF-8')
            ->setBody($html);
    }

    // 顯示指定送餐員目前的路線，以及可指派的路線
    public function staffRoutes(int $staffId)
    {
        $staffModel = new DeliveryStaffModel();
        $staff = $staffModel->find($staffId);

        if ($staff === null) {
            throw PageNotFoundException::forPageNotFound('找不到指定送餐員');
        }

        // 只查詢尚未指派，或目前已指派給這位送餐員的路線
        $routeList = db_connect()
            ->table('delivery_routes')
            ->select('id, route_no, delivery_staff_id')
            ->groupStart()
                ->where('delivery_staff_id', null)
                ->orWhere('delivery_staff_id', $staffId)
            ->groupEnd()
            ->orderBy('route_no', 'ASC')
            ->get()
            ->getResultArray();

        // 整理目前已指派給這位送餐員的路線 ID
        $selectedRouteIds = [];

        foreach ($routeList as $route) {
            if ((int) ($route['delivery_staff_id'] ?? 0) === $staffId) {
                $selectedRouteIds[] = (int) $route['id'];
            }
        }

        $smarty = new SmartyEngine();
        $this->assignCommonData($smarty);

        $smarty->assign('staff', $staff);
        $smarty->assign('routes', $routeList);
        $smarty->assign('selectedRouteIds', $selectedRouteIds);
        $smarty->assign(
            'successMessage',
            session()->getFlashdata('success') ?? ''
        );
        $smarty->assign(
            'errorMessage',
            session()->getFlashdata('error') ?? ''
        );
        $smarty->assign('csrfField', csrf_field());

        $html = $smarty->fetch('delivery_staff_routes.tpl');

        return $this->response
            ->setContentType('text/html', 'UTF-8')
            ->setBody($html);
    }

    // 儲存指定送餐員負責的路線
    public function saveStaffRoutes(int $staffId)
    {
        // 確認送餐員存在
        $staffModel = new DeliveryStaffModel();
        $staff = $staffModel->find($staffId);

        if ($staff === null) {
            throw PageNotFoundException::forPageNotFound('找不到指定送餐員');
        }

        // 沒有勾選路線時，表單不會送 route_ids
        $routeIds = $this->request->getPost('route_ids') ?? [];

        if (! is_array($routeIds)) {
            return redirect()
                ->to('/delivery_staff/' . $staffId . '/routes')
                ->with('error', '路線資料格式不正確。');
        }

        // 只允許選擇尚未指派，或目前屬於這位送餐員的路線
        $availableRoutes = db_connect()
            ->table('delivery_routes')
            ->select('id')
            ->groupStart()
                ->where('delivery_staff_id', null)
                ->orWhere('delivery_staff_id', $staffId)
            ->groupEnd()
            ->get()
            ->getResultArray();

        $availableIds = array_map(
            'intval',
            array_column($availableRoutes, 'id')
        );

        foreach ($routeIds as $routeId) {
            if (
                ! ctype_digit((string) $routeId)
                || ! in_array((int) $routeId, $availableIds, true)
            ) {
                return redirect()
                    ->to('/delivery_staff/' . $staffId . '/routes')
                    ->with('error', '所選路線無法指派，請重新選擇。');
            }
        }

        $routeIds = array_values(array_unique(array_map('intval', $routeIds)));

        $db = db_connect();
        $db->transBegin();

        // 先解除這位送餐員原本的路線
        $db->table('delivery_routes')
            ->where('delivery_staff_id', $staffId)
            ->update(['delivery_staff_id' => null]);

        // 再指派表單中勾選的路線
        if (! empty($routeIds)) {
            $db->table('delivery_routes')
                ->whereIn('id', $routeIds)
                ->update(['delivery_staff_id' => $staffId]);
        }

        if ($db->transStatus() === false) {
            $db->transRollback();

            return redirect()
                ->to('/delivery_staff/' . $staffId . '/routes')
                ->with('error', '路線儲存失敗，請稍後再試。');
        }

        $db->transCommit();

        return redirect()
            ->to('/delivery_staff/' . $staffId . '/routes')
            ->with('success', '送餐員負責路線已更新。');
    }

    // 顯示指定路線的案主列表
    public function routeClients(int $routeId)
    {
        $routeModel = new DeliveryRouteModel();
        $route = $routeModel->find($routeId);

        if ($route === null) {
            throw PageNotFoundException::forPageNotFound('找不到指定路線');
        }

        // 依照案主資料中的 route_no 找出這條路線的案主
        $clientModel = new ClientModel();
        $clients = $clientModel
            ->where('route_no', $route['route_no'])
            ->orderBy('delivery_order', 'ASC')
            ->orderBy('id', 'ASC')
            ->findAll();

        // 只挑出已經有經緯度的案主，準備交給地圖顯示
        $mapClients = array_values(array_map(
            static function (array $client): array {
                return [
                    'id'       => (int) $client['id'],
                    'name'     => $client['ct_name'],
                    'address'  => $client['ct_addr'],
                    'latitude' => (float) $client['latitude'],
                    'longitude'=> (float) $client['longitude'],
                    'order'    => (int) ($client['delivery_order'] ?? 0),
                ];
            },
            array_filter(
                $clients,
                static fn (array $client): bool =>
                    is_numeric($client['latitude'] ?? null)
                    && is_numeric($client['longitude'] ?? null)
            )
        ));

        // 轉成 JSON，並避免案主文字被當成 HTML 或 JavaScript 程式碼
        $mapClientsJson = json_encode(
            $mapClients,
            JSON_UNESCAPED_UNICODE
            | JSON_HEX_TAG
            | JSON_HEX_APOS
            | JSON_HEX_AMP
            | JSON_HEX_QUOT
        );

        // 傳給 Smarty 地圖模板
        $smarty->assign('mapClientsJson', $mapClientsJson ?: '[]');
        
        $smarty = new SmartyEngine();
        $this->assignCommonData($smarty);

        $smarty->assign('route', $route);
        $smarty->assign('clients', $clients);
        $smarty->assign(
            'routeOrderUrl',
            base_url('delivery-routes/' . $routeId . '/clients/order')
        );
        $smarty->assign('csrfField', csrf_field());

        $smarty->assign(
            'successMessage',
            session()->getFlashdata('success') ?? ''
        );
        $smarty->assign(
            'errorMessage',
            session()->getFlashdata('error') ?? ''
        );

        $html = $smarty->fetch('delivery_route_clients.tpl');

        return $this->response
            ->setContentType('text/html', 'UTF-8')
            ->setBody($html);
    }

    // 儲存拖曳後的案主送餐順序
    public function saveClientOrder(int $routeId)
    {
        $routeModel = new DeliveryRouteModel();
        $route = $routeModel->find($routeId);

        if ($route === null) {
            throw PageNotFoundException::forPageNotFound('找不到指定路線');
        }

        // 表單中的 client_ids[] 順序就是拖曳後的順序
        $clientIds = $this->request->getPost('client_ids');

        if (! is_array($clientIds)) {
            return redirect()
                ->to('/delivery-routes/' . $routeId . '/clients')
                ->with('error', '案主順序資料格式不正確。');
        }

        // 確認每個案主編號都是正整數
        foreach ($clientIds as $clientId) {
            if (! ctype_digit((string) $clientId)) {
                return redirect()
                    ->to('/delivery-routes/' . $routeId . '/clients')
                    ->with('error', '案主編號資料不正確。');
            }
        }

        $clientIds = array_map('intval', $clientIds);

        // 不允許重複的案主編號
        if (count($clientIds) !== count(array_unique($clientIds))) {
            return redirect()
                ->to('/delivery-routes/' . $routeId . '/clients')
                ->with('error', '案主排序資料有重複項目。');
        }

        // 取得目前屬於這條路線的案主編號
        $clientModel = new ClientModel();
        $expectedIds = $clientModel
            ->select('id')
            ->where('route_no', $route['route_no'])
            ->findColumn('id');

        $expectedIds = array_map('intval', $expectedIds ?? []);

        // 確認送來的案主清單與這條路線目前的案主完全相同
        $submittedIds = $clientIds;
        sort($expectedIds);
        sort($submittedIds);

        if ($expectedIds !== $submittedIds) {
            return redirect()
                ->to('/delivery-routes/' . $routeId . '/clients')
                ->with('error', '案主清單已變更，請重新整理頁面後再排序。');
        }

        $db = db_connect();
        $db->transBegin();

        // 依表單中的順序，從 1 開始寫入送餐順序
        foreach ($clientIds as $index => $clientId) {
            if (! $clientModel->update($clientId, [
                'delivery_order' => $index + 1,
            ])) {
                $db->transRollback();

                return redirect()
                    ->to('/delivery-routes/' . $routeId . '/clients')
                    ->with('error', '送餐順序儲存失敗。');
            }
        }

        if ($db->transStatus() === false) {
            $db->transRollback();

            return redirect()
                ->to('/delivery-routes/' . $routeId . '/clients')
                ->with('error', '送餐順序儲存失敗。');
        }

        $db->transCommit();

        return redirect()
            ->to('/delivery-routes/' . $routeId . '/clients')
            ->with('success', '送餐順序已儲存。');
    }

    // 儲存單一路線的送餐員指派
    public function assign(int $id)
    {
        // 建立路線 Model，確認指定路線存在
        $routeModel = new DeliveryRouteModel();
        $route = $routeModel->find($id);

        // 找不到路線時顯示 404
        if ($route === null) {
            throw PageNotFoundException::forPageNotFound('找不到指定路線');
        }

        // 取得表單送來的送餐員編號
        $staffId = trim(
            (string) $this->request->getPost('delivery_staff_id')
        );

        // 空白代表取消指派；有值時必須是正整數
        $validationData = [
            'delivery_staff_id' => $staffId,
        ];
        $rules = [
            'delivery_staff_id' => 'permit_empty|is_natural_no_zero',
        ];

        // 格式錯誤時回到路線指派頁
        if (! $this->validateData($validationData, $rules)) {
            return redirect()
                ->to('/delivery-routes')
                ->with('error', '送餐員資料格式不正確。');
        }

        // 預設為 null，表示取消目前指派
        $staffIdToSave = null;

        // 有選擇送餐員時，確認該送餐員存在且啟用
        if ($staffId !== '') {
            $staffModel = new DeliveryStaffModel();

            $staff = $staffModel
                ->where('is_active', 1)
                ->find((int) $staffId);

            // 送餐員不存在或已停用時不更新
            if ($staff === null) {
                return redirect()
                    ->to('/delivery-routes')
                    ->with('error', '找不到啟用中的送餐員，請重新選擇。');
            }

            $staffIdToSave = (int) $staffId;
        }

        // 更新路線的送餐員指派
        $updated = $routeModel->update($id, [
            'delivery_staff_id' => $staffIdToSave,
        ]);

        // 更新失敗時返回頁面並顯示錯誤
        if (! $updated) {
            return redirect()
                ->to('/delivery-routes')
                ->with('error', '路線指派儲存失敗。');
        }

        // 更新成功後返回路線指派頁
        return redirect()
            ->to('/delivery-routes')
            ->with('success', '路線指派已更新。');
    }

    // 將共用網址及登入者資訊交給 Smarty 模板
    private function assignCommonData(SmartyEngine $smarty): void
    {
        // 傳入共用 CSS 網址
        $smarty->assign('cssUrl', base_url('css/style.css'));

        // 傳入側邊欄與麵包屑會使用的網址
        $smarty->assign('homeUrl', base_url('/'));
        $smarty->assign('clientListUrl', base_url('clients'));
        $smarty->assign('clientImportUrl', base_url('clients/import'));
        $smarty->assign('staffListUrl', base_url('delivery_staff'));
        $smarty->assign('deliveryRoutesUrl', base_url('delivery-routes'));
        $smarty->assign('trashUrl', base_url('clients/trash'));
        $smarty->assign('changePasswordUrl', base_url('change-password'));
        $smarty->assign('logoutUrl', base_url('logout'));

        // 取得登入帳號
        $currentUsername = session()->get('username') ?? '未取得帳號';

        // 取得顯示名稱；未設定時使用登入帳號
        $currentDisplayName = session()->get('display_name')
            ?: $currentUsername;

        // 將角色代碼轉成中文名稱
        $roleLabels = [
            'admin'  => '系統管理員',
            'staff'  => '工作人員',
            'viewer' => '唯讀使用者',
        ];

        $currentRole = session()->get('role') ?? '';
        $currentRoleLabel = $roleLabels[$currentRole] ?? '未設定角色';

        // 傳入側欄顯示的登入者資訊
        $smarty->assign('currentUsername', $currentUsername);
        $smarty->assign('currentDisplayName', $currentDisplayName);
        $smarty->assign('currentRoleLabel', $currentRoleLabel);

        // 傳入登出表單需要的 CSRF 欄位
        $smarty->assign('logoutCsrfField', csrf_field());
    }
}