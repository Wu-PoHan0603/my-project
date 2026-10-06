// 找到所有姓名照片預覽區
document.querySelectorAll('.js-photo-preview').forEach((nameButton) => {
    const preview = nameButton.querySelector('.client-photo-popover');
    const image = nameButton.querySelector('.client-photo-image');
    const message = nameButton.querySelector('.client-photo-message');

    // 滑鼠滑過或鍵盤選取姓名時，載入這位案主的照片
    function loadPhotoPreview() {
        // 已成功載入或確認沒有照片時，不重複請求
        if (image.dataset.loaded === 'true' || image.dataset.failed === 'true') {
            return;
        }

        // 先顯示載入提示
        message.textContent = '照片載入中……';
        message.hidden = false;

        // 圖片載入成功時顯示照片
        image.onload = () => {
            image.hidden = false;
            message.hidden = true;
            image.dataset.loaded = 'true';
        };

        // 找不到圖片時顯示友善提示
        image.onerror = () => {
            image.hidden = true;
            message.textContent = '目前沒有可預覽的照片';
            message.hidden = false;
            image.dataset.failed = 'true';
        };

        // 使用 HTML data-photo-url 中的受保護網址載入照片
        image.src = nameButton.dataset.photoUrl;
    }

    // 滑鼠滑過姓名時載入照片
    nameButton.addEventListener('mouseenter', loadPhotoPreview);

    // 鍵盤移到姓名按鈕時也能載入照片
    nameButton.addEventListener('focus', loadPhotoPreview);
});