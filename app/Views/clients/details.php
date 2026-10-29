<?php
/**@var array $client */
/**@var array $profile */
/**@var array $attachments */
// 確保 Controller 傳入的資料是陣列
$client = is_array($client ?? null) ? $client : [];
$profile = is_array($profile ?? null) ? $profile : [];
$attachments = is_array($attachments ?? null) ? $attachments : [];

// 設定案主詳細資料表的欄位名稱、標籤和輸入方式
$profileFields = [
    ['name' => 'region', 'label' => '區域', 'type' => 'text'],
    ['name' => 'national_id', 'label' => '案主身分證', 'type' => 'text'],
    ['name' => 'sex', 'label' => '案主性別', 'type' => 'text'],
    ['name' => 'birthday', 'label' => '案主生日', 'type' => 'date'],
    ['name' => 'mobile', 'label' => '案主手機', 'type' => 'tel'],
    ['name' => 'home_phone', 'label' => '案主家電', 'type' => 'tel'],
    ['name' => 'contact_person_1', 'label' => '案主聯絡人-1', 'type' => 'text'],
    ['name' => 'contact_person_2', 'label' => '案主聯絡人-2', 'type' => 'text'],
    ['name' => 'case_manager', 'label' => '個管', 'type' => 'text'],
    ['name' => 'care_specialist', 'label' => '照專', 'type' => 'text'],
    ['name' => 'home_service', 'label' => '居服', 'type' => 'text'],
    ['name' => 'registered_address', 'label' => '案主戶籍地址', 'type' => 'text', 'wide' => true],
    ['name' => 'longitude', 'label' => '案主家經度', 'type' => 'number'],
    ['name' => 'latitude', 'label' => '案主家緯度', 'type' => 'number'],
    ['name' => 'checkin_distance', 'label' => '打卡距離', 'type' => 'number'],
    ['name' => 'meal_box_price', 'label' => '餐盒單價', 'type' => 'number'],
    ['name' => 'remittance_suffix', 'label' => '匯款尾碼', 'type' => 'text'],
    [
        'name' => 'only_ot_case',
        'label' => '是否為僅 OT 個案',
        'type' => 'select',
        'options' => ['0' => '否', '1' => '是'],
    ],
    ['name' => 'living_status', 'label' => '居住狀況', 'type' => 'text'],
    ['name' => 'identity_type', 'label' => '身分別', 'type' => 'text'],
    ['name' => 'disability_status', 'label' => '身心障礙', 'type' => 'text'],
    ['name' => 'health_status', 'label' => '健康狀況', 'type' => 'textarea', 'wide' => true],
    ['name' => 'disability_level', 'label' => '失能程度', 'type' => 'text'],
    ['name' => 'disease_names', 'label' => '疾病名稱', 'type' => 'textarea', 'wide' => true],
    ['name' => 'care_level', 'label' => '關懷分級', 'type' => 'text'],
    ['name' => 'assessment_summary', 'label' => '個案評估摘要', 'type' => 'textarea', 'wide' => true],
    ['name' => 'treatment_plan_summary', 'label' => '處遇計畫摘要', 'type' => 'textarea', 'wide' => true],
    [
        'name' => 'is_active',
        'label' => '是否啟用',
        'type' => 'select',
        'options' => ['1' => '啟用', '0' => '停用'],
    ],
];
?>

<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="UTF-8">

    <!-- 載入專案共用樣式 -->
    <link rel="stylesheet" href="<?= base_url('css/style.css') ?>">

    <title>案主詳細資料</title>
</head>

<body>
    <main class="client-detail-page">

        <!-- 返回案主列表 -->
        <a href="<?= base_url('clients') ?>" class="btn btn-secondary">
            返回案主列表
        </a>

        <!-- 案主詳細資料頁的麵包屑導覽 -->
        <nav class="page-breadcrumb" aria-label="麵包屑導覽">

            <!-- 點擊後回首頁 -->
            <a href="<?= base_url('/') ?>">首頁</a>

            <!-- 路徑分隔符 -->
            <span class="page-breadcrumb-separator" aria-hidden="true">&gt;</span>

            <!-- 點擊後回到案主列表 -->
            <span>案主管理</span>
            
            <!-- 路徑分隔符 -->
            <span class="page-breadcrumb-separator" aria-hidden="true">&gt;</span>

            <!-- 點擊後回到案主列表 -->
            <a href="<?= base_url('/clients') ?>">案主資料</a>

            <!-- 路徑分隔符 -->
            <span class="page-breadcrumb-separator" aria-hidden="true">&gt;</span>

            <!-- 顯示目前案主姓名；若沒有姓名就顯示預設文字 -->
            <span aria-current="page">
                <?= esc($client['ct_name'] ?? '案主詳細資料') ?>
            </span>

        </nav>

        <!-- 顯示案主姓名 -->
        <h1>
            案主詳細資料：<?= esc($client['ct_name'] ?? '未命名案主') ?>
        </h1>

        <!-- 六個資料分頁 -->
        <nav class="client-detail-tabs" role="tablist">
            <button type="button" role="tab"
                    data-detail-tab="detail-basic" aria-selected="true">
                案主資料
            </button>
            <button type="button" role="tab"
                    data-detail-tab="detail-history" aria-selected="false">
                歷程資料
            </button>
            <button type="button" role="tab"
                    data-detail-tab="detail-identity" aria-selected="false">
                身分別資料
            </button>
            <button type="button" role="tab"
                    data-detail-tab="detail-opening" aria-selected="false">
                開案資料
            </button>
            <button type="button" role="tab"
                    data-detail-tab="detail-route" aria-selected="false">
                路徑資料
            </button>
            <button type="button" role="tab"
                    data-detail-tab="detail-care" aria-selected="false">
                關懷紀錄
            </button>
        </nav>

        <!-- 案主資料分頁 -->
        <section id="detail-basic" class="client-detail-panel" role="tabpanel">
            <h2>案主資料</h2>

            <?php $successMessage = session()->getFlashdata('success'); ?>
            <?php if ($successMessage): ?>
                <!-- 顯示儲存成功訊息 -->
                <p class="detail-alert detail-alert-success">
                    <?= esc($successMessage) ?>
                </p>
            <?php endif; ?>

            <?php $errorMessage = session()->getFlashdata('error'); ?>
            <?php if ($errorMessage): ?>
                <!-- 顯示資料庫儲存失敗訊息 -->
                <p class="detail-alert detail-alert-error">
                    <?= esc($errorMessage) ?>
                </p>
            <?php endif; ?>

            <?php $errors = session()->getFlashdata('errors') ?? []; ?>
            <?php if (! empty($errors)): ?>
                <!-- 列出欄位驗證錯誤 -->
                <div class="detail-alert detail-alert-error">
                    <p>請檢查以下欄位：</p>
                    <ul>
                        <?php foreach ($errors as $message): ?>
                            <li><?= esc($message) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <!-- 表單以 POST 送到剛加入的案主詳細資料儲存路由 -->
            <form
                action="<?= base_url('clients/' . $client['id'] . '/details') ?>"
                method="post"
                class="client-detail-form"
            >
                <!-- CSRF Token 用來驗證表單是從本站送出 -->
                <?= csrf_field() ?>

                <div class="client-detail-form-grid">

                    <!-- 案主姓名儲存在 clients 資料表 -->
                    <div class="client-detail-form-field">
                        <label for="ct_name">案主姓名</label>
                        <input
                            type="text"
                            id="ct_name"
                            name="ct_name"
                            value="<?= esc(old('ct_name', $client['ct_name'] ?? ''), 'attr') ?>"
                            required
                            maxlength="100"
                        >
                    </div>

                    <!-- 聯絡地址在資料庫的欄位名稱是 ct_addr -->
                    <div class="client-detail-form-field is-wide">
                        <label for="ct_addr">案主聯絡地址</label>
                        <input
                            type="text"
                            id="ct_addr"
                            name="ct_addr"
                            value="<?= esc(old('ct_addr', $client['ct_addr'] ?? ''), 'attr') ?>"
                            required
                            maxlength="255"
                        >
                    </div>

                    <!-- 路線編號儲存在clients資料表 -->
                    <div class="client-detail-form-field">
                        <label for="route_no">路線編號</label>
                        <input 
                            type="number"
                            id="route_no"
                            name="route_no"
                            value="<?= esc(old('route_no', $client['route_no'] ?? '', 'attr')) ?>"
                            require
                            step="1"
                        >
                    </div>

                    <?php foreach ($profileFields as $field): ?>
                        <?php
                        // 取得欄位名稱，用它讀取個人資料表中的原始值
                        $fieldName = $field['name'];

                        // 新案主尚無詳細資料時，布林欄位使用資料表預設值
                        $defaultValue = '';

                        if ($fieldName === 'only_ot_case') {
                            $defaultValue = '0';
                        } elseif ($fieldName === 'is_active') {
                            $defaultValue = '1';
                        }

                        // 驗證失敗回到本頁時，優先顯示剛才輸入的內容
                        $fieldValue = old(
                            $fieldName,
                            $profile[$fieldName] ?? $defaultValue
                        );

                        // 長文字欄位使用較寬的版面
                        $wideClass = ! empty($field['wide']) ? 'is-wide' : '';
                        ?>

                        <div class="client-detail-form-field <?= esc($wideClass, 'attr') ?>">
                            <label for="profile-<?= esc($fieldName, 'attr') ?>">
                                <?= esc($field['label']) ?>
                            </label>

                            <?php if ($field['type'] === 'textarea'): ?>
                                <!-- textarea 用來輸入多行文字 -->
                                <textarea
                                    id="profile-<?= esc($fieldName, 'attr') ?>"
                                    name="<?= esc($fieldName, 'attr') ?>"
                                    rows="4"
                                ><?= esc((string) $fieldValue) ?></textarea>

                            <?php elseif ($field['type'] === 'select'): ?>
                                <!-- 下拉選單用來選擇是/否或啟用狀態 -->
                                <select
                                    id="profile-<?= esc($fieldName, 'attr') ?>"
                                    name="<?= esc($fieldName, 'attr') ?>"
                                    required
                                >
                                    <?php foreach ($field['options'] as $optionValue => $optionLabel): ?>
                                        <option
                                            value="<?= esc($optionValue, 'attr') ?>"
                                            <?= (string) $fieldValue === (string) $optionValue ? 'selected' : '' ?>
                                        >
                                            <?= esc($optionLabel) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>

                            <?php else: ?>
                                <!-- 一般欄位依設定使用文字、日期、電話或數字輸入框 -->
                                <input
                                    type="<?= esc($field['type'], 'attr') ?>"
                                    id="profile-<?= esc($fieldName, 'attr') ?>"
                                    name="<?= esc($fieldName, 'attr') ?>"
                                    value="<?= esc((string) $fieldValue, 'attr') ?>"
                                    <?= $field['type'] === 'number' ? 'step="any"' : '' ?>
                                >
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>

                </div>

                <!-- 按下按鈕後送出表單，Controller 會驗證並儲存 -->
                <div class="client-detail-form-actions">
                    <button type="submit" class="btn btn-primary">
                        儲存案主資料
                    </button>

                    <a href="<?= base_url('clients') ?>" class="btn btn-secondary">
                        返回案主列表
                    </a>
                </div>
            </form>

            <!-- 案主人像上傳區 -->
            <h2>上傳人物照片</h2>

            <!-- multipart/form-data 才能讓表單傳送檔案 -->
            <form
                action="<?= esc(base_url('clients/' . (int) $client['id'] . '/attachments'), 'attr') ?>"
                method="post"
                enctype="multipart/form-data"
                class="client-attachment-upload-form"
            >
                <!-- 加入 CSRF 欄位，讓 CI4 驗證表單來源 -->
                <?= csrf_field() ?>

                <!-- label 的 for 要對應檔案欄位的 id -->
                <label for="client-photo">選擇人物照片</label>

                <!-- accept 限制檔案選擇視窗優先顯示圖片格式 -->
                <input
                    type="file"
                    id="client-photo"
                    name="attachment"
                    accept="image/jpeg,image/png,image/webp,image/gif"
                    required
                >

                <!-- 送出後由 uploadAttachment() 再做後端檢查 -->
                <button type="submit" class="btn btn-primary">上傳照片</button>
            </form>


            <!-- 顯示附件資料 -->
            <h2>人物照片</h2>

            <?php if (! empty($attachments)): ?>
                <?php
                // 將資料庫中的分類代碼轉成畫面顯示的中文
                $categoryLabels = [
                    'family_tree' => '家系圖',
                    'resource_map' => '資源生態圖',
                    'client_photo' => '案主大頭照',
                    'other' => '其他',
                ];
                ?>

                <!-- 列出這位案主已上傳的附件 -->
                <ul class="client-attachment-list">
                    <?php foreach ($attachments as $attachment): ?>
                        <?php
                        // 取得附件編號，供預覽與刪除網址使用
                        $attachmentId = (int) ($attachment['id'] ?? 0);

                        // 取得原始檔名，僅在畫面顯示
                        $originalName = $attachment['original_name'] ?? '未命名檔案';

                        // 取得分類代碼；沒有分類時使用空字串
                        $category = (string) ($attachment['category'] ?? '');

                        // 將分類代碼轉成中文；遇到未知分類時顯示原代碼
                        $categoryLabel = $categoryLabels[$category] ?? $category;
                        ?>

                        <!-- 每個附件各自顯示名稱、分類與操作按鈕 -->
                        <li class="client-attachment-item">
                            <span>
                                <!-- 使用 esc() 避免檔名被當成 HTML 執行 -->
                                <?= esc($originalName) ?>

                                <!-- 分類有內容時才顯示分類文字 -->
                                <?php if ($categoryLabel !== ''): ?>
                                    （<?= esc($categoryLabel) ?>）
                                <?php endif; ?>
                            </span>

                            <!-- 附件的預覽與刪除操作 -->
                            <div class="action-buttons">
                                <!-- 透過登入保護的 Controller 路由開啟圖片或 PDF -->
                                <a
                                    class="btn btn-primary"
                                    href="<?= esc(
                                        base_url(
                                            'clients/'
                                            . (int) $client['id']
                                            . '/attachments/'
                                            . $attachmentId
                                        ),
                                        'attr'
                                    ) ?>"
                                    target="_blank"
                                    rel="noopener"
                                >
                                    預覽／開啟
                                </a>

                                <!-- 永久刪除使用 POST 表單，不用 GET 連結 -->
                                <form
                                    action="<?= esc(
                                        base_url(
                                            'clients/'
                                            . (int) $client['id']
                                            . '/attachments/'
                                            . $attachmentId
                                            . '/delete'
                                        ),
                                        'attr'
                                    ) ?>"
                                    method="post"
                                    class="delete-from"
                                    onsubmit="return confirm('確定要永久刪除這個附件嗎？')"
                                >
                                    <!-- 加入 CSRF Token，供網站的 CSRF Filter 驗證 -->
                                    <?= csrf_field() ?>

                                    <!-- 送出刪除請求；Controller 會永久刪除資料記錄與檔案 -->
                                    <button type="submit" class="btn btn-danger">
                                        永久刪除
                                    </button>
                                </form>
                            </div>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php else: ?>
                <!-- 沒有附件時顯示提示 -->
                <p>目前沒有附件。</p>
            <?php endif; ?>
        </section>

        <!-- 其他分頁先保留位置 -->
        <section id="detail-history" class="client-detail-panel" role="tabpanel" hidden>
            <h2>歷程資料</h2>
            <p>目前尚無資料可顯示。</p>
        </section>

        <section id="detail-identity" class="client-detail-panel" role="tabpanel" hidden>
            <h2>身分別資料</h2>
            <p>目前尚無資料可顯示。</p>
        </section>

        <section id="detail-opening" class="client-detail-panel" role="tabpanel" hidden>
            <h2>開案資料</h2>
            <p>目前尚無資料可顯示。</p>
        </section>

        <section id="detail-route" class="client-detail-panel" role="tabpanel" hidden>
            <h2>路徑資料</h2>
            <p>目前尚無資料可顯示。</p>
        </section>

        <section id="detail-care" class="client-detail-panel" role="tabpanel" hidden>
            <h2>關懷紀錄</h2>
            <p>目前尚無資料可顯示。</p>
        </section>
    </main>

    <script>
        // 點選分頁時，只顯示對應的資料區塊
        document.querySelectorAll('[data-detail-tab]').forEach((button) => {
            button.addEventListener('click', () => {
                const selectedPanelId = button.dataset.detailTab;

                // 顯示目前選取的分頁，隱藏其他分頁
                document.querySelectorAll('.client-detail-panel').forEach((panel) => {
                    panel.hidden = panel.id !== selectedPanelId;
                });

                // 更新分頁按鈕的選取狀態
                document.querySelectorAll('[data-detail-tab]').forEach((tabButton) => {
                    tabButton.setAttribute(
                        'aria-selected',
                        String(tabButton === button)
                    );
                });
            });
        });
    </script>
</body>
</html>