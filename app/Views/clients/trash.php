<!DOCTYPE html>
<html lang="zh-TW">

<head>
    <meta charset="UTF-8">

    <!-- 瀏覽器分頁標籤 -->
    <title>案主資源回收桶</title>

    <!-- 載入 public/css/style.css -->
    <link rel="stylesheet" href="<?= base_url('css/style.css') ?>">
</head>

<body>
    <div class="layout">
        <!-- 左側選單 -->
        <aside class="sidebar">
            <h2>案主管理系統</h2>

            <nav>
                <!-- #首頁尚未使用 -->
                <a href="#">首頁</a>
                
                <!-- 前往一般案主列表 -->
                <a href="<?= base_url('clients') ?>">案主資料</a>

                <!-- 前往新增案主資料 -->
                <a href="<?= base_url('clients/create') ?>">新增案主</a>

                <!-- 前往資源回收桶 -->
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

            <!-- 頁面標題 -->
            <h1>資源回收桶</h1>

            <!-- 判斷是否有還原成功訊息 -->
            <?php if (session()->getFlashdata('success')): ?>

                <!-- esc()防止訊息被當成HTML執行 -->
                <p class="message-success">
                    <?= esc(session()->getFlashdata('success')) ?>
                </p>
            <?php endif ?>

            <!-- 判斷是否有錯誤訊息 -->
            <?php if (session()->getFlashdata('error')): ?>

                <!-- 顯示一次性的錯誤訊息 -->
                <p class="message-error">
                    <?= esc(session()->getFlashdata('error')) ?>
                </p>
            <?php endif ?>

            <!-- 說明軟刪除資料仍然保留在資料庫 -->
            <p>
                這裡顯示已軟刪除的案主資料
            </p>

            <!-- 判斷資源回收桶是否有資料 -->
            <?php if (!empty($deletedClients)): ?>

                <!-- 已刪除案主資料表 -->
                <table class="client-table">
                    <thead>
                        <tr>
                            <th>編號</th>
                            <th>案主姓名</th>
                            <th>案主地址</th>
                            <th>路線編號</th>
                            <th>刪除時間</th>
                            <th>操作</th>
                        </tr>
                    </thead>

                    <tbody>
                        <!-- foreach 每次取得一筆已刪除資料 並放入 $client -->
                        <?php foreach ($deletedClients as $client): ?>

                            <?php 
                            //預設刪除時間為空字串
                            $deletedAtTaipei = '';

                            //有刪除時間才進行時區轉換
                            if (!empty($client['deleted_at'])) {
                                //將資料庫時間解讀成UTC
                                $deletedAtUtc = 
                                \CodeIgniter\I18n\Time::parse($client['deleted_at'], 'UTC');

                                //將UTC轉換成台灣時間
                                $deletedAtTaipeiObject = 
                                $deletedAtUtc->setTimezone('Asia/Taipei');

                                //整理成畫面需要的格式
                                $deletedAtTaipei = 
                                $deletedAtTaipeiObject->format('Y-m-d H:i:s');
                            }
                            ?>

                            <!-- 顯示一筆已刪除的案主 -->
                            <tr>
                                <!-- 案主編號 -->
                                <td><?= esc($client['id']) ?></td>

                                <!-- 案主姓名 -->
                                <td><?= esc($client['ct_name']) ?></td>

                                <!-- 案主地址 -->
                                <td><?= esc($client['ct_address']) ?></td>

                                <!-- 路線編號 -->
                                <td><?= esc($client['route_no']) ?></td>

                                <!-- 台灣時間的刪除時間 -->
                                <td><?= esc($deletedAtTaipei) ?></td>

                                <!-- 還原操作 -->
                                <td>
                                    <!-- 還原會修改資料庫 因此使用POST表單，不能使用普通GET連結 -->
                                    <form action="<?= base_url('clients/restore/' . $client['id']) ?>" class="action-form" method="post" onsubmit="return confirm('確定要還原這位案主嗎?')">
                                        <!-- 產生csrf Token 訪指其他網站偽造還原請求 -->
                                        <?= csrf_field() ?>

                                        <!-- 送出還原請求 -->
                                        <button type="submit" class="btn btn-success">還原</button>
                                    </form>

                                    <!-- 永久刪除表單 -->
                                    <form action="<?= base_url('clients/force-delete/' . $client['id']) ?>" class="action-form" method="post" onsubmit="return confirm ('確定要永久刪除這位案主嗎?永久刪除後無法還原!')">

                                        <!-- 永久刪除請求的CSRF Token -->
                                        <?= csrf_field() ?>

                                        <!-- 送出永久刪除請求 -->
                                        <button type="submit" class="btn btn-danger">永久刪除</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach ?>
                    </tbody>
                </table>
            
            <?php else: ?>

                <!-- 沒有任何軟刪除資料時顯示 -->
                <p>資源回收桶目前沒有資料。</p>

            <?php endif ?>
        </main>
    </div>
</body>

</html>