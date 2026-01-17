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

// Mark as read
if (isset($_GET['mark_read'])) {
    $id = intval($_GET['mark_read']);
    $stmt = $pdo->prepare("UPDATE contact_messages SET status = 'read', read_at = NOW() WHERE id = ?");
    $stmt->execute([$id]);
    $success_message = "✓ Message marked as read.";
}

// Mark as replied
if (isset($_GET['mark_replied'])) {
    $id = intval($_GET['mark_replied']);
    $stmt = $pdo->prepare("UPDATE contact_messages SET status = 'replied', replied_at = NOW() WHERE id = ?");
    $stmt->execute([$id]);
    $success_message = "✓ Message marked as replied.";
}

// Delete message
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $stmt = $pdo->prepare("DELETE FROM contact_messages WHERE id = ?");
    $stmt->execute([$id]);
    $success_message = "✓ Message deleted.";
    redirect('manage_contacts.php');
}

// Bulk actions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['bulk_action'])) {
    $action = $_POST['bulk_action'];
    $selected = $_POST['selected'] ?? [];
    
    if (!empty($selected) && in_array($action, ['read', 'replied', 'delete'])) {
        $ids = array_map('intval', $selected);
        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        
        if ($action === 'delete') {
            $stmt = $pdo->prepare("DELETE FROM contact_messages WHERE id IN ($placeholders)");
            $stmt->execute($ids);
            $success_message = "✓ " . count($ids) . " message(s) deleted.";
        } else {
            $status = $action;
            $field = $action === 'read' ? 'read_at' : 'replied_at';
            $stmt = $pdo->prepare("UPDATE contact_messages SET status = ?, $field = NOW() WHERE id IN ($placeholders)");
            $stmt->execute(array_merge([$status], $ids));
            $success_message = "✓ " . count($ids) . " message(s) marked as $status.";
        }
    }
}

// Filters
$status_filter = $_GET['status'] ?? 'all';
$search = $_GET['search'] ?? '';

// Build query
$where = [];
$params = [];

if ($status_filter !== 'all') {
    $where[] = "status = ?";
    $params[] = $status_filter;
}

if (!empty($search)) {
    $where[] = "(name LIKE ? OR email LIKE ? OR subject LIKE ? OR message LIKE ?)";
    $search_term = "%$search%";
    $params = array_merge($params, [$search_term, $search_term, $search_term, $search_term]);
}

$where_clause = !empty($where) ? "WHERE " . implode(" AND ", $where) : "";

// Fetch messages
$stmt = $pdo->prepare("SELECT * FROM contact_messages $where_clause ORDER BY created_at DESC");
$stmt->execute($params);
$messages = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get stats
$stats = [
    'total' => $pdo->query("SELECT COUNT(*) FROM contact_messages")->fetchColumn(),
    'new' => $pdo->query("SELECT COUNT(*) FROM contact_messages WHERE status = 'new'")->fetchColumn(),
    'read' => $pdo->query("SELECT COUNT(*) FROM contact_messages WHERE status = 'read'")->fetchColumn(),
    'replied' => $pdo->query("SELECT COUNT(*) FROM contact_messages WHERE status = 'replied'")->fetchColumn(),
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Contact Messages - Admin Panel</title>
<link rel="stylesheet" href="../assets/admin.css">
<style>
    * { margin: 0; padding: 0; box-sizing: border-box; }
    body {
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        background: #f5f7fa;
        color: #333;
    }
    
    .admin-header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
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
    
    .btn-secondary {
        background: white;
        color: #667eea;
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
    
    /* Stats */
    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 1.5rem;
        margin-bottom: 2rem;
    }
    
    .stat-card {
        background: white;
        padding: 1.5rem;
        border-radius: 12px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.05);
        border-left: 4px solid #667eea;
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
        color: #667eea;
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
    
    /* Filters */
    .filters-section {
        background: white;
        padding: 1.5rem;
        border-radius: 12px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.05);
        margin-bottom: 2rem;
    }
    
    .filters-row {
        display: flex;
        gap: 1rem;
        flex-wrap: wrap;
        align-items: center;
    }
    
    .filter-group {
        flex: 1;
        min-width: 200px;
    }
    
    .filter-group label {
        display: block;
        margin-bottom: 0.5rem;
        font-weight: 600;
        color: #555;
        font-size: 0.9rem;
    }
    
    .filter-group input,
    .filter-group select {
        width: 100%;
        padding: 0.7rem;
        border: 2px solid #e1e8ed;
        border-radius: 8px;
        font-size: 0.95rem;
    }
    
    .filter-group input:focus,
    .filter-group select:focus {
        outline: none;
        border-color: #667eea;
    }
    
    /* Table */
    .table-section {
        background: white;
        border-radius: 12px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.05);
        overflow: hidden;
    }
    
    .table-header {
        padding: 1.5rem 2rem;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    
    .bulk-actions {
        display: flex;
        gap: 0.5rem;
        align-items: center;
    }
    
    .bulk-actions select,
    .bulk-actions button {
        padding: 0.5rem 1rem;
        border-radius: 6px;
        border: none;
        font-size: 0.9rem;
    }
    
    .bulk-actions select {
        background: white;
        color: #333;
    }
    
    .bulk-actions button {
        background: #28a745;
        color: white;
        cursor: pointer;
        font-weight: 600;
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
    
    tbody tr.unread {
        background: #fff3cd;
    }
    
    td {
        padding: 1rem;
        vertical-align: top;
    }
    
    .status-badge {
        display: inline-block;
        padding: 0.3rem 0.8rem;
        border-radius: 12px;
        font-size: 0.8rem;
        font-weight: 600;
    }
    
    .status-new { background: #fff3cd; color: #856404; }
    .status-read { background: #d1ecf1; color: #0c5460; }
    .status-replied { background: #d4edda; color: #155724; }
    
    .message-preview {
        max-width: 300px;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
        color: #666;
        font-size: 0.9rem;
    }
    
    .action-btns {
        display: flex;
        gap: 0.5rem;
        flex-wrap: wrap;
    }
    
    .action-btns a {
        padding: 0.4rem 0.8rem;
        border-radius: 6px;
        text-decoration: none;
        font-size: 0.85rem;
        font-weight: 600;
        transition: all 0.3s;
    }
    
    .btn-view {
        background: #007bff;
        color: white;
    }
    .btn-view:hover {
        background: #0056b3;
    }
    
    .btn-reply {
        background: #28a745;
        color: white;
    }
    .btn-reply:hover {
        background: #218838;
    }
    
    .btn-delete {
        background: #dc3545;
        color: white;
    }
    .btn-delete:hover {
        background: #c82333;
    }
    
    .no-messages {
        text-align: center;
        padding: 3rem;
        color: #666;
    }
    
    /* Modal */
    .modal {
        display: none;
        position: fixed;
        z-index: 1000;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        background: rgba(0,0,0,0.7);
    }
    
    .modal-content {
        background: white;
        margin: 5% auto;
        padding: 2rem;
        border-radius: 12px;
        max-width: 700px;
        max-height: 80vh;
        overflow-y: auto;
    }
    
    .modal-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 1.5rem;
        padding-bottom: 1rem;
        border-bottom: 2px solid #e1e8ed;
    }
    
    .modal-close {
        font-size: 2rem;
        cursor: pointer;
        color: #666;
    }
    
    .modal-close:hover {
        color: #dc3545;
    }
    
    .message-detail {
        line-height: 1.6;
    }
    
    .message-detail p {
        margin: 0.8rem 0;
    }
    
    .message-detail strong {
        color: #333;
    }
    
    @media (max-width: 768px) {
        .filters-row { flex-direction: column; }
        .filter-group { width: 100%; }
        .table-header { flex-direction: column; gap: 1rem; }
        table { font-size: 0.85rem; }
        .action-btns { flex-direction: column; }
    }
</style>
</head>
<body>

<div class="admin-header">
    <h1>✉️ Contact Messages</h1>
    <div class="header-actions">
        <a href="dashboard.php" class="btn btn-secondary">← Dashboard</a>
    </div>
</div>

<div class="container">
    <!-- Stats -->
    <div class="stats-grid">
        <div class="stat-card">
            <h3>Total Messages</h3>
            <div class="number"><?php echo $stats['total']; ?></div>
        </div>
        <div class="stat-card">
            <h3>New (Unread)</h3>
            <div class="number"><?php echo $stats['new']; ?></div>
        </div>
        <div class="stat-card">
            <h3>Read</h3>
            <div class="number"><?php echo $stats['read']; ?></div>
        </div>
        <div class="stat-card">
            <h3>Replied</h3>
            <div class="number"><?php echo $stats['replied']; ?></div>
        </div>
    </div>

    <?php if($success_message): ?>
    <div class="message success-message"><?php echo $success_message; ?></div>
    <?php endif; ?>

    <!-- Filters -->
    <div class="filters-section">
        <form method="get" class="filters-row">
            <div class="filter-group">
                <label>Status</label>
                <select name="status" onchange="this.form.submit()">
                    <option value="all" <?php echo $status_filter === 'all' ? 'selected' : ''; ?>>All Messages</option>
                    <option value="new" <?php echo $status_filter === 'new' ? 'selected' : ''; ?>>New</option>
                    <option value="read" <?php echo $status_filter === 'read' ? 'selected' : ''; ?>>Read</option>
                    <option value="replied" <?php echo $status_filter === 'replied' ? 'selected' : ''; ?>>Replied</option>
                </select>
            </div>
            <div class="filter-group">
                <label>Search</label>
                <input type="text" name="search" placeholder="Search by name, email, subject..." value="<?php echo htmlspecialchars($search); ?>">
            </div>
            <div class="filter-group" style="align-self: flex-end;">
                <button type="submit" class="btn btn-secondary" style="width: 100%;">Apply Filters</button>
            </div>
        </form>
    </div>

    <!-- Table -->
    <div class="table-section">
        <div class="table-header">
            <h2>📋 Messages (<?php echo count($messages); ?>)</h2>
            <form method="post" class="bulk-actions" id="bulkForm">
                <select name="bulk_action">
                    <option value="">Bulk Actions</option>
                    <option value="read">Mark as Read</option>
                    <option value="replied">Mark as Replied</option>
                    <option value="delete">Delete</option>
                </select>
                <button type="submit" onclick="return confirm('Apply this action to selected messages?')">Apply</button>
            </form>
        </div>
        
        <?php if (empty($messages)): ?>
        <div class="no-messages">
            <p>📭 No messages found.</p>
        </div>
        <?php else: ?>
        <table>
            <thead>
                <tr>
                    <th style="width: 40px;"><input type="checkbox" id="selectAll"></th>
                    <th>Status</th>
                    <th>From</th>
                    <th>Subject</th>
                    <th>Message</th>
                    <th>Date</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($messages as $msg): ?>
                <tr class="<?php echo $msg['status'] === 'new' ? 'unread' : ''; ?>">
                    <td>
                        <input type="checkbox" name="selected[]" value="<?php echo $msg['id']; ?>" form="bulkForm">
                    </td>
                    <td>
                        <span class="status-badge status-<?php echo $msg['status']; ?>">
                            <?php echo ucfirst($msg['status']); ?>
                        </span>
                    </td>
                    <td>
                        <strong><?php echo htmlspecialchars($msg['name']); ?></strong><br>
                        <small><?php echo htmlspecialchars($msg['email']); ?></small>
                        <?php if (!empty($msg['phone'])): ?>
                        <br><small>📞 <?php echo htmlspecialchars($msg['phone']); ?></small>
                        <?php endif; ?>
                    </td>
                    <td><strong><?php echo htmlspecialchars($msg['subject']); ?></strong></td>
                    <td>
                        <div class="message-preview">
                            <?php echo htmlspecialchars(substr($msg['message'], 0, 100)); ?>
                            <?php echo strlen($msg['message']) > 100 ? '...' : ''; ?>
                        </div>
                    </td>
                    <td><small><?php echo date('M d, Y', strtotime($msg['created_at'])); ?><br><?php echo date('h:i A', strtotime($msg['created_at'])); ?></small></td>
                    <td>
                        <div class="action-btns">
                            <a href="#" onclick="viewMessage(<?php echo $msg['id']; ?>); return false;" class="btn-view">👁️ View</a>
                            <a href="?mark_replied=<?php echo $msg['id']; ?>" class="btn-reply">✉️ Replied</a>
                            <a href="?delete=<?php echo $msg['id']; ?>" class="btn-delete" onclick="return confirm('Delete this message?')">🗑️</a>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php endif; ?>
    </div>
</div>

<!-- Modal -->
<div id="messageModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h2>Message Details</h2>
            <span class="modal-close" onclick="closeModal()">&times;</span>
        </div>
        <div id="modalBody" class="message-detail">
            Loading...
        </div>
    </div>
</div>

<script>
// Select all checkbox
document.getElementById('selectAll')?.addEventListener('change', function() {
    document.querySelectorAll('input[name="selected[]"]').forEach(cb => cb.checked = this.checked);
});

// View message modal
const messagesData = <?php echo json_encode($messages); ?>;

function viewMessage(id) {
    const msg = messagesData.find(m => m.id == id);
    if (!msg) return;
    
    const modalBody = document.getElementById('modalBody');
    modalBody.innerHTML = `
        <p><strong>From:</strong> ${msg.name}</p>
        <p><strong>Email:</strong> <a href="mailto:${msg.email}">${msg.email}</a></p>
        ${msg.phone ? `<p><strong>Phone:</strong> <a href="tel:${msg.phone}">${msg.phone}</a></p>` : ''}
        <p><strong>Subject:</strong> ${msg.subject}</p>
        <p><strong>Status:</strong> <span class="status-badge status-${msg.status}">${msg.status}</span></p>
        <p><strong>Received:</strong> ${new Date(msg.created_at).toLocaleString()}</p>
        <hr style="margin: 1.5rem 0; border: none; border-top: 2px solid #e1e8ed;">
        <p><strong>Message:</strong></p>
        <p style="white-space: pre-wrap; line-height: 1.8; color: #555;">${msg.message}</p>
        <hr style="margin: 1.5rem 0; border: none; border-top: 2px solid #e1e8ed;">
        <p style="font-size: 0.85rem; color: #666;">
            <strong>IP Address:</strong> ${msg.ip_address || 'N/A'}<br>
            <strong>User Agent:</strong> ${msg.user_agent || 'N/A'}
        </p>
    `;
    
    document.getElementById('messageModal').style.display = 'block';
    
    // Mark as read
    if (msg.status === 'new') {
        fetch('?mark_read=' + id).then(() => location.reload());
    }
}

function closeModal() {
    document.getElementById('messageModal').style.display = 'none';
}

// Close modal on outside click
window.onclick = function(event) {
    const modal = document.getElementById('messageModal');
    if (event.target === modal) {
        closeModal();
    }
}

// Close with ESC key
document.addEventListener('keydown', function(event) {
    if (event.key === 'Escape') {
        closeModal();
    }
});
</script>

</body>
</html>