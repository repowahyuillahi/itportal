<?php
/**
 * pages/maintenance/view.php — Maintenance Report detail (Tabler datagrid)
 */
requireRole('admin', 'staff');

$id = (int) ($_GET['id'] ?? 0);
if (!$id) {
    redirect(url('/maintenance'));
}

$stmt = db()->prepare("SELECT * FROM maintenance_reports WHERE id = ? AND is_active = 1");
$stmt->execute([$id]);
$r = $stmt->fetch();
if (!$r) {
    flash('error', 'Laporan tidak ditemukan.');
    redirect(url('/maintenance'));
}

$pageTitle = 'Detail Laporan #' . $r['id'];
$pagePretitle = 'Maintenance';

$statusColor = match ($r['status']) { 'Open' => 'yellow', 'In Progress' => 'blue', 'Closed' => 'green', default => 'secondary'};

$pageActions = '<a href="' . url('/maintenance') . '" class="btn btn-outline-secondary d-none d-sm-inline-block"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon"><path d="M9 14l-4 -4l4 -4"/><path d="M5 10h11a4 4 0 1 1 0 8h-1"/></svg> Kembali</a>';
$pageActions .= ' <a href="' . url('/maintenance/edit?id=' . $id) . '" class="btn btn-primary d-none d-sm-inline-block"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon"><path d="M7 7h-1a2 2 0 0 0 -2 2v9a2 2 0 0 0 2 2h9a2 2 0 0 0 2 -2v-1"/><path d="M20.385 6.585a2.1 2.1 0 0 0 -2.97 -2.97l-8.415 8.385v3h3l8.385 -8.415z"/><path d="M16 5l3 3"/></svg> Edit</a>';

ob_start();
?>
<div class="row row-deck row-cards">
    <div class="col-lg-4">
        <div class="card">
            <div class="card-status-top bg-<?= $statusColor ?>"></div>
            <div class="card-header">
                <h3 class="card-title">Informasi Laporan</h3>
                <div class="card-actions"><span class="badge bg-<?= $statusColor ?>-lt">
                        <?= e($r['status']) ?>
                    </span></div>
            </div>
            <div class="card-body">
                <div class="datagrid">
                    <div class="datagrid-item">
                        <div class="datagrid-title">Tanggal</div>
                        <div class="datagrid-content">
                            <?= date('d M Y', strtotime($r['tanggal'])) ?>
                        </div>
                    </div>
                    <div class="datagrid-item">
                        <div class="datagrid-title">Pelapor</div>
                        <div class="datagrid-content">
                            <?= e($r['pelapor']) ?>
                        </div>
                    </div>
                    <div class="datagrid-item">
                        <div class="datagrid-title">Dealer</div>
                        <div class="datagrid-content">
                            <?= e($r['dealer']) ?>
                        </div>
                    </div>
                    <div class="datagrid-item">
                        <div class="datagrid-title">Item</div>
                        <div class="datagrid-content"><span class="badge bg-azure-lt">
                                <?= e($r['item']) ?>
                            </span></div>
                    </div>
                    <div class="datagrid-item">
                        <div class="datagrid-title">Waktu Mulai</div>
                        <div class="datagrid-content">
                            <?= $r['waktu_mulai'] ? date('d M Y H:i', strtotime($r['waktu_mulai'])) : '—' ?>
                        </div>
                    </div>
                    <div class="datagrid-item">
                        <div class="datagrid-title">Waktu Selesai</div>
                        <div class="datagrid-content">
                            <?= $r['waktu_selesai'] ? date('d M Y H:i', strtotime($r['waktu_selesai'])) : '—' ?>
                        </div>
                    </div>
                    <div class="datagrid-item">
                        <div class="datagrid-title">Lead Time</div>
                        <div class="datagrid-content"><strong>
                                <?= $r['lead_time'] ? substr($r['lead_time'], 0, 5) : '—' ?>
                            </strong></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Detail Laporan</h3>
            </div>
            <div class="card-body">
                <div class="mb-4">
                    <h4 class="text-primary">Laporan Awal</h4>
                    <p>
                        <?= nl2br(e($r['laporan_awal'])) ?>
                    </p>
                </div>
                <?php if ($r['pengecekan']): ?>
                    <div class="mb-4">
                        <h4 class="text-warning">Pengecekan</h4>
                        <p>
                            <?= nl2br(e($r['pengecekan'])) ?>
                        </p>
                    </div>
                <?php endif; ?>
                <?php if ($r['solusi']): ?>
                    <div class="mb-4">
                        <h4 class="text-success">Solusi</h4>
                        <p>
                            <?= nl2br(e($r['solusi'])) ?>
                        </p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
<?php
$content = ob_get_clean();
include BASE_PATH . '/layouts/main.php';
