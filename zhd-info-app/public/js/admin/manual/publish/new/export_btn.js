export function setupExportBtn() {
    const exportBtn = document.getElementById('exportBtn');
    if (!exportBtn) return;

    exportBtn.addEventListener('click', function () {
        // data-org1-id属性から値を取得
        const org1Id = this.dataset.org1Id;

        // CSRFトークン取得
        const meta = document.querySelector('meta[name="csrf-token"]');
        const csrfToken = meta ? meta.getAttribute('content') : '';

        // FormData作成
        const formData = new FormData();
        formData.append("organization1_id", org1Id);

        // fetchでリクエスト
        fetch('/admin/manual/publish/csv/store/export', {
            method: 'POST',
            body: formData,
            headers: {
                'X-CSRF-TOKEN': csrfToken
            }
        })
            .then(async response => {
                if (!response.ok) throw response;
                const blob = await response.blob();

                // ファイル名取得
                const disposition = response.headers.get('Content-Disposition');
                let fileName = 'export.csv';
                if (disposition && disposition.includes('filename=')) {
                    fileName = disposition.split('filename=')[1].split(';')[0].replace(/"/g, '');
                }

                const url = window.URL.createObjectURL(blob);
                const a = document.createElement('a');
                a.href = url;
                a.download = "店舗選択_" + fileName;
                document.body.appendChild(a);
                a.click();
                window.URL.revokeObjectURL(url);
                document.body.removeChild(a);
            })
            .catch(async errorResponse => {
                let errorManual = 'An error occurred. Please try again later.';
                if (errorResponse instanceof Response) {
                    switch (errorResponse.status) {
                        case 422:
                            errorManual = 'Validation error. Please check your input and try again.';
                            break;
                        case 504:
                            errorManual = 'Server timeout. Please try again later.';
                            break;
                        case 500:
                            errorManual = 'Internal server error. Please try again later.';
                            break;
                    }
                    console.log('Error: ' + errorResponse.status);
                }
                alert(errorManual);
            });
    });
}