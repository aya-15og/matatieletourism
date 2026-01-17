<?php
session_start();
if (!isset($_SESSION['admin'])) {
    header("Location: login.php");
    exit;
}
require '../includes/config.php';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    // Debugging
    $debug = [];
    
    if ($action === 'add' || $action === 'edit') {
        $id = $_POST['id'] ?? null;
        $name = trim($_POST['name']);
        $description = trim($_POST['description']);
        $start_date = $_POST['start_date'];
        $end_date = $_POST['end_date'] ?: null;
        $location = trim($_POST['location']);
        $link = trim($_POST['link']);
        $visible = isset($_POST['visible']) ? 1 : 0;
        $display_order = (int)$_POST['display_order'];
        
        // Start with existing image if editing
        $image_name = ($action === 'edit' && !empty($_POST['existing_image'])) ? $_POST['existing_image'] : '';
        
        $debug[] = "Action: $action";
        $debug[] = "Existing image from POST: " . ($_POST['existing_image'] ?? 'none');
        $debug[] = "File upload error code: " . ($_FILES['image']['error'] ?? 'no file');
        $debug[] = "File name: " . ($_FILES['image']['name'] ?? 'none');
        $debug[] = "File size: " . ($_FILES['image']['size'] ?? '0');
        
        // Check if new image is being uploaded
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $debug[] = "NEW IMAGE DETECTED - Processing...";
            
            // Process new image upload
            $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
            $filename = $_FILES['image']['name'];
            $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
            
            $debug[] = "File extension: $ext";
            
            if (in_array($ext, $allowed)) {
                $new_filename = uniqid() . '_' . time() . '.' . $ext;
                $upload_path = '../images/events/';
                
                $debug[] = "New filename: $new_filename";
                
                // Create directory if it doesn't exist
                if (!is_dir($upload_path)) {
                    mkdir($upload_path, 0755, true);
                    $debug[] = "Created upload directory";
                }
                
                // Load image based on type
                $source = null;
                $gd_error = '';
                
                // First, try to get image info
                $image_info = @getimagesize($_FILES['image']['tmp_name']);
                if ($image_info === false) {
                    $debug[] = "ERROR: Cannot read image file";
                    $gd_error = "Cannot read image file";
                } else {
                    $debug[] = "Image info: " . $image_info[0] . "x" . $image_info[1] . " type: " . $image_info['mime'];
                    
                    try {
                        switch($ext) {
                            case 'jpg':
                            case 'jpeg':
                                $source = @imagecreatefromjpeg($_FILES['image']['tmp_name']);
                                if (!$source) {
                                    $gd_error = "Failed to create JPEG image resource. Error: " . error_get_last()['message'];
                                }
                                break;
                            case 'png':
                                $source = @imagecreatefrompng($_FILES['image']['tmp_name']);
                                if (!$source) {
                                    $gd_error = "Failed to create PNG image resource";
                                }
                                break;
                            case 'gif':
                                $source = @imagecreatefromgif($_FILES['image']['tmp_name']);
                                if (!$source) {
                                    $gd_error = "Failed to create GIF image resource";
                                }
                                break;
                            case 'webp':
                                $source = @imagecreatefromwebp($_FILES['image']['tmp_name']);
                                if (!$source) {
                                    $gd_error = "Failed to create WEBP image resource";
                                }
                                break;
                        }
                    } catch (Exception $e) {
                        $gd_error = $e->getMessage();
                    }
                }
                
                $debug[] = "Image source created: " . ($source ? 'YES' : 'NO');
                if ($gd_error) {
                    $debug[] = "GD Error: $gd_error";
                }
                
                if ($source) {
                    // Get original dimensions
                    $orig_width = imagesx($source);
                    $orig_height = imagesy($source);
                    
                    // Set target dimensions (sidebar widget size)
                    $target_width = 400;
                    $target_height = 300;
                    
                    // Calculate scaling to cover the target area
                    $scale = max($target_width / $orig_width, $target_height / $orig_height);
                    $new_width = $orig_width * $scale;
                    $new_height = $orig_height * $scale;
                    
                    // Create new image
                    $resized = imagecreatetruecolor($target_width, $target_height);
                    
                    // Preserve transparency for PNG and GIF
                    if ($ext === 'png' || $ext === 'gif') {
                        imagealphablending($resized, false);
                        imagesavealpha($resized, true);
                        $transparent = imagecolorallocatealpha($resized, 255, 255, 255, 127);
                        imagefilledrectangle($resized, 0, 0, $target_width, $target_height, $transparent);
                    }
                    
                    // Center the image
                    $src_x = ($new_width - $target_width) / 2;
                    $src_y = ($new_height - $target_height) / 2;
                    
                    // Resample
                    imagecopyresampled(
                        $resized, $source,
                        0, 0,
                        -$src_x / $scale, -$src_y / $scale,
                        $new_width, $new_height,
                        $orig_width, $orig_height
                    );
                    
                    // Save resized image
                    $save_path = $upload_path . $new_filename;
                    switch($ext) {
                        case 'jpg':
                        case 'jpeg':
                            imagejpeg($resized, $save_path, 85);
                            break;
                        case 'png':
                            imagepng($resized, $save_path, 8);
                            break;
                        case 'gif':
                            imagegif($resized, $save_path);
                            break;
                        case 'webp':
                            imagewebp($resized, $save_path, 85);
                            break;
                    }
                    
                    imagedestroy($source);
                    imagedestroy($resized);
                    
                    $debug[] = "Image saved successfully to: $save_path";
                    $debug[] = "File exists: " . (file_exists($save_path) ? 'YES' : 'NO');
                    
                    // Delete old image if editing and it exists
                    if ($action === 'edit' && !empty($_POST['existing_image'])) {
                        $old_file = $upload_path . basename($_POST['existing_image']);
                        if (file_exists($old_file)) {
                            unlink($old_file);
                            $debug[] = "Deleted old image: $old_file";
                        }
                    }
                    
                    // Set new image name
                    $image_name = $new_filename;
                    $debug[] = "Set image_name to: $image_name";
                } else {
                    $debug[] = "ERROR: Image processing failed!";
                }
            } else {
                $debug[] = "ERROR: Invalid file type: $ext";
            }
        } else {
            $debug[] = "No new image uploaded - keeping existing: $image_name";
        }
        
        if ($action === 'add') {
            $stmt = $pdo->prepare("INSERT INTO events (name, description, start_date, end_date, location, image, link, visible, display_order) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$name, $description, $start_date, $end_date, $location, $image_name, $link, $visible, $display_order]);
            $success = "Event added successfully! Image: " . ($image_name ?: 'none');
        } else {
            $debug[] = "About to UPDATE with image_name: $image_name";
            $stmt = $pdo->prepare("UPDATE events SET name=?, description=?, start_date=?, end_date=?, location=?, image=?, link=?, visible=?, display_order=? WHERE id=?");
            $stmt->execute([$name, $description, $start_date, $end_date, $location, $image_name, $link, $visible, $display_order, $id]);
            $success = "Event updated successfully!<br><strong>Debug Info:</strong><br>" . implode('<br>', $debug);
        }
    }
    
    if ($action === 'delete') {
        $id = $_POST['id'];
        
        // Get image filename before deleting
        $stmt = $pdo->prepare("SELECT image FROM events WHERE id=?");
        $stmt->execute([$id]);
        $event = $stmt->fetch();
        
        // Delete the event
        $stmt = $pdo->prepare("DELETE FROM events WHERE id=?");
        $stmt->execute([$id]);
        
        // Delete image file
        if (!empty($event['image'])) {
            $image_path = '../images/events/' . basename($event['image']);
            if (file_exists($image_path)) {
                unlink($image_path);
            }
        }
        
        $success = "Event deleted successfully!";
    }
    
    if ($action === 'toggle_visibility') {
        $id = $_POST['id'];
        $stmt = $pdo->prepare("UPDATE events SET visible = NOT visible WHERE id=?");
        $stmt->execute([$id]);
        $success = "Visibility updated!";
    }
    
    if ($action === 'remove_image') {
        $id = $_POST['id'];
        
        // Get image filename
        $stmt = $pdo->prepare("SELECT image FROM events WHERE id=?");
        $stmt->execute([$id]);
        $event = $stmt->fetch();
        
        // Update event to remove image
        $stmt = $pdo->prepare("UPDATE events SET image = NULL WHERE id=?");
        $stmt->execute([$id]);
        
        // Delete image file
        if (!empty($event['image'])) {
            $image_path = '../images/events/' . basename($event['image']);
            if (file_exists($image_path)) {
                unlink($image_path);
            }
        }
        
        // Redirect to edit page to refresh the form
        header("Location: manage_events.php?edit=" . $id . "&msg=image_removed");
        exit;
    }
}

// Fetch all events
$events = $pdo->query("SELECT * FROM events ORDER BY start_date DESC, display_order ASC")->fetchAll(PDO::FETCH_ASSOC);

// Get event for editing if id is set
$edit_event = null;
if (isset($_GET['edit'])) {
    $stmt = $pdo->prepare("SELECT * FROM events WHERE id=?");
    $stmt->execute([$_GET['edit']]);
    $edit_event = $stmt->fetch();
}

// Check for success message from redirect
if (isset($_GET['msg'])) {
    if ($_GET['msg'] === 'image_removed') {
        $success = "Image removed successfully!";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Manage Events | Admin</title>
<link rel="stylesheet" href="../assets/admin.css">
<style>
.form-section {
    background: white;
    padding: 2rem;
    border-radius: 8px;
    margin-bottom: 2rem;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}
.form-group {
    margin-bottom: 1.5rem;
}
.form-group label {
    display: block;
    margin-bottom: 0.5rem;
    font-weight: 600;
    color: #333;
}
.form-group input[type="text"],
.form-group input[type="date"],
.form-group input[type="url"],
.form-group input[type="number"],
.form-group textarea,
.form-group select {
    width: 100%;
    padding: 0.75rem;
    border: 1px solid #ddd;
    border-radius: 4px;
    font-size: 1rem;
}
.form-group textarea {
    min-height: 100px;
    resize: vertical;
}
.form-group input[type="file"] {
    padding: 0.5rem;
}
.checkbox-group {
    display: flex;
    align-items: center;
    gap: 0.5rem;
}
.checkbox-group input[type="checkbox"] {
    width: auto;
}
.form-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 1rem;
}
.btn-primary {
    background: #667eea;
    color: white;
    padding: 0.75rem 1.5rem;
    border: none;
    border-radius: 4px;
    cursor: pointer;
    font-size: 1rem;
    font-weight: 600;
}
.btn-primary:hover {
    background: #5568d3;
}
.btn-cancel {
    background: #6b7280;
    color: white;
    padding: 0.75rem 1.5rem;
    border: none;
    border-radius: 4px;
    cursor: pointer;
    font-size: 1rem;
    text-decoration: none;
    display: inline-block;
    margin-left: 1rem;
}
.events-table {
    width: 100%;
    background: white;
    border-radius: 8px;
    overflow: hidden;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}
.events-table table {
    width: 100%;
    border-collapse: collapse;
}
.events-table th {
    background: #667eea;
    color: white;
    padding: 1rem;
    text-align: left;
    font-weight: 600;
}
.events-table td {
    padding: 1rem;
    border-bottom: 1px solid #eee;
}
.events-table tr:last-child td {
    border-bottom: none;
}
.event-thumbnail {
    width: 80px;
    height: 60px;
    object-fit: cover;
    border-radius: 4px;
}
.status-badge {
    display: inline-block;
    padding: 0.25rem 0.75rem;
    border-radius: 12px;
    font-size: 0.85rem;
    font-weight: 600;
}
.status-visible {
    background: #d1fae5;
    color: #065f46;
}
.status-hidden {
    background: #fee2e2;
    color: #991b1b;
}
.status-upcoming {
    background: #dbeafe;
    color: #1e40af;
}
.status-ongoing {
    background: #fef3c7;
    color: #92400e;
}
.status-past {
    background: #e5e7eb;
    color: #374151;
}
.action-buttons {
    display: flex;
    gap: 0.5rem;
}
.action-buttons button,
.action-buttons a {
    padding: 0.5rem 1rem;
    border-radius: 4px;
    text-decoration: none;
    font-size: 0.875rem;
    cursor: pointer;
    border: none;
}
.btn-edit {
    background: #3b82f6;
    color: white;
}
.btn-delete {
    background: #ef4444;
    color: white;
}
.btn-toggle {
    background: #6b7280;
    color: white;
}
.success-message {
    background: #d1fae5;
    color: #065f46;
    padding: 1rem;
    border-radius: 4px;
    margin-bottom: 1rem;
    border-left: 4px solid #10b981;
}
.image-preview {
    margin-top: 0.5rem;
}
.image-preview img {
    max-width: 200px;
    border-radius: 4px;
    border: 2px solid #ddd;
}
.help-text {
    font-size: 0.875rem;
    color: #6b7280;
    margin-top: 0.25rem;
}
.btn-remove-image,
.btn-cancel-upload {
    background: #ef4444;
    color: white;
    padding: 0.5rem 1rem;
    border: none;
    border-radius: 4px;
    cursor: pointer;
    font-size: 0.875rem;
    margin-top: 0.5rem;
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
}
.btn-remove-image:hover,
.btn-cancel-upload:hover {
    background: #dc2626;
}
.btn-cancel-upload {
    background: #6b7280;
}
.btn-cancel-upload:hover {
    background: #4b5563;
}
.debug-info {
    background: #fff3cd;
    border: 1px solid #ffc107;
    padding: 1rem;
    margin-bottom: 1rem;
    border-radius: 4px;
    font-family: monospace;
    font-size: 0.875rem;
}
</style>
</head>
<body>
<header>
    <div><strong>Manage Events</strong></div>
    <nav>
        <a href="dashboard.php">← Dashboard</a>
        <a href="../index.php" target="_blank">View Site</a>
        <a href="logout.php">Logout</a>
    </nav>
</header>

<main>
    <h1><?= $edit_event ? 'Edit Event' : 'Add New Event' ?></h1>
    
    <?php if (isset($success)): ?>
        <div class="success-message" style="white-space: pre-wrap;"><?= $success ?></div>
    <?php endif; ?>
    
    <div class="form-section">
        <form method="post" enctype="multipart/form-data">
            <input type="hidden" name="action" value="<?= $edit_event ? 'edit' : 'add' ?>">
            <?php if ($edit_event): ?>
                <input type="hidden" name="id" value="<?= $edit_event['id'] ?>">
                <input type="hidden" name="existing_image" value="<?= htmlspecialchars($edit_event['image'] ?? '') ?>">
            <?php endif; ?>
            
            <div class="form-group">
                <label for="name">Event Name *</label>
                <input type="text" id="name" name="name" required 
                       value="<?= htmlspecialchars($edit_event['name'] ?? '') ?>">
            </div>
            
            <div class="form-group">
                <label for="description">Description</label>
                <textarea id="description" name="description"><?= htmlspecialchars($edit_event['description'] ?? '') ?></textarea>
                <div class="help-text">Brief description of the event (optional)</div>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="start_date">Start Date *</label>
                    <input type="date" id="start_date" name="start_date" required
                           value="<?= htmlspecialchars($edit_event['start_date'] ?? '') ?>">
                </div>
                
                <div class="form-group">
                    <label for="end_date">End Date</label>
                    <input type="date" id="end_date" name="end_date"
                           value="<?= htmlspecialchars($edit_event['end_date'] ?? '') ?>">
                    <div class="help-text">Leave empty for single-day events</div>
                </div>
            </div>
            
            <div class="form-group">
                <label for="location">Location</label>
                <input type="text" id="location" name="location"
                       value="<?= htmlspecialchars($edit_event['location'] ?? '') ?>">
                <div class="help-text">Venue or location name</div>
            </div>
            
            <div class="form-group">
                <label for="image">Event Image</label>
                <input type="file" id="image" name="image" accept="image/*" onchange="previewImage(this)">
                <div class="help-text">Image will be automatically resized to 400x300px for optimal display. Recommended: 800x600px or larger</div>
                <?php if ($edit_event && !empty($edit_event['image'])): ?>
                    <div class="image-preview" id="currentImagePreview">
                        <p><strong>Current image:</strong></p>
                        <img src="../images/events/<?= htmlspecialchars(basename($edit_event['image'])) ?>" alt="Current event image" id="currentImage">
                        <button type="button" class="btn-remove-image" onclick="removeCurrentImage()">
                            <svg width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                                <path d="M2.5 1a1 1 0 0 0-1 1v1a1 1 0 0 0 1 1H3v9a2 2 0 0 0 2 2h6a2 2 0 0 0 2-2V4h.5a1 1 0 0 0 1-1V2a1 1 0 0 0-1-1H10a1 1 0 0 0-1-1H7a1 1 0 0 0-1 1H2.5zm3 4a.5.5 0 0 1 .5.5v7a.5.5 0 0 1-1 0v-7a.5.5 0 0 1 .5-.5zM8 5a.5.5 0 0 1 .5.5v7a.5.5 0 0 1-1 0v-7A.5.5 0 0 1 8 5zm3 .5v7a.5.5 0 0 1-1 0v-7a.5.5 0 0 1 1 0z"/>
                            </svg>
                            Remove Image
                        </button>
                    </div>
                    <div class="image-preview" id="newImagePreview" style="display: none;">
                        <p><strong>New image preview:</strong></p>
                        <img src="" alt="New image preview" id="newImage">
                        <button type="button" class="btn-cancel-upload" onclick="cancelImageUpload()">Cancel Upload</button>
                    </div>
                <?php else: ?>
                    <div class="image-preview" id="newImagePreview" style="display: none;">
                        <p><strong>Image preview:</strong></p>
                        <img src="" alt="Image preview" id="newImage">
                        <button type="button" class="btn-cancel-upload" onclick="cancelImageUpload()">Cancel</button>
                    </div>
                <?php endif; ?>
            </div>
            
            <div class="form-group">
                <label for="link">Event Link/URL</label>
                <input type="url" id="link" name="link"
                       value="<?= htmlspecialchars($edit_event['link'] ?? '') ?>"
                       placeholder="https://example.com/event">
                <div class="help-text">Link to event page, ticket sales, or more information (optional)</div>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="display_order">Display Order</label>
                    <input type="number" id="display_order" name="display_order" 
                           value="<?= htmlspecialchars($edit_event['display_order'] ?? '0') ?>">
                    <div class="help-text">Lower numbers appear first in slideshow</div>
                </div>
                
                <div class="form-group">
                    <label>&nbsp;</label>
                    <div class="checkbox-group">
                        <input type="checkbox" id="visible" name="visible" 
                               <?= ($edit_event['visible'] ?? 1) ? 'checked' : '' ?>>
                        <label for="visible" style="margin: 0;">Visible on website</label>
                    </div>
                </div>
            </div>
            
            <div style="margin-top: 2rem;">
                <button type="submit" class="btn-primary">
                    <?= $edit_event ? 'Update Event' : 'Add Event' ?>
                </button>
                <?php if ($edit_event): ?>
                    <a href="manage_events.php" class="btn-cancel">Cancel Edit</a>
                <?php endif; ?>
            </div>
        </form>
    </div>
    
    <h2>All Events</h2>
    <div class="events-table">
        <table>
            <thead>
                <tr>
                    <th>Image</th>
                    <th>Event Name</th>
                    <th>Date(s)</th>
                    <th>Location</th>
                    <th>Status</th>
                    <th>Visibility</th>
                    <th>Order</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($events)): ?>
                    <tr>
                        <td colspan="8" style="text-align: center; padding: 2rem; color: #6b7280;">
                            No events yet. Add your first event above!
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($events as $event): 
                        $today = date('Y-m-d');
                        $start = $event['start_date'];
                        $end = $event['end_date'] ?: $start;
                        
                        if ($today < $start) {
                            $status = 'upcoming';
                            $status_text = 'Upcoming';
                        } elseif ($today >= $start && $today <= $end) {
                            $status = 'ongoing';
                            $status_text = 'Ongoing';
                        } else {
                            $status = 'past';
                            $status_text = 'Past';
                        }
                    ?>
                    <tr>
                        <td>
                            <?php if (!empty($event['image'])): ?>
                                <img src="../images/events/<?= htmlspecialchars(basename($event['image'])) ?>" 
                                     alt="<?= htmlspecialchars($event['name']) ?>" 
                                     class="event-thumbnail">
                            <?php else: ?>
                                <div style="width: 80px; height: 60px; background: #e5e7eb; border-radius: 4px; display: flex; align-items: center; justify-content: center; font-size: 0.75rem; color: #6b7280;">No image</div>
                            <?php endif; ?>
                        </td>
                        <td>
                            <strong><?= htmlspecialchars($event['name']) ?></strong>
                            <?php if (!empty($event['description'])): ?>
                                <br><small style="color: #6b7280;"><?= htmlspecialchars(substr($event['description'], 0, 60)) ?><?= strlen($event['description']) > 60 ? '...' : '' ?></small>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?= date('M d, Y', strtotime($event['start_date'])) ?>
                            <?php if ($event['end_date'] && $event['end_date'] !== $event['start_date']): ?>
                                <br><small style="color: #6b7280;">to <?= date('M d, Y', strtotime($event['end_date'])) ?></small>
                            <?php endif; ?>
                        </td>
                        <td><?= htmlspecialchars($event['location'] ?: '—') ?></td>
                        <td>
                            <span class="status-badge status-<?= $status ?>"><?= $status_text ?></span>
                        </td>
                        <td>
                            <span class="status-badge <?= $event['visible'] ? 'status-visible' : 'status-hidden' ?>">
                                <?= $event['visible'] ? 'Visible' : 'Hidden' ?>
                            </span>
                        </td>
                        <td><?= $event['display_order'] ?></td>
                        <td>
                            <div class="action-buttons">
                                <a href="?edit=<?= $event['id'] ?>" class="btn-edit">Edit</a>
                                <form method="post" style="display: inline;">
                                    <input type="hidden" name="action" value="toggle_visibility">
                                    <input type="hidden" name="id" value="<?= $event['id'] ?>">
                                    <button type="submit" class="btn-toggle">
                                        <?= $event['visible'] ? 'Hide' : 'Show' ?>
                                    </button>
                                </form>
                                <form method="post" style="display: inline;" 
                                      onsubmit="return confirm('Delete this event? This cannot be undone.');">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="id" value="<?= $event['id'] ?>">
                                    <button type="submit" class="btn-delete">Delete</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</main>

<script>
// Preview image before upload
function previewImage(input) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById('newImage').src = e.target.result;
            document.getElementById('newImagePreview').style.display = 'block';
            const currentPreview = document.getElementById('currentImagePreview');
            if (currentPreview) {
                currentPreview.style.display = 'none';
            }
        };
        reader.readAsDataURL(input.files[0]);
    }
}

// Cancel image upload
function cancelImageUpload() {
    document.getElementById('image').value = '';
    document.getElementById('newImagePreview').style.display = 'none';
    const currentPreview = document.getElementById('currentImagePreview');
    if (currentPreview) {
        currentPreview.style.display = 'block';
    }
}

// Remove current image
function removeCurrentImage() {
    if (confirm('Remove this image? This will delete the image file.')) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.innerHTML = `
            <input type="hidden" name="action" value="remove_image">
            <input type="hidden" name="id" value="<?= $edit_event['id'] ?? '' ?>">
        `;
        document.body.appendChild(form);
        form.submit();
    }
}
</script>
</body>
</html>