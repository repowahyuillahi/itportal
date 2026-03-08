<?php
/**
 * layouts/main.php — Full Tabler.io Combo Layout (sidebar + top navbar)
 * Matches https://preview.tabler.io/layout-combo.html exactly
 */
$_user = currentUser();
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title><?= e($pageTitle ?? 'Dashboard') ?> — <?= e(APP_NAME) ?></title>
    <!-- Tabler Core v1.4.0 -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/core@1.4.0/dist/css/tabler.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/core@1.4.0/dist/css/tabler-vendors.min.css">
    <!-- Tabler Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@3.31.0/dist/tabler-icons.min.css">
    <!-- Custom -->
    <link rel="stylesheet" href="<?= asset('custom/app.css') ?>">
    <style>
        #page-loader {
            position: fixed;
            inset: 0;
            background: rgba(255, 255, 255, 0.92);
            backdrop-filter: blur(4px);
            z-index: 99999;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 20px;
            opacity: 0;
            pointer-events: none;
            transition: opacity .25s ease;
        }

        #page-loader.active {
            opacity: 1;
            pointer-events: all;
        }

        #page-loader .loader-label {
            font-size: .75rem;
            color: #888;
            letter-spacing: .1em;
            text-transform: uppercase;
        }

        .ldr-wrap {
            width: 100px;
            height: 50px;
            position: relative;
        }

        .ldr-dot {
            width: 16px;
            height: 16px;
            position: absolute;
            border-radius: 50%;
            background: #0054a6;
            left: 15%;
            transform-origin: 50%;
            animation: ldrBounce .5s alternate infinite ease;
        }

        @keyframes ldrBounce {
            0% {
                top: 40px;
                height: 5px;
                border-radius: 50px 50px 25px 25px;
                transform: scaleX(1.7);
            }

            40% {
                height: 16px;
                border-radius: 50%;
                transform: scaleX(1);
            }

            100% {
                top: 0%;
            }
        }

        .ldr-dot:nth-child(2) {
            left: 45%;
            animation-delay: .2s;
        }

        .ldr-dot:nth-child(3) {
            left: auto;
            right: 15%;
            animation-delay: .3s;
        }

        .ldr-shd {
            width: 16px;
            height: 4px;
            border-radius: 50%;
            background: rgba(0, 84, 166, .2);
            position: absolute;
            top: 42px;
            transform-origin: 50%;
            left: 15%;
            filter: blur(1px);
            animation: ldrShd .5s alternate infinite ease;
        }

        @keyframes ldrShd {
            0% {
                transform: scaleX(1.5);
            }

            40% {
                transform: scaleX(1);
                opacity: .7;
            }

            100% {
                transform: scaleX(.2);
                opacity: .4;
            }
        }

        .ldr-shd:nth-child(4) {
            left: 45%;
            animation-delay: .2s;
        }

        .ldr-shd:nth-child(5) {
            left: auto;
            right: 15%;
            animation-delay: .3s;
        }
    </style>
</head>

<body>
    <!-- Page Loading Overlay -->
    <div id="page-loader">
        <div class="ldr-wrap">
            <div class="ldr-dot"></div>
            <div class="ldr-dot"></div>
            <div class="ldr-dot"></div>
            <div class="ldr-shd"></div>
            <div class="ldr-shd"></div>
            <div class="ldr-shd"></div>
        </div>
        <div class="loader-label">Memuat halaman...</div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/@tabler/core@1.4.0/dist/js/tabler.min.js" defer></script>
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
    <div class="page">
        <!-- SIDEBAR (dark vertical navbar) -->
        <?php include __DIR__ . '/partials/sidebar.php'; ?>

        <!-- TOP HEADER NAVBAR (horizontal, desktop only) -->
        <?php include __DIR__ . '/partials/topbar.php'; ?>

        <!-- PAGE WRAPPER -->
        <div class="page-wrapper">
            <!-- Page Header -->
            <div class="page-header d-print-none">
                <div class="container-xl">
                    <div class="row g-2 align-items-center">
                        <div class="col">
                            <div class="page-pretitle"><?= e($pagePretitle ?? APP_NAME) ?></div>
                            <h2 class="page-title"><?= e($pageTitle ?? 'Dashboard') ?></h2>
                        </div>
                        <?php if (!empty($pageActions)): ?>
                            <div class="col-auto ms-auto d-print-none">
                                <div class="btn-list">
                                    <?= $pageActions ?>
                                </div>
                            </div>
                        <?php else: ?>
                            <div class="col-auto ms-auto d-print-none">
                                <div class="btn-list">
                                    <a href="<?= url('/maintenance/create') ?>"
                                        class="btn btn-primary d-none d-sm-inline-block">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                            fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                            stroke-linejoin="round" class="icon">
                                            <path d="M12 5l0 14" />
                                            <path d="M5 12l14 0" />
                                        </svg>
                                        Buat Laporan
                                    </a>
                                    <a href="<?= url('/maintenance/create') ?>" class="btn btn-primary d-sm-none btn-icon"
                                        aria-label="Buat Laporan">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                            fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                            stroke-linejoin="round" class="icon">
                                            <path d="M12 5l0 14" />
                                            <path d="M5 12l14 0" />
                                        </svg>
                                    </a>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <!-- Page Body -->
            <div class="page-body">
                <div class="container-xl">
                    <?php include __DIR__ . '/partials/flash.php'; ?>
                    <?= $content ?? '' ?>
                </div>
            </div>
            <!-- Footer -->
            <footer class="footer footer-transparent d-print-none">
                <div class="container-xl">
                    <div class="row text-center align-items-center flex-row-reverse">
                        <div class="col-lg-auto ms-lg-auto">
                            <ul class="list-inline list-inline-dots mb-0">
                                <li class="list-inline-item"><a href="<?= url('/admin/settings') ?>"
                                        class="link-secondary">Pengaturan</a></li>
                            </ul>
                        </div>
                        <div class="col-12 col-lg-auto mt-3 mt-lg-0">
                            <ul class="list-inline list-inline-dots mb-0">
                                <li class="list-inline-item">Copyright &copy; <?= date('Y') ?> <a href="."
                                        class="link-secondary"><?= e(APP_NAME) ?></a>. All rights reserved.</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </footer>
        </div>
    </div>
    <script src="<?= asset('custom/app.js') ?>"></script>
    <script>
        // Notification badge: hide when dropdown opened, persist via localStorage
        (function () {
            var badge = document.getElementById('notif-badge');
            var bellBtn = document.getElementById('notif-bell-btn');
            if (!badge || !bellBtn) return;

            var currentCount = parseInt(badge.getAttribute('data-count') || '0', 10);
            var seenCount = parseInt(localStorage.getItem('notif_seen_count') || '-1', 10);

            // If user already saw this count, hide the badge immediately
            if (seenCount >= currentCount) {
                badge.style.display = 'none';
            }

            // When dropdown is shown, mark as seen
            bellBtn.addEventListener('show.bs.dropdown', function () {
                badge.style.display = 'none';
                localStorage.setItem('notif_seen_count', currentCount);
            });
        })();
    </script>
    <script>
        (function () {
            var loader = document.getElementById('page-loader');
            var shown = false;
            var minMs = 600;

            function show() {
                if (shown) return;
                shown = true;
                loader.classList.add('active');
            }
            function hide() {
                loader.classList.remove('active');
                shown = false;
            }

            document.addEventListener('click', function (e) {
                var a = e.target.closest('a[href]');
                if (!a) return;
                var href = a.getAttribute('href');
                if (!href || href.charAt(0) === '#' ||
                    href.indexOf('javascript') === 0 ||
                    href.indexOf('mailto') === 0 ||
                    href.indexOf('tel') === 0 ||
                    a.target === '_blank' ||
                    a.hasAttribute('data-bs-toggle') ||
                    a.hasAttribute('data-no-loader')) return;
                try {
                    var u = new URL(href, location.href);
                    if (u.hostname !== location.hostname) return;
                } catch (ex) { return; }
                e.preventDefault();
                show();
                var dest = href;
                var t0 = Date.now();
                setTimeout(function () {
                    var wait = Math.max(0, minMs - (Date.now() - t0));
                    setTimeout(function () { location.href = dest; }, wait);
                }, 30);
            });

            document.addEventListener('submit', function (e) {
                if (!e.target.hasAttribute('data-no-loader')) show();
            });

            window.addEventListener('pageshow', function () { hide(); });
        })();
    </script>
</body>

</html>