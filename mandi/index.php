<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dr. Mandilakhe Msingapantsi - Orthopaedic Surgeon | Rustenburg, South Africa</title>
    <meta name="description" content="Dr. Mandilakhe Msingapantsi MBChB(UKZN), Dip HivM(UKZN), FC(Ortho) SA, MMed Ortho(SMU) - Specialist orthopaedic surgeon in Rustenburg at Medicare Private Hospital and Life Peglerae Hospital.">
    <meta name="keywords" content="orthopaedic surgeon, Rustenburg, bone doctor, joint replacement, fracture care, sports medicine">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        :root {
            --primary-blue: #1e3a8a;
            --secondary-blue: #3b82f6;
            --light-blue: #dbeafe;
            --accent-red: #ef4444;
            --dark-gray: #1f2937;
            --light-gray: #f3f4f6;
            --white: #ffffff;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            color: var(--dark-gray);
            overflow-x: hidden;
        }

        /* Header Styles */
        header {
            background: rgba(255, 255, 255, 0.98);
            backdrop-filter: blur(10px);
            color: var(--primary-blue);
            padding: 0.5rem 0;
            position: fixed;
            width: 100%;
            top: 0;
            z-index: 1000;
            box-shadow: 0 2px 20px rgba(30, 58, 138, 0.1);
            border-bottom: 2px solid var(--light-blue);
        }

        nav {
            max-width: 1200px;
            margin: 0 auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0 2rem;
        }

        .logo-container {
            display: flex;
            align-items: center;
            gap: 1rem;
            transition: transform 0.3s;
        }

        .logo-container:hover {
            transform: scale(1.05);
        }

        .logo-image {
            height: 60px;
            width: auto;
            object-fit: contain;
            background: linear-gradient(135deg, var(--primary-blue), var(--secondary-blue));
            padding: 0.5rem;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(30, 58, 138, 0.2);
        }

        .logo-text {
            font-size: 1.2rem;
            font-weight: bold;
            line-height: 1.2;
            color: var(--primary-blue);
        }

        .logo-text small {
            display: block;
            font-size: 0.7rem;
            font-weight: normal;
            color: var(--secondary-blue);
        }

        nav ul {
            list-style: none;
            display: flex;
            gap: 0.5rem;
            align-items: center;
        }

        nav li a {
            color: var(--primary-blue);
            text-decoration: none;
            font-weight: 600;
            padding: 0.7rem 1.5rem;
            border-radius: 50px;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
            display: inline-block;
        }

        nav li a::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(135deg, var(--secondary-blue), var(--primary-blue));
            transition: left 0.3s ease;
            z-index: -1;
            border-radius: 50px;
        }

        nav li a:hover {
            color: var(--white);
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(59, 130, 246, 0.3);
        }

        nav li a:hover::before {
            left: 0;
        }

        nav li a.active {
            background: linear-gradient(135deg, var(--secondary-blue), var(--primary-blue));
            color: var(--white);
            box-shadow: 0 4px 12px rgba(59, 130, 246, 0.4);
        }

        .mobile-menu-btn {
            display: none;
            background: linear-gradient(135deg, var(--secondary-blue), var(--primary-blue));
            border: none;
            color: var(--white);
            font-size: 1.5rem;
            cursor: pointer;
            padding: 0.5rem 1rem;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3);
            transition: all 0.3s;
        }

        .mobile-menu-btn:hover {
            transform: scale(1.05);
            box-shadow: 0 6px 20px rgba(59, 130, 246, 0.4);
        }

        /* Hero Section */
        .hero {
            background: linear-gradient(135deg, rgba(30, 58, 138, 0.95) 0%, rgba(59, 130, 246, 0.95) 100%),
                        url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1200 600"><rect fill="%231e3a8a" width="1200" height="600"/></svg>');
            background-size: cover;
            background-position: center;
            color: var(--white);
            padding: 180px 2rem 120px;
            text-align: center;
            margin-top: 80px;
            position: relative;
            overflow: hidden;
        }

        .hero::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1440 320"><path fill="%23ffffff" fill-opacity="0.05" d="M0,96L48,112C96,128,192,160,288,160C384,160,480,128,576,122.7C672,117,768,139,864,154.7C960,171,1056,181,1152,165.3C1248,149,1344,107,1392,85.3L1440,64L1440,320L1392,320C1344,320,1248,320,1152,320C1056,320,960,320,864,320C768,320,672,320,576,320C480,320,384,320,288,320C192,320,96,320,48,320L0,320Z"></path></svg>');
            background-size: cover;
            background-position: bottom;
            opacity: 0.3;
        }

        .hero-content {
            position: relative;
            z-index: 1;
        }

        .hero h1 {
            font-size: 3.5rem;
            margin-bottom: 0.5rem;
            animation: fadeInUp 1s ease;
            text-shadow: 0 2px 10px rgba(0,0,0,0.2);
        }

        .hero .credentials {
            font-size: 1.1rem;
            margin-bottom: 1rem;
            opacity: 0.95;
            animation: fadeInUp 1s ease 0.1s both;
            background: rgba(255,255,255,0.1);
            display: inline-block;
            padding: 0.5rem 1.5rem;
            border-radius: 50px;
            backdrop-filter: blur(10px);
        }

        .hero p {
            font-size: 1.4rem;
            margin-bottom: 2rem;
            animation: fadeInUp 1s ease 0.2s both;
        }

        .cta-button {
            display: inline-block;
            background: var(--accent-red);
            color: var(--white);
            padding: 1.2rem 3rem;
            text-decoration: none;
            border-radius: 50px;
            font-weight: bold;
            font-size: 1.1rem;
            transition: all 0.3s;
            animation: fadeInUp 1s ease 0.4s both;
            box-shadow: 0 8px 25px rgba(239, 68, 68, 0.4);
            position: relative;
            overflow: hidden;
        }

        .cta-button::before {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            width: 0;
            height: 0;
            border-radius: 50%;
            background: rgba(255,255,255,0.2);
            transform: translate(-50%, -50%);
            transition: width 0.6s, height 0.6s;
        }

        .cta-button:hover::before {
            width: 300px;
            height: 300px;
        }

        .cta-button:hover {
            background: #dc2626;
            transform: translateY(-5px);
            box-shadow: 0 15px 35px rgba(239, 68, 68, 0.5);
        }

        /* Section Styles */
        section {
            padding: 5rem 2rem;
            max-width: 1200px;
            margin: 0 auto;
        }

        h2 {
            font-size: 2.5rem;
            color: var(--primary-blue);
            margin-bottom: 2rem;
            text-align: center;
            position: relative;
            padding-bottom: 1rem;
        }

        h2::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 50%;
            transform: translateX(-50%);
            width: 100px;
            height: 4px;
            background: var(--accent-red);
            border-radius: 2px;
        }

        .about-content {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 3rem;
            align-items: center;
            margin-top: 3rem;
        }

        .about-image {
            background: linear-gradient(135deg, var(--primary-blue), var(--secondary-blue));
            height: 400px;
            border-radius: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--white);
            font-size: 4rem;
            box-shadow: 0 20px 60px rgba(30, 58, 138, 0.3);
        }

        .about-text p {
            margin-bottom: 1rem;
            font-size: 1.1rem;
            line-height: 1.8;
        }

        /* Services Grid */
        .services-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 2rem;
            margin-top: 3rem;
        }

        .service-card {
            background: var(--white);
            padding: 2.5rem;
            border-radius: 20px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.08);
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            border-top: 4px solid var(--secondary-blue);
            position: relative;
            overflow: hidden;
        }

        .service-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(135deg, var(--secondary-blue), var(--primary-blue));
            opacity: 0;
            transition: opacity 0.4s;
            z-index: 0;
        }

        .service-card:hover::before {
            opacity: 0.05;
        }

        .service-card:hover {
            transform: translateY(-15px) scale(1.02);
            box-shadow: 0 20px 50px rgba(59, 130, 246, 0.25);
            border-top-color: var(--accent-red);
        }

        .service-card > * {
            position: relative;
            z-index: 1;
        }

        .service-icon {
            font-size: 3.5rem;
            margin-bottom: 1.5rem;
            display: inline-block;
            transition: all 0.4s;
            filter: drop-shadow(0 4px 8px rgba(59, 130, 246, 0.2));
        }

        .service-card:hover .service-icon {
            transform: scale(1.2) rotate(5deg);
            filter: drop-shadow(0 8px 16px rgba(239, 68, 68, 0.3));
        }

        .service-card h3 {
            color: var(--primary-blue);
            margin-bottom: 1rem;
            font-size: 1.5rem;
        }

        /* Conditions Section */
        .conditions-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
            margin-top: 3rem;
        }

        .condition-item {
            background: linear-gradient(135deg, var(--light-blue), #eff6ff);
            padding: 1.5rem;
            border-radius: 15px;
            border-left: 5px solid var(--secondary-blue);
            transition: all 0.4s ease;
            cursor: pointer;
            position: relative;
            overflow: hidden;
        }

        .condition-item::after {
            content: '→';
            position: absolute;
            right: 1.5rem;
            top: 50%;
            transform: translateY(-50%) translateX(10px);
            opacity: 0;
            transition: all 0.3s;
            font-size: 1.5rem;
            color: var(--white);
        }

        .condition-item:hover {
            background: linear-gradient(135deg, var(--secondary-blue), var(--primary-blue));
            color: var(--white);
            transform: translateX(15px) scale(1.02);
            box-shadow: 0 8px 25px rgba(59, 130, 246, 0.3);
            border-left-color: var(--accent-red);
        }

        .condition-item:hover::after {
            opacity: 1;
            transform: translateY(-50%) translateX(0);
        }

        /* Qualifications */
        .qualifications {
            background: var(--light-gray);
        }

        .qual-list {
            max-width: 800px;
            margin: 3rem auto 0;
        }

        .qual-item {
            background: var(--white);
            padding: 2rem;
            margin-bottom: 1.5rem;
            border-radius: 15px;
            border-left: 6px solid var(--secondary-blue);
            box-shadow: 0 3px 15px rgba(0,0,0,0.05);
            transition: all 0.4s ease;
            position: relative;
            overflow: hidden;
        }

        .qual-item::before {
            content: '';
            position: absolute;
            left: 0;
            top: 0;
            width: 6px;
            height: 100%;
            background: linear-gradient(180deg, var(--accent-red), var(--secondary-blue));
            transform: scaleY(0);
            transition: transform 0.4s ease;
        }

        .qual-item:hover {
            transform: translateX(10px);
            box-shadow: 0 8px 30px rgba(59, 130, 246, 0.2);
            border-left-color: var(--accent-red);
        }

        .qual-item:hover::before {
            transform: scaleY(1);
        }

        .qual-item h4 {
            color: var(--primary-blue);
            margin-bottom: 0.5rem;
        }

        /* Contact Section */
        .contact {
            background: linear-gradient(135deg, var(--primary-blue) 0%, var(--secondary-blue) 100%);
            color: var(--white);
        }

        .contact h2::after {
            background: var(--white);
        }

        .contact-content {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 3rem;
            margin-top: 3rem;
        }

        .contact-info {
            display: flex;
            flex-direction: column;
            gap: 2rem;
        }

        .contact-item {
            display: flex;
            align-items: center;
            gap: 1.5rem;
            background: rgba(255,255,255,0.1);
            padding: 2rem;
            border-radius: 15px;
            backdrop-filter: blur(10px);
            transition: all 0.4s ease;
            border: 2px solid transparent;
        }

        .contact-item:hover {
            background: rgba(255,255,255,0.15);
            transform: translateX(10px);
            border-color: rgba(255,255,255,0.3);
            box-shadow: 0 8px 25px rgba(0,0,0,0.2);
        }

        .contact-icon {
            font-size: 2.5rem;
            color: var(--accent-red);
            transition: all 0.4s ease;
            filter: drop-shadow(0 4px 8px rgba(239, 68, 68, 0.3));
        }

        .contact-item:hover .contact-icon {
            transform: scale(1.2) rotate(10deg);
        }

        .contact-form {
            background: var(--white);
            padding: 2rem;
            border-radius: 15px;
            color: var(--dark-gray);
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 600;
            color: var(--primary-blue);
        }

        .form-group input,
        .form-group textarea {
            width: 100%;
            padding: 0.8rem;
            border: 2px solid var(--light-blue);
            border-radius: 8px;
            font-size: 1rem;
            transition: border-color 0.3s;
        }

        .form-group input:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: var(--secondary-blue);
        }

        .form-group textarea {
            resize: vertical;
            min-height: 120px;
        }

        .submit-btn {
            background: linear-gradient(135deg, var(--accent-red), #dc2626);
            color: var(--white);
            padding: 1.2rem 2rem;
            border: none;
            border-radius: 50px;
            font-size: 1.1rem;
            font-weight: bold;
            cursor: pointer;
            transition: all 0.4s ease;
            width: 100%;
            position: relative;
            overflow: hidden;
            box-shadow: 0 6px 20px rgba(239, 68, 68, 0.3);
        }

        .submit-btn::before {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            width: 0;
            height: 0;
            border-radius: 50%;
            background: rgba(255,255,255,0.3);
            transform: translate(-50%, -50%);
            transition: width 0.6s, height 0.6s;
        }

        .submit-btn:hover::before {
            width: 400px;
            height: 400px;
        }

        .submit-btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 12px 35px rgba(239, 68, 68, 0.4);
        }

        .submit-btn:active {
            transform: translateY(0);
        }

        .submit-btn:disabled {
            background: #9ca3af;
            cursor: not-allowed;
            transform: none;
        }

        /* Footer */
        footer {
            background: var(--dark-gray);
            color: var(--white);
            text-align: center;
            padding: 2rem;
        }

        /* Animations */
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .mobile-menu-btn {
                display: block;
            }

            nav ul {
                display: none;
                position: absolute;
                top: 100%;
                left: 0;
                width: 100%;
                background: rgba(255, 255, 255, 0.98);
                backdrop-filter: blur(10px);
                flex-direction: column;
                padding: 1rem;
                box-shadow: 0 10px 30px rgba(0,0,0,0.1);
                border-radius: 0 0 15px 15px;
            }

            nav ul.active {
                display: flex;
            }

            nav li {
                width: 100%;
            }

            nav li a {
                display: block;
                width: 100%;
                text-align: center;
                padding: 1rem;
                margin: 0.3rem 0;
            }

            .logo-container {
                flex-direction: column;
                gap: 0.3rem;
                align-items: flex-start;
            }

            .logo-image {
                height: 45px;
            }

            .logo-text {
                font-size: 1rem;
            }

            .hero h1 {
                font-size: 2rem;
            }

            .hero .credentials {
                font-size: 0.8rem;
            }

            .hero p {
                font-size: 1.1rem;
            }

            .about-content,
            .contact-content {
                grid-template-columns: 1fr;
            }

            h2 {
                font-size: 2rem;
            }
        }
    </style>
</head>
<body>
    <!-- Header -->
    <header>
        <nav>
            <div class="logo-container">
                <img src="msi_logo.png" alt="Dr. Mandilakhe Msingapantsi Logo" class="logo-image">
                <div class="logo-text">
                    Dr. M. Msingapantsi
                    <small>Orthopaedic Surgeon</small>
                </div>
            </div>
            <button class="mobile-menu-btn" onclick="toggleMenu()">☰</button>
            <ul id="navMenu">
                <li><a href="#home">Home</a></li>
                <li><a href="#about">About</a></li>
                <li><a href="#services">Services</a></li>
                <li><a href="#conditions">Conditions</a></li>
                <li><a href="#contact">Contact</a></li>
            </ul>
        </nav>
    </header>

    <!-- Hero Section -->
    <section class="hero" id="home">
        <div class="hero-content">
            <h1>Dr. Mandilakhe Msingapantsi</h1>
            <p class="credentials">MBChB(UKZN), Dip HivM(UKZN), FC(Ortho) SA, MMed Ortho(SMU)</p>
            <p>Specialist Orthopaedic Surgeon | Rustenburg, North West</p>
            <p style="font-size: 1rem; margin-top: -1rem;">PR No. 0886572 | MP0650064</p>
            <a href="#contact" class="cta-button">Book Appointment</a>
        </div>
    </section>

    <!-- About Section -->
    <section id="about">
        <h2>About Dr. Msingapantsi</h2>
        <div class="about-content">
            <div class="about-image">
                <span>🩺</span>
            </div>
            <div class="about-text">
                <p>Dr. Mandilakhe Msingapantsi is a highly qualified orthopaedic surgeon with impressive credentials including MBChB from the University of KwaZulu-Natal, a Diploma in HIV Management, Fellowship of the College of Orthopaedic Surgeons of South Africa (FC(Ortho) SA), and a Master of Medicine in Orthopaedics from Sefako Makgatho Health Sciences University.</p>
                <p>Based in Rustenburg, North West, South Africa, Dr. Msingapantsi provides specialized orthopaedic care at two premier facilities: Medicare Private Hospital and Life Peglerae Hospital. With extensive training and experience in musculoskeletal medicine, he is committed to providing exceptional care to patients suffering from bone, joint, and soft tissue conditions.</p>
                <p>With a focus on restoring mobility, reducing pain, and improving quality of life, Dr. Msingapantsi utilizes the latest techniques and evidence-based practices in orthopaedic surgery to achieve optimal outcomes for his patients. His patient-centered approach ensures that each individual receives personalized care tailored to their specific needs and lifestyle.</p>
            </div>
        </div>
    </section>

    <!-- Services Section -->
    <section id="services" style="background: var(--light-gray);">
        <h2>Orthopaedic Services</h2>
        <div class="services-grid">
            <div class="service-card">
                <div class="service-icon">🦴</div>
                <h3>Trauma & Fracture Care</h3>
                <p>Expert management of bone fractures, dislocations, and acute traumatic injuries with both surgical and non-surgical approaches.</p>
            </div>
            <div class="service-card">
                <div class="service-icon">🦵</div>
                <h3>Joint Replacement</h3>
                <p>Advanced hip and knee replacement surgeries to restore mobility and relieve pain from severe arthritis and joint damage.</p>
            </div>
            <div class="service-card">
                <div class="service-icon">⚽</div>
                <h3>Sports Medicine</h3>
                <p>Specialized treatment for sports-related injuries including ligament tears, tendon injuries, and arthroscopic procedures.</p>
            </div>
            <div class="service-card">
                <div class="service-icon">🔬</div>
                <h3>Arthroscopic Surgery</h3>
                <p>Minimally invasive surgical techniques for joint problems, offering faster recovery times and reduced post-operative pain.</p>
            </div>
            <div class="service-card">
                <div class="service-icon">🏥</div>
                <h3>Orthopaedic Consultations</h3>
                <p>Comprehensive evaluations, diagnostic imaging interpretation, and treatment planning for all musculoskeletal conditions.</p>
            </div>
            <div class="service-card">
                <div class="service-icon">💊</div>
                <h3>Pain Management</h3>
                <p>Conservative treatment options including injections, physiotherapy referrals, and rehabilitation programs for chronic conditions.</p>
            </div>
        </div>
    </section>

    <!-- Conditions Treated -->
    <section id="conditions">
        <h2>Conditions Treated</h2>
        <div class="conditions-grid">
            <div class="condition-item">
                <h4>Arthritis & Joint Pain</h4>
            </div>
            <div class="condition-item">
                <h4>Fractures & Dislocations</h4>
            </div>
            <div class="condition-item">
                <h4>Sports Injuries</h4>
            </div>
            <div class="condition-item">
                <h4>Ligament Tears (ACL, MCL)</h4>
            </div>
            <div class="condition-item">
                <h4>Tendon Injuries</h4>
            </div>
            <div class="condition-item">
                <h4>Rotator Cuff Problems</h4>
            </div>
            <div class="condition-item">
                <h4>Knee Meniscus Tears</h4>
            </div>
            <div class="condition-item">
                <h4>Hip Disorders</h4>
            </div>
            <div class="condition-item">
                <h4>Shoulder Conditions</h4>
            </div>
            <div class="condition-item">
                <h4>Carpal Tunnel Syndrome</h4>
            </div>
            <div class="condition-item">
                <h4>Bone Infections</h4>
            </div>
            <div class="condition-item">
                <h4>Degenerative Diseases</h4>
            </div>
        </div>
    </section>

    <!-- Qualifications -->
    <section class="qualifications">
        <h2>Qualifications & Expertise</h2>
        <div class="qual-list">
            <div class="qual-item">
                <h4>🎓 MBChB (UKZN)</h4>
                <p>Bachelor of Medicine and Bachelor of Surgery from the University of KwaZulu-Natal</p>
            </div>
            <div class="qual-item">
                <h4>💊 Dip HivM (UKZN)</h4>
                <p>Diploma in HIV Management from the University of KwaZulu-Natal</p>
            </div>
            <div class="qual-item">
                <h4>🏥 FC(Ortho) SA</h4>
                <p>Fellowship of the College of Orthopaedic Surgeons of South Africa</p>
            </div>
            <div class="qual-item">
                <h4>🔬 MMed Ortho (SMU)</h4>
                <p>Master of Medicine in Orthopaedics from Sefako Makgatho Health Sciences University</p>
            </div>
            <div class="qual-item">
                <h4>📋 Professional Registration</h4>
                <p>HPCSA Registration: PR No. 0886572 | Medical Practice Number: MP0650064</p>
            </div>
            <div class="qual-item">
                <h4>💼 Professional Experience</h4>
                <p>Extensive experience in managing complex musculoskeletal conditions and performing advanced surgical procedures at leading medical facilities in Rustenburg</p>
            </div>
        </div>
    </section>

    <!-- Contact Section -->
    <section class="contact" id="contact">
        <h2>Contact & Appointments</h2>
        <div class="contact-content">
            <div class="contact-info">
                <div class="contact-item">
                    <div class="contact-icon">🏥</div>
                    <div>
                        <h4>Medicare Private Hospital</h4>
                        <p>54 Zand Street<br>
                        Rustenburg, 0299<br>
                        North West, South Africa<br>
                        <strong>Tel:</strong> 014 523 9381</p>
                    </div>
                </div>
                <div class="contact-item">
                    <div class="contact-icon">🏥</div>
                    <div>
                        <h4>Life Peglerae Hospital</h4>
                        <p>54 Heystek Street<br>
                        Rustenburg, 0299<br>
                        North West, South Africa<br>
                        <strong>Tel:</strong> 014 010 0312</p>
                    </div>
                </div>
                <div class="contact-item">
                    <div class="contact-icon">📞</div>
                    <div>
                        <h4>Emergency Contact</h4>
                        <p><strong>Emergency:</strong> 073 095 6868</p>
                    </div>
                </div>
                <div class="contact-item">
                    <div class="contact-icon">✉️</div>
                    <div>
                        <h4>Email</h4>
                        <p>drmsingapantsi@icloud.com</p>
                    </div>
                </div>
                <div class="contact-item">
                    <div class="contact-icon">🕐</div>
                    <div>
                        <h4>Consultation Hours</h4>
                        <p>By Appointment Only<br>
                        Please call to schedule</p>
                    </div>
                </div>
            </div>
            <div class="contact-form">
                <h3 style="color: var(--primary-blue); margin-bottom: 1.5rem;">Request an Appointment</h3>
                <form id="appointmentForm" method="POST" action="send-appointment.php">
                    <div class="form-group">
                        <label for="name">Full Name *</label>
                        <input type="text" id="name" name="name" required minlength="2" maxlength="100">
                    </div>
                    <div class="form-group">
                        <label for="phone">Phone Number *</label>
                        <input type="tel" id="phone" name="phone" required pattern="[0-9\s\-\+\(\)]{10,15}" title="Please enter a valid phone number">
                    </div>
                    <div class="form-group">
                        <label for="email">Email Address *</label>
                        <input type="email" id="email" name="email" required>
                    </div>
                    <div class="form-group">
                        <label for="message">Reason for Consultation *</label>
                        <textarea id="message" name="message" required minlength="10" maxlength="1000"></textarea>
                    </div>
                    <!-- Honeypot field (hidden from users, catches bots) -->
                    <div style="position: absolute; left: -5000px;" aria-hidden="true">
                        <input type="text" name="website" tabindex="-1" autocomplete="off">
                    </div>
                    <!-- Timestamp for spam detection -->
                    <input type="hidden" name="form_timestamp" id="form_timestamp" value="">
                    <!-- CSRF Token -->
                    <input type="hidden" name="csrf_token" id="csrf_token" value="">
                    <button type="submit" class="submit-btn" id="submitBtn">Submit Request</button>
                    <div id="formMessage" style="margin-top: 1rem; padding: 1rem; border-radius: 8px; display: none;"></div>
                </form>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer>
        <p>&copy; <?php echo date('Y'); ?> Dr. Mandilakhe Msingapantsi - Orthopaedic Surgeon. All rights reserved.</p>
        <p>Practice Number: 0886572 | HPCSA Registered</p>
        <p style="margin-top: 1rem; font-size: 0.9rem;">This website is for informational purposes only and does not constitute medical advice. Please consult with Dr. Msingapantsi for proper diagnosis and treatment.</p>
    </footer>

    <script>
        // Generate CSRF token and timestamp on page load
        window.addEventListener('DOMContentLoaded', function() {
            // Generate simple CSRF token
            const csrfToken = generateToken();
            document.getElementById('csrf_token').value = csrfToken;
            sessionStorage.setItem('csrf_token', csrfToken);
            
            // Set form timestamp (used to detect too-fast submissions)
            document.getElementById('form_timestamp').value = Date.now();
        });

        function generateToken() {
            return Math.random().toString(36).substr(2) + Date.now().toString(36);
        }

        function toggleMenu() {
            const menu = document.getElementById('navMenu');
            menu.classList.toggle('active');
        }

        // Handle form submission with validation
        document.getElementById('appointmentForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const submitBtn = document.getElementById('submitBtn');
            const formMessage = document.getElementById('formMessage');
            const formTimestamp = document.getElementById('form_timestamp').value;
            const currentTime = Date.now();
            
            // Check if form was filled too quickly (less than 3 seconds - likely a bot)
            if (currentTime - formTimestamp < 3000) {
                showMessage('Please take your time filling out the form.', 'error');
                return;
            }
            
            // Disable submit button to prevent double submission
            submitBtn.disabled = true;
            submitBtn.textContent = 'Sending...';
            
            // Get form data
            const formData = new FormData(this);
            
            // Send form via AJAX
            fetch('send-appointment.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showMessage(data.message || 'Thank you! Your appointment request has been sent. We will contact you shortly.', 'success');
                    document.getElementById('appointmentForm').reset();
                    // Reset timestamp for new submission
                    document.getElementById('form_timestamp').value = Date.now();
                } else {
                    showMessage(data.message || 'There was an error sending your request. Please try again or call us directly.', 'error');
                }
            })
            .catch(error => {
                showMessage('There was an error sending your request. Please try again or call us directly.', 'error');
            })
            .finally(() => {
                submitBtn.disabled = false;
                submitBtn.textContent = 'Submit Request';
            });
        });

        function showMessage(message, type) {
            const formMessage = document.getElementById('formMessage');
            formMessage.textContent = message;
            formMessage.style.display = 'block';
            formMessage.style.background = type === 'success' ? '#d4edda' : '#f8d7da';
            formMessage.style.color = type === 'success' ? '#155724' : '#721c24';
            formMessage.style.border = type === 'success' ? '1px solid #c3e6cb' : '1px solid #f5c6cb';
            
            // Auto-hide message after 5 seconds
            setTimeout(() => {
                formMessage.style.display = 'none';
            }, 5000);
        }

        // Smooth scrolling for navigation links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    // Update active state
                    document.querySelectorAll('nav a').forEach(link => {
                        link.classList.remove('active');
                    });
                    this.classList.add('active');
                    
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                    // Close mobile menu if open
                    document.getElementById('navMenu').classList.remove('active');
                }
            });
        });

        // Update active nav on scroll
        window.addEventListener('scroll', function() {
            const header = document.querySelector('header');
            if (window.scrollY > 50) {
                header.style.boxShadow = '0 5px 30px rgba(30, 58, 138, 0.15)';
            } else {
                header.style.boxShadow = '0 2px 20px rgba(30, 58, 138, 0.1)';
            }

            // Update active menu item based on scroll position
            const sections = document.querySelectorAll('section[id]');
            const scrollPosition = window.scrollY + 150;

            sections.forEach(section => {
                const sectionTop = section.offsetTop;
                const sectionHeight = section.offsetHeight;
                const sectionId = section.getAttribute('id');

                if (scrollPosition >= sectionTop && scrollPosition < sectionTop + sectionHeight) {
                    document.querySelectorAll('nav a').forEach(link => {
                        link.classList.remove('active');
                        if (link.getAttribute('href') === `#${sectionId}`) {
                            link.classList.add('active');
                        }
                    });
                }
            });
        });
    </script>
</body>
</html>