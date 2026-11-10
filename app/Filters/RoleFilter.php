<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

// 在 Controller 執行前檢查使用者角色
class RoleFilter implements FilterInterface
{
    /**
     * 檢查登入者的角色是否符合此路由允許的角色。
     *
     * 路由可以傳入角色，例如：
     * role:admin,staff
     *
     * @param list<string>|null $arguments 路由允許的角色
     */
    public function before(RequestInterface $request, $arguments = null)
    {
        // 沒有設定允許角色時，預設拒絕請求
        $allowedRoles = is_array($arguments) ? $arguments : [];

        // 移除角色文字前後的空白
        $allowedRoles = array_map(
            static fn ($role) => trim((string) $role),
            $allowedRoles
        );

        // 從 Session 取得登入者的角色
        $currentRole = (string) session()->get('role');

        // 角色不在允許清單中時，回傳 403 禁止存取
        if (! in_array($currentRole, $allowedRoles, true)) {
            return service('response')
                ->setStatusCode(403)
                ->setBody('403 權限不足，無法執行此操作。');
        }

        // 角色符合允許清單，繼續執行 Controller
        return null;
    }

    /**
     * 此 Filter 不需要修改 Controller 執行後的回應。
     */
    public function after(
        RequestInterface $request,
        ResponseInterface $response,
        $arguments = null
    ) {
        // 不執行額外處理
    }
}