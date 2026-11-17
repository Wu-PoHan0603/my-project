{* 路線指派頁：由 Smarty 顯示路線和送餐員資料 *}
<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    {* 載入專案共用 CSS *}
    <link rel="stylesheet" href="{$cssUrl}">

    <title>路線指派</title>
</head>

<body>
    {* 整頁容器：排列側邊欄與主要內容 *}
    <div class="layout">

        {* 左側系統導覽欄 *}
        <aside class="sidebar">
            <h2>案主管理系統</h2>

            {* 系統主要導覽選單 *}
            <nav aria-label="主要導覽">
                {* 前往首頁 *}
                <a href="{$homeUrl}">首頁</a>

                {* 案主管理選單 *}
                <details class="sidebar-menu-group">
                    <summary>案主管理</summary>

                    <div class="sidebar-submenu">
                        <a href="{$clientListUrl}">案主資料</a>
                    </div>
                </details>

                {* 送餐員管理選單；目前頁面預設展開 *}
                <details class="sidebar-menu-group" open>
                    <summary>送餐員管理</summary>

                    <div class="sidebar-submenu">
                        <a href="{$staffListUrl}">送餐員資料</a>
                    </div>
                </details>

                {* 前往資源回收桶 *}
                <a href="{$trashUrl}">資源回收桶</a>
            </nav>

            {* 側欄底部的個人設定區 *}
            <div class="sidebar-footer">
                <details class="sidebar-settings">
                    <summary class="sidebar-settings-trigger">
                        個人設定
                    </summary>

                    <div class="sidebar-settings-panel">
                        {* 顯示登入者資訊 *}
                        <div class="sidebar-account-info">
                            <p class="sidebar-account-row">
                                <span class="sidebar-account-label">登入帳號</span>
                                <strong class="sidebar-account-value">
                                    {$currentUsername}
                                </strong>
                            </p>

                            <p class="sidebar-account-row">
                                <span class="sidebar-account-label">顯示名稱</span>
                                <strong class="sidebar-account-value">
                                    {$currentDisplayName}
                                </strong>
                            </p>

                            <p class="sidebar-account-row">
                                <span class="sidebar-account-label">角色</span>
                                <strong class="sidebar-account-value">
                                    {$currentRoleLabel}
                                </strong>
                            </p>
                        </div>

                        {* 前往修改密碼頁 *}
                        <a
                            href="{$changePasswordUrl}"
                            class="sidebar-settings-link"
                        >
                            更新密碼
                        </a>

                        {* 登出使用 POST 表單 *}
                        <form
                            action="{$logoutUrl}"
                            method="post"
                            class="sidebar-logout-form"
                        >
                            {* CSRF 欄位是 HTML，使用 nofilter 輸出 *}
                            {$logoutCsrfField nofilter}

                            <button
                                type="submit"
                                class="sidebar-logout-button"
                            >
                                登出
                            </button>
                        </form>
                    </div>
                </details>
            </div>
        </aside>

        {* 右側主要內容 *}
        <main class="content">

            {* 路線指派頁麵包屑 *}
            <nav class="page-breadcrumb" aria-label="麵包屑導覽">
                <a href="{$homeUrl}">首頁</a>

                <span
                    class="page-breadcrumb-separator"
                    aria-hidden="true"
                >&gt;</span>

                <span>送餐員管理</span>

                <span
                    class="page-breadcrumb-separator"
                    aria-hidden="true"
                >&gt;</span>

                <span aria-current="page">路線指派</span>
            </nav>

            {* 頁面標題 *}
            <h1>路線指派</h1>

            {* 顯示指派成功訊息 *}
            {if $successMessage}
                <p class="message-success">
                    {$successMessage}
                </p>
            {/if}

            {* 顯示指派錯誤訊息 *}
            {if $errorMessage}
                <p class="message-error">
                    {$errorMessage}
                </p>
            {/if}

            {* 路線指派資料區 *}
            <section class="delivery-staff-card">
                <h2>路線與送餐員指派</h2>

                {* 有路線資料時顯示表格 *}
                {if !empty($routes)}
                    <table class="delivery-staff-table">
                        <thead>
                            <tr>
                                <th>路線編號</th>
                                <th>目前指派送餐員</th>
                                <th>操作</th>
                            </tr>
                        </thead>

                        <tbody>
                            {* 逐筆顯示路線資料 *}
                            {foreach $routes as $route}
                                <tr>
                                    {* 顯示路線編號 *}
                                    <td>{$route.route_no}</td>

                                    {* 顯示已指派送餐員；沒有指派時顯示提示 *}
                                    <td>
                                        {$route.staff_name|default:'尚未指派'}
                                    </td>

                                    <td>
                                        {* 僅管理員與工作人員能新增或取消指派 *}
                                        {if $canManageRoutes}
                                            <form
                                                action="{$deliveryRoutesUrl}/assign/{$route.id}"
                                                method="post"
                                                class="action-buttons"
                                            >
                                                {* 輸出 CI4 產生的 CSRF 欄位 *}
                                                {$csrfField nofilter}

                                                <select
                                                    name="delivery_staff_id"
                                                    aria-label="選擇送餐員"
                                                >
                                                    {* 空值代表取消目前指派 *}
                                                    <option value="">
                                                        尚未指派
                                                    </option>

                                                    {* 列出啟用中的送餐員 *}
                                                    {foreach $staffList as $staff}
                                                        <option
                                                            value="{$staff.id}"
                                                            {if $route.delivery_staff_id == $staff.id}
                                                                selected
                                                            {/if}
                                                        >
                                                            {$staff.staff_name}
                                                        </option>
                                                    {/foreach}
                                                </select>

                                                <button
                                                    type="submit"
                                                    class="btn btn-primary"
                                                >
                                                    儲存指派
                                                </button>
                                            </form>
                                        {else}
                                            <span>唯讀</span>
                                        {/if}
                                    </td>
                                </tr>
                            {/foreach}
                        </tbody>
                    </table>
                {else}
                    {* 沒有路線資料時顯示提示 *}
                    <p>目前沒有路線資料。</p>
                {/if}
            </section>
        </main>
    </div>
</body>
</html>