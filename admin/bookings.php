<?php
session_start();
if (!isset($_SESSION['admin'])) {
    header("Location: login.php");
    exit;
}
require '../includes/config.php';

// Handle status updates
if (isset($_POST['update_status'])) {
    $booking_id = intval($_POST['booking_id']);
    $status = $_POST['status'];
    $payment_status = $_POST['payment_status'];
    
    $stmt = $pdo->prepare("UPDATE bookings SET booking_status = ?, payment_status = ? WHERE id = ?");
    $stmt->execute([$status, $payment_status, $booking_id]);
    
    $success_message = "Booking status updated successfully!";
}

// Fetch filter parameters
$filter_status = $_GET['status'] ?? 'all';
$filter_partner = $_GET['partner'] ?? 'all';
$search = $_GET['search'] ?? '';

// Build query
$query = "SELECT b.*, s.name as stay_name, s.location as stay_location, s.is_partner 
          FROM bookings b 
          JOIN stays s ON b.stay_id = s.id 
          WHERE 1=1";

$params = [];

if ($filter_status !== 'all') {
    $query .= " AND b.booking_status = ?";
    $params[] = $filter_status;
}

if ($filter_partner === 'partner') {
    $query .= " AND b.is_partner_booking = 1";
} elseif ($filter_partner === 'inquiry') {
    $query .= " AND b.is_partner_booking = 0";
}

if (!empty($search)) {
    $query .= " AND (b.booking_reference LIKE ? OR b.guest_name LIKE ? OR b.guest_email LIKE ? OR s.name LIKE ?)";
    $searchTerm = "%{$search}%";
    $params[] = $searchTerm;
    $params[] = $searchTerm;
    $params[] = $searchTerm;
    $params[] = $searchTerm;
}

$query .= " ORDER BY b.created_at DESC";

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$bookings = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get statistics
$stats = [
    'total' => $pdo->query("SELECT COUNT(*) FROM bookings")->fetchColumn(),
    'confirmed' => $pdo->query("SELECT COUNT(*) FROM bookings WHERE booking_status = 'confirmed'")->fetchColumn(),
    'pending' => $pdo->query("SELECT COUNT(*) FROM bookings WHERE booking_status = 'pending'")->fetchColumn(),
    'partner' => $pdo->query("SELECT COUNT(*) FROM bookings WHERE is_partner_booking = 1")->fetchColumn(),
    'total_revenue' => $pdo->query("SELECT SUM(commission_amount) FROM bookings WHERE booking_status = 'confirmed' AND is_partner_booking = 1")->fetchColumn() ?: 0
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Manage Bookings - Admin</title>
<link rel="stylesheet" href="../assets/admin.css">
<style>
:root {
    --primary: #2563eb;
    --success: #10b981;
    --warning: #f59e0b;
    --danger: #ef4444;
    --gray: #6b7280;
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

h1 {
    color: #1f2937;
    margin-bottom: 1.5rem;
}

.back-btn {
    display: inline-block;
    padding: 0.75rem 1.5rem;
    background: #6b7280;
    color: white;
    border-radius: 8px;
    text-decoration: none;
    font-weight: 600;
    margin-bottom: 2rem;
}

.back-btn:hover {
    background: #4b5563;
}

/* Statistics Cards */
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
    box-shadow: 0 1px 3px rgba(0,0,0,0.1);
}

.stat-label {
    font-size: 0.875rem;
    color: var(--gray);
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    margin-bottom: 0.5rem;
}

.stat-value {
    font-size: 2rem;
    font-weight: 700;
    color: #1f2937;
}

.stat-card.primary .stat-value { color: var(--primary); }
.stat-card.success .stat-value { color: var(--success); }
.stat-card.warning .stat-value { color: var(--warning); }

/* Filters */
.filters-section {
    background: white;
    padding: 1.5rem;
    border-radius: 12px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.1);
    margin-bottom: 2rem;
}

.filters-row {
    display: grid;
    grid-template-columns: 2fr 1fr 1fr auto;
    gap: 1rem;
    align-items: end;
}

.filter-group label {
    display: block;
    font-weight: 600;
    color: #374151;
    margin-bottom: 0.5rem;
    font-size: 0.875rem;
}

.filter-group input,
.filter-group select {
    width: 100%;
    padding: 0.75rem;
    border: 1px solid #d1d5db;
    border-radius: 8px;
    font-size: 1rem;
}

.filter-btn {
    padding: 0.75rem 1.5rem;
    background: var(--primary);
    color: white;
    border: none;
    border-radius: 8px;
    font-weight: 600;
    cursor: pointer;
    white-space: nowrap;
}

.filter-btn:hover {
    background: #1d4ed8;
}

.reset-btn {
    padding: 0.75rem 1.5rem;
    background: white;
    color: var(--gray);
    border: 1px solid #d1d5db;
    border-radius: 8px;
    font-weight: 600;
    cursor: pointer;
}

/* Table */
.bookings-table {
    background: white;
    border-radius: 12px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.1);
    overflow: hidden;
}

table {
    width: 100%;
    border-collapse: collapse;
}

thead {
    background: #f9fafb;
}

th {
    padding: 1rem;
    text-align: left;
    font-weight: 600;
    color: #374151;
    font-size: 0.875rem;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    border-bottom: 2px solid #e5e7eb;
}

td {
    padding: 1rem;
    border-bottom: 1px solid #f3f4f6;
    font-size: 0.9rem;
}

tr:hover {
    background: #fafafa;
}

.booking-ref {
    font-family: 'Courier New', monospace;
    font-weight: 700;
    color: var(--primary);
}

.status-badge {
    display: inline-block;
    padding: 0.35rem 0.75rem;
    border-radius: 20px;
    font-size: 0.75rem;
    font-weight: 600;
    text-transform: uppercase;
}

.status-confirmed { background: #d1fae5; color: #065f46; }
.status-pending { background: #fef3c7; color: #92400e; }
.status-cancelled { background: #fee2e2; color: #991b1b; }
.status-completed { background: #dbeafe; color: #1e40af; }

.payment-paid { background: #d1fae5; color: #065f46; }
.payment-unpaid { background: #fee2e2; color: #991b1b; }

.partner-badge {
    background: var(--success);
    color: white;
    padding: 0.25rem 0.5rem;
    border-radius: 12px;
    font-size: 0.7rem;
    font-weight: 600;
}

.inquiry-badge {
    background: var(--warning);
    color: white;
    padding: 0.25rem 0.5rem;
    border-radius: 12px;
    font-size: 0.7rem;
    font-weight: 600;
}

.action-btn {
    padding: 0.4rem 0.8rem;
    border-radius: 6px;
    text-decoration: none;
    font-size: 0.8rem;
    font-weight: 500;
    border: none;
    cursor: pointer;
    margin-right: 0.5rem;
}

.btn-view {
    background: #dbeafe;
    color: #1e40af;
}

.btn-edit {
    background: #fef3c7;
    color: #92400e;
}

.modal {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0,0,0,0.5);
    z-index: 1000;
    align-items: center;
    justify-content: center;
}

.modal.active {
    display: flex;
}

.modal-content {
    background: white;
    padding: 2rem;
    border-radius: 16px;
    max-width: 600px;
    width: 90%;
    max-height: 90vh;
    overflow-y: auto;
}

.modal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1.5rem;
}

.modal-close {
    background: none;
    border: none;
    font-size: 1.5rem;
    cursor: pointer;
    color: var(--gray);
}

.detail-row {
    display: grid;
    grid-template-columns: 140px 1fr;
    padding: 0.75rem 0;
    border-bottom: 1px solid #f3f4f6;
}

.detail-label {
    font-weight: 600;
    color: var(--gray);
}

.detail-value {
    color: #1f2937;
}

.form-group {
    margin-bottom: 1rem;
}

.form-group label {
    display: block;
    font-weight: 600;
    color: #374151;
    margin-bottom: 0.5rem;
}

.form-group select {
    width: 100%;
    padding: 0.75rem;
    border: 1px solid #d1d5db;
    border-radius: 8px;
}

.save-btn {
    width: 100%;
    padding: 1rem;
    background: var(--primary);
    color: white;
    border: none;
    border-radius: 8px;
    font-weight: 600;
    cursor: pointer;
    margin-top: 1rem;
}

.save-btn:hover {
    background: #1d4ed8;
}

.empty-state {
    text-align: center;
    padding: 4rem 2rem;
    color: var(--gray);
}

.success-message {
    background: #d1fae5;
    color: #065f46;
    padding: 1rem;
    border-radius: 8px;
    margin-bottom: 1.5rem;
}

@media (max-width: 1200px) {
    .filters-row {
        grid-template-columns: 1fr;
    }
    
    table {
        font-size: 0.8rem;
    }
    
    th, td {
        padding: 0.75rem 0.5rem;
    }
}
</style>
</head>
<body>

<div class="admin-container">
    <h1>📋 Manage Bookings</h1>
    <a href="dashboard.php" class="back-btn">← Back to Dashboard</a>

    <?php if (isset($success_message)): ?>
    <div class="success-message">✓ <?= $success_message ?></div>
    <?php endif; ?>

    <!-- Statistics -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-label">Total Bookings</div>
            <div class="stat-value"><?= $stats['total'] ?></div>
        </div>
        <div class="stat-card success">
            <div class="stat-label">Confirmed</div>
            <div class="stat-value"><?= $stats['confirmed'] ?></div>
        </div>
        <div class="stat-card warning">
            <div class="stat-label">Pending</div>
            <div class="stat-value"><?= $stats['pending'] ?></div>
        </div>
        <div class="stat-card primary">
            <div class="stat-label">Partner Bookings</div>
            <div class="stat-value"><?= $stats['partner'] ?></div>
        </div>
        <div class="stat-card">
            <div class="stat-label">Total Commission</div>
            <div class="stat-value">R <?= number_format($stats['total_revenue'], 2) ?></div>
        </div>
    </div>

    <!-- Filters -->
    <div class="filters-section">
        <form method="GET" class="filters-row">
            <div class="filter-group">
                <label>Search</label>
                <input type="text" name="search" placeholder="Reference, guest name, email..." value="<?= htmlspecialchars($search) ?>">
            </div>
            <div class="filter-group">
                <label>Status</label>
                <select name="status">
                    <option value="all" <?= $filter_status === 'all' ? 'selected' : '' ?>>All Statuses</option>
                    <option value="pending" <?= $filter_status === 'pending' ? 'selected' : '' ?>>Pending</option>
                    <option value="confirmed" <?= $filter_status === 'confirmed' ? 'selected' : '' ?>>Confirmed</option>
                    <option value="cancelled" <?= $filter_status === 'cancelled' ? 'selected' : '' ?>>Cancelled</option>
                    <option value="completed" <?= $filter_status === 'completed' ? 'selected' : '' ?>>Completed</option>
                </select>
            </div>
            <div class="filter-group">
                <label>Type</label>
                <select name="partner">
                    <option value="all" <?= $filter_partner === 'all' ? 'selected' : '' ?>>All Types</option>
                    <option value="partner" <?= $filter_partner === 'partner' ? 'selected' : '' ?>>Partner</option>
                    <option value="inquiry" <?= $filter_partner === 'inquiry' ? 'selected' : '' ?>>Inquiry</option>
                </select>
            </div>
            <button type="submit" class="filter-btn">Apply</button>
            <a href="bookings.php" class="reset-btn">Reset</a>
        </form>
    </div>

    <!-- Bookings Table -->
    <div class="bookings-table">
        <?php if (empty($bookings)): ?>
            <div class="empty-state">
                <p>No bookings found matching your filters.</p>
            </div>
        <?php else: ?>
        <table>
            <thead>
                <tr>
                    <th>Reference</th>
                    <th>Guest</th>
                    <th>Property</th>
                    <th>Check-in</th>
                    <th>Check-out</th>
                    <th>Guests</th>
                    <th>Type</th>
                    <th>Status</th>
                    <th>Payment</th>
                    <th>Total</th>
                    <th>Commission</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($bookings as $booking): ?>
                <tr>
                    <td><span class="booking-ref"><?= htmlspecialchars($booking['booking_reference']) ?></span></td>
                    <td>
                        <strong><?= htmlspecialchars($booking['guest_name']) ?></strong><br>
                        <small><?= htmlspecialchars($booking['guest_email']) ?></small>
                    </td>
                    <td><?= htmlspecialchars($booking['stay_name']) ?></td>
                    <td><?= date('M d, Y', strtotime($booking['check_in_date'])) ?></td>
                    <td><?= date('M d, Y', strtotime($booking['check_out_date'])) ?></td>
                    <td><?= $booking['number_of_guests'] ?></td>
                    <td>
                        <?php if ($booking['is_partner_booking']): ?>
                            <span class="partner-badge">Partner</span>
                        <?php else: ?>
                            <span class="inquiry-badge">Inquiry</span>
                        <?php endif; ?>
                    </td>
                    <td><span class="status-badge status-<?= $booking['booking_status'] ?>"><?= ucfirst($booking['booking_status']) ?></span></td>
                    <td><span class="status-badge payment-<?= $booking['payment_status'] ?>"><?= ucfirst($booking['payment_status']) ?></span></td>
                    <td>R <?= number_format($booking['total_amount'], 2) ?></td>
                    <td>R <?= number_format($booking['commission_amount'], 2) ?></td>
                    <td>
                        <button class="action-btn btn-view" onclick="viewBooking(<?= $booking['id'] ?>)">View</button>
                        <button class="action-btn btn-edit" onclick="editBooking(<?= $booking['id'] ?>)">Edit</button>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php endif; ?>
    </div>
</div>

<!-- View Modal -->
<div id="viewModal" class="modal">
    <div class="modal-content" id="viewModalContent">
        <!-- Content loaded dynamically -->
    </div>
</div>

<!-- Edit Modal -->
<div id="editModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h2>Update Booking Status</h2>
            <button class="modal-close" onclick="closeModal('editModal')">&times;</button>
        </div>
        <form method="POST" id="editForm">
            <input type="hidden" name="update_status" value="1">
            <input type="hidden" name="booking_id" id="edit_booking_id">
            
            <div class="form-group">
                <label>Booking Status</label>
                <select name="status" id="edit_status">
                    <option value="pending">Pending</option>
                    <option value="confirmed">Confirmed</option>
                    <option value="cancelled">Cancelled</option>
                    <option value="completed">Completed</option>
                </select>
            </div>
            
            <div class="form-group">
                <label>Payment Status</label>
                <select name="payment_status" id="edit_payment_status">
                    <option value="unpaid">Unpaid</option>
                    <option value="paid">Paid</option>
                    <option value="refunded">Refunded</option>
                </select>
            </div>
            
            <button type="submit" class="save-btn">Update Status</button>
        </form>
    </div>
</div>

<script>
const bookings = <?= json_encode($bookings) ?>;

function viewBooking(id) {
    const booking = bookings.find(b => b.id == id);
    if (!booking) return;
    
    const content = `
        <div class="modal-header">
            <h2>Booking Details</h2>
            <button class="modal-close" onclick="closeModal('viewModal')">&times;</button>
        </div>
        <div class="detail-row">
            <span class="detail-label">Reference:</span>
            <span class="detail-value booking-ref">${booking.booking_reference}</span>
        </div>
        <div class="detail-row">
            <span class="detail-label">Type:</span>
            <span class="detail-value">${booking.is_partner_booking ? '<span class="partner-badge">Partner</span>' : '<span class="inquiry-badge">Inquiry</span>'}</span>
        </div>
        <div class="detail-row">
            <span class="detail-label">Property:</span>
            <span class="detail-value"><strong>${booking.stay_name}</strong><br>${booking.stay_location}</span>
        </div>
        <div class="detail-row">
            <span class="detail-label">Guest Name:</span>
            <span class="detail-value">${booking.guest_name}</span>
        </div>
        <div class="detail-row">
            <span class="detail-label">Email:</span>
            <span class="detail-value">${booking.guest_email}</span>
        </div>
        <div class="detail-row">
            <span class="detail-label">Phone:</span>
            <span class="detail-value">${booking.guest_phone}</span>
        </div>
        <div class="detail-row">
            <span class="detail-label">Check-in:</span>
            <span class="detail-value">${new Date(booking.check_in_date).toLocaleDateString()}</span>
        </div>
        <div class="detail-row">
            <span class="detail-label">Check-out:</span>
            <span class="detail-value">${new Date(booking.check_out_date).toLocaleDateString()}</span>
        </div>
        <div class="detail-row">
            <span class="detail-label">Guests:</span>
            <span class="detail-value">${booking.number_of_guests}</span>
        </div>
        <div class="detail-row">
            <span class="detail-label">Nights:</span>
            <span class="detail-value">${booking.number_of_nights}</span>
        </div>
        <div class="detail-row">
            <span class="detail-label">Total Amount:</span>
            <span class="detail-value"><strong>R ${parseFloat(booking.total_amount).toFixed(2)}</strong></span>
        </div>
        <div class="detail-row">
            <span class="detail-label">Commission:</span>
            <span class="detail-value"><strong>R ${parseFloat(booking.commission_amount).toFixed(2)}</strong></span>
        </div>
        <div class="detail-row">
            <span class="detail-label">Booking Status:</span>
            <span class="detail-value"><span class="status-badge status-${booking.booking_status}">${booking.booking_status}</span></span>
        </div>
        <div class="detail-row">
            <span class="detail-label">Payment Status:</span>
            <span class="detail-value"><span class="status-badge payment-${booking.payment_status}">${booking.payment_status}</span></span>
        </div>
        ${booking.special_requests ? `
        <div class="detail-row">
            <span class="detail-label">Special Requests:</span>
            <span class="detail-value">${booking.special_requests}</span>
        </div>
        ` : ''}
        <div class="detail-row">
            <span class="detail-label">Created:</span>
            <span class="detail-value">${new Date(booking.created_at).toLocaleString()}</span>
        </div>
    `;
    
    document.getElementById('viewModalContent').innerHTML = content;
    document.getElementById('viewModal').classList.add('active');
}

function editBooking(id) {
    const booking = bookings.find(b => b.id == id);
    if (!booking) return;
    
    document.getElementById('edit_booking_id').value = id;
    document.getElementById('edit_status').value = booking.booking_status;
    document.getElementById('edit_payment_status').value = booking.payment_status;
    
    document.getElementById('editModal').classList.add('active');
}

function closeModal(modalId) {
    document.getElementById(modalId).classList.remove('active');
}

// Close modal on outside click
document.querySelectorAll('.modal').forEach(modal => {
    modal.addEventListener('click', (e) => {
        if (e.target === modal) {
            modal.classList.remove('active');
        }
    });
});
</script>

</body>
</html>