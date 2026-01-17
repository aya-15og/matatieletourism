<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sara-Lee Guesthouse - Bed & Breakfast | 6 Seymour Street</title>
    <meta name="description" content="Sara-Lee Guesthouse offers comfortable bed and breakfast accommodation on 6 Seymour Street. Call 079 891 1983 to book your stay.">
    <link rel="stylesheet" href="css/style.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;600;700&family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/sara/css/gallery.css">
</head>
<body>
    <!-- Top Bar -->
    <div class="top-bar">
        <div class="container">
            <div class="top-bar-content">
                <div class="contact-info">
                    <span>📞 <a href="tel:0798911983">079 891 1983</a></span>
                    <span>✉️ <a href="mailto:bookings@sara-lee.co.za">bookings@sara-lee.co.za</a></span>
                </div>
                <div class="social-links">
                    <a href="#" aria-label="Facebook">Facebook</a>
                    <a href="#" aria-label="Instagram">Instagram</a>
                </div>
            </div>
        </div>
    </div>

    <!-- Navigation -->
    <nav class="navbar">
        <div class="container">
            <div class="nav-wrapper">
                <div class="logo">
                    <a href="index.php">
                        <span class="logo-text">Sara-Lee</span>
                        <span class="logo-subtitle">Guesthouse</span>
                    </a>
                </div>
                
                <div class="nav-toggle" id="navToggle">
                    <span></span>
                    <span></span>
                    <span></span>
                </div>

                <ul class="nav-menu" id="navMenu">
                    <li><a href="index.php" class="<?php echo ($page == 'home') ? 'active' : ''; ?>">Home</a></li>
                    <li><a href="about.php" class="<?php echo ($page == 'about') ? 'active' : ''; ?>">About</a></li>
                    <li><a href="rooms.php" class="<?php echo ($page == 'rooms') ? 'active' : ''; ?>">Rooms</a></li>
                    <li><a href="gallery.php" class="<?php echo ($page == 'gallery') ? 'active' : ''; ?>">Gallery</a></li>
                    <li><a href="contact.php" class="<?php echo ($page == 'contact') ? 'active' : ''; ?>">Contact</a></li>
                    <li><a href="booking.php" class="btn-book <?php echo ($page == 'booking') ? 'active' : ''; ?>">Book Now</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <script>
        // Mobile menu toggle
        const navToggle = document.getElementById('navToggle');
        const navMenu = document.getElementById('navMenu');
        
        navToggle.addEventListener('click', function() {
            navMenu.classList.toggle('active');
            navToggle.classList.toggle('active');
        });

        // Sticky navbar
        window.addEventListener('scroll', function() {
            const navbar = document.querySelector('.navbar');
            if (window.scrollY > 100) {
                navbar.classList.add('sticky');
            } else {
                navbar.classList.remove('sticky');
            }
        });
    </script>