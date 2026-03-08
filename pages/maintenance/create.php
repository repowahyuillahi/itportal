<?php
/**
 * pages/maintenance/create.php — Create Maintenance Report
 */
requireRole('admin', 'staff');

$pageTitle = 'Buat Laporan Maintenance';
$pagePretitle = 'Maintenance';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();
    $tanggal = $_POST['tanggal'] ?? date('Y-m-d');
    $pelapor = trim($_POST['pelapor'] ?? '');
    $dealer = trim($_POST['dealer'] ?? '');
    $laporan_awal = trim($_POST['laporan_awal'] ?? '');
    $pengecekan = trim($_POST['pengecekan'] ?? '');
    $solusi = trim($_POST['solusi'] ?? '');
    $item = trim($_POST['item'] ?? '');
    $status = $_POST['status'] ?? 'Open';
    $waktu_mulai = $_POST['waktu_mulai'] ?: null;
    $waktu_selesai = $_POST['waktu_selesai'] ?: null;

    if ($pelapor === '' || $dealer === '' || $laporan_awal === '' || $item === '') {
        flash('error', 'Pelapor, Dealer, Laporan, dan Item wajib diisi.');
    } else {
        $stmt = db()->prepare("INSERT INTO maintenance_reports (status, tanggal, pelapor, dealer, laporan_awal, pengecekan, solusi, item, waktu_mulai, waktu_selesai, created_by, is_active) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 1)");
        $stmt->execute([$status, $tanggal, $pelapor, $dealer, $laporan_awal, $pengecekan ?: null, $solusi ?: null, $item, $waktu_mulai, $waktu_selesai, $_SESSION['user_id']]);
        flash('success', 'Laporan berhasil dibuat.');
        redirect(url('/maintenance'));
    }
}

// Get dealer list for select
$dealers = db()->query("SELECT nama_dealer, area FROM dealers WHERE is_active = 1 ORDER BY area, nama_dealer")->fetchAll();
$dealersByArea = [];
foreach ($dealers as $d) {
    $dealersByArea[$d['area']][] = $d['nama_dealer'];
}
$items = db()->query("SELECT DISTINCT item FROM maintenance_reports WHERE is_active = 1 ORDER BY item")->fetchAll(PDO::FETCH_COLUMN);

$pageActions = '<a href="' . url('/maintenance') . '" class="btn btn-outline-secondary d-none d-sm-inline-block"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon"><path d="M9 14l-4 -4l4 -4"/><path d="M5 10h11a4 4 0 1 1 0 8h-1"/></svg> Kembali</a>';

ob_start();
?>
<div class="row row-cards">
    <div class="col-12">
        <form method="POST">
            <?= csrfField() ?>
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Detail Laporan</h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label required">Tanggal</label>
                            <input type="date" name="tanggal" class="form-control" value="<?= date('Y-m-d') ?>"
                                required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label required">Pelapor</label>
                            <input type="text" name="pelapor" class="form-control" placeholder="Nama Pelapor" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label required">Dealer</label>
                            <select name="dealer" class="form-select" required>
                                <option value="">-- Pilih Dealer --</option>
                                <?php foreach ($dealersByArea as $area => $names): ?>
                                    <optgroup label="<?= e($area) ?>">
                                        <?php foreach ($names as $nm): ?>
                                            <option value="<?= e($nm) ?>"><?= e($nm) ?></option>
                                        <?php endforeach; ?>
                                    </optgroup>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label required">Item / Kategori</label>
                            <select name="item" class="form-select" required>
                                <option value="">-- Pilih Item --</option>
                                <?php foreach ($items as $it): ?>
                                    <option value="<?= e($it) ?>">
                                        <?= e($it) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Status</label>
                            <select name="status" class="form-select">
                                <option value="Open">Open</option>
                                <option value="In Progress">In Progress</option>
                                <option value="Closed">Closed</option>
                            </select>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label required">Laporan Awal</label>
                        <textarea name="laporan_awal" class="form-control" rows="3" required
                            placeholder="Masalah yang dilaporkan..."></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Pengecekan</label>
                        <textarea name="pengecekan" class="form-control" rows="3"
                            placeholder="Hasil pengecekan..."></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Solusi</label>
                        <textarea name="solusi" class="form-control" rows="3"
                            placeholder="Solusi yang diterapkan..."></textarea>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Waktu Mulai</label>
                            <input type="datetime-local" name="waktu_mulai" class="form-control">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Waktu Selesai</label>
                            <input type="datetime-local" name="waktu_selesai" class="form-control">
                        </div>
                    </div>
                </div>
                <div class="card-footer text-end">
                    <a href="<?= url('/maintenance') ?>" class="btn btn-link">Batal</a>
                    <button type="submit" class="btn btn-primary ms-auto">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                            stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                            class="icon">
                            <path d="M5 12l5 5l10 -10" />
                        </svg>
                        Simpan
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
<?php
$content = ob_get_clean();
include BASE_PATH . '/layouts/main.php';
