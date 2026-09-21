<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="UTF-8">
    <title>新增案主</title>
    <link rel="stylesheet" href="<?= base_url('css/style.css') ?>">
</head>

<body>
    <div class="layout">

        <aside class="sidebar">
            <h2>案主管理系統</h2>

            <nav>
                <a href="#">首頁</a>
                <a href="<?= base_url('clients') ?>">案主資料</a>
                <a href="<?= base_url('clients/create') ?>">新增案主</a>
                <a href="#">資源回收桶</a>
                <a href="#">登出</a>
            </nav>
        </aside>

        <main class="content">
            <h1>新增案主</h1>

            <form>
                <div>
                    <label for="ct_name">案主姓名</label>
                    <input type="text" id="ct_name" name="ct_name">
                </div>
                <br>

                <div>
                    <label for="ct_address">案主地址</label>
                    <input type="text" id="ct_address" name="ct_address">
                </div>
                <br>

                <div>
                    <label for="route_no">路線編號</label>
                    <input type="number" id="route_no" name="route_no">
                </div>
                <br>

                <button type="submit">新增案主</button>
            </form>
        </main>

    </div>
</body>

</html>