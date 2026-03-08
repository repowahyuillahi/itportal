<?php
/**
 * pages/tickets/actions/reply.php — Handle POST reply
 */
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect(url('/tickets'));
}
verifyCsrf();

$ticketId = (int) ($_POST['ticket_id'] ?? 0);
$message = trim($_POST['message'] ?? '');
$isInternal = (int) ($_POST['is_internal'] ?? 0);

if (!$ticketId || $message === '') {
    flash('error', 'Pesan wajib diisi.');
    redirect(url('/tickets/view?id=' . $ticketId));
}

// Verify ticket exists
$stmt = db()->prepare("SELECT * FROM tickets WHERE id = ?");
$stmt->execute([$ticketId]);
$ticket = $stmt->fetch();

if (!$ticket) {
    flash('error', 'Ticket tidak ditemukan.');
    redirect(url('/tickets'));
}

// Check access
if (hasRole('user', 'dealer') && $ticket['user_id'] !== (int) $_SESSION['user_id']) {
    flash('error', 'Anda tidak memiliki akses.');
    redirect(url('/tickets'));
}

try {
    db()->beginTransaction();

    // Insert message
    $stmt = db()->prepare("INSERT INTO ticket_messages (ticket_id, user_id, message, is_internal, created_at) VALUES (?, ?, ?, ?, NOW())");
    $stmt->execute([$ticketId, $_SESSION['user_id'], $message, $isInternal]);
    $messageId = db()->lastInsertId();

    // Handle attachment
    if (!empty($_FILES['attachment']) && $_FILES['attachment']['error'] === UPLOAD_ERR_OK) {
        $file = uploadFile($_FILES['attachment'], 'tickets/' . $ticketId);
        if ($file) {
            $stmt = db()->prepare("INSERT INTO ticket_attachments (ticket_id, message_id, file_path, original_name, mime, size, created_at) VALUES (?, ?, ?, ?, ?, ?, NOW())");
            $stmt->execute([$ticketId, $messageId, $file['path'], $file['original'], $file['mime'], $file['size']]);
        }
    }

    // Update ticket updated_at
    db()->prepare("UPDATE tickets SET updated_at = NOW() WHERE id = ?")->execute([$ticketId]);

    // Audit
    logTicketAudit($ticketId, $_SESSION['user_id'], 'reply', ['is_internal' => $isInternal]);

    db()->commit();
    flash('success', 'Balasan berhasil dikirim.');
} catch (Exception $e) {
    db()->rollBack();
    flash('error', 'Gagal mengirim balasan.');
}

redirect(url('/tickets/view?id=' . $ticketId));
