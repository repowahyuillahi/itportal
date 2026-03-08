<?php
/**
 * pages/tickets/actions/assign.php — Handle POST assign staff
 */
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect(url('/tickets'));
}
verifyCsrf();
requireRole('admin', 'staff');

$ticketId = (int) ($_POST['ticket_id'] ?? 0);
$assignedTo = ($_POST['assigned_to'] ?? '') !== '' ? (int) $_POST['assigned_to'] : null;

if (!$ticketId) {
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

try {
    db()->beginTransaction();

    $stmt = db()->prepare("UPDATE tickets SET assigned_to = ?, updated_at = NOW() WHERE id = ?");
    $stmt->execute([$assignedTo, $ticketId]);

    // Get assigned staff name
    $staffName = 'Tidak ada';
    if ($assignedTo) {
        $s = db()->prepare("SELECT full_name FROM users WHERE id = ?");
        $s->execute([$assignedTo]);
        $staffName = $s->fetchColumn() ?: 'Unknown';
    }

    // System message
    $sysMsg = "Ticket di-assign ke $staffName";
    $stmt = db()->prepare("INSERT INTO ticket_messages (ticket_id, user_id, message, is_internal, created_at) VALUES (?, ?, ?, 0, NOW())");
    $stmt->execute([$ticketId, $_SESSION['user_id'], $sysMsg]);

    // Audit
    logTicketAudit($ticketId, $_SESSION['user_id'], 'assigned', ['assigned_to' => $assignedTo, 'staff_name' => $staffName]);

    db()->commit();
    flash('success', "Ticket berhasil di-assign ke $staffName.");
} catch (Exception $e) {
    db()->rollBack();
    flash('error', 'Gagal meng-assign ticket.');
}

redirect(url('/tickets/view?id=' . $ticketId));
