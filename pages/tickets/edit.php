<?php
/**
 * pages/tickets/edit.php — Edit ticket (Tabler card style)
 */
requireRole('admin', 'staff');

$ticketId = (int) ($_GET['id'] ?? 0);
if (!$ticketId) {
    redirect(url('/tickets'));
}

$stmt = db()->prepare("SELECT * FROM tickets WHERE id = ?");
$stmt->execute([$ticketId]);
$ticket = $stmt->fetch();
if (!$ticket) {
    flash('error', 'Ticket tidak ditemukan.');
    redirect(url('/tickets'));
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();
    $v = new Validator($_POST);
    $v->required('subject', 'Subject')->maxLength('subject', 255, 'Subject')
        ->required('category', 'Kategori')
        ->in('priority', ['low', 'medium', 'high', 'urgent'], 'Prioritas');

    if ($v->fails()) {
        flash('error', $v->firstError());
    } else {
        $stmt = db()->prepare("UPDATE tickets SET subject = ?, category = ?, priority = ?, updated_at = NOW() WHERE id = ?");
        $stmt->execute([$v->get('subject'), $v->get('category'), $v->get('priority'), $ticketId]);
        logTicketAudit($ticketId, $_SESSION['user_id'], 'edited', ['subject' => $v->get('subject')]);
        flash('success', 'Ticket berhasil diupdate.');
        redirect(url('/tickets/view?id=' . $ticketId));
    }
}

$pageTitle = 'Edit Ticket ' . $ticket['ticket_code'];
$pagePretitle = 'Tickets';

$pageActions = '<a href="' . url('/tickets/view?id=' . $ticketId) . '" class="btn btn-outline-secondary d-none d-sm-inline-block"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon"><path d="M9 14l-4 -4l4 -4"/><path d="M5 10h11a4 4 0 1 1 0 8h-1"/></svg> Kembali</a>';

ob_start();
?>
<div class="row row-cards">
    <div class="col-lg-8">
        <form method="POST">
            <?= csrfField() ?>
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Edit Detail Ticket</h3>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label required">Subject</label>
                        <input type="text" name="subject" class="form-control" value="<?= e($ticket['subject']) ?>"
                            required maxlength="255">
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label required">Kategori</label>
                            <select name="category" class="form-select" required>
                                <?php foreach (ticketCategories() as $val => $label): ?>
                                    <option value="<?= e($val) ?>" <?= $ticket['category'] === $val ? 'selected' : '' ?>>
                                        <?= e($label) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Prioritas</label>
                            <select name="priority" class="form-select">
                                <?php foreach (['low', 'medium', 'high', 'urgent'] as $p): ?>
                                    <option value="<?= $p ?>" <?= $ticket['priority'] === $p ? 'selected' : '' ?>>
                                        <?= ucfirst($p) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="card-footer text-end">
                    <a href="<?= url('/tickets/view?id=' . $ticketId) ?>" class="btn btn-link">Batal</a>
                    <button type="submit" class="btn btn-primary ms-auto">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                            stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                            class="icon">
                            <path d="M5 12l5 5l10 -10" />
                        </svg>
                        Simpan
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
<?php
$content = ob_get_clean();
include BASE_PATH . '/layouts/main.php';
