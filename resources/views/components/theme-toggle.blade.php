<button type="button" x-data="{ dark: document.documentElement.classList.contains('dark') }"
    @click="dark = !dark; document.documentElement.classList.toggle('dark', dark); localStorage.setItem('hr-theme', dark ? 'dark' : 'light')"
    :aria-pressed="dark" aria-label="Bật hoặc tắt giao diện tối" class="app-button-secondary">
    <span x-text="dark ? '☀ Sáng' : '☾ Tối'">Sáng / tối</span>
</button>
