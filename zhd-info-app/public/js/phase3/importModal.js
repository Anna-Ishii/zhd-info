document.addEventListener('DOMContentLoaded', function () {
    console.log('🔍 importModal.js loaded');
    
    const modalBtns = document.querySelectorAll('.inport-modal-btn');
    const closelBtn = document.getElementById('inportCloselBtn');
    const openBtn = document.getElementById('inportOpenBtn');
    const modal = document.getElementById('inportModal');

    console.log('📊 Debug Info:');
    console.log('- Modal buttons found:', modalBtns.length);
    console.log('- Modal element:', modal);
    console.log('- Close button:', closelBtn);
    console.log('- Open button:', openBtn);
    console.log('- Modal current classes:', modal ? modal.className : 'modal not found');
    console.log('- Modal current style:', modal ? modal.style.display : 'modal not found');

    if (modalBtns.length > 0 && modal && closelBtn && openBtn) {
        console.log('✅ All elements found, adding event listeners');
        
        modalBtns.forEach((btn, index) => {
            console.log(`🔗 Adding click listener to button ${index + 1}`);
            btn.addEventListener('click', function (e) {
                console.log('🖱️ Modal button clicked!');
                console.log('- Event target:', e.target);
                console.log('- Button element:', this);
                console.log('- Modal before click:', modal.className);
                
                modal.classList.add('disp');
                
                console.log('- Modal after adding disp class:', modal.className);
                console.log('- Modal computed style after:', window.getComputedStyle(modal).display);
            });
        });

        closelBtn.addEventListener('click', function () {
            console.log('❌ Close button clicked');
            modal.classList.remove('disp');
            console.log('- Modal classes after close:', modal.className);
        });
        
        openBtn.addEventListener('click', function () {
            console.log('📂 Open button clicked');
            const fileName = this.getAttribute('data-file');
            console.log('- File name:', fileName);
            if (fileName) {
                console.log('- Redirecting to:', fileName + ".html");
                window.location.href = fileName + ".html";
            } else {
                console.log('- No file name, closing modal');
                modal.classList.remove('disp');
            }
        });
    } else {
        console.log('❌ Some elements not found:');
        console.log('- Modal buttons:', modalBtns.length);
        console.log('- Modal element exists:', !!modal);
        console.log('- Close button exists:', !!closelBtn);
        console.log('- Open button exists:', !!openBtn);
    }
});












