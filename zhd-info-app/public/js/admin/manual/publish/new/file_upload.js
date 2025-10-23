export function setupFileUpload(uploader) {
    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    const label = uploader.querySelector('label');
    const fileUploadedList = uploader.querySelector('.file-uploaded-list');
    const fileInput = uploader.querySelector('input[type="file"]');
    const fileNameInput = uploader.querySelector('input[data-variable-name="manual_file_name"]');
    const filePathInput = uploader.querySelector('input[data-variable-name="manual_file_path"]');

    showBefore();

    label.addEventListener('dragover', (e) => {
        e.preventDefault();
        label.classList.add('dragover');
    });
    label.addEventListener('dragleave', (e) => {
        e.preventDefault();
        label.classList.remove('dragover');
    });
    label.addEventListener('drop', (e) => {
        e.preventDefault();
        label.classList.remove('dragover');
        const files = e.dataTransfer.files;
        if (files.length > 0) handleFile(files[0]);
    });

    fileInput.addEventListener('change', (e) => {
        if (e.target.files.length > 0) handleFile(e.target.files[0]);
    });

    function handleFile(file) {
        const allowedTypes = ['image/png', 'image/jpeg', 'image/jpg', 'application/pdf'];
        if (!allowedTypes.includes(file.type)) {
            alert('許可されていないファイル形式です');
            return;
        }
        if (file.size > 100 * 1024 * 1024) {
            alert('サイズが大きすぎます（最大100MB）');
            return;
        }

        let formData = new FormData();
        formData.append("file", file);

        const xhr = new XMLHttpRequest();
        xhr.open("POST", "/admin/manual/publish/upload", true);
        xhr.setRequestHeader('X-CSRF-TOKEN', csrfToken);

        xhr.onload = function () {
            let response;
            try {
                response = JSON.parse(xhr.responseText);
            } catch (e) {
                console.error('Error parsing JSON response:', e);
                response = {};
            }
            console.log("アップロード時のレスポンス:", response);
            if (xhr.status >= 200 && xhr.status < 300 && response) {
                if (response.content_name && response.content_url) {
                    fileNameInput.value = response.content_name;
                    filePathInput.value = response.content_url;
                    showAfter();

                    fileUploadedList.innerHTML = '';
                    const fileDiv = document.createElement('div');
                    fileDiv.className = 'file-uploaded';
                    fileDiv.style.display = 'flex';
                    fileDiv.style.alignItems = 'center';

                    const fileNameP = document.createElement('p');
                    fileNameP.className = 'file__name';
                    const fileLink = document.createElement('span');
                    fileLink.textContent = response.content_name || file.name;
                    fileNameP.appendChild(fileLink);

                    const fileSizeP = document.createElement('p');
                    fileSizeP.className = 'file__size';
                    fileSizeP.textContent = (file.size / 1024).toFixed(1) + 'KB';
                    fileSizeP.style.marginLeft = 'auto';

                    const deleteBtn = document.createElement('p');
                    deleteBtn.className = 'file__delete_btn';
                    const deleteImg = document.createElement('img');
                    deleteImg.src = '/img/delete_icon.svg';
                    deleteImg.alt = 'ファイル削除';
                    deleteBtn.appendChild(deleteImg);
                    deleteBtn.style.cursor = 'pointer';
                    deleteBtn.onclick = () => {
                        showBefore();
                        fileInput.value = '';
                        fileUploadedList.innerHTML = '';
                        fileNameInput.value = '';
                        filePathInput.value = '';
                    };

                    fileDiv.appendChild(fileNameP);
                    fileDiv.appendChild(fileSizeP);
                    fileDiv.appendChild(deleteBtn);

                    fileUploadedList.appendChild(fileDiv);
                } else {
                    if (response.errorMessages && Array.isArray(response.errorMessages)) {
                        alert(response.errorMessages.join('\n'));
                    } else if (response.errorMessage && Array.isArray(response.errorMessage)) {
                        alert(response.errorMessage.join('\n'));
                    } else if (response.message) {
                        alert(response.message);
                    } else {
                        alert('ファイルのアップロードに失敗しました');
                    }
                    fileNameInput.value = '';
                    filePathInput.value = '';
                }
            }
        }
        xhr.onerror = function () {
            alert('ファイルのアップロードに失敗しました');
            fileNameInput.value = '';
            filePathInput.value = '';
        }
        xhr.send(formData);
    }
    function showBefore() {
        label.style.display = '';
        fileInput.style.display = '';
        fileUploadedList.style.display = 'none';
    }

    function showAfter() {
        label.style.display = 'none';
        fileInput.style.display = 'none';
        fileUploadedList.style.display = '';
    }
}