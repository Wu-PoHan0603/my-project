<?php
/**@var array $client */
/**@var array $profile */
/**@var array $attachments */
// 確保 Controller 傳入的資料是陣列
$client = is_array($client ?? null) ? $client : [];
$profile = is_array($profile ?? null) ? $profile : [];
$attachments = is_array($attachments ?? null) ? $attachments : [];

// 整理案主資料與個人資料表的欄位
$fields = [
    ['區域', 'region', $profile['region'] ?? null],
    ['案主姓名', 'ct_name', $client['ct_name'] ?? null],
    ['案主身分證', 'national_id', $profile['national_id'] ?? null],
    ['案主性別', 'sex', $profile['sex'] ?? null],
    ['案主生日', 'birthday', $profile['birthday'] ?? null],
    ['案主手機', 'mobile', $profile['mobile'] ?? null],
    ['案主家電', 'home_phone', $profile['home_phone'] ?? null],
    ['案主聯絡人-1', 'contact_person_1', $profile['contact_person_1'] ?? null],
    ['案主聯絡人-2', 'contact_person_2', $profile['contact_person_2'] ?? null],
    ['個管', 'case_manager', $profile['case_manager'] ?? null],
    ['照專', 'care_specialist', $profile['care_specialist'] ?? null],
    ['居服', 'home_service', $profile['home_service'] ?? null],
    ['案主戶籍地址', 'registered_address', $profile['registered_address'] ?? null],
    ['案主聯絡地址', 'ct_address', $client['ct_address'] ?? null],
    ['案主家經度', 'longitude', $profile['longitude'] ?? null],
    ['案主家緯度', 'latitude', $profile['latitude'] ?? null],
    ['打卡距離', 'checkin_distance', $profile['checkin_distance'] ?? null],
    ['餐盒單價', 'meal_box_price', $profile['meal_box_price'] ?? null],
    ['匯款尾碼', 'remittance_suffix', $profile['remittance_suffix'] ?? null],
    ['是否為僅 OT 個案', 'only_ot_case', $profile['only_ot_case'] ?? null],
    ['居住狀況', 'living_status', $profile['living_status'] ?? null],
    ['身分別', 'identity_type', $profile['identity_type'] ?? null],
    ['身心障礙', 'disability_status', $profile['disability_status'] ?? null],
    ['健康狀況', 'health_status', $profile['health_status'] ?? null],
    ['失能程度', 'disability_level', $profile['disability_level'] ?? null],
    ['疾病名稱', 'disease_names', $profile['disease_names'] ?? null],
    ['關懷分級', 'care_level', $profile['care_level'] ?? null],
    ['個案評估摘要', 'assessment_summary', $profile['assessment_summary'] ?? null],
    ['處遇計畫摘要', 'treatment_plan_summary', $profile['treatment_plan_summary'] ?? null],
    ['是否啟用', 'is_active', $profile['is_active'] ?? null],
];
?>

<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

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

            <dl class="client-detail-grid">
                <?php foreach ($fields as [$label, $key, $value]): ?>
                    <?php
                    // 空值顯示尚未填寫
                    if ($value === null || $value === '') {
                        $value = '尚未填寫';
                    }

                    // 將資料庫中的 1、0 顯示成是、否
                    if (
                        in_array($key, ['only_ot_case', 'is_active'], true)
                        && $value !== '尚未填寫'
                    ) {
                        $value = (string) $value === '1' ? '是' : '否';
                    }
                    ?>

                    <div class="client-detail-item">
                        <dt><?= esc($label) ?></dt>
                        <dd><?= esc((string) $value) ?></dd>
                    </div>
                <?php endforeach; ?>
            </dl>

            <!-- 顯示附件資料 -->
            <h2>照片與文件</h2>

            <?php if (! empty($attachments)): ?>
                <ul class="client-attachment-list">
                    <?php foreach ($attachments as $attachment): ?>
                        <li>
                            <?= esc($attachment['original_name'] ?? '未命名檔案') ?>
                            <?php if (! empty($attachment['category'])): ?>
                                （<?= esc($attachment['category']) ?>）
                            <?php endif; ?>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php else: ?>
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