<!DOCTYPE html>
<html lang="pl">
<head>
    <?php
    $title    = 'Rejestracja — SyncU';
    $extraCss = ['auth'];
    include __DIR__ . '/../partials/head.php';
    ?>
</head>
<body class="auth-body">

<div class="auth-blob auth-blob--tr" aria-hidden="true"></div>
<div class="auth-blob auth-blob--bl" aria-hidden="true"></div>

<main class="auth-center">
    <div class="auth-container">

        <div class="auth-card">

            <div class="auth-heading">
                <h1 class="auth-title">Utwórz konto.</h1>
                <p class="auth-subtitle">Dołącz i zacznij planować swoją naukę.</p>
            </div>

            <?php if (!empty($error)): ?>
                <div class="auth-alert">
                    <i class="fa-solid fa-circle-exclamation"></i>
                    <?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="/register" class="auth-form" novalidate>
                <?= CsrfGuard::field() ?>

                <div class="auth-row-2col">
                    <div class="auth-field">
                        <label class="auth-label" for="first_name">Imię</label>
                        <input class="auth-input" type="text" id="first_name" name="first_name"
                               placeholder="Jan"
                               value="<?= htmlspecialchars($_POST['first_name'] ?? '') ?>"
                               required autocomplete="given-name">
                    </div>
                    <div class="auth-field">
                        <label class="auth-label" for="last_name">Nazwisko</label>
                        <input class="auth-input" type="text" id="last_name" name="last_name"
                               placeholder="Kowalski"
                               value="<?= htmlspecialchars($_POST['last_name'] ?? '') ?>"
                               required autocomplete="family-name">
                    </div>
                </div>

                <div class="auth-field">
                    <label class="auth-label" for="email">Adres e-mail</label>
                    <input class="auth-input" type="email" id="email" name="email"
                           placeholder="student@uczelnia.pl"
                           value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
                           required autocomplete="email">
                </div>

                <div class="auth-field">
                    <label class="auth-label" for="password">Hasło</label>
                    <div class="auth-input-wrap">
                        <input class="auth-input" type="password" id="password" name="password"
                               placeholder="Minimum 8 znaków"
                               required autocomplete="new-password">
                        <button type="button" class="auth-eye-btn" id="toggle-password" aria-label="Pokaż/ukryj hasło">
                            <i class="fa-regular fa-eye" aria-hidden="true"></i>
                        </button>
                    </div>
                    <p class="auth-hint">Co najmniej 8 znaków, jedna litera i jedna cyfra.</p>
                </div>

                <div class="auth-field">
                    <label class="auth-label" for="password_confirm">Potwierdź hasło</label>
                    <input class="auth-input" type="password" id="password_confirm" name="password_confirm"
                           placeholder="Powtórz hasło"
                           required autocomplete="new-password">
                </div>

                <button type="submit" class="auth-submit">Zarejestruj się</button>

            </form>

            <p class="auth-register">
                <span>Masz już konto?&nbsp;</span>
                <a href="/login" class="auth-register-link">Zaloguj się</a>
            </p>

        </div>

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

    </div>
</main>

<script>
const toggle = document.getElementById('toggle-password');
const pwd    = document.getElementById('password');
if (toggle && pwd) {
    toggle.addEventListener('click', () => {
        const isText = pwd.type === 'text';
        pwd.type = isText ? 'password' : 'text';
        toggle.querySelector('i').className = isText ? 'fa-regular fa-eye' : 'fa-regular fa-eye-slash';
    });
}
</script>

</body>
</html>
