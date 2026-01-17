<?php
// inc/security.php
if (session_status() === PHP_SESSION_NONE) session_start();

/**
 * CSRF token helpers
 */
function csrf_token() {
    if (empty($_SESSION['_csrf_token'])) {
        $_SESSION['_csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['_csrf_token'];
}
function csrf_tag() {
    return '<input type="hidden" name="_csrf_token" value="'.htmlspecialchars(csrf_token()).'">';
}
function csrf_check($token) {
    if (empty($_SESSION['_csrf_token']) || !$token) return false;
    return hash_equals($_SESSION['_csrf_token'], $token);
}

/**
 * Simple input sanitizers
 */
function e($s) { return htmlspecialchars($s, ENT_QUOTES|ENT_SUBSTITUTE, 'UTF-8'); }
function clean_text($s) { return trim(filter_var($s, FILTER_SANITIZE_FULL_SPECIAL_CHARS)); }
function clean_int($s) { return filter_var($s, FILTER_VALIDATE_INT, FILTER_NULL_ON_FAILURE); }
function clean_datetime_local($s) {
    // Expect `YYYY-MM-DDTHH:MM` or `YYYY-MM-DD HH:MM:SS`
    $s = str_replace('T', ' ', trim($s));
    $d = date_create($s);
    return $d ? $d->format('Y-m-d H:i:s') : null;
}

/**
 * Role check helpers (use require_role for pages)
 */
function require_role($role) {
    require_once __DIR__ . '/auth.php';
    $u = current_user();
    if (!$u || $u['role'] !== $role) {
        http_response_code(403);
        echo 'Forbidden';
        exit;
    }
}
function require_any_role(array $roles) {
    require_once __DIR__ . '/auth.php';
    $u = current_user();
    if (!$u || !in_array($u['role'], $roles, true)) {
        http_response_code(403);
        echo 'Forbidden';
        exit;
    }
}

/**
 * Example: check CSRF on POST requests
 */
function require_valid_csrf() {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $token = $_POST['_csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? null;
        if (!csrf_check($token)) {
            http_response_code(400);
            die('Invalid CSRF token');
        }
    }
}
