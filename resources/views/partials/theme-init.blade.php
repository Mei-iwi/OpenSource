<script>
    (() => {
        try {
            document.documentElement.classList.toggle('dark', localStorage.getItem('hr-theme') === 'dark');
        } catch (_) {}
    })();
</script>
