<?php
/**
 * pages/dealers/index.php — Dealer management CRUD (Tabler card-table)
 */
requireRole('admin', 'staff');

$pageTitle = 'Kelola Dealer';
$pagePretitle = 'Data Master';

// Handle POST actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();
    $action = $_POST['action'] ?? '';

    if ($action === 'create') {
        $kode = trim($_POST['kode_dealer'] ?? '');
        $nama = trim($_POST['nama_dealer'] ?? '');
        $alamat = trim($_POST['alamat'] ?? '') ?: null;
        $area = trim($_POST['area'] ?? '');
        $hp = trim($_POST['no_handphone'] ?? '') ?: null;
        $telp = trim($_POST['no_telephone'] ?? '') ?: null;
        if ($kode === '' || $nama === '' || $area === '') {
            flash('error', 'Kode, Nama, dan Area wajib diisi.');
        } else {
            $chk = db()->prepare("SELECT id FROM dealers WHERE kode_dealer = ?");
            $chk->execute([$kode]);
            if ($chk->fetch()) {
                flash('error', 'Kode dealer sudah ada.');
            } else {
                $stmt = db()->prepare("INSERT INTO dealers (kode_dealer, nama_dealer, alamat, area, no_handphone, no_telephone, is_active) VALUES (?,?,?,?,?,?,1)");
                $stmt->execute([$kode, $nama, $alamat, $area, $hp, $telp]);
                flash('success', 'Dealer berhasil ditambahkan.');
            }
        }
    } elseif ($action === 'update') {
        $id = (int) ($_POST['id'] ?? 0);
        $kode = trim($_POST['kode_dealer'] ?? '');
        $nama = trim($_POST['nama_dealer'] ?? '');
        $alamat = trim($_POST['alamat'] ?? '') ?: null;
        $area = trim($_POST['area'] ?? '');
        $hp = trim($_POST['no_handphone'] ?? '') ?: null;
        $telp = trim($_POST['no_telephone'] ?? '') ?: null;
        if ($id && $kode && $nama && $area) {
            $stmt = db()->prepare("UPDATE dealers SET kode_dealer=?, nama_dealer=?, alamat=?, area=?, no_handphone=?, no_telephone=? WHERE id=?");
            $stmt->execute([$kode, $nama, $alamat, $area, $hp, $telp, $id]);
            flash('success', 'Dealer berhasil diupdate.');
        }
    } elseif ($action === 'toggle') {
        $id = (int) ($_POST['id'] ?? 0);
        if ($id) {
            db()->prepare("UPDATE dealers SET is_active = NOT is_active WHERE id = ?")->execute([$id]);
            flash('success', 'Status dealer berhasil diubah.');
        }
    }
    redirect(url('/dealers'));
}

$filterArea = $_GET['area'] ?? '';
$search = trim($_GET['q'] ?? '');

$where = [];
$params = [];
$showInactive = isset($_GET['show_all']);
if (!$showInactive) {
    $where[] = "d.is_active = 1";
}
if ($filterArea) {
    $where[] = "d.area = ?";
    $params[] = $filterArea;
}
if ($search) {
    $where[] = "(d.kode_dealer LIKE ? OR d.nama_dealer LIKE ? OR d.alamat LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = "%$search%";
}
$whereSQL = $where ? 'WHERE ' . implode(' AND ', $where) : '';

$stmt = db()->prepare("SELECT d.* FROM dealers d $whereSQL ORDER BY d.area, d.nama_dealer");
$stmt->execute($params);
$dealers = $stmt->fetchAll();

$areas = db()->query("SELECT DISTINCT area FROM dealers ORDER BY area")->fetchAll(PDO::FETCH_COLUMN);

$pageActions = '<button class="btn btn-primary d-none d-sm-inline-block" data-bs-toggle="modal" data-bs-target="#modal-add-dealer"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon"><path d="M12 5l0 14"/><path d="M5 12l14 0"/></svg> Tambah Dealer</button>';

ob_start();
?>
<!-- Filters -->
<div class="card mb-3">
    <div class="card-body">
        <form method="GET" class="row g-3 align-items-end">
            <div class="col-md-4">
                <label class="form-label">Cari</label>
                <div class="input-icon">
                    <span class="input-icon-addon"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                            viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                            stroke-linecap="round" stroke-linejoin="round" class="icon">
                            <path d="M10 10m-7 0a7 7 0 1 0 14 0a7 7 0 1 0 -14 0" />
                            <path d="M21 21l-6 -6" />
                        </svg></span>
                    <input type="text" name="q" class="form-control" placeholder="Kode / Nama / Alamat..."
                        value="<?= e($search) ?>">
                </div>
            </div>
            <div class="col-md-3">
                <label class="form-label">Area</label>
                <select name="area" class="form-select">
                    <option value="">Semua</option>
                    <?php foreach ($areas as $a): ?>
                        <option value="<?= e($a) ?>" <?= $filterArea === $a ? 'selected' : '' ?>><?= e($a) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-auto">
                <button type="submit" class="btn btn-primary"><svg xmlns="http://www.w3.org/2000/svg" width="24"
                        height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                        stroke-linecap="round" stroke-linejoin="round" class="icon">
                        <path d="M10 10m-7 0a7 7 0 1 0 14 0a7 7 0 1 0 -14 0" />
                        <path d="M21 21l-6 -6" />
                    </svg> Filter</button>
            </div>
        </form>
    </div>
</div>

<!-- Table -->
<div class="card">
    <div class="card-header">
        <h3 class="card-title"><?= count($dealers) ?> dealer</h3>
    </div>
    <div class="table-responsive">
        <table class="table table-vcenter card-table table-striped">
            <thead>
                <tr>
                    <th>Kode</th>
                    <th>Nama Dealer</th>
                    <th>Area</th>
                    <th>Alamat</th>
                    <th>No HP</th>
                    <th>No Telp</th>
                    <th>Status</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($dealers as $d): ?>
                    <tr>
                        <td><span class="text-primary fw-bold"><?= e($d['kode_dealer']) ?></span></td>
                        <td><?= e($d['nama_dealer']) ?></td>
                        <td><span
                                class="badge bg-<?= match ($d['area']) { 'HO' => 'red', 'TB' => 'blue', 'TM' => 'purple', 'INDEPENDENT' => 'green', 'WH' => 'orange', default => 'secondary'} ?>-lt"><?= e($d['area']) ?></span>
                        </td>
                        <td class="text-secondary"><?= e($d['alamat'] ?? '—') ?></td>
                        <td class="text-secondary"><?= e($d['no_handphone'] ?? '—') ?></td>
                        <td class="text-secondary"><?= e($d['no_telephone'] ?? '—') ?></td>
                        <td><?= $d['is_active'] ? '<span class="badge bg-green">Aktif</span>' : '<span class="badge bg-red">Nonaktif</span>' ?>
                        </td>
                        <td>
                            <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal"
                                data-bs-target="#modal-edit-<?= $d['id'] ?>">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24"
                                    fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                    stroke-linejoin="round" class="icon">
                                    <path d="M7 7h-1a2 2 0 0 0 -2 2v9a2 2 0 0 0 2 2h9a2 2 0 0 0 2 -2v-1" />
                                    <path d="M20.385 6.585a2.1 2.1 0 0 0 -2.97 -2.97l-8.415 8.385v3h3l8.385 -8.415z" />
                                    <path d="M16 5l3 3" />
                                </svg>
                            </button>
                            <form method="POST" class="d-inline">
                                <?= csrfField() ?>
                                <input type="hidden" name="action" value="toggle">
                                <input type="hidden" name="id" value="<?= $d['id'] ?>">
                                <button class="btn btn-sm btn-outline-<?= $d['is_active'] ? 'warning' : 'success' ?>"
                                    onclick="return confirm('Yakin?')"><?= $d['is_active'] ? 'Off' : 'On' ?></button>
                            </form>
                        </td>
                    </tr>
                    <!-- Edit Modal -->
                    <div class="modal fade" id="modal-edit-<?= $d['id'] ?>">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <form method="POST">
                                    <?= csrfField() ?>
                                    <input type="hidden" name="action" value="update">
                                    <input type="hidden" name="id" value="<?= $d['id'] ?>">
                                    <div class="modal-header">
                                        <h5 class="modal-title">Edit Dealer</h5><button type="button" class="btn-close"
                                            data-bs-dismiss="modal"></button>
                                    </div>
                                    <div class="modal-body">
                                        <div class="row">
                                            <div class="col-md-6 mb-3"><label class="form-label required">Kode
                                                    Dealer</label><input type="text" name="kode_dealer" class="form-control"
                                                    value="<?= e($d['kode_dealer']) ?>" required></div>
                                            <div class="col-md-6 mb-3"><label class="form-label required">Area</label>
                                                <select name="area" class="form-select" required>
                                                    <?php foreach (['HO', 'TB', 'TM', 'INDEPENDENT', 'WH', 'OTHERS'] as $opt): ?>
                                                        <option value="<?= $opt ?>" <?= $d['area'] === $opt ? 'selected' : '' ?>>
                                                            <?= $opt ?>
                                                        </option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="mb-3"><label class="form-label required">Nama Dealer</label><input
                                                type="text" name="nama_dealer" class="form-control"
                                                value="<?= e($d['nama_dealer']) ?>" required></div>
                                        <div class="mb-3"><label class="form-label">Alamat</label><textarea name="alamat"
                                                class="form-control" rows="2"><?= e($d['alamat']) ?></textarea></div>
                                        <div class="row">
                                            <div class="col-md-6 mb-3"><label class="form-label">No HP</label><input
                                                    type="text" name="no_handphone" class="form-control"
                                                    value="<?= e($d['no_handphone']) ?>"></div>
                                            <div class="col-md-6 mb-3"><label class="form-label">No Telp</label><input
                                                    type="text" name="no_telephone" class="form-control"
                                                    value="<?= e($d['no_telephone']) ?>"></div>
                                        </div>
                                    </div>
                                    <div class="modal-footer"><button type="button" class="btn btn-secondary"
                                            data-bs-dismiss="modal">Batal</button><button type="submit"
                                            class="btn btn-primary">Simpan</button></div>
                                </form>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Add Dealer Modal -->
<div class="modal fade" id="modal-add-dealer">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST">
                <?= csrfField() ?>
                <input type="hidden" name="action" value="create">
                <div class="modal-header">
                    <h5 class="modal-title">Tambah Dealer Baru</h5><button type="button" class="btn-close"
                        data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6 mb-3"><label class="form-label required">Kode Dealer</label><input
                                type="text" name="kode_dealer" class="form-control" required></div>
                        <div class="col-md-6 mb-3"><label class="form-label required">Area</label>
                            <select name="area" class="form-select" required>
                                <option value="">-- Pilih --</option>
                                <option value="HO">HO</option>
                                <option value="TB">TB</option>
                                <option value="TM">TM</option>
                                <option value="INDEPENDENT">INDEPENDENT</option>
                                <option value="WH">WH</option>
                                <option value="OTHERS">OTHERS</option>
                            </select>
                        </div>
                    </div>
                    <div class="mb-3"><label class="form-label required">Nama Dealer</label><input type="text"
                            name="nama_dealer" class="form-control" required></div>
                    <div class="mb-3"><label class="form-label">Alamat</label><textarea name="alamat"
                            class="form-control" rows="2"></textarea></div>
                    <div class="row">
                        <div class="col-md-6 mb-3"><label class="form-label">No HP</label><input type="text"
                                name="no_handphone" class="form-control"></div>
                        <div class="col-md-6 mb-3"><label class="form-label">No Telp</label><input type="text"
                                name="no_telephone" class="form-control"></div>
                    </div>
                </div>
                <div class="modal-footer"><button type="button" class="btn btn-secondary"
                        data-bs-dismiss="modal">Batal</button><button type="submit"
                        class="btn btn-primary">Simpan</button></div>
            </form>
        </div>
    </div>
</div>
<?php
$content = ob_get_clean();
include BASE_PATH . '/layouts/main.php';
