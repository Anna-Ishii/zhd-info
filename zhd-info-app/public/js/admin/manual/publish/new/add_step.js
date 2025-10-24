import { setupFileUpload } from './file_upload.js';

export function setupAddStep() {
    const stepList = document.getElementById('stepList');
    const addStepBtn = document.getElementById('stepAddBtn');
    const assetsElem = document.getElementById('step-assets');

    if (!stepList || !addStepBtn || !assetsElem) return;

    const assets = assetsElem.dataset;
    const uploadCloud = assets.uploadCloud || '';
    const deleteIcon = assets.deleteIcon || '';

    addStepBtn.addEventListener('click', () => {
        const stepCount = stepList.querySelectorAll('.content__wrap').length;
        const newStepNumber = stepCount + 1;
        const newStepId = `step${newStepNumber}`;
        const newFileInputId = `${newStepId}File`;
        const newName = `step[${newStepNumber}]`;

        const newStep = document.createElement('div');
        newStep.className = 'content__wrap';
        newStep.innerHTML = `
            <p class="content">手順${newStepNumber}</p>
            <div class="content-sub__wrap">
                <p class="content-sub">手順名</p>
                <div class="input-group custom-textbox">
                    <input type="text" name="${newName}[title]" placeholder="タイトルを入力してください">
                </div>
            </div>
            <div class="content-sub__wrap">
                <p class="content-sub">手順ファイル添付</p>
                <div class="file-upload__container">
                    <div class="file-upload__container__item">
                        <div class="file-upload__container__item__wrap">
                            <div class="file-input-item">
                                <div class="uploader" id="${newStepId}">
                                    <label for="${newFileInputId}">
                                        <img class="uploadbefore" src="${uploadCloud}" alt="" />
                                        <div class="upload-txt uploadbefore">
                                            <p>ここにファイルをドロップ</p>
                                            <p>または</p>
                                            <p class="upload-txt-btn">ファイルを選択</p>
                                        </div>
                                        <div class="uploaded" style="display:none;">
                                            <img class="uploaded-img" src="" alt="" />
                                            <p class="uploaded-txt"></p>
                                        </div>
                                    </label>
                                    <div class="file-uploaded-list">
                                    
                                    </div>
                                    <input type="file" name="${newName}[file]" id="${newFileInputId}" />
                                    <input type="hidden" name="${newName}[file_name]" data-variable-name="manual_file_name">
                                    <input type="hidden" name="${newName}[file_path]" data-variable-name="manual_file_path">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="content-sub__wrap">
                <p class="content-sub">手順内容</p>
                <div class="input-group custom-textbox">
                    <textarea name="${newName}[detail]" placeholder="手順内容を入力してください" rows="3"></textarea>
                </div>
                <div class="button__wrap__item delete-step-btn" style="cursor:pointer;" tabindex="0" role="button">
                    <p>この手順を削除する</p>
                    <img src="${deleteIcon}" alt="削除">
                </div>
            </div>
        `;
        stepList.appendChild(newStep);

        const fileUploadElem = newStep.querySelector(`#${newStepId}`);
        setupFileUpload(fileUploadElem);

        addDeleteEvent(newStep);
        renumberSteps();
    });

    function addDeleteEvent(stepElem) {
        const deleteBtn = stepElem.querySelector('.delete-step-btn');
        if (deleteBtn) {
            deleteBtn.addEventListener('click', () => {
                stepElem.remove();
                renumberSteps();
            });
        }
    }

    function renumberSteps() {
        const steps = stepList.querySelectorAll('.content__wrap');
        steps.forEach((wrap, i) => {
            const stepNum = i + 1;
            const label = wrap.querySelector('.content');
            if (label) label.textContent = `手順${stepNum}`;

            const uploader = wrap.querySelector('.uploader');
            if (uploader) {
                const stepFileId = `step${stepNum}`;
                uploader.id = stepFileId;

                const input = uploader.querySelector('input[type="file"]');
                const labelElem = uploader.querySelector('label');
                if (input) {
                    input.id = `${stepFileId}File`;
                    input.name = `step[${stepNum}][file]`;
                }
                if (labelElem) labelElem.setAttribute('for', `${stepFileId}File`);
            }

            const titleInput = wrap.querySelector('input[type="text"]');
            if (titleInput) titleInput.name = `step[${stepNum}][title]`;
            const detailTextarea = wrap.querySelector('textarea');
            if (detailTextarea) detailTextarea.name = `step[${stepNum}][detail]`;
        });
    }

    stepList.querySelectorAll('.content__wrap').forEach((wrap, i) => {
        addDeleteEvent(wrap);
        const stepNum = i + 1;
        const uploader = wrap.querySelector('.uploader');
        if (uploader) {
            const stepFileId = `step${stepNum}`;
            uploader.id = stepFileId;

            const input = uploader.querySelector('input[type="file"]');
            const labelElem = uploader.querySelector('label');
            if (input) {
                input.id = `${stepFileId}File`;
                input.name = `step[${stepNum}][file]`;
            }
            if (labelElem) labelElem.setAttribute('for', `${stepFileId}File`);
            setupFileUpload(uploader);
        }

        const titleInput = wrap.querySelector('input[type="text"]');
        if (titleInput) titleInput.name = `step[${stepNum}][title]`;
        const detailTextarea = wrap.querySelector('textarea');
        if (detailTextarea) detailTextarea.name = `step[${stepNum}][detail]`;
    });
}