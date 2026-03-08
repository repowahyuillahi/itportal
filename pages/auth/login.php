<?php
/**
 * pages/auth/login.php — Sign-in page matching Tabler sign-in.html exactly
 */
$pageTitle = 'Sign in';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($username === '' || $password === '') {
        flash('error', 'Username dan password wajib diisi.');
    } elseif (attemptLogin($username, $password)) {
        flash('success', 'Selamat datang, ' . e($_SESSION['full_name']) . '!');
        redirect(url('/dashboard'));
    } else {
        flash('error', 'Username atau password salah.');
    }
}

ob_start();
?>
<div class="card card-md">
    <div class="card-body">
        <h2 class="h2 text-center mb-4">Login to your account</h2>
        <form method="POST" autocomplete="off" novalidate>
            <?= csrfField() ?>
            <div class="mb-3">
                <label class="form-label">Username</label>
                <input type="text" name="username" class="form-control" placeholder="Username" autocomplete="off"
                    value="<?= e(old('username')) ?>" autofocus>
            </div>
            <div class="mb-2">
                <label class="form-label">
                    Password
                </label>
                <div class="input-group input-group-flat">
                    <input type="password" name="password" class="form-control" placeholder="Your password"
                        autocomplete="off">
                    <span class="input-group-text">
                        <a href="#" class="link-secondary" title="Show password" data-bs-toggle="tooltip" onclick="
                            var input = this.closest('.input-group').querySelector('input');
                            if(input.type === 'password'){ input.type = 'text'; } else { input.type = 'password'; }
                            return false;
                        ">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                stroke-linejoin="round" class="icon">
                                <path d="M10 12a2 2 0 1 0 4 0a2 2 0 0 0 -4 0" />
                                <path
                                    d="M21 12c-2.4 4 -5.4 6 -9 6c-3.6 0 -6.6 -2 -9 -6c2.4 -4 5.4 -6 9 -6c3.6 0 6.6 2 9 6" />
                            </svg>
                        </a>
                    </span>
                </div>
            </div>
            <div class="mb-2">
                <label class="form-check">
                    <input type="checkbox" class="form-check-input" />
                    <span class="form-check-label">Remember me on this device</span>
                </label>
            </div>
            <div class="form-footer">
                <button type="submit" class="btn btn-primary w-100">Sign in</button>
            </div>
        </form>
    </div>
</div>
<?php
$content = ob_get_clean();
include BASE_PATH . '/layouts/auth.php';
