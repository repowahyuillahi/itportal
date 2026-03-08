<?php
/**
 * pages/tickets/create.php — Create ticket form (Tabler card style)
 */
$pageTitle = 'Buat Ticket Baru';
$pagePretitle = 'Tickets';

$pageActions = '<a href="' . url('/tickets') . '" class="btn btn-outline-secondary d-none d-sm-inline-block"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon"><path d="M9 14l-4 -4l4 -4"/><path d="M5 10h11a4 4 0 1 1 0 8h-1"/></svg> Kembali</a>';

ob_start();
?>
<div class="row row-cards">
    <div class="col-lg-8">
        <form method="POST" action="<?= url('/tickets/actions/create') ?>" enctype="multipart/form-data">
            <?= csrfField() ?>
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Detail Ticket</h3>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label required">Subject</label>
                        <input type="text" name="subject" class="form-control" placeholder="Ringkasan masalah..."
                            value="<?= e(old('subject')) ?>" required maxlength="255">
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label required">Kategori</label>
                            <select name="category" class="form-select" required>
                                <option value="">-- Pilih Kategori --</option>
                                <?php foreach (ticketCategories() as $val => $label): ?>
                                    <option value="<?= e($val) ?>" <?= old('category') === $val ? 'selected' : '' ?>>
                                        <?= e($label) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Prioritas</label>
                            <select name="priority" class="form-select">
                                <option value="medium" <?= old('priority', 'medium') === 'medium' ? 'selected' : '' ?>>
                                    Medium</option>
                                <option value="low" <?= old('priority') === 'low' ? 'selected' : '' ?>>Low</option>
                                <option value="high" <?= old('priority') === 'high' ? 'selected' : '' ?>>High</option>
                                <option value="urgent" <?= old('priority') === 'urgent' ? 'selected' : '' ?>>Urgent
                                </option>
                            </select>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label required">Pesan / Detail Masalah</label>
                        <textarea name="message" class="form-control" rows="6"
                            placeholder="Jelaskan masalah secara detail..." required><?= e(old('message')) ?></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Lampiran (opsional)</label>
                        <input type="file" name="attachment" class="form-control">
                        <small class="form-hint">Maks <?= UPLOAD_MAX_MB ?>MB. Format: jpg, png, pdf, doc, xls, zip,
                            dll.</small>
                    </div>
                </div>
                <div class="card-footer text-end">
                    <a href="<?= url('/tickets') ?>" class="btn btn-link">Batal</a>
                    <button type="submit" class="btn btn-primary ms-auto">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                            stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                            class="icon">
                            <path d="M10 14l11 -11" />
                            <path d="M21 3l-6.5 18a.55 .55 0 0 1 -1 0l-3.5 -7l-7 -3.5a.55 .55 0 0 1 0 -1l18 -6.5" />
                        </svg>
                        Kirim Ticket
                    </button>
                </div>
            </div>
        </form>
    </div>
    <div class="col-lg-4">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Panduan</h3>
            </div>
            <div class="card-body">
                <div class="markdown">
                    <p>Untuk mempercepat penanganan, mohon isi informasi berikut:</p>
                    <ul>
                        <li><strong>Subject:</strong> Ringkasan singkat masalah</li>
                        <li><strong>Kategori:</strong> Pilih jenis masalah (Hardware, Software, dll)</li>
                        <li><strong>Detail:</strong> Jelaskan masalah secara rinci, termasuk langkah yang sudah dicoba
                        </li>
                        <li><strong>Lampiran:</strong> Screenshot atau dokumen pendukung</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
<?php
$content = ob_get_clean();
include BASE_PATH . '/layouts/main.php';
