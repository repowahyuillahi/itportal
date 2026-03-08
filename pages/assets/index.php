<?php
/**
 * pages/assets/index.php — Assets management CRUD
 */
requireRole('admin', 'staff');

$pageTitle = 'IT Asset Inventory';
$pagePretitle = 'Data Master';

$users = db()->query("SELECT id, full_name FROM users ORDER BY full_name")->fetchAll();

// Handle POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();
    $action = $_POST['action'] ?? '';

    if ($action === 'create') {
        $code = trim($_POST['asset_code'] ?? '');
        $name = trim($_POST['name'] ?? '');
        $type = trim($_POST['type'] ?? '');
        $branch = trim($_POST['branch'] ?? '');
        $userId = ($_POST['user_id'] ?? '') !== '' ? (int)$_POST['user_id'] : null;
        $status = $_POST['status'] ?? 'Active';
        
        if ($code === '' || $name === '') {
            flash('error', 'Kode dan Nama Asset wajib diisi.');
        } else {
            $chk = db()->prepare("SELECT id FROM assets WHERE asset_code = ?");
            $chk->execute([$code]);
            if ($chk->fetch()) {
                flash('error', 'Kode aset sudah ada.');
            } else {
                $stmt = db()->prepare("INSERT INTO assets (asset_code, name, type, branch, user_id, status) VALUES (?,?,?,?,?,?)");
                $stmt->execute([$code, $name, $type ?: null, $branch ?: null, $userId, $status]);
                flash('success', 'Asset berhasil ditambahkan.');
            }
        }
    } elseif ($action === 'update') {
        $id = (int) ($_POST['id'] ?? 0);
        $code = trim($_POST['asset_code'] ?? '');
        $name = trim($_POST['name'] ?? '');
        $type = trim($_POST['type'] ?? '');
        $branch = trim($_POST['branch'] ?? '');
        $userId = ($_POST['user_id'] ?? '') !== '' ? (int)$_POST['user_id'] : null;
        $status = $_POST['status'] ?? 'Active';
        
        if ($id && $code && $name) {
            $stmt = db()->prepare("UPDATE assets SET asset_code=?, name=?, type=?, branch=?, user_id=?, status=? WHERE id=?");
            $stmt->execute([$code, $name, $type ?: null, $branch ?: null, $userId, $status, $id]);
            flash('success', 'Asset berhasil diupdate.');
        } else {
            flash('error', 'Data tidak valid.');
        }
    } elseif ($action === 'delete') {
        $id = (int) ($_POST['id'] ?? 0);
        if ($id) {
            // make sure it is not used in tickets/maintenance, but just simple delete for now
            db()->prepare("DELETE FROM assets WHERE id = ?")->execute([$id]);
            flash('success', 'Asset berhasil dihapus.');
        }
    }
    redirect(url('/it-assets'));
}

$search = trim($_GET['q'] ?? '');
$filterType = $_GET['type'] ?? '';
$filterStatus = $_GET['status'] ?? '';

$where = [];
$params = [];
if ($search) {
    $where[] = "(a.asset_code LIKE ? OR a.name LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}
if ($filterType) {
    $where[] = "a.type = ?";
    $params[] = $filterType;
}
if ($filterStatus) {
    $where[] = "a.status = ?";
    $params[] = $filterStatus;
}
$whereSQL = $where ? 'WHERE ' . implode(' AND ', $where) : '';

$query = "SELECT a.*, u.full_name as assigned_user 
          FROM assets a 
          LEFT JOIN users u ON a.user_id = u.id 
          $whereSQL 
          ORDER BY a.asset_code ASC";
$stmt = db()->prepare($query);
$stmt->execute($params);
$assets = $stmt->fetchAll();

$typesStmt = db()->query("SELECT DISTINCT type FROM assets WHERE type IS NOT NULL AND type != '' ORDER BY type");
$types = $typesStmt->fetchAll(PDO::FETCH_COLUMN);

$pageActions = '<button class="btn btn-primary d-none d-sm-inline-block" data-bs-toggle="modal" data-bs-target="#modal-add-asset"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon"><path d="M12 5l0 14"/><path d="M5 12l14 0"/></svg> Tambah Asset</button>';

ob_start();
?>
<div class="card mb-3">
    <div class="card-body">
        <form method="GET" class="row g-3 align-items-end">
            <div class="col-md-4">
                <label class="form-label">Cari Asset</label>
                <div class="input-icon">
                    <span class="input-icon-addon">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" class="icon" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M10 10m-7 0a7 7 0 1 0 14 0a7 7 0 1 0 -14 0"/><path d="M21 21l-6 -6"/></svg>
                    </span>
                    <input type="text" name="q" class="form-control" placeholder="Kode atau Nama..." value="<?= e($search) ?>">
                </div>
            </div>
            <div class="col-md-3">
                <label class="form-label">Tipe</label>
                <select name="type" class="form-select">
                    <option value="">Semua Tipe</option>
                    <?php foreach ($types as $t): ?>
                        <option value="<?= e($t) ?>" <?= $filterType === $t ? 'selected' : '' ?>><?= e($t) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Status</label>
                <select name="status" class="form-select">
                    <option value="">Semua Status</option>
                    <?php foreach (['Active', 'Broken', 'Maintenance', 'Retired'] as $s): ?>
                        <option value="<?= $s ?>" <?= $filterStatus === $s ? 'selected' : '' ?>><?= $s ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-auto">
                <button type="submit" class="btn btn-primary">Filter</button>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h3 class="card-title"><?= count($assets) ?> Aset Ditemukan</h3>
    </div>
    <div class="table-responsive">
        <table class="table table-vcenter card-table table-striped">
            <thead>
                <tr>
                    <th>Kode</th>
                    <th>Nama Asset</th>
                    <th>Tipe</th>
                    <th>Cabang/Lokasi</th>
                    <th>Dipegang Oleh</th>
                    <th>Status</th>
                    <th class="w-1">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($assets)): ?>
                    <tr><td colspan="7" class="text-center py-4 text-secondary">Tidak ada data aset.</td></tr>
                <?php else: ?>
                    <?php foreach ($assets as $a): ?>
                        <tr>
                            <td><span class="badge bg-blue-lt fw-bold"><?= e($a['asset_code']) ?></span></td>
                            <td class="fw-bold"><?= e($a['name']) ?></td>
                            <td><?= e($a['type'] ?? '—') ?></td>
                            <td><?= e($a['branch'] ?? '—') ?></td>
                            <td>
                                <?php if ($a['assigned_user']): ?>
                                    <div class="d-flex align-items-center">
                                        <span class="avatar avatar-xs me-2" style="background-image: url(https://ui-avatars.com/api/?name=<?= urlencode($a['assigned_user']) ?>&size=32&background=0054a6&color=fff)"></span>
                                        <?= e($a['assigned_user']) ?>
                                    </div>
                                <?php else: ?>
                                    <span class="text-secondary">—</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php 
                                $bg = match($a['status']) {
                                    'Active' => 'green',
                                    'Broken' => 'red',
                                    'Maintenance' => 'orange',
                                    'Retired' => 'secondary',
                                    default => 'secondary'
                                };
                                ?>
                                <span class="badge bg-<?= $bg ?>"><?= e($a['status']) ?></span>
                            </td>
                            <td class="text-nowrap">
                                <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#modal-edit-<?= $a['id'] ?>">Edit</button>
                                <form method="POST" class="d-inline" onsubmit="return confirm('Yakin hapus aset ini?');">
                                    <?= csrfField() ?>
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="id" value="<?= $a['id'] ?>">
                                    <button class="btn btn-sm btn-outline-danger">Hapus</button>
                                </form>
                            </td>
                        </tr>
                        <!-- Edit Modal -->
                        <div class="modal fade" id="modal-edit-<?= $a['id'] ?>" tabindex="-1">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <form method="POST">
                                        <?= csrfField() ?>
                                        <input type="hidden" name="action" value="update">
                                        <input type="hidden" name="id" value="<?= $a['id'] ?>">
                                        <div class="modal-header">
                                            <h5 class="modal-title">Edit Asset</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                        </div>
                                        <div class="modal-body">
                                            <div class="mb-3">
                                                <label class="form-label required">Kode Asset</label>
                                                <input type="text" name="asset_code" class="form-control" value="<?= e($a['asset_code']) ?>" required>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label required">Nama Asset</label>
                                                <input type="text" name="name" class="form-control" value="<?= e($a['name']) ?>" required>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label">Tipe (e.g. Laptop, PC, Printer)</label>
                                                <input type="text" name="type" class="form-control" value="<?= e($a['type']) ?>">
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label">Cabang / Lokasi</label>
                                                <input type="text" name="branch" class="form-control" value="<?= e($a['branch']) ?>">
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label">Dipegang Oleh (User)</label>
                                                <select name="user_id" class="form-select">
                                                    <option value="">-- Tidak Ada --</option>
                                                    <?php foreach ($users as $u): ?>
                                                        <option value="<?= $u['id'] ?>" <?= $a['user_id'] == $u['id'] ? 'selected' : '' ?>><?= e($u['full_name']) ?></option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label">Status</label>
                                                <select name="status" class="form-select">
                                                    <?php foreach (['Active', 'Broken', 'Maintenance', 'Retired'] as $s): ?>
                                                        <option value="<?= $s ?>" <?= $a['status'] === $s ? 'selected' : '' ?>><?= $s ?></option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                                            <button type="submit" class="btn btn-primary">Simpan</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Add Modal -->
<div class="modal fade" id="modal-add-asset" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST">
                <?= csrfField() ?>
                <input type="hidden" name="action" value="create">
                <div class="modal-header">
                    <h5 class="modal-title">Tambah Asset Baru</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label required">Kode Asset</label>
                        <input type="text" name="asset_code" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label required">Nama Asset</label>
                        <input type="text" name="name" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Tipe (e.g. Laptop, PC, Printer)</label>
                        <input type="text" name="type" class="form-control">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Cabang / Lokasi</label>
                        <input type="text" name="branch" class="form-control">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Dipegang Oleh (User)</label>
                        <select name="user_id" class="form-select">
                            <option value="">-- Tidak Ada --</option>
                            <?php foreach ($users as $u): ?>
                                <option value="<?= $u['id'] ?>"><?= e($u['full_name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-select">
                            <?php foreach (['Active', 'Broken', 'Maintenance', 'Retired'] as $s): ?>
                                <option value="<?= $s ?>"><?= $s ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
include BASE_PATH . '/layouts/main.php';
