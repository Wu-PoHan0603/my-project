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
                <a href="#">資源回收桶</a>
                <!-- 尚未建立登出Route,所以暫時使用 # -->
                <a href="#">登出</a>
            </nav>

        </aside>

        <!-- 右側主要內容 -->
        <main class="content">
            <h1>修改案主</h1>

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
                    <input type="text" name="ct_name" id="ct_name" value="<?= esc($client['ct_name']) ?>">
                </div>
                <br>

                <div>
                    <label for="ct_address">案主地址</label>

                    <!-- 顯示資料庫原本的案主地址 -->
                    <input type="text" id="ct_address" name="ct_address" value="<?= esc($client['ct_address']) ?>">
                </div>
                <br>

                <div>
                    <label for="route_no">路線編號</label>

                    <!-- 顯示資料庫原本的案主路線編號 -->
                    <input type="number" id="route_no" name="route_no" value="<?= esc($client['route_no']) ?>">
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