<?php
ini_set('display_errors', 0);
error_reporting(E_ALL);
session_start();
if (!isset($_SESSION['admin'])) {
    header("Location: login.php");
    exit;
}
require '../includes/config.php';
require '../includes/functions.php';

$edit_data = [
    'id' => '',
    'title' => '',
    'image' => '',
    'link' => '',
    'position' => 'sidebar',
    'start_date' => '',
    'end_date' => '',
    'visible' => 1
];

// Editing existing
if (isset($_GET['edit'])) {
    $id = intval($_GET['edit']);
    $stmt = $pdo->prepare("SELECT * FROM ads WHERE id=?");
    $stmt->execute([$id]);
    $edit_data = $stmt->fetch(PDO::FETCH_ASSOC) ?: $edit_data;
}

// Adding new
if (isset($_GET['new'])) {
    $edit_data = [
        'id' => '',
        'title' => '',
        'image' => '',
        'link' => '',
        'position' => 'sidebar',
        'start_date' => '',
        'end_date' => '',
        'visible' => 1
    ];
}

// Enhanced image processing function
function process_ad_image($file, $position) {
    $upload_dir = '../images/ads/';
    
    // Define optimal dimensions for each position
    $dimensions = [
        'top' => ['width' => 970, 'height' => 250],
        'top-alt' => ['width' => 728, 'height' => 90],
        'sidebar' => ['width' => 300, 'height' => 250],
        'sidebar-tall' => ['width' => 300, 'height' => 600],
        'bottom' => ['width' => 728, 'height' => 90],
        'bottom-wide' => ['width' => 970, 'height' => 90],
        'inline' => ['width' => 468, 'height' => 60],
        'hero-overlay' => ['width' => 336, 'height' => 280]
    ];
    
    $target_dim = $dimensions[$position] ?? $dimensions['sidebar'];
    
    // Validate file
    $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    $file_type = $file['type'];
    
    if (!in_array($file_type, $allowed_types)) {
        return ['error' => 'Invalid file type. Only JPG, PNG, GIF, and WebP allowed.'];
    }
    
    // Create image from upload
    $source = null;
    switch ($file_type) {
        case 'image/jpeg':
            $source = imagecreatefromjpeg($file['tmp_name']);
            break;
        case 'image/png':
            $source = imagecreatefrompng($file['tmp_name']);
            break;
        case 'image/gif':
            $source = imagecreatefromgif($file['tmp_name']);
            break;
        case 'image/webp':
            $source = imagecreatefromwebp($file['tmp_name']);
            break;
    }
    
    if (!$source) {
        return ['error' => 'Failed to process image.'];
    }
    
    $src_width = imagesx($source);
    $src_height = imagesy($source);
    
    // Calculate aspect ratios
    $src_ratio = $src_width / $src_height;
    $target_ratio = $target_dim['width'] / $target_dim['height'];
    
    // Determine crop/resize strategy
    if (abs($src_ratio - $target_ratio) < 0.1) {
        // Aspect ratios are similar - simple resize
        $new_width = $target_dim['width'];
        $new_height = $target_dim['height'];
        $src_x = 0;
        $src_y = 0;
        $src_w = $src_width;
        $src_h = $src_height;
    } else {
        // Aspect ratios differ - crop to fit
        if ($src_ratio > $target_ratio) {
            // Source is wider - crop width
            $src_h = $src_height;
            $src_w = $src_height * $target_ratio;
            $src_x = ($src_width - $src_w) / 2;
            $src_y = 0;
        } else {
            // Source is taller - crop height
            $src_w = $src_width;
            $src_h = $src_width / $target_ratio;
            $src_x = 0;
            $src_y = ($src_height - $src_h) / 2;
        }
        $new_width = $target_dim['width'];
        $new_height = $target_dim['height'];
    }
    
    // Create final image
    $final = imagecreatetruecolor($new_width, $new_height);
    
    // Preserve transparency for PNG/GIF
    if ($file_type === 'image/png' || $file_type === 'image/gif') {
        imagealphablending($final, false);
        imagesavealpha($final, true);
        $transparent = imagecolorallocatealpha($final, 255, 255, 255, 127);
        imagefilledrectangle($final, 0, 0, $new_width, $new_height, $transparent);
    }
    
    // Copy and resample
    imagecopyresampled($final, $source, 0, 0, $src_x, $src_y, $new_width, $new_height, $src_w, $src_h);
    
    // Generate filename
    $filename = 'ad_' . $position . '_' . time() . '_' . uniqid() . '.jpg';
    $filepath = $upload_dir . $filename;
    
    // Save optimized image
    imagejpeg($final, $filepath, 90);
    
    // Clean up
    imagedestroy($source);
    imagedestroy($final);
    
    return ['success' => true, 'filename' => $filename, 'dimensions' => $target_dim];
}

// Handle form submit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'] ?? '';
    $title = $_POST['title'];
    $link = $_POST['link'];
    $position = $_POST['position'];
    $start_date = $_POST['start_date'] ?: null;
    $end_date = $_POST['end_date'] ?: null;
    $visible = isset($_POST['visible']) ? 1 : 0;

    $image_filename = null;
    
    // Process image upload
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $result = process_ad_image($_FILES['image'], $position);
        
        if (isset($result['error'])) {
            $error_message = $result['error'];
        } else {
            $image_filename = $result['filename'];
        }
    }

    if (!isset($error_message)) {
        if ($id) {
            if ($image_filename) {
                // Delete old image if exists
                $old = $pdo->prepare("SELECT image FROM ads WHERE id=?")->execute([$id]);
                $old_data = $old->fetch(PDO::FETCH_ASSOC);
                if ($old_data && file_exists('../images/ads/' . $old_data['image'])) {
                    unlink('../images/ads/' . $old_data['image']);
                }
                
                $stmt = $pdo->prepare("UPDATE ads SET title=?, image=?, link=?, position=?, start_date=?, end_date=?, visible=? WHERE id=?");
                $stmt->execute([$title, $image_filename, $link, $position, $start_date, $end_date, $visible, $id]);
            } else {
                $stmt = $pdo->prepare("UPDATE ads SET title=?, link=?, position=?, start_date=?, end_date=?, visible=? WHERE id=?");
                $stmt->execute([$title, $link, $position, $start_date, $end_date, $visible, $id]);
            }
        } else {
            if ($image_filename) {
                $stmt = $pdo->prepare("INSERT INTO ads (title, image, link, position, start_date, end_date, visible) VALUES (?, ?, ?, ?, ?, ?, ?)");
                $stmt->execute([$title, $image_filename, $link, $position, $start_date, $end_date, $visible]);
            }
        }

        redirect('manage_ads.php');
    }
}

// Delete
if (isset($_GET['delete'])) {
    $stmt = $pdo->prepare("SELECT image FROM ads WHERE id=?");
    $stmt->execute([intval($_GET['delete'])]);
    $ad = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($ad && file_exists('../images/ads/' . $ad['image'])) {
        unlink('../images/ads/' . $ad['image']);
    }
    
    $stmt = $pdo->prepare("DELETE FROM ads WHERE id=?");
    $stmt->execute([intval($_GET['delete'])]);
    redirect('manage_ads.php');
}

// Fetch all with stats
$rows = $pdo->query("SELECT * FROM ads ORDER BY created_at DESC")->fetchAll(PDO::FETCH_ASSOC);

function get_ad_path($image) {
    if (empty($image)) return '../images/placeholder.jpg';
    $filename = basename($image);
    return '../images/ads/' . $filename;
}

function is_ad_active($start, $end) {
    $now = date('Y-m-d');
    $active = true;
    if ($start && $start > $now) $active = false;
    if ($end && $end < $now) $active = false;
    return $active;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Manage Advertisements - Admin</title>
<link rel="stylesheet" href="../assets/admin.css">
<style>
.visible-yes { color: green; font-weight: bold; }
.visible-no { color: #aaa; }
.ad-preview { max-width: 200px; max-height: 100px; object-fit: contain; }
.position-badge { 
    display: inline-block;
    padding: 0.3rem 0.8rem;
    border-radius: 12px;
    font-size: 0.85rem;
    font-weight: 600;
}
.position-top { background: #e3f2fd; color: #1976d2; }
.position-top-alt { background: #e1f5fe; color: #0277bd; }
.position-sidebar { background: #f3e5f5; color: #7b1fa2; }
.position-sidebar-tall { background: #f3e5f5; color: #6a1b9a; }
.position-bottom { background: #e8f5e9; color: #388e3c; }
.position-bottom-wide { background: #e8f5e9; color: #2e7d32; }
.position-inline { background: #fff3e0; color: #f57c00; }
.position-hero-overlay { background: #fce4ec; color: #c2185b; }
.status-active { color: green; }
.status-inactive { color: orange; }
.status-expired { color: red; }
.info-box {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 1.5rem;
    border-radius: 8px;
    margin-bottom: 2rem;
}
.info-box h3 {
    margin: 0 0 1rem 0;
    font-size: 1.3rem;
}
.info-box ul {
    margin: 0.5rem 0 0 1.5rem;
    padding: 0;
}
.info-box li {
    margin: 0.3rem 0;
}
.alert-error {
    background: #ffebee;
    border-left: 4px solid #f44336;
    color: #c62828;
    padding: 1rem;
    margin-bottom: 1rem;
    border-radius: 4px;
}
.dimensions-info {
    background: #f8f9fa;
    padding: 0.8rem;
    border-radius: 6px;
    margin-top: 0.5rem;
    font-size: 0.9rem;
    color: #666;
}
.preview-container {
    margin-top: 1rem;
    padding: 1rem;
    background: #f8f9fa;
    border-radius: 8px;
}
.preview-label {
    font-weight: 600;
    margin-bottom: 0.5rem;
    color: #333;
}
</style>
</head>
<body>

<h1>Manage Advertisements</h1>
<a href="dashboard.php" class="back-btn">← Back to Dashboard</a>

<?php if (isset($error_message)): ?>
<div class="alert-error">
    <strong>Error:</strong> <?= htmlspecialchars($error_message) ?>
</div>
<?php endif; ?>

<div class="info-box">
    <h3>📐 Advertisement Positions & Sizes:</h3>
    <ul>
        <li><strong>Top Banner:</strong> 970x250px - Premium hero section placement</li>
        <li><strong>Top Alt Banner:</strong> 728x90px - Alternative header banner</li>
        <li><strong>Sidebar Standard:</strong> 300x250px - Medium rectangle (most popular)</li>
        <li><strong>Sidebar Tall:</strong> 300x600px - Half-page vertical</li>
        <li><strong>Bottom Banner:</strong> 728x90px - Footer banner</li>
        <li><strong>Bottom Wide:</strong> 970x90px - Wide footer banner</li>
        <li><strong>Inline Content:</strong> 468x60px - Between content sections</li>
        <li><strong>Hero Overlay:</strong> 336x280px - Large rectangle on hero</li>
    </ul>
    <p style="margin-top: 1rem; opacity: 0.95;">💡 <strong>Pro Tips:</strong></p>
    <ul>
        <li>Images are automatically cropped and optimized to exact dimensions</li>
        <li>Upload high-quality images (at least target size or larger)</li>
        <li>Ads rotate randomly - multiple ads per position for variety</li>
        <li>Use scheduling to run campaigns during specific periods</li>
        <li>Monitor CTR to optimize ad performance</li>
    </ul>
</div>

<form method="post" enctype="multipart/form-data">
    <input type="hidden" name="id" value="<?= htmlspecialchars($edit_data['id']) ?>">

    <label>Ad Title/Campaign Name: *</label>
    <input type="text" name="title" required value="<?= htmlspecialchars($edit_data['title'] ?? '') ?>" placeholder="e.g., Summer Special Promotion">

    <label>Click-through URL (optional):</label>
    <input type="url" name="link" placeholder="https://example.com" value="<?= htmlspecialchars($edit_data['link'] ?? '') ?>">
    <small>Leave empty if ad should not be clickable</small>

    <label>Ad Position: *</label>
    <select name="position" required id="position-select">
        <option value="top" <?= ($edit_data['position'] ?? '') === 'top' ? 'selected' : '' ?>>Top Banner (970x250) - Premium</option>
        <option value="top-alt" <?= ($edit_data['position'] ?? '') === 'top-alt' ? 'selected' : '' ?>>Top Alt Banner (728x90)</option>
        <option value="sidebar" <?= ($edit_data['position'] ?? '') === 'sidebar' ? 'selected' : '' ?>>Sidebar Standard (300x250) ⭐ Popular</option>
        <option value="sidebar-tall" <?= ($edit_data['position'] ?? '') === 'sidebar-tall' ? 'selected' : '' ?>>Sidebar Tall (300x600)</option>
        <option value="bottom" <?= ($edit_data['position'] ?? '') === 'bottom' ? 'selected' : '' ?>>Bottom Banner (728x90)</option>
        <option value="bottom-wide" <?= ($edit_data['position'] ?? '') === 'bottom-wide' ? 'selected' : '' ?>>Bottom Wide (970x90)</option>
        <option value="inline" <?= ($edit_data['position'] ?? '') === 'inline' ? 'selected' : '' ?>>Inline Content (468x60)</option>
        <option value="hero-overlay" <?= ($edit_data['position'] ?? '') === 'hero-overlay' ? 'selected' : '' ?>>Hero Overlay (336x280)</option>
    </select>
    <div class="dimensions-info" id="dimensions-info">
        Select a position to see optimal dimensions
    </div>

    <label>Start Date (optional):</label>
    <input type="date" name="start_date" value="<?= htmlspecialchars($edit_data['start_date'] ?? '') ?>">
    <small>Ad will only show from this date onwards</small>

    <label>End Date (optional):</label>
    <input type="date" name="end_date" value="<?= htmlspecialchars($edit_data['end_date'] ?? '') ?>">
    <small>Ad will stop showing after this date</small>

    <label>
        <input type="checkbox" name="visible" value="1" <?= ($edit_data['visible'] ?? 1) ? 'checked' : '' ?>>
        Visible (Active)
    </label>

    <?php if (!empty($edit_data['image'])): ?>
        <div class="preview-container">
            <div class="preview-label">Current Ad Image:</div>
            <img src="<?= htmlspecialchars(get_ad_path($edit_data['image'])) ?>" alt="Current ad" style="max-width: 100%; border: 2px solid #e0e0e0; border-radius: 4px;">
        </div>
    <?php endif; ?>

    <label>Ad Image: <?= empty($edit_data['id']) ? '*' : '' ?></label>
    <input type="file" name="image" accept="image/*" <?= empty($edit_data['id']) ? 'required' : '' ?>>
    <small>Upload JPG, PNG, GIF, or WebP. Image will be automatically optimized and cropped to exact dimensions.</small>

    <button type="submit"><?= $edit_data['id'] ? 'Update' : 'Add' ?> Advertisement</button>
</form>

<h2>Active Advertisements</h2>

<?php if (empty($rows)): ?>
<div style="text-align: center; padding: 3rem; background: #f8f9fa; border-radius: 8px; color: #999;">
    <p style="font-size: 1.1rem; margin: 0;">📢 No advertisements yet.</p>
    <p style="margin: 0.5rem 0 0 0;">Add your first ad using the form above!</p>
</div>
<?php else: ?>

<table>
<thead>
<tr>
    <th>Preview</th>
    <th>Title</th>
    <th>Position</th>
    <th>Schedule</th>
    <th>Status</th>
    <th>Performance</th>
    <th>Actions</th>
</tr>
</thead>
<tbody>
<?php foreach ($rows as $r): 
    $active = is_ad_active($r['start_date'], $r['end_date']);
    $status_class = $r['visible'] ? ($active ? 'status-active' : 'status-inactive') : 'status-expired';
?>
<tr>
    <td><img src="<?= htmlspecialchars(get_ad_path($r['image'])) ?>" class="ad-preview" alt="<?= htmlspecialchars($r['title']) ?>"></td>
    <td>
        <strong><?= htmlspecialchars($r['title']) ?></strong>
        <?php if (!empty($r['link'])): ?>
            <br><small><a href="<?= htmlspecialchars($r['link']) ?>" target="_blank" style="color: #667eea;">🔗 <?= htmlspecialchars(substr($r['link'], 0, 40)) ?><?= strlen($r['link']) > 40 ? '...' : '' ?></a></small>
        <?php endif; ?>
    </td>
    <td>
        <span class="position-badge position-<?= $r['position'] ?>">
            <?= ucfirst(str_replace('-', ' ', $r['position'])) ?>
        </span>
    </td>
    <td>
        <?php if ($r['start_date'] || $r['end_date']): ?>
            <small>
                <?= $r['start_date'] ? date('M d, Y', strtotime($r['start_date'])) : 'Anytime' ?><br>
                to<br>
                <?= $r['end_date'] ? date('M d, Y', strtotime($r['end_date'])) : 'No end' ?>
            </small>
        <?php else: ?>
            <small style="color: #999;">Always active</small>
        <?php endif; ?>
    </td>
    <td class="<?= $status_class ?>">
        <?php 
        if (!$r['visible']) echo '✗ Hidden';
        elseif (!$active) echo '⏸ Scheduled';
        else echo '✓ Active';
        ?>
    </td>
    <td>
        <small>
            👁️ <?= number_format($r['impressions']) ?> views<br>
            🖱️ <?= number_format($r['clicks']) ?> clicks
            <?php if ($r['impressions'] > 0): ?>
                <br><strong>CTR: <?= number_format(($r['clicks'] / $r['impressions']) * 100, 2) ?>%</strong>
            <?php endif; ?>
        </small>
    </td>
    <td>
        <a href="?edit=<?= $r['id'] ?>" class="edit">Edit</a>
        <a href="?delete=<?= $r['id'] ?>" class="del" onclick="return confirm('Delete this ad? Stats will be lost.')">Delete</a>
    </td>
</tr>
<?php endforeach; ?>
</tbody>
</table>

<?php endif; ?>

<div style="background: #fff3cd; border-left: 4px solid #ffc107; padding: 1rem; border-radius: 4px; margin-top: 2rem;">
    <p style="margin: 0; color: #856404;"><strong>📊 Performance Tracking:</strong></p>
    <p style="margin: 0.5rem 0 0 0; color: #856404;">
        • <strong>Impressions</strong> = Number of times ad was displayed<br>
        • <strong>Clicks</strong> = Number of times ad was clicked<br>
        • <strong>CTR (Click-Through Rate)</strong> = (Clicks ÷ Impressions) × 100%<br>
        • Industry average CTR is 0.5-2%. Above 2% is excellent!
    </p>
</div>

<script>
const dimensionsMap = {
    'top': '970×250px - Full-width premium banner below hero',
    'top-alt': '728×90px - Standard leaderboard banner',
    'sidebar': '300×250px - Medium rectangle (IAB standard)',
    'sidebar-tall': '300×600px - Half-page ad (high visibility)',
    'bottom': '728×90px - Footer leaderboard',
    'bottom-wide': '970×90px - Super leaderboard',
    'inline': '468×60px - Banner between content',
    'hero-overlay': '336×280px - Large rectangle overlay'
};

document.getElementById('position-select').addEventListener('change', function() {
    const info = dimensionsMap[this.value];
    document.getElementById('dimensions-info').textContent = '📏 ' + info;
});

// Set initial dimensions info
const initialPos = document.getElementById('position-select').value;
document.getElementById('dimensions-info').textContent = '📏 ' + dimensionsMap[initialPos];
</script>

</body>
</html>