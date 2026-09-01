import './bootstrap';

const savedTheme = localStorage.getItem('hr-theme');
if (savedTheme === 'dark' || (!savedTheme && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
    document.documentElement.classList.add('dark');
}

import Alpine from 'alpinejs';

window.Alpine = Alpine;

Alpine.start();
