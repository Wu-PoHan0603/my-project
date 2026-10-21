<?php
/**
 * Controller 傳入首頁 View 的變數。
 * @var string $greeting
 * @var string $username
 * @var string $displayName
 * @var string $roleLabel
 */
?>
<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="UTF-8">

    <!-- 載入專案共用 CSS；這一步先建立結構，下一步再補首頁專屬樣式 -->
    <link rel="stylesheet" href="<?= base_url('css/style.css') ?>">

    <title>系統首頁</title>
</head>

<body>
    <!-- layout 是整頁的外層：左側選單和右側內容放在裡面 -->
    <div class="layout">

        <!-- 左側導覽區 -->
        <aside class="sidebar">
            <h2>案主管理系統</h2>

            <!-- 系統主要功能連結 -->
            <nav aria-label="主要導覽">
                <!-- 首頁目前所在位置 -->
                <a class="home-nav-current" href="<?= base_url('/') ?>">首頁</a>

                <!-- 案主管理可展開，裡面放案主資料連結 -->
                <details class="sidebar-menu-group" open>
                    <summary>案主管理</summary>

                    <div class="sidebar-submenu">
                        <a href="<?= base_url('clients') ?>">案主資料</a>
                    </div>
                </details>

                <!-- 前往資源回收桶 -->
                <a href="<?= base_url('clients/trash') ?>">資源回收桶</a>
            </nav>

            <!-- 左下角個人設定；點開後才顯示帳號、角色及相關功能 -->
            <div class="sidebar-footer">
                <details class="sidebar-settings">
                    <summary class="sidebar-settings-trigger">個人設定</summary>

                    <div class="sidebar-settings-panel">
                        <!-- 顯示目前登入的帳號 -->
                        <p class="sidebar-account-row">
                            <span class="sidebar-account-label">登入帳號</span>
                            <strong class="sidebar-account-value">
                                <?= esc($username) ?>
                            </strong>
                        </p>

                        <!-- 顯示個人設定中的顯示名稱 -->
                        <p class="sidebar-account-row">
                            <span class="sidebar-account-label">顯示名稱</span>
                            <strong class="sidebar-account-value">
                                <?= esc($displayName) ?>
                            </strong>
                        </p>

                        <!-- 顯示中文角色名稱 -->
                        <p class="sidebar-account-row">
                            <span class="sidebar-account-label">角色</span>
                            <strong class="sidebar-account-value">
                                <?= esc($roleLabel) ?>
                            </strong>
                        </p>

                        <!-- 前往修改密碼頁面 -->
                        <a
                            href="<?= base_url('change-password') ?>"
                            class="sidebar-settings-link"
                        >
                            更新密碼
                        </a>

                        <!-- 登出會改變登入狀態，所以使用 POST 表單 -->
                        <form
                            action="<?= base_url('logout') ?>"
                            method="post"
                            class="sidebar-logout-form"
                        >
                            <!-- 產生 CSRF 欄位，供 CI4 驗證表單來源 -->
                            <?= csrf_field() ?>

                            <!-- 按下按鈕後送出登出表單 -->
                            <button type="submit" class="sidebar-logout-button">
                                登出
                            </button>
                        </form>
                    </div>
                </details>
            </div>
        </aside>

        <!-- 右側主要內容 -->
        <main class="content">

            <!-- 首頁的路徑提示；目前就在首頁，所以只顯示首頁 -->
            <nav class="page-breadcrumb" aria-label="麵包屑導覽">
                <span aria-current="page">首頁</span>
            </nav>

            <!-- 顯示依台灣時間產生的問候語與登入者名稱 -->
            <section class="home-welcome">
                <h1>
                    <?= esc($greeting) ?>，<?= esc($displayName) ?>
                </h1>

                <p>歡迎回到案主管理系統，請選擇下方功能開始工作。</p>
            </section>

            <!-- 常用功能區 -->
            <section class="home-actions-section">
                <h2>常用功能</h2>
                <p>快速前往你要處理的工作。</p>

                <!-- 這些連結連到專案中已存在的頁面 -->
                <div class="home-actions">
                    <a class="home-action" href="<?= base_url('clients') ?>">
                        <h3>案主資料</h3>
                        <p>查詢案主名單，查看或修改個案資料。</p>
                        <span>前往案主資料 →</span>
                    </a>

                    <a class="home-action home-action-add" href="<?= base_url('clients/create') ?>">
                        <h3>新增案主</h3>
                        <p>建立一筆新的案主基本資料。</p>
                        <span>新增案主 →</span>
                    </a>

                    <a class="home-action" href="<?= base_url('clients/trash') ?>">
                        <h3>資源回收桶</h3>
                        <p>查看已移除的案主資料，並進行還原。</p>
                        <span>查看回收桶 →</span>
                    </a>
                </div>
            </section>
        </main>
    </div>
</body>
</html>