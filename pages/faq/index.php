<?php
/**
 * pages/faq/index.php — Public/User FAQ view with image support
 */
$pageTitle = 'Knowledge Base / FAQ';
$pagePretitle = 'Informasi & Bantuan';

$search = trim($_GET['q'] ?? '');

$query = "SELECT * FROM faqs";
$params = [];
if ($search !== '') {
    $query .= " WHERE question LIKE ? OR answer LIKE ?";
    $params[] = "%$search%";
    $params[] = "%$search%";
}
$query .= " ORDER BY category ASC, updated_at DESC";

$stmt = db()->prepare($query);
$stmt->execute($params);
$faqs = $stmt->fetchAll();

// Group by category
$grouped = [];
foreach ($faqs as $f) {
    if (!$f['category']) $f['category'] = 'Umum';
    $grouped[$f['category']][] = $f;
}

ob_start();
?>
<div class="row mb-4">
    <div class="col-md-6 offset-md-3">
        <form method="GET" class="d-flex">
            <div class="input-icon w-100">
                <span class="input-icon-addon">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon"><path d="M10 10m-7 0a7 7 0 1 0 14 0a7 7 0 1 0 -14 0"/><path d="M21 21l-6 -6"/></svg>
                </span>
                <input type="text" name="q" class="form-control form-control-lg" placeholder="Cari pertanyaan atau jawaban..." value="<?= e($search) ?>">
            </div>
            <button type="submit" class="btn btn-primary btn-lg ms-2">Cari</button>
        </form>
    </div>
</div>

<?php if (empty($grouped)): ?>
    <div class="empty">
        <div class="empty-icon">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon"><path d="M8 9h8"/><path d="M8 13h6"/><path d="M18 4a3 3 0 0 1 3 3v8a3 3 0 0 1 -3 3h-5l-5 3v-3h-2a3 3 0 0 1 -3 -3v-8a3 3 0 0 1 3 -3h12z"/></svg>
        </div>
        <p class="empty-title">Tidak ada hasil</p>
        <p class="empty-subtitle text-secondary">
            Cobalah ubah kata kunci pencarian atau hubungi Helpdesk.
        </p>
        <?php if ($search): ?>
        <div class="empty-action">
            <a href="<?= url('/faq') ?>" class="btn btn-primary">Reset Pencarian</a>
        </div>
        <?php endif; ?>
    </div>
<?php else: ?>
    <div class="row row-cards">
        <?php foreach ($grouped as $cat => $items): ?>
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title text-primary"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon me-2"><path d="M4 14v-3a8 8 0 1 1 16 0v3"/><path d="M18 19c0 1.657 -2.686 3 -6 3"/><path d="M4 14a2 2 0 0 1 2 -2h1a2 2 0 0 1 2 2v3a2 2 0 0 1 -2 2h-1a2 2 0 0 1 -2 -2z"/><path d="M15 14a2 2 0 0 1 2 -2h1a2 2 0 0 1 2 2v3a2 2 0 0 1 -2 2h-1a2 2 0 0 1 -2 -2z"/></svg> <?= e($cat) ?></h3>
                </div>
                <div class="card-body">
                    <div class="accordion" id="faq-<?= md5($cat) ?>">
                        <?php foreach ($items as $idx => $f): ?>
                        <div class="accordion-item">
                            <h2 class="accordion-header" id="heading-<?= $f['id'] ?>">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse-<?= $f['id'] ?>" aria-expanded="false" aria-controls="collapse-<?= $f['id'] ?>">
                                    <?= e($f['question']) ?>
                                </button>
                            </h2>
                            <div id="collapse-<?= $f['id'] ?>" class="accordion-collapse collapse" aria-labelledby="heading-<?= $f['id'] ?>" data-bs-parent="#faq-<?= md5($cat) ?>">
                                <div class="accordion-body">
                                    <p class="mb-0"><?= nl2br(e($f['answer'])) ?></p>
                                    <?php if (!empty($f['image_path'])): ?>
                                        <div class="mt-3">
                                            <a href="<?= e(url('/' . $f['image_path'])) ?>" target="_blank" title="Klik untuk perbesar">
                                                <img src="<?= e(url('/' . $f['image_path'])) ?>"
                                                     alt="Screenshot panduan"
                                                     class="img-fluid rounded border"
                                                     style="max-height: 400px; cursor: zoom-in;">
                                            </a>
                                            <div class="text-secondary small mt-1">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon me-1"><path d="M4 15s1-1 4-1 5 2 8 2 4-1 4-1V3s-1 1-4 1-5-2-8-2-4 1-4 1z"/><path d="M4 22l0 -7"/></svg>
                                                Klik gambar untuk melihat ukuran penuh
                                            </div>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<?php
$content = ob_get_clean();
include BASE_PATH . '/layouts/main.php';
