<!DOCTYPE html>
<html lang="pl">
<head>
    <?php
    $title = '404 — Nie znaleziono strony';
    include __DIR__ . '/partials/head.php';
    ?>
    <style>
        body { background: var(--bg); }
        .error-page {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 40px 20px;
            text-align: center;
        }
        .error-code {
            font-size: 7rem;
            font-weight: 800;
            line-height: 1;
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            margin-bottom: 16px;
            letter-spacing: -4px;
        }
        .error-title {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--text);
            margin-bottom: 10px;
        }
        .error-desc {
            color: var(--text-muted);
            max-width: 380px;
            margin: 0 auto 32px;
            font-size: 0.95rem;
        }
        .error-actions {
            display: flex;
            gap: 12px;
            justify-content: center;
            flex-wrap: wrap;
        }
    </style>
</head>
<body>
<div class="error-page">
    <div>
        <div class="error-code">404</div>
        <h1 class="error-title">Strona nie istnieje</h1>
        <p class="error-desc">
            Wygląda na to, że szukana strona została usunięta,
            przeniesiona lub nigdy nie istniała.
        </p>
        <div class="error-actions">
            <a href="/dashboard" class="btn btn-primary">
                <i class="fa-solid fa-house"></i>
                Wróć na dashboard
            </a>
            <a href="javascript:history.back()" class="btn btn-secondary">
                <i class="fa-solid fa-arrow-left"></i>
                Poprzednia strona
            </a>
        </div>
    </div>
</div>
</body>
</html>
