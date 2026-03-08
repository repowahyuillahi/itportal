<?php
/**
 * layouts/partials/sidebar.php — Always-expanded sidebar, white text
 */
$currentPath = parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH);
$currentPath = str_replace('/itportal', '', $currentPath);
?>
<aside class="navbar navbar-vertical navbar-expand-lg" data-bs-theme="dark">
    <div class="container-fluid">
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#sidebar-menu"
            aria-controls="sidebar-menu" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <h1 class="navbar-brand navbar-brand-autodark">
            <a href="<?= url('/dashboard') ?>">
                <svg xmlns="http://www.w3.org/2000/svg" width="160" height="36" viewBox="0 0 300 76" fill="none">
                    <rect width="68" height="68" rx="16" fill="#0054a6" />
                    <path
                        d="M20.672 18.32L47.328 18.32C48.854 18.32 50.088 19.572 50.088 21.12V31.84H40.248L34.8 25.68L29.352 31.84H17.912V21.12C17.912 19.572 19.146 18.32 20.672 18.32Z"
                        fill="white" />
                    <path
                        d="M17.912 36.16H29.352L34.8 42.32L40.248 36.16H50.088V46.88C50.088 48.428 48.854 49.68 47.328 49.68H20.672C19.146 49.68 17.912 48.428 17.912 46.88V36.16Z"
                        fill="white" fill-opacity="0.5" />
                    <text x="80" y="30" font-family="Inter, sans-serif" font-weight="700" font-size="22"
                        fill="white"><?= e(APP_NAME) ?></text>
                    <text x="80" y="56" font-family="Inter, sans-serif" font-weight="400" font-size="16"
                        fill="rgba(255,255,255,0.5)">Helpdesk System</text>
                </svg>
            </a>
        </h1>
        <div class="navbar-nav flex-row d-lg-none">
            <div class="nav-item dropdown">
                <a href="#" class="nav-link d-flex lh-1 text-reset p-0" data-bs-toggle="dropdown">
                    <span class="avatar avatar-sm"
                        style="background-image: url(https://ui-avatars.com/api/?name=<?= urlencode($_SESSION['full_name'] ?? 'U') ?>&size=32&background=0054a6&color=fff)"></span>
                </a>
                <div class="dropdown-menu dropdown-menu-end">
                    <a class="dropdown-item" href="<?= url('/profile') ?>">Profil Saya</a>
                    <div class="dropdown-divider"></div>
                    <a class="dropdown-item" href="<?= url('/logout') ?>"
                        onclick="return confirm('Yakin mau logout?')">Logout</a>
                </div>
            </div>
        </div>
        <div class="collapse navbar-collapse" id="sidebar-menu">
            <ul class="navbar-nav pt-lg-3">
                <!-- Home -->
                <li class="nav-item">
                    <a class="nav-link <?= $currentPath === '/dashboard' || $currentPath === '/' ? 'active' : '' ?>"
                        href="<?= url('/dashboard') ?>">
                        <span class="nav-link-icon"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                stroke-linecap="round" stroke-linejoin="round" class="icon">
                                <path d="M5 12l-2 0l9 -9l9 9l-2 0" />
                                <path d="M5 12v7a2 2 0 0 0 2 2h10a2 2 0 0 0 2 -2v-7" />
                                <path d="M9 21v-6a2 2 0 0 1 2 -2h2a2 2 0 0 1 2 2v6" />
                            </svg></span>
                        <span class="nav-link-title">Home</span>
                    </a>
                </li>
                <!-- Maintenance - always expanded -->
                <li class="nav-item <?= str_starts_with($currentPath, '/maintenance') ? 'active' : '' ?>">
                    <a class="nav-link" href="<?= url('/maintenance') ?>">
                        <span class="nav-link-icon"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                stroke-linecap="round" stroke-linejoin="round" class="icon">
                                <path
                                    d="M7 10h3v-3l-3.5 -3.5a6 6 0 0 1 8 8l6 6a2 2 0 0 1 -3 3l-6 -6a6 6 0 0 1 -8 -8l3.5 3.5" />
                            </svg></span>
                        <span class="nav-link-title">Semua Laporan</span>
                    </a>
                </li>
                <li class="nav-item <?= $currentPath === '/maintenance/create' ? 'active' : '' ?>">
                    <a class="nav-link" href="<?= url('/maintenance/create') ?>">
                        <span class="nav-link-icon"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                stroke-linecap="round" stroke-linejoin="round" class="icon">
                                <path d="M12 5l0 14" />
                                <path d="M5 12l14 0" />
                            </svg></span>
                        <span class="nav-link-title">Buat Laporan</span>
                    </a>
                </li>
                <?php if (hasRole('admin', 'staff')): ?>
                    <li class="nav-item nav-item-separator">
                        <hr class="my-1" style="border-color: rgba(255,255,255,0.15)">
                    </li>
                    <!-- Data Master - always expanded -->
                    <li class="nav-item"><span class="nav-link nav-link-header"
                            style="color: rgba(255,255,255,0.4); font-size: 0.625rem; letter-spacing: .04em; text-transform: uppercase; padding-bottom: 0;">Data
                            Master</span></li>
                    <li class="nav-item <?= $currentPath === '/dealers' ? 'active' : '' ?>">
                        <a class="nav-link" href="<?= url('/dealers') ?>">
                            <span class="nav-link-icon"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                    viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                    stroke-linecap="round" stroke-linejoin="round" class="icon">
                                    <path d="M3 21l18 0" />
                                    <path d="M9 8l1 0" />
                                    <path d="M9 12l1 0" />
                                    <path d="M9 16l1 0" />
                                    <path d="M14 8l1 0" />
                                    <path d="M14 12l1 0" />
                                    <path d="M14 16l1 0" />
                                    <path d="M5 21v-16a2 2 0 0 1 2 -2h10a2 2 0 0 1 2 2v16" />
                                </svg></span>
                            <span class="nav-link-title">Dealer</span>
                        </a>
                    </li>
                    <li class="nav-item <?= $currentPath === '/divisi' ? 'active' : '' ?>">
                        <a class="nav-link" href="<?= url('/divisi') ?>">
                            <span class="nav-link-icon"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                    viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                    stroke-linecap="round" stroke-linejoin="round" class="icon">
                                    <path d="M12 3l8 4.5l0 9l-8 4.5l-8 -4.5l0 -9l8 -4.5" />
                                    <path d="M12 12l8 -4.5" />
                                    <path d="M12 12l0 9" />
                                    <path d="M12 12l-8 -4.5" />
                                </svg></span>
                            <span class="nav-link-title">Divisi</span>
                        </a>
                    </li>
                    <li class="nav-item <?= $currentPath === '/sertifikat' ? 'active' : '' ?>">
                        <a class="nav-link" href="<?= url('/sertifikat') ?>">
                            <span class="nav-link-icon"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                    viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                    stroke-linecap="round" stroke-linejoin="round" class="icon">
                                    <path d="M15 15m-3 0a3 3 0 1 0 6 0a3 3 0 1 0 -6 0" />
                                    <path d="M13 17.5v4.5l2 -1.5l2 1.5v-4.5" />
                                    <path
                                        d="M10 19h-5a2 2 0 0 1 -2 -2v-10c0 -1.1 .9 -2 2 -2h14a2 2 0 0 1 2 2v10a2 2 0 0 1 -1 1.73" />
                                    <path d="M6 9l12 0" />
                                    <path d="M6 12l3 0" />
                                    <path d="M6 15l2 0" />
                                </svg></span>
                            <span class="nav-link-title">Sertifikat</span>
                        </a>
                    </li>
                    <li class="nav-item <?= $currentPath === '/zahir' ? 'active' : '' ?>">
                        <a class="nav-link" href="<?= url('/zahir') ?>">
                            <span class="nav-link-icon"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                    viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                    stroke-linecap="round" stroke-linejoin="round" class="icon">
                                    <path
                                        d="M15 21h-9a3 3 0 0 1 -3 -3v-1h10v2a2 2 0 0 0 4 0v-14a2 2 0 1 1 2 2h-2m2 -4h-11a3 3 0 0 0 -3 3v11" />
                                    <path d="M9 7l4 0" />
                                    <path d="M9 11l4 0" />
                                </svg></span>
                            <span class="nav-link-title">Zahir License</span>
                        </a>
                    </li>
                    <li class="nav-item nav-item-separator">
                        <hr class="my-1" style="border-color: rgba(255,255,255,0.15)">
                    </li>
                    <!-- Admin -->
                    <li class="nav-item"><span class="nav-link nav-link-header"
                            style="color: rgba(255,255,255,0.4); font-size: 0.625rem; letter-spacing: .04em; text-transform: uppercase; padding-bottom: 0;">Admin</span>
                    </li>
                    <li class="nav-item <?= $currentPath === '/admin/users' ? 'active' : '' ?>">
                        <a class="nav-link" href="<?= url('/admin/users') ?>">
                            <span class="nav-link-icon"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                    viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                    stroke-linecap="round" stroke-linejoin="round" class="icon">
                                    <path d="M9 7m-4 0a4 4 0 1 0 8 0a4 4 0 1 0 -8 0" />
                                    <path d="M3 21v-2a4 4 0 0 1 4 -4h4a4 4 0 0 1 4 4v2" />
                                    <path d="M16 3.13a4 4 0 0 1 0 7.75" />
                                    <path d="M21 21v-2a4 4 0 0 0 -3 -3.85" />
                                </svg></span>
                            <span class="nav-link-title">Users</span>
                        </a>
                    </li>
                    <li class="nav-item <?= $currentPath === '/admin/settings' ? 'active' : '' ?>">
                        <a class="nav-link" href="<?= url('/admin/settings') ?>">
                            <span class="nav-link-icon"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                    viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                    stroke-linecap="round" stroke-linejoin="round" class="icon">
                                    <path
                                        d="M10.325 4.317c.426 -1.756 2.924 -1.756 3.35 0a1.724 1.724 0 0 0 2.573 1.066c1.543 -.94 3.31 .826 2.37 2.37a1.724 1.724 0 0 0 1.066 2.573c1.756 .426 1.756 2.924 0 3.35a1.724 1.724 0 0 0 -1.066 2.573c.94 1.543 -.826 3.31 -2.37 2.37a1.724 1.724 0 0 0 -2.573 1.066c-.426 1.756 -2.924 1.756 -3.35 0a1.724 1.724 0 0 0 -2.573 -1.066c-1.543 .94 -3.31 -.826 -2.37 -2.37a1.724 1.724 0 0 0 -1.066 -2.573c-1.756 -.426 -1.756 -2.924 0 -3.35a1.724 1.724 0 0 0 1.066 -2.573c-.94 -1.543 .826 -3.31 2.37 -2.37c1 .608 2.296 .07 2.572 -1.065z" />
                                    <path d="M9 12a3 3 0 1 0 6 0a3 3 0 0 0 -6 0" />
                                </svg></span>
                            <span class="nav-link-title">Pengaturan</span>
                        </a>
                    </li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</aside>