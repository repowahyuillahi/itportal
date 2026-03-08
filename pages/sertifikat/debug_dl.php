<?php
require_once __DIR__ . '/../../includes/bootstrap.php';
requireRole('admin', 'staff');

// Quick debug for download.php
echo "<pre>";

// Check ZipArchive
echo "ZipArchive: " . (class_exists('ZipArchive') ? '✅ Available' : '❌ NOT available') . "\n";
echo "sys_get_temp_dir: " . sys_get_temp_dir() . "\n";
$tmp = tempnam(sys_get_temp_dir(), 'test_');
echo "Temp file writable: " . ($tmp ? '✅ ' . $tmp : '❌') . "\n";
if ($tmp)
    unlink($tmp);

echo "\n--- Scanning dpackweb folders ---\n";
$dpackRoot = BASE_PATH . '/storage/data/dpackweb';
$found = [];
foreach (glob($dpackRoot . '/*', GLOB_ONLYDIR) as $areaDir) {
    foreach (glob($areaDir . '/*', GLOB_ONLYDIR) as $f) {
        $name = basename($f);
        if (preg_match('/\(([^)]+)\)/', $name, $m)) {
            $norm = strtoupper(preg_replace('/[\s\-]/', '', $m[1]));
            $found[$norm] = $name;
        }
    }
}
echo "Total folders with codes: " . count($found) . "\n";

// Check DB vs folder
$certs = db()->query("SELECT id, nama_dealer, kode_dealer FROM sertifikat WHERE is_active=1 ORDER BY kode_dealer")->fetchAll();
echo "\n--- DB kode vs folder match ---\n";
printf("%-15s %-30s %-6s %s\n", "KODE_DB", "NAMA_DB", "MATCH", "FOLDER");
echo str_repeat("-", 80) . "\n";
foreach ($certs as $c) {
    $norm = strtoupper(preg_replace('/[\s\-]/', '', $c['kode_dealer']));
    $match = isset($found[$norm]);
    $folder = $found[$norm] ?? '—';
    printf(
        "%-15s %-30s %-6s %s\n",
        $c['kode_dealer'],
        substr($c['nama_dealer'], 0, 28),
        $match ? '✅' : '❌',
        $folder
    );
}
echo "</pre>";
