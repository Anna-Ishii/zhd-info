<!-- モーダル：結合PDFファイルモーダル -->
<div class="join-file-modal" id="joinFileModal" style="display: none;">
    <div class="join-file-modal-overlay"></div>
    <div class="join-file-modal-container">
        <div class="join-file-modal-content">
            <div class="join-file-modal-header">
                <div class="join-file-modal-title">
                    <h3>ファイルの結合</h3>
                    <p class="join-file-modal-subtitle">結合するファイルを選択してください</p>
                </div>
                <button type="button" class="join-file-modal-close" id="joinFileModalClose">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M18 6L6 18M6 6L18 18" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </button>
            </div>
            <div class="join-file-modal-body" id="fileCheckboxes">
                <!-- チェックボックスがここに追加されます -->
            </div>
            <div class="join-file-modal-footer">
                <div class="footer-message"></div>
                <div class="footer-buttons">
                    <button type="button" class="join-file-btn join-file-btn-secondary" id="joinFileModalClose">キャンセル</button>
                    <button type="button" class="join-file-btn join-file-btn-primary" id="joinFileBtn">結合</button>
                </div>
            </div>
        </div>
    </div>
</div>
