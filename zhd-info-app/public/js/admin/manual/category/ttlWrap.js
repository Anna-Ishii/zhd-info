// 要素を取得
const ttlWrap = document.querySelector('.ttl__wrap');
const ttlInput = document.querySelector('.ttl__input');
const addTtl = document.querySelector('#add__ttl');
const addTxt = document.querySelector('#add__txt');
const ttlField = document.querySelector('#ttl');
const txtField = document.querySelector('#txt');

// 要素が存在する場合のみ処理を実行
if (ttlField && addTtl) {
    // 入力内容を#add__ttl, #add__txtに挿入
    ttlField.addEventListener('input', () => {
        addTtl.textContent = ttlField.value || '無題のフォーム';
    });
}

if (txtField && addTxt) {
    txtField.addEventListener('input', () => {
        addTxt.textContent = txtField.value || '';
    });
}

if (ttlWrap && ttlInput) {
    ttlWrap.addEventListener('click', () => {
        ttlWrap.style.display = 'none'; // ttl__wrapを非表示
        ttlInput.classList.add('active'); // ttl__inputを表示
    });
    
    // フォームの外側をクリックしたらttl__inputを非表示にしてttl__wrapを表示
    document.addEventListener('click', (event) => {
        // クリックがttl__inputやその子要素以外の場合に実行
        if (!ttlInput.contains(event.target) && !ttlWrap.contains(event.target)) {
            ttlInput.classList.remove('active'); // ttl__inputを非表示
            ttlWrap.style.display = 'flex'; // ttl__wrapを表示
        }
    });
}
