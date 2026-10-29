<?php
/**
 * 以下變數由 ClientController::index() 傳入。
 *
 * @var int $pageSize
 * @var int[] $pageSizeOptions
 * @var int $currentPage
 * @var int $pageCount
 * @var string $keyword
 */
?>
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
                <a href="<?= base_url('/') ?>">首頁</a>

                <!-- 點擊「案主管理」可展開或收合子選單 -->
                <!-- open 代表頁面載入時先展開；移除 open 就會預設收合 -->
                <!-- details 與 summary 標籤是用來製作原生摺疊面板 -->
                <!-- 容器元素 -->
                <details class="sidebar-menu-group" open>
                    <!-- 標題元素 -->
                    <summary>案主管理</summary>

                    <div class="sidebar-submenu">
                        <!-- 案主資料連結 -->
                        <a href="<?= base_url('clients') ?>">案主資料</a>
                    </div>
                </details>

                <a href="<?= base_url('clients/trash') ?>">資源回收桶</a>
            </nav>
            <?php
            // 取得登入帳號；Session 沒有帳號時顯示提示文字
            $currentUsername = session()->get('username') ?? '未取得帳號';

            // 取得顯示名稱；沒有設定時使用登入帳號
            $currentDisplayName = session()->get('display_name')
                ?: $currentUsername;

            // 將角色代碼換成中文名稱
            $roleLabels = [
                'admin'  => '系統管理員',
                'staff'  => '工作人員',
                'viewer' => '唯讀使用者',
            ];

            // 取得目前角色；未知角色顯示預設文字
            $currentRole = session()->get('role') ?? '';
            $currentRoleLabel = $roleLabels[$currentRole] ?? '未設定角色';
            ?>

            <!-- 側欄左下角只顯示「個人設定」入口 -->
            <div class="sidebar-footer">

                <!-- details/summary 可點開或收合設定內容，不需要另外寫 JavaScript -->
                <details class="sidebar-settings">

                    <!-- 收合時只會看到這個標題 -->
                    <summary class="sidebar-settings-trigger">
                        個人設定
                    </summary>

                    <!-- 點開後才顯示的設定內容 -->
                    <div class="sidebar-settings-panel">

                        <!-- 顯示登入者資訊 -->
                        <div class="sidebar-account-info">

                            <p class="sidebar-account-row">
                                <span class="sidebar-account-label">登入帳號</span>
                                <strong class="sidebar-account-value">
                                    <?= esc($currentUsername) ?>
                                </strong>
                            </p>

                            <p class="sidebar-account-row">
                                <span class="sidebar-account-label">顯示名稱</span>
                                <strong class="sidebar-account-value">
                                    <?= esc($currentDisplayName) ?>
                                </strong>
                            </p>

                            <p class="sidebar-account-row">
                                <span class="sidebar-account-label">角色</span>
                                <strong class="sidebar-account-value">
                                    <?= esc($currentRoleLabel) ?>
                                </strong>
                            </p>
                        </div>

                        <!-- 前往目前已完成的更新密碼頁面 -->
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
                            <!-- 產生 CSRF Token，供 CSRF Filter 驗證 -->
                            <?= csrf_field() ?>

                            <!-- 送出登出請求 -->
                            <button type="submit" class="sidebar-logout-button">
                                登出
                            </button>
                        </form>
                    </div>
                </details>
            </div>
        </aside>
        
        <!-- 網頁右側的主要內容 -->
        <main class="content">

            <!-- 固定在上方的頁面工具區 -->
            <div class="client-sticky-top">

                <!-- 麵包屑導覽：顯示從首頁到目前頁面的路徑 -->
                <nav class="page-breadcrumb" aria-label="麵包屑導覽">
    
                    <!-- 點擊「首頁」回到網站首頁 -->
                    <a href="<?= base_url('/') ?>">首頁</a>
    
                    <!-- 大於符號是路徑分隔符，不是連結 -->
                    <span class="page-breadcrumb-separator" aria-hidden="true">&gt;</span>
    
                    <!-- 點擊「個案管理」回到案主列表 -->
                    <span>案主管理</span>
    
                    <!-- 分隔目前所在頁與上一層路徑 -->
                    <span class="page-breadcrumb-separator" aria-hidden="true">&gt;</span>
    
                    <!-- aira-current 表示這是目前所在頁面 -->
                    <span aria-current="page">案主資料</span>
    
                </nav>
    
                <!-- 頁面標題 -->
                <h1>案主資料管理</h1>
                
                <!-- 搜尋案主區域 -->
                <!-- 搜尋表單使用GET action指定送回/clients -->
                <form action="<?= base_url('clients') ?>" class="search-form" method="get">
                    <!-- label的for對應輸入框id 點擊文字時，游標會進入輸入框 -->
                    <label for="keyword">搜索案主</label>

                    <!-- name="keyword" 是送給Controller 的欄位名稱 value 顯示目前搜尋關鍵字 搜尋後輸入框不會變回空白 -->
                    <input type="text" id="keyword" name="keyword" value="<?= esc(old('keyword', $keyword ?? '')) ?>" placeholder="請輸入案主名稱">

                    <!-- 送出GET搜尋請求 -->
                    <button type="submit" class="btn btn-primary">搜尋</button>

                    <!-- 清除搜尋條件 直接回到沒有keyword的/clients -->
                    <a href="<?= base_url('clients') ?>" class="btn btn-secondary">清除</a>
                </form>

                <div class="client-list-heading">

                    <!-- 案主列表的小標題 -->
                    <h2>案主資料列表</h2>

                    <div class="client-table-toolbar">
                        <!-- 前往新增案主頁；沿用目前 CSS 的紅色按鈕樣式 -->
                        <a href="<?= base_url('clients/create') ?>" class="btn btn-danger">新增案主</a>
                    </div>
                </div>
            </div>

            <!--
                判斷 Session 中是否存在新增成功訊息
                Flashdata 只會顯示一次
            -->
            <?php if (session()->getFlashdata('success')): ?>
                <p class="message-success">
                    <!-- 使用 esc() 安全顯示成功訊息 -->
                    <?= esc(session()->getFlashdata('success')) ?>
                </p>
            <?php endif ?>

            <?php if (session()->getFlashdata('error')):  ?>
                <p class="message-error">
                    <!-- 顯示 Controller 傳來的一次性錯誤訊息esc() 防止訊息被當成 HTML 執行 -->
                    <?= esc(session()->getFlashdata('error')) ?>
                </p>
            <?php endif ?>

            <!-- 案主資料表格區域 -->
            <section class="client-table-section">
                    

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
                                    <th>建立時間</th>
                                    <th>修改時間</th>
                                    <th>操作</th>
                                </tr>
                            </thead>

                            <tbody>
                            <?php foreach ($clients as $client): ?>

                                <?php
                                // 每次 foreach 取得一位案主後
                                // 先準備建立時間的顯示內容
                                $createdAtTaipei = '';

                                // 確認這一筆案主有 b_date
                                // 避免空值傳入 Time::parse()
                                if (! empty($client['b_date'])) {
                                    // 將資料庫的 b_date 解讀為 UTC 時間
                                    $createdAtUtc = \CodeIgniter\I18n\Time::parse(
                                        $client['b_date'],
                                        'UTC'
                                    );

                                    // 將 UTC 時間轉換成台灣時區
                                    // 這只會轉換畫面顯示，不會修改資料庫
                                    $createdAtTaipeiObject = $createdAtUtc->setTimezone(
                                        'Asia/Taipei'
                                    );

                                    // 將時間物件整理成年月日時分秒
                                    $createdAtTaipei = $createdAtTaipeiObject->format(
                                        'Y-m-d H:i:s'
                                    );
                                }
                                ?>

                                <?php 
                                
                                //修改時間預設顯示空白
                                $updatedAtTaipei = '';

                                // e_date 存的是 UTC;顯示時轉換為台灣時間
                                if (! empty($client['e_date'])) {
                                    $updatedAtUtc = \CodeIgniter\I18n\Time::parse(
                                        $client['e_date'],
                                        'UTC'
                                    );

                                    $updatedAtTaipei = $updatedAtUtc
                                        ->setTimezone('Asia/Taipei')
                                        ->format('Y-m-d H:i:s');
                                }
                                ?>

                                <!-- 每一筆案主建立一個表格資料列 -->
                                <tr>
                                    <!-- 第一欄：案主編號 -->
                                    <td>
                                        <?= esc($client['id']) ?>
                                    </td>

                                    <!-- 第二欄：案主姓名 -->
                                    <td>
                                        <button
                                            type="button"
                                            class="client-name-photo-trigger js-photo-preview"
                                            data-photo-url="<?= esc(
                                                base_url('clients/' . $client['id'] . '/photo-preview'),
                                                'attr'
                                            ) ?>"
                                        >
                                            <?= esc($client['ct_name']) ?>

                                            <!-- 照片由 JavaScript 在滑過姓名時載入 -->
                                            <span class="client-photo-popover">
                                                <img alt="<?= esc($client['ct_name'], 'attr') ?>的照片" class="client-photo-image" hidden
                                            >
                                                <span class="client-photo-message">滑過姓名載入照片</span>
                                            </span>
                                        </button>
                                    </td>

                                    <!-- 第三欄：案主地址 -->
                                    <td>
                                        <?= esc($client['ct_addr']) ?>
                                    </td>

                                    <!-- 第四欄：路線編號 -->
                                    <td>
                                        <?= esc($client['route_no']) ?>
                                    </td>

                                    <!-- 第五欄：轉換完成的台灣建立時間 -->
                                    <td>
                                        <?= esc($createdAtTaipei) ?>
                                    </td>

                                    <!-- 第六欄：轉換完成的台灣更新時間 -->
                                    <td>
                                        <?= esc($updatedAtTaipei) ?>
                                    </td>

                                    <!-- 第七欄：操作按鈕 -->
                                    <td>
                                        <!-- action-buttons是按鈕排列容器 CSS會讓容器裡的按鈕水平並排 -->
                                        <div class="action-buttons">
                                            

                                            <!-- 只有按這個按鈕才會開啟詳細資料視窗 -->
                                            
                                            <!-- 開啟這位案主的詳細資料網址 -->
                                            <a
                                                class="btn btn-primary"
                                                href="<?= base_url('clients/' . $client['id'] . '/details') ?>"
                                            >
                                                詳細資料
                                            </a>
                                            
                                            <!--
                                                將案主 id 放進修改網址
                                                例如 id 是 2，網址就是 clients/edit/2
                                            -->
                                            <a
                                                class="btn btn-warning"
                                                href="<?= base_url(
                                                    'clients/edit/' . $client['id']
                                                ) ?>"
                                            >
                                                修改
                                            </a>
                                            <!-- 刪除會改變資料庫所以使用 POST 表單，不能使用一般 GET 超連結 -->
                                            <form 
                                                action="<?= base_url('clients/delete/' . $client['id']) ?>" 
                                                class="delete-from" 
                                                method="post" 
                                                onsubmit="return confirm('確定要刪除這位案主嗎?')">
    
                                            <!-- 產生 CSRF Token防止其他網站冒用使用者身分送出刪除請求 -->
                                            <?= csrf_field() ?>
    
                                            <!-- 送出刪除表單 -->
                                            <button type="submit" class="btn btn-danger">刪除</button> 
                                            </form>
                                        </div>
                                    </td>
                                </tr>

                            <?php endforeach; ?>
                        </tbody>
                        </table>
                        <!-- 每頁筆數選單與跳頁輸入框 -->
                        <div class="client-pagination-toolbar">
                            <form
                                action="<?= base_url('clients') ?>"
                                method="get"
                                class="client-pagination-form"
                                id="client-pagination-form"
                            >
                                <!-- 保留目前的搜尋關鍵字 -->
                                <input
                                    type="hidden"
                                    name="keyword"
                                    value="<?= esc($keyword ?? '', 'attr') ?>"
                                >

                                <label for="client-page-size">每頁顯示</label>

                                <!-- 選擇每頁顯示 10、20 或 50 筆 -->
                                <select id="client-page-size" name="perPage">
                                    <?php foreach ($pageSizeOptions as $option): ?>
                                        <option
                                            value="<?= esc((string) $option) ?>"
                                            <?= $pageSize === $option ? 'selected' : '' ?>
                                        >
                                            <?= esc((string) $option) ?> 筆
                                        </option>
                                    <?php endforeach; ?>
                                </select>

                                <label for="page_clients">跳到第</label>

                                <!-- page_clients 對應 paginate($pageSize, 'clients', 頁碼) -->
                                <input
                                    type="number"
                                    id="page_clients"
                                    name="page_clients"
                                    min="1"
                                    max="<?= esc((string) $pageCount) ?>"
                                    value="<?= esc((string) $currentPage) ?>"
                                    required
                                >

                                <span>頁，共 <?= esc((string) $pageCount) ?> 頁</span>

                                <button type="submit" class="client-page-submit">跳轉</button>
                            </form>
                        </div>

                        <!-- 套用自訂模板，只顯示數字頁碼 -->
                        <?= isset($pager) ? $pager->links('clients', 'client_numbers') : '' ?>

                        <script>
                            // 更改每頁筆數時，回到第 1 頁再重新載入
                            document
                                .getElementById('client-page-size')
                                .addEventListener('change', function () {
                                    const pageInput = this.form.querySelector('[name="page_clients"]');
                                    pageInput.value = '1';
                                    this.form.requestSubmit();
                                });
                        </script>
                    <?php else: ?>
                        <!-- $clients 沒有資料時顯示提示文字 -->
                        <p>目前沒有案主資料</p>
                    <?php endif; ?>
            </section>
        </main>
    </div>

<!-- 加上版本參數，讓瀏覽器載入最新的 JavaScript -->
<script src="<?= base_url('js/client-detail.js?v=3') ?>"></script>

<!-- 這段 JavaScript 專門計算上方固定區塊的高度，並把結果交給 CSS 使用 -->
<script>
    // 等 HTML 文件解析完成後再執行，確保上方區塊已經出現在網頁中
    document.addEventListener('DOMContentLoaded', function () {

        // 在頁面中尋找 class 為 client-sticky-top 的"元素"
        // 這個元素包住上方固定的麵包屑、標題、搜尋列等內容
        const stickyTop = document.querySelector('.client-sticky-top');

        // 如果目前頁面沒有這個元素，就結束程式
        // 這樣可以避免後續讀取不存在元素時發生錯誤
        if (!stickyTop) {
            return;
        }

        // 定義計算上方區塊高度的函式
        function updateStickyHeight() {

            // 取得元素目前實際顯示的高度，單位是像素
            const height = stickyTop.getBoundingClientRect().height;

            // 將高度寫到整份 HTML 文件的 CSS 自訂變數中
            // CSS 就能透過 var(--client-sticky-top-height) 讀取這個值
            document.documentElement.style.setProperty(
                '--client-sticky-top-height',

                // CSS 的高度需要包含單位，因此在數字後面加上 px
                height + 'px'
            );
        }

        // 頁面載入時先計算一次，設定表格表頭的起始位置
        updateStickyHeight();

        // 視窗尺寸改變時重新計算
        // 例如瀏覽器視窗變窄、上方內容換行後，高度可能會改變
        window.addEventListener('resize', updateStickyHeight);
    });
</script>
</body>
    
</html>