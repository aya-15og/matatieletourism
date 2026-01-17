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

$success_message = '';
$error_message = '';

// Handle manual save of order
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_order'])) {
    $order = json_decode($_POST['order_data'], true);
    if (is_array($order)) {
        foreach ($order as $index => $id) {
            $stmt = $pdo->prepare("UPDATE dining SET sort_order = ? WHERE id = ?");
            $stmt->execute([$index, intval($id)]);
        }
        $success_message = "✓ Display order saved successfully!";
    }
}

$edit_data = [
    'id' => '', 
    'name' => '', 
    'category' => 'restaurant', 
    'desc' => '', 
    'location' => '', 
    'contact' => '', 
    'hours' => '', 
    'img' => ''
];

// Editing existing
if (isset($_GET['edit'])) {
    $id = intval($_GET['edit']);
    $stmt = $pdo->prepare("SELECT * FROM dining WHERE id=?");
    $stmt->execute([$id]);
    $edit_data = $stmt->fetch(PDO::FETCH_ASSOC) ?: $edit_data;
}

// Adding new
if (isset($_GET['new'])) {
    $edit_data = [
        'id' => '', 
        'name' => '', 
        'category' => 'restaurant', 
        'desc' => '', 
        'location' => '', 
        'contact' => '', 
        'hours' => '', 
        'img' => ''
    ];
}

// Handle form submit
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !isset($_POST['save_order'])) {
    $id = $_POST['id'] ?? '';
    $name = trim($_POST['name']);
    $category = trim($_POST['category']);
    $desc = trim($_POST['desc']);
    $location = trim($_POST['location']);
    $contact = trim($_POST['contact']);
    $hours = trim($_POST['hours']);
    
    // Validate required fields
    if (empty($name) || empty($desc) || empty($location)) {
        $error_message = "✗ Name, description, and location are required!";
    } else {
        $image = upload_image('image', '../images/dining/');

        if ($id) {
            // Update existing
            if ($image) {
                $stmt = $pdo->prepare("UPDATE dining SET name=?, category=?, `desc`=?, location=?, contact=?, hours=?, img=? WHERE id=?");
                $stmt->execute([$name, $category, $desc, $location, $contact, $hours, $image, $id]);
            } else {
                $stmt = $pdo->prepare("UPDATE dining SET name=?, category=?, `desc`=?, location=?, contact=?, hours=? WHERE id=?");
                $stmt->execute([$name, $category, $desc, $location, $contact, $hours, $id]);
            }
            $success_message = "✓ Dining place updated successfully!";
        } else {
            // Insert new
            $max = $pdo->query("SELECT MAX(sort_order) FROM dining")->fetchColumn();
            $next_sort = $max ? $max + 1 : 1;
            $stmt = $pdo->prepare("INSERT INTO dining (name, category, `desc`, location, contact, hours, img, sort_order) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$name, $category, $desc, $location, $contact, $hours, $image, $next_sort]);
            $success_message = "✓ New dining place added successfully!";
        }
        
        if (empty($error_message)) {
            redirect('manage_dining.php');
        }
    }
}

// Delete
if (isset($_GET['delete'])) {
    $delete_id = intval($_GET['delete']);
    
    // Get image path before deleting
    $stmt = $pdo->prepare("SELECT img FROM dining WHERE id=?");
    $stmt->execute([$delete_id]);
    $img_data = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Delete from database
    $stmt = $pdo->prepare("DELETE FROM dining WHERE id=?");
    $stmt->execute([$delete_id]);
    
    // Delete image file if exists
    if (!empty($img_data['img']) && file_exists('../' . $img_data['img'])) {
        unlink('../' . $img_data['img']);
    }
    
    $success_message = "✓ Dining place deleted successfully!";
    redirect('manage_dining.php');
}

// Fetch all
$rows = $pdo->query("SELECT * FROM dining ORDER BY sort_order ASC")->fetchAll(PDO::FETCH_ASSOC);

// Count by category
$stats = [
    'total' => count($rows),
    'restaurant' => 0,
    'cafe' => 0,
    'fastfood' => 0,
    'traditional' => 0,
    'hotel' => 0
];

foreach ($rows as $r) {
    $cat = $r['category'] ?? 'restaurant';
    if (isset($stats[$cat])) $stats[$cat]++;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Manage Dining - Admin Panel</title>
<link rel="stylesheet" href="../assets/admin.css">
<style>
    * { margin: 0; padding: 0; box-sizing: border-box; }
    body {
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        background: #f5f7fa;
        color: #333;
    }
    
    .admin-header {
        background: linear-gradient(135deg, #DC3545 0%, #FD7E14 100%);
        color: white;
        padding: 1.5rem 2rem;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    }
    
    .admin-header h1 {
        font-size: 1.8rem;
        margin-bottom: 0.5rem;
    }
    
    .header-actions {
        display: flex;
        gap: 1rem;
        align-items: center;
        flex-wrap: wrap;
        margin-top: 1rem;
    }
    
    .btn {
        padding: 0.7rem 1.3rem;
        border-radius: 8px;
        text-decoration: none;
        font-weight: 600;
        transition: all 0.3s;
        display: inline-block;
        border: none;
        cursor: pointer;
        font-size: 0.95rem;
    }
    
    .btn-primary {
        background: #28a745;
        color: white;
    }
    .btn-primary:hover {
        background: #218838;
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(40, 167, 69, 0.3);
    }
    
    .btn-secondary {
        background: white;
        color: #DC3545;
        border: 2px solid white;
    }
    .btn-secondary:hover {
        background: rgba(255,255,255,0.9);
    }
    
    .container {
        max-width: 1400px;
        margin: 0 auto;
        padding: 2rem;
    }
    
    /* Stats Cards */
    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
        gap: 1.5rem;
        margin-bottom: 2rem;
    }
    
    .stat-card {
        background: white;
        padding: 1.5rem;
        border-radius: 12px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.05);
        border-left: 4px solid #DC3545;
        transition: transform 0.3s;
    }
    
    .stat-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 4px 15px rgba(0,0,0,0.1);
    }
    
    .stat-card h3 {
        font-size: 0.85rem;
        color: #666;
        text-transform: uppercase;
        margin-bottom: 0.5rem;
    }
    
    .stat-card .number {
        font-size: 2rem;
        font-weight: bold;
        color: #DC3545;
    }
    
    /* Messages */
    .message {
        padding: 1rem 1.5rem;
        border-radius: 8px;
        margin-bottom: 1.5rem;
        font-weight: 500;
    }
    
    .success-message {
        background: #d4edda;
        color: #155724;
        border-left: 4px solid #28a745;
    }
    
    .error-message {
        background: #f8d7da;
        color: #721c24;
        border-left: 4px solid #dc3545;
    }
    
    /* Form Section */
    .form-section {
        background: white;
        padding: 2rem;
        border-radius: 12px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.05);
        margin-bottom: 2rem;
    }
    
    .form-section h2 {
        margin-bottom: 1.5rem;
        color: #333;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }
    
    .form-section h2:before {
        content: '🍽️';
        font-size: 1.5rem;
    }
    
    .form-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
        gap: 1.5rem;
    }
    
    .form-group {
        margin-bottom: 1.5rem;
    }
    
    .form-group.full-width {
        grid-column: 1 / -1;
    }
    
    .form-group label {
        display: block;
        margin-bottom: 0.5rem;
        font-weight: 600;
        color: #555;
    }
    
    .form-group input[type="text"],
    .form-group textarea,
    .form-group select {
        width: 100%;
        padding: 0.8rem;
        border: 2px solid #e1e8ed;
        border-radius: 8px;
        font-size: 1rem;
        transition: border-color 0.3s;
        font-family: inherit;
    }
    
    .form-group input:focus,
    .form-group textarea:focus,
    .form-group select:focus {
        outline: none;
        border-color: #DC3545;
    }
    
    .form-group textarea {
        min-height: 120px;
        resize: vertical;
    }
    
    .image-preview {
        margin: 1rem 0;
        max-width: 300px;
    }
    
    .image-preview img {
        width: 100%;
        border-radius: 8px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    }
    
    /* Table Section */
    .table-section {
        background: white;
        border-radius: 12px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.05);
        overflow: hidden;
    }
    
    .table-header {
        padding: 1.5rem 2rem;
        background: linear-gradient(135deg, #DC3545 0%, #FD7E14 100%);
        color: white;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    
    .table-header h2 {
        margin: 0;
        font-size: 1.3rem;
    }
    
    .save-order-btn {
        background: #28a745;
        color: white;
        padding: 0.7rem 1.5rem;
        border: none;
        border-radius: 8px;
        cursor: pointer;
        font-weight: 600;
        opacity: 0;
        transition: all 0.3s;
    }
    
    .save-order-btn.show {
        opacity: 1;
    }
    
    .save-order-btn:hover {
        background: #218838;
        transform: scale(1.05);
    }
    
    table {
        width: 100%;
        border-collapse: collapse;
    }
    
    thead {
        background: #f8f9fa;
    }
    
    th {
        padding: 1rem;
        text-align: left;
        font-weight: 600;
        color: #555;
        border-bottom: 2px solid #e1e8ed;
    }
    
    tbody tr {
        border-bottom: 1px solid #f1f3f5;
        transition: background 0.2s;
    }
    
    tbody tr:hover {
        background: #f8f9fa;
    }
    
    td {
        padding: 1rem;
        vertical-align: middle;
    }
    
    .drag-handle {
        cursor: move;
        font-size: 1.5rem;
        color: #999;
        user-select: none;
    }
    
    .drag-handle:hover {
        color: #DC3545;
    }
    
    .thumb {
        width: 80px;
        height: 60px;
        object-fit: cover;
        border-radius: 6px;
        box-shadow: 0 2px 6px rgba(0,0,0,0.1);
    }
    
    .badge {
        display: inline-block;
        padding: 0.3rem 0.8rem;
        border-radius: 12px;
        font-size: 0.8rem;
        font-weight: 600;
    }
    
    .badge-restaurant { background: #d1ecf1; color: #0c5460; }
    .badge-cafe { background: #fff3cd; color: #856404; }
    .badge-fastfood { background: #f8d7da; color: #721c24; }
    .badge-traditional { background: #d4edda; color: #155724; }
    .badge-hotel { background: #e2e3e5; color: #383d41; }
    
    .action-btns {
        display: flex;
        gap: 0.5rem;
        flex-wrap: wrap;
    }
    
    .action-btns a {
        padding: 0.5rem 1rem;
        border-radius: 6px;
        text-decoration: none;
        font-size: 0.85rem;
        font-weight: 600;
        transition: all 0.3s;
    }
    
    .btn-edit {
        background: #007bff;
        color: white;
    }
    .btn-edit:hover {
        background: #0056b3;
    }
    
    .btn-delete {
        background: #dc3545;
        color: white;
    }
    .btn-delete:hover {
        background: #c82333;
    }
    
    .sortable-ghost {
        opacity: 0.4;
        background: #ffe5e8;
    }
    
    @media (max-width: 768px) {
        .container { padding: 1rem; }
        .stats-grid { grid-template-columns: repeat(2, 1fr); }
        .form-grid { grid-template-columns: 1fr; }
        table { font-size: 0.85rem; }
        .action-btns { flex-direction: column; }
    }
</style>
</head>
<body>

<div class="admin-header">
    <h1>🍽️ Manage Dining & Restaurants</h1>
    <div class="header-actions">
        <a href="dashboard.php" class="btn btn-secondary">← Dashboard</a>
        <a href="?new=1" class="btn btn-primary">+ Add New Restaurant</a>
    </div>
</div>

<div class="container">
    <!-- Stats -->
    <div class="stats-grid">
        <div class="stat-card">
            <h3>Total Listings</h3>
            <div class="number"><?php echo $stats['total']; ?></div>
        </div>
        <div class="stat-card">
            <h3>Restaurants</h3>
            <div class="number"><?php echo $stats['restaurant']; ?></div>
        </div>
        <div class="stat-card">
            <h3>Cafés</h3>
            <div class="number"><?php echo $stats['cafe']; ?></div>
        </div>
        <div class="stat-card">
            <h3>Fast Food</h3>
            <div class="number"><?php echo $stats['fastfood']; ?></div>
        </div>
        <div class="stat-card">
            <h3>Traditional</h3>
            <div class="number"><?php echo $stats['traditional']; ?></div>
        </div>
        <div class="stat-card">
            <h3>Hotel Dining</h3>
            <div class="number"><?php echo $stats['hotel']; ?></div>
        </div>
    </div>

    <!-- Messages -->
    <?php if ($success_message): ?>
    <div class="message success-message"><?php echo $success_message; ?></div>
    <?php endif; ?>
    
    <?php if ($error_message): ?>
    <div class="message error-message"><?php echo $error_message; ?></div>
    <?php endif; ?>

    <!-- Form -->
    <?php if (isset($_GET['edit']) || isset($_GET['new'])): ?>
    <div class="form-section">
        <h2><?php echo $edit_data['id'] ? 'Edit Dining Place' : 'Add New Dining Place'; ?></h2>
        
        <form method="post" enctype="multipart/form-data">
            <input type="hidden" name="id" value="<?php echo htmlspecialchars($edit_data['id']); ?>">
            
            <div class="form-grid">
                <div class="form-group">
                    <label>Restaurant Name *</label>
                    <input type="text" name="name" required value="<?php echo htmlspecialchars($edit_data['name'] ?? ''); ?>" placeholder="e.g., Matatiele Family Restaurant">
                </div>
                
                <div class="form-group">
                    <label>Category *</label>
                    <select name="category" required>
                        <option value="restaurant" <?php echo ($edit_data['category'] ?? '') === 'restaurant' ? 'selected' : ''; ?>>Restaurant</option>
                        <option value="cafe" <?php echo ($edit_data['category'] ?? '') === 'cafe' ? 'selected' : ''; ?>>Café</option>
                        <option value="fastfood" <?php echo ($edit_data['category'] ?? '') === 'fastfood' ? 'selected' : ''; ?>>Fast Food</option>
                        <option value="traditional" <?php echo ($edit_data['category'] ?? '') === 'traditional' ? 'selected' : ''; ?>>Traditional</option>
                        <option value="hotel" <?php echo ($edit_data['category'] ?? '') === 'hotel' ? 'selected' : ''; ?>>Hotel Dining</option>
                    </select>
                </div>
                
                <div class="form-group full-width">
                    <label>Description *</label>
                    <textarea name="desc" required placeholder="Describe the dining experience, cuisine type, specialties..."><?php echo htmlspecialchars($edit_data['desc'] ?? ''); ?></textarea>
                </div>
                
                <div class="form-group">
                    <label>Location *</label>
                    <input type="text" name="location" required value="<?php echo htmlspecialchars($edit_data['location'] ?? ''); ?>" placeholder="e.g., Main Street, Matatiele">
                </div>
                
                <div class="form-group">
                    <label>Contact</label>
                    <input type="text" name="contact" value="<?php echo htmlspecialchars($edit_data['contact'] ?? ''); ?>" placeholder="Phone number">
                </div>
                
                <div class="form-group full-width">
                    <label>Operating Hours</label>
                    <input type="text" name="hours" value="<?php echo htmlspecialchars($edit_data['hours'] ?? ''); ?>" placeholder="e.g., Mon-Fri: 8:00 AM - 10:00 PM, Sat-Sun: 9:00 AM - 11:00 PM">
                </div>
                
                <div class="form-group">
                    <?php if (!empty($edit_data['img'])): ?>
                    <div class="image-preview">
                        <p><strong>Current Image:</strong></p>
                        <img src="../<?php echo htmlspecialchars($edit_data['img']); ?>" alt="Current" id="currentImage">
                    </div>
                    <?php endif; ?>
                    
                    <label>Upload Image</label>
                    <input type="file" name="image" accept="image/*" id="imageInput">
                </div>
            </div>
            
            <div style="margin-top: 2rem; display: flex; gap: 1rem;">
                <button type="submit" class="btn btn-primary">
                    <?php echo $edit_data['id'] ? '💾 Update' : '➕ Add'; ?> Dining Place
                </button>
                <a href="manage_dining.php" class="btn" style="background: #6c757d; color: white;">Cancel</a>
            </div>
        </form>
    </div>
    <?php endif; ?>

    <!-- Table -->
    <div class="table-section">
        <div class="table-header">
            <h2>📋 All Dining Places (<?php echo count($rows); ?>)</h2>
            <button id="saveOrderBtn" class="save-order-btn">💾 Save Order</button>
        </div>
        
        <table>
            <thead>
                <tr>
                    <th style="width: 40px;">Sort</th>
                    <th style="width: 100px;">Image</th>
                    <th>Name</th>
                    <th>Category</th>
                    <th>Location</th>
                    <th>Contact</th>
                    <th style="width: 200px;">Actions</th>
                </tr>
            </thead>
            <tbody id="sortable">
                <?php foreach ($rows as $r): ?>
                <tr data-id="<?php echo $r['id']; ?>">
                    <td class="drag-handle">☰</td>
                    <td>
                        <?php if (!empty($r['img'])): ?>
                        <img src="../<?php echo htmlspecialchars($r['img']); ?>" class="thumb" alt="<?php echo htmlspecialchars($r['name']); ?>">
                        <?php else: ?>
                        <img src="../images/placeholder.jpg" class="thumb" alt="No image">
                        <?php endif; ?>
                    </td>
                    <td><strong><?php echo htmlspecialchars($r['name']); ?></strong></td>
                    <td>
                        <?php
                        $cat_badges = [
                            'restaurant' => 'badge-restaurant',
                            'cafe' => 'badge-cafe',
                            'fastfood' => 'badge-fastfood',
                            'traditional' => 'badge-traditional',
                            'hotel' => 'badge-hotel'
                        ];
                        $cat_class = $cat_badges[$r['category']] ?? 'badge-restaurant';
                        ?>
                        <span class="badge <?php echo $cat_class; ?>">
                            <?php echo ucfirst($r['category']); ?>
                        </span>
                    </td>
                    <td><?php echo htmlspecialchars(substr($r['location'] ?? 'N/A', 0, 40)); ?></td>
                    <td><?php echo htmlspecialchars($r['contact'] ?? 'N/A'); ?></td>
                    <td>
                        <div class="action-btns">
                            <a href="?edit=<?php echo $r['id']; ?>" class="btn-edit">✏️ Edit</a>
                            <a href="?delete=<?php echo $r['id']; ?>" class="btn-delete" onclick="return confirm('Are you sure you want to delete this dining place?')">🗑️ Delete</a>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<form id="saveOrderForm" method="POST" style="display: none;">
    <input type="hidden" name="save_order" value="1">
    <input type="hidden" name="order_data" id="orderData">
</form>

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
    onEnd: function () {
        hasChanges = true;
        saveBtn.classList.add('show');
    }
});

saveBtn.addEventListener('click', function() {
    let order = [];
    tableBody.querySelectorAll('tr').forEach((tr) => {
        order.push(tr.dataset.id);
    });
    
    orderDataInput.value = JSON.stringify(order);
    saveForm.submit();
});

// Image preview
const imageInput = document.getElementById('imageInput');
const currentImage = document.getElementById('currentImage');
if (imageInput && currentImage) {
    imageInput.addEventListener('change', function() {
        const file = this.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) { currentImage.src = e.target.result; }
            reader.readAsDataURL(file);
        }
    });
}

window.addEventListener('beforeunload', function(e) {
    if (hasChanges) {
        e.preventDefault();
        e.returnValue = 'You have unsaved changes. Are you sure you want to leave?';
        return e.returnValue;
    }
});
</script>

</body>
</html>