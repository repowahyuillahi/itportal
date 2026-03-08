<?php
/**
 * pages/wa/index.php — WhatsApp update stub (Tabler v1.4.0, no login)
 */
$token = trim($_GET['token'] ?? '');
$pageTitle = 'Update Ticket';
$ticket = null;
$success = false;

if ($token) {
    $stmt = db()->prepare("SELECT tt.*, t.ticket_code, t.subject, t.status, t.id as ticket_id FROM ticket_tokens tt JOIN tickets t ON tt.ticket_id = t.id WHERE tt.token = ? AND tt.type = 'wa_update' AND tt.revoked_at IS NULL LIMIT 1");
    $stmt->execute([$token]);
    $ticket = $stmt->fetch();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $ticket) {
    $message = trim($_POST['message'] ?? '');
    if ($message !== '') {
        db()->prepare("INSERT INTO ticket_messages (ticket_id, user_id, message, is_internal, created_at) VALUES (?, NULL, ?, 0, NOW())")->execute([$ticket['ticket_id'], $message]);
        db()->prepare("UPDATE tickets SET updated_at = NOW() WHERE id = ?")->execute([$ticket['ticket_id']]);
        $success = true;
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
                <i class="ti ti-brand-whatsapp" style="font-size:2.5rem; color:#25d366"></i>
                <h2 class="mt-2">Update Ticket</h2>
            </div>

            <?php if (!$token || !$ticket): ?>
                <div class="card card-md">
                    <div class="card-body">
                        <div class="empty">
                            <div class="empty-icon"><i class="ti ti-link-off"
                                    style="font-size:3rem; color:var(--tblr-danger)"></i></div>
                            <p class="empty-title">Link Tidak Valid</p>
                            <p class="empty-subtitle text-secondary">Link ini tidak valid atau sudah kadaluarsa.</p>
                        </div>
                    </div>
                </div>

            <?php elseif ($success): ?>
                <div class="card card-md">
                    <div class="card-body">
                        <div class="empty">
                            <div class="empty-icon"><i class="ti ti-circle-check"
                                    style="font-size:3rem; color:var(--tblr-success)"></i></div>
                            <p class="empty-title text-success">Update Terkirim!</p>
                            <p class="empty-subtitle text-secondary">Update untuk ticket
                                <strong><?= e($ticket['ticket_code']) ?></strong> berhasil dikirim.</p>
                            <div class="empty-action"><a href="<?= url('/wa?token=' . urlencode($token)) ?>"
                                    class="btn btn-primary">Kirim Lagi</a></div>
                        </div>
                    </div>
                </div>

            <?php else: ?>
                <div class="card card-md">
                    <div class="card-status-top bg-success"></div>
                    <div class="card-header">
                        <h3 class="card-title"><?= e($ticket['ticket_code']) ?></h3>
                        <div class="card-actions"><?= statusBadge($ticket['status']) ?></div>
                    </div>
                    <div class="card-body">
                        <p class="text-secondary mb-3"><?= e($ticket['subject']) ?></p>
                        <form method="POST" action="<?= url('/wa?token=' . urlencode($token)) ?>">
                            <div class="mb-3">
                                <label class="form-label">Kirim Update / Komentar</label>
                                <textarea name="message" class="form-control" rows="4" required
                                    placeholder="Tulis update Anda..."></textarea>
                            </div>
                            <div class="form-footer"><button type="submit" class="btn btn-success w-100"><i
                                        class="ti ti-send me-1"></i> Kirim Update</button></div>
                        </form>
                    </div>
                </div>
            <?php endif; ?>
            <div class="text-center text-secondary mt-3">&copy; <?= date('Y') ?> <?= e(APP_NAME) ?></div>
        </div>
    </div>
</body>

</html>