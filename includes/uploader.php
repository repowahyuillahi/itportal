<?php
/**
 * uploader.php — Safe file upload handler
 */

/**
 * Allowed file extensions
 */
function allowedExtensions(): array
{
    return ['jpg', 'jpeg', 'png', 'gif', 'webp', 'pdf', 'doc', 'docx', 'xls', 'xlsx', 'txt', 'zip', 'rar'];
}

/**
 * Handle file upload safely
 * Returns array ['path' => relative path, 'original' => original name, 'mime' => mime, 'size' => size]
 * Returns null on failure
 */
function uploadFile(array $file, string $subDir = ''): ?array
{
    if ($file['error'] !== UPLOAD_ERR_OK) {
        return null;
    }

    // Check file size
    $maxBytes = UPLOAD_MAX_MB * 1024 * 1024;
    if ($file['size'] > $maxBytes) {
        flash('error', 'File terlalu besar. Maksimal ' . UPLOAD_MAX_MB . 'MB.');
        return null;
    }

    // Check extension
    $originalName = $file['name'];
    $ext = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
    if (!in_array($ext, allowedExtensions(), true)) {
        flash('error', 'Tipe file tidak diizinkan: .' . $ext);
        return null;
    }

    // Generate safe filename
    $newName = date('Ymd_His') . '_' . bin2hex(random_bytes(8)) . '.' . $ext;

    // Create upload directory
    $uploadDir = UPLOAD_DIR;
    if ($subDir) {
        $uploadDir .= '/' . trim($subDir, '/');
    }
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }

    $destPath = $uploadDir . '/' . $newName;
    if (!move_uploaded_file($file['tmp_name'], $destPath)) {
        flash('error', 'Gagal menyimpan file.');
        return null;
    }

    // Relative path from storage/uploads
    $relativePath = ($subDir ? trim($subDir, '/') . '/' : '') . $newName;

    return [
        'path' => $relativePath,
        'original' => $originalName,
        'mime' => $file['type'] ?: mime_content_type($destPath),
        'size' => $file['size'],
    ];
}
