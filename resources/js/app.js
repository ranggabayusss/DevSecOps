document.querySelectorAll('[data-password-toggle]').forEach((toggle) => {
    const input = document.getElementById(toggle.dataset.passwordToggle);

    if (!input) {
        return;
    }

    toggle.addEventListener('click', () => {
        const show = input.type === 'password';

        input.type = show ? 'text' : 'password';
        toggle.setAttribute('aria-pressed', show ? 'true' : 'false');
        toggle.setAttribute('aria-label', show ? 'Sembunyikan password' : 'Tampilkan password');

        toggle.querySelector('[data-password-icon="show"]')?.classList.toggle('hidden', show);
        toggle.querySelector('[data-password-icon="hide"]')?.classList.toggle('hidden', !show);
    });
});
