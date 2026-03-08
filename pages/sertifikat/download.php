<?php
/**
 * pages/sertifikat/download.php
 * Streams a ZIP of the dealer's DPackWeb certificate folder (.P12 + .PIN)
 * matched by kode query parameter from CSV-based listing.
 *
 * URL: /sertifikat/download?kode=<kode>&dealer=<nama>
 */
requireRole('admin', 'staff');

if (!class_exists('ZipArchive')) {
    http_response_code(500);
    exit('Error: Ekstensi PHP zip tidak aktif. Aktifkan extension=zip di php.ini dan restart Apache.');
}

$kode = trim((string) ($_GET['kode'] ?? ''));
$namaDealer = trim((string) ($_GET['dealer'] ?? ''));
$downloadAll = (int) ($_GET['all'] ?? 0) === 1;

// Backward compatibility for old links: /sertifikat/download?id=<id>
$id = (int) ($_GET['id'] ?? 0);
if ($kode === '' && $id > 0) {
    $stmt = db()->prepare('SELECT kode_dealer, nama_dealer FROM sertifikat WHERE id = ? AND is_active = 1');
    $stmt->execute([$id]);
    $cert = $stmt->fetch();
    if ($cert) {
        $kode = trim((string) ($cert['kode_dealer'] ?? ''));
        $namaDealer = trim((string) ($cert['nama_dealer'] ?? ''));
    }
}

if ($kode === '' && $namaDealer === '') {
    if ($downloadAll !== 1) {
        http_response_code(400);
        exit('Parameter tidak valid. Gunakan kode atau dealer.');
    }
}

$dpackRoot = BASE_PATH . '/storage/data/dpackweb';
$zipDir = $dpackRoot . '/zips';
$masterZip = $dpackRoot . '/dpackweb_all_sertifikat.zip';

if ($downloadAll === 1) {
    if (!is_file($masterZip)) {
        http_response_code(404);
        exit('File ZIP gabungan belum tersedia.');
    }
    header('Content-Type: application/zip');
    header('Content-Disposition: attachment; filename="dpackweb_all_sertifikat.zip"');
    header('Content-Length: ' . filesize($masterZip));
    header('Cache-Control: no-cache, no-store, must-revalidate');
    header('Pragma: no-cache');
    header('Expires: 0');
    readfile($masterZip);
    exit;
}

if ($kode === '' && $namaDealer === '') {
    http_response_code(400);
    exit('Parameter tidak valid. Gunakan kode atau dealer.');
}

function normKode(string $s): string
{
    return strtoupper(preg_replace('/[\s\-]/', '', $s));
}

function normKodeShort(string $s): string
{
    return preg_replace('/^AP/', '', normKode($s));
}

function extractKodeFromFolderName(string $folderName): string
{
    if (preg_match('/\(([^)]+)\)/', $folderName, $m)) {
        return normKode($m[1]);
    }
    return '';
}

function extractKodeFromP12Files(string $folderPath): string
{
    $p12 = glob($folderPath . '/*.p12');
    if (empty($p12)) {
        $p12 = glob($folderPath . '/*.P12');
    }
    if (!empty($p12)) {
        return normKode(pathinfo($p12[0], PATHINFO_FILENAME));
    }
    return '';
}

$targetNorm = normKode($kode);
$targetShort = normKodeShort($kode);
$dealerNorm = normKode($namaDealer);

$prebuiltZip = null;
if ($targetNorm !== '') {
    $zipByKode = $zipDir . '/' . preg_replace('/[^A-Za-z0-9_\-]/', '_', $targetNorm) . '.zip';
    $zipByShort = $zipDir . '/' . preg_replace('/[^A-Za-z0-9_\-]/', '_', $targetShort) . '.zip';
    if (is_file($zipByKode)) {
        $prebuiltZip = $zipByKode;
    } elseif (is_file($zipByShort)) {
        $prebuiltZip = $zipByShort;
    }
}

if ($prebuiltZip && is_file($prebuiltZip)) {
    $safeNama = preg_replace('/[^A-Za-z0-9_\-]/', '_', $namaDealer ?: 'dealer');
    $safeKode = preg_replace('/[^A-Za-z0-9_\-]/', '_', $kode ?: pathinfo($prebuiltZip, PATHINFO_FILENAME));
    $filename = $safeNama . '_' . $safeKode . '.zip';
    header('Content-Type: application/zip');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Content-Length: ' . filesize($prebuiltZip));
    header('Cache-Control: no-cache, no-store, must-revalidate');
    header('Pragma: no-cache');
    header('Expires: 0');
    readfile($prebuiltZip);
    exit;
}

$dealerDir = null;

foreach (glob($dpackRoot . '/*', GLOB_ONLYDIR) as $areaDir) {
    $candidates = [$areaDir];
    foreach (glob($areaDir . '/*', GLOB_ONLYDIR) as $dealerFolder) {
        $candidates[] = $dealerFolder;
    }

    foreach ($candidates as $candidate) {
        $folderName = basename($candidate);
        $folderNormName = normKode($folderName);
        $folderKode = extractKodeFromFolderName($folderName);
        if ($folderKode === '') {
            $folderKode = extractKodeFromP12Files($candidate);
        }
        $folderShort = $folderKode ? preg_replace('/^AP/', '', $folderKode) : '';

        $kodeMatched = false;
        if ($targetNorm !== '') {
            $kodeMatched = ($folderKode !== '' && ($folderKode === $targetNorm || $folderShort === $targetShort));
        }

        $dealerMatched = false;
        if ($dealerNorm !== '') {
            $dealerMatched = ($folderNormName === $dealerNorm);
        }

        if (($targetNorm !== '' && $kodeMatched) || ($targetNorm === '' && $dealerMatched)) {
            $dealerDir = $candidate;
            break 2;
        }
    }
}

if (!$dealerDir) {
    http_response_code(404);
    exit('Folder sertifikat tidak ditemukan di storage DPackWeb.');
}

$files = array_filter((array) glob($dealerDir . '/*'), 'is_file');
if (empty($files)) {
    http_response_code(404);
    exit('Tidak ada file di folder dealer ini.');
}

$tmpZip = tempnam(sys_get_temp_dir(), 'cert_') . '.zip';
$zip = new ZipArchive();
if ($zip->open($tmpZip, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
    http_response_code(500);
    exit('Gagal membuat file ZIP (ZipArchive::open failed).');
}

foreach ($files as $file) {
    $zip->addFile($file, basename($file));
}
$zip->close();

$safeNama = preg_replace('/[^A-Za-z0-9_\-]/', '_', $namaDealer ?: basename($dealerDir));
$safeKode = preg_replace('/[^A-Za-z0-9_\-]/', '_', $kode ?: 'UNKNOWN');
$filename = $safeNama . '_' . $safeKode . '.zip';

header('Content-Type: application/zip');
header('Content-Disposition: attachment; filename="' . $filename . '"');
header('Content-Length: ' . filesize($tmpZip));
header('Cache-Control: no-cache, no-store, must-revalidate');
header('Pragma: no-cache');
header('Expires: 0');
readfile($tmpZip);
@unlink($tmpZip);
exit;
