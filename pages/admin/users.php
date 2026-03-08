<?php
/**
 * pages/admin/users.php — User management (admin only)
 */
requireRole('admin');
$pageTitle = 'Manajemen User';
$pagePretitle = 'Admin';
$pageActions = '<button class="btn btn-primary d-none d-sm-inline-block" data-bs-toggle="modal" data-bs-target="#modal-add-user"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon"><path d="M12 5l0 14"/><path d="M5 12l14 0"/></svg> Tambah User</button>';

// Handle create/edit POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();
    $action = $_POST['action'] ?? '';

    if ($action === 'create') {
        $v = new Validator($_POST);
        $v->required('username', 'Username')->required('full_name', 'Nama Lengkap')
            ->required('password', 'Password')->minLength('password', 6, 'Password')
            ->in('role', ['admin', 'staff', 'user', 'dealer'], 'Role');

        if ($v->fails()) {
            flash('error', $v->firstError());
        } else {
            $chk = db()->prepare("SELECT id FROM users WHERE username = ?");
            $chk->execute([$v->get('username')]);
            if ($chk->fetch()) {
                flash('error', 'Username sudah digunakan.');
            } else {
                $hash = password_hash($v->get('password'), PASSWORD_BCRYPT);
                $stmt = db()->prepare("INSERT INTO users (username, password, full_name, role, is_active, created_at, updated_at) VALUES (?, ?, ?, ?, 1, NOW(), NOW())");
                $stmt->execute([$v->get('username'), $hash, $v->get('full_name'), $v->get('role')]);
                flash('success', 'User berhasil ditambahkan.');
            }
        }
    } elseif ($action === 'toggle') {
        $uid = (int) ($_POST['user_id'] ?? 0);
        if ($uid && $uid !== (int) $_SESSION['user_id']) {
            $stmt = db()->prepare("UPDATE users SET is_active = NOT is_active, updated_at = NOW() WHERE id = ?");
            $stmt->execute([$uid]);
            flash('success', 'Status user berhasil diubah.');
        }
    } elseif ($action === 'reset_password') {
        $uid = (int) ($_POST['user_id'] ?? 0);
        $newPass = $_POST['new_password'] ?? '';
        if ($uid && strlen($newPass) >= 6) {
            $hash = password_hash($newPass, PASSWORD_BCRYPT);
            $stmt = db()->prepare("UPDATE users SET password = ?, updated_at = NOW() WHERE id = ?");
            $stmt->execute([$hash, $uid]);
            flash('success', 'Password berhasil direset.');
        } else {
            flash('error', 'Password minimal 6 karakter.');
        }
    }
    redirect(url('/admin/users'));
}

// Fetch users
$users = db()->query("SELECT * FROM users ORDER BY role, full_name")->fetchAll();

ob_start();
?>
<div class="card">
    <div class="table-responsive">
        <table class="table table-vcenter card-table">
            <thead>
                <tr>
                    <th>Username</th>
                    <th>Nama Lengkap</th>
                    <th>Role</th>
                    <th>Status</th>
                    <th>Dibuat</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $u): ?>
                    <tr>
                        <td class="fw-bold"><?= e($u['username']) ?></td>
                        <td><?= e($u['full_name']) ?></td>
                        <td><span class="badge bg-blue-lt"><?= e(ucfirst($u['role'])) ?></span></td>
                        <td><?= $u['is_active'] ? '<span class="badge bg-green">Aktif</span>' : '<span class="badge bg-red">Nonaktif</span>' ?></td>
                        <td class="text-secondary"><?= formatDate($u['created_at'], 'd/m/Y') ?></td>
                        <td>
                            <?php if ($u['id'] != $_SESSION['user_id']): ?>
                                <form method="POST" class="d-inline">
                                    <?= csrfField() ?>
                                    <input type="hidden" name="action" value="toggle">
                                    <input type="hidden" name="user_id" value="<?= $u['id'] ?>">
                                    <button class="btn btn-sm btn-outline-<?= $u['is_active'] ? 'warning' : 'success' ?>" onclick="return confirm('Yakin?')"><?= $u['is_active'] ? 'Nonaktifkan' : 'Aktifkan' ?></button>
                                </form>
                                <button class="btn btn-sm btn-outline-secondary ms-1" data-bs-toggle="modal" data-bs-target="#modal-reset-<?= $u['id'] ?>">Reset Pass</button>
                                <div class="modal fade" id="modal-reset-<?= $u['id'] ?>"><div class="modal-dialog modal-sm"><div class="modal-content">
                                    <form method="POST">
                                        <?= csrfField() ?>
                                        <input type="hidden" name="action" value="reset_password">
                                        <input type="hidden" name="user_id" value="<?= $u['id'] ?>">
                                        <div class="modal-header"><h5 class="modal-title">Reset Password: <?= e($u['username']) ?></h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                                        <div class="modal-body"><input type="password" name="new_password" class="form-control" placeholder="Password baru (min 6)" required minlength="6"></div>
                                        <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button><button type="submit" class="btn btn-primary">Reset</button></div>
                                    </form>
                                </div></div></div>
                            <?php else: ?>
                                <span class="text-secondary">—</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Add User Modal -->
<div class="modal fade" id="modal-add-user"><div class="modal-dialog"><div class="modal-content">
    <form method="POST">
        <?= csrfField() ?>
        <input type="hidden" name="action" value="create">
        <div class="modal-header"><h5 class="modal-title">Tambah User Baru</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
        <div class="modal-body">
            <div class="mb-3"><label class="form-label required">Username</label><input type="text" name="username" class="form-control" required></div>
            <div class="mb-3"><label class="form-label required">Nama Lengkap</label><input type="text" name="full_name" class="form-control" required></div>
            <div class="mb-3"><label class="form-label required">Password</label><input type="password" name="password" class="form-control" required minlength="6"></div>
            <div class="mb-3"><label class="form-label required">Role</label>
                <select name="role" class="form-select" required>
                    <option value="user">User</option><option value="staff">Staff</option><option value="admin">Admin</option><option value="dealer">Dealer</option>
                </select>
            </div>
        </div>
        <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button><button type="submit" class="btn btn-primary">Simpan</button></div>
    </form>
</div></div></div>
<?php
$content = ob_get_clean();
include BASE_PATH . '/layouts/main.php';
