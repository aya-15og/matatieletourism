<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dr PM Jada - Orthopaedic Surgeon</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        :root {
            --primary-cyan: #00B4D8;
            --dark-blue: #023E8A;
            --light-cyan: #90E0EF;
            --white: #FFFFFF;
            --light-gray: #F8F9FA;
            --text-dark: #212529;
            --text-gray: #6C757D;
            --dark-gold: #B8860B;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            color: var(--text-dark);
            overflow-x: hidden;
        }
        
        /* Header Styles */
        header {
            background: var(--white);
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            position: fixed;
            width: 100%;
            top: 0;
            z-index: 1000;
        }
        
        .header-content {
            max-width: 1200px;
            margin: 0 auto;
            padding: 1rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .logo-section {
            display: flex;
            align-items: center;
            gap: 1rem;
        }
        
        .logo-circle {
            width: 60px;
            height: 60px;
            background: linear-gradient(135deg, var(--dark-blue), var(--primary-cyan));
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 4px 15px rgba(0, 180, 216, 0.3);
            overflow: hidden;
        }
        
        .logo-circle img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        
        .logo-circle a {
            width: 100%;
            height: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .logo-text h1 {
            font-size: 1.5rem;
            color: var(--dark-blue);
            font-weight: 700;
        }
        
        .logo-text p {
            font-size: 0.85rem;
            color: var(--primary-cyan);
            font-weight: 600;
        }
        
        .nav-toggle {
            display: none;
            background: none;
            border: none;
            font-size: 1.5rem;
            color: var(--dark-blue);
            cursor: pointer;
        }
        
        nav ul {
            display: flex;
            list-style: none;
            gap: 1rem;
        }
        
        nav a {
            text-decoration: none;
            color: var(--dark-gold);
            font-weight: 700;
            padding: 0.6rem 1.5rem;
            border-radius: 25px;
            background: linear-gradient(135deg, var(--primary-cyan), var(--dark-blue));
            transition: all 0.3s ease;
            box-shadow: 0 3px 10px rgba(0, 180, 216, 0.3);
            display: inline-block;
        }
        
        nav a:hover {
            background: linear-gradient(135deg, var(--dark-blue), var(--primary-cyan));
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0, 180, 216, 0.5);
            color: #8B6914;
        }
        
        nav a:active {
            transform: translateY(0);
        }
        
        /* Hero Section */
        .hero {
            margin-top: 90px;
            background: linear-gradient(135deg, var(--light-cyan), var(--primary-cyan));
            padding: 4rem 1rem;
            text-align: center;
        }
        
        .hero-content {
            max-width: 900px;
            margin: 0 auto;
        }
        
        .credentials-badge {
            display: inline-block;
            background: var(--white);
            padding: 0.75rem 1.5rem;
            border-radius: 50px;
            margin-bottom: 1.5rem;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
        
        .credentials-badge p {
            color: var(--dark-blue);
            font-weight: 600;
            font-size: 0.9rem;
        }
        
        .hero h2 {
            font-size: 2.5rem;
            color: var(--dark-blue);
            margin-bottom: 1rem;
            font-weight: 800;
        }
        
        .hero-subtitle {
            font-size: 1.5rem;
            color: var(--dark-blue);
            margin-bottom: 1.5rem;
            font-weight: 600;
        }
        
        .hero-description {
            font-size: 1.1rem;
            color: var(--text-dark);
            margin-bottom: 2rem;
            max-width: 700px;
            margin-left: auto;
            margin-right: auto;
        }
        
        .cta-button {
            display: inline-block;
            background: var(--dark-blue);
            color: var(--white);
            padding: 1rem 2.5rem;
            border-radius: 50px;
            text-decoration: none;
            font-weight: 700;
            font-size: 1.1rem;
            transition: transform 0.3s, box-shadow 0.3s;
            box-shadow: 0 4px 15px rgba(2, 62, 138, 0.3);
        }
        
        .cta-button:hover {
            transform: translateY(-3px);
            box-shadow: 0 6px 20px rgba(2, 62, 138, 0.4);
        }
        
        /* Section Styles */
        section {
            padding: 4rem 1rem;
        }
        
        .section-header {
            text-align: center;
            margin-bottom: 3rem;
        }
        
        .section-header h3 {
            font-size: 2.5rem;
            color: var(--dark-blue);
            margin-bottom: 0.5rem;
            font-weight: 700;
        }
        
        .section-divider {
            width: 100px;
            height: 4px;
            background: linear-gradient(to right, var(--primary-cyan), var(--dark-blue));
            margin: 0 auto;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
        }
        
        /* About Section */
        .about-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 2rem;
        }
        
        .about-card {
            background: var(--white);
            padding: 2rem;
            border-radius: 15px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
            text-align: center;
            transition: transform 0.3s;
            border-top: 4px solid var(--primary-cyan);
        }
        
        .about-card:hover {
            transform: translateY(-10px);
        }
        
        .about-icon {
            width: 70px;
            height: 70px;
            background: linear-gradient(135deg, var(--primary-cyan), var(--dark-blue));
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1.5rem;
            font-size: 2rem;
        }
        
        .about-card h4 {
            color: var(--dark-blue);
            font-size: 1.3rem;
            margin-bottom: 1rem;
            font-weight: 700;
        }
        
        .about-card p {
            color: var(--text-gray);
            line-height: 1.8;
        }
        
        /* Services Section */
        .services-section {
            background: var(--light-gray);
        }
        
        .services-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 1.5rem;
        }
        
        .service-item {
            background: var(--white);
            padding: 1.5rem;
            border-radius: 10px;
            border-left: 5px solid var(--primary-cyan);
            box-shadow: 0 3px 15px rgba(0,0,0,0.08);
            transition: all 0.3s;
        }
        
        .service-item:hover {
            border-left-color: var(--dark-blue);
            box-shadow: 0 5px 25px rgba(0,0,0,0.15);
        }
        
        .service-item h4 {
            color: var(--dark-blue);
            font-size: 1.1rem;
            font-weight: 600;
        }
        
        /* Hours Section */
        .hours-card {
            max-width: 700px;
            margin: 0 auto;
            background: var(--white);
            padding: 2.5rem;
            border-radius: 20px;
            box-shadow: 0 5px 30px rgba(0,0,0,0.1);
        }
        
        .hours-icon {
            text-align: center;
            font-size: 3rem;
            margin-bottom: 1.5rem;
        }
        
        .hours-list {
            list-style: none;
        }
        
        .hours-item {
            display: flex;
            justify-content: space-between;
            padding: 1rem 0;
            border-bottom: 2px solid var(--light-gray);
        }
        
        .hours-item:last-child {
            border-bottom: none;
        }
        
        .hours-day {
            font-weight: 600;
            color: var(--dark-blue);
        }
        
        .hours-time {
            color: var(--text-gray);
        }
        
        .hours-note {
            margin-top: 1.5rem;
            padding: 1rem;
            background: var(--light-cyan);
            border-radius: 10px;
            text-align: center;
        }
        
        .hours-note p {
            color: var(--dark-blue);
            font-weight: 600;
        }
        
        /* Contact Section */
        .contact-section {
            background: var(--light-gray);
        }
        
        .contact-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 2rem;
        }
        
        .contact-card {
            background: var(--white);
            padding: 2rem;
            border-radius: 15px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
        }
        
        .contact-icon {
            width: 60px;
            height: 60px;
            background: var(--light-cyan);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 1rem;
            font-size: 1.5rem;
        }
        
        .contact-card h4 {
            color: var(--dark-blue);
            font-size: 1.3rem;
            margin-bottom: 1rem;
            font-weight: 700;
        }
        
        .contact-card p {
            color: var(--text-gray);
            margin-bottom: 0.5rem;
        }
        
        .contact-card a {
            color: var(--primary-cyan);
            text-decoration: none;
            font-weight: 600;
            font-size: 1.2rem;
        }
        
        .contact-card a:hover {
            color: var(--dark-blue);
        }
        
        .cta-card {
            background: linear-gradient(135deg, var(--dark-blue), var(--primary-cyan));
            color: var(--white);
        }
        
        .cta-card h4,
        .cta-card p {
            color: var(--white);
        }
        
        /* Contact Form */
        .contact-form {
            background: var(--white);
            padding: 2.5rem;
            border-radius: 15px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
        }
        
        .form-group {
            margin-bottom: 1.5rem;
        }
        
        .form-group label {
            display: block;
            color: var(--dark-blue);
            font-weight: 600;
            margin-bottom: 0.5rem;
        }
        
        .form-group input,
        .form-group textarea {
            width: 100%;
            padding: 0.75rem;
            border: 2px solid var(--light-gray);
            border-radius: 8px;
            font-size: 1rem;
            transition: border-color 0.3s;
        }
        
        .form-group input:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: var(--primary-cyan);
        }
        
        .form-group textarea {
            resize: vertical;
            min-height: 120px;
        }
        
        .submit-btn {
            width: 100%;
            background: linear-gradient(135deg, var(--primary-cyan), var(--dark-blue));
            color: var(--white);
            padding: 1rem;
            border: none;
            border-radius: 50px;
            font-size: 1.1rem;
            font-weight: 700;
            cursor: pointer;
            transition: transform 0.3s, box-shadow 0.3s;
        }
        
        .submit-btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 5px 20px rgba(0, 180, 216, 0.4);
        }
        
        /* Footer */
        footer {
            background: var(--dark-blue);
            color: var(--white);
            padding: 2.5rem 1rem;
            text-align: center;
        }
        
        .footer-logo {
            font-size: 2rem;
            margin-bottom: 1rem;
        }
        
        footer h4 {
            font-size: 1.8rem;
            margin-bottom: 0.5rem;
        }
        
        footer p {
            color: var(--light-cyan);
            margin-bottom: 0.5rem;
        }
        
        .footer-divider {
            width: 100%;
            height: 1px;
            background: var(--primary-cyan);
            margin: 1.5rem 0;
            opacity: 0.3;
        }
        
        .footer-bottom {
            font-size: 0.9rem;
            color: var(--light-cyan);
        }
        
        /* Mobile Responsive */
        @media (max-width: 768px) {
            .nav-toggle {
                display: block;
            }
            
            nav {
                position: absolute;
                top: 100%;
                left: 0;
                right: 0;
                background: var(--white);
                box-shadow: 0 5px 15px rgba(0,0,0,0.1);
                max-height: 0;
                overflow: hidden;
                transition: max-height 0.3s ease;
            }
            
            nav.active {
                max-height: 500px;
            }
            
            nav ul {
                flex-direction: column;
                padding: 1rem;
                gap: 0.75rem;
            }
            
            nav li {
                padding: 0;
            }
            
            nav a {
                display: block;
                text-align: center;
            }
            
            .hero h2 {
                font-size: 2rem;
            }
            
            .hero-subtitle {
                font-size: 1.2rem;
            }
            
            .section-header h3 {
                font-size: 2rem;
            }
            
            .contact-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <!-- Header -->
    <header>
        <div class="header-content">
            <div class="logo-section">
                <div class="logo-circle">
                    <a href="/">
                        <img src="assets/jada.png" alt="Dr PM Jada">
                    </a>
                </div>
                <div class="logo-text">
                    <h1>Dr. PM Jada</h1>
                    <p>Orthopaedic Surgeon</p>
                </div>
            </div>
            <button class="nav-toggle" onclick="toggleMenu()">☰</button>
            <nav id="mainNav">
                <ul>
                    <li><a href="#home">Home</a></li>
                    <li><a href="#about">About</a></li>
                    <li><a href="#services">Services</a></li>
                    <li><a href="#hours">Hours</a></li>
                    <li><a href="#contact">Contact</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <!-- Hero Section -->
    <section id="home" class="hero">
        <div class="hero-content">
            <div class="credentials-badge">
                <p>MBBCh (Wits) • FC (Orth) SA • MMed (Orth) Wits</p>
            </div>
            <h2>Dr Prince Masibulele Jada</h2>
            <p class="hero-subtitle">ORTHOPAEDIC SURGEON</p>
            <p class="hero-description">
                Specialist in bone conditions for all ages. Providing expert orthopaedic care 
                with compassion, dedication, and the latest medical techniques.
            </p>
            <a href="#contact" class="cta-button">Book an Appointment</a>
        </div>
    </section>

    <!-- About Section -->
    <section id="about">
        <div class="container">
            <div class="section-header">
                <h3>About Dr. Jada</h3>
                <div class="section-divider"></div>
            </div>
            <div class="about-grid">
                <div class="about-card">
                    <div class="about-icon">🏆</div>
                    <h4>Highly Qualified</h4>
                    <p>Fellowship in Orthopaedic Surgery with extensive training from the University of Witwatersrand. Registered specialist with the Health Professions Council of South Africa.</p>
                </div>
                <div class="about-card">
                    <div class="about-icon">👥</div>
                    <h4>All Ages Welcome</h4>
                    <p>Comprehensive orthopaedic care for patients of all ages, from pediatric conditions to adult trauma and degenerative disorders.</p>
                </div>
                <div class="about-card">
                    <div class="about-icon">❤️</div>
                    <h4>Patient-Centered Care</h4>
                    <p>Dedicated to providing compassionate, personalized treatment plans tailored to each patient's unique needs and recovery goals.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Services Section -->
    <section id="services" class="services-section">
        <div class="container">
            <div class="section-header">
                <h3>Our Services</h3>
                <div class="section-divider"></div>
            </div>
            <div class="services-grid">
                <div class="service-item">
                    <h4>Joint Replacement Surgery</h4>
                </div>
                <div class="service-item">
                    <h4>Sports Injury Treatment</h4>
                </div>
                <div class="service-item">
                    <h4>Fracture Management</h4>
                </div>
                <div class="service-item">
                    <h4>Arthroscopic Surgery</h4>
                </div>
                <div class="service-item">
                    <h4>Spinal Disorders</h4>
                </div>
                <div class="service-item">
                    <h4>Paediatric Orthopaedics</h4>
                </div>
                <div class="service-item">
                    <h4>Medico Legal Assessments</h4>
                </div>
                <div class="service-item">
                    <h4>Occupational Health Assessments</h4>
                </div>
                <div class="service-item">
                    <h4>Trauma Surgery</h4>
                </div>
            </div>
        </div>
    </section>

    <!-- Hours Section -->
    <section id="hours">
        <div class="container">
            <div class="section-header">
                <h3>Consultation Hours</h3>
                <div class="section-divider"></div>
            </div>
            <div class="hours-card">
                <div class="hours-icon">🕐</div>
                <ul class="hours-list">
                    <li class="hours-item">
                        <span class="hours-day">Monday - Friday</span>
                        <span class="hours-time">08:00 - 16:00</span>
                    </li>
                    <li class="hours-item">
                        <span class="hours-day">Tuesday</span>
                        <span class="hours-time">11:00 - 16:00 (Consultations)</span>
                    </li>
                    <li class="hours-item">
                        <span class="hours-day">Wednesday</span>
                        <span class="hours-time">12:00 - 16:00 (Consultations)</span>
                    </li>
                    <li class="hours-item">
                        <span class="hours-day">Thursday</span>
                        <span class="hours-time">13:00 - 16:00 (Medico Legal)</span>
                    </li>
                    <li class="hours-item">
                        <span class="hours-day">Monday & Friday</span>
                        <span class="hours-time">Theatre Days</span>
                    </li>
                </ul>
                <div class="hours-note">
                    <p>Closed on Public Holidays and Weekends</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Contact Section -->
    <section id="contact" class="contact-section">
        <div class="container">
            <div class="section-header">
                <h3>Get in Touch</h3>
                <div class="section-divider"></div>
            </div>
            <div class="contact-grid">
                <div>
                    <div class="contact-card">
                        <div class="contact-icon">📍</div>
                        <h4>Location</h4>
                        <p>Suite 207 (2nd floor)</p>
                        <p>Royal Buffalo Specialist Hospital</p>
                        <p>Amalinda Main Road</p>
                        <p>East London, 5201</p>
                    </div>
                    
                    <div class="contact-card" style="margin-top: 2rem;">
                        <div class="contact-icon">📞</div>
                        <h4>Phone</h4>
                        <a href="tel:043 422 0454">043 422 0454</a>
                    </div>
                    
                    <div class="contact-card" style="margin-top: 2rem;">
                        <div class="contact-icon">✉️</div>
                        <h4>Email</h4>
                        <a href="mailto:info@drpmjada.co.za">info@drpmjada.co.za</a>
                    </div>
                    
                    <div class="contact-card cta-card" style="margin-top: 2rem;">
                        <div class="contact-icon">📅</div>
                        <h4>Ready to Book?</h4>
                        <p>Call us during business hours or fill out the appointment request form.</p>
                        <p style="margin-top: 1rem;">We look forward to helping you on your path to recovery.</p>
                    </div>
                </div>
                
                <div class="contact-form">
                    <h4 style="color: var(--dark-blue); margin-bottom: 1.5rem; font-size: 1.8rem;">Request an Appointment</h4>
                    <div class="form-group">
                        <label for="name">Full Name</label>
                        <input type="text" id="name" name="name" placeholder="Your full name" required>
                    </div>
                    <div class="form-group">
                        <label for="email">Email</label>
                        <input type="email" id="email" name="email" placeholder="your.email@example.com" required>
                    </div>
                    <div class="form-group">
                        <label for="phone">Phone Number</label>
                        <input type="tel" id="phone" name="phone" placeholder="Your phone number" required>
                    </div>
                    <div class="form-group">
                        <label for="message">Message</label>
                        <textarea id="message" name="message" placeholder="Please describe your condition or reason for appointment..." required></textarea>
                    </div>
                    <button type="submit" class="submit-btn" onclick="submitForm()">Submit Request</button>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer>
        <div class="container">
            <div class="footer-logo">🩺</div>
            <h4>Dr. PM Jada</h4>
            <p>Orthopaedic Specialist Surgeon</p>
            <p>MBBCh (Wits) • FC (Orth) SA • MMed (Orth) Wits</p>
            <p style="font-size: 0.9rem; margin-top: 0.5rem;">MP0718831 | Practice No: 0808237</p>
            <div class="footer-divider"></div>
            <div class="footer-bottom">
                <p>&copy; 2024 Dr. PM Jada. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <script>
        function toggleMenu() {
            const nav = document.getElementById('mainNav');
            nav.classList.toggle('active');
        }
        
        function submitForm() {
            const name = document.getElementById('name').value;
            const email = document.getElementById('email').value;
            const phone = document.getElementById('phone').value;
            const message = document.getElementById('message').value;
            
            if (name && email && phone && message) {
                alert('Thank you for your appointment request! We will contact you shortly.');
                // Clear form
                document.getElementById('name').value = '';
                document.getElementById('email').value = '';
                document.getElementById('phone').value = '';
                document.getElementById('message').value = '';
            } else {
                alert('Please fill in all fields.');
            }
        }
        
        // Smooth scrolling for anchor links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                    // Close mobile menu if open
                    document.getElementById('mainNav').classList.remove('active');
                }
            });
        });
    </script>
</body>
</html>