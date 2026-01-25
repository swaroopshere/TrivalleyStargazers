<?php
/**
 * TVS Helper Functions
 *
 * Common utility functions used throughout the website
 */

require_once __DIR__ . '/config.php';

/**
 * Escape HTML for safe output
 */
function e(string $str): string {
    return htmlspecialchars($str, ENT_QUOTES, 'UTF-8');
}

/**
 * Format a date for display
 */
function formatDate(string $date, string $format = 'F j, Y'): string {
    $timestamp = strtotime($date);
    return $timestamp ? date($format, $timestamp) : '';
}

/**
 * Format a time for display
 */
function formatTime(string $time, string $format = 'g:i A'): string {
    $timestamp = strtotime($time);
    return $timestamp ? date($format, $timestamp) : '';
}

/**
 * Format meeting date and time
 */
function formatMeetingDateTime(string $date, string $time): string {
    return formatDate($date, 'l, F j, Y') . ' at ' . formatTime($time);
}

/**
 * Get month name from number
 */
function getMonthName(int $month): string {
    return MONTH_NAMES[$month] ?? '';
}

/**
 * Generate obfuscated email contact (matches existing tvs.js contact function)
 */
function contactEmail(string $user, string $domain, string $name): string {
    $email = $user . '@' . $domain;
    return '<a href="mailto:' . e($email) . '">' . e($name) . '</a>';
}

/**
 * Generate contact link with progressive enhancement
 * Name displays as plain text, JS enhances to mailto link
 */
function contactLink(string $user, string $domain, string $name): string {
    if (empty($user) || empty($domain)) {
        return e($name);
    }
    return '<span class="contact-link" data-user="' . e($user) . '" data-domain="' . e($domain) . '">' . e($name) . '</span>';
}

/**
 * Check if a file exists and is accessible
 */
function fileExistsPublic(string $path): bool {
    $fullPath = ROOT_PATH . '/' . ltrim($path, '/');
    return file_exists($fullPath) && is_readable($fullPath);
}

/**
 * Get file size in human readable format
 */
function formatFileSize(int $bytes): string {
    $units = ['B', 'KB', 'MB', 'GB'];
    $factor = floor((strlen($bytes) - 1) / 3);
    return sprintf("%.1f %s", $bytes / pow(1024, $factor), $units[$factor]);
}

/**
 * Validate and sanitize a date string
 */
function sanitizeDate(string $date): ?string {
    $timestamp = strtotime($date);
    return $timestamp ? date('Y-m-d', $timestamp) : null;
}

/**
 * Validate and sanitize a time string
 */
function sanitizeTime(string $time): ?string {
    $timestamp = strtotime($time);
    return $timestamp ? date('H:i:s', $timestamp) : null;
}

/**
 * Get meeting format label
 */
function getMeetingFormatLabel(string $format): string {
    $labels = [
        'in-person' => 'In-person only',
        'zoom' => 'Zoom only',
        'hybrid' => 'Hybrid (In-person and Zoom)'
    ];
    return $labels[$format] ?? $format;
}

/**
 * Build newsletter file path
 */
function buildNewsletterPath(int $year, int $month, string $fileType = 'pdf'): string {
    $monthStr = str_pad($month, 2, '0', STR_PAD_LEFT);
    $yearStr = substr($year, 2, 2);

    if ($fileType === 'html') {
        return "newsletters/{$year}/{$monthStr}{$yearStr}/index.html";
    }

    return "newsletters/{$year}/tvsnews{$monthStr}{$yearStr}.pdf";
}

/**
 * Redirect with flash message
 */
function redirect(string $url, string $message = '', string $type = 'success'): void {
    if ($message) {
        $_SESSION['flash_message'] = $message;
        $_SESSION['flash_type'] = $type;
    }
    header('Location: ' . $url);
    exit;
}

/**
 * Get and clear flash message
 */
function getFlashMessage(): ?array {
    if (isset($_SESSION['flash_message'])) {
        $message = [
            'message' => $_SESSION['flash_message'],
            'type' => $_SESSION['flash_type'] ?? 'success'
        ];
        unset($_SESSION['flash_message'], $_SESSION['flash_type']);
        return $message;
    }
    return null;
}

/**
 * Display flash message HTML
 */
function showFlashMessage(): void {
    $flash = getFlashMessage();
    if ($flash) {
        $class = $flash['type'] === 'error' ? 'alert-error' : 'alert-success';
        echo '<div class="alert ' . $class . '">' . e($flash['message']) . '</div>';
    }
}

/**
 * Pagination helper
 */
function paginate(int $total, int $perPage, int $currentPage): array {
    $totalPages = (int)ceil($total / $perPage);
    $currentPage = max(1, min($currentPage, $totalPages));
    $offset = ($currentPage - 1) * $perPage;

    return [
        'total' => $total,
        'per_page' => $perPage,
        'current_page' => $currentPage,
        'total_pages' => $totalPages,
        'offset' => $offset,
        'has_prev' => $currentPage > 1,
        'has_next' => $currentPage < $totalPages
    ];
}

/**
 * Validate uploaded file
 */
function validateUpload(array $file): array {
    if ($file['error'] !== UPLOAD_ERR_OK) {
        $errors = [
            UPLOAD_ERR_INI_SIZE => 'File exceeds server limit',
            UPLOAD_ERR_FORM_SIZE => 'File exceeds form limit',
            UPLOAD_ERR_PARTIAL => 'File was only partially uploaded',
            UPLOAD_ERR_NO_FILE => 'No file was uploaded',
            UPLOAD_ERR_NO_TMP_DIR => 'Missing temp directory',
            UPLOAD_ERR_CANT_WRITE => 'Failed to write file',
            UPLOAD_ERR_EXTENSION => 'Upload stopped by extension'
        ];
        return ['success' => false, 'error' => $errors[$file['error']] ?? 'Unknown upload error'];
    }

    if ($file['size'] > MAX_UPLOAD_SIZE) {
        return ['success' => false, 'error' => 'File exceeds maximum size of ' . formatFileSize(MAX_UPLOAD_SIZE)];
    }

    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mimeType = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);

    if (!in_array($mimeType, ALLOWED_UPLOAD_TYPES)) {
        return ['success' => false, 'error' => 'Invalid file type. Only PDF files are allowed.'];
    }

    return ['success' => true, 'mime_type' => $mimeType];
}

/**
 * Safe string truncation
 */
function truncate(string $str, int $length = 100, string $suffix = '...'): string {
    if (strlen($str) <= $length) {
        return $str;
    }
    return substr($str, 0, $length - strlen($suffix)) . $suffix;
}

/**
 * Generate a slug from a string
 */
function slugify(string $str): string {
    $str = strtolower(trim($str));
    $str = preg_replace('/[^a-z0-9]+/', '-', $str);
    return trim($str, '-');
}

/**
 * Check if request is AJAX
 */
function isAjax(): bool {
    return !empty($_SERVER['HTTP_X_REQUESTED_WITH']) &&
           strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
}

/**
 * Return JSON response
 */
function jsonResponse(array $data, int $status = 200): void {
    http_response_code($status);
    header('Content-Type: application/json');
    echo json_encode($data);
    exit;
}

/**
 * Get current page for active nav highlighting
 */
function getCurrentPage(): string {
    $path = $_SERVER['PHP_SELF'] ?? '';
    return basename($path, '.php');
}

/**
 * Check if current page matches
 */
function isCurrentPage(string $page): bool {
    return getCurrentPage() === $page;
}

/**
 * Generate nav link with active class
 */
function navLink(string $url, string $title, string $page, string $id = ''): string {
    $class = isCurrentPage($page) ? ' style="text-decoration: underline;"' : '';
    $idAttr = $id ? ' id="' . e($id) . '"' : '';
    return '<a href="' . e($url) . '" title="' . e($title) . '"' . $idAttr . $class . '>';
}

/**
 * Initialize the database if needed (for first run)
 */
function initializeFirstRun(): void {
    // Check if database exists and has users
    try {
        $users = dbQuery("SELECT COUNT(*) as count FROM users");
        if ($users[0]['count'] == 0) {
            // Create default admin user with temporary password
            $tempPassword = bin2hex(random_bytes(8));
            $hash = password_hash($tempPassword, PASSWORD_DEFAULT);

            dbExecute(
                "UPDATE users SET password_hash = ? WHERE username = 'admin'",
                [$hash]
            );

            // Log the temporary password (in production, this should be emailed)
            error_log("TVS Admin temporary password: " . $tempPassword);
        }
    } catch (Exception $e) {
        // Database not yet initialized, will be created on first access
    }
}
