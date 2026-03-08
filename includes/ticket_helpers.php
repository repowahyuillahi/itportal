<?php
/**
 * ticket_helpers.php — Ticket-specific helper functions
 */

/**
 * Generate unique ticket code: TCK-YYYYMMDD-XXXX
 */
function generateTicketCode(): string
{
    $today = date('Ymd');
    $prefix = "TCK-$today-";

    // Get max sequence for today
    $stmt = db()->prepare("SELECT ticket_code FROM tickets WHERE ticket_code LIKE ? ORDER BY id DESC LIMIT 1");
    $stmt->execute([$prefix . '%']);
    $last = $stmt->fetchColumn();

    if ($last) {
        $seq = (int) substr($last, -4) + 1;
    } else {
        $seq = 1;
    }

    return $prefix . str_pad($seq, 4, '0', STR_PAD_LEFT);
}

/**
 * Generate random token (for public tracking or WA)
 */
function generateToken(int $length = 48): string
{
    return bin2hex(random_bytes($length / 2));
}

/**
 * Create ticket tokens (public_track + wa_update) for a ticket
 */
function createTicketTokens(int $ticketId): array
{
    $tokens = [];
    foreach (['public_track', 'wa_update'] as $type) {
        $token = generateToken();
        $stmt = db()->prepare("INSERT INTO ticket_tokens (ticket_id, token, type, created_at) VALUES (?, ?, ?, NOW())");
        $stmt->execute([$ticketId, $token, $type]);
        $tokens[$type] = $token;
    }
    return $tokens;
}

/**
 * Revoke public_track tokens when ticket is resolved/closed
 */
function revokePublicTokens(int $ticketId): void
{
    $stmt = db()->prepare("UPDATE ticket_tokens SET revoked_at = NOW() WHERE ticket_id = ? AND type = 'public_track' AND revoked_at IS NULL");
    $stmt->execute([$ticketId]);
}

/**
 * Get ticket's public tracking URL
 */
function getTrackingUrl(int $ticketId): ?string
{
    $stmt = db()->prepare("SELECT token FROM ticket_tokens WHERE ticket_id = ? AND type = 'public_track' AND revoked_at IS NULL LIMIT 1");
    $stmt->execute([$ticketId]);
    $token = $stmt->fetchColumn();
    return $token ? url('/track?token=' . $token) : null;
}

/**
 * Get ticket's WA update token
 */
function getWaToken(int $ticketId): ?string
{
    $stmt = db()->prepare("SELECT token FROM ticket_tokens WHERE ticket_id = ? AND type = 'wa_update' AND revoked_at IS NULL LIMIT 1");
    $stmt->execute([$ticketId]);
    return $stmt->fetchColumn() ?: null;
}

/**
 * Generate WA link
 */
function generateWaLink(string $ticketCode, string $waToken, string $phone = ''): string
{
    $phone = $phone ?: WA_NUMBER_DEFAULT;
    $msg = "Update ticket $ticketCode: " . url('/wa?token=' . $waToken);
    return 'https://wa.me/' . $phone . '?text=' . urlencode($msg);
}

/**
 * Log ticket audit action
 */
function logTicketAudit(int $ticketId, ?int $actorId, string $action, array $meta = []): void
{
    $stmt = db()->prepare("INSERT INTO ticket_audit (ticket_id, actor_user_id, action, meta_json, created_at) VALUES (?, ?, ?, ?, NOW())");
    $stmt->execute([$ticketId, $actorId, $action, json_encode($meta)]);
}

/**
 * Category options
 */
function ticketCategories(): array
{
    return [
        'Printer' => 'Printer',
        'Network' => 'Network',
        'Hardware' => 'Hardware',
        'Software' => 'Software',
        'CCTV' => 'CCTV',
        'YDT' => 'YDT',
        'Dpack Web' => 'Dpack Web',
        'Dpack Mobile' => 'Dpack Mobile',
        'Mini PC' => 'Mini PC',
        'Monitor' => 'Monitor',
        'Kabel Listrik dan Adaptor' => 'Kabel & Adaptor',
        'Fingerprint Absensi' => 'Fingerprint',
        'Router' => 'Router',
        'Scanner' => 'Scanner',
        'ZAHIR' => 'ZAHIR',
        'Laptop' => 'Laptop',
        'Lainnya' => 'Lainnya',
    ];
}

/**
 * Get staff users for assignment dropdown
 */
function getStaffUsers(): array
{
    $stmt = db()->query("SELECT id, full_name, username FROM users WHERE role IN ('admin','staff') AND is_active = 1 ORDER BY full_name");
    return $stmt->fetchAll();
}
