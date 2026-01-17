<?php
session_start();
$page = 'home';
include 'includes/header.php';
?>

<!-- Hero Section -->
<section class="hero">
    <div class="hero-overlay"></div>
    <div class="hero-content">
        <h1 class="fade-in">Welcome to Sara-Lee Guesthouse</h1>
        <p class="fade-in-delay">Bed & Breakfast | 6 Seymour Street</p>
        <a href="tel:0798911983" class="btn btn-primary fade-in-delay-2">
            <i class="phone-icon">📞</i> 079 891 1983
        </a>
        <a href="#rooms" class="btn btn-secondary fade-in-delay-2">View Rooms</a>
    </div>
    <div class="scroll-indicator">
        <span>Scroll Down</span>
        <div class="arrow-down"></div>
    </div>
</section>

<!-- Welcome Section -->
<section class="welcome-section">
    <div class="container">
        <div class="row">
            <div class="col-md-6">
                <img src="images/gallery/exterior/guesthouse-exterior.jpg" alt="Sara-Lee Guesthouse" class="img-fluid rounded shadow">
            </div>
            <div class="col-md-6">
                <h2>Your Home Away From Home</h2>
                <p class="lead">Experience warm hospitality and comfortable accommodation in the heart of Seymour Street.</p>
                <p>At Sara-Lee Guesthouse, we pride ourselves on providing exceptional bed and breakfast services with personalized attention to every guest. Our beautiful property features well-appointed rooms, lush gardens, and all the amenities you need for a memorable stay.</p>
                <ul class="feature-list">
                    <li>✓ Comfortable & Clean Rooms</li>
                    <li>✓ Delicious Breakfast Included</li>
                    <li>✓ Free Wi-Fi Throughout</li>
                    <li>✓ Secure Parking</li>
                    <li>✓ Beautiful Garden Views</li>
                    <li>✓ Friendly & Professional Service</li>
                </ul>
                <a href="about.php" class="btn btn-outline">Learn More About Us</a>
            </div>
        </div>
    </div>
</section>

<!-- Rooms Preview Section -->
<section id="rooms" class="rooms-section">
    <div class="container">
        <h2 class="section-title">Our Accommodation</h2>
        <p class="section-subtitle">Choose from our range of comfortable rooms</p>
        
        <div class="rooms-grid">
            <div class="room-card">
                <div class="room-image">
                    <img src="images/gallery/rooms/deluxe-room.jpg" alt="Deluxe Room">
                    <div class="room-badge">Popular</div>
                </div>
                <div class="room-details">
                    <h3>Deluxe Room</h3>
                    <p>Spacious room with queen-size bed, en-suite bathroom, and garden view</p>
                    <ul class="room-amenities">
                        <li>🛏️ Queen Bed</li>
                        <li>🚿 En-suite Bathroom</li>
                        <li>📺 TV & Wi-Fi</li>
                        <li>❄️ Air Conditioning</li>
                    </ul>
                    <div class="room-price">
                        <span class="price">R850</span>
                        <span class="period">per night</span>
                    </div>
                    <a href="rooms.php" class="btn btn-primary btn-block">View Details</a>
                </div>
            </div>

            <div class="room-card">
                <div class="room-image">
                    <img src="images/gallery/rooms/standard-room.jpg" alt="Standard Room">
                </div>
                <div class="room-details">
                    <h3>Standard Room</h3>
                    <p>Cozy room with double bed, private bathroom, and all essential amenities</p>
                    <ul class="room-amenities">
                        <li>🛏️ Double Bed</li>
                        <li>🚿 Private Bathroom</li>
                        <li>📺 TV & Wi-Fi</li>
                        <li>☕ Tea/Coffee Station</li>
                    </ul>
                    <div class="room-price">
                        <span class="price">R650</span>
                        <span class="period">per night</span>
                    </div>
                    <a href="rooms.php" class="btn btn-primary btn-block">View Details</a>
                </div>
            </div>

            <div class="room-card">
                <div class="room-image">
                    <img src="images/gallery/rooms/family-room.jpg" alt="Family Room">
                </div>
                <div class="room-details">
                    <h3>Family Suite</h3>
                    <p>Perfect for families with separate sleeping areas and extra space</p>
                    <ul class="room-amenities">
                        <li>🛏️ Multiple Beds</li>
                        <li>🚿 Large Bathroom</li>
                        <li>📺 TV & Wi-Fi</li>
                        <li>🏠 Living Area</li>
                    </ul>
                    <div class="room-price">
                        <span class="price">R1,200</span>
                        <span class="period">per night</span>
                    </div>
                    <a href="rooms.php" class="btn btn-primary btn-block">View Details</a>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Amenities Section -->
<section class="amenities-section">
    <div class="container">
        <h2 class="section-title">Amenities & Services</h2>
        <div class="amenities-grid">
            <div class="amenity-item">
                <div class="amenity-icon">🍳</div>
                <h4>Breakfast Included</h4>
                <p>Delicious homemade breakfast served daily</p>
            </div>
            <div class="amenity-item">
                <div class="amenity-icon">📶</div>
                <h4>Free Wi-Fi</h4>
                <p>High-speed internet throughout the property</p>
            </div>
            <div class="amenity-item">
                <div class="amenity-icon">🚗</div>
                <h4>Secure Parking</h4>
                <p>Safe off-street parking for all guests</p>
            </div>
            <div class="amenity-item">
                <div class="amenity-icon">🌿</div>
                <h4>Garden Views</h4>
                <p>Beautiful landscaped gardens to relax in</p>
            </div>
            <div class="amenity-item">
                <div class="amenity-icon">🧹</div>
                <h4>Daily Housekeeping</h4>
                <p>Fresh towels and clean rooms daily</p>
            </div>
            <div class="amenity-item">
                <div class="amenity-icon">🔒</div>
                <h4>24/7 Security</h4>
                <p>Your safety is our priority</p>
            </div>
        </div>
    </div>
</section>

<!-- Testimonials Section -->
<!--<section class="testimonials-section">
    <div class="container">
        <h2 class="section-title">What Our Guests Say</h2>
        <div class="testimonials-grid">
            <div class="testimonial-card">
                <div class="stars">⭐⭐⭐⭐⭐</div>
                <p>"Wonderful stay at Sara-Lee! The rooms were immaculate, breakfast was delicious, and the hosts were incredibly welcoming. Highly recommended!"</p>
                <div class="testimonial-author">
                    <strong>Sarah M.</strong>
                    <span>Cape Town</span>
                </div>
            </div>
            <div class="testimonial-card">
                <div class="stars">⭐⭐⭐⭐⭐</div>
                <p>"Perfect location and excellent service. The attention to detail and warm hospitality made our anniversary trip truly special. We'll definitely be back!"</p>
                <div class="testimonial-author">
                    <strong>John & Lisa D.</strong>
                    <span>Johannesburg</span>
                </div>
            </div>
            <div class="testimonial-card">
                <div class="stars">⭐⭐⭐⭐⭐</div>
                <p>"Best B&B experience we've had! Clean, comfortable, and the breakfast was outstanding. The garden is beautiful and it's such a peaceful place to stay."</p>
                <div class="testimonial-author">
                    <strong>Michael P.</strong>
                    <span>Durban</span>-->
                </div>
            </div>
        </div>
    </div>
</section>

<!-- CTA Section -->
<section class="cta-section">
    <div class="container">
        <h2>Ready to Book Your Stay?</h2>
        <p>Contact us today to make a reservation or inquire about availability</p>
        <div class="cta-buttons">
            <a href="booking.php" class="btn btn-primary btn-lg">Book Now</a>
            <a href="contact.php" class="btn btn-secondary btn-lg">Contact Us</a>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>