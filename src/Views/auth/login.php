<!DOCTYPE html>
<html lang="pl">
<head>
    <?php
    $title = 'Logowanie — SharePlanner';
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
                Ogarnij swoje<br />studia w jednym<br />miejscu.
            </h1>
            <p class="auth-panel-subtitle">
                Planuj sesje, zarządzaj egzaminami i współdziel notatki ze znajomymi.
            </p>
        </div>

        <div class="auth-features">
            <div class="auth-feature">
                <div class="auth-feature-icon"><i class="fa-solid fa-calendar-check"></i></div>
                <span>Kalendarz z wydarzeniami i egzaminami</span>
            </div>
            <div class="auth-feature">
                <div class="auth-feature-icon"><i class="fa-solid fa-list-check"></i></div>
                <span>Listy zadań i plan nauki</span>
            </div>
            <div class="auth-feature">
                <div class="auth-feature-icon"><i class="fa-solid fa-share-nodes"></i></div>
                <span>Współdzielenie notatek z grupą</span>
            </div>
        </div>
    </div>

    <!-- Right form panel -->
    <div class="auth-form-section">
        <div class="auth-form-wrapper">

            <div class="auth-form-header">
                <h2 class="auth-form-title">Zaloguj się</h2>
                <p class="auth-form-subtitle">
                    Nie masz konta?
                    <a href="/register">Zarejestruj się za darmo</a>
                </p>
            </div>

            <?php if (!empty($error)): ?>
                <div class="auth-alert auth-alert-error">
                    <i class="fa-solid fa-circle-exclamation"></i>
                    <?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="/login">

                <div class="form-group">
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
                            placeholder="••••••••"
                            required
                            autocomplete="current-password"
                        />
                        <span class="input-toggle" id="toggle-password" title="Pokaż/ukryj hasło">
                            <i class="fa-regular fa-eye"></i>
                        </span>
                    </div>
                </div>

                <div class="auth-options">
                    <label class="checkbox-label">
                        <input type="checkbox" name="remember" />
                        Zapamiętaj mnie
                    </label>
                    <a href="/forgot-password" class="auth-forgot">Zapomniałem hasła</a>
                </div>

                <button type="submit" class="btn btn-primary btn-lg btn-block">
                    <i class="fa-solid fa-right-to-bracket"></i>
                    Zaloguj się
                </button>

            </form>

            <p class="auth-footer-note">
                Kontynuując, akceptujesz
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
