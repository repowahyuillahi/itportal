<?php
/**
 * pages/tickets/actions/create.php — Handle POST create ticket
 */
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect(url('/tickets/create'));
}
verifyCsrf();

$v = new Validator($_POST);
$v->required('subject', 'Subject')
    ->maxLength('subject', 255, 'Subject')
    ->required('category', 'Kategori')
    ->required('message', 'Pesan')
    ->in('priority', ['low', 'medium', 'high', 'urgent'], 'Prioritas');

if ($v->fails()) {
    storeOldInput();
    flash('error', $v->firstError());
    redirect(url('/tickets/create'));
}

$ticketCode = generateTicketCode();
$userId = $_SESSION['user_id'];
$asset_id = ($_POST['asset_id'] ?? '') !== '' ? (int)$_POST['asset_id'] : null;

try {
    db()->beginTransaction();

    // Insert ticket
    $stmt = db()->prepare("INSERT INTO tickets (ticket_code, user_id, subject, category, priority, status, asset_id, created_at, updated_at)
                           VALUES (?, ?, ?, ?, ?, 'open', ?, NOW(), NOW())");
    $stmt->execute([$ticketCode, $userId, $v->get('subject'), $v->get('category'), $v->get('priority', 'medium'), $asset_id]);
    $ticketId = db()->lastInsertId();

    // Insert initial message
    $stmt = db()->prepare("INSERT INTO ticket_messages (ticket_id, user_id, message, created_at) VALUES (?, ?, ?, NOW())");
    $stmt->execute([$ticketId, $userId, $v->get('message')]);
    $messageId = db()->lastInsertId();

    // Handle attachment
    if (!empty($_FILES['attachment']) && $_FILES['attachment']['error'] === UPLOAD_ERR_OK) {
        $file = uploadFile($_FILES['attachment'], 'tickets/' . $ticketId);
        if ($file) {
            $stmt = db()->prepare("INSERT INTO ticket_attachments (ticket_id, message_id, file_path, original_name, mime, size, created_at) VALUES (?, ?, ?, ?, ?, ?, NOW())");
            $stmt->execute([$ticketId, $messageId, $file['path'], $file['original'], $file['mime'], $file['size']]);
        }
    }

    // Create tokens (public_track + wa_update)
    createTicketTokens($ticketId);

    // Audit log
    logTicketAudit($ticketId, $userId, 'created', ['ticket_code' => $ticketCode]);

    db()->commit();

    flash('success', "Ticket $ticketCode berhasil dibuat!");
    redirect(url('/tickets/view?id=' . $ticketId));

} catch (Exception $e) {
    db()->rollBack();
    flash('error', 'Gagal membuat ticket: ' . (APP_ENV === 'local' ? $e->getMessage() : 'Terjadi kesalahan.'));
    storeOldInput();
    redirect(url('/tickets/create'));
}
