<?php
session_start();
$page = 'booking';

// Room data
$rooms = [
    'deluxe' => ['name' => 'Deluxe Room', 'price' => 850],
    'standard' => ['name' => 'Standard Room', 'price' => 650],
    'family' => ['name' => 'Family Suite', 'price' => 1200]
];

// Get selected room from URL
$selected_room = $_GET['room'] ?? '';
$room_data = $rooms[$selected_room] ?? null;

// Form handling
$booking_confirmed = false;
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $room_type = $_POST['room_type'] ?? '';
    $checkin = $_POST['checkin'] ?? '';
    $checkout = $_POST['checkout'] ?? '';
    $guests = intval($_POST['guests'] ?? 1);
    $special_requests = trim($_POST['special_requests'] ?? '');
    
    // Validation
    if (empty($name)) $errors[] = "Name is required";
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "Valid email is required";
    if (empty($phone)) $errors[] = "Phone number is required";
    if (empty($room_type)) $errors[] = "Please select a room type";
    if (empty($checkin)) $errors[] = "Check-in date is required";
    if (empty($checkout)) $errors[] = "Check-out date is required";
    
    // Date validation
    if (!empty($checkin) && !empty($checkout)) {
        $checkin_date = new DateTime($checkin);
        $checkout_date = new DateTime($checkout);
        $today = new DateTime();
        $today->setTime(0, 0, 0);
        
        if ($checkin_date < $today) {
            $errors[] = "Check-in date cannot be in the past";
        }
        if ($checkout_date <= $checkin_date) {
            $errors[] = "Check-out must be after check-in";
        }
    }
    
    // If no errors, process booking
    if (empty($errors)) {
        // Calculate nights and total
        $checkin_date = new DateTime($checkin);
        $checkout_date = new DateTime($checkout);
        $interval = $checkin_date->diff($checkout_date);
        $nights = $interval->days;
        $room_price = $rooms[$room_type]['price'];
        $total = $nights * $room_price;
        
        // Send booking email
        $to = "bookings@sara-lee.co.za";
        $subject = "New Booking Request - Sara-Lee Guesthouse";
        $message = "NEW BOOKING REQUEST\n\n";
        $message .= "Guest Details:\n";
        $message .= "Name: $name\n";
        $message .= "Email: $email\n";
        $message .= "Phone: $phone\n\n";
        $message .= "Booking Details:\n";
        $message .= "Room Type: " . $rooms[$room_type]['name'] . "\n";
        $message .= "Check-in: " . $checkin_date->format('d M Y') . "\n";
        $message .= "Check-out: " . $checkout_date->format('d M Y') . "\n";
        $message .= "Number of Nights: $nights\n";
        $message .= "Number of Guests: $guests\n";
        $message .= "Total Amount: R" . number_format($total, 2) . "\n\n";
        if (!empty($special_requests)) {
            $message .= "Special Requests:\n$special_requests\n";
        }
        
        $headers = "From: $email\r\n";
        $headers .= "Reply-To: $email\r\n";
        
        if (mail($to, $subject, $message, $headers)) {
            $booking_confirmed = true;
        } else {
            $errors[] = "Failed to send booking. Please try again or call us directly.";
        }
    }
}

include 'includes/header.php';
?>

<!-- Page Header -->
<section class="page-header">
    <div class="page-header-overlay"></div>
    <div class="page-header-content">
        <h1>Book Your Stay</h1>
        <p>Reserve your room at Sara-Lee Guesthouse</p>
    </div>
</section>

<!-- Booking Section -->
<section class="booking-section">
    <div class="container">
        <?php if ($booking_confirmed): ?>
            <div class="booking-container" style="text-align: center;">
                <div style="font-size: 5rem; color: var(--primary-color); margin-bottom: 20px;">✓</div>
                <h2 style="color: var(--primary-color); margin-bottom: 20px;">Booking Request Received!</h2>
                <p style="font-size: 1.2rem; color: var(--text-light); margin-bottom: 30px;">
                    Thank you for your booking request. We have received your details and will contact you shortly to confirm your reservation and arrange payment.
                </p>
                <p style="color: var(--text-light); margin-bottom: 30px;">
                    A confirmation email has been sent to <strong><?php echo htmlspecialchars($email); ?></strong>
                </p>
                <div style="background: var(--bg-light); padding: 30px; border-radius: 10px; margin-bottom: 30px;">
                    <h3 style="color: var(--primary-color); margin-bottom: 20px;">Booking Summary</h3>
                    <div style="text-align: left; max-width: 400px; margin: 0 auto;">
                        <p><strong>Guest:</strong> <?php echo htmlspecialchars($name); ?></p>
                        <p><strong>Room:</strong> <?php echo $rooms[$room_type]['name']; ?></p>
                        <p><strong>Check-in:</strong> <?php echo $checkin_date->format('d M Y'); ?></p>
                        <p><strong>Check-out:</strong> <?php echo $checkout_date->format('d M Y'); ?></p>
                        <p><strong>Nights:</strong> <?php echo $nights; ?></p>
                        <p><strong>Guests:</strong> <?php echo $guests; ?></p>
                        <p style="font-size: 1.3rem; color: var(--primary-color); margin-top: 20px; padding-top: 20px; border-top: 2px solid var(--primary-color);">
                            <strong>Total:</strong> R<?php echo number_format($total, 2); ?>
                        </p>
                    </div>
                </div>
                <div style="display: flex; gap: 15px; justify-content: center; flex-wrap: wrap;">
                    <a href="index.php" class="btn btn-primary">Return Home</a>
                    <a href="tel:0798911983" class="btn btn-secondary">Call Us: 079 891 1983</a>
                </div>
            </div>
        <?php else: ?>
            <div class="booking-container">
                <h2>Make a Reservation</h2>
                <p style="text-align: center; color: var(--text-light); margin-bottom: 30px;">
                    Fill out the form below to request a booking. We'll confirm availability and send you payment details.
                </p>
                
                <?php if (!empty($errors)): ?>
                    <div class="form-alert error">
                        <strong>Please correct the following errors:</strong>
                        <ul style="margin: 10px 0 0 20px;">
                            <?php foreach ($errors as $error): ?>
                                <li><?php echo htmlspecialchars($error); ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>
                
                <form method="POST" action="" id="bookingForm">
                    <h3 style="color: var(--primary-color); margin: 30px 0 20px;">Guest Information</h3>
                    
                    <div class="form-group">
                        <label for="name">Full Name *</label>
                        <input type="text" id="name" name="name" class="form-control" 
                               value="<?php echo htmlspecialchars($_POST['name'] ?? ''); ?>" required>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="email">Email Address *</label>
                            <input type="email" id="email" name="email" class="form-control" 
                                   value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>" required>
                        </div>

                        <div class="form-group">
                            <label for="phone">Phone Number *</label>
                            <input type="tel" id="phone" name="phone" class="form-control" 
                                   value="<?php echo htmlspecialchars($_POST['phone'] ?? ''); ?>" required>
                        </div>
                    </div>

                    <h3 style="color: var(--primary-color); margin: 30px 0 20px;">Booking Details</h3>

                    <div class="form-group">
                        <label for="room_type">Room Type *</label>
                        <select id="room_type" name="room_type" class="form-control" required onchange="updatePrice()">
                            <option value="">Select a room...</option>
                            <option value="deluxe" <?php echo ($selected_room == 'deluxe' || ($_POST['room_type'] ?? '') == 'deluxe') ? 'selected' : ''; ?> data-price="850">
                                Deluxe Room - R850 per night
                            </option>
                            <option value="standard" <?php echo ($selected_room == 'standard' || ($_POST['room_type'] ?? '') == 'standard') ? 'selected' : ''; ?> data-price="650">
                                Standard Room - R650 per night
                            </option>
                            <option value="family" <?php echo ($selected_room == 'family' || ($_POST['room_type'] ?? '') == 'family') ? 'selected' : ''; ?> data-price="1200">
                                Family Suite - R1,200 per night
                            </option>
                        </select>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="checkin">Check-in Date *</label>
                            <input type="date" id="checkin" name="checkin" class="form-control" 
                                   value="<?php echo htmlspecialchars($_POST['checkin'] ?? ''); ?>" 
                                   min="<?php echo date('Y-m-d'); ?>" required onchange="updatePrice()">
                        </div>

                        <div class="form-group">
                            <label for="checkout">Check-out Date *</label>
                            <input type="date" id="checkout" name="checkout" class="form-control" 
                                   value="<?php echo htmlspecialchars($_POST['checkout'] ?? ''); ?>" 
                                   min="<?php echo date('Y-m-d', strtotime('+1 day')); ?>" required onchange="updatePrice()">
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="guests">Number of Guests *</label>
                        <select id="guests" name="guests" class="form-control" required>
                            <?php for ($i = 1; $i <= 6; $i++): ?>
                                <option value="<?php echo $i; ?>" <?php echo (($_POST['guests'] ?? 1) == $i) ? 'selected' : ''; ?>>
                                    <?php echo $i; ?> Guest<?php echo $i > 1 ? 's' : ''; ?>
                                </option>
                            <?php endfor; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="special_requests">Special Requests (Optional)</label>
                        <textarea id="special_requests" name="special_requests" class="form-control" 
                                  placeholder="e.g., Early check-in, dietary requirements, celebration..."><?php echo htmlspecialchars($_POST['special_requests'] ?? ''); ?></textarea>
                    </div>

                    <div class="booking-summary" id="bookingSummary" style="display: none;">
                        <h3>Booking Summary</h3>
                        <div class="summary-item">
                            <span>Room Type:</span>
                            <span id="summaryRoom">-</span>
                        </div>
                        <div class="summary-item">
                            <span>Check-in:</span>
                            <span id="summaryCheckin">-</span>
                        </div>
                        <div class="summary-item">
                            <span>Check-out:</span>
                            <span id="summaryCheckout">-</span>
                        </div>
                        <div class="summary-item">
                            <span>Number of Nights:</span>
                            <span id="summaryNights">-</span>
                        </div>
                        <div class="summary-item">
                            <span>Rate per Night:</span>
                            <span id="summaryRate">-</span>
                        </div>
                        <div class="summary-item">
                            <span>Total Amount:</span>
                            <span id="summaryTotal">R0.00</span>
                        </div>
                    </div>

                    <div style="background: #FFF3CD; border: 1px solid #FFD23F; padding: 15px; border-radius: 8px; margin: 20px 0;">
                        <strong>Please Note:</strong> A 30% deposit is required to secure your booking. We'll send payment details once we confirm availability.
                    </div>

                    <button type="submit" class="btn btn-primary btn-lg btn-block">Submit Booking Request</button>
                </form>
            </div>
        <?php endif; ?>
    </div>
</section>

<script>
function updatePrice() {
    const roomSelect = document.getElementById('room_type');
    const checkinInput = document.getElementById('checkin');
    const checkoutInput = document.getElementById('checkout');
    const summary = document.getElementById('bookingSummary');
    
    if (!roomSelect.value || !checkinInput.value || !checkoutInput.value) {
        summary.style.display = 'none';
        return;
    }
    
    const selectedOption = roomSelect.options[roomSelect.selectedIndex];
    const price = parseFloat(selectedOption.getAttribute('data-price'));
    const roomName = selectedOption.text.split(' - ')[0];
    
    const checkin = new Date(checkinInput.value);
    const checkout = new Date(checkoutInput.value);
    const timeDiff = checkout - checkin;
    const nights = Math.ceil(timeDiff / (1000 * 3600 * 24));
    
    if (nights > 0) {
        const total = nights * price;
        
        document.getElementById('summaryRoom').textContent = roomName;
        document.getElementById('summaryCheckin').textContent = checkin.toLocaleDateString('en-ZA', { day: 'numeric', month: 'short', year: 'numeric' });
        document.getElementById('summaryCheckout').textContent = checkout.toLocaleDateString('en-ZA', { day: 'numeric', month: 'short', year: 'numeric' });
        document.getElementById('summaryNights').textContent = nights;
        document.getElementById('summaryRate').textContent = 'R' + price.toFixed(2);
        document.getElementById('summaryTotal').textContent = 'R' + total.toFixed(2);
        
        summary.style.display = 'block';
    } else {
        summary.style.display = 'none';
    }
}

// Update price on page load if values are present
document.addEventListener('DOMContentLoaded', function() {
    updatePrice();
});
</script>

<?php include 'includes/footer.php'; ?>