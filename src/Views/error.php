<!DOCTYPE html>
<html lang="pl">
<head>
    <?php
    $title    = $errorCode . ' — SyncU';
    $extraCss = ['auth'];
    include __DIR__ . '/partials/head.php';
    ?>
</head>
<body class="auth-body">

<div class="auth-blob auth-blob--tr" aria-hidden="true"></div>
<div class="auth-blob auth-blob--bl" aria-hidden="true"></div>

<main class="auth-center">
    <div class="err-card">

        <div class="err-code"><?= htmlspecialchars((string)$errorCode) ?></div>
        <h1 class="err-title"><?= htmlspecialchars($errorTitle) ?></h1>
        <p class="err-desc"><?= htmlspecialchars($errorDesc) ?></p>

        <div class="err-actions">
            <a href="/dashboard" class="err-btn err-btn--primary">
                <i class="fa-solid fa-house"></i>
                Dashboard
            </a>
            <a href="javascript:history.back()" class="err-btn err-btn--secondary">
                <i class="fa-solid fa-arrow-left"></i>
                Wróć
            </a>
        </div>

    </div>
</main>

<style>
.auth-center { align-items: center; }

.err-card {
    background: white;
    border-radius: 12px;
    padding: 56px 48px;
    max-width: 480px;
    width: 100%;
    text-align: center;
    box-shadow: 0 24px 48px -12px rgba(42, 52, 54, 0.08);
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 12px;
}

.err-code {
    font-size: 88px;
    font-weight: 700;
    line-height: 1;
    letter-spacing: -4px;
    background: linear-gradient(135deg, #1b6871, #035c65);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
    margin-bottom: 4px;
}

.err-title {
    font-size: 22px;
    font-weight: 700;
    color: #2a3436;
    letter-spacing: -0.3px;
}

.err-desc {
    font-size: 14px;
    font-weight: 400;
    color: #576162;
    line-height: 22px;
    max-width: 340px;
    margin-bottom: 8px;
}

.err-actions {
    display: flex;
    gap: 12px;
    justify-content: center;
    flex-wrap: wrap;
    margin-top: 8px;
}

.err-btn {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 12px 24px;
    border-radius: 9999px;
    font-size: 14px;
    font-weight: 600;
    text-decoration: none;
    transition: opacity 150ms, background 150ms;
    font-family: var(--font, 'Inter', sans-serif);
}

.err-btn--primary {
    background: linear-gradient(170.75deg, #1b6871, #035c65);
    color: #eafcff;
}

.err-btn--primary:hover { opacity: 0.9; }

.err-btn--secondary {
    background: #e1eaeb;
    color: #476063;
}

.err-btn--secondary:hover { background: #d4e3e5; }
</style>

</body>
</html>
