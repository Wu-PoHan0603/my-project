<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="UTF-8">
    <title>案主資料管理</title>
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
        
        <!-- 網頁右側的主要內容 -->
        <main class="content">

            <!-- 頁面標題 -->
            <h1>案主資料管理</h1>

            <!--
                判斷 Session 中是否存在新增成功訊息
                Flashdata 只會顯示一次
            -->
            <?php if (session()->getFlashdata('success')): ?>
                <p>
                    <!-- 使用 esc() 安全顯示成功訊息 -->
                    <?= esc(session()->getFlashdata('success')) ?>
                </p>
            <?php endif ?>

            <!-- 搜尋案主區域 -->
            <section class="search-section">
                <!--
                    for="keyword" 對應輸入框的 id="keyword"
                    點擊文字時，游標會進入輸入框
                -->

                <label for="keyword">搜索案主</label>
                <!-- 案主姓名搜尋輸入框 -->

                <input type="text" name="keyword" id="keyword" placeholder="請輸入案主姓名">

                <!--
                    目前按鈕只有畫面
                    搜尋功能會在後續章節實作
                -->
                <button type="button">搜尋</button>
            </section>

            <!-- 案主資料表格區域 -->
            <section class="client-table-section">

                    <h2>案主資料列表</h2>

                    <?php if (!empty($clients)): ?>
                        <!--
                        $clients 有資料時顯示表格
                        empty() 用來判斷變數是否為空
                        ! 代表「不是」
                        所以 ! empty($clients) 代表 clients 不是空的
                        -->
                        <table class="client-table">

                            <thead>
                                <tr>
                                    <th>編號</th>
                                    <th>案主姓名</th>
                                    <th>案主地址</th>
                                    <th>路線編號</th>
                                    <th>操作</th>
                                </tr>
                            </thead>

                            <tbody>
                                <?php foreach ($clients as $client): ?>

                                <!-- foreach 會逐筆讀取案主資料
                                    每次取出一筆資料放入 $client -->

                                    <tr>
                                    <td><?= esc($client['id']) ?></td>
                                    <td><?= esc($client['ct_name']) ?></td>
                                    <td><?= esc($client['ct_address']) ?></td>
                                    <td><?= esc($client['route_no']) ?></td>
                                    <td><a href="<?= base_url('clients/edit/' . $client['id']) ?>">修改</a></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php else: ?>
                        <!-- $clients 沒有資料時顯示提示文字 -->
                        <p>目前沒有案主資料</p>
                    <?php endif; ?>
            </section>
        </main>
    </div>
</body>
    
</html>