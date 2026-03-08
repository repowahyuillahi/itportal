<?php
/**
 * pages/tickets/actions/update_status.php — Handle POST status change
 */
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect(url('/tickets'));
}
verifyCsrf();
requireRole('admin', 'staff');

$ticketId = (int) ($_POST['ticket_id'] ?? 0);
$newStatus = $_POST['status'] ?? '';

$validStatuses = ['open', 'in_progress', 'waiting', 'resolved', 'closed'];
if (!$ticketId || !in_array($newStatus, $validStatuses, true)) {
    flash('error', 'Data tidak valid.');
    redirect(url('/tickets'));
}

$stmt = db()->prepare("SELECT * FROM tickets WHERE id = ?");
$stmt->execute([$ticketId]);
$ticket = $stmt->fetch();

if (!$ticket) {
    flash('error', 'Ticket tidak ditemukan.');
    redirect(url('/tickets'));
}

$oldStatus = $ticket['status'];

try {
    db()->beginTransaction();

    $closedAt = in_array($newStatus, ['resolved', 'closed']) ? 'NOW()' : 'NULL';
    $stmt = db()->prepare("UPDATE tickets SET status = ?, closed_at = $closedAt, updated_at = NOW() WHERE id = ?");
    $stmt->execute([$newStatus, $ticketId]);

    // Revoke public tracking tokens when resolved/closed
    if (in_array($newStatus, ['resolved', 'closed'])) {
        revokePublicTokens($ticketId);
    }

    // System message
    $sysMsg = "Status diubah dari " . ucfirst(str_replace('_', ' ', $oldStatus)) . " ke " . ucfirst(str_replace('_', ' ', $newStatus));
    $stmt = db()->prepare("INSERT INTO ticket_messages (ticket_id, user_id, message, is_internal, created_at) VALUES (?, ?, ?, 0, NOW())");
    $stmt->execute([$ticketId, $_SESSION['user_id'], $sysMsg]);

    // Audit
    logTicketAudit($ticketId, $_SESSION['user_id'], 'status_changed', ['from' => $oldStatus, 'to' => $newStatus]);

    db()->commit();
    flash('success', 'Status ticket berhasil diupdate ke ' . ucfirst(str_replace('_', ' ', $newStatus)) . '.');
} catch (Exception $e) {
    db()->rollBack();
    flash('error', 'Gagal mengupdate status.');
}

redirect(url('/tickets/view?id=' . $ticketId));
