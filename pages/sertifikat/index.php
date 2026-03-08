<?php
/**
 * pages/sertifikat/index.php - Dealer certificates loaded from CSV (P12 source)
 */
requireRole('admin', 'staff');

$pageTitle = 'Sertifikat Dealer';
$pagePretitle = 'Data Master';

$filterArea = $_GET['area'] ?? '';
$filterYear = $_GET['year'] ?? '';
$filterStatus = $_GET['status'] ?? '';
$search = trim($_GET['q'] ?? '');

$today = date('Y-m-d');
$warn90 = date('Y-m-d', strtotime('+90 days'));

$csvPath = BASE_PATH . '/storage/data/dpackweb/sertifikat_data_per_kode_terbaru.csv';
$allCerts = [];

if (is_file($csvPath) && is_readable($csvPath)) {
    $fh = fopen($csvPath, 'r');
    if ($fh !== false) {
        $headers = fgetcsv($fh);
        if (is_array($headers)) {
            $headers = array_map(static fn($h) => trim((string) $h), $headers);
            while (($row = fgetcsv($fh)) !== false) {
                if (count($row) !== count($headers)) {
                    continue;
                }
                $r = array_combine($headers, $row);
                if (!is_array($r)) {
                    continue;
                }

                $allCerts[] = [
                    'area' => trim((string) ($r['area'] ?? '')),
                    'nama_dealer' => trim((string) ($r['dealer_folder'] ?? '')),
                    'kode_dealer' => trim((string) ($r['kode_final'] ?? '')),
                    'serial_number' => trim((string) ($r['serial_number'] ?? '')),
                    'tanggal_mulai' => trim((string) ($r['not_before'] ?? '')),
                    'tanggal_akhir' => trim((string) ($r['not_after'] ?? '')),
                    'cert_status' => trim((string) ($r['cert_status'] ?? '')),
                    'has_file' => trim((string) ($r['cert_status'] ?? '')) !== 'NO_P12',
                ];
            }
        }
        fclose($fh);
    }
}

$areas = array_values(array_unique(array_filter(array_map(static fn($c) => $c['area'], $allCerts))));
sort($areas);

$years = [];
foreach ($allCerts as $c) {
    if (!empty($c['tanggal_akhir'])) {
        $years[] = (int) date('Y', strtotime($c['tanggal_akhir']));
    }
}
$years = array_values(array_unique(array_filter($years)));
sort($years);

$certs = array_values(array_filter($allCerts, static function (array $c) use (
    $filterArea,
    $filterYear,
    $filterStatus,
    $search,
    $today,
    $warn90
) {
    $tanggalAkhir = $c['tanggal_akhir'] ?? '';
    $hasDate = $tanggalAkhir !== '';
    $expired = $hasDate && $tanggalAkhir < $today;
    $expiring = $hasDate && !$expired && $tanggalAkhir <= $warn90;

    if ($filterArea && ($c['area'] ?? '') !== $filterArea) {
        return false;
    }
    if ($filterYear) {
        if (!$hasDate || (int) date('Y', strtotime($tanggalAkhir)) !== (int) $filterYear) {
            return false;
        }
    }
    if ($filterStatus === 'expired' && !$expired) {
        return false;
    }
    if ($filterStatus === 'expiring' && !$expiring) {
        return false;
    }
    if ($filterStatus === 'active' && (!$hasDate || $tanggalAkhir <= $warn90)) {
        return false;
    }
    if ($search) {
        $needle = mb_strtolower($search);
        $hay = mb_strtolower(
            ($c['nama_dealer'] ?? '') . ' ' .
            ($c['kode_dealer'] ?? '') . ' ' .
            ($c['serial_number'] ?? '')
        );
        if (strpos($hay, $needle) === false) {
            return false;
        }
    }
    return true;
}));

usort($certs, static function (array $a, array $b): int {
    $ad = $a['tanggal_akhir'] ?: '9999-12-31';
    $bd = $b['tanggal_akhir'] ?: '9999-12-31';
    return strcmp($ad, $bd);
});

if (empty($allCerts) && !is_file($csvPath)) {
    flash('danger', 'File data sertifikat tidak ditemukan: storage/data/dpackweb/sertifikat_data_per_kode_terbaru.csv');
}

ob_start();
?>
<div class="card mb-3">
    <div class="card-body">
        <form method="GET" class="row g-3 align-items-end">
            <div class="col-md-3">
                <label class="form-label">Cari</label>
                <div class="input-icon">
                    <span class="input-icon-addon"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                            viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                            stroke-linecap="round" stroke-linejoin="round" class="icon">
                            <path d="M10 10m-7 0a7 7 0 1 0 14 0a7 7 0 1 0 -14 0" />
                            <path d="M21 21l-6 -6" />
                        </svg></span>
                    <input type="text" name="q" class="form-control" placeholder="Nama / Kode / Serial..."
                        value="<?= e($search) ?>">
                </div>
            </div>
            <div class="col-md-2">
                <label class="form-label">Area</label>
                <select name="area" class="form-select">
                    <option value="">Semua</option>
                    <?php foreach ($areas as $a): ?>
                        <option value="<?= e($a) ?>" <?= $filterArea === $a ? 'selected' : '' ?>>
                            <?= e($a) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">Tahun Expire</label>
                <select name="year" class="form-select">
                    <option value="">Semua Tahun</option>
                    <?php foreach ($years as $yr): ?>
                        <option value="<?= e($yr) ?>" <?= $filterYear == $yr ? 'selected' : '' ?>>
                            <?= e($yr) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">Status</label>
                <select name="status" class="form-select">
                    <option value="">Semua Status</option>
                    <option value="active" <?= $filterStatus === 'active' ? 'selected' : '' ?>>Active</option>
                    <option value="expiring" <?= $filterStatus === 'expiring' ? 'selected' : '' ?>>Expiring Soon (&lt;90hr)
                    </option>
                    <option value="expired" <?= $filterStatus === 'expired' ? 'selected' : '' ?>>Expired</option>
                </select>
            </div>
            <div class="col-auto d-flex gap-2">
                <button type="submit" class="btn btn-primary"><svg xmlns="http://www.w3.org/2000/svg" width="24"
                        height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                        stroke-linecap="round" stroke-linejoin="round" class="icon">
                        <path d="M10 10m-7 0a7 7 0 1 0 14 0a7 7 0 1 0 -14 0" />
                        <path d="M21 21l-6 -6" />
                    </svg> Filter</button>
                <a href="<?= url('/sertifikat') ?>" class="btn btn-outline-secondary">Reset</a>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h3 class="card-title"><?= count($certs) ?> sertifikat</h3>
    </div>
    <div class="table-responsive">
        <table class="table table-vcenter card-table table-striped">
            <thead>
                <tr>
                    <th>Area</th>
                    <th>Dealer</th>
                    <th>Kode</th>
                    <th>Serial Number</th>
                    <th>Kode DpackWeb</th>
                    <th>Mulai</th>
                    <th>Berakhir</th>
                    <th>Status</th>
                    <th class="text-center" style="width:110px">DpackWeb</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($certs as $c):
                    $expired = !empty($c['tanggal_akhir']) && $c['tanggal_akhir'] < $today;
                    $expiring = !empty($c['tanggal_akhir']) && !$expired && $c['tanggal_akhir'] <= $warn90;
                    $hasFile = !empty($c['has_file']);
                    ?>
                    <tr class="<?= $expired ? 'bg-red-lt' : ($expiring ? 'bg-yellow-lt' : '') ?>">
                        <td><span class="badge bg-azure-lt">
                                <?= e($c['area']) ?>
                            </span></td>
                        <td>
                            <?= e($c['nama_dealer']) ?>
                        </td>
                        <td class="text-primary fw-bold">
                            <?= e($c['kode_dealer']) ?>
                        </td>
                        <td><code><?= e($c['serial_number'] ?: '-') ?></code></td>
                        <td>
                            <?= e($c['kode_dealer'] ?: '-') ?>
                        </td>
                        <td class="text-nowrap">
                            <?= $c['tanggal_mulai'] ? date('d M Y', strtotime($c['tanggal_mulai'])) : '-' ?>
                        </td>
                        <td class="text-nowrap">
                            <?= $c['tanggal_akhir'] ? date('d M Y', strtotime($c['tanggal_akhir'])) : '-' ?>
                        </td>
                        <td>
                            <?php if (!$hasFile): ?>
                                <span class="badge bg-secondary">No File</span>
                            <?php elseif ($expired): ?>
                                <span class="badge bg-red">Expired</span>
                            <?php elseif ($expiring): ?>
                                <span class="badge bg-yellow">Expiring Soon</span>
                            <?php else: ?>
                                <span class="badge bg-green">Active</span>
                            <?php endif; ?>
                        </td>
                        <td class="text-center">
                            <?php if ($hasFile): ?>
                                <a href="<?= url('/sertifikat/download?kode=' . rawurlencode($c['kode_dealer']) . '&dealer=' . rawurlencode($c['nama_dealer'])) ?>"
                                    class="btn btn-sm btn-primary" data-no-loader
                                    title="Download ZIP <?= e($c['kode_dealer']) ?>">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24"
                                        fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                        stroke-linejoin="round" class="icon me-1">
                                        <path d="M4 17v2a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2v-2" />
                                        <path d="M7 11l5 5l5-5" />
                                        <path d="M12 4l0 12" />
                                    </svg>
                                    ZIP
                                </a>
                            <?php else: ?>
                                <span class="btn btn-sm btn-outline-secondary disabled" title="File tidak tersedia">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24"
                                        fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                        stroke-linejoin="round" class="icon me-1">
                                        <path d="M4 17v2a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2v-2" />
                                        <path d="M7 11l5 5l5-5" />
                                        <path d="M12 4l0 12" />
                                    </svg>
                                    ZIP
                                </span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php
$content = ob_get_clean();
include BASE_PATH . '/layouts/main.php';
