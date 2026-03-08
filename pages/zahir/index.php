<?php
/**
 * pages/zahir/index.php — Zahir License list
 */
requireRole('admin', 'staff');

$pageTitle = 'Zahir License';
$pagePretitle = 'Data Master';

$search = trim($_GET['q'] ?? '');
$where = ["z.is_active = 1"];
$params = [];
if ($search) {
    $where[] = "(z.user_name LIKE ? OR z.serial_number LIKE ? OR z.company_name LIKE ? OR z.activation_code LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = "%$search%";
}
$whereSQL = 'WHERE ' . implode(' AND ', $where);

$stmt = db()->prepare("SELECT z.* FROM zahir_license z $whereSQL ORDER BY z.user_name");
$stmt->execute($params);
$licenses = $stmt->fetchAll();

ob_start();
?>
<div class="card mb-3">
    <div class="card-body">
        <form method="GET" class="row g-3 align-items-end">
            <div class="col-md-5">
                <label class="form-label">Cari</label>
                <div class="input-icon">
                    <span class="input-icon-addon"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                            viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                            stroke-linecap="round" stroke-linejoin="round" class="icon">
                            <path d="M10 10m-7 0a7 7 0 1 0 14 0a7 7 0 1 0 -14 0" />
                            <path d="M21 21l-6 -6" />
                        </svg></span>
                    <input type="text" name="q" class="form-control" placeholder="User / Serial / Activation Code..."
                        value="<?= e($search) ?>">
                </div>
            </div>
            <div class="col-auto"><button type="submit" class="btn btn-primary"><svg xmlns="http://www.w3.org/2000/svg"
                        width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                        stroke-linecap="round" stroke-linejoin="round" class="icon">
                        <path d="M10 10m-7 0a7 7 0 1 0 14 0a7 7 0 1 0 -14 0" />
                        <path d="M21 21l-6 -6" />
                    </svg> Filter</button></div>
        </form>
    </div>
</div>
<div class="card">
    <div class="card-header">
        <h3 class="card-title">
            <?= count($licenses) ?> license
        </h3>
    </div>
    <div class="table-responsive">
        <table class="table table-vcenter card-table table-striped">
            <thead>
                <tr>
                    <th>User</th>
                    <th>Serial Number</th>
                    <th>Package</th>
                    <th>Feature</th>
                    <th>Activation Code</th>
                    <th>Company</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($licenses as $l): ?>
                    <tr>
                        <td class="fw-bold">
                            <?= e($l['user_name']) ?>
                        </td>
                        <td><code class="small"><?= e($l['serial_number'] ?? '—') ?></code></td>
                        <td><span class="badge bg-purple-lt">
                                <?= e($l['package_name'] ?? '—') ?>
                            </span></td>
                        <td><span class="text-truncate d-inline-block small" style="max-width:200px">
                                <?= e($l['feature_code'] ?? '—') ?>
                            </span></td>
                        <td><code><?= e($l['activation_code'] ?? '—') ?></code></td>
                        <td class="text-secondary small">
                            <?= e($l['company_name'] ?? '—') ?>
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
