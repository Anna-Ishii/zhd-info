import { setupTypeSelect } from './type_select.js';
import { setupDateInput } from './date_input.js';
import { setupExportBtn } from './export_btn.js';
import { setupBrandCheckboxSync } from './brand_checkbox.js';
import { setupFileUpload } from './file_upload.js';
import { setupAddStep } from './add_step.js';
import { setupShopSelect } from './shop_select.js';

document.addEventListener('DOMContentLoaded', () => {
    setupTypeSelect();
    setupDateInput();
    setupExportBtn();
    setupBrandCheckboxSync();
    setupFileUpload(document.getElementById('mainFile'));
    setupAddStep();
    setupShopSelect();
});