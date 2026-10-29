<?php

namespace App\Controllers; // 指定 Controller 的命名空間

use App\Libraries\SmartyEngine; // 載入剛建立的 Smarty 元件

class SmartyDemoController extends BaseController // 建立 Smarty 測試 Controller
{
    public function index()
    {
        // 建立 Smarty 元件，使用先前設定好的模板與快取資料夾
        $smarty = new SmartyEngine();

        // 建立兩筆假資料，這一步先不查詢資料庫
        $clients = [
            [
                'id'       => 1,
                'name'     => '測試案主甲',
                'address'  => '臺中市北屯區',
                'route_no' => 47,
            ],
            [
                'id'       => 2,
                'name'     => '測試案主乙',
                'address'  => '臺中市南區',
                'route_no' => 50,
            ],
        ];

        // 將標題傳給 Smarty 模板，模板會用 {$title} 顯示
        $smarty->assign('title', 'Smarty 案主清單測試');

        // 將假案主陣列傳給 Smarty 模板，模板會用 {$clients} 讀取
        $smarty->assign('clients', $clients);

        // 執行 hello.tpl，取得 Smarty 產生的 HTML
        $html = $smarty->fetch('hello.tpl');

        // 告訴瀏覽器回傳 UTF-8 HTML
        return $this->response
            ->setContentType('text/html', 'UTF-8')
            ->setBody($html);
    }
}