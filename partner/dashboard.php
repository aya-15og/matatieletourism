<?php
session_start();
require __DIR__ . '/../includes/functions.php';
require __DIR__ . '/../includes/db.php';

// Check if partner is logged in
if (!isset($_SESSION['partner_logged_in']) || $_SESSION['partner_logged_in'] !== true) {
    header('Location: /kokstad/partner/login.php');
    exit;
}

$partner_user_id = $_SESSION['partner_user_id'];
$accommodation_id = $_SESSION['partner_accommodation_id'];

// Get accommodation details
$stmt = $pdo->prepare("SELECT * FROM accommodations WHERE id = ?");
$stmt->execute([$accommodation_id]);
$accommodation = $stmt->fetch();

// Get dashboard stats
$statsStmt = $pdo->prepare("SELECT * FROM partner_dashboard_stats WHERE partner_user_id = ?");
$statsStmt->execute([$partner_user_id]);
$stats = $statsStmt->fetch();

if (!$stats) {
    // Fallback if view doesn't work
    $stats = [
        'total_rooms' => 0,
        'total_bookings' => 0,
        'bookings_this_month' => 0,
        'pending_inquiries' => 0,
        'total_earnings' => 0,
        'earnings_this_month' => 0,
        'total_views' => $accommodation['views'] ?? 0
    ];
}

// Get recent bookings
$recentBookingsStmt = $pdo->prepare("
    SELECT b.*, rt.name as room_name 
    FROM bookings b 
    LEFT JOIN room_types rt ON b.room_type_id = rt.id
    WHERE b.accommodation_id = ? 
    ORDER BY b.created_at DESC 
    LIMIT 5
");
$recentBookingsStmt->execute([$accommodation_id]);
$recentBookings = $recentBookingsStmt->fetchAll();

// Get recent inquiries
$recentInquiriesStmt = $pdo->prepare("
    SELECT * FROM accommodation_inquiries 
    WHERE accommodation_id = ? 
    ORDER BY created_at DESC 
    LIMIT 5
");
$recentInquiriesStmt->execute([$accommodation_id]);
$recentInquiries = $recentInquiriesStmt->fetchAll();

// Get unread notifications
$notificationsStmt = $pdo->prepare("
    SELECT * FROM partner_notifications 
    WHERE partner_user_id = ? AND is_read = 0 
    ORDER BY created_at DESC 
    LIMIT 5
");
$notificationsStmt->execute([$partner_user_id]);
$notifications = $notificationsStmt->fetchAll();

$page_title = 'Dashboard - Partner Portal';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $page_title ?></title>
    <link rel="stylesheet" href="/kokstad/partner/assets/css/partner-dashboard.css">
</head>
<body>
    <!-- Sidebar Navigation -->
    <aside class="sidebar">
        <div class="sidebar-header">
            <h2>🏨 Partner Portal</h2>
            <p><?= h($accommodation['name']) ?></p>
        </div>
        
        <nav class="sidebar-nav">
            <a href="/kokstad/partner/dashboard.php" class="nav-item active">
                <span class="nav-icon">📊</span>
                <span class="nav-text">Dashboard</span>
            </a>
            <a href="/kokstad/partner/property-details.php" class="nav-item">
                <span class="nav-icon">🏢</span>
                <span class="nav-text">Property Details</span>
            </a>
            <a href="/kokstad/partner/rooms.php" class="nav-item">
                <span class="nav-icon">🛏️</span>
                <span class="nav-text">Rooms & Pricing</span>
            </a>
            <a href="/kokstad/partner/bookings.php" class="nav-item">
                <span class="nav-icon">📅</span>
                <span class="nav-text">Bookings</span>
                <?php if ($stats['pending_inquiries'] > 0): ?>
                    <span class="badge"><?= $stats['pending_inquiries'] ?></span>
                <?php endif; ?>
            </a>
            <a href="/kokstad/partner/inquiries.php" class="nav-item">
                <span class="nav-icon">📧</span>
                <span class="nav-text">Inquiries</span>
            </a>
            <a href="/kokstad/partner/images.php" class="nav-item">
                <span class="nav-icon">🖼️</span>
                <span class="nav-text">Images</span>
            </a>
            <a href="/kokstad/partner/earnings.php" class="nav-item">
                <span class="nav-icon">💰</span>
                <span class="nav-text">Earnings</span>
            </a>
            <a href="/kokstad/partner/settings.php" class="nav-item">
                <span class="nav-icon">⚙️</span>
                <span class="nav-text">Settings</span>
            </a>
        </nav>
        
        <div class="sidebar-footer">
            <a href="/kokstad/partner/logout.php" class="nav-item logout">
                <span class="nav-icon">🚪</span>
                <span class="nav-text">Logout</span>
            </a>
        </div>
    </aside>

    <!-- Main Content -->
    <main class="main-content">
        <!-- Top Header -->
        <header class="top-header">
            <div class="header-left">
                <h1>Dashboard</h1>
                <p>Welcome back, <?= h($_SESSION['partner_name']) ?>!</p>
            </div>
            <div class="header-right">
                <a href="/kokstad/accommodation.php?id=<?= $accommodation_id ?>" target="_blank" class="btn btn-outline">
                    👁️ View Live Page
                </a>
                <div class="notifications-dropdown">
                    <button class="notifications-btn">
                        🔔
                        <?php if (count($notifications) > 0): ?>
                            <span class="notification-badge"><?= count($notifications) ?></span>
                        <?php endif; ?>
                    </button>
                </div>
            </div>
        </header>

        <!-- Stats Grid -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon" style="background: #dbeafe;">📊</div>
                <div class="stat-content">
                    <div class="stat-value"><?= number_format($stats['total_views']) ?></div>
                    <div class="stat-label">Total Views</div>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon" style="background: #dcfce7;">✅</div>
                <div class="stat-content">
                    <div class="stat-value"><?= $stats['total_bookings'] ?></div>
                    <div class="stat-label">Total Bookings</div>
                    <div class="stat-change positive">+<?= $stats['bookings_this_month'] ?> this month</div>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon" style="background: #fef3c7;">📧</div>
                <div class="stat-content">
                    <div class="stat-value"><?= $stats['pending_inquiries'] ?></div>
                    <div class="stat-label">Pending Inquiries</div>
                </div>
            </div>

            <div class="stat-card highlight">
                <div class="stat-icon" style="background: #dcfce7;">💰</div>
                <div class="stat-content">
                    <div class="stat-value">R<?= number_format($stats['total_earnings'], 2) ?></div>
                    <div class="stat-label">Total Earnings</div>
                    <div class="stat-change">R<?= number_format($stats['earnings_this_month'], 2) ?> this month</div>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon" style="background: #e0e7ff;">🛏️</div>
                <div class="stat-content">
                    <div class="stat-value"><?= $stats['total_rooms'] ?></div>
                    <div class="stat-label">Active Rooms</div>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon" style="background: <?= $accommodation['is_partner'] ? '#dcfce7' : '#fee2e2' ?>;">
                    <?= $accommodation['is_partner'] ? '✅' : '⚠️' ?>
                </div>
                <div class="stat-content">
                    <div class="stat-value"><?= $accommodation['is_partner'] ? 'Active' : 'Inactive' ?></div>
                    <div class="stat-label">Partner Status</div>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="quick-actions">
            <h2>Quick Actions</h2>
            <div class="actions-grid">
                <a href="/kokstad/partner/rooms.php?action=add" class="action-card">
                    <span class="action-icon">➕</span>
                    <span class="action-text">Add New Room</span>
                </a>
                <a href="/kokstad/partner/property-details.php" class="action-card">
                    <span class="action-icon">✏️</span>
                    <span class="action-text">Edit Property</span>
                </a>
                <a href="/kokstad/partner/images.php" class="action-card">
                    <span class="action-icon">📸</span>
                    <span class="action-text">Upload Images</span>
                </a>
                <a href="/kokstad/partner/bookings.php" class="action-card">
                    <span class="action-icon">📅</span>
                    <span class="action-text">View Bookings</span>
                </a>
            </div>
        </div>

        <!-- Content Grid -->
        <div class="content-grid">
            <!-- Recent Bookings -->
            <div class="content-card">
                <div class="card-header">
                    <h3>Recent Bookings</h3>
                    <a href="/kokstad/partner/bookings.php" class="card-link">View All →</a>
                </div>
                <div class="card-body">
                    <?php if (empty($recentBookings)): ?>
                        <div class="empty-state">
                            <p>No bookings yet</p>
                        </div>
                    <?php else: ?>
                        <div class="bookings-list">
                            <?php foreach ($recentBookings as $booking): ?>
                                <div class="booking-item">
                                    <div class="booking-info">
                                        <strong><?= h($booking['guest_name']) ?></strong>
                                        <span class="booking-room"><?= h($booking['room_name']) ?></span>
                                        <span class="booking-dates">
                                            <?= date('M d', strtotime($booking['check_in'])) ?> - 
                                            <?= date('M d, Y', strtotime($booking['check_out'])) ?>
                                        </span>
                                    </div>
                                    <div class="booking-status">
                                        <span class="status-badge status-<?= h($booking['booking_status']) ?>">
                                            <?= ucfirst(h($booking['booking_status'])) ?>
                                        </span>
                                        <span class="booking-amount">R<?= number_format($booking['total_amount'], 2) ?></span>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Recent Inquiries -->
            <div class="content-card">
                <div class="card-header">
                    <h3>Recent Inquiries</h3>
                    <a href="/kokstad/partner/inquiries.php" class="card-link">View All →</a>
                </div>
                <div class="card-body">
                    <?php if (empty($recentInquiries)): ?>
                        <div class="empty-state">
                            <p>No inquiries yet</p>
                        </div>
                    <?php else: ?>
                        <div class="inquiries-list">
                            <?php foreach ($recentInquiries as $inquiry): ?>
                                <div class="inquiry-item">
                                    <div class="inquiry-header">
                                        <strong><?= h($inquiry['guest_name']) ?></strong>
                                        <span class="inquiry-date"><?= date('M d, Y', strtotime($inquiry['created_at'])) ?></span>
                                    </div>
                                    <div class="inquiry-message"><?= h(substr($inquiry['message'], 0, 100)) ?>...</div>
                                    <div class="inquiry-meta">
                                        <span><?= h($inquiry['guest_email']) ?></span>
                                        <?php if ($inquiry['check_in']): ?>
                                            <span><?= date('M d', strtotime($inquiry['check_in'])) ?> - <?= date('M d', strtotime($inquiry['check_out'])) ?></span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </main>

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            background: #f3f4f6;
            display: flex;
            min-height: 100vh;
        }

        /* Sidebar */
        .sidebar {
            width: 280px;
            background: linear-gradient(180deg, #2c5282 0%, #1e3a5f 100%);
            color: white;
            display: flex;
            flex-direction: column;
            position: fixed;
            height: 100vh;
            overflow-y: auto;
        }

        .sidebar-header {
            padding: 30px 20px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }

        .sidebar-header h2 {
            font-size: 1.5rem;
            margin-bottom: 8px;
        }

        .sidebar-header p {
            font-size: 0.9rem;
            opacity: 0.8;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .sidebar-nav {
            flex: 1;
            padding: 20px 0;
        }

        .nav-item {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 14px 20px;
            color: rgba(255, 255, 255, 0.8);
            text-decoration: none;
            transition: all 0.3s ease;
            position: relative;
        }

        .nav-item:hover {
            background: rgba(255, 255, 255, 0.1);
            color: white;
        }

        .nav-item.active {
            background: rgba(255, 255, 255, 0.15);
            color: white;
            border-left: 4px solid white;
        }

        .nav-icon {
            font-size: 1.3rem;
        }

        .badge {
            position: absolute;
            right: 20px;
            background: #ef4444;
            color: white;
            padding: 2px 8px;
            border-radius: 10px;
            font-size: 0.75rem;
            font-weight: 700;
        }

        .sidebar-footer {
            padding: 20px 0;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
        }

        .nav-item.logout:hover {
            background: rgba(239, 68, 68, 0.2);
        }

        /* Main Content */
        .main-content {
            margin-left: 280px;
            flex: 1;
            padding: 30px;
        }

        .top-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 40px;
        }

        .top-header h1 {
            font-size: 2rem;
            color: #111827;
            margin-bottom: 5px;
        }

        .top-header p {
            color: #6b7280;
        }

        .header-right {
            display: flex;
            gap: 15px;
            align-items: center;
        }

        .btn {
            padding: 10px 20px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .btn-outline {
            border: 2px solid #2c5282;
            color: #2c5282;
            background: white;
        }

        .btn-outline:hover {
            background: #2c5282;
            color: white;
        }

        .notifications-btn {
            position: relative;
            background: white;
            border: 2px solid #e5e7eb;
            border-radius: 8px;
            padding: 10px 15px;
            font-size: 1.2rem;
            cursor: pointer;
        }

        .notification-badge {
            position: absolute;
            top: -5px;
            right: -5px;
            background: #ef4444;
            color: white;
            padding: 2px 6px;
            border-radius: 10px;
            font-size: 0.7rem;
            font-weight: 700;
        }

        /* Stats Grid */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 40px;
        }

        .stat-card {
            background: white;
            border-radius: 12px;
            padding: 24px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
            display: flex;
            align-items: center;
            gap: 20px;
            transition: all 0.3s ease;
        }

        .stat-card:hover {
            box-shadow: 0 4px 16px rgba(0, 0, 0, 0.1);
            transform: translateY(-2px);
        }

        .stat-card.highlight {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }

        .stat-icon {
            width: 60px;
            height: 60px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.8rem;
        }

        .stat-card.highlight .stat-icon {
            background: rgba(255, 255, 255, 0.2) !important;
        }

        .stat-value {
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 5px;
        }

        .stat-label {
            font-size: 0.9rem;
            color: #6b7280;
        }

        .stat-card.highlight .stat-label {
            color: rgba(255, 255, 255, 0.9);
        }

        .stat-change {
            font-size: 0.85rem;
            color: #6b7280;
            margin-top: 5px;
        }

        .stat-change.positive {
            color: #10b981;
        }

        /* Quick Actions */
        .quick-actions {
            margin-bottom: 40px;
        }

        .quick-actions h2 {
            margin-bottom: 20px;
            color: #111827;
        }

        .actions-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
        }

        .action-card {
            background: white;
            border-radius: 12px;
            padding: 24px;
            text-align: center;
            text-decoration: none;
            color: #111827;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
            transition: all 0.3s ease;
        }

        .action-card:hover {
            box-shadow: 0 4px 16px rgba(0, 0, 0, 0.1);
            transform: translateY(-4px);
        }

        .action-icon {
            font-size: 3rem;
            display: block;
            margin-bottom: 12px;
        }

        .action-text {
            font-weight: 600;
        }

        /* Content Grid */
        .content-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(450px, 1fr));
            gap: 30px;
        }

        .content-card {
            background: white;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
            overflow: hidden;
        }

        .card-header {
            padding: 20px 24px;
            border-bottom: 1px solid #e5e7eb;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .card-header h3 {
            color: #111827;
            font-size: 1.2rem;
        }

        .card-link {
            color: #2c5282;
            text-decoration: none;
            font-weight: 600;
            font-size: 0.9rem;
        }

        .card-body {
            padding: 24px;
        }

        .empty-state {
            text-align: center;
            padding: 40px 20px;
            color: #9ca3af;
        }

        /* Bookings List */
        .booking-item {
            display: flex;
            justify-content: space-between;
            padding: 16px;
            border-bottom: 1px solid #f3f4f6;
        }

        .booking-item:last-child {
            border-bottom: none;
        }

        .booking-info strong {
            display: block;
            margin-bottom: 6px;
        }

        .booking-room, .booking-dates {
            display: block;
            font-size: 0.85rem;
            color: #6b7280;
            margin-top: 4px;
        }

        .booking-status {
            text-align: right;
        }

        .status-badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
            margin-bottom: 6px;
        }

        .status-confirmed {
            background: #dcfce7;
            color: #065f46;
        }

        .status-pending {
            background: #fef3c7;
            color: #78350f;
        }

        .booking-amount {
            display: block;
            font-weight: 700;
            color: #111827;
            margin-top: 6px;
        }

        /* Inquiries List */
        .inquiry-item {
            padding: 16px;
            border-bottom: 1px solid #f3f4f6;
        }

        .inquiry-item:last-child {
            border-bottom: none;
        }

        .inquiry-header {
            display: flex;
            justify-content: space-between;
            margin-bottom: 8px;
        }

        .inquiry-date {
            font-size: 0.85rem;
            color: #6b7280;
        }

        .inquiry-message {
            font-size: 0.9rem;
            color: #4b5563;
            margin-bottom: 10px;
        }

        .inquiry-meta {
            font-size: 0.85rem;
            color: #6b7280;
            display: flex;
            gap: 15px;
        }

        @media (max-width: 1024px) {
            .sidebar {
                width: 240px;
            }

            .main-content {
                margin-left: 240px;
            }

            .content-grid {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 768px) {
            .sidebar {
                width: 100%;
                height: auto;
                position: relative;
            }

            .main-content {
                margin-left: 0;
            }

            .top-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 20px;
            }

            .stats-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</body>
</html>