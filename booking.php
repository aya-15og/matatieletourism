<?php
$page_title = "Book Your Stay";
include 'header.php';
require 'includes/config.php';

// Get stay details
$stay_id = isset($_GET['stay_id']) ? intval($_GET['stay_id']) : 0;

if (!$stay_id) {
    header("Location: stays.php");
    exit;
}

$stmt = $pdo->prepare("SELECT * FROM stays WHERE id = ?");
$stmt->execute([$stay_id]);
$stay = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$stay) {
    header("Location: stays.php");
    exit;
}

// Fetch room types if this is a partner property
$room_types = [];
if ($stay['is_partner']) {
    $stmt = $pdo->prepare("SELECT * FROM room_types WHERE stay_id = ? AND is_available = 1 ORDER BY price_per_night ASC");
    $stmt->execute([$stay_id]);
    $room_types = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function get_stay_image_path($image) {
    if (empty($image)) {
        return 'images/placeholder.jpg';
    }
    $filename = basename($image);
    return 'images/stays/' . $filename;
}

function get_room_image_path($image) {
    if (empty($image)) {
        return 'images/placeholder.jpg';
    }
    $filename = basename($image);
    return 'images/rooms/' . $filename;
}
?>

<main class="booking-main">
    <div class="container">
        <div class="booking-container">
            <!-- Property Preview -->
            <div class="property-preview">
                <a href="stays.php" class="back-link">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <line x1="19" y1="12" x2="5" y2="12"></line>
                        <polyline points="12 19 5 12 12 5"></polyline>
                    </svg>
                    Back to Accommodations
                </a>

                <div class="property-card">
                    <img src="<?= htmlspecialchars(get_stay_image_path($stay['image'])) ?>" 
                         alt="<?= htmlspecialchars($stay['name']) ?>" 
                         class="property-image">
                    
                    <div class="property-info">
                        <div class="property-header">
                            <h2><?= htmlspecialchars($stay['name']) ?></h2>
                            <?php if ($stay['is_partner']): ?>
                            <span class="instant-badge">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor">
                                    <path d="M13 10V3L4 14h7v7l9-11h-7z"/>
                                </svg>
                                Instant Booking
                            </span>
                            <?php endif; ?>
                        </div>

                        <div class="property-location">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path>
                                <circle cx="12" cy="10" r="3"></circle>
                            </svg>
                            <?= htmlspecialchars($stay['location']) ?>
                        </div>

                        <p class="property-description"><?= htmlspecialchars($stay['description']) ?></p>

                        <?php if (!empty($stay['amenities'])): 
                            $amenities = explode(',', $stay['amenities']);
                        ?>
                        <div class="amenities-list">
                            <h4>Property Amenities</h4>
                            <div class="amenities-grid">
                                <?php foreach ($amenities as $amenity): ?>
                                    <span class="amenity-item">
                                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <polyline points="20 6 9 17 4 12"></polyline>
                                        </svg>
                                        <?= htmlspecialchars(trim($amenity)) ?>
                                    </span>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        <?php endif; ?>

                        <div class="property-details">
                            <div class="detail-item">
                                <strong>Check-in:</strong> <?= htmlspecialchars($stay['check_in_time']) ?>
                            </div>
                            <div class="detail-item">
                                <strong>Check-out:</strong> <?= htmlspecialchars($stay['check_out_time']) ?>
                            </div>
                            <?php if (!$stay['is_partner'] || empty($room_types)): ?>
                            <div class="detail-item">
                                <strong>Max Guests:</strong> <?= htmlspecialchars($stay['max_guests']) ?> guests
                            </div>
                            <?php endif; ?>
                        </div>

                        <?php if (!empty($stay['cancellation_policy'])): ?>
                        <div class="cancellation-policy">
                            <h4>Cancellation Policy</h4>
                            <p><?= nl2br(htmlspecialchars($stay['cancellation_policy'])) ?></p>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Booking Form -->
            <div class="booking-form-container">
                <div class="booking-form-card">
                    <h3>
                        <?= $stay['is_partner'] ? 'Complete Your Booking' : 'Send Inquiry' ?>
                    </h3>

                    <?php if (!$stay['is_partner']): ?>
                    <div class="info-notice">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="12" cy="12" r="10"></circle>
                            <line x1="12" y1="16" x2="12" y2="12"></line>
                            <line x1="12" y1="8" x2="12.01" y2="8"></line>
                        </svg>
                        <div>
                            <strong>Inquiry Only</strong>
                            <p>This property is not a partner. Your inquiry will be sent via email and confirmation depends on availability. Response time may vary.</p>
                        </div>
                    </div>
                    <?php elseif (empty($room_types)): ?>
                    <div class="info-notice warning">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"></path>
                            <line x1="12" y1="9" x2="12" y2="13"></line>
                            <line x1="12" y1="17" x2="12.01" y2="17"></line>
                        </svg>
                        <div>
                            <strong>No Rooms Available</strong>
                            <p>This property hasn't configured their room types yet. Please contact them directly or check back later.</p>
                        </div>
                    </div>
                    <?php endif; ?>

                    <form id="bookingForm" method="POST" action="process_booking.php">
                        <input type="hidden" name="stay_id" value="<?= $stay['id'] ?>">
                        <input type="hidden" name="is_partner" value="<?= $stay['is_partner'] ?>">

                        <?php if ($stay['is_partner'] && !empty($room_types)): ?>
                        <!-- Room Type Selection for Partners -->
                        <div class="form-section">
                            <h4 class="section-title">Select Your Room</h4>
                            <div class="room-types-selection">
                                <?php foreach ($room_types as $index => $room): 
                                    $room_images = !empty($room['images']) ? explode(',', $room['images']) : [];
                                    $first_image = !empty($room_images) ? $room_images[0] : '';
                                ?>
                                <label class="room-type-option" for="room_<?= $room['id'] ?>">
                                    <input type="radio" 
                                           name="room_type_id" 
                                           id="room_<?= $room['id'] ?>" 
                                           value="<?= $room['id'] ?>"
                                           data-price="<?= $room['price_per_night'] ?>"
                                           data-max-guests="<?= $room['max_guests'] ?>"
                                           data-room-name="<?= htmlspecialchars($room['room_name']) ?>"
                                           required
                                           <?= $index === 0 ? 'checked' : '' ?>>
                                    
                                    <div class="room-option-card">
                                        <div class="room-option-image">
                                            <img src="<?= htmlspecialchars(get_room_image_path($first_image)) ?>" 
                                                 alt="<?= htmlspecialchars($room['room_name']) ?>">
                                        </div>
                                        
                                        <div class="room-option-details">
                                            <div class="room-option-header">
                                                <h5><?= htmlspecialchars($room['room_name']) ?></h5>
                                                <span class="room-option-price">R <?= number_format($room['price_per_night'], 2) ?></span>
                                            </div>
                                            
                                            <p class="room-option-description">
                                                <?= htmlspecialchars(substr($room['room_description'], 0, 80)) ?><?= strlen($room['room_description']) > 80 ? '...' : '' ?>
                                            </p>
                                            
                                            <div class="room-option-specs">
                                                <span>
                                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                        <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                                                        <circle cx="9" cy="7" r="4"></circle>
                                                        <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                                                        <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                                                    </svg>
                                                    Up to <?= $room['max_guests'] ?> guests
                                                </span>
                                                <span>
                                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                        <rect x="2" y="7" width="20" height="14" rx="2" ry="2"></rect>
                                                        <path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"></path>
                                                    </svg>
                                                    <?= htmlspecialchars($room['bed_configuration']) ?>
                                                </span>
                                                <?php if (!empty($room['room_size'])): ?>
                                                <span>
                                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                        <path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path>
                                                    </svg>
                                                    <?= htmlspecialchars($room['room_size']) ?>
                                                </span>
                                                <?php endif; ?>
                                            </div>
                                            
                                            <?php if (!empty($room['amenities'])): 
                                                $room_amenities = array_slice(explode(',', $room['amenities']), 0, 3);
                                            ?>
                                            <div class="room-option-amenities">
                                                <?php foreach ($room_amenities as $amenity): ?>
                                                    <span class="amenity-chip"><?= htmlspecialchars(trim($amenity)) ?></span>
                                                <?php endforeach; ?>
                                                <?php if (count(explode(',', $room['amenities'])) > 3): ?>
                                                    <span class="amenity-chip more">+<?= count(explode(',', $room['amenities'])) - 3 ?> more</span>
                                                <?php endif; ?>
                                            </div>
                                            <?php endif; ?>
                                            
                                            <?php if ($room['number_of_rooms'] <= 3): ?>
                                            <div class="room-availability-warning">
                                                ⚠️ Only <?= $room['number_of_rooms'] ?> room(s) left
                                            </div>
                                            <?php endif; ?>
                                        </div>
                                        
                                        <div class="room-option-checkmark">
                                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3">
                                                <polyline points="20 6 9 17 4 12"></polyline>
                                            </svg>
                                        </div>
                                    </div>
                                </label>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        <?php endif; ?>

                        <div class="form-group">
                            <label for="guest_name">Full Name *</label>
                            <input type="text" id="guest_name" name="guest_name" required>
                        </div>

                        <div class="form-group">
                            <label for="guest_email">Email Address *</label>
                            <input type="email" id="guest_email" name="guest_email" required>
                        </div>

                        <div class="form-group">
                            <label for="guest_phone">Phone Number *</label>
                            <input type="tel" id="guest_phone" name="guest_phone" required>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label for="check_in_date">Check-in Date *</label>
                                <input type="date" id="check_in_date" name="check_in_date" required>
                            </div>

                            <div class="form-group">
                                <label for="check_out_date">Check-out Date *</label>
                                <input type="date" id="check_out_date" name="check_out_date" required>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="number_of_guests">Number of Guests *</label>
                            <select id="number_of_guests" name="number_of_guests" required>
                                <option value="">Select guests</option>
                                <?php 
                                $max_guests_display = $stay['is_partner'] && !empty($room_types) ? max(array_column($room_types, 'max_guests')) : $stay['max_guests'];
                                for ($i = 1; $i <= $max_guests_display; $i++): 
                                ?>
                                    <option value="<?= $i ?>"><?= $i ?> <?= $i == 1 ? 'Guest' : 'Guests' ?></option>
                                <?php endfor; ?>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="special_requests">Special Requests</label>
                            <textarea id="special_requests" name="special_requests" rows="4" placeholder="Any special requirements or requests..."></textarea>
                        </div>

                        <!-- Booking Summary -->
                        <div class="booking-summary">
                            <h4>Booking Summary</h4>
                            <div id="selected-room-display" style="display: none; margin-bottom: 1rem; padding: 0.75rem; background: #f0fdf4; border-radius: 8px; border-left: 3px solid #10b981;">
                                <strong>Selected Room:</strong> <span id="selected-room-name"></span>
                            </div>
                            <div class="summary-row">
                                <span>Number of Nights:</span>
                                <span id="nights_display">-</span>
                            </div>
                            <?php if ($stay['is_partner'] && !empty($room_types)): ?>
                            <div class="summary-row">
                                <span>Rate per Night:</span>
                                <span id="rate_display">R <?= number_format($room_types[0]['price_per_night'], 2) ?></span>
                            </div>
                            <?php elseif ($stay['price_per_night']): ?>
                            <div class="summary-row">
                                <span>Rate per Night:</span>
                                <span>R <?= number_format($stay['price_per_night'], 2) ?></span>
                            </div>
                            <?php endif; ?>
                            
                            <?php if (($stay['is_partner'] && !empty($room_types)) || $stay['price_per_night']): ?>
                            <div class="summary-row">
                                <span>Subtotal:</span>
                                <span id="subtotal_display">R 0.00</span>
                            </div>
                            <?php if ($stay['is_partner']): ?>
                            <div class="summary-row text-small">
                                <span>Service Fee (9.5%):</span>
                                <span id="commission_display">R 0.00</span>
                            </div>
                            <?php endif; ?>
                            <div class="summary-row total">
                                <span>Total:</span>
                                <span id="total_display">R 0.00</span>
                            </div>
                            <?php else: ?>
                            <div class="summary-row">
                                <span>Price:</span>
                                <span>Contact property for rates</span>
                            </div>
                            <?php endif; ?>
                        </div>

                        <input type="hidden" name="number_of_nights" id="number_of_nights" value="0">
                        <input type="hidden" name="total_amount" id="total_amount" value="0">
                        <input type="hidden" name="commission_amount" id="commission_amount" value="0">

                        <button type="submit" class="submit-booking-btn" <?= ($stay['is_partner'] && empty($room_types)) ? 'disabled' : '' ?>>
                            <?= $stay['is_partner'] ? 'Confirm Booking' : 'Send Inquiry' ?>
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <line x1="22" y1="2" x2="11" y2="13"></line>
                                <polygon points="22 2 15 22 11 13 2 9 22 2"></polygon>
                            </svg>
                        </button>

                        <?php if ($stay['is_partner']): ?>
                        <p class="form-note">
                            By clicking "Confirm Booking", you agree to pay the total amount including the 9.5% service fee.
                        </p>
                        <?php endif; ?>
                    </form>
                </div>
            </div>
        </div>
    </div>
</main>

<style>
:root {
    --primary: #2563eb;
    --primary-dark: #1e40af;
    --success: #10b981;
    --warning: #f59e0b;
    --text-dark: #1f2937;
    --text-medium: #6b7280;
    --border: #e5e7eb;
    --bg-light: #f9fafb;
}

.booking-main {
    padding: 3rem 0;
    background: var(--bg-light);
    min-height: calc(100vh - 200px);
}

.booking-container {
    display: grid;
    grid-template-columns: 1fr 520px;
    gap: 2.5rem;
    max-width: 1400px;
    margin: 0 auto;
}

.back-link {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    color: var(--text-medium);
    text-decoration: none;
    margin-bottom: 1.5rem;
    font-weight: 500;
    transition: color 0.2s;
}

.back-link:hover {
    color: var(--primary);
}

/* Property Preview */
.property-card {
    background: white;
    border-radius: 16px;
    overflow: hidden;
    box-shadow: 0 1px 3px rgba(0,0,0,0.1);
}

.property-image {
    width: 100%;
    height: 300px;
    object-fit: cover;
}

.property-info {
    padding: 2rem;
}

.property-header {
    display: flex;
    justify-content: space-between;
    align-items: start;
    margin-bottom: 1rem;
}

.property-header h2 {
    font-size: 1.875rem;
    color: var(--text-dark);
    margin: 0;
}

.instant-badge {
    display: flex;
    align-items: center;
    gap: 0.4rem;
    padding: 0.4rem 0.875rem;
    background: var(--success);
    color: white;
    border-radius: 20px;
    font-size: 0.8rem;
    font-weight: 600;
}

.property-location {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    color: var(--text-medium);
    margin-bottom: 1.5rem;
}

.property-description {
    color: var(--text-medium);
    line-height: 1.7;
    margin-bottom: 2rem;
}

.amenities-list h4,
.cancellation-policy h4 {
    font-size: 1.125rem;
    color: var(--text-dark);
    margin-bottom: 1rem;
}

.amenities-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 0.75rem;
}

.amenity-item {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    color: var(--text-medium);
    font-size: 0.9rem;
}

.amenity-item svg {
    color: var(--success);
}

.property-details {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 1rem;
    margin: 2rem 0;
    padding: 1.5rem;
    background: var(--bg-light);
    border-radius: 12px;
}

.detail-item {
    font-size: 0.9rem;
}

.detail-item strong {
    display: block;
    color: var(--text-dark);
    margin-bottom: 0.25rem;
}

.cancellation-policy {
    margin-top: 2rem;
    padding-top: 2rem;
    border-top: 1px solid var(--border);
}

.cancellation-policy p {
    color: var(--text-medium);
    line-height: 1.6;
}

/* Booking Form */
.booking-form-card {
    background: white;
    border-radius: 16px;
    padding: 2rem;
    box-shadow: 0 1px 3px rgba(0,0,0,0.1);
    position: sticky;
    top: 2rem;
    max-height: calc(100vh - 4rem);
    overflow-y: auto;
}

.booking-form-card h3 {
    font-size: 1.5rem;
    color: var(--text-dark);
    margin-bottom: 1.5rem;
}

.info-notice {
    display: flex;
    gap: 1rem;
    padding: 1rem;
    background: #fef3c7;
    border-left: 3px solid #f59e0b;
    border-radius: 8px;
    margin-bottom: 1.5rem;
}

.info-notice.warning {
    background: #fee2e2;
    border-left-color: #ef4444;
}

.info-notice svg {
    flex-shrink: 0;
    color: #f59e0b;
}

.info-notice.warning svg {
    color: #ef4444;
}

.info-notice strong {
    display: block;
    color: #92400e;
    margin-bottom: 0.25rem;
}

.info-notice.warning strong {
    color: #991b1b;
}

.info-notice p {
    color: #78350f;
    font-size: 0.875rem;
    margin: 0;
    line-height: 1.5;
}

.info-notice.warning p {
    color: #7f1d1d;
}

/* Room Type Selection */
.form-section {
    margin-bottom: 2rem;
}

.section-title {
    font-size: 1.125rem;
    font-weight: 700;
    color: var(--text-dark);
    margin-bottom: 1rem;
}

.room-types-selection {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.room-type-option {
    cursor: pointer;
}

.room-type-option input[type="radio"] {
    display: none;
}

.room-option-card {
    position: relative;
    border: 2px solid var(--border);
    border-radius: 12px;
    padding: 1rem;
    transition: all 0.3s;
    display: grid;
    grid-template-columns: 100px 1fr auto;
    gap: 1rem;
    align-items: start;
}

.room-type-option input[type="radio"]:checked + .room-option-card {
    border-color: var(--success);
    background: #f0fdf4;
    box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.1);
}

.room-option-image {
    width: 100px;
    height: 100px;
    border-radius: 8px;
    overflow: hidden;
    background: var(--bg-light);
}

.room-option-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.room-option-details {
    flex: 1;
}

.room-option-header {
    display: flex;
    justify-content: space-between;
    align-items: start;
    margin-bottom: 0.5rem;
}

.room-option-header h5 {
    margin: 0;
    font-size: 1rem;
    font-weight: 700;
    color: var(--text-dark);
}

.room-option-price {
    font-size: 1.125rem;
    font-weight: 700;
    color: var(--success);
    white-space: nowrap;
}

.room-option-description {
    font-size: 0.875rem;
    color: var(--text-medium);
    margin: 0.5rem 0;
    line-height: 1.5;
}

.room-option-specs {
    display: flex;
    flex-wrap: wrap;
    gap: 1rem;
    margin: 0.75rem 0;
    font-size: 0.8rem;
    color: var(--text-medium);
}

.room-option-specs span {
    display: flex;
    align-items: center;
    gap: 0.35rem;
}

.room-option-specs svg {
    flex-shrink: 0;
}

.room-option-amenities {
    display: flex;
    flex-wrap: wrap;
    gap: 0.35rem;
    margin-top: 0.75rem;
}

.amenity-chip {
    padding: 0.25rem 0.6rem;
    background: #e0e7ff;
    color: #3730a3;
    border-radius: 6px;
    font-size: 0.7rem;
    font-weight: 500;
}

.amenity-chip.more {
    background: var(--bg-light);
    color: var(--text-medium);
}

.room-availability-warning {
    margin-top: 0.5rem;
    padding: 0.4rem 0.75rem;
    background: #fef3c7;
    border-radius: 6px;
    font-size: 0.75rem;
    color: #92400e;
    font-weight: 600;
}

.room-option-checkmark {
    width: 28px;
    height: 28px;
    border: 2px solid var(--border);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.3s;
    flex-shrink: 0;
}

.room-option-checkmark svg {
    opacity: 0;
    transition: opacity 0.3s;
    color: white;
}

.room-type-option input[type="radio"]:checked + .room-option-card .room-option-checkmark {
    background: var(--success);
    border-color: var(--success);
}

.room-type-option input[type="radio"]:checked + .room-option-card .room-option-checkmark svg {
    opacity: 1;
}

/* Form Elements */
.form-group {
    margin-bottom: 1.25rem;
}

.form-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 1rem;
}

label {
    display: block;
    font-weight: 600;
    color: var(--text-dark);
    margin-bottom: 0.5rem;
    font-size: 0.9rem;
}

input[type="text"],
input[type="email"],
input[type="tel"],
input[type="date"],
select,
textarea {
    width: 100%;
    padding: 0.75rem;
    border: 1px solid var(--border);
    border-radius: 8px;
    font-size: 1rem;
    transition: all 0.2s;
}

input:focus,
select:focus,
textarea:focus {
    outline: none;
    border-color: var(--primary);
    box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
}

textarea {
    resize: vertical;
}

.booking-summary {
    background: var(--bg-light);
    padding: 1.5rem;
    border-radius: 12px;
    margin: 2rem 0 1.5rem;
}

.booking-summary h4 {
    font-size: 1.125rem;
    color: var(--text-dark);
    margin-bottom: 1rem;
}

.summary-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 0.75rem 0;
    border-bottom: 1px solid var(--border);
}

.summary-row:last-child {
    border-bottom: none;
}

.summary-row.total {
    font-weight: 700;
    font-size: 1.125rem;
    color: var(--text-dark);
    padding-top: 1rem;
    margin-top: 0.5rem;
    border-top: 2px solid var(--border);
}

.summary-row.text-small {
    font-size: 0.875rem;
    color: var(--text-medium);
}

.submit-booking-btn {
    width: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.75rem;
    padding: 1rem;
    background: linear-gradient(135deg, var(--primary), #0ea5e9);
    color: white;
    border: none;
    border-radius: 12px;
    font-size: 1.125rem;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s;
}

.submit-booking-btn:hover:not(:disabled) {
    transform: translateY(-2px);
    box-shadow: 0 8px 20px rgba(37, 99, 235, 0.3);
}

.submit-booking-btn:disabled {
    background: #9ca3af;
    cursor: not-allowed;
    opacity: 0.6;
}

.form-note {
    text-align: center;
    font-size: 0.8rem;
    color: var(--text-medium);
    margin-top: 1rem;
}

@media (max-width: 968px) {
    .booking-container {
        grid-template-columns: 1fr;
    }

    .booking-form-card {
        position: static;
        max-height: none;
    }

    .property-details {
        grid-template-columns: 1fr;
    }

    .amenities-grid {
        grid-template-columns: 1fr;
    }

    .room-option-card {
        grid-template-columns: 80px 1fr;
    }

    .room-option-checkmark {
        grid-column: 2;
        justify-self: end;
    }
}
</style>

<script>
// Get initial price - either from first room type or property
let pricePerNight = <?= $stay['is_partner'] && !empty($room_types) ? $room_types[0]['price_per_night'] : ($stay['price_per_night'] ? $stay['price_per_night'] : 0) ?>;
const isPartner = <?= $stay['is_partner'] ? 'true' : 'false' ?>;
const commissionRate = 0.095;
const hasRoomTypes = <?= $stay['is_partner'] && !empty($room_types) ? 'true' : 'false' ?>;

// Set minimum dates
const today = new Date().toISOString().split('T')[0];
document.getElementById('check_in_date').setAttribute('min', today);

// Handle room type selection
if (hasRoomTypes) {
    const roomRadios = document.querySelectorAll('input[name="room_type_id"]');
    roomRadios.forEach(radio => {
        radio.addEventListener('change', function() {
            pricePerNight = parseFloat(this.dataset.price);
            const roomName = this.dataset.roomName;
            
            // Update rate display
            document.getElementById('rate_display').textContent = 'R ' + pricePerNight.toFixed(2);
            
            // Show selected room name
            document.getElementById('selected-room-name').textContent = roomName;
            document.getElementById('selected-room-display').style.display = 'block';
            
            // Update max guests dropdown
            const maxGuests = parseInt(this.dataset.maxGuests);
            updateGuestsDropdown(maxGuests);
            
            // Recalculate total
            calculateTotal();
        });
    });
    
    // Set initial room name
    const firstCheckedRoom = document.querySelector('input[name="room_type_id"]:checked');
    if (firstCheckedRoom) {
        document.getElementById('selected-room-name').textContent = firstCheckedRoom.dataset.roomName;
        document.getElementById('selected-room-display').style.display = 'block';
    }
}

function updateGuestsDropdown(maxGuests) {
    const guestsSelect = document.getElementById('number_of_guests');
    const currentValue = guestsSelect.value;
    
    guestsSelect.innerHTML = '<option value="">Select guests</option>';
    for (let i = 1; i <= maxGuests; i++) {
        const option = document.createElement('option');
        option.value = i;
        option.textContent = i + (i === 1 ? ' Guest' : ' Guests');
        if (i == currentValue && i <= maxGuests) {
            option.selected = true;
        }
        guestsSelect.appendChild(option);
    }
}

document.getElementById('check_in_date').addEventListener('change', function() {
    const checkIn = new Date(this.value);
    const nextDay = new Date(checkIn);
    nextDay.setDate(nextDay.getDate() + 1);
    document.getElementById('check_out_date').setAttribute('min', nextDay.toISOString().split('T')[0]);
    calculateTotal();
});

document.getElementById('check_out_date').addEventListener('change', calculateTotal);

function calculateTotal() {
    const checkIn = document.getElementById('check_in_date').value;
    const checkOut = document.getElementById('check_out_date').value;

    if (!checkIn || !checkOut || !pricePerNight) {
        return;
    }

    const checkInDate = new Date(checkIn);
    const checkOutDate = new Date(checkOut);
    const nights = Math.ceil((checkOutDate - checkInDate) / (1000 * 60 * 60 * 24));

    if (nights > 0) {
        const subtotal = pricePerNight * nights;
        const commission = isPartner ? subtotal * commissionRate : 0;
        const total = subtotal + commission;

        document.getElementById('nights_display').textContent = nights + (nights === 1 ? ' night' : ' nights');
        document.getElementById('subtotal_display').textContent = 'R ' + subtotal.toFixed(2);
        
        if (isPartner) {
            document.getElementById('commission_display').textContent = 'R ' + commission.toFixed(2);
        }
        
        document.getElementById('total_display').textContent = 'R ' + total.toFixed(2);
        
        document.getElementById('number_of_nights').value = nights;
        document.getElementById('total_amount').value = total.toFixed(2);
        document.getElementById('commission_amount').value = commission.toFixed(2);
    }
}

// Form validation
document.getElementById('bookingForm').addEventListener('submit', function(e) {
    const checkIn = document.getElementById('check_in_date').value;
    const checkOut = document.getElementById('check_out_date').value;

    if (!checkIn || !checkOut) {
        e.preventDefault();
        alert('Please select check-in and check-out dates.');
        return;
    }

    const checkInDate = new Date(checkIn);
    const checkOutDate = new Date(checkOut);

    if (checkOutDate <= checkInDate) {
        e.preventDefault();
        alert('Check-out date must be after check-in date.');
        return;
    }

    // Validate guest count for selected room
    if (hasRoomTypes) {
        const selectedRoom = document.querySelector('input[name="room_type_id"]:checked');
        const guestCount = parseInt(document.getElementById('number_of_guests').value);
        const maxGuests = parseInt(selectedRoom.dataset.maxGuests);

        if (guestCount > maxGuests) {
            e.preventDefault();
            alert(`The selected room can accommodate a maximum of ${maxGuests} guests. Please select a different room or reduce the number of guests.`);
            return;
        }
    }
});
</script>

<?php include 'footer.php'; ?>