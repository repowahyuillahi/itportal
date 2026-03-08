<?php
/**
 * layouts/auth.php — Auth layout matching Tabler sign-in.html exactly
 */
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title><?= e($pageTitle ?? 'Login') ?> — <?= e(APP_NAME) ?></title>
    <!-- Tabler Core v1.4.0 -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/core@1.4.0/dist/css/tabler.min.css">
    <!-- Tabler Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@3.31.0/dist/tabler-icons.min.css">
    <!-- Custom -->
    <link rel="stylesheet" href="<?= asset('custom/app.css') ?>">
</head>

<body class="d-flex flex-column">
    <script src="https://cdn.jsdelivr.net/npm/@tabler/core@1.4.0/dist/js/tabler.min.js" defer></script>
    <div class="page page-center">
        <div class="container container-tight py-4">
            <!-- Brand Logo -->
            <div class="text-center mb-4">
                <a href="." class="navbar-brand navbar-brand-autodark">
                    <svg xmlns="http://www.w3.org/2000/svg" width="140" height="36" viewBox="0 0 232 68" fill="none">
                        <rect width="68" height="68" rx="16" fill="#0054a6" />
                        <path
                            d="M20.672 18.32L47.328 18.32C48.854 18.32 50.088 19.572 50.088 21.12V31.84H40.248L34.8 25.68L29.352 31.84H17.912V21.12C17.912 19.572 19.146 18.32 20.672 18.32Z"
                            fill="white" />
                        <path
                            d="M17.912 36.16H29.352L34.8 42.32L40.248 36.16H50.088V46.88C50.088 48.428 48.854 49.68 47.328 49.68H20.672C19.146 49.68 17.912 48.428 17.912 46.88V36.16Z"
                            fill="white" fill-opacity="0.5" />
                        <text x="80" y="46" font-family="Inter, sans-serif" font-weight="700" font-size="32"
                            fill="#1e293b"><?= e(APP_NAME) ?></text>
                    </svg>
                </a>
            </div>
            <!-- Flash Messages -->
            <?php include __DIR__ . '/partials/flash.php'; ?>
            <!-- Content (login form card) -->
            <?= $content ?? '' ?>
        </div>
    </div>
</body>

</html>