<?php
ini_set('display_errors', 0);
error_reporting(E_ALL);

session_start();
if (!isset($_SESSION['admin'])) { header("Location: login.php"); exit; }

require '../includes/config.php';
require '../includes/functions.php';

// === Handle manual save of order ===
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_order'])) {
    $order = json_decode($_POST['order_data'], true);
    if (is_array($order)) {
        foreach ($order as $index => $id) {
            $stmt = $pdo->prepare("UPDATE activities SET sort_order = ? WHERE id = ?");
            $stmt->execute([$index, intval($id)]);
        }
        $success_message = "Order saved successfully!";
    }
}

// === Default form data ===
$edit_data = [
    'id'=>'', 'name'=>'', 'description'=>'', 'start_date'=>'', 'end_date'=>'',
    'venue'=>'', 'category'=>'', 'season'=>'', 'contact'=>'', 'image'=>'', 'featured'=>0
];

// === Editing existing ===
if (isset($_GET['edit'])) {
    $id = intval($_GET['edit']);
    $stmt = $pdo->prepare("SELECT * FROM activities WHERE id=?");
    $stmt->execute([$id]);
    $edit_data = $stmt->fetch(PDO::FETCH_ASSOC) ?: $edit_data;
}

// === Adding new ===
if (isset($_GET['new'])) {
    $edit_data = ['id'=>'', 'name'=>'', 'description'=>'', 'start_date'=>'', 'end_date'=>'',
                  'venue'=>'', 'category'=>'', 'season'=>'', 'contact'=>'', 'image'=>'', 'featured'=>0];
}

// === Handle form submit ===
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !isset($_POST['save_order'])) {
    $id = $_POST['id'] ?? '';
    $name = trim($_POST['name'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $start_date = $_POST['start_date'] ?? '';
    $end_date = $_POST['end_date'] ?? '';
    $venue = trim($_POST['venue'] ?? '');
    $category = trim($_POST['category'] ?? '');
    $season = trim($_POST['season'] ?? '');
    $contact = trim($_POST['contact'] ?? '');
    $featured = isset($_POST['featured']) ? 1 : 0;

    $image = upload_image('image', '../images/activities/');

    if ($id) {
        if ($image) {
            $stmt = $pdo->prepare("UPDATE activities 
                SET name=?, description=?, start_date=?, end_date=?, venue=?, category=?, season=?, contact=?, image=?, featured=? 
                WHERE id=?");
            $stmt->execute([$name,$description,$start_date,$end_date,$venue,$category,$season,$contact,$image,$featured,$id]);
        } else {
            $stmt = $pdo->prepare("UPDATE activities 
                SET name=?, description=?, start_date=?, end_date=?, venue=?, category=?, season=?, contact=?, featured=? 
                WHERE id=?");
            $stmt->execute([$name,$description,$start_date,$end_date,$venue,$category,$season,$contact,$featured,$id]);
        }
    } else {
        $max_order = $pdo->query("SELECT MAX(sort_order) FROM activities")->fetchColumn();
        $sort_order = ($max_order !== false ? intval($max_order)+1 : 0);
        $stmt = $pdo->prepare("INSERT INTO activities 
            (name, description, start_date, end_date, venue, category, season, contact, image, featured, sort_order) 
            VALUES (?,?,?,?,?,?,?,?,?,?,?)");
        $stmt->execute([$name,$description,$start_date,$end_date,$venue,$category,$season,$contact,$image,$featured,$sort_order]);
    }

    redirect('edit_things-to-do.php');
}

// === Delete ===
if (isset($_GET['delete'])) {
    $stmt = $pdo->prepare("DELETE FROM activities WHERE id=?");
    $stmt->execute([intval($_GET['delete'])]);
    redirect('edit_things-to-do.php');
}

// === Fetch all ordered by sort_order ===
$rows = $pdo->query("SELECT * FROM activities ORDER BY sort_order ASC")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Manage Activities - Admin</title>
<link rel="stylesheet" href="../assets/admin.css">
</head>
<body>

<h1>Manage Activities</h1>
<a href="dashboard.php" class="back-btn">← Back to Dashboard</a>

<?php if (isset($success_message)): ?>
<div class="success-message"><?= $success_message ?></div>
<?php endif; ?>

<form method="post" enctype="multipart/form-data">
  <input type="hidden" name="id" value="<?= htmlspecialchars($edit_data['id'] ?? '') ?>">

  <label>Name: <span style="color:red;">*</span></label>
  <input type="text" name="name" required value="<?= htmlspecialchars($edit_data['name'] ?? '') ?>">

  <label>Category: <span style="color:red;">*</span></label>
  <input type="text" name="category" required value="<?= htmlspecialchars($edit_data['category'] ?? '') ?>">

  <label>Venue:</label>
  <input type="text" name="venue" value="<?= htmlspecialchars($edit_data['venue'] ?? '') ?>">

  <label>Season:</label>
  <input type="text" name="season" placeholder="e.g. Summer, Winter, Year-round" value="<?= htmlspecialchars($edit_data['season'] ?? '') ?>">

  <label>Contact:</label>
  <input type="text" name="contact" placeholder="Phone, email, or website" value="<?= htmlspecialchars($edit_data['contact'] ?? '') ?>">

  <label>Description:</label>
  <textarea name="description" class="tinymce"><?= htmlspecialchars($edit_data['description'] ?? '') ?></textarea>

  <label>Start Date:</label>
  <input type="date" name="start_date" value="<?= htmlspecialchars($edit_data['start_date'] ?? '') ?>">

  <label>End Date:</label>
  <input type="date" name="end_date" value="<?= htmlspecialchars($edit_data['end_date'] ?? '') ?>">

  <?php if (!empty($edit_data['image'])): ?>
    <p>Current Image:</p>
    <img src="../images/activities/<?= htmlspecialchars(basename($edit_data['image'])) ?>" class="thumb" alt="Current image">
  <?php endif; ?>

  <label>Image:</label>
  <input type="file" name="image" accept="image/*">

  <label><input type="checkbox" name="featured" <?= !empty($edit_data['featured']) ? 'checked' : '' ?>> Mark as Featured</label>

  <button type="submit"><?= $edit_data['id'] ? 'Update' : 'Add' ?> Activity</button>
</form>

<button id="saveOrderBtn">💾 Save Order</button>

<table>
<thead>
<tr>
  <th>Order</th>
  <th>Image</th>
  <th>Name</th>
  <th>Category</th>
  <th>Start</th>
  <th>End</th>
  <th>Venue</th>
  <th>Featured</th>
  <th>Actions</th>
</tr>
</thead>
<tbody id="sortable">
<?php foreach ($rows as $r): ?>
<tr data-id="<?= $r['id'] ?>">
  <td class="drag-handle">☰</td>
  <td>
    <?php if (!empty($r['image'])): ?>
      <img src="../images/activities/<?= htmlspecialchars(basename($r['image'])) ?>" class="thumb" alt="<?= htmlspecialchars($r['name']) ?>">
    <?php else: ?>
      <img src="../images/placeholder.jpg" class="thumb" alt="No image">
    <?php endif; ?>
  </td>
  <td><?= htmlspecialchars($r['name'] ?? '') ?></td>
  <td><?= htmlspecialchars($r['category'] ?? '') ?></td>
  <td><?= htmlspecialchars($r['start_date'] ?? '') ?></td>
  <td><?= htmlspecialchars($r['end_date'] ?? '') ?></td>
  <td><?= htmlspecialchars($r['venue'] ?? '') ?></td>
  <td><?= $r['featured'] ? '⭐ Yes' : '—' ?></td>
  <td>
    <a href="?edit=<?= $r['id'] ?>" class="edit">Edit</a>
    <a href="?delete=<?= $r['id'] ?>" class="del" onclick="return confirm('Delete this activity?')">Delete</a>
  </td>
</tr>
<?php endforeach; ?>
</tbody>
</table>

<form id="saveOrderForm" method="POST" style="display:none;">
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

window.addEventListener('beforeunload', function(e) {
    if (hasChanges) {
        e.preventDefault();
        e.returnValue = '';
    }
});
</script>

</body>
</html>
