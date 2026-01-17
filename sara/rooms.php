<?php
session_start();
$page = 'rooms';
include 'includes/header.php';
?>

<!-- Page Header -->
<section class="page-header">
    <div class="page-header-overlay"></div>
    <div class="page-header-content">
        <h1>Our Rooms</h1>
        <p>Comfortable accommodation for every traveler</p>
    </div>
</section>

<!-- Rooms Detailed Section -->
<section class="rooms-detailed">
    <div class="container">
        <!-- Deluxe Room -->
        <div class="room-detail-card">
            <div class="room-detail-images">
                <div class="main-image">
                    <img src="images/gallery/rooms/deluxe-room.jpg" alt="Deluxe Room" id="deluxeMainImg">
                </div>
                <div class="thumbnail-images">
                    <img src="images/gallery/rooms/deluxe-room-1.jpg" alt="Deluxe Room View 1" onclick="changeImage('deluxeMainImg', this.src)">
                    <img src="images/gallery/rooms/deluxe-room-2.jpg" alt="Deluxe Room View 2" onclick="changeImage('deluxeMainImg', this.src)">
                    <img src="images/gallery/rooms/deluxe-room-3.jpg" alt="Deluxe Room View 3" onclick="changeImage('deluxeMainImg', this.src)">
                    <img src="images/gallery/rooms/deluxe-room-4.jpg" alt="Deluxe Room View 4" onclick="changeImage('deluxeMainImg', this.src)">
                </div>
            </div>
            <div class="room-detail-info">
                <div class="room-badge-lg">Most Popular</div>
                <h2>Deluxe Room</h2>
                <div class="room-price-lg">
                    <span class="price">R850</span>
                    <span class="period">per night</span>
                </div>
                <p class="room-description">
                    Our Deluxe Room offers spacious luxury with a queen-size bed, modern en-suite bathroom, 
                    and stunning garden views. Perfect for couples seeking comfort and relaxation.
                </p>
                
                <h3>Room Features</h3>
                <div class="features-grid">
                    <div class="feature-item">
                        <span class="feature-icon">🛏️</span>
                        <div>
                            <strong>Queen-Size Bed</strong>
                            <p>Premium mattress with luxury linens</p>
                        </div>
                    </div>
                    <div class="feature-item">
                        <span class="feature-icon">🚿</span>
                        <div>
                            <strong>En-Suite Bathroom</strong>
                            <p>Shower, bathtub, premium toiletries</p>
                        </div>
                    </div>
                    <div class="feature-item">
                        <span class="feature-icon">📺</span>
                        <div>
                            <strong>Entertainment</strong>
                            <p>Flat-screen TV with satellite channels</p>
                        </div>
                    </div>
                    <div class="feature-item">
                        <span class="feature-icon">❄️</span>
                        <div>
                            <strong>Climate Control</strong>
                            <p>Air conditioning & heating</p>
                        </div>
                    </div>
                    <div class="feature-item">
                        <span class="feature-icon">📶</span>
                        <div>
                            <strong>High-Speed Wi-Fi</strong>
                            <p>Complimentary internet access</p>
                        </div>
                    </div>
                    <div class="feature-item">
                        <span class="feature-icon">☕</span>
                        <div>
                            <strong>Tea/Coffee Station</strong>
                            <p>In-room refreshments</p>
                        </div>
                    </div>
                    <div class="feature-item">
                        <span class="feature-icon">🌿</span>
                        <div>
                            <strong>Garden View</strong>
                            <p>Private balcony overlooking gardens</p>
                        </div>
                    </div>
                    <div class="feature-item">
                        <span class="feature-icon">🔒</span>
                        <div>
                            <strong>Safe & Secure</strong>
                            <p>In-room safe for valuables</p>
                        </div>
                    </div>
                </div>
                
                <div class="room-actions">
                    <a href="booking.php?room=deluxe" class="btn btn-primary btn-lg">Book This Room</a>
                    <a href="contact.php" class="btn btn-outline btn-lg">Ask a Question</a>
                </div>
            </div>
        </div>

        <!-- Standard Room -->
        <div class="room-detail-card">
            <div class="room-detail-images">
                <div class="main-image">
                    <img src="images/gallery/rooms/standard-room-2.jpg" alt="Standard Room" id="standardMainImg">
                </div>
                <div class="thumbnail-images">
                    <img src="images/gallery/rooms/standard-room.jpg" alt="Standard Room View 1" onclick="changeImage('standardMainImg', this.src)">
                    <img src="images/gallery/rooms/standard-room-2.jpg" alt="Standard Room View 2" onclick="changeImage('standardMainImg', this.src)">
                    <img src="images/gallery/rooms/standard-room-3.jpg" alt="Standard Room View 3" onclick="changeImage('standardMainImg', this.src)">
                    <img src="images/gallery/rooms/standard-room-4.jpg" alt="Standard Room View 4" onclick="changeImage('standardMainImg', this.src)">
                </div>
            </div>
            <div class="room-detail-info">
                <div class="room-badge-lg value">Great Value</div>
                <h2>Standard Room</h2>
                <div class="room-price-lg">
                    <span class="price">R650</span>
                    <span class="period">per night</span>
                </div>
                <p class="room-description">
                    Our Standard Room provides cozy comfort with a double bed and private bathroom. 
                    Ideal for solo travelers or couples looking for quality accommodation at an excellent value.
                </p>
                
                <h3>Room Features</h3>
                <div class="features-grid">
                    <div class="feature-item">
                        <span class="feature-icon">🛏️</span>
                        <div>
                            <strong>Double Bed</strong>
                            <p>Comfortable mattress with quality linens</p>
                        </div>
                    </div>
                    <div class="feature-item">
                        <span class="feature-icon">🚿</span>
                        <div>
                            <strong>Private Bathroom</strong>
                            <p>Shower with hot water, toiletries</p>
                        </div>
                    </div>
                    <div class="feature-item">
                        <span class="feature-icon">📺</span>
                        <div>
                            <strong>TV & Entertainment</strong>
                            <p>Flat-screen TV with channels</p>
                        </div>
                    </div>
                    <div class="feature-item">
                        <span class="feature-icon">📶</span>
                        <div>
                            <strong>Free Wi-Fi</strong>
                            <p>High-speed internet included</p>
                        </div>
                    </div>
                    <div class="feature-item">
                        <span class="feature-icon">☕</span>
                        <div>
                            <strong>Refreshments</strong>
                            <p>Tea and coffee facilities</p>
                        </div>
                    </div>
                    <div class="feature-item">
                        <span class="feature-icon">🪟</span>
                        <div>
                            <strong>Natural Light</strong>
                            <p>Large windows with views</p>
                        </div>
                    </div>
                </div>
                
                <div class="room-actions">
                    <a href="booking.php?room=standard" class="btn btn-primary btn-lg">Book This Room</a>
                    <a href="contact.php" class="btn btn-outline btn-lg">Ask a Question</a>
                </div>
            </div>
        </div>

        <!-- Family Suite -->
        <div class="room-detail-card">
            <div class="room-detail-images">
                <div class="main-image">
                    <img src="images/gallery/rooms/family-room.jpg" alt="Family Suite" id="familyMainImg">
                </div>
                <div class="thumbnail-images">
                    <img src="images/gallery/rooms/family-room-1.jpg" alt="Family Suite View 1" onclick="changeImage('familyMainImg', this.src)">
                    <img src="images/gallery/rooms/family-room-2.jpg" alt="Family Suite View 2" onclick="changeImage('familyMainImg', this.src)">
                    <img src="images/gallery/rooms/family-room-3.jpg" alt="Family Suite View 3" onclick="changeImage('familyMainImg', this.src)">
                    <img src="images/gallery/rooms/family-room-4.jpg" alt="Family Suite View 4" onclick="changeImage('familyMainImg', this.src)">
                </div>
            </div>
            <div class="room-detail-info">
                <div class="room-badge-lg family">Family Favorite</div>
                <h2>Family Suite</h2>
                <div class="room-price-lg">
                    <span class="price">R1,200</span>
                    <span class="period">per night</span>
                </div>
                <p class="room-description">
                    Our spacious Family Suite is perfect for families or groups. Features multiple beds, 
                    separate living area, and all the comforts of home to ensure everyone enjoys their stay.
                </p>
                
                <h3>Room Features</h3>
                <div class="features-grid">
                    <div class="feature-item">
                        <span class="feature-icon">🛏️</span>
                        <div>
                            <strong>Multiple Beds</strong>
                            <p>Queen bed + 2 single beds</p>
                        </div>
                    </div>
                    <div class="feature-item">
                        <span class="feature-icon">🚿</span>
                        <div>
                            <strong>Large Bathroom</strong>
                            <p>Bath, shower, double vanity</p>
                        </div>
                    </div>
                    <div class="feature-item">
                        <span class="feature-icon">🏠</span>
                        <div>
                            <strong>Living Area</strong>
                            <p>Separate seating space with sofa</p>
                        </div>
                    </div>
                    <div class="feature-item">
                        <span class="feature-icon">📺</span>
                        <div>
                            <strong>2 TVs</strong>
                            <p>Entertainment in bedroom & living room</p>
                        </div>
                    </div>
                    <div class="feature-item">
                        <span class="feature-icon">❄️</span>
                        <div>
                            <strong>Climate Control</strong>
                            <p>Air conditioning throughout</p>
                        </div>
                    </div>
                    <div class="feature-item">
                        <span class="feature-icon">🍽️</span>
                        <div>
                            <strong>Mini Kitchenette</strong>
                            <p>Microwave, fridge, kettle</p>
                        </div>
                    </div>
                    <div class="feature-item">
                        <span class="feature-icon">🌳</span>
                        <div>
                            <strong>Private Patio</strong>
                            <p>Outdoor seating area</p>
                        </div>
                    </div>
                    <div class="feature-item">
                        <span class="feature-icon">📶</span>
                        <div>
                            <strong>High-Speed Wi-Fi</strong>
                            <p>Perfect for the whole family</p>
                        </div>
                    </div>
                </div>
                
                <div class="room-actions">
                    <a href="booking.php?room=family" class="btn btn-primary btn-lg">Book This Room</a>
                    <a href="contact.php" class="btn btn-outline btn-lg">Ask a Question</a>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Booking Policies Section -->
<section class="policies-section">
    <div class="container">
        <h2 class="section-title">Booking Policies</h2>
        <div class="policies-grid">
            <div class="policy-card">
                <h3>Check-in & Check-out</h3>
                <ul>
                    <li><strong>Check-in:</strong> 2:00 PM - 7:00 PM</li>
                    <li><strong>Check-out:</strong> 10:00 AM</li>
                    <li><strong>Late check-in:</strong> Available by arrangement</li>
                    <li><strong>Early check-in:</strong> Subject to availability</li>
                </ul>
            </div>
            <div class="policy-card">
                <h3>Cancellation Policy</h3>
                <ul>
                    <li><strong>Free cancellation:</strong> Up to 48 hours before arrival</li>
                    <li><strong>Late cancellation:</strong> First night charged</li>
                    <li><strong>No-show:</strong> Full booking charged</li>
                    <li><strong>Amendments:</strong> Free, subject to availability</li>
                </ul>
            </div>
            <div class="policy-card">
                <h3>Payment & Deposits</h3>
                <ul>
                    <li><strong>Deposit:</strong> 30% required to secure booking</li>
                    <li><strong>Balance:</strong> Due on arrival</li>
                    <li><strong>Methods:</strong> Cash, EFT, Card</li>
                    <li><strong>Currency:</strong> South African Rand (ZAR)</li>
                </ul>
            </div>
            <div class="policy-card">
                <h3>House Rules</h3>
                <ul>
                    <li><strong>Smoking:</strong> Designated outdoor areas only</li>
                    <li><strong>Pets:</strong> Not permitted</li>
                    <li><strong>Quiet hours:</strong> 10:00 PM - 7:00 AM</li>
                    <li><strong>Visitors:</strong> By prior arrangement</li>
                </ul>
            </div>
        </div>
    </div>
</section>

<script>
function changeImage(mainImgId, src) {
    document.getElementById(mainImgId).src = src;
}
</script>

<?php include 'includes/footer.php'; ?>