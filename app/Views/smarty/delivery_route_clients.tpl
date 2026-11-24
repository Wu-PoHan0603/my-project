<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    {* 專案共用樣式 *}
    <link rel="stylesheet" href="{$cssUrl}">

    {* Leaflet 地圖樣式 *}
    <link
        rel="stylesheet"
        href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
    >

    <title>路線案主與送餐順序</title>
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

                <a href="{$deliveryRoutesUrl}">路線指派</a>

                <span class="page-breadcrumb-separator" aria-hidden="true">&gt;</span>

                <span aria-current="page">
                    路線 {$route.route_no|escape:'html'} 案主排序
                </span>
            </nav>

            <h1>路線 {$route.route_no|escape:'html'} 案主與送餐順序</h1>

            {* 地圖顯示區 *}
            <div
                id="client-map"
                class="delivery-route-map"
                role="region"
                aria-label="案主位置與送餐順序地圖"
            ></div>

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
                <p>可拖曳案主列調整送餐順序；地圖錨點會依序編號。</p>

                {if !empty($clients)}
                    <form action="{$routeOrderUrl}" method="post">
                        {$csrfField nofilter}

                        <table class="delivery-staff-table">
                            <thead>
                                <tr>
                                    <th>順序</th>
                                    <th>案主姓名</th>
                                    <th>案主地址</th>
                                    <th>拖曳</th>
                                </tr>
                            </thead>

                            <tbody id="client-sort-list">
                                {foreach $clients as $client}
                                    <tr class="sortable-client-row" draggable="true">
                                        <td class="order-number"></td>
                                        <td>
                                            {$client.ct_name|escape:'html'}
                                            <input
                                                type="hidden"
                                                name="client_ids[]"
                                                value="{$client.id}"
                                            >
                                        </td>
                                        <td>{$client.ct_addr|escape:'html'}</td>
                                        <td class="drag-handle">☰ 拖曳排序</td>
                                    </tr>
                                {/foreach}
                            </tbody>
                        </table>

                        <div class="client-detail-form-actions">
                            <button type="submit" class="btn btn-primary">
                                儲存送餐順序
                            </button>

                            <a
                                href="javascript:history.back()"
                                class="btn btn-secondary"
                            >
                                返回路線
                            </a>
                        </div>
                    </form>
                {else}
                    <p>這條路線目前沒有案主資料。</p>
                    <a
                        href="javascript:history.back()"
                        class="btn btn-secondary"
                    >
                        返回路線
                    </a>
                {/if}
            </section>
        </main>
    </div>

    {* Leaflet 地圖程式庫 *}
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

    {* 拖曳案主資料列，並更新顯示順序 *}
    <script>
        const clientList = document.getElementById('client-sort-list');

        if (clientList) {
            let draggedRow = null;

            function updateOrderNumbers() {
                clientList
                    .querySelectorAll('.sortable-client-row')
                    .forEach((row, index) => {
                        row.querySelector('.order-number').textContent = index + 1;
                    });
            }

            clientList.addEventListener('dragstart', (event) => {
                const row = event.target.closest('.sortable-client-row');

                if (!row) {
                    return;
                }

                draggedRow = row;
                row.classList.add('dragging');
                event.dataTransfer.effectAllowed = 'move';
            });

            clientList.addEventListener('dragover', (event) => {
                event.preventDefault();

                const targetRow = event.target.closest('.sortable-client-row');

                if (!draggedRow || !targetRow || draggedRow === targetRow) {
                    return;
                }

                const bounds = targetRow.getBoundingClientRect();
                const insertAfter = event.clientY > bounds.top + bounds.height / 2;

                if (insertAfter) {
                    targetRow.after(draggedRow);
                } else {
                    targetRow.before(draggedRow);
                }

                updateOrderNumbers();
            });

            clientList.addEventListener('dragend', () => {
                if (draggedRow) {
                    draggedRow.classList.remove('dragging');
                    draggedRow = null;
                }

                updateOrderNumbers();
            });

            updateOrderNumbers();
        }
    </script>

    {* 顯示案主錨點和順序連線 *}
    <script>
        const mapClients = {$mapClientsJson nofilter};
        const mapElement = document.getElementById('client-map');

        if (mapElement && typeof L !== 'undefined') {
            const map = L.map('client-map').setView([23.6978, 120.9605], 8);

            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '&copy; OpenStreetMap contributors'
            }).addTo(map);

            const clientsWithCoordinates = Array.isArray(mapClients)
                ? mapClients
                    .filter((client) =>
                        Number.isFinite(Number(client.latitude))
                        && Number.isFinite(Number(client.longitude))
                    )
                    .sort((a, b) => Number(a.order) - Number(b.order))
                : [];

            const routePoints = [];

            clientsWithCoordinates.forEach((client, index) => {
                const latitude = Number(client.latitude);
                const longitude = Number(client.longitude);
                const point = [latitude, longitude];
                const markerNumber = Number(client.order) > 0
                    ? Number(client.order)
                    : index + 1;

                routePoints.push(point);

                const markerIcon = L.divIcon({
                    className: 'client-map-marker',
                    html: '<span>' + markerNumber + '</span>',
                    iconSize: [30, 30],
                    iconAnchor: [15, 15]
                });

                const marker = L.marker(point, { icon: markerIcon }).addTo(map);

                const popup = document.createElement('div');
                popup.textContent =
                    markerNumber + '. ' + client.name + '｜' + client.address;

                marker.bindPopup(popup);
            });

            if (routePoints.length > 0) {
                // 這是連接座標的直線示意，尚非實際道路導航路線
                L.polyline(routePoints, {
                    color: '#1976d2',
                    weight: 4,
                    opacity: 0.8
                }).addTo(map);

                map.fitBounds(routePoints, { padding: [30, 30] });
            }
        }
    </script>
</body>
</html>