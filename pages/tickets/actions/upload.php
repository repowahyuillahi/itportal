<?php
/**
 * pages/tickets/actions/upload.php — Handle file upload to existing ticket
 */
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect(url('/tickets'));
}
verifyCsrf();

$ticketId = (int) ($_POST['ticket_id'] ?? 0);
if (!$ticketId || empty($_FILES['attachment']) || $_FILES['attachment']['error'] !== UPLOAD_ERR_OK) {
    flash('error', 'File upload gagal.');
    redirect(url('/tickets/view?id=' . $ticketId));
}

$file = uploadFile($_FILES['attachment'], 'tickets/' . $ticketId);
if ($file) {
    $stmt = db()->prepare("INSERT INTO ticket_attachments (ticket_id, message_id, file_path, original_name, mime, size, created_at) VALUES (?, NULL, ?, ?, ?, ?, NOW())");
    $stmt->execute([$ticketId, $file['path'], $file['original'], $file['mime'], $file['size']]);
    flash('success', 'File berhasil diupload.');
}

redirect(url('/tickets/view?id=' . $ticketId));
