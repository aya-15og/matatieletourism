<?php
session_start();
$page = 'about';
include 'includes/header.php';
?>

<!-- Page Header -->
<section class="page-header">
    <div class="page-header-overlay"></div>
    <div class="page-header-content">
        <h1>About Us</h1>
        <p>Your home away from home</p>
    </div>
</section>

<!-- About Section -->
<section class="about-section">
    <div class="container">
        <div class="about-content">
            <div class="about-text">
                <h2>Welcome to Sara-Lee Guesthouse</h2>
                <p>
                    Nestled along scenic Seymour Street, Sara-Lee Guesthouse blends modern comfort with a warm, homely atmosphere. 
                    Whether you're here for business, exploring the region, or simply seeking a peaceful retreat, our guesthouse 
                    offers the ideal balance of relaxation and convenience.
                </p>
                <p>
                    From cozy rooms to delicious homemade breakfasts and tranquil gardens, every detail is designed to make 
                    you feel at home. Our friendly team ensures every stay is personal, memorable, and filled with genuine hospitality.
                </p>
            </div>
            <div class="about-image">
                <img src="images/gallery/exterior/drone-view-pool side.jpg" alt="Sara-Lee Guesthouse">
            </div>
        </div>
    </div>
</section>

<!-- Values Section -->
<section class="values-section">
    <div class="container">
        <h2 class="section-title">Our Values</h2>
        <p class="section-subtitle">What makes Sara-Lee special</p>
        
        <div class="values-grid">
            <div class="value-card">
                <div class="value-icon">🏠</div>
                <h3>Hospitality</h3>
                <p>We treat every guest like family, ensuring you feel welcome and comfortable throughout your stay.</p>
            </div>
            
            <div class="value-card">
                <div class="value-icon">✨</div>
                <h3>Quality</h3>
                <p>From our rooms to our breakfast, we maintain the highest standards in everything we do.</p>
            </div>
            
            <div class="value-card">
                <div class="value-icon">💚</div>
                <h3>Care</h3>
                <p>We pay attention to the little details that make a big difference in your comfort and enjoyment.</p>
            </div>
            
            <div class="value-card">
                <div class="value-icon">🌿</div>
                <h3>Tranquility</h3>
                <p>Our peaceful environment and beautiful gardens provide the perfect escape from everyday stress.</p>
            </div>
        </div>
    </div>
</section>

<!-- Our Story Section -->
<section class="about-section">
    <div class="container">
        <div class="about-content">
            <div class="about-image">
                <img src="images/gallery/sara-lee-storytelling.jpg" alt="Sara-Lee History">
            </div>
            <div class="about-text">
                <h2>Our Story</h2>
                <p>
                    Sara-Lee Guesthouse began with a simple dream — to create a place where travelers could experience true 
                    South African hospitality in a relaxed, home-like setting. What started as a small passion project has 
                    grown into one of the area’s most cherished getaways.
                </p>
                <p>
                    Over the years, we’ve welcomed guests from across the world, building lasting friendships through warmth, 
                    comfort, and care. While we’ve modernized our facilities, our mission remains the same — to make every guest 
                    feel like family.
                </p>
            </div>
        </div>
    </div>
</section>

<!-- Features Section -->
<section class="amenities-section">
    <div class="container">
        <h2 class="section-title">Why Choose Sara-Lee</h2>
        <p class="section-subtitle">Experience the difference</p>
        
        <div class="amenities-grid">
            <div class="amenity-item">
                <div class="amenity-icon">👥</div>
                <h4>Personal Service</h4>
                <p>Attentive hosts who go above and beyond to ensure your comfort</p>
            </div>
            
            <div class="amenity-item">
                <div class="amenity-icon">🏡</div>
                <h4>Homely Atmosphere</h4>
                <p>A warm, welcoming environment that feels like your own home</p>
            </div>
            
            <div class="amenity-item">
                <div class="amenity-icon">🍳</div>
                <h4>Delicious Breakfast</h4>
                <p>Freshly prepared homemade breakfast to start your day right</p>
            </div>
            
            <div class="amenity-item">
                <div class="amenity-icon">📍</div>
                <h4>Prime Location</h4>
                <p>Conveniently located on Seymour Street with easy access</p>
            </div>
            
            <div class="amenity-item">
                <div class="amenity-icon">🛏️</div>
                <h4>Comfortable Rooms</h4>
                <p>Well-appointed rooms with quality furnishings and linens</p>
            </div>
            
            <div class="amenity-item">
                <div class="amenity-icon">🌺</div>
                <h4>Beautiful Gardens</h4>
                <p>Lush, landscaped gardens perfect for relaxation</p>
            </div>
            
            <div class="amenity-item">
                <div class="amenity-icon">🔐</div>
                <h4>Safe & Secure</h4>
                <p>Secure parking and 24/7 security for peace of mind</p>
            </div>
            
            <div class="amenity-item">
                <div class="amenity-icon">💰</div>
                <h4>Great Value</h4>
                <p>Competitive rates without compromising on quality</p>
            </div>
        </div>
    </div>
</section>

<!-- Team/Host Section -->
<section class="about-section" style="background: var(--bg-light);">
    <div class="container" style="text-align: center; max-width: 800px;">
        <h2 style="color: var(--primary-color);">Meet Your Hosts</h2>
        <p style="font-size: 1.1rem; color: var(--text-light); margin-bottom: 30px;">
            At Sara-Lee Guesthouse, you're not just a guest – you're part of our extended family. Our dedicated 
            team is committed to making your stay memorable with genuine warmth and personalized attention.
        </p>
        <p style="font-size: 1.1rem; color: var(--text-light); margin-bottom: 30px;">
            We understand that the little things matter. Whether it's remembering your coffee preference, 
            recommending the best local attractions, or simply ensuring your room is just right, we're here 
            to make your stay as comfortable and enjoyable as possible.
        </p>
        <div style="background: var(--white); padding: 30px; border-radius: 15px; box-shadow: var(--shadow); margin-top: 40px;">
            <p style="font-size: 1.2rem; font-style: italic; color: var(--text-dark); margin-bottom: 15px;">
                "Our greatest joy is seeing our guests leave with smiles on their faces and memories in their hearts. 
                We look forward to welcoming you to Sara-Lee Guesthouse."
            </p>
            <p style="color: var(--primary-color); font-weight: 600; margin: 0;">- The Sara-Lee Team</p>
        </div>
    </div>
</section>

<!-- CTA Section -->
<section class="cta-section">
    <div class="container">
        <h2>Ready to Experience Sara-Lee?</h2>
        <p>Book your stay today and discover why our guests keep coming back</p>
        <div class="cta-buttons">
            <a href="booking.php" class="btn btn-primary btn-lg">Book Now</a>
            <a href="contact.php" class="btn btn-secondary btn-lg">Contact Us</a>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>
