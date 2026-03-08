<?php
/**
 * pages/dashboard/index.php — Dashboard focused on Maintenance Reports
 */
$pageTitle = 'Dashboard';
$pagePretitle = 'Overview';

$_user = currentUser();

// ─── Maintenance Stats ───
$mtOpen = db()->query("SELECT COUNT(*) FROM maintenance_reports WHERE status = 'Open' AND is_active = 1")->fetchColumn();
$mtToday = db()->query("SELECT COUNT(*) FROM maintenance_reports WHERE DATE(tanggal) = CURDATE() AND is_active = 1")->fetchColumn();
$mtClosed = db()->query("SELECT COUNT(*) FROM maintenance_reports WHERE status = 'Closed' AND is_active = 1")->fetchColumn();
$mtTotal = db()->query("SELECT COUNT(*) FROM maintenance_reports WHERE is_active = 1")->fetchColumn();

// Top item categories (for quick insight)
$topItems = db()->query("SELECT item, COUNT(*) as cnt FROM maintenance_reports WHERE is_active = 1 GROUP BY item ORDER BY cnt DESC LIMIT 5")->fetchAll();

// Recent maintenance (10 terbaru berdasarkan tanggal)
$recentMaint = db()->query("SELECT * FROM maintenance_reports WHERE is_active = 1 ORDER BY tanggal DESC, id DESC LIMIT 10")->fetchAll();

// Dealer count & certificate expiring
$totalDealers = db()->query("SELECT COUNT(*) FROM dealers WHERE is_active = 1")->fetchColumn();
$certExpiring = db()->query("SELECT COUNT(*) FROM sertifikat WHERE is_active = 1 AND tanggal_akhir <= DATE_ADD(CURDATE(), INTERVAL 90 DAY)")->fetchColumn();

// ─── Chart Data (7 Days Trend) ───
$trendQuery = db()->query("
    SELECT DATE(tanggal) as tgl, COUNT(*) as cnt 
    FROM maintenance_reports 
    WHERE is_active = 1 AND tanggal >= DATE_SUB(CURDATE(), INTERVAL 6 DAY)
    GROUP BY DATE(tanggal) 
    ORDER BY DATE(tanggal) ASC
")->fetchAll();

$chartDates = [];
$chartCounts = [];
// pad 7 days manually to ensure no gaps
for ($i = 6; $i >= 0; $i--) {
    $d = date('Y-m-d', strtotime("-$i days"));
    $chartDates[] = date('d M', strtotime($d));
    $foundCnt = 0;
    foreach ($trendQuery as $row) {
        if ($row['tgl'] === $d) {
            $foundCnt = (int) $row['cnt'];
            break;
        }
    }
    $chartCounts[] = $foundCnt;
}

// ─── Chart Data (Status Proportion) ───
$statusQuery = db()->query("
    SELECT status, COUNT(*) as cnt 
    FROM maintenance_reports 
    WHERE is_active = 1 
    GROUP BY status
")->fetchAll();
$statusMap = ['Open' => 0, 'In Progress' => 0, 'Closed' => 0];
foreach ($statusQuery as $row) {
    if (isset($statusMap[$row['status']])) {
        $statusMap[$row['status']] = (int) $row['cnt'];
    }
}
$statusSeries = array_values($statusMap);
$statusLabels = array_keys($statusMap);

ob_start();
?>
<!-- Welcome Card -->
<div class="row row-deck row-cards">
    <div class="col-12">
        <div class="card card-md">
            <div class="card-stamp">
                <div class="card-stamp-icon bg-primary">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                        class="icon">
                        <path d="M5 12l-2 0l9 -9l9 9l-2 0" />
                        <path d="M5 12v7a2 2 0 0 0 2 2h10a2 2 0 0 0 2 -2v-7" />
                        <path d="M9 21v-6a2 2 0 0 1 2 -2h2a2 2 0 0 1 2 2v6" />
                    </svg>
                </div>
            </div>
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col-10">
                        <h3 class="h1">Welcome back, <?= e($_user['full_name'] ?? 'User') ?></h3>
                        <div class="markdown text-secondary">
                            <?php if ($mtOpen > 0): ?>
                                Ada <strong><?= $mtOpen ?></strong> laporan maintenance yang masih open dan perlu ditangani.
                            <?php else: ?>
                                Semua laporan sudah ditangani. Tidak ada laporan open saat ini. 👍
                            <?php endif; ?>
                            <?php if ($certExpiring > 0): ?>
                                <br><span class="text-warning">⚠ <?= $certExpiring ?> sertifikat dealer akan segera
                                    expired.</span>
                            <?php endif; ?>
                        </div>
                        <div class="mt-3">
                            <a href="<?= url('/maintenance?status=Open') ?>" class="btn btn-primary me-2">Open
                                Maintenance</a>
                            <a href="<?= url('/maintenance/create') ?>" class="btn btn-outline-primary">Buat Laporan</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Stat Cards -->
    <div class="col-sm-6 col-lg-3">
        <div class="card">
            <div class="card-body">
                <div class="subheader mb-2">OPEN</div>
                <div class="h1 mb-1"><?= $mtOpen ?></div>
                <div class="text-secondary small">Menunggu penanganan</div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-lg-3">
        <div class="card">
            <div class="card-body">
                <div class="subheader mb-2">TODAY</div>
                <div class="h1 mb-1"><?= $mtToday ?></div>
                <div class="text-secondary small">Laporan masuk hari ini</div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-lg-3">
        <div class="card">
            <div class="card-body">
                <div class="subheader mb-2">CLOSED</div>
                <div class="h1 mb-1"><?= $mtClosed ?></div>
                <div class="text-secondary small">Selesai</div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-lg-3">
        <div class="card">
            <div class="card-body">
                <div class="subheader mb-2">TOTAL</div>
                <div class="h1 mb-1"><?= $mtTotal ?></div>
                <div class="text-secondary small">Semua laporan</div>
            </div>
        </div>
    </div>

    <!-- ── Baris 2: Charts ── -->
    <div class="col-lg-8 border-top-0 pt-3">
        <div class="card">
            <div class="card-header border-0 pb-0">
                <h3 class="card-title">Tren Laporan (7 Hari Terakhir)</h3>
            </div>
            <div class="card-body">
                <div id="chart-trend" style="height: 300px;"></div>
            </div>
        </div>
    </div>
    <div class="col-lg-4 border-top-0 pt-3">
        <div class="card">
            <div class="card-header border-0 pb-0">
                <h3 class="card-title">Proporsi Status</h3>
            </div>
            <div class="card-body d-flex align-items-center justify-content-center">
                <div id="chart-status" style="width: 100%;"></div>
            </div>
        </div>
    </div>

    <!-- ── Baris 3: Konten Utama (Laporan) + Panel Sekunder ── -->

    <!-- Laporan Terbaru – konten utama -->
    <div class="col-lg-8 border-top-0 pt-3">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Laporan Terbaru</h3>
                <div class="card-actions">
                    <a href="<?= url('/maintenance') ?>" class="btn btn-primary btn-sm">Lihat Semua</a>
                </div>
            </div>
            <div class="table-responsive">
                <table class="table table-vcenter card-table">
                    <thead>
                        <tr>
                            <th>Tanggal</th>
                            <th>Pelapor</th>
                            <th>Dealer</th>
                            <th>Item</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($recentMaint)): ?>
                            <tr>
                                <td colspan="5" class="text-center text-secondary">Belum ada laporan.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($recentMaint as $m): ?>
                                <tr class="cursor-pointer"
                                    onclick="window.location='<?= url('/maintenance/view?id=' . $m['id']) ?>'">
                                    <td class="text-nowrap text-secondary"><?= date('d M Y', strtotime($m['tanggal'])) ?></td>
                                    <td><?= e($m['pelapor']) ?></td>
                                    <td><?= e($m['dealer']) ?></td>
                                    <td><span class="badge bg-azure-lt"><?= e($m['item']) ?></span></td>
                                    <td>
                                        <span class="badge bg-<?= match ($m['status']) {
                                            'Open' => 'yellow',
                                            'In Progress' => 'blue',
                                            'Closed' => 'green',
                                            default => 'secondary'
                                        } ?>-lt">
                                            <?= e($m['status']) ?>
                                        </span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Panel Sekunder (kanan) -->
    <div class="col-lg-4">
        <div class="row g-3">

            <!-- Top Kategori Item -->
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Top Kategori Item</h3>
                    </div>
                    <div class="card-body">
                        <?php foreach ($topItems as $ti):
                            $pct = $mtTotal > 0 ? round(($ti['cnt'] / $mtTotal) * 100) : 0;
                            ?>
                            <div class="mb-3">
                                <div class="d-flex justify-content-between mb-1">
                                    <span><?= e($ti['item']) ?></span>
                                    <span class="text-secondary"><?= $ti['cnt'] ?> (<?= $pct ?>%)</span>
                                </div>
                                <div class="progress progress-sm">
                                    <div class="progress-bar bg-primary" style="width: <?= $pct ?>%"></div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <!-- Mini Stats: Dealers & Sertifikat -->
            <div class="col-6">
                <div class="card">
                    <div class="card-body text-center">
                        <div class="subheader mb-2">DEALERS</div>
                        <div class="h1 mb-1"><?= $totalDealers ?></div>
                        <div class="text-secondary small mb-3">Total aktif</div>
                        <a href="<?= url('/dealers') ?>" class="btn btn-sm btn-outline-primary w-100">Lihat</a>
                    </div>
                </div>
            </div>
            <div class="col-6">
                <div class="card">
                    <div class="card-body text-center">
                        <div class="subheader mb-2">SERTIFIKAT</div>
                        <div class="h1 mb-1 <?= $certExpiring > 0 ? 'text-warning' : '' ?>"><?= $certExpiring ?></div>
                        <div class="text-secondary small mb-3">Expiring (90hr)</div>
                        <a href="<?= url('/sertifikat') ?>" class="btn btn-sm btn-outline-warning w-100">Cek</a>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", function() {
    // Trend Chart Configuration
    var optionsTrend = {
        series: [{
            name: 'Laporan Baru',
            data: <?= json_encode($chartCounts) ?>
        }],
        chart: {
            height: 300,
            type: 'area',
            fontFamily: 'inherit',
            parentHeightOffset: 0,
            toolbar: {
                show: false
            },
            animations: {
                enabled: true
            }
        },
        dataLabels: {
            enabled: false
        },
        stroke: {
            curve: 'smooth',
            width: 2,
            colors: ['#0054a6']
        },
        fill: {
            type: 'gradient',
            gradient: {
                shadeIntensity: 1,
                opacityFrom: 0.4,
                opacityTo: 0.05,
                stops: [0, 90, 100]
            },
            colors: ['#0054a6']
        },
        xaxis: {
            categories: <?= json_encode($chartDates) ?>,
            tooltip: {
                enabled: false
            },
            axisBorder: {
                show: false
            }
        },
        yaxis: {
            labels: {
                padding: 4
            },
        },
        colors: ['#0054a6'],
        grid: {
            strokeDashArray: 4,
            padding: {
                top: -20,
                right: 0,
                left: -4,
                bottom: -4
            }
        }
    };

    var chartTrend = new ApexCharts(document.querySelector("#chart-trend"), optionsTrend);
    chartTrend.render();

    // Status Chart Configuration
    var optionsStatus = {
        series: <?= json_encode($statusSeries) ?>,
        labels: <?= json_encode($statusLabels) ?>,
        chart: {
            type: 'donut',
            height: 250,
            fontFamily: 'inherit',
        },
        colors: ['#f59f00', '#206bc4', '#2fb344'], // Open(Yellow), In Progress(Blue), Closed(Green)
        plotOptions: {
            pie: {
                donut: {
                    size: '65%'
                }
            }
        },
        dataLabels: {
            enabled: false
        },
        legend: {
            position: 'bottom'
        }
    };

    var chartStatus = new ApexCharts(document.querySelector("#chart-status"), optionsStatus);
    chartStatus.render();
});
</script>

<?php
$content = ob_get_clean();
include BASE_PATH . '/layouts/main.php';
