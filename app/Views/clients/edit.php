<?php /**@var array $client*/ ?>
<!DOCTYPE html>
<html lang="zh-TW">

<head>
    <meta charset="UTF-8">
    <title>修改案主</title>

    <!-- 載入 public/css/style.css -->
    <link rel="stylesheet" href="<?= base_url('css/style.css') ?>">
</head>


<body>

    <!-- 整體左右版面 -->
    <div class="layout">
        <!-- 左側選單 -->
        <aside class="sidebar">
            <h2>案主管理系統</h2>

            <nav>
                <!-- 尚未建立首頁Route,所以暫時使用 # -->
                <a href="#">首頁</a>
                <!-- 前往案主資料列表 -->
                <a href="<?= base_url('clients') ?>">案主資料</a>
                <!-- 前往新增案主列表 -->
                <a href="<?= base_url('clients/create') ?>">新增案主</a>
                <!-- 尚未建立資源回收桶Route,所以暫時使用 # -->
                <a href="<?= base_url('clients/trash') ?>">資源回收桶</a>
            </nav>
            <?php
            // 從登入 Session 取得顯示名稱；沒有時退回帳號名稱
            $currentDisplayName = session()->get('display_name')
                ?: session()->get('username')
                ?: '使用者';

            // 將資料庫角色代碼轉成畫面上的中文名稱
            $roleLabels = [
                'admin'  => '系統管理員',
                'staff'  => '工作人員',
                'viewer' => '唯讀使用者',
            ];

            // 找不到角色代碼時顯示預設文字
            $currentRole = session()->get('role') ?? '';
            $currentRoleLabel = $roleLabels[$currentRole] ?? '未設定角色';
            ?>

            <!-- 這個區塊放在側欄底部，顯示登入者資訊和操作 -->
            <div class="sidebar-footer">

                <!-- 顯示登入者名稱與角色 -->
                <div class="sidebar-account">
                    <p class="sidebar-account-name">
                        <?= esc($currentDisplayName) ?>
                    </p>

                    <p class="sidebar-account-role">
                        <?= esc($currentRoleLabel) ?>
                    </p>
                </div>

                <!-- 個人設定目前連到已完成的更新密碼頁面 -->
                <a
                    href="<?= base_url('change-password') ?>"
                    class="sidebar-settings-link"
                >
                    個人設定／更新密碼
                </a>

                <!-- 登出會改變登入狀態，所以使用 POST 表單 -->
                <form
                    action="<?= base_url('logout') ?>"
                    method="post"
                    class="sidebar-logout-form"
                >
                    <!-- 產生 CSRF Token，讓 CSRF Filter 驗證登出請求 -->
                    <?= csrf_field() ?>

                    <!-- 送出登出請求 -->
                    <button type="submit" class="sidebar-logout-button">
                        登出
                    </button>
                </form>
            </div>
        </aside>

        <!-- 右側主要內容 -->
        <main class="content">
            <h1>修改案主</h1>

            <?php if (session()->has('errors')): ?>
                <!-- 逐條顯示Controller傳回的驗證錯誤 -->
                <ul class="message-error">
                    <?php foreach (session('error') as $error):?>
                    <li><?= esc($error) ?></li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>

            <!-- 目前先建立表單畫面
            下一階段才會設定 action 與更新 Route -->
            <form action="<?= base_url('clients/update/' . $client['id']) ?>" method="post">

                <!--
                    產生 CSRF Token
                    CSRF Filter 會檢查表單是否來自自己的網站
                -->
                <?= csrf_field() ?>

                <!-- 顯示目前修改的案主編號 -->
                <p>
                    案主編號：
                    <?= esc($client['id']) ?>
                </p>

                <div>
                    <label for="ct_name">案主姓名</label>

                    <!--
                    value 放入資料庫原本的案主姓名
                    esc() 避免資料造成 XSS
                    -->
                    <input type="text" name="ct_name" id="ct_name" value="<?= esc(old('ct_name', $client['ct_name'])) ?>">
                </div>
                <br>

                <div>
                    <label for="ct_address">案主地址</label>

                    <!-- 顯示資料庫原本的案主地址 -->
                    <input type="text" id="ct_address" name="ct_address" value="<?= esc(old('ct_address', $client['ct_address'])) ?>">
                </div>
                <br>

                <div>
                    <label for="route_no">路線編號</label>

                    <!-- 顯示資料庫原本的案主路線編號 -->
                    <input type="number" id="route_no" name="route_no" value="<?= esc(old('route_no', $client['route_no'])) ?>">
                </div>
                <br>

                <!--
                    type="submit" 會送出目前所在的表單
                    btn-primary 是藍色按鈕樣式 (style.css)
                -->
                <button type="submit" class="btn btn-primary">儲存修改</button>

                <!-- 返回列表只是前往其他頁面
                所以繼續使用 a，再套用按鈕樣式 -->
                <a class="btn btn-warning" href="<?= base_url('clients') ?>">返回列表</a>

            </form>
        </main>

    </div>



</body>

</html>