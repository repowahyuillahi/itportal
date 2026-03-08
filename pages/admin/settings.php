<?php
/**
 * pages/admin/settings.php — Application settings with editable form
 */
requireRole('admin');
$pageTitle = 'Pengaturan';
$pagePretitle = 'Admin';

// ─── Handle POST ─────────────────────────────────────────────────────────────
$success = false;
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Fields that are allowed to be edited via UI
    $allowed = ['APP_NAME', 'APP_URL', 'UPLOAD_MAX_MB', 'WA_NUMBER_DEFAULT'];

    $envFile = BASE_PATH . '/.env';
    $lines = file_exists($envFile)
        ? file($envFile, FILE_IGNORE_NEW_LINES)
        : [];

    // Build a key=>value map from current .env
    $env = [];
    foreach ($lines as $line) {
        $line = trim($line);
        if ($line === '' || str_starts_with($line, '#')) {
            $env[] = ['raw' => $line];
            continue;
        }
        if (strpos($line, '=') !== false) {
            [$k, $v] = explode('=', $line, 2);
            $env[] = ['key' => trim($k), 'val' => trim($v)];
        } else {
            $env[] = ['raw' => $line];
        }
    }

    // Apply submitted values
    foreach ($env as &$entry) {
        if (!isset($entry['key']))
            continue;
        if (in_array($entry['key'], $allowed, true) && isset($_POST[$entry['key']])) {
            $entry['val'] = trim($_POST[$entry['key']]);
        }
    }
    unset($entry);

    // Rebuild .env content
    $out = '';
    foreach ($env as $entry) {
        if (isset($entry['raw'])) {
            $out .= $entry['raw'] . "\n";
        } else {
            $out .= $entry['key'] . '=' . $entry['val'] . "\n";
        }
    }

    if (file_put_contents($envFile, $out) !== false) {
        $success = true;
        flash('success', 'Pengaturan berhasil disimpan. Perubahan akan berlaku pada request berikutnya.');
    } else {
        flash('danger', 'Gagal menyimpan pengaturan. Pastikan file .env dapat ditulis oleh server.');
    }

    redirect(url('/admin/settings'));
}

ob_start();
?>

<?php if ($error): ?>
    <div class="alert alert-danger mb-3"><?= e($error) ?></div>
<?php endif; ?>

<div class="row g-4">
    <!-- Left: Edit Form -->
    <div class="col-lg-7">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                        class="icon me-2 text-primary">
                        <path
                            d="M10.325 4.317c.426 -1.756 2.924 -1.756 3.35 0a1.724 1.724 0 0 0 2.573 1.066c1.543 -.94 3.31 .826 2.37 2.37a1.724 1.724 0 0 0 1.066 2.573c1.756 .426 1.756 2.924 0 3.35a1.724 1.724 0 0 0 -1.066 2.573c.94 1.543 -.826 3.31 -2.37 2.37a1.724 1.724 0 0 0 -2.573 1.066c-.426 1.756 -2.924 1.756 -3.35 0a1.724 1.724 0 0 0 -2.573 -1.066c-1.543 .94 -3.31 -.826 -2.37 -2.37a1.724 1.724 0 0 0 -1.066 -2.573c-1.756 -.426 -1.756 -2.924 0 -3.35a1.724 1.724 0 0 0 1.066 -2.573c-.94 -1.543 .826 -3.31 2.37 -2.37c1 .608 2.296 .07 2.572 -1.065z" />
                        <path d="M9 12a3 3 0 1 0 6 0a3 3 0 0 0 -6 0" />
                    </svg>
                    Pengaturan Aplikasi
                </h3>
            </div>
            <div class="card-body">
                <form method="POST" action="<?= url('/admin/settings') ?>">
                    <!-- APP NAME -->
                    <div class="mb-3">
                        <label class="form-label required">Nama Aplikasi</label>
                        <input type="text" name="APP_NAME" class="form-control" value="<?= e(APP_NAME) ?>"
                            placeholder="Contoh: IT Portal Helpdesk" maxlength="60" required>
                        <div class="form-hint">Nama ini muncul di sidebar, tab browser, dan email.</div>
                    </div>

                    <!-- APP URL -->
                    <div class="mb-3">
                        <label class="form-label required">URL Aplikasi</label>
                        <input type="url" name="APP_URL" class="form-control" value="<?= e(APP_URL) ?>"
                            placeholder="http://localhost/itportal" required>
                        <div class="form-hint">URL dasar tanpa trailing slash. Digunakan untuk link email & redirect.
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <!-- UPLOAD MAX MB -->
                            <label class="form-label">Upload Maksimum (MB)</label>
                            <div class="input-group">
                                <input type="number" name="UPLOAD_MAX_MB" class="form-control"
                                    value="<?= e(UPLOAD_MAX_MB) ?>" min="1" max="100">
                                <span class="input-group-text">MB</span>
                            </div>
                            <div class="form-hint">Ukuran maksimum file saat upload lampiran.</div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <!-- WA NUMBER -->
                            <label class="form-label">Nomor WhatsApp Default</label>
                            <div class="input-group">
                                <span class="input-group-text">+</span>
                                <input type="text" name="WA_NUMBER_DEFAULT" class="form-control"
                                    value="<?= e(WA_NUMBER_DEFAULT) ?>" placeholder="628123456789" pattern="[0-9]+"
                                    title="Hanya angka, tanpa + atau -">
                            </div>
                            <div class="form-hint">Format internasional tanpa +. Contoh: 6281234567890</div>
                        </div>
                    </div>

                    <div class="form-footer">
                        <button type="submit" class="btn btn-primary">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24"
                                fill="none" stroke="currentColor" stroke-width="2" class="icon me-1">
                                <path d="M6 4h10l4 4v10a2 2 0 0 1 -2 2h-12a2 2 0 0 1 -2 -2v-12a2 2 0 0 1 2 -2" />
                                <path d="M10 14a2 2 0 1 0 4 0a2 2 0 0 0 -4 0" />
                                <path d="M8 4v4h6v-4" />
                            </svg>
                            Simpan Pengaturan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Right: Info Panel -->
    <div class="col-lg-5">
        <!-- System Info -->
        <div class="card mb-3">
            <div class="card-header">
                <h3 class="card-title">Informasi Sistem</h3>
            </div>
            <div class="card-body p-0">
                <div class="list-group list-group-flush">
                    <div class="list-group-item">
                        <div class="row align-items-center">
                            <div class="col text-truncate">
                                <span class="text-secondary">Nama Aplikasi</span>
                            </div>
                            <div class="col-auto">
                                <strong><?= e(APP_NAME) ?></strong>
                            </div>
                        </div>
                    </div>
                    <div class="list-group-item">
                        <div class="row align-items-center">
                            <div class="col text-truncate">
                                <span class="text-secondary">Environment</span>
                            </div>
                            <div class="col-auto">
                                <span class="badge bg-<?= APP_ENV === 'local' ? 'yellow' : 'green' ?>">
                                    <?= e(APP_ENV) ?>
                                </span>
                            </div>
                        </div>
                    </div>
                    <div class="list-group-item">
                        <div class="row align-items-center">
                            <div class="col text-truncate">
                                <span class="text-secondary">PHP Version</span>
                            </div>
                            <div class="col-auto">
                                <code><?= phpversion() ?></code>
                            </div>
                        </div>
                    </div>
                    <div class="list-group-item">
                        <div class="row align-items-center">
                            <div class="col text-truncate">
                                <span class="text-secondary">Database</span>
                            </div>
                            <div class="col-auto">
                                <code><?= e(DB_NAME) ?>@<?= e(DB_HOST) ?></code>
                            </div>
                        </div>
                    </div>
                    <div class="list-group-item">
                        <div class="row align-items-center">
                            <div class="col text-truncate">
                                <span class="text-secondary">Upload Max</span>
                            </div>
                            <div class="col-auto">
                                <strong><?= UPLOAD_MAX_MB ?> MB</strong>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Note -->
        <div class="alert alert-info">
            <div class="d-flex">
                <div>
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="2" class="icon alert-icon">
                        <path d="M3 12a9 9 0 1 0 18 0a9 9 0 0 0 -18 0" />
                        <path d="M12 9h.01" />
                        <path d="M11 12h1v4h1" />
                    </svg>
                </div>
                <div class="ms-2">
                    <h4 class="alert-title">Catatan Penting</h4>
                    <div class="text-secondary">
                        Pengaturan disimpan ke file <code>.env</code>. Perubahan nama aplikasi akan langsung terlihat
                        setelah halaman di-refresh. Pengaturan DB, TOKEN_SECRET, dan APP_ENV hanya bisa diubah manual
                        via file <code>.env</code>.
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
include BASE_PATH . '/layouts/main.php';
