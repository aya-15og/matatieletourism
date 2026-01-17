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

// Handle manual save of order
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_order'])) {
    $order = json_decode($_POST['order_data'], true);
    if (is_array($order)) {
        foreach ($order as $index => $id) {
            $stmt = $pdo->prepare("UPDATE stays SET sort_order = ? WHERE id = ?");
            $stmt->execute([$index, intval($id)]);
        }
        $success_message = "Order saved successfully!";
    }
}

$edit_data = [
    'id' => '',
    'name' => '',
    'category' => '',
    'location' => '',
    'contact' => '',
    'booking_email' => '',
    'description' => '',
    'amenities' => '',
    'price_per_night' => '',
    'max_guests' => 2,
    'check_in_time' => '14:00',
    'check_out_time' => '10:00',
    'cancellation_policy' => '',
    'image' => '',
    'featured' => 0,
    'is_partner' => 0
];

// Editing existing
if (isset($_GET['edit'])) {
    $id = intval($_GET['edit']);
    $stmt = $pdo->prepare("SELECT * FROM stays WHERE id=?");
    $stmt->execute([$id]);
    $edit_data = $stmt->fetch(PDO::FETCH_ASSOC) ?: $edit_data;
}

// Adding new
if (isset($_GET['new'])) {
    $edit_data = [
        'id' => '',
        'name' => '',
        'category' => '',
        'location' => '',
        'contact' => '',
        'booking_email' => '',
        'description' => '',
        'amenities' => '',
        'price_per_night' => '',
        'max_guests' => 2,
        'check_in_time' => '14:00',
        'check_out_time' => '10:00',
        'cancellation_policy' => '',
        'image' => '',
        'featured' => 0,
        'is_partner' => 0
    ];
}

// Handle form submit
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !isset($_POST['save_order'])) {
    $id = $_POST['id'] ?? '';
    $name = $_POST['name'];
    $category = $_POST['category'];
    $location = $_POST['location'];
    $contact = $_POST['contact'];
    $booking_email = $_POST['booking_email'] ?? '';
    $description = $_POST['description'];
    $amenities = $_POST['amenities'] ?? '';
    $price_per_night = !empty($_POST['price_per_night']) ? floatval($_POST['price_per_night']) : null;
    $max_guests = intval($_POST['max_guests']);
    $check_in_time = $_POST['check_in_time'];
    $check_out_time = $_POST['check_out_time'];
    $cancellation_policy = $_POST['cancellation_policy'] ?? '';
    $featured = isset($_POST['featured']) ? 1 : 0;
    $is_partner = isset($_POST['is_partner']) ? 1 : 0;

    $image = upload_image('image', '../images/stays/');

    if ($id) {
        if ($image) {
            $filename = basename($image);
            $stmt = $pdo->prepare("UPDATE stays SET name=?, category=?, location=?, contact=?, booking_email=?, description=?, amenities=?, price_per_night=?, max_guests=?, check_in_time=?, check_out_time=?, cancellation_policy=?, image=?, featured=?, is_partner=? WHERE id=?");
            $stmt->execute([$name, $category, $location, $contact, $booking_email, $description, $amenities, $price_per_night, $max_guests, $check_in_time, $check_out_time, $cancellation_policy, $filename, $featured, $is_partner, $id]);
        } else {
            $stmt = $pdo->prepare("UPDATE stays SET name=?, category=?, location=?, contact=?, booking_email=?, description=?, amenities=?, price_per_night=?, max_guests=?, check_in_time=?, check_out_time=?, cancellation_policy=?, featured=?, is_partner=? WHERE id=?");
            $stmt->execute([$name, $category, $location, $contact, $booking_email, $description, $amenities, $price_per_night, $max_guests, $check_in_time, $check_out_time, $cancellation_policy, $featured, $is_partner, $id]);
        }
    } else {
        $max = $pdo->query("SELECT MAX(sort_order) FROM stays")->fetchColumn();
        $next_sort = $max ? $max + 1 : 1;
        $filename = $image ? basename($image) : '';
        $stmt = $pdo->prepare("INSERT INTO stays (name, category, location, contact, booking_email, description, amenities, price_per_night, max_guests, check_in_time, check_out_time, cancellation_policy, image, featured, is_partner, sort_order) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$name, $category, $location, $contact, $booking_email, $description, $amenities, $price_per_night, $max_guests, $check_in_time, $check_out_time, $cancellation_policy, $filename, $featured, $is_partner, $next_sort]);
    }

    redirect('edit_stay.php');
}

// Delete
if (isset($_GET['delete'])) {
    $stmt = $pdo->prepare("DELETE FROM stays WHERE id=?");
    $stmt->execute([intval($_GET['delete'])]);
    redirect('edit_stay.php');
}

// Fetch all
$rows = $pdo->query("SELECT * FROM stays ORDER BY sort_order ASC")->fetchAll(PDO::FETCH_ASSOC);

function get_image_path($image) {
    if (empty($image)) {
        return '../images/placeholder.jpg';
    }
    $filename = basename($image);
    return '../images/stays/' . $filename;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Manage Accommodation - Admin</title>
<link rel="stylesheet" href="../assets/admin.css">
<style>
:root {
    --primary: #2563eb;
    --success: #10b981;
    --warning: #f59e0b;
    --danger: #ef4444;
}

body {
    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, sans-serif;
    background: #f3f4f6;
    margin: 0;
    padding: 20px;
}

.admin-container {
    max-width: 1400px;
    margin: 0 auto;
}

h1 {
    color: #1f2937;
    margin-bottom: 1.5rem;
}

.top-actions {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 2rem;
    flex-wrap: wrap;
    gap: 1rem;
}

.back-btn, .add-new-btn {
    padding: 0.75rem 1.5rem;
    border-radius: 8px;
    text-decoration: none;
    font-weight: 600;
    transition: all 0.2s;
}

.back-btn {
    background: #6b7280;
    color: white;
}

.back-btn:hover {
    background: #4b5563;
}

.add-new-btn {
    background: var(--primary);
    color: white;
}

.add-new-btn:hover {
    background: #1d4ed8;
}

.success-message {
    background: #d1fae5;
    color: #065f46;
    padding: 1rem;
    border-radius: 8px;
    margin-bottom: 1.5rem;
    border-left: 4px solid var(--success);
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

input[type="text"],
input[type="email"],
input[type="number"],
input[type="time"],
select,
textarea {
    padding: 0.75rem;
    border: 1px solid #d1d5db;
    border-radius: 8px;
    font-size: 1rem;
    transition: all 0.2s;
}

input:focus,
select:focus,
textarea:focus {
    outline: none;
    border-color: var(--primary);
    box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
}

textarea {
    min-height: 120px;
    resize: vertical;
}

.checkbox-group {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    padding: 1rem;
    background: #f9fafb;
    border-radius: 8px;
}

.checkbox-group input[type="checkbox"] {
    width: 20px;
    height: 20px;
    cursor: pointer;
}

.checkbox-group label {
    margin: 0;
    cursor: pointer;
}

.partner-badge {
    display: inline-block;
    padding: 0.25rem 0.75rem;
    background: var(--success);
    color: white;
    border-radius: 20px;
    font-size: 0.75rem;
    font-weight: 600;
    margin-left: 0.5rem;
}

.thumb {
    max-width: 200px;
    max-height: 150px;
    border-radius: 8px;
    border: 2px solid #e5e7eb;
    margin-top: 0.5rem;
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
    transition: all 0.2s;
}

.submit-btn:hover {
    background: #1d4ed8;
    transform: translateY(-1px);
}

#saveOrderBtn {
    position: fixed;
    bottom: 2rem;
    right: 2rem;
    background: var(--success);
    color: white;
    padding: 1rem 1.5rem;
    border: none;
    border-radius: 50px;
    font-weight: 600;
    cursor: pointer;
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    display: none;
    transition: all 0.3s;
}

#saveOrderBtn.show {
    display: block;
}

#saveOrderBtn:hover {
    transform: scale(1.05);
    box-shadow: 0 6px 20px rgba(0,0,0,0.2);
}

table {
    width: 100%;
    background: white;
    border-radius: 12px;
    overflow: hidden;
    box-shadow: 0 1px 3px rgba(0,0,0,0.1);
}

thead {
    background: #f9fafb;
}

th {
    padding: 1rem;
    text-align: left;
    font-weight: 600;
    color: #374151;
    border-bottom: 2px solid #e5e7eb;
}

td {
    padding: 1rem;
    border-bottom: 1px solid #f3f4f6;
}

.drag-handle {
    cursor: move;
    font-size: 1.25rem;
    color: #9ca3af;
    text-align: center;
}

.drag-handle:hover {
    color: #4b5563;
}

.featured-yes {
    color: var(--warning);
    font-weight: 600;
}

.featured-no {
    color: #9ca3af;
}

.partner-yes {
    color: var(--success);
    font-weight: 600;
}

.edit, .del {
    padding: 0.5rem 1rem;
    border-radius: 6px;
    text-decoration: none;
    font-size: 0.875rem;
    font-weight: 500;
    transition: all 0.2s;
}

.edit {
    background: #dbeafe;
    color: #1e40af;
    margin-right: 0.5rem;
}

.edit:hover {
    background: #bfdbfe;
}

.del {
    background: #fee2e2;
    color: #991b1b;
}

.del:hover {
    background: #fecaca;
}

.sortable-ghost {
    opacity: 0.4;
    background: #e0e7ff;
}

.info-box {
    background: #eff6ff;
    border-left: 4px solid var(--primary);
    padding: 1rem;
    border-radius: 8px;
    margin-bottom: 1.5rem;
}

.info-box strong {
    color: var(--primary);
}

@media (max-width: 768px) {
    .form-grid {
        grid-template-columns: 1fr;
    }
    
    table {
        font-size: 0.875rem;
    }
    
    .thumb {
        max-width: 100px;
        max-height: 75px;
    }
}
</style>
</head>
<body>

<div class="admin-container">
    <div class="top-actions">
        <div>
            <h1>🏨 Manage Accommodation</h1>
            <a href="dashboard.php" class="back-btn">← Back to Dashboard</a>
        </div>
        <a href="?new=1" class="add-new-btn">+ Add New Accommodation</a>
    </div>

    <?php if (isset($success_message)): ?>
    <div class="success-message">✓ <?= $success_message ?></div>
    <?php endif; ?>

    <?php if (isset($_GET['new']) || isset($_GET['edit'])): ?>
    <div class="form-card">
        <h2><?= $edit_data['id'] ? 'Edit Accommodation' : 'Add New Accommodation' ?></h2>
        
        <div class="info-box">
            <strong>Partner Status:</strong> Enable "Partner Accommodation" to allow instant bookings with automatic confirmation. Non-partner accommodations will receive inquiry emails only.
        </div>

        <form method="post" enctype="multipart/form-data">
            <input type="hidden" name="id" value="<?= htmlspecialchars($edit_data['id']) ?>">

            <div class="form-grid">
                <div class="form-group">
                    <label>Name *</label>
                    <input type="text" name="name" required value="<?= htmlspecialchars($edit_data['name'] ?? '') ?>">
                </div>

                <div class="form-group">
                    <label>Category *</label>
                    <select name="category" required>
                        <option value="">-- Select Category --</option>
                        <option value="hotel" <?= ($edit_data['category'] ?? '') === 'hotel' ? 'selected' : '' ?>>Hotel</option>
                        <option value="guesthouse" <?= ($edit_data['category'] ?? '') === 'guesthouse' ? 'selected' : '' ?>>Guesthouse</option>
                        <option value="bnb" <?= ($edit_data['category'] ?? '') === 'bnb' ? 'selected' : '' ?>>B&B</option>
                        <option value="cottage" <?= ($edit_data['category'] ?? '') === 'cottage' ? 'selected' : '' ?>>Cottage</option>
                        <option value="farmstay" <?= ($edit_data['category'] ?? '') === 'farmstay' ? 'selected' : '' ?>>Farmstay</option>
                        <option value="self-catering" <?= ($edit_data['category'] ?? '') === 'self-catering' ? 'selected' : '' ?>>Self-Catering</option>
                    </select>
                </div>

                <div class="form-group">
                    <label>Location</label>
                    <input type="text" name="location" value="<?= htmlspecialchars($edit_data['location'] ?? '') ?>">
                </div>

                <div class="form-group">
                    <label>Contact Number</label>
                    <input type="text" name="contact" value="<?= htmlspecialchars($edit_data['contact'] ?? '') ?>">
                </div>

                <div class="form-group">
                    <label>Booking Email</label>
                    <input type="email" name="booking_email" value="<?= htmlspecialchars($edit_data['booking_email'] ?? '') ?>">
                </div>

                <div class="form-group">
                    <label>Price per Night (ZAR)</label>
                    <input type="number" name="price_per_night" step="0.01" min="0" value="<?= htmlspecialchars($edit_data['price_per_night'] ?? '') ?>">
                </div>

                <div class="form-group">
                    <label>Max Guests</label>
                    <input type="number" name="max_guests" min="1" value="<?= htmlspecialchars($edit_data['max_guests'] ?? 2) ?>">
                </div>

                <div class="form-group">
                    <label>Check-in Time</label>
                    <input type="time" name="check_in_time" value="<?= htmlspecialchars($edit_data['check_in_time'] ?? '14:00') ?>">
                </div>

                <div class="form-group">
                    <label>Check-out Time</label>
                    <input type="time" name="check_out_time" value="<?= htmlspecialchars($edit_data['check_out_time'] ?? '10:00') ?>">
                </div>

                <div class="form-group full-width">
                    <label>Description</label>
                    <textarea name="description" class="tinymce"><?= htmlspecialchars($edit_data['description'] ?? '') ?></textarea>
                </div>

                <div class="form-group full-width">
                    <label>Amenities (comma-separated)</label>
                    <input type="text" name="amenities" placeholder="WiFi, Parking, Breakfast, Pool" value="<?= htmlspecialchars($edit_data['amenities'] ?? '') ?>">
                </div>

                <div class="form-group full-width">
                    <label>Cancellation Policy</label>
                    <textarea name="cancellation_policy" rows="3"><?= htmlspecialchars($edit_data['cancellation_policy'] ?? '') ?></textarea>
                </div>

                <?php if (!empty($edit_data['image'])): ?>
                <div class="form-group full-width">
                    <label>Current Image</label>
                    <img src="<?= htmlspecialchars(get_image_path($edit_data['image'])) ?>" class="thumb" alt="Current image">
                </div>
                <?php endif; ?>

                <div class="form-group full-width">
                    <label>Upload Image</label>
                    <input type="file" name="image" accept="image/*">
                </div>

                <div class="form-group">
                    <div class="checkbox-group">
                        <input type="checkbox" name="featured" value="1" id="featured" <?= ($edit_data['featured'] ?? 0) ? 'checked' : '' ?>>
                        <label for="featured">⭐ Featured on Homepage</label>
                    </div>
                </div>

                <div class="form-group">
                    <div class="checkbox-group">
                        <input type="checkbox" name="is_partner" value="1" id="is_partner" <?= ($edit_data['is_partner'] ?? 0) ? 'checked' : '' ?>>
                        <label for="is_partner">🤝 Partner Accommodation (Instant Booking)</label>
                    </div>
                </div>
            </div>

            <button type="submit" class="submit-btn">
                <?= $edit_data['id'] ? '💾 Update Accommodation' : '➕ Add Accommodation' ?>
            </button>
        </form>
    </div>
    <?php endif; ?>

    <button id="saveOrderBtn">💾 Save Order</button>

    <h2>All Accommodations</h2>
    <table>
        <thead>
            <tr>
                <th width="50">Order</th>
                <th width="100">Image</th>
                <th>Name</th>
                <th>Category</th>
                <th>Price/Night</th>
                <th>Partner</th>
                <th>Featured</th>
                <th width="180">Actions</th>
            </tr>
        </thead>
        <tbody id="sortable">
            <?php foreach ($rows as $r): ?>
            <tr data-id="<?= $r['id'] ?>">
                <td class="drag-handle">☰</td>
                <td><img src="<?= htmlspecialchars(get_image_path($r['image'])) ?>" class="thumb" alt="<?= htmlspecialchars($r['name']) ?>"></td>
                <td><strong><?= htmlspecialchars($r['name']) ?></strong></td>
                <td><?= ucfirst($r['category']) ?></td>
                <td><?= $r['price_per_night'] ? 'R ' . number_format($r['price_per_night'], 2) : 'N/A' ?></td>
                <td class="<?= $r['is_partner'] ? 'partner-yes' : '' ?>">
                    <?= $r['is_partner'] ? '✓ Yes' : '— No' ?>
                </td>
                <td class="<?= $r['featured'] ? 'featured-yes' : 'featured-no' ?>">
                    <?= $r['featured'] ? '⭐ Yes' : '— No' ?>
                </td>
                <td>
                    <a href="?edit=<?= $r['id'] ?>" class="edit">Edit</a>
                    <a href="?delete=<?= $r['id'] ?>" class="del" onclick="return confirm('Delete this accommodation?')">Delete</a>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<form id="saveOrderForm" method="POST" style="display: none;">
    <input type="hidden" name="save_order" value="1">
    <input type="hidden" name="order_data" id="orderData">
</form>

<?php add_tinymce(); ?>

<script src="https://cdnjs.cloudflare.com/ajax/libs/Sortable/1.15.0/Sortable.min.js"></script>
<script>
let hasChanges = false;
let tableBody = document.querySelector('#sortable');
let saveBtn = document.getElementById('saveOrderBtn');
let saveForm = document.getElementById('saveOrderForm');
let orderDataInput = document.getElementById('orderData');

let sortable = Sortable.create(tableBody, {
    handle: '.drag-handle',
    animation: 150,
    ghostClass: 'sortable-ghost',
    onEnd: function() {
        hasChanges = true;
        saveBtn.classList.add('show');
    }
});

saveBtn.addEventListener('click', function() {
    let order = [];
    tableBody.querySelectorAll('tr').forEach((tr) => order.push(tr.dataset.id));
    orderDataInput.value = JSON.stringify(order);
    saveForm.submit();
});

window.addEventListener('beforeunload', function(e) {
    if (hasChanges) {
        e.preventDefault();
        e.returnValue = '';
        return '';
    }
});
</script>

</body>
</html>