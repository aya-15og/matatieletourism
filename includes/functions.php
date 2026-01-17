<?php
// ============================
// Functions for Matatiele CMS
// ============================

// ------------------------
// Upload Image Helper
// ------------------------
function upload_image($field_name, $target_dir = 'images/') {
    if (isset($_FILES[$field_name]) && $_FILES[$field_name]['error'] === UPLOAD_ERR_OK) {
        $fileTmpPath = $_FILES[$field_name]['tmp_name'];
        // Sanitize original file name
        $fileName = preg_replace('/[^a-zA-Z0-9_\.-]/', '_', $_FILES[$field_name]['name']);
        $fileDest = rtrim($target_dir, '/') . '/' . $fileName;

        if (!is_dir($target_dir)) {
            mkdir($target_dir, 0755, true);
        }

        if (move_uploaded_file($fileTmpPath, $fileDest)) {
            return $fileDest;
        }
    }
    return null;
}


// ------------------------
// TinyMCE Helper (Self-hosted, no API key)
// ------------------------
function add_tinymce() {
    echo <<<HTML
<script src="/js/tinymce/tinymce.min.js"></script>
<script>
tinymce.init({
  selector: 'textarea.tinymce',
  height: 300,
  menubar: false,
  plugins: 'lists link image preview',
  toolbar: 'undo redo | bold italic | bullist numlist | link image | preview',
});
</script>
HTML;
}

// ------------------------
// Send Email Helper (Fallback)
// ------------------------
function send_mail($to, $subject, $body, $from = 'no-reply@matatiele.co.za') {
    // Simple PHP mail fallback
    $headers = "From: $from\r\n";
    $headers .= "Reply-To: $from\r\n";
    $headers .= "X-Mailer: PHP/" . phpversion();

    if (function_exists('mail')) {
        return mail($to, $subject, $body, $headers);
    }

    // Log email if mail() is disabled
    error_log("Mail to $to failed. Subject: $subject. Body: $body");
    return false;
}

// ------------------------
// Sanitize Input Helper
// ------------------------
function clean_input($data) {
    return htmlspecialchars(strip_tags(trim($data)), ENT_QUOTES, 'UTF-8');
}

// ------------------------
// Redirect Helper
// ------------------------
function redirect($url) {
    header("Location: $url");
    exit;
}
