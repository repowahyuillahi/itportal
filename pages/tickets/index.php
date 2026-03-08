<?php
/**
 * pages/tickets/index.php — Ticket listing (Tabler card-table style)
 */
$pageTitle = 'Semua Ticket';
$pagePretitle = 'Tickets';

// Filters
$filterStatus = $_GET['status'] ?? '';
$filterPriority = $_GET['priority'] ?? '';
$search = trim($_GET['q'] ?? '');
$page = max(1, (int) ($_GET['page'] ?? 1));
$perPage = 15;

// Build query
$where = [];
$params = [];

// Role-based visibility
if (hasRole('user', 'dealer') && !hasRole('admin', 'staff')) {
    $where[] = "t.user_id = ?";
    $params[] = $_SESSION['user_id'];
}

if ($filterStatus && $filterStatus !== 'all') {
    $where[] = "t.status = ?";
    $params[] = $filterStatus;
}
if ($filterPriority) {
    $where[] = "t.priority = ?";
    $params[] = $filterPriority;
}
if ($search) {
    $where[] = "(t.ticket_code LIKE ? OR t.subject LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

$whereSQL = $where ? 'WHERE ' . implode(' AND ', $where) : '';

// ── Export CSV ──
if (isset($_GET['export']) && $_GET['export'] === 'csv') {
    $exportSql = "SELECT t.*, u.full_name AS creator_name, a.full_name AS assigned_name
        FROM tickets t
        LEFT JOIN users u ON t.user_id = u.id
        LEFT JOIN users a ON t.assigned_to = a.id
        $whereSQL
        ORDER BY t.created_at DESC";
    $exportStmt = db()->prepare($exportSql);
    $exportStmt->execute($params);
    $exportRows = $exportStmt->fetchAll();

    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="data_tickets_' . date('Y-m-d') . '.csv"');
    $out = fopen('php://output', 'w');
    // BOM for Excel
    fprintf($out, chr(0xEF) . chr(0xBB) . chr(0xBF));
    fputcsv($out, ['Kode', 'Subject', 'Kategori', 'Status', 'Prioritas', 'Dibuat Oleh', 'Assigned', 'Tanggal Dibuat', 'Tanggal Selesai']);
    foreach ($exportRows as $row) {
        fputcsv($out, [
            $row['ticket_code'],
            $row['subject'],
            $row['category'],
            $row['status'],
            $row['priority'],
            $row['creator_name'] ?? 'Unknown',
            $row['assigned_name'] ?? 'Belum di-assign',
            $row['created_at'],
            $row['closed_at'] ?? ''
        ]);
    }
    fclose($out);
    exit;
}

// Count
$countStmt = db()->prepare("SELECT COUNT(*) FROM tickets t $whereSQL");
$countStmt->execute($params);
$total = $countStmt->fetchColumn();
$totalPages = max(1, ceil($total / $perPage));
$offset = ($page - 1) * $perPage;

// Fetch
$sql = "SELECT t.*, u.full_name AS creator_name, a.full_name AS assigned_name
    FROM tickets t
    LEFT JOIN users u ON t.user_id = u.id
    LEFT JOIN users a ON t.assigned_to = a.id
    $whereSQL
    ORDER BY t.created_at DESC LIMIT $perPage OFFSET $offset";
$stmt = db()->prepare($sql);
$stmt->execute($params);
$tickets = $stmt->fetchAll();

ob_start();
?>

<!-- Filters -->
<div class="card mb-3">
    <div class="card-body">
        <form method="GET" class="row g-3 align-items-end">
            <div class="col-md-3">
                <label class="form-label">Cari</label>
                <div class="input-icon">
                    <span class="input-icon-addon">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                            stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                            class="icon">
                            <path d="M10 10m-7 0a7 7 0 1 0 14 0a7 7 0 1 0 -14 0" />
                            <path d="M21 21l-6 -6" />
                        </svg>
                    </span>
                    <input type="text" name="q" class="form-control" placeholder="Kode / Subject..."
                        value="<?= e($search) ?>">
                </div>
            </div>
            <div class="col-md-2">
                <label class="form-label">Status</label>
                <select name="status" class="form-select">
                    <option value="all">Semua</option>
                    <?php foreach (['open', 'in_progress', 'waiting', 'resolved', 'closed'] as $s): ?>
                        <option value="<?= $s ?>" <?= $filterStatus === $s ? 'selected' : '' ?>>
                            <?= ucfirst(str_replace('_', ' ', $s)) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">Prioritas</label>
                <select name="priority" class="form-select">
                    <option value="">Semua</option>
                    <?php foreach (['low', 'medium', 'high', 'urgent'] as $p): ?>
                        <option value="<?= $p ?>" <?= $filterPriority === $p ? 'selected' : '' ?>><?= ucfirst($p) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-auto">
                <button type="submit" class="btn btn-primary">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                        class="icon">
                        <path d="M10 10m-7 0a7 7 0 1 0 14 0a7 7 0 1 0 -14 0" />
                        <path d="M21 21l-6 -6" />
                    </svg>
                    Filter
                </button>
            </div>
            <div class="col-auto">
                <a href="?<?= http_build_query(array_merge($_GET, ['export' => 'csv'])) ?>" class="btn btn-outline-green">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon"><path d="M14 3v4a1 1 0 0 0 1 1h4"/><path d="M17 21h-10a2 2 0 0 1 -2 -2v-14a2 2 0 0 1 2 -2h7l5 5v11a2 2 0 0 1 -2 2z"/><path d="M12 17v-6"/><path d="M9.5 14.5l2.5 2.5l2.5 -2.5"/></svg>
                    Export CSV
                </a>
            </div>
        </form>
    </div>
</div>

<!-- Results -->
<div class="card">
    <div class="card-header">
        <h3 class="card-title"><?= $total ?> ticket ditemukan</h3>
    </div>
    <div class="table-responsive">
        <table class="table table-vcenter card-table table-striped">
            <thead>
                <tr>
                    <th>Kode</th>
                    <th>Subject</th>
                    <th>Status</th>
                    <th>Prioritas</th>
                    <th>Dibuat oleh</th>
                    <th>Assigned</th>
                    <th>Tanggal</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($tickets)): ?>
                    <tr>
                        <td colspan="7" class="text-center text-secondary py-4">Tidak ada ticket ditemukan.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($tickets as $t): ?>
                        <tr class="cursor-pointer" onclick="window.location='<?= url('/tickets/view?id=' . $t['id']) ?>'">
                            <td class="text-nowrap"><span class="text-primary fw-bold"><?= e($t['ticket_code']) ?></span></td>
                            <td><?= e($t['subject']) ?></td>
                            <td><?= statusBadge($t['status']) ?></td>
                            <td><?= priorityBadge($t['priority']) ?></td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <span class="avatar avatar-xs me-2"
                                        style="background-image: url(https://ui-avatars.com/api/?name=<?= urlencode($t['creator_name'] ?? 'U') ?>&size=24&background=0054a6&color=fff)"></span>
                                    <?= e($t['creator_name'] ?? '-') ?>
                                </div>
                            </td>
                            <td><?= e($t['assigned_name'] ?? '—') ?></td>
                            <td class="text-secondary text-nowrap"><?= formatDate($t['created_at'], 'd M Y') ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    <?php if ($totalPages > 1): ?>
        <div class="card-footer d-flex align-items-center">
            <p class="m-0 text-secondary">Showing <span><?= $offset + 1 ?></span> to
                <span><?= min($offset + $perPage, $total) ?></span> of <span><?= $total ?></span> entries</p>
            <ul class="pagination m-0 ms-auto">
                <li class="page-item <?= $page <= 1 ? 'disabled' : '' ?>">
                    <a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['page' => $page - 1])) ?>">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                            stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                            class="icon">
                            <path d="M15 6l-6 6l6 6" />
                        </svg>
                        prev
                    </a>
                </li>
                <?php for ($i = max(1, $page - 2); $i <= min($totalPages, $page + 2); $i++): ?>
                    <li class="page-item <?= $i === $page ? 'active' : '' ?>"><a class="page-link"
                            href="?<?= http_build_query(array_merge($_GET, ['page' => $i])) ?>"><?= $i ?></a></li>
                <?php endfor; ?>
                <li class="page-item <?= $page >= $totalPages ? 'disabled' : '' ?>">
                    <a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['page' => $page + 1])) ?>">
                        next
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                            stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                            class="icon">
                            <path d="M9 6l6 6l-6 6" />
                        </svg>
                    </a>
                </li>
            </ul>
        </div>
    <?php endif; ?>
</div>
<?php
$content = ob_get_clean();
include BASE_PATH . '/layouts/main.php';
