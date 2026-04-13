<!DOCTYPE html>
<html lang="pl">
<head>
    <?php
    $title = 'Rejestracja — SharePlanner';
    $extraCss = ['auth'];
    include __DIR__ . '/../partials/head.php';
    ?>
</head>
<body>

<div class="auth-page">

    <!-- Left branding panel -->
    <div class="auth-panel">
        <div class="auth-brand">
            <div class="auth-brand-icon">
                <i class="fa-solid fa-calendar-days"></i>
            </div>
            <span class="auth-brand-name">SharePlanner</span>
        </div>

        <div class="auth-panel-body">
            <h1 class="auth-panel-title">
                Zacznij planować<br />już dziś.
            </h1>
            <p class="auth-panel-subtitle">
                Dołącz do tysięcy studentów, którzy zarządzają swoim czasem mądrzej.
            </p>
        </div>

        <div class="auth-features">
            <div class="auth-feature">
                <div class="auth-feature-icon"><i class="fa-solid fa-bolt"></i></div>
                <span>Błyskawiczna konfiguracja — dodaj przedmioty w minutę</span>
            </div>
            <div class="auth-feature">
                <div class="auth-feature-icon"><i class="fa-solid fa-chart-line"></i></div>
                <span>Śledź postęp przygotowań do egzaminów</span>
            </div>
            <div class="auth-feature">
                <div class="auth-feature-icon"><i class="fa-solid fa-users"></i></div>
                <span>Współdziel materiały z całą grupą</span>
            </div>
        </div>
    </div>

    <!-- Right form panel -->
    <div class="auth-form-section">
        <div class="auth-form-wrapper">

            <div class="auth-form-header">
                <h2 class="auth-form-title">Utwórz konto</h2>
                <p class="auth-form-subtitle">
                    Masz już konto?
                    <a href="/login">Zaloguj się</a>
                </p>
            </div>

            <?php if (!empty($error)): ?>
                <div class="auth-alert auth-alert-error">
                    <i class="fa-solid fa-circle-exclamation"></i>
                    <?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="/register">

                <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
                    <div class="form-group" style="margin-bottom:0;">
                        <label class="form-label" for="first_name">Imię</label>
                        <div class="input-group">
                            <i class="fa-regular fa-user input-icon"></i>
                            <input
                                type="text"
                                id="first_name"
                                name="first_name"
                                class="form-control"
                                placeholder="Jan"
                                value="<?= htmlspecialchars($_POST['first_name'] ?? '') ?>"
                                required
                            />
                        </div>
                    </div>
                    <div class="form-group" style="margin-bottom:0;">
                        <label class="form-label" for="last_name">Nazwisko</label>
                        <input
                            type="text"
                            id="last_name"
                            name="last_name"
                            class="form-control"
                            placeholder="Kowalski"
                            value="<?= htmlspecialchars($_POST['last_name'] ?? '') ?>"
                            required
                        />
                    </div>
                </div>

                <div class="form-group mt-4">
                    <label class="form-label" for="email">Adres e-mail</label>
                    <div class="input-group">
                        <i class="fa-regular fa-envelope input-icon"></i>
                        <input
                            type="email"
                            id="email"
                            name="email"
                            class="form-control"
                            placeholder="student@uczelnia.pl"
                            value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
                            required
                            autocomplete="email"
                        />
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label" for="password">Hasło</label>
                    <div class="input-group">
                        <i class="fa-regular fa-lock input-icon"></i>
                        <input
                            type="password"
                            id="password"
                            name="password"
                            class="form-control"
                            placeholder="Minimum 8 znaków"
                            required
                            autocomplete="new-password"
                        />
                        <span class="input-toggle" id="toggle-password">
                            <i class="fa-regular fa-eye"></i>
                        </span>
                    </div>
                    <p class="form-hint">Co najmniej 8 znaków, jedna litera i jedna cyfra.</p>
                </div>

                <div class="form-group">
                    <label class="form-label" for="password_confirm">Potwierdź hasło</label>
                    <div class="input-group">
                        <i class="fa-regular fa-lock input-icon"></i>
                        <input
                            type="password"
                            id="password_confirm"
                            name="password_confirm"
                            class="form-control"
                            placeholder="Powtórz hasło"
                            required
                            autocomplete="new-password"
                        />
                    </div>
                </div>

                <button type="submit" class="btn btn-primary btn-lg btn-block" style="margin-top:4px;">
                    <i class="fa-solid fa-user-plus"></i>
                    Zarejestruj się
                </button>

            </form>

            <p class="auth-footer-note">
                Rejestrując się, akceptujesz
                <a href="#">regulamin</a> i
                <a href="#">politykę prywatności</a>.
            </p>
        </div>
    </div>

</div>

<script>
    const toggle = document.getElementById('toggle-password');
    const pwd    = document.getElementById('password');
    if (toggle && pwd) {
        toggle.addEventListener('click', () => {
            const isText = pwd.type === 'text';
            pwd.type = isText ? 'password' : 'text';
            toggle.querySelector('i').className = isText
                ? 'fa-regular fa-eye'
                : 'fa-regular fa-eye-slash';
        });
    }
</script>

</body>
</html>
