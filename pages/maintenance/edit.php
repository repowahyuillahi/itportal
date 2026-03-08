<?php
/**
 * pages/maintenance/edit.php — Edit Maintenance Report
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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();
    $stmt = db()->prepare("UPDATE maintenance_reports SET status=?, tanggal=?, pelapor=?, dealer=?, laporan_awal=?, pengecekan=?, solusi=?, item=?, waktu_mulai=?, waktu_selesai=? WHERE id=?");
    $stmt->execute([
        $_POST['status'],
        $_POST['tanggal'],
        trim($_POST['pelapor']),
        trim($_POST['dealer']),
        trim($_POST['laporan_awal']),
        trim($_POST['pengecekan']) ?: null,
        trim($_POST['solusi']) ?: null,
        trim($_POST['item']),
        $_POST['waktu_mulai'] ?: null,
        $_POST['waktu_selesai'] ?: null,
        $id
    ]);
    if ($stmt->rowCount() > 0 || $stmt->errorCode() === '00000') {
        flash('success', 'Laporan berhasil diupdate.');
    } else {
        flash('error', 'Gagal update: ' . implode(' ', $stmt->errorInfo()));
    }
    redirect(url('/maintenance/view?id=' . $id));
}

$pageTitle = 'Edit Laporan #' . $r['id'];
$pagePretitle = 'Maintenance';

$dealers = db()->query("SELECT nama_dealer, area FROM dealers WHERE is_active = 1 ORDER BY area, nama_dealer")->fetchAll();
$dealersByArea = [];
foreach ($dealers as $d) {
    $dealersByArea[$d['area']][] = $d['nama_dealer'];
}
$items = db()->query("SELECT DISTINCT item FROM maintenance_reports WHERE is_active = 1 ORDER BY item")->fetchAll(PDO::FETCH_COLUMN);

$pageActions = '<a href="' . url('/maintenance/view?id=' . $id) . '" class="btn btn-outline-secondary d-none d-sm-inline-block"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon"><path d="M9 14l-4 -4l4 -4"/><path d="M5 10h11a4 4 0 1 1 0 8h-1"/></svg> Kembali</a>';

ob_start();
?>
<div class="row row-cards">
    <div class="col-lg-8">
        <form method="POST">
            <?= csrfField() ?>
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Edit Laporan</h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label required">Tanggal</label>
                            <input type="date" name="tanggal" class="form-control" value="<?= e($r['tanggal']) ?>"
                                required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label required">Pelapor</label>
                            <input type="text" name="pelapor" class="form-control" value="<?= e($r['pelapor']) ?>"
                                required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label required">Dealer</label>
                            <select name="dealer" class="form-select" required>
                                <?php
                                $selDealer = $r['dealer'];
                                $foundSel = false;
                                foreach ($dealersByArea as $area => $names):
                                    ?>
                                    <optgroup label="<?= e($area) ?>">
                                        <?php foreach ($names as $nm):
                                            if ($selDealer === $nm)
                                                $foundSel = true;
                                            ?>
                                            <option value="<?= e($nm) ?>" <?= $selDealer === $nm ? 'selected' : '' ?>><?= e($nm) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </optgroup>
                                <?php endforeach; ?>
                                <?php if (!$foundSel && $r['dealer']): ?>
                                    <option value="<?= e($r['dealer']) ?>" selected><?= e($r['dealer']) ?></option>
                                <?php endif; ?>
                            </select>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label required">Item</label>
                            <select name="item" class="form-select" required>
                                <?php
                                $itemFound = false;
                                foreach ($items as $it):
                                    if ($r['item'] === $it)
                                        $itemFound = true;
                                    ?>
                                    <option value="<?= e($it) ?>" <?= $r['item'] === $it ? 'selected' : '' ?>>
                                        <?= e($it) ?>
                                    </option>
                                <?php endforeach; ?>
                                <?php if (!$itemFound && $r['item']): ?>
                                    <option value="<?= e($r['item']) ?>" selected><?= e($r['item']) ?></option>
                                <?php endif; ?>
                            </select>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Status</label>
                            <select name="status" class="form-select">
                                <?php foreach (['Open', 'In Progress', 'Closed'] as $s): ?>
                                    <option value="<?= $s ?>" <?= $r['status'] === $s ? 'selected' : '' ?>>
                                        <?= $s ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label required">Laporan Awal</label>
                        <textarea name="laporan_awal" class="form-control" rows="3"
                            required><?= e($r['laporan_awal']) ?></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Pengecekan</label>
                        <textarea name="pengecekan" class="form-control" rows="3"><?= e($r['pengecekan']) ?></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Solusi</label>
                        <textarea name="solusi" class="form-control" rows="3"><?= e($r['solusi']) ?></textarea>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Waktu Mulai</label>
                            <input type="datetime-local" name="waktu_mulai" class="form-control"
                                value="<?= $r['waktu_mulai'] ? date('Y-m-d\TH:i', strtotime($r['waktu_mulai'])) : '' ?>">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Waktu Selesai</label>
                            <input type="datetime-local" name="waktu_selesai" class="form-control"
                                value="<?= $r['waktu_selesai'] ? date('Y-m-d\TH:i', strtotime($r['waktu_selesai'])) : '' ?>">
                        </div>
                    </div>
                </div>
                <div class="card-footer text-end">
                    <a href="<?= url('/maintenance/view?id=' . $id) ?>" class="btn btn-link">Batal</a>
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
