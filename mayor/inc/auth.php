<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Prevent redeclaration if the file is included multiple times
if (!function_exists('current_user')) {
    function current_user() {
        return $_SESSION['user'] ?? null;
    }
}

if (!function_exists('require_login')) {
    function require_login() {
        if (empty($_SESSION['user'])) {
            header('Location: /khiwa/login.php');
            exit;
        }
    }
}

if (!function_exists('require_admin')) {
    function require_admin() {
        if (empty($_SESSION['user']) || ($_SESSION['user']['role'] ?? '') !== 'admin') {
            header('Location: /khiwa/login.php');
            exit;
        }
    }
}

if (!function_exists('require_referee')) {
    function require_referee() {
        if (empty($_SESSION['user']) || !in_array($_SESSION['user']['role'], ['referee', 'admin'])) {
            header('Location: /khiwa/login.php');
            exit;
        }
    }
}

/* ---------- CSRF PROTECTION ---------- */

if (!function_exists('generate_csrf_token')) {
    function generate_csrf_token() {
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }
}

if (!function_exists('validate_csrf_token')) {
    function validate_csrf_token($token) {
        return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
    }
}

/* ---------- INPUT SANITIZATION ---------- */

if (!function_exists('sanitize_input')) {
    function sanitize_input($data) {
        if (is_array($data)) {
            return array_map('sanitize_input', $data);
        }
        return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
    }
}
