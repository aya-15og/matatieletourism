<?php
// SEO FIX 1: Update default page title to be more keyword-rich
if(!isset($page_title)) $page_title="Matatiele Tourism: Attractions, accommodation, Events & Activities";
if(!isset($hero_folder)) $hero_folder="images/hero/";
if(!isset($hero_heading)) $hero_heading="Discover Matatiele";
if(!isset($hero_text)) $hero_text="Explore the beauty of the Eastern Cape highlands";

// SEO FIX 2 & 3: Define Meta Description and Canonical URL for the homepage
$meta_description = "Discover Matatiele, where the Drakensberg meets rolling grasslands. Explore top attractions, local accommodation, and things to do in this Eastern Cape hidden gem.";
$canonical_url = "https://matatiele.co.za/";

// Determine if this is the homepage to apply specific meta tags
$is_homepage = (basename($_SERVER['PHP_SELF']) == 'index.php' || basename($_SERVER['PHP_SELF']) == '');
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?php echo htmlspecialchars($page_title,ENT_QUOTES); ?></title>

<!-- SEO FIX 2: Added Meta Description -->
<?php if ($is_homepage): ?>
<meta name="description" content="<?php echo htmlspecialchars($meta_description, ENT_QUOTES); ?>">
<?php endif; ?>

<!-- SEO FIX 3: Added Canonical Tag -->
<?php if ($is_homepage): ?>
<link rel="canonical" href="<?php echo htmlspecialchars($canonical_url, ENT_QUOTES); ?>">
<?php endif; ?>

<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="assets/style.css">

<style>
/* Header & navigation */
header {
    position: relative;
    z-index: 1000;
}

.top-bar {
    background: var(--green);
    padding: 12px 0;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}

.header-content {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 20px;
}

.brand {
    display: flex;
    align-items: center;
    gap: 16px;
    flex-shrink: 0;
    max-width: 450px;
}

.logo {
    flex-shrink: 0;
}

.logo img {
    width: 80px;
    height: 80px;
    object-fit: cover;
    border-radius: 30%;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}

.brand-text {
    color: #fff;
    flex: 1;
    min-width: 0;
}

.brand-title {
    font-weight: 800;
    font-size: 19px;
    line-height: 1.3;
    white-space: normal;
    word-wrap: break-word;
}

.brand-tagline {
    font-size: 13px;
    opacity: 0.95;
    margin-top: 3px;
    white-space: nowrap;
}

/* Desktop Navigation */
nav {
    display: flex;
    gap: 10px;
    align-items: center;
}

nav a {
    color: #fff;
    text-decoration: none;
    background: rgba(255, 255, 255, 0.1);
    padding: 10px 20px;
    border-radius: 8px;
    font-size: 14px;
    font-weight: 500;
    white-space: nowrap;
    transition: all 0.3s ease;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    min-width: fit-content;
}

nav a:hover {
    background: var(--gold);
    color: var(--brown);
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
}

nav a.active {
    background: var(--gold);
    color: var(--brown);
}

/* Mobile Menu Button */
.mobile-menu-btn {
    display: none;
    background: rgba(255, 255, 255, 0.1);
    border: none;
    color: #fff;
    padding: 10px;
    border-radius: 8px;
    cursor: pointer;
    transition: all 0.3s ease;
}

.mobile-menu-btn:hover {
    background: rgba(255, 255, 255, 0.2);
}

.mobile-menu-btn svg {
    display: block;
    width: 28px;
    height: 28px;
}

/* Tablet & Mobile Styles */
@media (max-width: 1024px) {
    nav {
        gap: 8px;
    }
    
    nav a {
        padding: 10px 16px;
        font-size: 13px;
    }
}

@media (max-width: 900px) {
    nav {
        gap: 6px;
    }
    
    nav a {
        padding: 9px 14px;
        font-size: 12.5px;
    }
}

@media (max-width: 768px) {
    .header-content {
        flex-wrap: wrap;
    }

    .brand {
        max-width: calc(100% - 60px);
    }

    .logo img {
        width: 70px;
        height: 70px;
    }

    .brand-title {
        font-size: 17px;
    }

    .brand-tagline {
        font-size: 12px;
    }

    .mobile-menu-btn {
        display: block;
    }

    nav {
        display: none;
        width: 100%;
        flex-direction: column;
        gap: 8px;
        padding-top: 12px;
    }

    nav.active {
        display: flex;
    }

    nav a {
        width: 100%;
        text-align: center;
        padding: 12px;
        font-size: 14px;
    }
}

@media (max-width: 480px) {
    .brand {
        max-width: calc(100% - 50px);
    }

    .logo img {
        width: 60px;
        height: 60px;
    }

    .brand-title {
        font-size: 15px;
    }

    .brand-tagline {
        font-size: 11px;
    }

    .top-bar {
        padding: 10px 0;
    }
}
</style>
</head>
<body>
<header>
  <div class="top-bar">
    <div class="container">
      <div class="header-content">
        <div class="brand">
          <div class="logo">
            <!-- SEO FIX 4: Improved Logo Alt Text -->
            <img src="assets/matatiele_logo.png" alt="Discover Matatiele - Nature, Culture, Adventure Logo">
          </div>
          <div class="brand-text">
            <div class="brand-title"><?php echo htmlspecialchars($page_title,ENT_QUOTES); ?></div> 
            <div class="brand-tagline">Nature • Culture • Adventure</div>
          </div>
        </div>

        <!-- Mobile Menu Button -->
        <button class="mobile-menu-btn" id="mobileMenuBtn" aria-label="Toggle Menu">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <line x1="3" y1="6" x2="21" y2="6"></line>
            <line x1="3" y1="12" x2="21" y2="12"></line>
            <line x1="3" y1="18" x2="21" y2="18"></line>
          </svg>
        </button>

        <nav id="mainNav">
          <a href="index.php">Home</a>
          <a href="attractions.php">Attractions</a>
          <a href="stays.php">Stay</a>
          <a href="things-to-do.php">Things To Do</a>
          <a href="dining.php">Dining</a>
          <a href="business-directory.php">Business Directory</a>
        </nav>
      </div>
    </div>
  </div>

  <!-- Hero slideshow (commented out)
  <div class="hero-container">
    <?php
    $slides = glob($hero_folder."*.{jpg,jpeg,png}", GLOB_BRACE);
    if($slides && count($slides) > 0):
      foreach($slides as $index=>$img):
    ?>
      <div class="hero-slide <?php echo $index===0?'active':'';?>" style="background-image:url('<?php echo htmlspecialchars($img,ENT_QUOTES);?>');"></div>
    <?php 
      endforeach;
    else:
    ?>
      <div class="hero-slide active" style="background-image:url('assets/default-hero.jpg');"></div>
    <?php endif; ?>
    
    <div class="hero-text">
      <h1><?php echo htmlspecialchars($hero_heading,ENT_QUOTES); ?></h1>
      <p><?php echo htmlspecialchars($hero_text,ENT_QUOTES); ?></p>
      <div class="hero-buttons">
        <a class="btn btn-primary" href="#attractions">Explore Attractions</a>
        <a class="btn btn-outline" href="#stay">Find Accommodation</a>
      </div>
    </div>
  </div>
  -->
</header>

<script>
// Hero slideshow
(function() {
  const slides = document.querySelectorAll('.hero-slide');
  if(slides.length > 1) {
    let index = 0;
    setInterval(() => {
      slides[index].classList.remove('active');
      index = (index + 1) % slides.length;
      slides[index].classList.add('active');
    }, 5000);
  }
})();

// Mobile menu toggle
(function() {
  const menuBtn = document.getElementById('mobileMenuBtn');
  const nav = document.getElementById('mainNav');
  
  if (menuBtn && nav) {
    menuBtn.addEventListener('click', function() {
      nav.classList.toggle('active');
      
      // Update button icon
      const svg = menuBtn.querySelector('svg');
      if (nav.classList.contains('active')) {
        svg.innerHTML = '<line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line>';
      } else {
        svg.innerHTML = '<line x1="3" y1="6" x2="21" y2="6"></line><line x1="3" y1="12" x2="21" y2="12"></line><line x1="3" y1="18" x2="21" y2="18"></line>';
      }
    });

    // Close mobile menu when clicking nav links
    nav.querySelectorAll('a').forEach(link => {
      link.addEventListener('click', function() {
        if (window.innerWidth <= 768) {
          nav.classList.remove('active');
          const svg = menuBtn.querySelector('svg');
          svg.innerHTML = '<line x1="3" y1="6" x2="21" y2="6"></line><line x1="3" y1="12" x2="21" y2="12"></line><line x1="3" y1="18" x2="21" y2="18"></line>';
        }
      });
    });
  }

  // Highlight active page
  const currentPage = window.location.pathname.split('/').pop() || 'index.php';
  nav.querySelectorAll('a').forEach(link => {
    if (link.getAttribute('href') === currentPage) {
      link.classList.add('active');
    }
  });
})();
</script>