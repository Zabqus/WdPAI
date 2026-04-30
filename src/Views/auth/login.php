<!DOCTYPE html>
<html lang="pl">
<head>
    <?php
    $title    = 'Sign In — SyncU';
    $extraCss = ['auth'];
    include __DIR__ . '/../partials/head.php';
    ?>
</head>
<body class="auth-body">

<!-- Abstract background blobs -->
<div class="auth-blob auth-blob--tr" aria-hidden="true"></div>
<div class="auth-blob auth-blob--bl" aria-hidden="true"></div>

<main class="auth-center">

    <div class="auth-container">

        <!-- Card -->
        <div class="auth-card">

            <!-- Heading -->
            <div class="auth-heading">
                <h1 class="auth-title">Welcome back.</h1>
                <p class="auth-subtitle">Step back into your curated study space.</p>
            </div>

            <!-- Social buttons -->
            <div class="auth-social">
                <button type="button" class="auth-social-btn">
                    <img src="https://www.figma.com/api/mcp/asset/4a613017-2979-4dec-b842-f5f8b3ff2690" alt="Google" class="auth-social-icon" width="20" height="20">
                    <span>Continue with Google</span>
                </button>
                <button type="button" class="auth-social-btn">
                    <i class="fa-solid fa-building-columns" style="font-size:16px;color:#476063;"></i>
                    <span>University ID</span>
                </button>
            </div>

            <!-- Divider -->
            <div class="auth-divider">
                <span class="auth-divider-line"></span>
                <span class="auth-divider-text">or access via mail</span>
                <span class="auth-divider-line"></span>
            </div>

            <!-- Form -->
            <?php if (!empty($error)): ?>
                <div class="auth-alert">
                    <i class="fa-solid fa-circle-exclamation"></i>
                    <?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="/login" class="auth-form" novalidate>
                <?= CsrfGuard::field() ?>

                <div class="auth-field">
                    <label class="auth-label" for="email">Email Address</label>
                    <input
                        type="email"
                        id="email"
                        name="email"
                        class="auth-input"
                        placeholder="student@university.edu"
                        value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
                        required
                        autocomplete="email"
                    />
                </div>

                <div class="auth-field">
                    <div class="auth-label-row">
                        <label class="auth-label" for="password">Password</label>
                        <a href="#" class="auth-forgot">Forgot?</a>
                    </div>
                    <input
                        type="password"
                        id="password"
                        name="password"
                        class="auth-input"
                        placeholder="••••••••"
                        required
                        autocomplete="current-password"
                    />
                </div>

                <button type="submit" class="auth-submit">Sign In</button>

            </form>

            <!-- Register link -->
            <p class="auth-register">
                <span>New to SyncU?&nbsp;</span>
                <a href="/register" class="auth-register-link">Create an account</a>
            </p>

        </div><!-- /auth-card -->

        <!-- Bottom icons -->
        <div class="auth-icons" aria-hidden="true">
            <div class="auth-icon-item">
                <i class="fa-solid fa-people-group auth-icon-glyph"></i>
                <span class="auth-icon-label">Collaboration</span>
            </div>
            <div class="auth-icon-item">
                <i class="fa-regular fa-calendar auth-icon-glyph"></i>
                <span class="auth-icon-label">Planning</span>
            </div>
            <div class="auth-icon-item">
                <i class="fa-regular fa-file-lines auth-icon-glyph"></i>
                <span class="auth-icon-label">Notes</span>
            </div>
        </div>

    </div><!-- /auth-container -->

</main>


<script>
document.querySelector('.auth-form').addEventListener('submit', async function (e) {
    e.preventDefault();

    const form   = this;
    const btn    = form.querySelector('[type="submit"]');
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
    alert.innerHTML = '<i class="fa-solid fa-circle-exclamation"></i> ' + msg;
}
</script>

</body>
</html>
