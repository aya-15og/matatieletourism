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

// Create businesses table if it doesn't exist
$create_table = "CREATE TABLE IF NOT EXISTS `businesses` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `category` varchar(100) NOT NULL,
  `type` varchar(100) DEFAULT NULL,
  `address` varchar(255) DEFAULT NULL,
  `phone` varchar(50) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `website` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `services` text DEFAULT NULL,
  `visible` tinyint(1) DEFAULT 1,
  `featured` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

try {
    $pdo->exec($create_table);
} catch (PDOException $e) {
    // Table already exists
}

$edit_data = [
    'id' => '',
    'name' => '',
    'category' => '',
    'type' => '',
    'address' => '',
    'phone' => '',
    'email' => '',
    'website' => '',
    'description' => '',
    'services' => '',
    'visible' => 1,
    'featured' => 0
];

// Editing existing
if (isset($_GET['edit'])) {
    $id = intval($_GET['edit']);
    $stmt = $pdo->prepare("SELECT * FROM businesses WHERE id=?");
    $stmt->execute([$id]);
    $edit_data = $stmt->fetch(PDO::FETCH_ASSOC) ?: $edit_data;
}

// Handle form submit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'] ?? '';
    $name = $_POST['name'];
    $category = $_POST['category'];
    $type = $_POST['type'];
    $address = $_POST['address'];
    $phone = $_POST['phone'];
    $email = $_POST['email'];
    $website = $_POST['website'];
    $description = $_POST['description'];
    $services = $_POST['services'];
    $visible = isset($_POST['visible']) ? 1 : 0;
    $featured = isset($_POST['featured']) ? 1 : 0;

    if ($id) {
        $stmt = $pdo->prepare("UPDATE businesses SET name=?, category=?, type=?, address=?, phone=?, email=?, website=?, description=?, services=?, visible=?, featured=? WHERE id=?");
        $stmt->execute([$name, $category, $type, $address, $phone, $email, $website, $description, $services, $visible, $featured, $id]);
    } else {
        $stmt = $pdo->prepare("INSERT INTO businesses (name, category, type, address, phone, email, website, description, services, visible, featured) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$name, $category, $type, $address, $phone, $email, $website, $description, $services, $visible, $featured]);
    }

    redirect('manage_businesses.php');
}

// Delete
if (isset($_GET['delete'])) {
    $stmt = $pdo->prepare("DELETE FROM businesses WHERE id=?");
    $stmt->execute([intval($_GET['delete'])]);
    redirect('manage_businesses.php');
}

// Fetch all businesses grouped by category
$rows = $pdo->query("SELECT * FROM businesses ORDER BY category ASC, name ASC")->fetchAll(PDO::FETCH_ASSOC);

// Get category counts
$category_counts = [];
foreach ($rows as $row) {
    $cat = $row['category'];
    if (!isset($category_counts[$cat])) {
        $category_counts[$cat] = 0;
    }
    $category_counts[$cat]++;
}

$categories = [
    'Shopping' => '🛍️',
    'Healthcare' => '⚕️',
    'Funeral Services' => '🕊️',
    'Financial Services' => '💰',
    'Government' => '🏛️',
    'Services' => '⚙️',
    'Fuel' => '⛽',
    'Education' => '📚',
    'Automotive' => '🚗',
    'Professional Services' => '💼',
    'Personal Services' => '💅',
    'Food Services' => '🍰',
    'Entertainment' => '🎭',
    'Agriculture' => '🌾',
    'Other' => '📍'
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Manage Business Directory - Admin</title>
<link rel="stylesheet" href="../assets/admin.css">
<style>
.visible-yes { color: green; font-weight: bold; }
.visible-no { color: #aaa; }
.featured-badge { 
    background: #ffd700;
    color: #333;
    padding: 0.2rem 0.6rem;
    border-radius: 10px;
    font-size: 0.8rem;
    font-weight: 600;
}
.category-badge {
    display: inline-block;
    padding: 0.3rem 0.8rem;
    border-radius: 12px;
    background: #f0f0f0;
    color: #666;
    font-size: 0.85rem;
    margin-right: 0.5rem;
}
.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
    gap: 1rem;
    margin: 2rem 0;
}
.stat-box {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 1.5rem;
    border-radius: 8px;
    text-align: center;
}
.stat-number {
    font-size: 2rem;
    font-weight: bold;
}
.stat-label {
    font-size: 0.9rem;
    opacity: 0.9;
    margin-top: 0.5rem;
}
.filter-tabs {
    display: flex;
    gap: 0.5rem;
    margin: 1rem 0;
    flex-wrap: wrap;
}
.filter-tab {
    padding: 0.6rem 1.2rem;
    background: #f0f0f0;
    border: none;
    border-radius: 20px;
    cursor: pointer;
    font-size: 0.9rem;
}
.filter-tab.active {
    background: #667eea;
    color: white;
}
</style>
</head>
<body>

<h1>Manage Business Directory</h1>
<a href="dashboard.php" class="back-btn">← Back to Dashboard</a>

<div class="stats-grid">
    <div class="stat-box">
        <div class="stat-number"><?= count($rows) ?></div>
        <div class="stat-label">Total Businesses</div>
    </div>
    <div class="stat-box">
        <div class="stat-number"><?= count($category_counts) ?></div>
        <div class="stat-label">Categories</div>
    </div>
    <div class="stat-box">
        <div class="stat-number"><?= count(array_filter($rows, fn($r) => $r['visible'])) ?></div>
        <div class="stat-label">Visible</div>
    </div>
    <div class="stat-box">
        <div class="stat-number"><?= count(array_filter($rows, fn($r) => $r['featured'])) ?></div>
        <div class="stat-label">Featured</div>
    </div>
</div>

<form method="post">
    <input type="hidden" name="id" value="<?= htmlspecialchars($edit_data['id']) ?>">

    <label>Business Name: *</label>
    <input type="text" name="name" required value="<?= htmlspecialchars($edit_data['name'] ?? '') ?>">

    <label>Category: *</label>
    <select name="category" required>
        <option value="">-- Select Category --</option>
        <?php foreach (array_keys($categories) as $cat): ?>
            <option value="<?= $cat ?>" <?= ($edit_data['category'] ?? '') === $cat ? 'selected' : '' ?>>
                <?= $categories[$cat] ?> <?= $cat ?>
            </option>
        <?php endforeach; ?>
    </select>

    <label>Business Type:</label>
    <input type="text" name="type" placeholder="e.g., Supermarket, Pharmacy, Hardware Store" value="<?= htmlspecialchars($edit_data['type'] ?? '') ?>">

    <label>Physical Address:</label>
    <input type="text" name="address" placeholder="Street address, Matatiele" value="<?= htmlspecialchars($edit_data['address'] ?? '') ?>">

    <label>Phone Number:</label>
    <input type="tel" name="phone" placeholder="039 xxx xxxx" value="<?= htmlspecialchars($edit_data['phone'] ?? '') ?>">

    <label>Email Address:</label>
    <input type="email" name="email" placeholder="info@business.co.za" value="<?= htmlspecialchars($edit_data['email'] ?? '') ?>">

    <label>Website URL:</label>
    <input type="url" name="website" placeholder="https://example.com" value="<?= htmlspecialchars($edit_data['website'] ?? '') ?>">

    <label>Description:</label>
    <textarea name="description" rows="4" placeholder="Brief description of the business..."><?= htmlspecialchars($edit_data['description'] ?? '') ?></textarea>

    <label>Services Offered:</label>
    <textarea name="services" rows="2" placeholder="e.g., Groceries, Fresh Produce, Bakery"><?= htmlspecialchars($edit_data['services'] ?? '') ?></textarea>

    <label>
        <input type="checkbox" name="visible" value="1" <?= ($edit_data['visible'] ?? 1) ? 'checked' : '' ?>>
        Visible in Directory
    </label>

    <label>
        <input type="checkbox" name="featured" value="1" <?= ($edit_data['featured'] ?? 0) ? 'checked' : '' ?>>
        Featured Business (appears prominently)
    </label>

    <button type="submit"><?= $edit_data['id'] ? 'Update' : 'Add' ?> Business</button>
</form>

<h2>Business Listings</h2>

<div class="filter-tabs">
    <button class="filter-tab active" onclick="filterByCategory('all')">All (<?= count($rows) ?>)</button>
    <?php foreach ($category_counts as $cat => $count): ?>
        <button class="filter-tab" onclick="filterByCategory('<?= htmlspecialchars($cat) ?>')">
            <?= $categories[$cat] ?? '📍' ?> <?= htmlspecialchars($cat) ?> (<?= $count ?>)
        </button>
    <?php endforeach; ?>
</div>

<table id="businessTable">
<thead>
<tr>
    <th>Business Name</th>
    <th>Category</th>
    <th>Contact</th>
    <th>Status</th>
    <th>Actions</th>
</tr>
</thead>
<tbody>
<?php foreach ($rows as $r): ?>
<tr data-category="<?= htmlspecialchars($r['category']) ?>">
    <td>
        <strong><?= htmlspecialchars($r['name']) ?></strong>
        <?php if ($r['featured']): ?>
            <span class="featured-badge">⭐ Featured</span>
        <?php endif; ?>
        <?php if (!empty($r['type'])): ?>
            <br><small style="color: #999;"><?= htmlspecialchars($r['type']) ?></small>
        <?php endif; ?>
    </td>
    <td>
        <span class="category-badge">
            <?= $categories[$r['category']] ?? '📍' ?> <?= htmlspecialchars($r['category']) ?>
        </span>
    </td>
    <td>
        <?php if (!empty($r['phone'])): ?>
            📞 <?= htmlspecialchars($r['phone']) ?><br>
        <?php endif; ?>
        <?php if (!empty($r['email'])): ?>
            ✉️ <?= htmlspecialchars($r['email']) ?><br>
        <?php endif; ?>
        <?php if (!empty($r['website'])): ?>
            <a href="<?= htmlspecialchars($r['website']) ?>" target="_blank">🔗 Website</a>
        <?php endif; ?>
    </td>
    <td class="<?= $r['visible'] ? 'visible-yes' : 'visible-no' ?>">
        <?= $r['visible'] ? '✓ Visible' : '✗ Hidden' ?>
    </td>
    <td>
        <a href="?edit=<?= $r['id'] ?>" class="edit">Edit</a>
        <a href="?delete=<?= $r['id'] ?>" class="del" onclick="return confirm('Delete this business?')">Delete</a>
    </td>
</tr>
<?php endforeach; ?>
</tbody>
</table>

<?php if (empty($rows)): ?>
<p style="text-align: center; color: #999; padding: 2rem;">No businesses yet. Add your first business above!</p>
<?php endif; ?>

<script>
function filterByCategory(category) {
    const rows = document.querySelectorAll('#businessTable tbody tr');
    const tabs = document.querySelectorAll('.filter-tab');
    
    // Update active tab
    tabs.forEach(tab => tab.classList.remove('active'));
    event.target.classList.add('active');
    
    // Filter rows
    rows.forEach(row => {
        if (category === 'all' || row.dataset.category === category) {
            row.style.display = '';
        } else {
            row.style.display = 'none';
        }
    });
}
</script>

</body>
</html>