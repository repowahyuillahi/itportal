<?php
/**
 * partials/flash.php — Flash messages with auto-dismiss after 10 seconds
 */
$_flashMessages = getFlash();
foreach ($_flashMessages as $msg):
    $alertClass = match ($msg['type']) {
        'success' => 'alert-success',
        'error' => 'alert-danger',
        'warning' => 'alert-warning',
        'info' => 'alert-info',
        default => 'alert-info',
    };
    $icon = match ($msg['type']) {
        'success' => 'ti-check',
        'error' => 'ti-alert-circle',
        'warning' => 'ti-alert-triangle',
        default => 'ti-info-circle',
    };
    ?>
    <div class="alert <?= $alertClass ?> alert-dismissible mb-3 auto-dismiss" role="alert">
        <div class="d-flex">
            <div><i class="ti <?= $icon ?> me-2"></i></div>
            <div><?= e($msg['message']) ?></div>
        </div>
        <a class="btn-close" data-bs-dismiss="alert" aria-label="close"></a>
    </div>
<?php endforeach; ?>
<?php if (!empty($_flashMessages)): ?>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            document.querySelectorAll('.auto-dismiss').forEach(function (alert) {
                setTimeout(function () {
                    alert.classList.add('alert-fade-out');
                    setTimeout(function () { alert.remove(); }, 500);
                }, 10000);
            });
        });
    </script>
<?php endif; ?>