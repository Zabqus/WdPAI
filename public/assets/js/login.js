document.querySelector('.auth-form').addEventListener('submit', async function (e) {
    e.preventDefault();

    const form     = this;
    const btn      = form.querySelector('[type="submit"]');
    const original = btn.textContent;
    btn.disabled    = true;
    btn.textContent = 'Logowanie…';

    try {
        const res  = await fetch('/login', {
            method:  'POST',
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
            body:    new FormData(form),
        });
        const data = await res.json();

        if (data.success) {
            window.location.href = data.redirect;
        } else {
            showError(data.error);
            btn.disabled    = false;
            btn.textContent = original;
        }
    } catch {
        showError('Wystąpił błąd połączenia. Spróbuj ponownie.');
        btn.disabled    = false;
        btn.textContent = original;
    }
});

function showError(msg) {
    let alert = document.querySelector('.auth-alert');
    if (!alert) {
        alert = document.createElement('div');
        alert.className = 'auth-alert';
        document.querySelector('.auth-form').insertAdjacentElement('beforebegin', alert);
    }
    alert.textContent = msg;
}
