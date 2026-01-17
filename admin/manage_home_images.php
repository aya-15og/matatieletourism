<?php
session_start();
if (!isset($_SESSION['admin'])) { header("Location: login.php"); exit; }
require '../includes/config.php';

// ✅ Handle new upload
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['image'])) {
    $section = $_POST['section'];
    $title = $_POST['title'] ?? '';
    $desc = $_POST['description'] ?? '';
    $folder = "../images/home/$section/";

    if (!is_dir($folder)) mkdir($folder, 0775, true);

    $filename = basename($_FILES['image']['name']);
    $target = $folder . $filename;

    if (move_uploaded_file($_FILES['image']['tmp_name'], $target)) {
        $stmt = $pdo->prepare("INSERT INTO home_images (section, title, description, filename, visible, order_no) VALUES (?,?,?,?,1,0)");
        $stmt->execute([$section, $title, $desc, $filename]);
        $msg = "✅ Image uploaded successfully.";
    } else {
        $msg = "❌ Upload failed.";
    }
}

// ✅ Handle deletion
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $img = $pdo->query("SELECT * FROM home_images WHERE id=$id")->fetch();
    if ($img) {
        @unlink("../images/home/{$img['section']}/{$img['filename']}");
        $pdo->exec("DELETE FROM home_images WHERE id=$id");
        $msg = "🗑️ Image deleted.";
    }
}

// ✅ Handle visibility toggle
if (isset($_POST['toggle_id'])) {
    $id = (int)$_POST['toggle_id'];
    $pdo->exec("UPDATE home_images SET visible = 1 - visible WHERE id = $id");
    echo "ok";
    exit;
}

// ✅ Handle reorder
if (isset($_POST['order'])) {
    $order = $_POST['order'];
    foreach ($order as $pos => $id) {
        $stmt = $pdo->prepare("UPDATE home_images SET order_no=? WHERE id=?");
        $stmt->execute([$pos, $id]);
    }
    echo "ok";
    exit;
}

// ✅ Handle edit (title/desc)
if (isset($_POST['edit_id'])) {
    $id = (int)$_POST['edit_id'];
    $title = $_POST['title'] ?? '';
    $desc = $_POST['description'] ?? '';
    $stmt = $pdo->prepare("UPDATE home_images SET title=?, description=? WHERE id=?");
    $stmt->execute([$title, $desc, $id]);
    echo "ok";
    exit;
}

$sections = ['hero','attractions','stays','things_to_do','gallery'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Manage Homepage Images</title>
<link rel="stylesheet" href="../assets/admin.css">
<style>
.image-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
  gap: 15px;
  margin-bottom: 40px;
}
.image-card {
  border: 1px solid #ddd;
  background: #fafafa;
  border-radius: 6px;
  padding: 8px;
  text-align: center;
  cursor: move;
  box-shadow: 0 1px 2px rgba(0,0,0,0.1);
  transition: box-shadow .2s;
}
.image-card.dragging { opacity: 0.5; }
.image-card img {
  width: 100%;
  height: 120px;
  object-fit: cover;
  border-radius: 4px;
  background: #eee;
}
.caption {
  margin-top: 6px;
  font-size: 13px;
}
.toggle {
  display: inline-block;
  background: #ddd;
  border-radius: 12px;
  padding: 2px 8px;
  cursor: pointer;
  font-size: 12px;
  margin-top: 4px;
}
.toggle.active { background: #28a745; color: #fff; }
.delete, .edit {
  display: inline-block;
  margin-top: 4px;
  color: #c00;
  text-decoration: none;
  font-size: 12px;
  margin-right: 8px;
}
.edit { color: #0066cc; }
.upload-form {
  background: #fff;
  padding: 20px;
  border: 1px solid #ddd;
  border-radius: 8px;
  margin-bottom: 40px;
}
.notice {
  background: #e0ffe0;
  border: 1px solid #a0d0a0;
  padding: 10px;
  margin-bottom: 15px;
  border-radius: 5px;
}
.edit-fields {
  display: none;
  margin-top: 8px;
}
.edit-fields input, .edit-fields textarea {
  width: 100%;
  font-size: 12px;
  margin-bottom: 6px;
  padding: 5px;
}
.edit-fields button {
  background: #28a745;
  color: #fff;
  border: none;
  padding: 5px 10px;
  cursor: pointer;
  font-size: 12px;
  border-radius: 3px;
}
</style>
</head>
<body>
<header>
  <div><strong>Homepage Image Manager</strong></div>
  <nav><a href="dashboard.php">← Back to Dashboard</a></nav>
</header>

<main>
  <h1>Manage Homepage Images</h1>
  <?php if (!empty($msg)) echo "<p class='notice'>$msg</p>"; ?>

  <form method="POST" enctype="multipart/form-data" class="upload-form">
    <label>Section:</label>
    <select name="section">
      <?php foreach ($sections as $s): ?>
        <option value="<?= $s ?>"><?= ucfirst(str_replace('_',' ', $s)) ?></option>
      <?php endforeach; ?>
    </select>

    <label>Title:</label>
    <input type="text" name="title">

    <label>Description:</label>
    <textarea name="description"></textarea>

    <label>Image File:</label>
    <input type="file" name="image" accept="image/*" required>

    <button type="submit">Upload</button>
  </form>

  <?php foreach ($sections as $s): 
    $imgs = $pdo->prepare("SELECT * FROM home_images WHERE section=? ORDER BY order_no ASC, id DESC");
    $imgs->execute([$s]);
    $data = $imgs->fetchAll(PDO::FETCH_ASSOC);
  ?>
  <h2><?= ucfirst(str_replace('_',' ', $s)) ?> Images</h2>
  <div class="image-grid" data-section="<?= $s ?>">
    <?php foreach ($data as $img): ?>
      <div class="image-card" draggable="true" data-id="<?= $img['id'] ?>">
        <img src="../images/home/<?= $s ?>/<?= htmlspecialchars($img['filename']) ?>" alt="">
        <div class="caption">
          <strong><?= htmlspecialchars($img['title'] ?: 'Untitled') ?></strong><br>
          <small><?= htmlspecialchars($img['description'] ?: '') ?></small><br>
          <div class="toggle <?= $img['visible'] ? 'active' : '' ?>" data-id="<?= $img['id'] ?>">
            <?= $img['visible'] ? 'Visible' : 'Hidden' ?>
          </div><br>
          <a href="#" class="edit" data-id="<?= $img['id'] ?>">Edit</a>
          <a href="?delete=<?= $img['id'] ?>" class="delete" onclick="return confirm('Delete this image?');">Delete</a>

          <div class="edit-fields" id="edit-<?= $img['id'] ?>">
            <input type="text" name="title" value="<?= htmlspecialchars($img['title']) ?>" placeholder="Title">
            <textarea name="description" placeholder="Description"><?= htmlspecialchars($img['description']) ?></textarea>
            <button type="button" class="save-btn" data-id="<?= $img['id'] ?>">Save</button>
          </div>
        </div>
      </div>
    <?php endforeach; ?>
    <?php if (empty($data)) echo "<p>No images uploaded yet.</p>"; ?>
  </div>
  <?php endforeach; ?>
</main>

<script>
// ✅ Toggle visibility
document.querySelectorAll('.toggle').forEach(btn => {
  btn.addEventListener('click', async () => {
    const id = btn.dataset.id;
    const formData = new FormData();
    formData.append('toggle_id', id);
    await fetch('', { method: 'POST', body: formData });
    btn.classList.toggle('active');
    btn.textContent = btn.classList.contains('active') ? 'Visible' : 'Hidden';
  });
});

// ✅ Edit (inline)
document.querySelectorAll('.edit').forEach(btn => {
  btn.addEventListener('click', e => {
    e.preventDefault();
    const id = btn.dataset.id;
    const fields = document.getElementById('edit-' + id);
    fields.style.display = fields.style.display === 'block' ? 'none' : 'block';
  });
});
document.querySelectorAll('.save-btn').forEach(btn => {
  btn.addEventListener('click', async () => {
    const id = btn.dataset.id;
    const parent = document.getElementById('edit-' + id);
    const title = parent.querySelector('input[name="title"]').value;
    const desc = parent.querySelector('textarea[name="description"]').value;
    const fd = new FormData();
    fd.append('edit_id', id);
    fd.append('title', title);
    fd.append('description', desc);
    const res = await fetch('', { method: 'POST', body: fd });
    if (res.ok) location.reload();
  });
});

// ✅ Drag & drop reorder
document.querySelectorAll('.image-grid').forEach(grid => {
  let dragged;
  grid.addEventListener('dragstart', e => {
    if (e.target.classList.contains('image-card')) {
      dragged = e.target;
      e.target.classList.add('dragging');
    }
  });
  grid.addEventListener('dragend', e => {
    e.target.classList.remove('dragging');
    const order = Array.from(grid.querySelectorAll('.image-card')).map(el => el.dataset.id);
    const formData = new FormData();
    order.forEach(id => formData.append('order[]', id));
    fetch('', { method: 'POST', body: formData });
  });
  grid.addEventListener('dragover', e => {
    e.preventDefault();
    const afterElement = getDragAfterElement(grid, e.clientY);
    if (afterElement == null) grid.appendChild(dragged);
    else grid.insertBefore(dragged, afterElement);
  });
});
function getDragAfterElement(container, y) {
  const draggableElements = [...container.querySelectorAll('.image-card:not(.dragging)')];
  return draggableElements.reduce((closest, child) => {
    const box = child.getBoundingClientRect();
    const offset = y - box.top - box.height / 2;
    if (offset < 0 && offset > closest.offset) {
      return { offset: offset, element: child };
    } else {
      return closest;
    }
  }, { offset: Number.NEGATIVE_INFINITY }).element;
}
</script>
</body>
</html>
