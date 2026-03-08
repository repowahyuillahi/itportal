<?php
/**
 * NotificationService.php — Stub for future WhatsApp (Baileys) integration
 *
 * This class is a placeholder. Implement sendWaMessage() when Baileys is ready.
 */

class NotificationService
{
    /**
     * Send WhatsApp notification (STUB)
     */
    public static function sendWaMessage(string $phone, string $message): bool
    {
        // TODO: Implement Baileys integration
        // For now, just log it
        $logFile = BASE_PATH . '/storage/logs/wa_notifications.log';
        $logLine = date('Y-m-d H:i:s') . " | To: $phone | Msg: $message\n";
        file_put_contents($logFile, $logLine, FILE_APPEND);
        return true;
    }

    /**
     * Send ticket update notification via WA (STUB)
     */
    public static function notifyTicketUpdate(string $ticketCode, string $phone, string $trackUrl): bool
    {
        $message = "Update ticket $ticketCode: $trackUrl";
        return self::sendWaMessage($phone, $message);
    }
}
