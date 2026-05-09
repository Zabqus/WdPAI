<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0" />
<meta name="csrf-token" content="<?= htmlspecialchars(CsrfGuard::token(), ENT_QUOTES) ?>" />
<title><?= htmlspecialchars($title ?? 'SharePlanner') ?></title>

<link rel="preconnect" href="https://fonts.googleapis.com" />
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet" />

<?php $cssBase = __DIR__ . '/../../../public/assets/css/'; ?>
<link rel="stylesheet" href="/public/assets/css/main.css?v=<?= @filemtime($cssBase . 'main.css') ?>"/>
<?php if (!empty($extraCss)): ?>
    <?php foreach ($extraCss as $css): ?>
        <link rel="stylesheet" href="/public/assets/css/<?= htmlspecialchars($css) ?>.css?v=<?= @filemtime($cssBase . $css . '.css') ?>"/>
    <?php endforeach; ?>
<?php endif; ?>

<script src="https://kit.fontawesome.com/3bd737e540.js" crossorigin="anonymous"></script>
