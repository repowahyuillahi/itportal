<?php
/**
 * pages/divisi/index.php — Divisi management CRUD (Tabler card-table)
 */
requireRole('admin', 'staff');

$pageTitle = 'Kelola Divisi';
$pagePretitle = 'Data Master';

// Handle POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();
    $action = $_POST['action'] ?? '';

    if ($action === 'create') {
        $kode = strtoupper(trim($_POST['kode_divisi'] ?? ''));
        $nama = strtoupper(trim($_POST['nama_divisi'] ?? ''));
        $ket = trim($_POST['keterangan'] ?? '') ?: null;
        if ($kode === '' || $nama === '') {
            flash('error', 'Kode dan Nama divisi wajib diisi.');
        } else {
            $chk = db()->prepare("SELECT id FROM divisi WHERE kode_divisi = ?");
            $chk->execute([$kode]);
            if ($chk->fetch()) {
                flash('error', 'Kode divisi sudah ada.');
            } else {
                $stmt = db()->prepare("INSERT INTO divisi (kode_divisi, nama_divisi, keterangan, is_active) VALUES (?,?,?,1)");
                $stmt->execute([$kode, $nama, $ket]);
                flash('success', 'Divisi berhasil ditambahkan.');
            }
        }
    } elseif ($action === 'update') {
        $id = (int) ($_POST['id'] ?? 0);
        $kode = strtoupper(trim($_POST['kode_divisi'] ?? ''));
        $nama = strtoupper(trim($_POST['nama_divisi'] ?? ''));
        $ket = trim($_POST['keterangan'] ?? '') ?: null;
        if ($id && $kode && $nama) {
            $stmt = db()->prepare("UPDATE divisi SET kode_divisi=?, nama_divisi=?, keterangan=? WHERE id=?");
            $stmt->execute([$kode, $nama, $ket, $id]);
            flash('success', 'Divisi berhasil diupdate.');
        }
    } elseif ($action === 'toggle') {
        $id = (int) ($_POST['id'] ?? 0);
        if ($id) {
            db()->prepare("UPDATE divisi SET is_active = NOT is_active WHERE id = ?")->execute([$id]);
            flash('success', 'Status divisi berhasil diubah.');
        }
    }
    redirect(url('/divisi'));
}

// Fetch divisi with dealer_divisi count
$divisis = db()->query("
    SELECT d.*, 
        (SELECT COUNT(DISTINCT dd.area) FROM dealer_divisi dd WHERE dd.divisi_id = d.id) AS area_count
    FROM divisi d 
    ORDER BY d.kode_divisi
")->fetchAll();

// Fetch dealer_divisi mappings for display
$mappings = db()->query("SELECT dd.*, d.nama_divisi FROM dealer_divisi dd JOIN divisi d ON dd.divisi_id = d.id ORDER BY dd.area, d.kode_divisi")->fetchAll();

$pageActions = '<button class="btn btn-primary d-none d-sm-inline-block" data-bs-toggle="modal" data-bs-target="#modal-add-divisi"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon"><path d="M12 5l0 14"/><path d="M5 12l14 0"/></svg> Tambah Divisi</button>';

ob_start();
?>
<!-- Divisi Table -->
<div class="card mb-3">
    <div class="card-header">
        <h3 class="card-title">
            <?= count($divisis) ?> divisi
        </h3>
    </div>
    <div class="table-responsive">
        <table class="table table-vcenter card-table table-striped">
            <thead>
                <tr>
                    <th>Kode</th>
                    <th>Nama Divisi</th>
                    <th>Keterangan</th>
                    <th>Area Terdaftar</th>
                    <th>Status</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($divisis as $d): ?>
                    <tr>
                        <td><span class="badge bg-blue-lt fw-bold">
                                <?= e($d['kode_divisi']) ?>
                            </span></td>
                        <td class="fw-bold">
                            <?= e($d['nama_divisi']) ?>
                        </td>
                        <td class="text-secondary">
                            <?= e($d['keterangan'] ?? '—') ?>
                        </td>
                        <td><span class="badge bg-azure-lt">
                                <?= $d['area_count'] ?> area
                            </span></td>
                        <td>
                            <?= $d['is_active'] ? '<span class="badge bg-green">Aktif</span>' : '<span class="badge bg-red">Nonaktif</span>' ?>
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
                                    onclick="return confirm('Yakin?')">
                                    <?= $d['is_active'] ? 'Off' : 'On' ?>
                                </button>
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
                                        <h5 class="modal-title">Edit Divisi</h5><button type="button" class="btn-close"
                                            data-bs-dismiss="modal"></button>
                                    </div>
                                    <div class="modal-body">
                                        <div class="mb-3"><label class="form-label required">Kode Divisi</label><input
                                                type="text" name="kode_divisi" class="form-control"
                                                value="<?= e($d['kode_divisi']) ?>" required></div>
                                        <div class="mb-3"><label class="form-label required">Nama Divisi</label><input
                                                type="text" name="nama_divisi" class="form-control"
                                                value="<?= e($d['nama_divisi']) ?>" required></div>
                                        <div class="mb-3"><label class="form-label">Keterangan</label><input type="text"
                                                name="keterangan" class="form-control" value="<?= e($d['keterangan']) ?>">
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

<!-- Area Mapping -->
<div class="card">
    <div class="card-header">
        <h3 class="card-title">Mapping Divisi per Area</h3>
    </div>
    <div class="table-responsive">
        <table class="table table-vcenter card-table table-striped">
            <thead>
                <tr>
                    <th>Area</th>
                    <th>Divisi</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $grouped = [];
                foreach ($mappings as $m) {
                    $grouped[$m['area']][] = $m['nama_divisi'];
                }
                foreach ($grouped as $area => $divs): ?>
                    <tr>
                        <td><span
                                class="badge bg-<?= match ($area) { 'TB' => 'blue', 'TM' => 'purple', 'INDEPENDENT' => 'green', 'WH' => 'orange', 'OTHERS' => 'secondary', default => 'red'} ?>-lt">
                                <?= e($area) ?>
                            </span></td>
                        <td>
                            <?php foreach ($divs as $dn): ?><span class="badge bg-azure-lt me-1">
                                    <?= e($dn) ?>
                                </span>
                            <?php endforeach; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Add Divisi Modal -->
<div class="modal fade" id="modal-add-divisi">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST">
                <?= csrfField() ?>
                <input type="hidden" name="action" value="create">
                <div class="modal-header">
                    <h5 class="modal-title">Tambah Divisi Baru</h5><button type="button" class="btn-close"
                        data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3"><label class="form-label required">Kode Divisi</label><input type="text"
                            name="kode_divisi" class="form-control" placeholder="e.g. SM, CS, IT" required></div>
                    <div class="mb-3"><label class="form-label required">Nama Divisi</label><input type="text"
                            name="nama_divisi" class="form-control" placeholder="e.g. SHOP MANAGER" required></div>
                    <div class="mb-3"><label class="form-label">Keterangan</label><input type="text" name="keterangan"
                            class="form-control"></div>
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
