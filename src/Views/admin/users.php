<!DOCTYPE html>
<html lang="pl">
<head>
    <?php
    $extraCss = ['dashboard', 'admin'];
    include __DIR__ . '/../partials/head.php';
    ?>
</head>
<body class="db-page">

<header class="db-navbar">
    <div class="db-navbar-inner">
        <span class="db-brand" style="font-weight:700;font-size:1.1rem;">SyncU &mdash; Panel admina</span>
        <nav class="db-nav-links">
            <a href="/dashboard" class="db-nav-link">Dashboard</a>
            <a href="/admin"     class="db-nav-link active">Użytkownicy</a>
        </nav>
        <div class="db-navbar-right">
            <div class="db-identity">
                <div class="db-user-avatar"><?= strtoupper(substr(Session::get('user_name', 'A'), 0, 2)) ?></div>
                <a href="/logout" class="db-nav-link" style="font-size:.85rem;">Wyloguj</a>
            </div>
        </div>
    </div>
</header>

<main class="db-canvas">

    <div class="db-page-header">
        <h1 class="db-greeting">Zarządzanie użytkownikami</h1>
        <p class="db-subtitle">Łącznie: <strong><?= count($users) ?></strong> kont</p>
    </div>

    <section class="db-card admin-card">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Użytkownik</th>
                    <th>E-mail</th>
                    <th>Rola</th>
                    <th>Aktywny</th>
                    <th>Rejestracja</th>
                    <th>Akcje</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($users as $user): ?>
                <?php $isSelf = $user->getId() === $currentId; ?>
                <tr class="<?= $isSelf ? 'admin-row--self' : '' ?>">
                    <td class="admin-td-id"><?= $user->getId() ?></td>
                    <td>
                        <div class="admin-user-cell">
                            <div class="db-user-avatar admin-avatar">
                                <?= strtoupper(substr($user->getUsername(), 0, 2)) ?>
                            </div>
                            <?= htmlspecialchars($user->getUsername()) ?>
                            <?php if ($isSelf): ?>
                                <span class="admin-badge-you">ty</span>
                            <?php endif; ?>
                        </div>
                    </td>
                    <td><?= htmlspecialchars($user->getEmail()) ?></td>
                    <td>
                        <?php if ($isSelf): ?>
                            <span class="admin-role-badge admin-role-<?= $user->getRole() ?>"><?= $user->getRole() ?></span>
                        <?php else: ?>
                            <form method="POST" action="/admin/role" class="admin-role-form">
                                <input type="hidden" name="user_id" value="<?= $user->getId() ?>">
                                <select name="role" class="admin-select" onchange="this.form.submit()">
                                    <option value="user"  <?= $user->getRole() === 'user'  ? 'selected' : '' ?>>user</option>
                                    <option value="admin" <?= $user->getRole() === 'admin' ? 'selected' : '' ?>>admin</option>
                                </select>
                            </form>
                        <?php endif; ?>
                    </td>
                    <td>
                        <span class="admin-status <?= $user->isActive() ? 'admin-status--on' : 'admin-status--off' ?>">
                            <?= $user->isActive() ? 'tak' : 'nie' ?>
                        </span>
                    </td>
                    <td class="admin-td-date"><?= date('d.m.Y', strtotime($user->getCreatedAt())) ?></td>
                    <td>
                        <?php if (!$isSelf): ?>
                            <form method="POST" action="/admin/delete"
                                  onsubmit="return confirm('Usunąć użytkownika <?= htmlspecialchars($user->getUsername(), ENT_QUOTES) ?>?')">
                                <input type="hidden" name="user_id" value="<?= $user->getId() ?>">
                                <button type="submit" class="admin-btn-delete">
                                    <i class="fa-regular fa-trash-can"></i> Usuń
                                </button>
                            </form>
                        <?php else: ?>
                            <span class="admin-td-na">—</span>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </section>

</main>
</body>
</html>
