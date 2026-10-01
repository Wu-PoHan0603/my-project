<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class AuthFilter implements FilterInterface
{
    // 在 Controller 執行前檢查登入狀態
    public function before(RequestInterface $request, $arguments = null)
    {
        // 沒有登入標記就導回登入頁
        if (session()->get('isLoggedIn') !== true) {
            return redirect()
                ->to('/login')
                ->with('error', '請先登入');
        }

        // 有登入標記時，讓請求繼續執行
        return null;
    }

    // 這個 Filter 不需要修改 Controller 執行後的回應
    public function after(
        RequestInterface $request,
        ResponseInterface $response,
        $arguments = null
    ) {
        // 不做額外處理
    }
}