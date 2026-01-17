<?php
$base_gallery_dir = 'images/gallery/';
$exterior_dir = $base_gallery_dir . 'exterior/';

echo "<h1>Debugging Exterior Images</h1>";
echo "<h2>1. Directory Path Check</h2>";
echo "<p>Expected Directory Path: <strong>" . $exterior_dir . "</strong></p>";

// Check if the directory exists
if (is_dir($exterior_dir)) {
    echo "<p style='color: green;'><strong>SUCCESS:</strong> The directory exists.</p>";
} else {
    echo "<p style='color: red;'><strong>ERROR:</strong> The directory <strong>" . $exterior_dir . "</strong> does not exist or is not readable by the server. Please check the exact spelling and case.</p>";
}

echo "<h2>2. Image Scan Check</h2>";
// Scan for images
$image_files = glob($exterior_dir . '*.{jpg,jpeg,png,gif}', GLOB_BRACE);

if (!empty($image_files)) {
    echo "<p style='color: green;'><strong>SUCCESS:</strong> Found " . count($image_files) . " images.</p>";
    echo "<ul>";
    foreach ($image_files as $file) {
        echo "<li>" . $file . "</li>";
    }
    echo "</ul>";
} else {
    echo "<p style='color: red;'><strong>ERROR:</strong> No images found in the directory. This could be due to incorrect file extensions, or a permissions issue.</p>";
    
    // Check if the directory is empty
    $all_files = glob($exterior_dir . '*');
    if (!empty($all_files)) {
        echo "<p style='color: orange;'><strong>WARNING:</strong> The folder is not empty, but no images were found with the extensions {jpg, jpeg, png, gif}. Found " . count($all_files) . " total items.</p>";
    }
}

echo "<h2>3. Permissions Check</h2>";
if (is_readable($exterior_dir)) {
    echo "<p style='color: green;'><strong>SUCCESS:</strong> The directory is readable.</p>";
} else {
    echo "<p style='color: red;'><strong>ERROR:</strong> The directory <strong>" . $exterior_dir . "</strong> is not readable. You may need to adjust folder permissions (e.g., to 755).</p>";
}

?>
