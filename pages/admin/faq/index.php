<?php
/**
 * pages/admin/faq/index.php — Manage FAQs (CRUD) with image upload
 */
requireRole('admin', 'staff');

$pageTitle = 'Kelola FAQ';
$pagePretitle = 'Admin';

$uploadDir = BASE_PATH . '/uploads/faq/';
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0755, true);
}

function handleFaqImage(string $uploadDir): ?string
{
    if (empty($_FILES['image']) || $_FILES['image']['error'] !== UPLOAD_ERR_OK) {
        return null;
    }
    $file  = $_FILES['image'];
    $ext   = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $allow = ['jpg','jpeg','png','gif','webp'];
    if (!in_array($ext, $allow)) {
        flash('error', 'Format gambar tidak didukung (jpg, png, gif, webp).');
        return null;
    }
    if ($file['size'] > 5 * 1024 * 1024) {
        flash('error', 'Ukuran gambar maksimal 5MB.');
        return null;
    }
    $name = 'faq_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
    move_uploaded_file($file['tmp_name'], $uploadDir . $name);
    return 'uploads/faq/' . $name;
}

// Handle POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();
    $action = $_POST['action'] ?? '';

    if ($action === 'create') {
        $question = trim($_POST['question'] ?? '');
        $category = trim($_POST['category'] ?? '');
        $answer   = trim($_POST['answer'] ?? '');

        if ($question === '' || $answer === '') {
            flash('error', 'Pertanyaan dan Jawaban wajib diisi.');
        } else {
            $imgPath = handleFaqImage($uploadDir);
            $stmt = db()->prepare("INSERT INTO faqs (question, answer, category, image_path) VALUES (?,?,?,?)");
            $stmt->execute([$question, $answer, $category ?: null, $imgPath]);
            flash('success', 'FAQ berhasil ditambahkan.');
        }
    } elseif ($action === 'update') {
        $id       = (int) ($_POST['id'] ?? 0);
        $question = trim($_POST['question'] ?? '');
        $category = trim($_POST['category'] ?? '');
        $answer   = trim($_POST['answer'] ?? '');

        if ($id && $question !== '' && $answer !== '') {
            $imgPath = handleFaqImage($uploadDir);

            if ($imgPath !== null) {
                // New image uploaded — delete old one
                $old = db()->prepare("SELECT image_path FROM faqs WHERE id=?");
                $old->execute([$id]);
                $oldPath = $old->fetchColumn();
                if ($oldPath && file_exists(BASE_PATH . '/' . $oldPath)) {
                    unlink(BASE_PATH . '/' . $oldPath);
                }
                $stmt = db()->prepare("UPDATE faqs SET question=?, answer=?, category=?, image_path=? WHERE id=?");
                $stmt->execute([$question, $answer, $category ?: null, $imgPath, $id]);
            } else {
                $stmt = db()->prepare("UPDATE faqs SET question=?, answer=?, category=? WHERE id=?");
                $stmt->execute([$question, $answer, $category ?: null, $id]);
            }
            // Handle remove-image checkbox
            if (isset($_POST['remove_image']) && $_POST['remove_image'] === '1') {
                $old = db()->prepare("SELECT image_path FROM faqs WHERE id=?");
                $old->execute([$id]);
                $oldPath = $old->fetchColumn();
                if ($oldPath && file_exists(BASE_PATH . '/' . $oldPath)) {
                    unlink(BASE_PATH . '/' . $oldPath);
                }
                db()->prepare("UPDATE faqs SET image_path=NULL WHERE id=?")->execute([$id]);
            }
            flash('success', 'FAQ berhasil diupdate.');
        } else {
            flash('error', 'Data tidak valid.');
        }
    } elseif ($action === 'delete') {
        $id = (int) ($_POST['id'] ?? 0);
        if ($id) {
            $old = db()->prepare("SELECT image_path FROM faqs WHERE id=?");
            $old->execute([$id]);
            $oldPath = $old->fetchColumn();
            if ($oldPath && file_exists(BASE_PATH . '/' . $oldPath)) {
                unlink(BASE_PATH . '/' . $oldPath);
            }
            db()->prepare("DELETE FROM faqs WHERE id = ?")->execute([$id]);
            flash('success', 'FAQ berhasil dihapus.');
        }
    }
    redirect(url('/admin/faq'));
}

// Fetch all FAQs
$faqs = db()->query("SELECT * FROM faqs ORDER BY category, id DESC")->fetchAll();

$pageActions = '<button class="btn btn-primary d-none d-sm-inline-block" data-bs-toggle="modal" data-bs-target="#modal-add-faq"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon"><path d="M12 5l0 14"/><path d="M5 12l14 0"/></svg> Tambah FAQ</button>';

ob_start();
?>
<div class="card mb-3">
    <div class="card-header">
        <h3 class="card-title"><?= count($faqs) ?> FAQ</h3>
    </div>
    <div class="table-responsive">
        <table class="table table-vcenter card-table table-striped">
            <thead>
                <tr>
                    <th>Kategori</th>
                    <th>Pertanyaan</th>
                    <th>Jawaban</th>
                    <th>Gambar</th>
                    <th>Update Terakhir</th>
                    <th class="w-1">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($faqs)): ?>
                    <tr><td colspan="6" class="text-center text-secondary py-4">Belum ada FAQ.</td></tr>
                <?php else: ?>
                    <?php foreach ($faqs as $f): ?>
                        <tr>
                            <td><span class="badge bg-blue-lt"><?= e($f['category'] ?: 'Umum') ?></span></td>
                            <td class="fw-bold"><?= e($f['question']) ?></td>
                            <td><div class="text-truncate" style="max-width: 250px;"><?= e($f['answer']) ?></div></td>
                            <td>
                                <?php if ($f['image_path']): ?>
                                    <a href="<?= e(url('/' . $f['image_path'])) ?>" target="_blank">
                                        <img src="<?= e(url('/' . $f['image_path'])) ?>" alt="screenshot" class="rounded" style="height:40px; width:60px; object-fit:cover; border:1px solid #ddd;">
                                    </a>
                                <?php else: ?>
                                    <span class="text-secondary">—</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-secondary"><?= date('d/m/Y', strtotime($f['updated_at'])) ?></td>
                            <td class="text-nowrap">
                                <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#modal-edit-<?= $f['id'] ?>">Edit</button>
                                <form method="POST" class="d-inline" onsubmit="return confirm('Yakin hapus FAQ ini?');"><?= csrfField() ?>
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="id" value="<?= $f['id'] ?>">
                                    <button class="btn btn-sm btn-outline-danger">Hapus</button>
                                </form>
                            </td>
                        </tr>
                        <!-- Edit Modal -->
                        <div class="modal fade" id="modal-edit-<?= $f['id'] ?>" tabindex="-1">
                            <div class="modal-dialog modal-lg">
                                <div class="modal-content">
                                    <form method="POST" enctype="multipart/form-data">
                                        <?= csrfField() ?>
                                        <input type="hidden" name="action" value="update">
                                        <input type="hidden" name="id" value="<?= $f['id'] ?>">
                                        <div class="modal-header">
                                            <h5 class="modal-title">Edit FAQ</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                        </div>
                                        <div class="modal-body">
                                            <div class="mb-3">
                                                <label class="form-label">Kategori</label>
                                                <input type="text" name="category" class="form-control" value="<?= e($f['category']) ?>" placeholder="e.g. Teknis, Layanan, Umum">
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label required">Pertanyaan</label>
                                                <input type="text" name="question" class="form-control" value="<?= e($f['question']) ?>" required>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label required">Jawaban</label>
                                                <textarea name="answer" class="form-control" rows="5" required><?= e($f['answer']) ?></textarea>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label">Gambar / Screenshot (opsional)</label>
                                                <?php if ($f['image_path']): ?>
                                                    <div class="mb-2">
                                                        <img src="<?= e(url('/' . $f['image_path'])) ?>" alt="screenshot" class="rounded img-fluid" style="max-height:200px; border:1px solid #ddd;">
                                                        <div class="form-check mt-1">
                                                            <input class="form-check-input" type="checkbox" name="remove_image" value="1" id="remove-img-<?= $f['id'] ?>">
                                                            <label class="form-check-label text-danger" for="remove-img-<?= $f['id'] ?>">Hapus gambar ini</label>
                                                        </div>
                                                    </div>
                                                <?php endif; ?>
                                                <input type="file" name="image" class="form-control" accept="image/*">
                                                <small class="form-hint">Format: jpg, png, gif, webp. Maks 5MB. Kosongkan jika tidak ingin mengganti.</small>
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
<div class="modal fade" id="modal-add-faq" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form method="POST" enctype="multipart/form-data">
                <?= csrfField() ?>
                <input type="hidden" name="action" value="create">
                <div class="modal-header">
                    <h5 class="modal-title">Tambah FAQ Baru</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Kategori</label>
                        <input type="text" name="category" class="form-control" placeholder="e.g. Teknis, Layanan, Umum">
                    </div>
                    <div class="mb-3">
                        <label class="form-label required">Pertanyaan</label>
                        <input type="text" name="question" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label required">Jawaban</label>
                        <textarea name="answer" class="form-control" rows="5" required></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Gambar / Screenshot (opsional)</label>
                        <input type="file" name="image" class="form-control" accept="image/*">
                        <small class="form-hint">Format: jpg, png, gif, webp. Maks 5MB.</small>
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
