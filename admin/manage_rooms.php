<?php
session_start();
if (!isset($_SESSION['admin'])) {
    header("Location: login.php");
    exit;
}
require '../includes/config.php';
require '../includes/functions.php';

// Get selected property
$selected_stay_id = isset($_GET['stay_id']) ? intval($_GET['stay_id']) : 0;

// Fetch all partner properties
$partner_properties = $pdo->query("SELECT id, name FROM stays WHERE is_partner = 1 ORDER BY name ASC")->fetchAll(PDO::FETCH_ASSOC);

if (!$selected_stay_id && !empty($partner_properties)) {
    $selected_stay_id = $partner_properties[0]['id'];
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !isset($_POST['save_order'])) {
    $id = $_POST['id'] ?? '';
    $stay_id = intval($_POST['stay_id']);
    $room_name = $_POST['room_name'];
    $room_description = $_POST['room_description'];
    $price_per_night = floatval($_POST['price_per_night']);
    $max_guests = intval($_POST['max_guests']);
    $number_of_rooms = intval($_POST['number_of_rooms']);
    $room_size = $_POST['room_size'] ?? '';
    $bed_configuration = $_POST['bed_configuration'];
    
    // Combine checked amenities with custom amenities
    $amenities_array = [];
    if (isset($_POST['amenities_check']) && is_array($_POST['amenities_check'])) {
        $amenities_array = $_POST['amenities_check'];
    }
    if (!empty($_POST['amenities_custom'])) {
        $custom_amenities = array_map('trim', explode(',', $_POST['amenities_custom']));
        $amenities_array = array_merge($amenities_array, $custom_amenities);
    }
    $amenities = implode(', ', array_unique(array_filter($amenities_array)));
    
    $is_available = isset($_POST['is_available']) ? 1 : 0;
    
    // Handle multiple image uploads
    $images = [];
    if (isset($_FILES['images']) && !empty($_FILES['images']['name'][0])) {
        $upload_dir = '../images/rooms/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }
        
        foreach ($_FILES['images']['tmp_name'] as $key => $tmp_name) {
            if ($_FILES['images']['error'][$key] === UPLOAD_ERR_OK) {
                $filename = time() . '_' . $key . '_' . basename($_FILES['images']['name'][$key]);
                $filepath = $upload_dir . $filename;
                
                if (move_uploaded_file($tmp_name, $filepath)) {
                    $images[] = $filename;
                }
            }
        }
    }
    
    if ($id) {
        // Update existing
        if (!empty($images)) {
            $images_str = implode(',', $images);
            $stmt = $pdo->prepare("UPDATE room_types SET stay_id=?, room_name=?, room_description=?, price_per_night=?, max_guests=?, number_of_rooms=?, room_size=?, bed_configuration=?, amenities=?, images=?, is_available=? WHERE id=?");
            $stmt->execute([$stay_id, $room_name, $room_description, $price_per_night, $max_guests, $number_of_rooms, $room_size, $bed_configuration, $amenities, $images_str, $is_available, $id]);
        } else {
            $stmt = $pdo->prepare("UPDATE room_types SET stay_id=?, room_name=?, room_description=?, price_per_night=?, max_guests=?, number_of_rooms=?, room_size=?, bed_configuration=?, amenities=?, is_available=? WHERE id=?");
            $stmt->execute([$stay_id, $room_name, $room_description, $price_per_night, $max_guests, $number_of_rooms, $room_size, $bed_configuration, $amenities, $is_available, $id]);
        }
    } else {
        // Insert new
        $max = $pdo->query("SELECT MAX(sort_order) FROM room_types WHERE stay_id = $stay_id")->fetchColumn();
        $next_sort = $max ? $max + 1 : 1;
        $images_str = !empty($images) ? implode(',', $images) : '';
        
        $stmt = $pdo->prepare("INSERT INTO room_types (stay_id, room_name, room_description, price_per_night, max_guests, number_of_rooms, room_size, bed_configuration, amenities, images, is_available, sort_order) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$stay_id, $room_name, $room_description, $price_per_night, $max_guests, $number_of_rooms, $room_size, $bed_configuration, $amenities, $images_str, $is_available, $next_sort]);
    }
    
    redirect('manage_rooms.php?stay_id=' . $stay_id);
}

// Delete room type
if (isset($_GET['delete'])) {
    $stmt = $pdo->prepare("DELETE FROM room_types WHERE id=?");
    $stmt->execute([intval($_GET['delete'])]);
    redirect('manage_rooms.php?stay_id=' . $selected_stay_id);
}

// Fetch room types for selected property
$room_types = [];
if ($selected_stay_id) {
    $stmt = $pdo->prepare("SELECT * FROM room_types WHERE stay_id = ? ORDER BY sort_order ASC");
    $stmt->execute([$selected_stay_id]);
    $room_types = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Edit data
$edit_data = null;
if (isset($_GET['edit'])) {
    $stmt = $pdo->prepare("SELECT * FROM room_types WHERE id=?");
    $stmt->execute([intval($_GET['edit'])]);
    $edit_data = $stmt->fetch(PDO::FETCH_ASSOC);
}

function get_room_image_path($image) {
    if (empty($image)) {
        return '../images/placeholder.jpg';
    }
    return '../images/rooms/' . $image;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Manage Room Types - Admin</title>
<link rel="stylesheet" href="../assets/admin.css">
<style>
:root {
    --primary: #2563eb;
    --success: #10b981;
    --warning: #f59e0b;
}

body {
    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
    background: #f3f4f6;
    margin: 0;
    padding: 20px;
}

.admin-container {
    max-width: 1600px;
    margin: 0 auto;
}

.top-bar {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 2rem;
    flex-wrap: wrap;
    gap: 1rem;
}

.back-btn {
    padding: 0.75rem 1.5rem;
    background: #6b7280;
    color: white;
    border-radius: 8px;
    text-decoration: none;
    font-weight: 600;
}

.property-selector {
    display: flex;
    align-items: center;
    gap: 1rem;
}

.property-selector label {
    font-weight: 600;
    color: #374151;
}

.property-selector select {
    padding: 0.75rem 1rem;
    border: 1px solid #d1d5db;
    border-radius: 8px;
    font-size: 1rem;
    min-width: 250px;
}

.info-banner {
    background: linear-gradient(135deg, #eff6ff 0%, #dbeafe 100%);
    border-left: 4px solid var(--primary);
    padding: 1.5rem;
    border-radius: 8px;
    margin-bottom: 2rem;
}

.info-banner h3 {
    margin: 0 0 0.5rem 0;
    color: var(--primary);
}

.info-banner p {
    margin: 0;
    color: #1e40af;
    line-height: 1.6;
}

.add-room-btn {
    padding: 0.75rem 1.5rem;
    background: var(--success);
    color: white;
    border: none;
    border-radius: 8px;
    font-weight: 600;
    cursor: pointer;
    text-decoration: none;
    display: inline-block;
}

.form-card {
    background: white;
    padding: 2rem;
    border-radius: 12px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.1);
    margin-bottom: 2rem;
}

.form-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 1.5rem;
}

.form-group {
    display: flex;
    flex-direction: column;
}

.form-group.full-width {
    grid-column: 1 / -1;
}

label {
    font-weight: 600;
    color: #374151;
    margin-bottom: 0.5rem;
    font-size: 0.9rem;
}

input, select, textarea {
    padding: 0.75rem;
    border: 1px solid #d1d5db;
    border-radius: 8px;
    font-size: 1rem;
}

input:focus, select:focus, textarea:focus {
    outline: none;
    border-color: var(--primary);
    box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
}

.amenities-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
    gap: 0.75rem;
    padding: 1rem;
    background: #f9fafb;
    border-radius: 8px;
    max-height: 300px;
    overflow-y: auto;
}

.amenity-checkbox {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.5rem 0.75rem;
    background: white;
    border: 1px solid #e5e7eb;
    border-radius: 6px;
    cursor: pointer;
    transition: all 0.2s;
    margin: 0;
}

.amenity-checkbox:hover {
    border-color: var(--primary);
    background: #eff6ff;
}

.amenity-checkbox input[type="checkbox"] {
    width: 18px;
    height: 18px;
    cursor: pointer;
}

.amenity-checkbox input[type="checkbox"]:checked + span {
    color: var(--primary);
    font-weight: 600;
}

.amenity-checkbox span {
    font-size: 0.9rem;
    color: #374151;
    font-weight: 400;
}

.checkbox-group {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    padding: 1rem;
    background: #f9fafb;
    border-radius: 8px;
}

.submit-btn {
    background: var(--primary);
    color: white;
    padding: 1rem 2rem;
    border: none;
    border-radius: 8px;
    font-size: 1rem;
    font-weight: 600;
    cursor: pointer;
    margin-top: 1rem;
}

.submit-btn:hover {
    background: #1d4ed8;
}

.rooms-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
    gap: 1.5rem;
}

.room-card {
    background: white;
    border-radius: 12px;
    overflow: hidden;
    box-shadow: 0 1px 3px rgba(0,0,0,0.1);
    transition: transform 0.2s;
}

.room-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
}

.room-image {
    width: 100%;
    height: 200px;
    object-fit: cover;
    background: #f3f4f6;
}

.room-content {
    padding: 1.5rem;
}

.room-header {
    display: flex;
    justify-content: space-between;
    align-items: start;
    margin-bottom: 1rem;
}

.room-name {
    font-size: 1.25rem;
    font-weight: 700;
    color: #1f2937;
    margin: 0;
}

.room-price {
    font-size: 1.5rem;
    font-weight: 700;
    color: var(--success);
}

.room-details {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 0.75rem;
    margin: 1rem 0;
    padding: 1rem;
    background: #f9fafb;
    border-radius: 8px;
}

.detail-item {
    font-size: 0.875rem;
}

.detail-label {
    color: #6b7280;
    display: block;
}

.detail-value {
    color: #1f2937;
    font-weight: 600;
}

.room-amenities {
    margin: 1rem 0;
}

.amenity-tags {
    display: flex;
    flex-wrap: wrap;
    gap: 0.5rem;
}

.amenity-tag {
    padding: 0.35rem 0.75rem;
    background: #e0e7ff;
    color: #3730a3;
    border-radius: 8px;
    font-size: 0.8rem;
    font-weight: 500;
}

.room-actions {
    display: flex;
    gap: 0.5rem;
    margin-top: 1rem;
}

.btn-edit, .btn-delete {
    flex: 1;
    padding: 0.625rem 1rem;
    border-radius: 6px;
    text-decoration: none;
    text-align: center;
    font-size: 0.875rem;
    font-weight: 500;
}

.btn-edit {
    background: #dbeafe;
    color: #1e40af;
}

.btn-delete {
    background: #fee2e2;
    color: #991b1b;
}

.availability-badge {
    display: inline-block;
    padding: 0.35rem 0.75rem;
    border-radius: 20px;
    font-size: 0.75rem;
    font-weight: 600;
    text-transform: uppercase;
}

.available {
    background: #d1fae5;
    color: #065f46;
}

.unavailable {
    background: #fee2e2;
    color: #991b1b;
}

.empty-state {
    text-align: center;
    padding: 4rem 2rem;
    background: white;
    border-radius: 12px;
}

.empty-state svg {
    width: 64px;
    height: 64px;
    color: #d1d5db;
    margin-bottom: 1rem;
}

@media (max-width: 768px) {
    .form-grid {
        grid-template-columns: 1fr;
    }
    
    .rooms-grid {
        grid-template-columns: 1fr;
    }
    
    .room-details {
        grid-template-columns: 1fr;
    }
    
    .amenities-grid {
        grid-template-columns: 1fr;
    }
}
</style>
</head>
<body>

<div class="admin-container">
    <div class="top-bar">
        <div>
            <h1>🛏️ Manage Room Types</h1>
            <a href="dashboard.php" class="back-btn">← Back to Dashboard</a>
        </div>
        
        <div class="property-selector">
            <label for="property_select">Select Property:</label>
            <select id="property_select" onchange="window.location.href='manage_rooms.php?stay_id='+this.value">
                <?php if (empty($partner_properties)): ?>
                    <option value="">No partner properties available</option>
                <?php else: ?>
                    <?php foreach ($partner_properties as $prop): ?>
                        <option value="<?= $prop['id'] ?>" <?= $prop['id'] == $selected_stay_id ? 'selected' : '' ?>>
                            <?= htmlspecialchars($prop['name']) ?>
                        </option>
                    <?php endforeach; ?>
                <?php endif; ?>
            </select>
        </div>
    </div>

    <?php if (empty($partner_properties)): ?>
        <div class="info-banner">
            <h3>⚠️ No Partner Properties Available</h3>
            <p>You need to create partner properties first. Go to <a href="edit_stay.php" style="color: var(--primary); font-weight: 600;">Accommodation Properties</a> and enable "Partner Accommodation" for properties that should offer instant bookings.</p>
        </div>
    <?php else: ?>
    
    <div class="info-banner">
        <h3>💡 About Room Types</h3>
        <p>
            <strong>Room Types</strong> allow partner properties to offer multiple accommodation options at different price points. 
            Each room type can have its own pricing, capacity, bed configuration, and amenities. 
            Guests will be able to select their preferred room type when making a booking.
        </p>
    </div>

    <?php if (isset($_GET['edit']) || isset($_GET['new'])): ?>
    <div class="form-card">
        <h2><?= isset($_GET['edit']) ? 'Edit Room Type' : 'Add New Room Type' ?></h2>
        <form method="POST" enctype="multipart/form-data">
            <input type="hidden" name="id" value="<?= $edit_data['id'] ?? '' ?>">
            <input type="hidden" name="stay_id" value="<?= $selected_stay_id ?>">
            
            <div class="form-grid">
                <div class="form-group">
                    <label>Room Name *</label>
                    <input type="text" name="room_name" required value="<?= htmlspecialchars($edit_data['room_name'] ?? '') ?>" placeholder="e.g., Standard Room, Deluxe Suite">
                </div>
                
                <div class="form-group">
                    <label>Price per Night (ZAR) *</label>
                    <input type="number" name="price_per_night" step="0.01" min="0" required value="<?= htmlspecialchars($edit_data['price_per_night'] ?? '') ?>">
                </div>
                
                <div class="form-group">
                    <label>Maximum Guests *</label>
                    <input type="number" name="max_guests" min="1" required value="<?= htmlspecialchars($edit_data['max_guests'] ?? 2) ?>">
                </div>
                
                <div class="form-group">
                    <label>Number of Rooms Available *</label>
                    <input type="number" name="number_of_rooms" min="1" required value="<?= htmlspecialchars($edit_data['number_of_rooms'] ?? 1) ?>">
                </div>
                
                <div class="form-group">
                    <label>Room Size</label>
                    <input type="text" name="room_size" value="<?= htmlspecialchars($edit_data['room_size'] ?? '') ?>" placeholder="e.g., 25 sqm">
                </div>
                
                <div class="form-group">
                    <label>Bed Configuration *</label>
                    <input type="text" name="bed_configuration" required value="<?= htmlspecialchars($edit_data['bed_configuration'] ?? '') ?>" placeholder="e.g., 1 King Bed, 2 Single Beds">
                </div>
                
                <div class="form-group full-width">
                    <label>Description</label>
                    <textarea name="room_description" rows="4"><?= htmlspecialchars($edit_data['room_description'] ?? '') ?></textarea>
                </div>
                
                <div class="form-group full-width">
                    <label>Select Amenities</label>
                    <div class="amenities-grid">
                        <?php
                        $common_amenities = [
                            'WiFi', 'TV', 'Air Conditioning', 'Mini Bar', 'Balcony', 
                            'Ocean View', 'Mountain View', 'Kitchenette', 'Safe', 
                            'Coffee Maker', 'Hair Dryer', 'Iron & Ironing Board', 
                            'Work Desk', 'Seating Area', 'Bathtub', 'Shower', 
                            'Toiletries', 'Towels', 'Slippers', 'Bathrobes',
                            'Room Service', 'Daily Housekeeping', 'Fireplace', 'Soundproofing'
                        ];
                        $selected_amenities = !empty($edit_data['amenities']) ? array_map('trim', explode(',', $edit_data['amenities'])) : [];
                        
                        foreach ($common_amenities as $amenity):
                            $checked = in_array($amenity, $selected_amenities) ? 'checked' : '';
                        ?>
                            <label class="amenity-checkbox">
                                <input type="checkbox" name="amenities_check[]" value="<?= htmlspecialchars($amenity) ?>" <?= $checked ?>>
                                <span><?= htmlspecialchars($amenity) ?></span>
                            </label>
                        <?php endforeach; ?>
                    </div>
                </div>
                
                <div class="form-group full-width">
                    <label>Additional Amenities (comma-separated)</label>
                    <input type="text" name="amenities_custom" id="amenities_custom" value="" placeholder="Add any amenities not listed above, separated by commas">
                    <small style="color: #6b7280; margin-top: 0.5rem; display: block;">Add amenities that aren't in the list above</small>
                </div>
                
                <div class="form-group full-width">
                    <label>Room Images (Multiple)</label>
                    <input type="file" name="images[]" accept="image/*" multiple>
                    <?php if (!empty($edit_data['images'])): ?>
                        <small style="color: #6b7280; margin-top: 0.5rem; display: block;">Current images will be replaced if new images are uploaded</small>
                    <?php endif; ?>
                </div>
                
                <div class="form-group">
                    <div class="checkbox-group">
                        <input type="checkbox" name="is_available" id="is_available" value="1" <?= ($edit_data['is_available'] ?? 1) ? 'checked' : '' ?>>
                        <label for="is_available" style="margin: 0; cursor: pointer;">Available for Booking</label>
                    </div>
                </div>
            </div>
            
            <button type="submit" class="submit-btn">
                <?= isset($_GET['edit']) ? '💾 Update Room Type' : '➕ Add Room Type' ?>
            </button>
        </form>
    </div>
    <?php else: ?>
    
    <div style="text-align: right; margin-bottom: 2rem;">
        <a href="?stay_id=<?= $selected_stay_id ?>&new=1" class="add-room-btn">+ Add Room Type</a>
    </div>
    
    <?php if (empty($room_types)): ?>
        <div class="empty-state">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path>
                <polyline points="9 22 9 12 15 12 15 22"></polyline>
            </svg>
            <h3>No Room Types Yet</h3>
            <p>Add different room types for this property to enable bookings with varying price points.</p>
            <a href="?stay_id=<?= $selected_stay_id ?>&new=1" class="add-room-btn" style="margin-top: 1rem;">Add Your First Room Type</a>
        </div>
    <?php else: ?>
    
    <div class="rooms-grid">
        <?php foreach ($room_types as $room): 
            $images = !empty($room['images']) ? explode(',', $room['images']) : [];
            $first_image = !empty($images) ? $images[0] : '';
        ?>
        <div class="room-card">
            <img src="<?= htmlspecialchars(get_room_image_path($first_image)) ?>" alt="<?= htmlspecialchars($room['room_name']) ?>" class="room-image">
            
            <div class="room-content">
                <div class="room-header">
                    <div>
                        <h3 class="room-name"><?= htmlspecialchars($room['room_name']) ?></h3>
                        <span class="availability-badge <?= $room['is_available'] ? 'available' : 'unavailable' ?>">
                            <?= $room['is_available'] ? 'Available' : 'Unavailable' ?>
                        </span>
                    </div>
                    <div class="room-price">
                        R <?= number_format($room['price_per_night'], 2) ?>
                        <small style="display: block; font-size: 0.7rem; font-weight: normal; color: #6b7280;">per night</small>
                    </div>
                </div>
                
                <?php if (!empty($room['room_description'])): ?>
                <p style="color: #6b7280; font-size: 0.9rem; margin: 0.75rem 0;">
                    <?= htmlspecialchars(substr($room['room_description'], 0, 100)) ?><?= strlen($room['room_description']) > 100 ? '...' : '' ?>
                </p>
                <?php endif; ?>
                
                <div class="room-details">
                    <div class="detail-item">
                        <span class="detail-label">Max Guests</span>
                        <span class="detail-value"><?= $room['max_guests'] ?> guests</span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">Rooms Available</span>
                        <span class="detail-value"><?= $room['number_of_rooms'] ?></span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">Bed Type</span>
                        <span class="detail-value"><?= htmlspecialchars($room['bed_configuration']) ?></span>
                    </div>
                    <?php if (!empty($room['room_size'])): ?>
                    <div class="detail-item">
                        <span class="detail-label">Size</span>
                        <span class="detail-value"><?= htmlspecialchars($room['room_size']) ?></span>
                    </div>
                    <?php endif; ?>
                </div>
                
                <?php if (!empty($room['amenities'])): ?>
                <div class="room-amenities">
                    <div class="amenity-tags">
                        <?php 
                        $amenities = array_slice(array_map('trim', explode(',', $room['amenities'])), 0, 4);
                        foreach ($amenities as $amenity): 
                        ?>
                            <span class="amenity-tag"><?= htmlspecialchars($amenity) ?></span>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>
                
                <div class="room-actions">
                    <a href="?stay_id=<?= $selected_stay_id ?>&edit=<?= $room['id'] ?>" class="btn-edit">Edit</a>
                    <a href="?stay_id=<?= $selected_stay_id ?>&delete=<?= $room['id'] ?>" class="btn-delete" onclick="return confirm('Delete this room type?')">Delete</a>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    
    <?php endif; ?>
    <?php endif; ?>
    <?php endif; ?>
</div>

</body>
</html>