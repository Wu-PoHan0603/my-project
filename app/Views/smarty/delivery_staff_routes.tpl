<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="{$cssUrl}">
    <title>送餐員路線指派</title>
</head>
<body>
    <div class="layout">
        <aside class="sidebar">
            <h2>案主管理系統</h2>

            <nav aria-label="主要導覽">
                <a href="{$homeUrl}">首頁</a>

                <details class="sidebar-menu-group">
                    <summary>案主管理</summary>
                    <div class="sidebar-submenu">
                        <a href="{$clientListUrl}">案主資料</a>
                    </div>
                </details>

                <details class="sidebar-menu-group" open>
                    <summary>送餐員管理</summary>
                    <div class="sidebar-submenu">
                        <a href="{$staffListUrl}">送餐員資料</a>
                    </div>
                </details>

                <a href="{$deliveryRoutesUrl}">路線指派</a>
                <a href="{$trashUrl}">資源回收桶</a>
            </nav>
        </aside>

        <main class="content">
            <nav class="page-breadcrumb" aria-label="麵包屑導覽">
                <a href="{$homeUrl}">首頁</a>
                <span class="page-breadcrumb-separator" aria-hidden="true">&gt;</span>
                <a href="{$staffListUrl}">送餐員資料</a>
                <span class="page-breadcrumb-separator" aria-hidden="true">&gt;</span>
                <span aria-current="page">路線指派</span>
            </nav>

            <h1>送餐員路線指派</h1>
            <h2>{$staff.staff_name|escape:'html'} 負責的路線</h2>

            {if $successMessage}
                <p class="delivery-message-success">
                    {$successMessage|escape:'html'}
                </p>
            {/if}

            {if $errorMessage}
                <p class="delivery-message-error">
                    {$errorMessage|escape:'html'}
                </p>
            {/if}

            <section class="delivery-staff-card">
                <form
                    action="{$staffListUrl}/{$staff.id}/routes"
                    method="post"
                >
                    {$csrfField nofilter}

                    {if !empty($routes)}
                        <div class="delivery-route-list">
                            {foreach $routes as $route}
                                <div class="delivery-route-item">
                                    <label>
                                        <input
                                            type="checkbox"
                                            name="route_ids[]"
                                            value="{$route.id}"
                                            {if $route.delivery_staff_id == $staff.id}checked{/if}
                                        >
                                        路線 {$route.route_no|escape:'html'}
                                    </label>

                                    {if $route.delivery_staff_id == $staff.id}
                                        <a
                                            class="btn btn-primary"
                                            href="{$deliveryRoutesUrl}/{$route.id}/clients"
                                        >
                                            查看案主與排序
                                        </a>
                                    {/if}
                                </div>
                            {/foreach}
                        </div>
                    {else}
                        <p>目前沒有可指派的路線。</p>
                    {/if}

                    <div class="client-detail-form-actions">
                        <button type="submit" class="btn btn-primary">
                            儲存路線
                        </button>

                        <a href="{$staffListUrl}" class="btn btn-secondary">
                            返回送餐員列表
                        </a>
                    </div>
                </form>
            </section>
        </main>
    </div>
</body>
</html>