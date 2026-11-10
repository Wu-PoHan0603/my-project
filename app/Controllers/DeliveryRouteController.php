<?php

namespace App\Controllers;

use App\Models\DeliveryRouteModel;
use App\Models\DeliveryStaffModel;
use CodeIgniter\Controller;
use CodeIgniter\Exceptions\PageNotFoundException;

// 處理路線列表與送餐員指派
class DeliveryRouteController extends Controller
{
    // 顯示所有路線及可指派的送餐員
    public function index()
    {
        // 建立路線 Model
        $routeModel = new DeliveryRouteModel();

        // 建立送餐員 Model
        $staffModel = new DeliveryStaffModel();

        // 查詢路線，並連接已指派送餐員的姓名
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

        // 只列出啟用中的送餐員供指派
        $staffList = $staffModel
            ->where('is_active', 1)
            ->orderBy('staff_name', 'ASC')
            ->findAll();

        // 載入路線指派畫面並傳入路線和送餐員資料
        return view('delivery_staffs/index', [
            'routes' => $routes,
            'staffList' => $staffList,
        ]);
    }

    // 儲存單一路線的送餐員指派
    public function assign(int $id)
    {
        // 建立路線 Model
        $routeModel = new DeliveryRouteModel();

        // 確認網址中的路線資料存在
        $route = $routeModel->find($id);

        // 路線不存在時顯示 404
        if ($route === null) {
            throw PageNotFoundException::forPageNotFound('找不到指定路線');
        }

        // 取得表單送來的送餐員編號
        $staffId = trim(
            (string) $this->request->getPost('delivery_staff_id')
        );

        // 空白代表取消指派；有值時必須是大於 0 的整數
        $validationData = [
            'delivery_staff_id' => $staffId,
        ];

        // 設定送餐員編號的驗證規則
        $rules = [
            'delivery_staff_id' => 'permit_empty|is_natural_no_zero',
        ];

        // 驗證失敗時返回路線列表
        if (! $this->validateData($validationData, $rules)) {
            return redirect()
                ->to('/delivery-routes')
                ->with('error', '送餐員資料格式不正確。');
        }

        // 預設為 null，表示路線目前沒有指派送餐員
        $staffIdToSave = null;

        // 使用者有選擇送餐員時，確認該送餐員存在且啟用
        if ($staffId !== '') {
            // 建立送餐員 Model
            $staffModel = new DeliveryStaffModel();

            // 只查詢啟用中的送餐員
            $staff = $staffModel
                ->where('is_active', 1)
                ->find((int) $staffId);

            // 送餐員不存在或已停用時，不儲存指派
            if ($staff === null) {
                return redirect()
                    ->to('/delivery-routes')
                    ->with('error', '找不到啟用中的送餐員，請重新選擇。');
            }

            // 將通過檢查的編號轉成整數
            $staffIdToSave = (int) $staffId;
        }

        // 更新路線所屬的送餐員；null 代表取消指派
        $updated = $routeModel->update($id, [
            'delivery_staff_id' => $staffIdToSave,
        ]);

        // 更新失敗時返回列表並顯示錯誤
        if (! $updated) {
            return redirect()
                ->to('/delivery-routes')
                ->with('error', '路線指派儲存失敗。');
        }

        // 儲存成功後返回列表
        return redirect()
            ->to('/delivery-routes')
            ->with('success', '路線指派已更新。');
    }
}