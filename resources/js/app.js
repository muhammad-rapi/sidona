// Fokus keyboard tetap terlihat; klik mouse/sentuh tidak memunculkan cincin fokus pada tombol dan tautan.
const root = document.documentElement;

window.addEventListener('pointerdown', () => root.setAttribute('data-pointer', ''), true);
window.addEventListener('keydown', (event) => {
    if (event.key === 'Tab') {
        root.removeAttribute('data-pointer');
    }
}, true);
