<?php

namespace App\Libraries; // 指定這個類別屬於 App\Libraries 命名空間

use Smarty\Smarty; // 載入 Composer 安裝的 Smarty 類別

class SmartyEngine extends Smarty // 建立專案自己的 Smarty 元件
{
    public function __construct() // 建立元件時自動執行初始化設定
    {
        parent::__construct(); // 先執行 Smarty 原本的初始化程序

        $templateDirectory = APPPATH . 'Views/smarty'; // 設定 .tpl 模板所在位置
        $compileDirectory = WRITEPATH . 'cache/smarty/compiled'; // 設定編譯後模板的儲存位置
        $cacheDirectory = WRITEPATH . 'cache/smarty/cache'; // 設定 Smarty 快取的儲存位置

        if (! is_dir($templateDirectory)) { // 檢查模板資料夾是否存在
            throw new \RuntimeException('找不到 Smarty 模板資料夾：' . $templateDirectory); // 不存在時顯示錯誤
        }

        foreach ([$compileDirectory, $cacheDirectory] as $directory) { // 逐一檢查編譯和快取資料夾
            if (! is_dir($directory) && ! mkdir($directory, 0775, true) && ! is_dir($directory)) { // 不存在就建立資料夾
                throw new \RuntimeException('無法建立 Smarty 資料夾：' . $directory); // 建立失敗時顯示錯誤
            }
        }

        $this->setTemplateDir($templateDirectory); // 告訴 Smarty 到哪裡尋找 .tpl 模板
        $this->setCompileDir($compileDirectory); // 告訴 Smarty 把編譯結果放在哪裡
        $this->setCacheDir($cacheDirectory); // 告訴 Smarty 把快取放在哪裡
        $this->setEscapeHtml(true); // 預設跳脫模板輸出中的 HTML 特殊字元
    }
}