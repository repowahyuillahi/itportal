<?php
/**
 * pages/profile/index.php — User profile page with photo upload
 */
requireLogin();
$pageTitle = 'Profil Saya';
$pagePretitle = 'Akun';

$_user = currentUser();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();
    $fullName = trim($_POST['full_name'] ?? '');
    $email = trim($_POST['email'] ?? '') ?: null;

    if ($fullName === '') {
        flash('error', 'Nama lengkap wajib diisi.');
    } else {
        // Handle avatar upload
        $avatarPath = $_user['avatar'] ?? null;
        if (!empty($_FILES['avatar']['tmp_name']) && $_FILES['avatar']['error'] === UPLOAD_ERR_OK) {
            $allowedTypes = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];
            $fileType = mime_content_type($_FILES['avatar']['tmp_name']);
            if (in_array($fileType, $allowedTypes)) {
                $ext = match ($fileType) {
                    'image/jpeg' => 'jpg',
                    'image/png' => 'png',
                    'image/webp' => 'webp',
                    'image/gif' => 'gif',
                    default => 'jpg'
                };
                $avatarDir = BASE_PATH . '/storage/avatars';
                if (!is_dir($avatarDir))
                    mkdir($avatarDir, 0755, true);
                $filename = 'avatar_' . $_user['id'] . '_' . time() . '.' . $ext;
                move_uploaded_file($_FILES['avatar']['tmp_name'], $avatarDir . '/' . $filename);
                $avatarPath = 'storage/avatars/' . $filename;
            } else {
                flash('error', 'Format file tidak didukung. Gunakan JPG, PNG, WebP, atau GIF.');
                redirect(url('/profile'));
            }
        }

        // Update password if provided
        $newPass = $_POST['new_password'] ?? '';
        $updates = "full_name = ?, email = ?, avatar = ?, updated_at = NOW()";
        $params = [$fullName, $email, $avatarPath, $_user['id']];

        if ($newPass !== '') {
            if (strlen($newPass) < 6) {
                flash('error', 'Password minimal 6 karakter.');
                redirect(url('/profile'));
            }
            $updates = "full_name = ?, email = ?, avatar = ?, password = ?, updated_at = NOW()";
            $hash = password_hash($newPass, PASSWORD_BCRYPT);
            $params = [$fullName, $email, $avatarPath, $hash, $_user['id']];
        }

        $stmt = db()->prepare("UPDATE users SET $updates WHERE id = ?");
        $stmt->execute($params);
        $_SESSION['full_name'] = $fullName;
        flash('success', 'Profil berhasil diupdate.');
        redirect(url('/profile'));
    }
}

$avatarUrl = !empty($_user['avatar'])
    ? url($_user['avatar'])
    : 'https://ui-avatars.com/api/?name=' . urlencode($_user['full_name'] ?? 'U') . '&size=128&background=0054a6&color=fff';

ob_start();
?>
<div class="row row-cards">
    <div class="col-lg-4">
        <div class="card">
            <div class="card-body text-center">
                <span class="avatar avatar-xl mb-3"
                    style="background-image: url(<?= e($avatarUrl) ?>); width: 8rem; height: 8rem;"></span>
                <h3 class="mb-1">
                    <?= e($_user['full_name']) ?>
                </h3>
                <p class="text-secondary mb-0">@
                    <?= e($_user['username']) ?>
                </p>
                <div class="mt-2"><span class="badge bg-blue-lt">
                        <?= e(ucfirst($_user['role'])) ?>
                    </span></div>
            </div>
            <div class="card-body border-top">
                <div class="datagrid">
                    <div class="datagrid-item">
                        <div class="datagrid-title">Member sejak</div>
                        <div class="datagrid-content">
                            <?= formatDate($_user['created_at'], 'd M Y') ?>
                        </div>
                    </div>
                    <div class="datagrid-item">
                        <div class="datagrid-title">Status</div>
                        <div class="datagrid-content"><span class="badge bg-green">Aktif</span></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-8">
        <form method="POST" enctype="multipart/form-data">
            <?= csrfField() ?>
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Edit Profil</h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label required">Nama Lengkap</label>
                            <input type="text" name="full_name" class="form-control"
                                value="<?= e($_user['full_name']) ?>" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" name="email" class="form-control"
                                value="<?= e($_user['email'] ?? '') ?>" placeholder="email@example.com">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Foto Profil</label>
                        <input type="file" name="avatar" class="form-control" accept="image/*">
                        <small class="form-hint">JPG, PNG, WebP, GIF. Max 2MB.</small>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Username</label>
                            <input type="text" class="form-control" value="<?= e($_user['username']) ?>" disabled>
                            <small class="form-hint">Username tidak bisa diubah.</small>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Password Baru</label>
                            <input type="password" name="new_password" class="form-control"
                                placeholder="Kosongkan jika tidak ingin diubah" minlength="6">
                        </div>
                    </div>
                </div>
                <div class="card-footer text-end">
                    <button type="submit" class="btn btn-primary">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                            stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                            class="icon">
                            <path d="M5 12l5 5l10 -10" />
                        </svg>
                        Simpan Perubahan
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
<?php
$content = ob_get_clean();
include BASE_PATH . '/layouts/main.php';
