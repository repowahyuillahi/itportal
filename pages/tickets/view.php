<?php
/**
 * pages/tickets/view.php — Ticket detail (Tabler card layout)
 */
$ticketId = (int) ($_GET['id'] ?? 0);
if (!$ticketId) {
    redirect(url('/tickets'));
}

$stmt = db()->prepare("SELECT t.*, u.full_name AS creator_name, st.full_name AS assigned_name, a.asset_code, a.name AS asset_name
    FROM tickets t
    LEFT JOIN users u ON t.user_id = u.id
    LEFT JOIN users st ON t.assigned_to = st.id
    LEFT JOIN assets a ON t.asset_id = a.id
    WHERE t.id = ?");
$stmt->execute([$ticketId]);
$ticket = $stmt->fetch();
if (!$ticket) {
    flash('error', 'Ticket tidak ditemukan.');
    redirect(url('/tickets'));
}

// Check access
if (hasRole('user', 'dealer') && !hasRole('admin', 'staff') && $ticket['user_id'] !== (int) $_SESSION['user_id']) {
    redirect(url('/errors/403'));
}

$pageTitle = $ticket['ticket_code'];
$pagePretitle = 'Ticket Detail';

// Messages
$msgStmt = db()->prepare("SELECT m.*, u.full_name FROM ticket_messages m LEFT JOIN users u ON m.user_id = u.id WHERE m.ticket_id = ? ORDER BY m.created_at ASC");
$msgStmt->execute([$ticketId]);
$messages = $msgStmt->fetchAll();

// Attachments
$attStmt = db()->prepare("SELECT * FROM ticket_attachments WHERE ticket_id = ?");
$attStmt->execute([$ticketId]);
$attachments = $attStmt->fetchAll();

// Staff list for assign
$staffList = getStaffUsers();

// Tracking tokens
$tokenStmt = db()->prepare("SELECT * FROM ticket_tokens WHERE ticket_id = ? AND revoked_at IS NULL ORDER BY created_at DESC");
$tokenStmt->execute([$ticketId]);
$tokens = $tokenStmt->fetchAll();

$pageActions = '<a href="' . url('/tickets') . '" class="btn btn-outline-secondary d-none d-sm-inline-block"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon"><path d="M9 14l-4 -4l4 -4"/><path d="M5 10h11a4 4 0 1 1 0 8h-1"/></svg> Kembali</a>';
if (hasRole('admin', 'staff')) {
    $pageActions .= ' <a href="' . url('/tickets/edit?id=' . $ticketId) . '" class="btn btn-primary d-none d-sm-inline-block"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon"><path d="M7 7h-1a2 2 0 0 0 -2 2v9a2 2 0 0 0 2 2h9a2 2 0 0 0 2 -2v-1"/><path d="M20.385 6.585a2.1 2.1 0 0 0 -2.97 -2.97l-8.415 8.385v3h3l8.385 -8.415z"/><path d="M16 5l3 3"/></svg> Edit</a>';
}

ob_start();
?>
<div class="row row-deck row-cards">
    <!-- Left Column: Ticket Info -->
    <div class="col-lg-4">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Informasi Ticket</h3>
            </div>
            <div class="card-body">
                <div class="datagrid">
                    <div class="datagrid-item">
                        <div class="datagrid-title">Status</div>
                        <div class="datagrid-content"><?= statusBadge($ticket['status']) ?></div>
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
                        <div class="datagrid-title">Subject</div>
                        <div class="datagrid-content"><?= e($ticket['subject']) ?></div>
                    </div>
                    <div class="datagrid-item">
                        <div class="datagrid-title">Terkait Aset</div>
                        <div class="datagrid-content">
                            <?php if ($ticket['asset_code']): ?>
                                <a href="<?= url('/it-assets?q=' . urlencode($ticket['asset_code'])) ?>" class="badge bg-purple-lt text-decoration-none">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon text-purple me-1"><path d="M4 7v-1a2 2 0 0 1 2 -2h2"/><path d="M4 17v1a2 2 0 0 0 2 2h2"/><path d="M16 4h2a2 2 0 0 1 2 2v1"/><path d="M16 20h2a2 2 0 0 0 2 -2v-1"/><path d="M5 11h1v2h-1z"/><path d="M10 11l0 2"/><path d="M14 11h1v2h-1z"/><path d="M19 11l0 2"/></svg>
                                    <?= e($ticket['asset_name']) ?> (<?= e($ticket['asset_code']) ?>)
                                </a>
                            <?php else: ?>
                                <span class="text-secondary">—</span>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="datagrid-item">
                        <div class="datagrid-title">Dibuat oleh</div>
                        <div class="datagrid-content">
                            <div class="d-flex align-items-center">
                                <span class="avatar avatar-xs me-2"
                                    style="background-image: url(https://ui-avatars.com/api/?name=<?= urlencode($ticket['creator_name'] ?? 'U') ?>&size=24&background=0054a6&color=fff)"></span>
                                <?= e($ticket['creator_name'] ?? '-') ?>
                            </div>
                        </div>
                    </div>
                    <div class="datagrid-item">
                        <div class="datagrid-title">Assigned</div>
                        <div class="datagrid-content"><?= e($ticket['assigned_name'] ?? 'Belum di-assign') ?></div>
                    </div>
                    <div class="datagrid-item">
                        <div class="datagrid-title">Dibuat</div>
                        <div class="datagrid-content"><?= formatDate($ticket['created_at']) ?></div>
                    </div>
                    <div class="datagrid-item">
                        <div class="datagrid-title">Diupdate</div>
                        <div class="datagrid-content"><?= formatDate($ticket['updated_at']) ?></div>
                    </div>
                </div>
            </div>
        </div>

        <?php if (hasRole('admin', 'staff')): ?>
            <!-- Actions Card -->
            <div class="card mt-3">
                <div class="card-header">
                    <h3 class="card-title">Aksi</h3>
                </div>
                <div class="card-body">
                    <!-- Assign -->
                    <form method="POST" action="<?= url('/tickets/actions/assign') ?>" class="mb-3">
                        <?= csrfField() ?>
                        <input type="hidden" name="ticket_id" value="<?= $ticketId ?>">
                        <label class="form-label">Assign ke Staff</label>
                        <div class="row g-2">
                            <div class="col"><select name="assigned_to" class="form-select form-select-sm">
                                    <option value="">-- Pilih --</option>
                                    <?php foreach ($staffList as $s): ?>
                                        <option value="<?= $s['id'] ?>" <?= $ticket['assigned_to'] == $s['id'] ? 'selected' : '' ?>><?= e($s['full_name']) ?></option>
                                    <?php endforeach; ?>
                                </select></div>
                            <div class="col-auto"><button class="btn btn-sm btn-primary">Assign</button></div>
                        </div>
                    </form>
                    <!-- Status Update -->
                    <form method="POST" action="<?= url('/tickets/actions/update-status') ?>">
                        <?= csrfField() ?>
                        <input type="hidden" name="ticket_id" value="<?= $ticketId ?>">
                        <label class="form-label">Update Status</label>
                        <div class="row g-2">
                            <div class="col"><select name="status" class="form-select form-select-sm">
                                    <?php foreach (['open', 'in_progress', 'waiting', 'resolved', 'closed'] as $s): ?>
                                        <option value="<?= $s ?>" <?= $ticket['status'] === $s ? 'selected' : '' ?>>
                                            <?= ucfirst(str_replace('_', ' ', $s)) ?></option>
                                    <?php endforeach; ?>
                                </select></div>
                            <div class="col-auto"><button class="btn btn-sm btn-warning">Update</button></div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Tracking Links -->
            <div class="card mt-3">
                <div class="card-header">
                    <h3 class="card-title">Tracking Links</h3>
                </div>
                <div class="card-body">
                    <?php if (empty($tokens)): ?>
                        <p class="text-secondary small">Belum ada tracking token aktif.</p>
                    <?php else: ?>
                        <?php foreach ($tokens as $tk): ?>
                            <div class="mb-2">
                                <span
                                    class="badge bg-<?= $tk['type'] === 'public_track' ? 'blue' : 'green' ?>-lt"><?= e($tk['type']) ?></span>
                                <div class="mt-1">
                                    <code
                                        class="small"><?= e(url(($tk['type'] === 'public_track' ? '/track' : '/wa') . '?token=' . $tk['token'])) ?></code>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                    <?php
                    $waLink = generateWALink($ticket['ticket_code'], $tokens[0]['token'] ?? '');
                    if ($waLink): ?>
                        <a href="<?= e($waLink) ?>" target="_blank" class="btn btn-sm btn-success mt-2 w-100">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                                stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                class="icon">
                                <path d="M3 21l1.65 -3.8a9 9 0 1 1 3.4 2.9l-5.05 .9" />
                                <path
                                    d="M9 10a.5 .5 0 0 0 1 0v-1a.5 .5 0 0 0 -1 0v1a5 5 0 0 0 5 5h1a.5 .5 0 0 0 0 -1h-1a.5 .5 0 0 0 0 1" />
                            </svg>
                            Kirim via WhatsApp
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>

        <!-- Attachments -->
        <?php if (!empty($attachments)): ?>
            <div class="card mt-3">
                <div class="card-header">
                    <h3 class="card-title">Lampiran</h3>
                </div>
                <div class="list-group list-group-flush">
                    <?php foreach ($attachments as $att): ?>
                        <a href="<?= e($att['file_path']) ?>" target="_blank"
                            class="list-group-item list-group-item-action d-flex align-items-center">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                                stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                class="icon me-2">
                                <path d="M14 3v4a1 1 0 0 0 1 1h4" />
                                <path d="M17 21h-10a2 2 0 0 1 -2 -2v-14a2 2 0 0 1 2 -2h7l5 5v11a2 2 0 0 1 -2 2z" />
                            </svg>
                            <div>
                                <div><?= e($att['original_name']) ?></div>
                                <div class="text-secondary small"><?= number_format($att['size'] / 1024, 1) ?> KB</div>
                            </div>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <!-- Right Column: Timeline + Reply -->
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                        class="icon me-1">
                        <path
                            d="M3 20l1.3 -3.9c-2.324 -3.437 -1.426 -7.872 2.1 -10.374c3.526 -2.501 8.59 -2.296 11.845 .48c3.255 2.777 3.695 7.266 1.029 10.501c-2.666 3.235 -7.615 4.215 -11.574 2.293l-4.7 1" />
                    </svg>
                    Timeline
                </h3>
            </div>
            <div class="card-body">
                <?php if (empty($messages)): ?>
                    <p class="text-secondary text-center py-4">Belum ada pesan.</p>
                <?php else: ?>
                    <div class="divide-y">
                        <?php foreach ($messages as $m): ?>
                            <div class="py-3 <?= $m['is_internal'] ? 'bg-yellow-lt px-3 rounded' : '' ?>">
                                <div class="row align-items-center">
                                    <div class="col-auto">
                                        <span class="avatar avatar-sm"
                                            style="background-image: url(https://ui-avatars.com/api/?name=<?= urlencode($m['full_name'] ?? 'S') ?>&size=32&background=<?= $m['is_internal'] ? 'f59f00' : '0054a6' ?>&color=fff)"></span>
                                    </div>
                                    <div class="col">
                                        <div class="text-truncate">
                                            <strong><?= e($m['full_name'] ?? 'System') ?></strong>
                                            <?php if ($m['is_internal']): ?><span
                                                    class="badge bg-yellow-lt ms-1">Internal</span><?php endif; ?>
                                        </div>
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

        <!-- Reply Form -->
        <div class="card mt-3">
            <div class="card-header">
                <h3 class="card-title">Balas</h3>
            </div>
            <div class="card-body">
                <form method="POST" action="<?= url('/tickets/actions/reply') ?>" enctype="multipart/form-data">
                    <?= csrfField() ?>
                    <input type="hidden" name="ticket_id" value="<?= $ticketId ?>">
                    <div class="mb-3">
                        <textarea name="message" class="form-control" rows="4" placeholder="Tulis balasan..."
                            required></textarea>
                    </div>
                    <div class="row align-items-center">
                        <div class="col">
                            <input type="file" name="attachment" class="form-control form-control-sm">
                        </div>
                        <?php if (hasRole('admin', 'staff')): ?>
                            <div class="col-auto">
                                <label class="form-check form-check-inline m-0">
                                    <input name="is_internal" type="checkbox" class="form-check-input" value="1">
                                    <span class="form-check-label">Internal Note</span>
                                </label>
                            </div>
                        <?php endif; ?>
                        <div class="col-auto">
                            <button type="submit" class="btn btn-primary">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                    fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                    stroke-linejoin="round" class="icon">
                                    <path d="M10 14l11 -11" />
                                    <path
                                        d="M21 3l-6.5 18a.55 .55 0 0 1 -1 0l-3.5 -7l-7 -3.5a.55 .55 0 0 1 0 -1l18 -6.5" />
                                </svg>
                                Kirim
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<?php
$content = ob_get_clean();
include BASE_PATH . '/layouts/main.php';
