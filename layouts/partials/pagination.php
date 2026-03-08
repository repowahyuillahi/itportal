<?php
/**
 * partials/pagination.php — Reusable pagination component
 * Variables: $currentPage, $totalPages, $baseUrl (with ? or &)
 */
if (!isset($totalPages) || $totalPages <= 1)
    return;
?>
<ul class="pagination justify-content-center mt-4">
    <li class="page-item <?= $currentPage <= 1 ? 'disabled' : '' ?>">
        <a class="page-link" href="<?= $baseUrl ?>page=<?= $currentPage - 1 ?>" tabindex="-1">
            <i class="ti ti-chevron-left"></i> Prev
        </a>
    </li>
    <?php
    $start = max(1, $currentPage - 2);
    $end = min($totalPages, $currentPage + 2);
    if ($start > 1): ?>
        <li class="page-item"><a class="page-link" href="<?= $baseUrl ?>page=1">1</a></li>
        <?php if ($start > 2): ?>
            <li class="page-item disabled"><span class="page-link">...</span></li>
        <?php endif; ?>
    <?php endif; ?>
    <?php for ($i = $start; $i <= $end; $i++): ?>
        <li class="page-item <?= $i === $currentPage ? 'active' : '' ?>">
            <a class="page-link" href="<?= $baseUrl ?>page=<?= $i ?>">
                <?= $i ?>
            </a>
        </li>
    <?php endfor; ?>
    <?php if ($end < $totalPages): ?>
        <?php if ($end < $totalPages - 1): ?>
            <li class="page-item disabled"><span class="page-link">...</span></li>
        <?php endif; ?>
        <li class="page-item"><a class="page-link" href="<?= $baseUrl ?>page=<?= $totalPages ?>">
                <?= $totalPages ?>
            </a></li>
    <?php endif; ?>
    <li class="page-item <?= $currentPage >= $totalPages ? 'disabled' : '' ?>">
        <a class="page-link" href="<?= $baseUrl ?>page=<?= $currentPage + 1 ?>">
            Next <i class="ti ti-chevron-right"></i>
        </a>
    </li>
</ul>