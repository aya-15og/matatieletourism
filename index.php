<?php
ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);
error_reporting(E_ALL);

require 'includes/config.php';

// SEO
$page_title = "Discover Matatiele - Attractions, Stays, and Local Business Directory";
$page_description = "Explore the majestic Matatiele, where the Drakensberg meets rolling grasslands. Find local attractions, plan your stay, and browse the comprehensive local business directory.";

// Helper function to fetch featured content
function fetch_featured_content($pdo, $table, $page_path) {
    $image_path_prefix = "images/{$page_path}/";
    $sql = "SELECT id, name AS title, image, description AS short_description 
            FROM {$table} 
            WHERE featured=1 
            ORDER BY id DESC 
            LIMIT 6";
    $items = $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($items as &$item) {
        if (!empty($item['image'])) {
            $filename = basename($item['image']);
            $item['image_path'] = $image_path_prefix . $filename;
        } else {
            $item['image_path'] = $image_path_prefix . 'default.jpg';
        }
    }
    unset($item);
    return $items;
}

// Fetch featured content
$featured_attractions = fetch_featured_content($pdo, 'attractions', 'attractions');
$featured_stays = fetch_featured_content($pdo, 'stays', 'stays');
$featured_activities = fetch_featured_content($pdo, 'activities', 'activities');

// Fetch sponsors
try {
    $sponsors = $pdo->query("SELECT name, logo, website FROM sponsors WHERE visible=1 ORDER BY display_order ASC LIMIT 12")->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $sponsors = [];
    error_log("Sponsors query error: " . $e->getMessage());
}

// ENHANCED AD FETCHING with date validation
try {
    $ad_top = $pdo->query("
        SELECT * FROM ads 
        WHERE position='top' 
        AND visible=1 
        AND (start_date IS NULL OR start_date <= CURDATE())
        AND (end_date IS NULL OR end_date >= CURDATE())
        ORDER BY RAND() LIMIT 1
    ")->fetch(PDO::FETCH_ASSOC);
    
    $ad_hero_overlay = $pdo->query("
        SELECT * FROM ads 
        WHERE position='hero-overlay' 
        AND visible=1 
        AND (start_date IS NULL OR start_date <= CURDATE())
        AND (end_date IS NULL OR end_date >= CURDATE())
        ORDER BY RAND() LIMIT 1
    ")->fetch(PDO::FETCH_ASSOC);
    
    $ad_sidebar = $pdo->query("
        SELECT * FROM ads 
        WHERE position IN ('sidebar', 'sidebar-tall') 
        AND visible=1 
        AND (start_date IS NULL OR start_date <= CURDATE())
        AND (end_date IS NULL OR end_date >= CURDATE())
        ORDER BY RAND() LIMIT 3
    ")->fetchAll(PDO::FETCH_ASSOC);
    
    $ad_inline = $pdo->query("
        SELECT * FROM ads 
        WHERE position='inline' 
        AND visible=1 
        AND (start_date IS NULL OR start_date <= CURDATE())
        AND (end_date IS NULL OR end_date >= CURDATE())
        ORDER BY RAND() LIMIT 2
    ")->fetchAll(PDO::FETCH_ASSOC);
    
    $ad_bottom = $pdo->query("
        SELECT * FROM ads 
        WHERE position IN ('bottom', 'bottom-wide') 
        AND visible=1 
        AND (start_date IS NULL OR start_date <= CURDATE())
        AND (end_date IS NULL OR end_date >= CURDATE())
        ORDER BY RAND() LIMIT 1
    ")->fetch(PDO::FETCH_ASSOC);
    
    // Update impressions for all fetched ads
    $all_ads = array_filter([$ad_top, $ad_hero_overlay, $ad_bottom]);
    foreach ($all_ads as $ad) {
        if ($ad) {
            $pdo->prepare("UPDATE ads SET impressions = impressions + 1 WHERE id = ?")->execute([$ad['id']]);
        }
    }
    foreach ($ad_sidebar as $ad) {
        $pdo->prepare("UPDATE ads SET impressions = impressions + 1 WHERE id = ?")->execute([$ad['id']]);
    }
    foreach ($ad_inline as $ad) {
        $pdo->prepare("UPDATE ads SET impressions = impressions + 1 WHERE id = ?")->execute([$ad['id']]);
    }
    
} catch (PDOException $e) {
    $ad_top = $ad_hero_overlay = $ad_bottom = null;
    $ad_sidebar = $ad_inline = [];
    error_log("Ads query error: " . $e->getMessage());
}

// Glance video
try {
    $glance_video_result = $pdo->query("SELECT filename FROM videos WHERE section='glance' AND visible=1 LIMIT 1")->fetch(PDO::FETCH_ASSOC);
    $glance_video = $glance_video_result['filename'] ?? null;
} catch (PDOException $e) {
    $glance_video = null;
}

// Hero images
$hero_images = $pdo->query("SELECT * FROM home_images WHERE section='hero' ORDER BY order_no ASC")->fetchAll(PDO::FETCH_ASSOC);

// Gallery
$gallery_images = $pdo->query("SELECT filename FROM gallery ORDER BY id DESC LIMIT 12")->fetchAll(PDO::FETCH_COLUMN);

// Hero text
$hero_heading = "Matatiele — where the Drakensberg meets rolling grasslands";
$hero_text = "Explore highland vistas, hidden waterfalls, sandstone heritage buildings and warm local hospitality. Trails, birding, local festivals and farm-to-table food await.";

// Schema Markup for LocalBusiness
$schema_markup = '
<script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@type": "LocalBusiness",
  "name": "Discover Matatiele - Tourism & Business Directory",
  "image": "https://matatiele.co.za/images/logo.png",
  "url": "https://matatiele.co.za/",
  "telephone": "+27 39 737 3135",
  "address": {
    "@type": "PostalAddress",
    "streetAddress": "102 Main Street",
    "addressLocality": "Matatiele",
    "addressRegion": "Eastern Cape",
    "postalCode": "4730",
    "addressCountry": "ZA"
  },
  "description": "' . addslashes($page_description) . '",
  "sameAs": [
    "https://www.facebook.com/DiscoverMatatiele",
    "https://www.instagram.com/discovermatatiele"
  ],
  "contactPoint": {
    "@type": "ContactPoint",
    "contactType": "customer service",
    "email": "marketing@matatiele.co.za"
  }
}
</script>';

// Note: To ensure the schema is included in the <head>, we pass it to header.php
// If your header.php doesn't support a $extra_head variable, you should add it there.
$extra_head = $schema_markup;

include 'header.php';
?>

<!-- HERO SECTION with optional overlay ad -->
<section class="hero-split">
  <div class="hero-content">
    <div class="hero-badge">Welcome to Matatiele</div>
    <h1><?= htmlspecialchars($hero_heading); ?></h1>
    <p class="hero-description"><?= htmlspecialchars($hero_text); ?></p>
    <div class="hero-actions">
      <a href="#attractions" class="btn btn-primary">Explore Attractions</a>
      <a href="#stay" class="btn btn-secondary">Plan Your Stay</a>
    </div>
  </div>

  <div class="hero-image">
    <div class="image-wrapper">
      <?php if (!empty($hero_images)): ?>
        <img src="images/home/hero/<?= htmlspecialchars($hero_images[0]['filename']); ?>" 
             alt="<?= htmlspecialchars($hero_images[0]['name'] ?? 'Hero Image'); ?>" class="hero-img">
      <?php else: ?>
        <img src="images/home/hero/matatiele1.jpg" alt="Matatiele Landscape" class="hero-img">
      <?php endif; ?>
      <div class="image-overlay"></div>
      
      <?php if ($ad_hero_overlay): ?>
      <div class="hero-ad-overlay">
        <span class="ad-label-overlay">Sponsored</span>
        <?php if (!empty($ad_hero_overlay['link'])): ?>
          <a href="<?= htmlspecialchars($ad_hero_overlay['link']) ?>" 
             target="_blank" 
             rel="noopener"
             onclick="trackAdClick(<?= $ad_hero_overlay['id'] ?>); return true;">
            <img src="images/ads/<?= htmlspecialchars(basename($ad_hero_overlay['image'])) ?>" 
                 alt="<?= htmlspecialchars($ad_hero_overlay['title'] ?? 'Advertisement') ?>"
                 loading="lazy">
          </a>
        <?php else: ?>
          <img src="images/ads/<?= htmlspecialchars(basename($ad_hero_overlay['image'])) ?>" 
               alt="<?= htmlspecialchars($ad_hero_overlay['title'] ?? 'Advertisement') ?>"
               loading="lazy">
        <?php endif; ?>
      </div>
      <?php endif; ?>
    </div>
  </div>
</section>

<!-- TOP BANNER AD -->
<?php if ($ad_top): ?>
<section class="ad-banner-top">
  <div class="container">
    <div class="ad-container">
      <span class="ad-label">Advertisement</span>
      <?php if (!empty($ad_top['link'])): ?>
        <a href="<?= htmlspecialchars($ad_top['link']) ?>" 
           target="_blank" 
           rel="noopener"
           onclick="trackAdClick(<?= $ad_top['id'] ?>); return true;">
          <img src="images/ads/<?= htmlspecialchars(basename($ad_top['image'])) ?>" 
               alt="<?= htmlspecialchars($ad_top['title'] ?? 'Advertisement') ?>"
               loading="lazy">
        </a>
      <?php else: ?>
        <img src="images/ads/<?= htmlspecialchars(basename($ad_top['image'])) ?>" 
             alt="<?= htmlspecialchars($ad_top['title'] ?? 'Advertisement') ?>"
             loading="lazy">
      <?php endif; ?>
    </div>
  </div>
</section>
<?php endif; ?>

<main class="container main-with-sidebar">

<div class="main-content">

<!-- ABOUT SECTION -->
<section id="about-glance" class="about-glance-section">
  <div class="about-content">
    <h2>About Matatiele</h2>
    <p class="lead">Nestled in the shadows of the majestic Drakensberg escarpment, Matatiele is a hidden gem where nature and culture converge.</p>
    <p>The town's name comes from local Sotho and Phuthi words that reference the area's distinctive marshes and water features.</p>
    <p>From the dramatic mountain passes to the warm hospitality of its people, Matatiele invites you to discover a slower pace of life surrounded by breathtaking scenery.</p>
  </div>
  
  <div class="glance-cards">
    <h3>At a Glance</h3>
    <div class="glance-grid">
      <div class="glance-card">
        <div class="glance-icon">📍</div>
        <div class="glance-info">
          <div class="glance-label">Location</div>
          <div class="glance-value">Eastern Cape</div>
          <div class="glance-detail">Alfred Nzo District</div>
        </div>
      </div>
      <div class="glance-card">
        <div class="glance-icon">👥</div>
        <div class="glance-info">
          <div class="glance-label">Population</div>
          <div class="glance-value">~12,000</div>
          <div class="glance-detail">2011 Census</div>
        </div>
      </div>
      <div class="glance-card">
        <div class="glance-icon">🏔️</div>
        <div class="glance-info">
          <div class="glance-label">Elevation</div>
          <div class="glance-value">1,700m</div>
          <div class="glance-detail">Highland Region</div>
        </div>
      </div>
      <div class="glance-card">
        <div class="glance-icon">💼</div>
        <div class="glance-info">
          <div class="glance-label">Economy</div>
          <div class="glance-value">Agriculture</div>
          <div class="glance-detail">& Tourism</div>
        </div>
      </div>
    </div>
    
    <div class="glance-video-wrapper">
      <?php if ($glance_video): ?>
        <video controls poster="images/video-poster.jpg" class="glance-video">
          <source src="videos/<?= htmlspecialchars($glance_video); ?>" type="video/mp4">
          Your browser does not support the video tag.
        </video>
      <?php else: ?>
        <video controls poster="images/video-poster.jpg" class="glance-video">
          <source src="videos/matatiele_intro.mp4" type="video/mp4">
          Your browser does not support the video tag.
        </video>
      <?php endif; ?>
    </div>
  </div>
</section>

<!-- LOCAL BUSINESS DIRECTORY TEASER -->
<section class="business-directory-teaser">
  <div class="container">
    <div class="teaser-content">
      <div class="teaser-text">
        <span class="teaser-badge">Explore Local</span>
        <h2>Matatiele Business Directory</h2>
        <p>Discover local shops, restaurants, services, and businesses that make Matatiele unique. Support local and find everything you need during your visit.</p>
        <div class="business-categories">
          <span class="category-tag">🍽️ Restaurants</span>
          <span class="category-tag">🛍️ Shops</span>
          <span class="category-tag">⚙️ Services</span>
          <span class="category-tag">🏥 Healthcare</span>
          <span class="category-tag">⛽ Fuel Stations</span>
        </div>
        <a href="business-directory.php" class="btn btn-primary">Browse Directory</a>
      </div>
      <div class="teaser-visual">
        <div class="business-preview-grid">
          <div class="business-preview-card">
            <div class="business-icon">🏪</div>
            <div class="business-count">50+</div>
            <div class="business-label">Local Shops</div>
          </div>
          <div class="business-preview-card">
            <div class="business-icon">🍴</div>
            <div class="business-count">25+</div>
            <div class="business-label">Restaurants</div>
          </div>
          <div class="business-preview-card">
            <div class="business-icon">🏨</div>
            <div class="business-count">15+</div>
            <div class="business-label">Accommodation</div>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- GALLERY TEASER -->
<section class="gallery-teaser">
  <div class="container">
    <h2>Matatiele Photo Gallery</h2>
    <p class="section-description">A visual journey through the landscapes, people, and culture of Matatiele.</p>
    
    <div class="gallery-grid">
      <?php if (!empty($gallery_images)): ?>
        <?php foreach (array_slice($gallery_images, 0, 6) as $image): ?>
          <div class="gallery-item">
            <img src="images/gallery/<?= htmlspecialchars($image); ?>" 
                 alt="Photo of Matatiele" 
                 loading="lazy"
                 class="clickable-img">
          </div>
        <?php endforeach; ?>
      <?php else: ?>
        <p>No gallery images available at the moment.</p>
      <?php endif; ?>
    </div>
    
    <div class="section-footer">
      <a href="gallery.php" class="btn btn-primary">View Full Gallery</a>
    </div>
  </div>
</section>

<!-- SPONSORS/PARTNERS SECTION -->
<?php if (!empty($sponsors)): ?>
<section class="sponsors-section">
  <div class="container">
    <h2>Our Partners & Sponsors</h2>
    <p class="section-subtitle">Supporting tourism and development in Matatiele</p>
    <div class="sponsors-grid">
      <?php foreach($sponsors as $sponsor): ?>
        <div class="sponsor-card">
          <?php if (!empty($sponsor['website'])): ?>
            <a href="<?= htmlspecialchars($sponsor['website']) ?>" target="_blank" rel="noopener" title="<?= htmlspecialchars($sponsor['name']) ?>">
              <img src="images/sponsors/<?= htmlspecialchars($sponsor['logo']) ?>" alt="<?= htmlspecialchars($sponsor['name']) ?>">
            </a>
          <?php else: ?>
            <img src="images/sponsors/<?= htmlspecialchars($sponsor['logo']) ?>" alt="<?= htmlspecialchars($sponsor['name']) ?>">
          <?php endif; ?>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>
<?php endif; ?>

<!-- BOTTOM BANNER AD -->
<?php if ($ad_bottom): ?>
<section class="ad-banner-bottom">
  <div class="container">
    <div class="ad-container">
      <span class="ad-label">Advertisement</span>
      <?php if (!empty($ad_bottom['link'])): ?>
        <a href="<?= htmlspecialchars($ad_bottom['link']) ?>" 
           target="_blank" 
           rel="noopener"
           onclick="trackAdClick(<?= $ad_bottom['id'] ?>); return true;">
          <img src="images/ads/<?= htmlspecialchars(basename($ad_bottom['image'])) ?>" 
               alt="<?= htmlspecialchars($ad_bottom['title'] ?? 'Advertisement') ?>"
               loading="lazy">
        </a>
      <?php else: ?>
        <img src="images/ads/<?= htmlspecialchars(basename($ad_bottom['image'])) ?>" 
             alt="<?= htmlspecialchars($ad_bottom['title'] ?? 'Advertisement') ?>"
             loading="lazy">
      <?php endif; ?>
    </div>
  </div>
</section>
<?php endif; ?>

<!-- LIGHTBOX -->
<div class="lightbox-overlay" id="lightbox">
  <div class="lightbox-content">
    <span class="lightbox-close" id="lightboxClose">&times;</span>
    <img id="lightboxImg" src="" alt="Full view">
  </div>
</div>

<?php include 'footer.php'; ?>

<style>
/* ============================================
   FIXED RESPONSIVE CSS FOR MATATIELE WEBSITE
   All transform scaling removed, proper fluid typography added
   ============================================ */

/* ========== HERO SECTION ========== */
.hero-split {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 3rem;
  align-items: center;
  padding: 3rem 1rem;
  max-width: 1400px;
  margin: 0 auto;
}

.hero-content {
  padding: 2rem;
}

.hero-badge {
  display: inline-block;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
  padding: 0.5rem 1.2rem;
  border-radius: 20px;
  font-size: clamp(0.75rem, 1.5vw, 0.9rem);
  font-weight: 600;
  margin-bottom: 1.5rem;
}

.hero-content h1 {
  font-size: clamp(1.75rem, 5vw, 2.8rem);
  line-height: 1.2;
  margin-bottom: 1rem;
  color: #333;
}

.hero-description {
  font-size: clamp(0.95rem, 2vw, 1.15rem);
  color: #666;
  line-height: 1.7;
  margin-bottom: 2rem;
}

.hero-actions {
  display: flex;
  gap: 1rem;
  flex-wrap: wrap;
}

.btn {
  padding: 0.9rem 1.8rem;
  border-radius: 8px;
  text-decoration: none;
  font-weight: 600;
  transition: all 0.3s;
  display: inline-block;
}

.btn-primary {
  background: #667eea;
  color: white;
}

.btn-primary:hover {
  background: #5568d3;
  transform: translateY(-2px);
}

.btn-secondary {
  background: white;
  color: #667eea;
  border: 2px solid #667eea;
}

.btn-secondary:hover {
  background: #f8f9ff;
  transform: translateY(-2px);
}

.hero-image {
  position: relative;
  border-radius: 24px;
  overflow: hidden;
  box-shadow: 0 20px 40px rgba(0,0,0,0.1);
  min-height: 400px;
}

.image-wrapper {
  height: 100%;
  width: 100%;
}

.hero-img {
  width: 100%;
  height: 100%;
  object-fit: cover;
  display: block;
}

.image-overlay {
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background: linear-gradient(to bottom, transparent, rgba(0,0,0,0.2));
}

.hero-ad-overlay {
  position: absolute;
  bottom: 20px;
  right: 20px;
  background: rgba(255,255,255,0.9);
  backdrop-filter: blur(10px);
  padding: 10px;
  border-radius: 12px;
  box-shadow: 0 8px 24px rgba(0,0,0,0.15);
  max-width: 250px;
  z-index: 10;
}

.ad-label-overlay {
  display: block;
  font-size: 0.65rem;
  font-weight: 700;
  text-transform: uppercase;
  color: #666;
  margin-bottom: 5px;
}

.hero-ad-overlay img {
  width: 100%;
  border-radius: 6px;
  display: block;
}

/* ========== ABOUT SECTION ========== */
.about-glance-section {
  display: grid;
  grid-template-columns: 1.2fr 0.8fr;
  gap: 4rem;
  padding: 4rem 0;
  align-items: start;
}

.about-content h2 {
  font-size: clamp(1.8rem, 4vw, 2.5rem);
  margin-bottom: 1.5rem;
  color: #333;
}

.lead {
  font-size: clamp(1.1rem, 2vw, 1.25rem);
  color: #444;
  font-weight: 500;
  margin-bottom: 1.5rem;
}

.about-content p {
  color: #666;
  line-height: 1.8;
  margin-bottom: 1.2rem;
}

.glance-cards {
  background: #f8f9fa;
  padding: 2rem;
  border-radius: 24px;
}

.glance-cards h3 {
  font-size: clamp(1.2rem, 2.5vw, 1.5rem);
  margin-bottom: 1rem;
  color: #333;
}

.glance-grid {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 1rem;
  margin-bottom: 1.5rem;
}

.glance-card {
  background: white;
  padding: 1.2rem;
  border-radius: 12px;
  box-shadow: 0 2px 8px rgba(0,0,0,0.08);
  display: flex;
  align-items: center;
  gap: 1rem;
  transition: all 0.3s;
}

.glance-card:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(0,0,0,0.12);
}

.glance-icon {
  font-size: clamp(1.8rem, 3vw, 2.5rem);
}

.glance-info {
  flex: 1;
}

.glance-label {
  font-size: clamp(0.75rem, 1.2vw, 0.85rem);
  color: #999;
  text-transform: uppercase;
  letter-spacing: 0.5px;
  margin-bottom: 0.2rem;
}

.glance-value {
  font-size: clamp(1.1rem, 2vw, 1.3rem);
  font-weight: 700;
  color: #333;
  margin-bottom: 0.1rem;
}

.glance-detail {
  font-size: clamp(0.75rem, 1.2vw, 0.85rem);
  color: #666;
}

/* ========== VIDEO SECTION (FIXED) ========== */
.glance-video-wrapper {
  margin-top: 24px;
  display: flex;
  justify-content: flex-end;
  width: 100%;
  max-width: 100%;
}

.glance-video {
  width: 100%;
  max-width: min(350px, 100%);
  height: auto;
  aspect-ratio: 16 / 9;
  border-radius: 12px;
  box-shadow: 0 4px 16px rgba(0,0,0,0.15);
  display: block;
  object-fit: cover;
}

/* ========== BUSINESS DIRECTORY TEASER (FIXED) ========== */
.business-directory-teaser {
  background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
  padding: 4rem 1rem;
  color: white;
  margin-top: 3rem;
  border-radius: 24px;
}

.teaser-content {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 3rem;
  align-items: center;
  max-width: 1400px;
  margin: 0 auto;
}

.teaser-badge {
  display: inline-block;
  background: rgba(255,255,255,0.2);
  padding: 0.4rem 1rem;
  border-radius: 20px;
  font-size: clamp(0.75rem, 1.5vw, 0.85rem);
  font-weight: 600;
  margin-bottom: 1rem;
}

.teaser-text h2 {
  font-size: clamp(1.8rem, 4vw, 2.5rem);
  margin-bottom: 1rem;
}

.teaser-text p {
  font-size: clamp(0.95rem, 2vw, 1.1rem);
  margin-bottom: 1.5rem;
  opacity: 0.95;
}

.business-categories {
  display: flex;
  flex-wrap: wrap;
  gap: 0.8rem;
  margin-bottom: 2rem;
}

.category-tag {
  background: rgba(255,255,255,0.2);
  padding: 0.5rem 1rem;
  border-radius: 20px;
  font-size: clamp(0.8rem, 1.5vw, 0.9rem);
  font-weight: 500;
}

.business-preview-grid {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 1rem;
}

.business-preview-card {
  background: rgba(255,255,255,0.15);
  backdrop-filter: blur(10px);
  padding: 1.5rem;
  border-radius: 16px;
  text-align: center;
  transition: all 0.3s;
}

.business-preview-card:hover {
  transform: translateY(-5px);
  background: rgba(255,255,255,0.25);
}

.business-icon {
  font-size: clamp(2rem, 4vw, 3rem);
  margin-bottom: 0.5rem;
}

.business-count {
  font-size: clamp(1.5rem, 3vw, 2rem);
  font-weight: 700;
  margin-bottom: 0.3rem;
}

.business-label {
  font-size: clamp(0.8rem, 1.5vw, 0.9rem);
  opacity: 0.9;
}

/* ========== GALLERY SECTION ========== */
.gallery-teaser {
  padding: 4rem 1rem;
  background: #f8f9fa;
  text-align: center;
  border-radius: 24px;
  margin-top: 3rem;
}

.gallery-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
  gap: 1rem;
  margin-top: 2rem;
}

.gallery-item {
  overflow: hidden;
  border-radius: 8px;
  box-shadow: 0 2px 8px rgba(0,0,0,0.1);
  aspect-ratio: 1 / 1;
}

.gallery-item img {
  width: 100%;
  height: 100%;
  object-fit: cover;
  transition: transform 0.3s;
  cursor: pointer;
}

.gallery-item img:hover {
  transform: scale(1.05);
}

/* ========== SPONSORS SECTION ========== */
.sponsors-section {
  background: linear-gradient(180deg, #f8f9fa 0%, #ffffff 100%);
  padding: 4rem 1rem;
  margin-top: 3rem;
}

.sponsors-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
  gap: 2rem;
  max-width: 1200px;
  margin: 0 auto;
}

.sponsor-card {
  background: white;
  padding: 1.5rem;
  border-radius: 12px;
  box-shadow: 0 2px 8px rgba(0,0,0,0.08);
  display: flex;
  align-items: center;
  justify-content: center;
  transition: all 0.3s ease;
  min-height: 120px;
}

.sponsor-card img {
  max-width: 100%;
  max-height: 80px;
  width: auto;
  height: auto;
  object-fit: contain;
  filter: grayscale(100%);
  opacity: 0.7;
  transition: all 0.3s ease;
}

.sponsor-card:hover img {
  filter: grayscale(0%);
  opacity: 1;
}

/* ========== LIGHTBOX ========== */
.lightbox-overlay {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background: rgba(0, 0, 0, 0.9);
  display: none;
  justify-content: center;
  align-items: center;
  z-index: 1000;
}

.lightbox-overlay.active {
  display: flex;
}

.lightbox-content {
  position: relative;
  max-width: 90%;
  max-height: 90%;
}

.lightbox-content img {
  display: block;
  width: auto;
  max-width: 100%;
  max-height: 100vh;
  border-radius: 8px;
}

.lightbox-close {
  position: absolute;
  top: 20px;
  right: 30px;
  color: #fff;
  font-size: 40px;
  font-weight: bold;
  transition: 0.3s;
  cursor: pointer;
}

/* ========== RESPONSIVE ========== */
@media (max-width: 768px) {
  .hero-split, .about-glance-section, .teaser-content {
    grid-template-columns: 1fr;
    gap: 2rem;
  }
  
  .hero-image {
    min-height: 300px;
  }
  
  .hero-ad-overlay {
    position: relative;
    bottom: auto;
    right: auto;
    max-width: 100%;
    margin-top: 1rem;
  }
  
  .glance-video-wrapper {
    justify-content: center;
  }
}
</style>

<script>
// Lightbox functionality
document.addEventListener("DOMContentLoaded", function() {
  const overlay = document.getElementById("lightbox");
  const img = document.getElementById("lightboxImg");
  const close = document.getElementById("lightboxClose");

  document.querySelectorAll(".clickable-img, .hero-img").forEach(i => {
    i.addEventListener("click", () => {
      img.src = i.src; 
      overlay.classList.add("active");
    });
  });

  [overlay, close].forEach(el => el.addEventListener("click", e => {
    if (e.target === overlay || e.target === close) overlay.classList.remove("active");
  }));

  document.addEventListener("keydown", e => { 
    if (e.key === "Escape") overlay.classList.remove("active"); 
  });
});

// Track ad clicks
function trackAdClick(adId) {
  if (!adId) return;
  
  fetch('track_ad_click.php', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json'
    },
    body: JSON.stringify({ ad_id: adId })
  }).catch(err => console.error('Ad tracking error:', err));
}
</script>