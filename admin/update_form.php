<?php
session_start();
if (!isset($_SESSION['admin'])) {
    header("Location: login.php");
    exit;
}

require '../includes/config.php';

// Get parameters
$page = $_GET['page'] ?? '';
$id = (int)($_GET['id'] ?? 0);

if (!$page || !$id) {
    die("Invalid request");
}

// Map pages to tables
$tables = [
    'dining' => 'dining',
    'attractions' => 'attractions',
    'stay' => 'stays',
    'things-to-do' => 'activities'
];

if (!isset($tables[$page])) {
    die("Unknown page type");
}

$table = $tables[$page];

// Fetch item
$stmt = $pdo->prepare("SELECT * FROM `$table` WHERE id = ?");
$stmt->execute([$id]);
$item = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$item) die("Item not found");

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'] ?? '';
    $description = $_POST['description'] ?? '';
    $location = $_POST['location'] ?? '';
    $contact = $_POST['contact'] ?? '';
    $display_order = (int)($_POST['display_order'] ?? 0);

    // Handle image upload if provided
    $image = $item['image'];
    if (!empty($_FILES['image']['name'])) {
        $targetDir = "../images/{$page}/";
        if (!file_exists($targetDir)) mkdir($targetDir, 0755, true);
        $filename = basename($_FILES['image']['name']);
        $targetFile = $targetDir . $filename;
        if (move_uploaded_file($_FILES['image']['tmp_name'], $targetFile)) {
            $image = "images/{$page}/" . $filename;
        }
    }

    $updateSQL = "UPDATE `$table` SET name=?, description=?, location=?, contact=?, image=?, display_order=? WHERE id=?";
    $stmt = $pdo->prepare($updateSQL);
    $stmt->execute([$name, $description, $location, $contact, $image, $display_order, $id]);

    header("Location: edit_{$page}.php?success=1");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Edit <?= htmlspecialchars($page) ?></title>
<link rel="stylesheet" href="../assets/admin.css">
<style>
    .form-container { max-width: 600px; margin: 40px auto; background:#fff; padding:20px; border-radius:12px; box-shadow:0 4px 12px rgba(0,0,0,0.1);}
    .form-container h2 { margin-bottom:20px; color:#2E6F3A;}
    .form-group { margin-bottom:15px; }
    .form-group label { display:block; margin-bottom:5px; font-weight:bold; }
    .form-group input, .form-group textarea { width:100%; padding:10px; border-radius:6px; border:1px solid #ccc; }
    .btn { padding:10px 20px; background:#C59D5F; color:#fff; border:none; border-radius:8px; cursor:pointer; margin-right:10px; font-weight:bold;}
    .btn:hover { background:#b48a4d; }
    .back-btn { background:#6B4C3B; }
    .back-btn:hover { background:#5a3f33; }
</style>
</head>
<body>
<div class="form-container">
    <h2>Edit <?= htmlspecialchars($page) ?> Item</h2>
    <form method="post" enctype="multipart/form-data">
        <div class="form-group">
            <label>Name</label>
            <input type="text" name="name" value="<?= htmlspecialchars($item['name']) ?>" required>
        </div>
        <?php if (isset($item['description'])): ?>
        <div class="form-group">
            <label>Description</label>
            <textarea name="description" rows="4"><?= htmlspecialchars($item['description']) ?></textarea>
        </div>
        <?php endif; ?>
        <?php if (isset($item['location'])): ?>
        <div class="form-group">
            <label>Location</label>
            <input type="text" name="location" value="<?= htmlspecialchars($item['location']) ?>">
        </div>
        <?php endif; ?>
        <?php if (isset($item['contact'])): ?>
        <div class="form-group">
            <label>Contact</label>
            <input type="text" name="contact" value="<?= htmlspecialchars($item['contact']) ?>">
        </div>
        <?php endif; ?>
        <div class="form-group">
            <label>Display Order</label>
            <input type="number" name="display_order" value="<?= $item['display_order'] ?? 0 ?>">
        </div>
        <div class="form-group">
            <label>Image (optional)</label>
            <input type="file" name="image">
            <?php if (!empty($item['image'])): ?>
                <img src="../<?= $item['image'] ?>" style="max-width:150px; margin-top:10px;">
            <?php endif; ?>
        </div>
        <button type="submit" class="btn">Update</button>
        <a href="edit_<?= $page ?>.php" class="btn back-btn">Back to Dashboard</a>
    </form>
</div>
</body>
</html>
