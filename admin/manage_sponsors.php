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
            $stmt = $pdo->prepare("UPDATE sponsors SET display_order = ? WHERE id = ?");
            $stmt->execute([$index, intval($id)]);
        }
        $success_message = "Order saved successfully!";
    }
}

$edit_data = [
    'id' => '',
    'name' => '',
    'logo' => '',
    'website' => '',
    'description' => '',
    'visible' => 1
];

// Editing existing
if (isset($_GET['edit'])) {
    $id = intval($_GET['edit']);
    $stmt = $pdo->prepare("SELECT * FROM sponsors WHERE id=?");
    $stmt->execute([$id]);
    $edit_data = $stmt->fetch(PDO::FETCH_ASSOC) ?: $edit_data;
}

// Adding new
if (isset($_GET['new'])) {
    $edit_data = [
        'id' => '',
        'name' => '',
        'logo' => '',
        'website' => '',
        'description' => '',
        'visible' => 1
    ];
}

// Handle form submit
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !isset($_POST['save_order'])) {
    $id = $_POST['id'] ?? '';
    $name = $_POST['name'];
    $website = $_POST['website'];
    $description = $_POST['description'];
    $visible = isset($_POST['visible']) ? 1 : 0;

    // Upload logo
    $logo = upload_image('logo', '../images/sponsors/');

    if ($id) {
        if ($logo) {
            $filename = basename($logo);
            $stmt = $pdo->prepare("UPDATE sponsors SET name=?, logo=?, website=?, description=?, visible=? WHERE id=?");
            $stmt->execute([$name, $filename, $website, $description, $visible, $id]);
        } else {
            $stmt = $pdo->prepare("UPDATE sponsors SET name=?, website=?, description=?, visible=? WHERE id=?");
            $stmt->execute([$name, $website, $description, $visible, $id]);
        }
    } else {
        $max = $pdo->query("SELECT MAX(display_order) FROM sponsors")->fetchColumn();
        $next_order = $max ? $max + 1 : 1;
        $filename = $logo ? basename($logo) : '';
        $stmt = $pdo->prepare("INSERT INTO sponsors (name, logo, website, description, visible, display_order) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$name, $filename, $website, $description, $visible, $next_order]);
    }

    redirect('manage_sponsors.php');
}

// Delete
if (isset($_GET['delete'])) {
    $stmt = $pdo->prepare("DELETE FROM sponsors WHERE id=?");
    $stmt->execute([intval($_GET['delete'])]);
    redirect('manage_sponsors.php');
}

// Fetch all
$rows = $pdo->query("SELECT * FROM sponsors ORDER BY display_order ASC")->fetchAll(PDO::FETCH_ASSOC);

function get_logo_path($logo) {
    if (empty($logo)) return '../images/placeholder.jpg';
    $filename = basename($logo);
    return '../images/sponsors/' . $filename;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Manage Sponsors - Admin</title>
<link rel="stylesheet" href="../assets/admin.css">
<style>
.visible-yes { color: green; font-weight: bold; }
.visible-no { color: #aaa; }
.logo-preview { max-width: 120px; max-height: 60px; object-fit: contain; }
</style>
</head>
<body>

<h1>Manage Sponsors & Partners</h1>
<a href="dashboard.php" class="back-btn">← Back to Dashboard</a>

<?php if (isset($success_message)): ?>
<div class="success-message"><?= $success_message ?></div>
<?php endif; ?>

<form method="post" enctype="multipart/form-data">
    <input type="hidden" name="id" value="<?= htmlspecialchars($edit_data['id']) ?>">

    <label>Sponsor Name:</label>
    <input type="text" name="name" required value="<?= htmlspecialchars($edit_data['name'] ?? '') ?>">

    <label>Website URL:</label>
    <input type="url" name="website" placeholder="https://example.com" value="<?= htmlspecialchars($edit_data['website'] ?? '') ?>">

    <label>Description:</label>
    <textarea name="description" rows="3"><?= htmlspecialchars($edit_data['description'] ?? '') ?></textarea>

    <label>
        <input type="checkbox" name="visible" value="1" <?= ($edit_data['visible'] ?? 1) ? 'checked' : '' ?>>
        Visible on Website
    </label>

    <?php if (!empty($edit_data['logo'])): ?>
        <p>Current Logo:</p>
        <img src="<?= htmlspecialchars(get_logo_path($edit_data['logo'])) ?>" class="thumb" alt="Current logo">
    <?php endif; ?>

    <label>Logo Image (transparent PNG recommended):</label>
    <input type="file" name="logo" accept="image/*">
    <small>Recommended size: 200x100px or similar aspect ratio</small>

    <button type="submit"><?= $edit_data['id'] ? 'Update' : 'Add' ?> Sponsor</button>
</form>

<button id="saveOrderBtn">💾 Save Display Order</button>

<table>
<thead>
<tr>
    <th>Order</th>
    <th>Logo</th>
    <th>Name</th>
    <th>Website</th>
    <th>Description</th>
    <th>Visible</th>
    <th>Actions</th>
</tr>
</thead>
<tbody id="sortable">
<?php foreach ($rows as $r): ?>
<tr data-id="<?= $r['id'] ?>">
    <td class="drag-handle">☰</td>
    <td><img src="<?= htmlspecialchars(get_logo_path($r['logo'])) ?>" class="logo-preview" alt="<?= htmlspecialchars($r['name']) ?>"></td>
    <td><strong><?= htmlspecialchars($r['name']) ?></strong></td>
    <td>
        <?php if (!empty($r['website'])): ?>
            <a href="<?= htmlspecialchars($r['website']) ?>" target="_blank">🔗 Visit</a>
        <?php else: ?>
            <span style="color: #999;">—</span>
        <?php endif; ?>
    </td>
    <td><?= htmlspecialchars(substr($r['description'] ?? '', 0, 60)) ?><?= strlen($r['description'] ?? '') > 60 ? '...' : '' ?></td>
    <td class="<?= $r['visible'] ? 'visible-yes' : 'visible-no' ?>">
        <?= $r['visible'] ? '✓ Yes' : '✗ No' ?>
    </td>
    <td>
        <a href="?edit=<?= $r['id'] ?>" class="edit">Edit</a>
        <a href="?delete=<?= $r['id'] ?>" class="del" onclick="return confirm('Delete this sponsor?')">Delete</a>
    </td>
</tr>
<?php endforeach; ?>
</tbody>
</table>

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