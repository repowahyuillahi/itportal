<?php
/**
 * pages/track/index.php — Public ticket tracking (Tabler v1.4.0, no login)
 */
$token = trim($_GET['token'] ?? '');
$pageTitle = 'Track Ticket';

$ticket = null;
$messages = [];

if ($token) {
    $stmt = db()->prepare("SELECT tt.*, t.ticket_code, t.subject, t.status, t.priority, t.category, t.created_at as ticket_created, u.full_name as creator_name
                           FROM ticket_tokens tt JOIN tickets t ON tt.ticket_id = t.id LEFT JOIN users u ON t.user_id = u.id
                           WHERE tt.token = ? AND tt.type = 'public_track' LIMIT 1");
    $stmt->execute([$token]);
    $tokenData = $stmt->fetch();
    if ($tokenData && $tokenData['revoked_at'] === null) {
        $ticket = $tokenData;
        $msgStmt = db()->prepare("SELECT m.*, u.full_name FROM ticket_messages m LEFT JOIN users u ON m.user_id = u.id WHERE m.ticket_id = ? AND m.is_internal = 0 ORDER BY m.created_at ASC");
        $msgStmt->execute([$tokenData['ticket_id']]);
        $messages = $msgStmt->fetchAll();
    }
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <title><?= e($pageTitle) ?> — <?= e(APP_NAME) ?></title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/core@1.4.0/dist/css/tabler.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@3.31.0/dist/tabler-icons.min.css">
    <link rel="stylesheet" href="<?= asset('custom/app.css') ?>">
</head>

<body class="d-flex flex-column">
    <script src="https://cdn.jsdelivr.net/npm/@tabler/core@1.4.0/dist/js/tabler.min.js" defer></script>
    <div class="page page-center">
        <div class="container container-tight py-4">
            <div class="text-center mb-4">
                <a href="<?= url('/') ?>" class="navbar-brand navbar-brand-autodark">
                    <svg xmlns="http://www.w3.org/2000/svg" width="140" height="36" viewBox="0 0 232 68" fill="none">
                        <rect width="68" height="68" rx="16" fill="#0054a6" />
                        <path
                            d="M20.672 18.32L47.328 18.32C48.854 18.32 50.088 19.572 50.088 21.12V31.84H40.248L34.8 25.68L29.352 31.84H17.912V21.12C17.912 19.572 19.146 18.32 20.672 18.32Z"
                            fill="white" />
                        <path
                            d="M17.912 36.16H29.352L34.8 42.32L40.248 36.16H50.088V46.88C50.088 48.428 48.854 49.68 47.328 49.68H20.672C19.146 49.68 17.912 48.428 17.912 46.88V36.16Z"
                            fill="white" fill-opacity="0.5" />
                        <text x="80" y="46" font-family="Inter, sans-serif" font-weight="700" font-size="32"
                            fill="#1e293b"><?= e(APP_NAME) ?></text>
                    </svg>
                </a>
            </div>

            <?php if (!$token): ?>
                <div class="card card-md">
                    <div class="card-body">
                        <h2 class="h2 text-center mb-4">Tracking Ticket</h2>
                        <form method="GET">
                            <div class="mb-3">
                                <label class="form-label">Token</label>
                                <input type="text" name="token" class="form-control"
                                    placeholder="Masukkan token tracking..." required autofocus>
                            </div>
                            <div class="form-footer">
                                <button type="submit" class="btn btn-primary w-100">Track Ticket</button>
                            </div>
                        </form>
                    </div>
                </div>

            <?php elseif (!$ticket): ?>
                <div class="card card-md">
                    <div class="card-body">
                        <div class="empty">
                            <div class="empty-icon"><i class="ti ti-link-off"
                                    style="font-size:3rem; color:var(--tblr-danger)"></i></div>
                            <p class="empty-title">Link Tidak Aktif</p>
                            <p class="empty-subtitle text-secondary">Link tracking ini sudah tidak aktif atau ticket telah
                                ditutup.</p>
                            <div class="empty-action"><a href="<?= url('/track') ?>" class="btn btn-primary">Coba Lagi</a>
                            </div>
                        </div>
                    </div>
                </div>

            <?php else: ?>
                <div class="card mb-3">
                    <div class="card-status-top bg-primary"></div>
                    <div class="card-header">
                        <h3 class="card-title"><?= e($ticket['ticket_code']) ?></h3>
                        <div class="card-actions"><?= statusBadge($ticket['status']) ?></div>
                    </div>
                    <div class="card-body">
                        <div class="datagrid">
                            <div class="datagrid-item">
                                <div class="datagrid-title">Subject</div>
                                <div class="datagrid-content"><?= e($ticket['subject']) ?></div>
                            </div>
                            <div class="datagrid-item">
                                <div class="datagrid-title">Prioritas</div>
                                <div class="datagrid-content"><?= priorityBadge($ticket['priority']) ?></div>
                            </div>
                            <div class="datagrid-item">
                                <div class="datagrid-title">Kategori</div>
                                <div class="datagrid-content"><?= e($ticket['category']) ?></div>
                            </div>
                            <div class="datagrid-item">
                                <div class="datagrid-title">Tanggal</div>
                                <div class="datagrid-content"><?= formatDate($ticket['ticket_created']) ?></div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Timeline</h3>
                    </div>
                    <div class="card-body">
                        <?php if (empty($messages)): ?>
                            <p class="text-secondary text-center py-3">Belum ada update.</p>
                        <?php else: ?>
                            <div class="divide-y">
                                <?php foreach ($messages as $m): ?>
                                    <div class="py-3">
                                        <div class="row align-items-center">
                                            <div class="col-auto"><span class="avatar avatar-sm"
                                                    style="background-image: url(https://ui-avatars.com/api/?name=<?= urlencode($m['full_name'] ?? 'S') ?>&size=32&background=0054a6&color=fff)"></span>
                                            </div>
                                            <div class="col"><strong><?= e($m['full_name'] ?? 'System') ?></strong>
                                                <div class="text-secondary small"><?= formatDate($m['created_at']) ?></div>
                                            </div>
                                        </div>
                                        <div class="mt-2 ps-5"><?= nl2br(e($m['message'])) ?></div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>
            <div class="text-center text-secondary mt-3">&copy; <?= date('Y') ?> <?= e(APP_NAME) ?></div>
        </div>
    </div>
</body>

</html>