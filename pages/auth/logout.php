<?php
/**
 * pages/auth/logout.php — Logout action
 */
logout();
flash('success', 'Anda telah berhasil logout.');
redirect(url('/login'));
