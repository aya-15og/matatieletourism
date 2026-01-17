<?php
ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);
error_reporting(E_ALL);

require '../includes/config.php'; // adjust path if needed

function esc($s) {
  return htmlspecialchars($s, ENT_QUOTES);
}

// Handle file upload
$message = '';
if (isset($_POST['upload'])) {
    if (!empty($_FILES['image']['name'])) {
        $filename = $_FILES['image']['name'];
        $tmpname  = $_FILES['image']['tmp_name'];
        $caption  = $_POST['caption'] ?? '';
        $featured = isset($_POST['featured']) ? 1 : 0;

        $target = "../images/gallery/" . basename($filename);

        if (move_uploaded_file($tmpname, $target)) {
            $stmt = $pdo->prepare("INSERT INTO gallery (filename, caption, featured) VALUES (?, ?, ?)");
            $stmt->execute([$filename, $caption, $featured]);
            $message = "Image uploaded successfully!";
        } else {
            $message = "Failed to move uploaded file.";
        }
    } else {
        $message = "Please select an image to upload.";
    }
}

// Handle toggle featured status
if (isset($_GET['toggle_featured'])) {
    $id = (int)$_GET['toggle_featured'];
    $stmt = $pdo->prepare("UPDATE gallery SET featured = CASE WHEN featured = 1 THEN 0 ELSE 1 END WHERE id = ?");
    $stmt->execute([$id]);
    header("Location: manage_gallery.php");
    exit;
}

// Handle edit caption
if (isset($_POST['edit_caption'])) {
    $id = (int)$_POST['id'];
    $caption = $_POST['caption'] ?? '';
    $stmt = $pdo->prepare("UPDATE gallery SET caption = ? WHERE id = ?");
    $stmt->execute([$caption, $id]);
    $message = "Caption updated successfully!";
}

// Handle delete request
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $stmt = $pdo->prepare("SELECT filename FROM gallery WHERE id = ?");
    $stmt->execute([$id]);
    $file = $stmt->fetchColumn();
    if ($file && file_exists("../images/gallery/" . $file)) {
        unlink("../images/gallery/" . $file); // delete file from server
    }
    $stmt = $pdo->prepare("DELETE FROM gallery WHERE id = ?");
    $stmt->execute([$id]);
    header("Location: manage_gallery.php");
    exit;
}

// Fetch gallery images
$stmt = $pdo->query("SELECT * FROM gallery ORDER BY created_at DESC");
$gallery = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Manage Gallery - Admin</title>
<style>
body { font-family: 'Segoe UI', sans-serif; background: #f4f4f4; margin:0; color:#333; }
header { background:#00703c; color:#fff; padding:15px 0; text-align:center; }
.container { width:95%; max-width:1200px; margin:30px auto; background:#fff; padding:20px; border-radius:8px; box-shadow:0 3px 10px rgba(0,0,0,0.1); }
h1 { text-align:center; margin-bottom:20px; }
table { width:100%; border-collapse:collapse; margin-top:10px; }
th, td { border:1px solid #ddd; padding:10px; text-align:left; }
th { background:#00703c; color:#fff; }
tr:nth-child(even){background:#f9f9f9;}
img.thumb { width:100px; height:70px; object-fit:cover; border-radius:6px; }
.actions a, .actions button { text-decoration:none; margin-right:8px; padding:6px 10px; border-radius:4px; color:#fff; font-size:0.9em; border:none; cursor:pointer; }
.btn-edit { background:#00703c; }
.btn-toggle { background:#f39c12; }
.btn-delete { background:#c0392b; }
.featured { background:#ffd700; color:#111; font-weight:600; padding:2px 6px; border-radius:4px; font-size:0.8em; }
.not-featured { background:#ccc; color:#555; padding:2px 6px; border-radius:4px; font-size:0.8em; }
form.inline-upload { margin-bottom: 20px; display:flex; flex-wrap:wrap; gap:10px; align-items:center; }
form.inline-upload input[type="file"] { flex:1; }
form.inline-upload input[type="text"] { flex:2; padding:6px; border-radius:4px; border:1px solid #ccc; }
form.inline-upload input[type="submit"] { padding:8px 12px; border:none; border-radius:4px; background:#00703c; color:#fff; cursor:pointer; }
form.inline-upload input[type="submit"]:hover { background:#005d30; }
.message { margin-bottom:15px; font-weight:bold; color:green; }
.edit-form { display:none; margin-top:5px; }
.edit-form input { width:90%; padding:5px; }
.edit-form button { margin-top:5px; padding:5px 10px; background:#00703c; color:#fff; border:none; border-radius:4px; cursor:pointer; }
</style>
</head>
<body>

<header>
<h1>Manage Gallery</h1>
</header>

<div class="container">

<?php if($message): ?>
  <div class="message"><?= esc($message); ?></div>
<?php endif; ?>

<!-- Inline upload form -->
<form class="inline-upload" method="post" enctype="multipart/form-data">
  <input type="file" name="image" required>
  <input type="text" name="caption" placeholder="Caption (optional)">
  <label><input type="checkbox" name="featured"> Featured</label>
  <input type="submit" name="upload" value="Upload">
</form>

<table>
<thead>
<tr>
<th>ID</th>
<th>Preview</th>
<th>Filename</th>
<th>Caption</th>
<th>Featured</th>
<th>Created At</th>
<th>Actions</th>
</tr>
</thead>
<tbody>
<?php if(count($gallery) > 0): ?>
<?php foreach($gallery as $g): ?>
<tr>
<td><?= esc($g['id']); ?></td>
<td><img src="../images/gallery/<?= esc($g['filename']); ?>" class="thumb" alt=""></td>
<td><?= esc($g['filename']); ?></td>
<td>
  <div id="caption-display-<?= $g['id']; ?>">
    <?= esc($g['caption'] ?? ''); ?>
  </div>
  <div id="caption-edit-<?= $g['id']; ?>" class="edit-form">
    <form method="post">
      <input type="hidden" name="id" value="<?= $g['id']; ?>">
      <input type="text" name="caption" value="<?= esc($g['caption'] ?? ''); ?>" placeholder="Enter caption">
      <button type="submit" name="edit_caption">Save</button>
      <button type="button" onclick="toggleEdit(<?= $g['id']; ?>)">Cancel</button>
    </form>
  </div>
</td>
<td><?= $g['featured'] ? '<span class="featured">Yes</span>' : '<span class="not-featured">No</span>'; ?></td>
<td><?= esc($g['created_at']); ?></td>
<td class="actions">
<button class="btn-edit" onclick="toggleEdit(<?= $g['id']; ?>)">Edit Caption</button>
<a href="?toggle_featured=<?= esc($g['id']); ?>" class="btn-toggle">Toggle Featured</a>
<a href="?delete=<?= esc($g['id']); ?>" class="btn-delete" onclick="return confirm('Delete this image?')">Delete</a>
</td>
</tr>
<?php endforeach; ?>
<?php else: ?>
<tr><td colspan="7" style="text-align:center;">No gallery images found.</td></tr>
<?php endif; ?>
</tbody>
</table>
</div>

<script>
function toggleEdit(id) {
  const display = document.getElementById('caption-display-' + id);
  const edit = document.getElementById('caption-edit-' + id);
  
  if (edit.style.display === 'none' || edit.style.display === '') {
    display.style.display = 'none';
    edit.style.display = 'block';
  } else {
    display.style.display = 'block';
    edit.style.display = 'none';
  }
}
</script>

</body>
</html>