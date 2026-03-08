<?php
/**
 * pages/maintenance/index.php — Maintenance Reports list with View/Edit/Export
 */
requireRole('admin', 'staff');

$pageTitle = 'Laporan Maintenance';
$pagePretitle = 'Maintenance';

// Filters
$filterStatus = $_GET['status'] ?? '';
$filterItem = trim($_GET['item'] ?? '');
$search = trim($_GET['q'] ?? '');
$page = max(1, (int) ($_GET['page'] ?? 1));
$perPage = 20;

$where = ["m.is_active = 1"];
$params = [];

if ($filterStatus && $filterStatus !== 'all') {
    $where[] = "m.status = ?";
    $params[] = $filterStatus;
}
if ($filterItem) {
    $where[] = "m.item = ?";
    $params[] = $filterItem;
}
if ($search) {
    $where[] = "(m.pelapor LIKE ? OR m.dealer LIKE ? OR m.laporan_awal LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

$whereSQL = 'WHERE ' . implode(' AND ', $where);

// ── Export CSV ──
// ── Export CSV ──
if (isset($_GET['export']) && $_GET['export'] === 'csv') {
    $exportStmt = db()->prepare("
        SELECT m.*, u.full_name as technician_name 
        FROM maintenance_reports m 
        LEFT JOIN users u ON m.technician_id = u.id 
        $whereSQL 
        ORDER BY m.tanggal DESC, m.id DESC
    ");
    $exportStmt->execute($params);
    $exportRows = $exportStmt->fetchAll();

    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="laporan_maintenance_' . date('Y-m-d') . '.csv"');
    $out = fopen('php://output', 'w');
    // BOM for Excel
    fprintf($out, chr(0xEF) . chr(0xBB) . chr(0xBF));
    fputcsv($out, ['No', 'Tanggal', 'Pelapor', 'Dealer', 'Item', 'Status', 'Laporan Awal', 'Pengecekan', 'Solusi', 'Lead Time', 'Teknisi']);
    foreach ($exportRows as $i => $row) {
        fputcsv($out, [
            $i + 1,
            $row['tanggal'],
            $row['pelapor'],
            $row['dealer'],
            $row['item'],
            $row['status'],
            $row['laporan_awal'],
            $row['pengecekan'],
            $row['solusi'],
            $row['lead_time'] ?? '',
            $row['technician_name'] ?? ''
        ]);
    }
    fclose($out);
    exit;
}

$countStmt = db()->prepare("SELECT COUNT(*) FROM maintenance_reports m $whereSQL");
$countStmt->execute($params);
$total = $countStmt->fetchColumn();
$totalPages = max(1, ceil($total / $perPage));
$offset = ($page - 1) * $perPage;

$sql = "
    SELECT m.*, u.full_name as technician_name 
    FROM maintenance_reports m 
    LEFT JOIN users u ON m.technician_id = u.id 
    $whereSQL 
    ORDER BY m.tanggal DESC, m.id DESC 
    LIMIT $perPage OFFSET $offset
";
$stmt = db()->prepare($sql);
$stmt->execute($params);
$reports = $stmt->fetchAll();

$itemsStmt = db()->query("SELECT DISTINCT item FROM maintenance_reports WHERE is_active = 1 ORDER BY item");
$itemCategories = $itemsStmt->fetchAll(PDO::FETCH_COLUMN);

// Stats
$totalOpen = db()->query("SELECT COUNT(*) FROM maintenance_reports WHERE status = 'Open' AND is_active = 1")->fetchColumn();
$totalInProgress = db()->query("SELECT COUNT(*) FROM maintenance_reports WHERE status = 'In Progress' AND is_active = 1")->fetchColumn();
$totalClosed = db()->query("SELECT COUNT(*) FROM maintenance_reports WHERE status = 'Closed' AND is_active = 1")->fetchColumn();

$pageActions = '<a href="' . url('/maintenance/create') . '" class="btn btn-primary d-none d-sm-inline-block"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon"><path d="M12 5l0 14"/><path d="M5 12l14 0"/></svg> Laporan Baru</a>';

ob_start();
?>
<!-- Stat Cards -->
<div class="row row-deck row-cards mb-3">
    <div class="col-sm-4 col-lg-4">
        <a href="?status=Open" class="card card-link">
            <div class="card-body">
                <div class="d-flex align-items-center"><div class="subheader">OPEN</div></div>
                <div class="d-flex align-items-baseline">
                    <div class="h1 mb-0 me-2"><?= $totalOpen ?></div>
                    <div class="me-auto"><span class="text-yellow d-inline-flex align-items-center lh-1">Menunggu</span></div>
                </div>
            </div>
        </a>
    </div>
    <div class="col-sm-4 col-lg-4">
        <a href="?status=In+Progress" class="card card-link">
            <div class="card-body">
                <div class="d-flex align-items-center"><div class="subheader">IN PROGRESS</div></div>
                <div class="d-flex align-items-baseline">
                    <div class="h1 mb-0 me-2"><?= $totalInProgress ?></div>
                    <div class="me-auto"><span class="text-blue d-inline-flex align-items-center lh-1">Dikerjakan</span></div>
                </div>
            </div>
        </a>
    </div>
    <div class="col-sm-4 col-lg-4">
        <a href="?status=Closed" class="card card-link">
            <div class="card-body">
                <div class="d-flex align-items-center"><div class="subheader">CLOSED</div></div>
                <div class="d-flex align-items-baseline">
                    <div class="h1 mb-0 me-2"><?= $totalClosed ?></div>
                    <div class="me-auto"><span class="text-green d-inline-flex align-items-center lh-1">Selesai</span></div>
                </div>
            </div>
        </a>
    </div>
</div>

<!-- Filters -->
<div class="card mb-3">
    <div class="card-body">
        <form method="GET" class="row g-3 align-items-end">
            <div class="col-md-3">
                <label class="form-label">Cari</label>
                <div class="input-icon">
                    <span class="input-icon-addon"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon"><path d="M10 10m-7 0a7 7 0 1 0 14 0a7 7 0 1 0 -14 0"/><path d="M21 21l-6 -6"/></svg></span>
                    <input type="text" name="q" class="form-control" placeholder="Pelapor / Dealer / Laporan..." value="<?= e($search) ?>">
                </div>
            </div>
            <div class="col-md-2">
                <label class="form-label">Status</label>
                <select name="status" class="form-select">
                    <option value="all">Semua</option>
                    <option value="Open" <?= $filterStatus === 'Open' ? 'selected' : '' ?>>Open</option>
                    <option value="In Progress" <?= $filterStatus === 'In Progress' ? 'selected' : '' ?>>In Progress</option>
                    <option value="Closed" <?= $filterStatus === 'Closed' ? 'selected' : '' ?>>Closed</option>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">Item</label>
                <select name="item" class="form-select">
                    <option value="">Semua</option>
                    <?php foreach ($itemCategories as $ic): ?>
                    <option value="<?= e($ic) ?>" <?= $filterItem === $ic ? 'selected' : '' ?>><?= e($ic) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-auto">
                <button type="submit" class="btn btn-primary">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon"><path d="M10 10m-7 0a7 7 0 1 0 14 0a7 7 0 1 0 -14 0"/><path d="M21 21l-6 -6"/></svg>
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
    <div class="card-header"><h3 class="card-title"><?= $total ?> laporan ditemukan</h3></div>
    <div class="table-responsive">
        <table class="table table-vcenter card-table table-striped">
            <thead>
                <tr>
                    <th>No</th><th>Tanggal</th><th>Pelapor</th><th>Dealer</th><th>Laporan</th><th>Item</th><th>Teknisi</th><th>Status</th><th>Lead Time</th><th class="w-1">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($reports)): ?>
                    <tr><td colspan="10" class="text-center text-secondary py-4">Tidak ada laporan ditemukan.</td></tr>
                <?php else: ?>
                    <?php foreach ($reports as $r): ?>
                    <tr>
                        <td class="text-secondary"><?= $r['id'] ?></td>
                        <td class="text-nowrap"><?= date('d M Y', strtotime($r['tanggal'])) ?></td>
                        <td><?= e($r['pelapor']) ?></td>
                        <td><?= e($r['dealer']) ?></td>
                        <td><span class="text-truncate d-inline-block" style="max-width:200px"><?= e($r['laporan_awal']) ?></span></td>
                        <td><span class="badge bg-azure-lt"><?= e($r['item']) ?></span></td>
                        <td><?= e($r['technician_name'] ?? '-') ?></td>
                        <td>
                            <?php $statusColor = match ($r['status']) { 'Open' => 'yellow', 'In Progress' => 'blue', 'Closed' => 'green', default => 'secondary'}; ?>
                            <span class="badge bg-<?= $statusColor ?>-lt"><?= e($r['status']) ?></span>
                        </td>
                        <td class="text-secondary"><?= $r['lead_time'] ? substr($r['lead_time'], 0, 5) : '—' ?></td>
                        <td>
                            <span class="dropdown">
                                <button class="btn btn-sm dropdown-toggle align-text-top" data-bs-boundary="viewport" data-bs-toggle="dropdown">Aksi</button>
                                <div class="dropdown-menu dropdown-menu-end">
                                    <a class="dropdown-item" href="<?= url('/maintenance/view?id=' . $r['id']) ?>">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon me-2"><path d="M10 12a2 2 0 1 0 4 0a2 2 0 0 0 -4 0"/><path d="M21 12c-2.4 4 -5.4 6 -9 6c-3.6 0 -6.6 -2 -9 -6c2.4 -4 5.4 -6 9 -6c3.6 0 6.6 2 9 6"/></svg>
                                        Lihat Detail
                                    </a>
                                    <a class="dropdown-item" href="<?= url('/maintenance/edit?id=' . $r['id']) ?>">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon me-2"><path d="M7 7h-1a2 2 0 0 0 -2 2v9a2 2 0 0 0 2 2h9a2 2 0 0 0 2 -2v-1"/><path d="M20.385 6.585a2.1 2.1 0 0 0 -2.97 -2.97l-8.415 8.385v3h3l8.385 -8.415z"/><path d="M16 5l3 3"/></svg>
                                        Edit
                                    </a>
                                </div>
                            </span>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    <?php if ($totalPages > 1): ?>
    <div class="card-footer d-flex align-items-center">
        <p class="m-0 text-secondary">Showing <?= $offset + 1 ?> to <?= min($offset + $perPage, $total) ?> of <?= $total ?></p>
        <ul class="pagination m-0 ms-auto">
            <li class="page-item <?= $page <= 1 ? 'disabled' : '' ?>"><a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['page' => $page - 1])) ?>"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon"><path d="M15 6l-6 6l6 6"/></svg> prev</a></li>
            <?php for ($i = max(1, $page - 2); $i <= min($totalPages, $page + 2); $i++): ?>
            <li class="page-item <?= $i === $page ? 'active' : '' ?>"><a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['page' => $i])) ?>"><?= $i ?></a></li>
            <?php endfor; ?>
            <li class="page-item <?= $page >= $totalPages ? 'disabled' : '' ?>"><a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['page' => $page + 1])) ?>">next <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon"><path d="M9 6l6 6l-6 6"/></svg></a></li>
        </ul>
    </div>
    <?php endif; ?>
</div>
<?php
$content = ob_get_clean();
include BASE_PATH . '/layouts/main.php';
