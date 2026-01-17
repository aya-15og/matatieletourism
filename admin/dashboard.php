<?php
session_start();
if (!isset($_SESSION['admin'])) {
    header("Location: login.php");
    exit;
}
require '../includes/config.php';

// quick stats
$counts = [
  'attractions' => $pdo->query("SELECT COUNT(*) FROM attractions")->fetchColumn(),
  'stay'        => $pdo->query("SELECT COUNT(*) FROM stays")->fetchColumn(),
  'activities'  => $pdo->query("SELECT COUNT(*) FROM activities")->fetchColumn(),
  'dining'      => $pdo->query("SELECT COUNT(*) FROM dining")->fetchColumn(),
  'messages'    => $pdo->query("SELECT COUNT(*) FROM contact_messages")->fetchColumn()
];

// Bookings stats
try {
    $counts['bookings_total'] = $pdo->query("SELECT COUNT(*) FROM bookings")->fetchColumn();
    $counts['bookings_pending'] = $pdo->query("SELECT COUNT(*) FROM bookings WHERE booking_status='pending'")->fetchColumn();
    $counts['bookings_confirmed'] = $pdo->query("SELECT COUNT(*) FROM bookings WHERE booking_status='confirmed'")->fetchColumn();
    $counts['bookings_partner'] = $pdo->query("SELECT COUNT(*) FROM bookings WHERE is_partner_booking=1")->fetchColumn();
    $counts['bookings_revenue'] = $pdo->query("SELECT SUM(commission_amount) FROM bookings WHERE booking_status='confirmed' AND is_partner_booking=1")->fetchColumn() ?: 0;
    $counts['partners_active'] = $pdo->query("SELECT COUNT(*) FROM stays WHERE is_partner=1")->fetchColumn();
} catch (PDOException $e) {
    $counts['bookings_total'] = 0;
    $counts['bookings_pending'] = 0;
    $counts['bookings_confirmed'] = 0;
    $counts['bookings_partner'] = 0;
    $counts['bookings_revenue'] = 0;
    $counts['partners_active'] = 0;
}

// Room types stats
try {
    $counts['room_types'] = $pdo->query("SELECT COUNT(*) FROM room_types")->fetchColumn();
} catch (PDOException $e) {
    $counts['room_types'] = 0;
}

// New counts for sponsors, ads, and businesses
try {
    $counts['sponsors'] = $pdo->query("SELECT COUNT(*) FROM sponsors")->fetchColumn();
    $counts['sponsors_visible'] = $pdo->query("SELECT COUNT(*) FROM sponsors WHERE visible=1")->fetchColumn();
} catch (PDOException $e) {
    $counts['sponsors'] = 0;
    $counts['sponsors_visible'] = 0;
}

try {
    $counts['ads'] = $pdo->query("SELECT COUNT(*) FROM ads")->fetchColumn();
    $counts['ads_active'] = $pdo->query("SELECT COUNT(*) FROM ads WHERE visible=1")->fetchColumn();
} catch (PDOException $e) {
    $counts['ads'] = 0;
    $counts['ads_active'] = 0;
}

try {
    $counts['businesses'] = $pdo->query("SELECT COUNT(*) FROM businesses")->fetchColumn();
    $counts['businesses_visible'] = $pdo->query("SELECT COUNT(*) FROM businesses WHERE visible=1")->fetchColumn();
} catch (PDOException $e) {
    $counts['businesses'] = 0;
    $counts['businesses_visible'] = 0;
}

// Events stats
try {
    $counts['events'] = $pdo->query("SELECT COUNT(*) FROM events")->fetchColumn();
    $counts['events_visible'] = $pdo->query("SELECT COUNT(*) FROM events WHERE visible=1")->fetchColumn();
    $counts['events_upcoming'] = $pdo->query("SELECT COUNT(*) FROM events WHERE start_date >= CURDATE() AND visible=1")->fetchColumn();
} catch (PDOException $e) {
    $counts['events'] = 0;
    $counts['events_visible'] = 0;
    $counts['events_upcoming'] = 0;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Matatiele Tourism | Admin Dashboard</title>
<link rel="stylesheet" href="../assets/admin.css">
<style>
.section-header {
  margin: 3rem 0 1.5rem 0;
  padding-bottom: 0.5rem;
  border-bottom: 3px solid #667eea;
  color: #333;
}
.section-header:first-of-type {
  margin-top: 2rem;
}
.card.new-feature {
  border: 2px solid #667eea;
  position: relative;
}
.card.new-feature::before {
  content: "NEW";
  position: absolute;
  top: -10px;
  right: 15px;
  background: #667eea;
  color: white;
  padding: 0.3rem 0.8rem;
  border-radius: 12px;
  font-size: 0.75rem;
  font-weight: 700;
  letter-spacing: 1px;
}
.card.featured-card {
  border: 2px solid #10b981;
  position: relative;
  background: linear-gradient(135deg, #f0fdf4 0%, #dcfce7 100%);
}
.card.featured-card::before {
  content: "⭐ FEATURED";
  position: absolute;
  top: -10px;
  right: 15px;
  background: #10b981;
  color: white;
  padding: 0.3rem 0.8rem;
  border-radius: 12px;
  font-size: 0.75rem;
  font-weight: 700;
  letter-spacing: 1px;
}
.count-badge {
  display: inline-block;
  background: #e8f5e9;
  color: #2e7d32;
  padding: 0.3rem 0.8rem;
  border-radius: 15px;
  font-size: 0.85rem;
  font-weight: 600;
  margin-left: 0.5rem;
}
.count-badge.warning {
  background: #fff3cd;
  color: #856404;
}
.count-badge.primary {
  background: #dbeafe;
  color: #1e40af;
}
.revenue-highlight {
  font-size: 1.3rem;
  font-weight: 700;
  color: #10b981;
  margin-top: 0.5rem;
  display: block;
}
.mini-stats {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 0.5rem;
  margin-top: 1rem;
  padding-top: 1rem;
  border-top: 1px solid rgba(0,0,0,0.1);
}
.mini-stat {
  display: flex;
  flex-direction: column;
}
.mini-stat-label {
  font-size: 0.75rem;
  color: #6b7280;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 0.05em;
}
.mini-stat-value {
  font-size: 1.25rem;
  font-weight: 700;
  color: #1f2937;
  margin-top: 0.25rem;
}
</style>
</head>
<body>
<header>
  <div><strong>Matatiele Tourism Admin</strong></div>
  <nav>
    <a href="../index.php" target="_blank">View Site</a>
    <a href="logout.php">Logout</a>
  </nav>
</header>

<main>
<h1>Welcome, <?= htmlspecialchars($_SESSION['admin']) ?></h1>
<p>Manage all content on the Matatiele Tourism website from one place.</p>

<!-- BOOKINGS & REVENUE SECTION -->
<h2 class="section-header">🏨 Bookings & Revenue Management</h2>
<div class="grid">
    <div class="card featured-card">
      <h2>📋 Bookings Dashboard</h2>
      <p>View and manage all accommodation bookings and inquiries.</p>
      <p class="count"><?= $counts['bookings_total'] ?> total bookings
        <?php if ($counts['bookings_pending'] > 0): ?>
        <span class="count-badge warning"><?= $counts['bookings_pending'] ?> pending</span>
        <?php endif; ?>
      </p>
      <span class="revenue-highlight">R <?= number_format($counts['bookings_revenue'], 2) ?></span>
      <small style="color: #6b7280;">Total Commission Earned</small>
      
      <div class="mini-stats">
        <div class="mini-stat">
          <span class="mini-stat-label">Confirmed</span>
          <span class="mini-stat-value"><?= $counts['bookings_confirmed'] ?></span>
        </div>
        <div class="mini-stat">
          <span class="mini-stat-label">Partner</span>
          <span class="mini-stat-value"><?= $counts['bookings_partner'] ?></span>
        </div>
      </div>
      
      <div class="buttons" style="margin-top: 1rem;">
        <a href="bookings.php" class="edit">View All Bookings</a>
      </div>
    </div>

    <div class="card new-feature">
      <h2>🛏️ Room Types & Inventory</h2>
      <p>Manage different room types, pricing, and availability for partner properties.</p>
      <p class="count"><?= $counts['room_types'] ?> room types configured
        <span class="count-badge primary"><?= $counts['partners_active'] ?> partners</span>
      </p>
      <div class="mini-stats">
        <div class="mini-stat">
          <span class="mini-stat-label">Standard</span>
          <span class="mini-stat-value">
            <?php
            try {
              echo $pdo->query("SELECT COUNT(*) FROM room_types WHERE room_name LIKE '%Standard%'")->fetchColumn();
            } catch (PDOException $e) {
              echo '0';
            }
            ?>
          </span>
        </div>
        <div class="mini-stat">
          <span class="mini-stat-label">Suites</span>
          <span class="mini-stat-value">
            <?php
            try {
              echo $pdo->query("SELECT COUNT(*) FROM room_types WHERE room_name LIKE '%Suite%'")->fetchColumn();
            } catch (PDOException $e) {
              echo '0';
            }
            ?>
          </span>
        </div>
      </div>
      <div class="buttons" style="margin-top: 1rem;">
        <a href="manage_rooms.php" class="edit">Manage Room Types</a>
      </div>
    </div>
</div>

<!-- CORE CONTENT SECTION -->
<h2 class="section-header">📍 Core Tourism Content</h2>
<div class="grid">
    <div class="card">
      <h2>Attractions</h2>
      <p>Manage natural, cultural and historic attractions.</p>
      <p class="count"><?= $counts['attractions'] ?> entries</p>
      <div class="buttons">
        <a href="manage_attractions.php" class="edit">Manage</a>
      </div>
    </div>

    <div class="card">
      <h2>Accommodation Properties</h2>
      <p>Manage all accommodation properties, partner status, and basic details.</p>
      <p class="count"><?= $counts['stay'] ?> properties
        <?php if ($counts['partners_active'] > 0): ?>
        <span class="count-badge"><?= $counts['partners_active'] ?> partners</span>
        <?php endif; ?>
      </p>
      <div class="buttons">
        <a href="edit_stay.php" class="edit">Manage Properties</a>
      </div>
    </div>

    <div class="card new-feature">
      <h2>📅 Events & Happenings</h2>
      <p>Manage upcoming events with slideshow display on homepage sidebar.</p>
      <p class="count"><?= $counts['events'] ?> events
        <?php if ($counts['events_upcoming'] > 0): ?>
        <span class="count-badge primary"><?= $counts['events_upcoming'] ?> upcoming</span>
        <?php endif; ?>
        <span class="count-badge"><?= $counts['events_visible'] ?> visible</span>
      </p>
      <div class="buttons">
        <a href="manage_events.php" class="edit">Manage Events</a>
      </div>
    </div>

    <div class="card">
      <h2>Things to Do</h2>
      <p>Manage festivals, annual gatherings and local events.</p>
      <p class="count"><?= $counts['activities'] ?> entries</p>
      <div class="buttons">
        <a href="edit_things-to-do.php" class="edit">Manage</a>
      </div>
    </div>

    <div class="card">
      <h2>Dining</h2>
      <p>Manage restaurants, cafés, and local cuisine.</p>
      <p class="count"><?= $counts['dining'] ?> entries</p>
      <div class="buttons">
        <a href="manage_dining.php" class="edit">Manage</a>
      </div>
    </div>
</div>

<!-- BUSINESS & MARKETING SECTION -->
<h2 class="section-header">💼 Business Directory & Marketing</h2>
<div class="grid">
    <div class="card new-feature">
      <h2>Business Directory</h2>
      <p>Manage local businesses, services, and contact information.</p>
      <p class="count"><?= $counts['businesses'] ?> businesses
        <span class="count-badge"><?= $counts['businesses_visible'] ?> visible</span>
      </p>
      <div class="buttons">
        <a href="manage_businesses.php" class="edit">Manage Directory</a>
      </div>
    </div>

    <div class="card new-feature">
      <h2>Sponsors & Partners</h2>
      <p>Manage sponsor logos and partnership information displayed on homepage.</p>
      <p class="count"><?= $counts['sponsors'] ?> sponsors
        <span class="count-badge"><?= $counts['sponsors_visible'] ?> visible</span>
      </p>
      <div class="buttons">
        <a href="manage_sponsors.php" class="edit">Manage Sponsors</a>
      </div>
    </div>

    <div class="card new-feature">
      <h2>Advertisements</h2>
      <p>Manage ad banners on homepage (top, sidebar, bottom positions).</p>
      <p class="count"><?= $counts['ads'] ?> ads
        <span class="count-badge"><?= $counts['ads_active'] ?> active</span>
      </p>
      <div class="buttons">
        <a href="manage_ads.php" class="edit">Manage Ads</a>
      </div>
    </div>
</div>

<!-- MEDIA & CONTENT SECTION -->
<h2 class="section-header">🖼️ Media & Visual Content</h2>
<div class="grid">
    <div class="card">
      <h2>Homepage Images</h2>
      <p>Manage hero, attraction, stay, and gallery images.</p>
      <p class="count">Dynamic banners and cards</p>
      <div class="buttons">
        <a href="manage_home_images.php" class="edit">Manage Images</a>
      </div>
    </div>
    
    <div class="card">
      <h2>Gallery</h2>
      <p>Manage photo gallery images displayed on the website.</p>
      <p class="count">
        <?php
        try {
          $gallery_count = $pdo->query("SELECT COUNT(*) FROM gallery")->fetchColumn();
          $gallery_featured = $pdo->query("SELECT COUNT(*) FROM gallery WHERE featured=1")->fetchColumn();
          echo $gallery_count . ' images';
          if ($gallery_featured > 0) {
            echo ' <span class="count-badge">' . $gallery_featured . ' featured</span>';
          }
        } catch (PDOException $e) {
          echo 'Photo gallery';
        }
        ?>
      </p>
      <div class="buttons">
        <a href="manage_gallery.php" class="edit">Manage Gallery</a>
      </div>
    </div>
</div>

<!-- VISITOR ENGAGEMENT SECTION -->
<h2 class="section-header">📊 Visitor Engagement & Analytics</h2>
<div class="grid">
    <div class="card">
      <h2>Messages</h2>
      <p>Review contact form messages from website visitors.</p>
      <p class="count"><?= $counts['messages'] ?> messages</p>
      <div class="buttons">
        <a href="manage_contacts.php" class="edit">View Messages</a>
      </div>
    </div>

    <div class="card">
      <h2>Visitor Analytics</h2>
      <p>View website traffic, visitor statistics and page views.</p>
      <p class="count">📊 Analytics</p>
      <div class="buttons">
        <a href="visitors.php" class="edit">View Analytics</a>
      </div>
    </div>
</div>

<!-- QUICK TIPS BOX - EVENTS -->
<div style="background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%); color: white; padding: 2rem; border-radius: 12px; margin-top: 3rem;">
  <h3 style="margin: 0 0 1rem 0; font-size: 1.5rem;">📅 Events Management Guide</h3>
  
  <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 2rem; margin: 1.5rem 0;">
    <div>
      <h4 style="margin: 0 0 0.75rem 0; font-size: 1.1rem;">🎨 Slideshow Display</h4>
      <ul style="margin: 0; padding-left: 1.5rem; opacity: 0.95; line-height: 1.8;">
        <li><strong>Featured Events:</strong> Events with images appear in slideshow</li>
        <li><strong>Auto-Rotate:</strong> Changes every 5 seconds automatically</li>
        <li><strong>Manual Controls:</strong> Visitors can navigate with arrows/dots</li>
        <li><strong>Image Size:</strong> Upload 800x600px or larger for best quality</li>
        <li><strong>Auto-Resize:</strong> Images cropped to 400x300px perfectly</li>
      </ul>
    </div>
    <div>
      <h4 style="margin: 0 0 0.75rem 0; font-size: 1.1rem;">📋 Event Details</h4>
      <ul style="margin: 0; padding-left: 1.5rem; opacity: 0.95; line-height: 1.8;">
        <li><strong>Date Range:</strong> Single day or multi-day events</li>
        <li><strong>Location:</strong> Venue name displays on event card</li>
        <li><strong>External Links:</strong> Link to ticket sales or more info</li>
        <li><strong>Display Order:</strong> Control which events show first</li>
        <li><strong>Visibility Toggle:</strong> Show/hide without deleting</li>
      </ul>
    </div>
  </div>
  
  <div style="background: rgba(255,255,255,0.1); padding: 1.5rem; border-radius: 8px; margin-top: 1.5rem;">
    <h4 style="margin: 0 0 0.75rem 0; font-size: 1.1rem;">💡 Best Practices</h4>
    <p style="margin: 0; opacity: 0.95; line-height: 1.6;">
      <strong>Images:</strong> Use high-quality event posters or venue photos. The system automatically crops to fit the sidebar widget perfectly.<br>
      <strong>Upcoming Only:</strong> Only events with start dates in the future display on the homepage.<br>
      <strong>Multiple Events:</strong> Add as many as you like - the slideshow handles them all beautifully!
    </p>
  </div>
</div>

<!-- QUICK TIPS BOX - BOOKINGS & ROOMS -->
<div style="background: linear-gradient(135deg, #10b981 0%, #059669 100%); color: white; padding: 2rem; border-radius: 12px; margin-top: 2rem;">
  <h3 style="margin: 0 0 1rem 0; font-size: 1.5rem;">💡 Accommodation Management Guide</h3>
  
  <div style="background: rgba(255,255,255,0.1); padding: 1.5rem; border-radius: 8px; margin-bottom: 1.5rem;">
    <h4 style="margin: 0 0 0.75rem 0; font-size: 1.1rem;">🏨 Two-Step Setup</h4>
    <p style="margin: 0 0 1rem 0; opacity: 0.95; line-height: 1.6;">
      <strong>Step 1: Accommodation Properties</strong> - Create the main property listing (hotel, guesthouse, B&B)<br>
      <strong>Step 2: Room Types</strong> - For partner properties, add different room types with individual pricing
    </p>
  </div>
  
  <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 2rem; margin: 1.5rem 0;">
    <div>
      <h4 style="margin: 0 0 0.75rem 0; font-size: 1.1rem;">🤝 Partner Properties</h4>
      <ul style="margin: 0; padding-left: 1.5rem; opacity: 0.95; line-height: 1.8;">
        <li><strong>Multiple Room Types:</strong> Standard, Deluxe, Suite, Family</li>
        <li><strong>Individual Pricing:</strong> Each room type has its own rate</li>
        <li><strong>Room Inventory:</strong> Track available rooms per type</li>
        <li><strong>Instant Booking:</strong> Automatic confirmation</li>
        <li><strong>Commission:</strong> Earn 9.5% on each booking</li>
      </ul>
    </div>
    <div>
      <h4 style="margin: 0 0 0.75rem 0; font-size: 1.1rem;">📧 Non-Partner Properties</h4>
      <ul style="margin: 0; padding-left: 1.5rem; opacity: 0.95; line-height: 1.8;">
        <li><strong>Simple Listing:</strong> One price or "Contact for rates"</li>
        <li><strong>No Room Types:</strong> General property listing only</li>
        <li><strong>Inquiry Only:</strong> No instant confirmation</li>
        <li><strong>Email Forwarding:</strong> Sent to property</li>
        <li><strong>Partnership Invitation:</strong> In every inquiry</li>
      </ul>
    </div>
  </div>
  
  <p style="margin: 1rem 0 0 0; opacity: 0.95; background: rgba(255,255,255,0.1); padding: 1rem; border-radius: 8px;">
    <strong>💰 Example:</strong> "Mountain View Lodge" (property) can have: Standard Room (R650), Deluxe Room (R950), Family Suite (R1450), Executive Suite (R1850). Guests select their preferred room type when booking.
  </p>
</div>

<!-- BUSINESS DIRECTORY TIPS BOX -->
<div style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 2rem; border-radius: 12px; margin-top: 2rem;">
  <h3 style="margin: 0 0 1rem 0; font-size: 1.5rem;">💡 Business Directory Tips</h3>
  <p style="margin: 0 0 1rem 0; opacity: 0.95; line-height: 1.6;">
    The Business Directory is perfect for listing local services and small businesses. Consider adding:
  </p>
  <ul style="margin: 0; padding-left: 1.5rem; opacity: 0.95; line-height: 1.8;">
    <li><strong>Tradespeople:</strong> Electricians, Plumbers, Carpenters, Builders, Painters</li>
    <li><strong>Home Services:</strong> Handymen, Gardeners, Cleaners, Pest Control</li>
    <li><strong>Food Services:</strong> Bakers, Caterers, Food Trucks, Butchers</li>
    <li><strong>Professional Services:</strong> Accountants, Lawyers, Insurance Agents</li>
    <li><strong>Automotive:</strong> Mechanics, Auto Electricians, Panel Beaters, Car Wash</li>
    <li><strong>Personal Services:</strong> Hairdressers, Barbers, Beauty Salons, Tailors</li>
  </ul>
  <p style="margin: 1rem 0 0 0; opacity: 0.95;">
    <strong>Note:</strong> Accommodation and dining already have dedicated pages, so the directory focuses on other local services.
  </p>
</div>

</main>
</body>
</html>