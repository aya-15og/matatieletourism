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

<!-- FEATURED ATTRACTIONS -->
<section id="attractions" class="featured-section">
  <h2>Featured Attractions</h2>
  <p class="section-description">From the majestic Drakensberg to historical sites, Matatiele is rich with places to explore.</p>
  
  <div class="featured-grid">
    <?php if (!empty($featured_attractions)): ?>
      <?php foreach ($featured_attractions as $attraction): ?>
        <article class="card featured-card">
          <img src="<?= htmlspecialchars($attraction['image_path']); ?>" 
               alt="<?= htmlspecialchars($attraction['title']); ?>" 
               class="card-img-top" 
               loading="lazy">
          <div class="card-body">
            <h3 class="card-title"><?= htmlspecialchars($attraction['title']); ?></h3>
            <p class="card-text"><?= htmlspecialchars($attraction['short_description']); ?></p>
          </div>
        </article>
      <?php endforeach; ?>
    <?php else: ?>
      <p>No featured attractions available at the moment. Check back soon!</p>
    <?php endif; ?>
  </div>
  
  <div class="section-footer">
    <a href="attractions.php" class="btn btn-primary">View All Attractions</a>
  </div>
</section>

<!-- INLINE AD 1 - Between Attractions and Stays -->
<?php if (!empty($ad_inline[0])): ?>
<section class="ad-inline">
  <div class="ad-inline-container">
    <span class="ad-label">Sponsored</span>
    <?php if (!empty($ad_inline[0]['link'])): ?>
      <a href="<?= htmlspecialchars($ad_inline[0]['link']) ?>" 
         target="_blank" 
         rel="noopener"
         onclick="trackAdClick(<?= $ad_inline[0]['id'] ?>); return true;">
        <img src="images/ads/<?= htmlspecialchars(basename($ad_inline[0]['image'])) ?>" 
             alt="<?= htmlspecialchars($ad_inline[0]['title'] ?? 'Advertisement') ?>"
             loading="lazy">
      </a>
    <?php else: ?>
      <img src="images/ads/<?= htmlspecialchars(basename($ad_inline[0]['image'])) ?>" 
           alt="<?= htmlspecialchars($ad_inline[0]['title'] ?? 'Advertisement') ?>"
           loading="lazy">
    <?php endif; ?>
  </div>
</section>
<?php endif; ?>

<!-- FEATURED STAYS -->
<section id="stay" class="featured-section">
  <h2>Where to Stay</h2>
  <p class="section-description">Find the perfect accommodation, from cozy guesthouses to mountain lodges.</p>
  
  <div class="featured-grid">
    <?php if (!empty($featured_stays)): ?>
      <?php foreach ($featured_stays as $stay): ?>
        <article class="card featured-card">
          <img src="<?= htmlspecialchars($stay['image_path']); ?>" 
               alt="<?= htmlspecialchars($stay['title']); ?>" 
               class="card-img-top" 
               loading="lazy">
          <div class="card-body">
            <h3 class="card-title"><?= htmlspecialchars($stay['title']); ?></h3>
            <p class="card-text"><?= htmlspecialchars($stay['short_description']); ?></p>
          </div>
        </article>
      <?php endforeach; ?>
    <?php else: ?>
      <p>No featured stays available at the moment. Check back soon!</p>
    <?php endif; ?>
  </div>
  
  <div class="section-footer">
    <a href="stays.php" class="btn btn-primary">View All Stays</a>
  </div>
</section>

<!-- INLINE AD 2 - Between Stays and Activities -->
<?php if (!empty($ad_inline[1])): ?>
<section class="ad-inline">
  <div class="ad-inline-container">
    <span class="ad-label">Sponsored</span>
    <?php if (!empty($ad_inline[1]['link'])): ?>
      <a href="<?= htmlspecialchars($ad_inline[1]['link']) ?>" 
         target="_blank" 
         rel="noopener"
         onclick="trackAdClick(<?= $ad_inline[1]['id'] ?>); return true;">
        <img src="images/ads/<?= htmlspecialchars(basename($ad_inline[1]['image'])) ?>" 
             alt="<?= htmlspecialchars($ad_inline[1]['title'] ?? 'Advertisement') ?>"
             loading="lazy">
      </a>
    <?php else: ?>
      <img src="images/ads/<?= htmlspecialchars(basename($ad_inline[1]['image'])) ?>" 
           alt="<?= htmlspecialchars($ad_inline[1]['title'] ?? 'Advertisement') ?>"
           loading="lazy">
    <?php endif; ?>
  </div>
</section>
<?php endif; ?>

<!-- FEATURED ACTIVITIES -->
<section id="activities" class="featured-section">
  <h2>Things to Do</h2>
  <p class="section-description">Hiking, birding, cultural tours, and more to fill your itinerary.</p>
  
  <div class="featured-grid">
    <?php if (!empty($featured_activities)): ?>
      <?php foreach ($featured_activities as $activity): ?>
        <article class="card featured-card">
          <img src="<?= htmlspecialchars($activity['image_path']); ?>" 
               alt="<?= htmlspecialchars($activity['title']); ?>" 
               class="card-img-top" 
               loading="lazy">
          <div class="card-body">
            <h3 class="card-title"><?= htmlspecialchars($activity['title']); ?></h3>
            <p class="card-text"><?= htmlspecialchars($activity['short_description']); ?></p>
          </div>
        </article>
      <?php endforeach; ?>
    <?php else: ?>
      <p>No featured activities available at the moment. Check back soon!</p>
    <?php endif; ?>
  </div>
  
  <div class="section-footer">
    <a href="activities.php" class="btn btn-primary">View All Activities</a>
  </div>
</section>

</div><!-- /.main-content -->

<?php
// Fetch upcoming visible events with images for slideshow
try {
    $upcoming_events_slideshow = $pdo->query("
        SELECT id, name, description, start_date, end_date, location, image, link 
        FROM events 
        WHERE start_date >= CURDATE() 
        AND visible = 1 
        AND image IS NOT NULL 
        AND image != ''
        ORDER BY display_order ASC, start_date ASC 
        LIMIT 10
    ")->fetchAll(PDO::FETCH_ASSOC);
    
    $upcoming_events_list = $pdo->query("
        SELECT name, start_date, end_date, location 
        FROM events 
        WHERE start_date >= CURDATE() 
        AND visible = 1
        ORDER BY start_date ASC 
        LIMIT 8
    ")->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    $upcoming_events_slideshow = [];
    $upcoming_events_list = [];
}
?>

<aside class="sidebar">
  <!-- Quick Links Widget (Static - Always visible) -->
  <div class="widget sidebar-widget">
    <h3>Quick Links</h3>
    <ul class="quick-links">
      <li><a href="business-directory.php">Local Business Directory</a></li>
      <li><a href="contact.php">Contact Us</a></li>
      <li><a href="about.php">About Matatiele</a></li>
      <li><a href="gallery.php">Photo Gallery</a></li>
    </ul>
  </div>
  
  <!-- Sidebar Ads Slideshow Widget -->
  <?php if (!empty($ad_sidebar)): ?>
    <div class="widget sidebar-widget ads-slideshow-widget">
      <span class="ad-label">Sponsored</span>
      <div class="ads-slideshow">
        <?php foreach ($ad_sidebar as $index => $ad): ?>
          <div class="ad-slide <?= $index === 0 ? 'active' : '' ?>" data-slide="<?= $index ?>">
            <?php if (!empty($ad['link'])): ?>
              <a href="<?= htmlspecialchars($ad['link']) ?>" 
                 target="_blank" 
                 rel="noopener"
                 onclick="trackAdClick(<?= $ad['id'] ?>); return true;">
                <img src="images/ads/<?= htmlspecialchars(basename($ad['image'])) ?>" 
                     alt="<?= htmlspecialchars($ad['title'] ?? 'Advertisement') ?>"
                     loading="lazy">
              </a>
            <?php else: ?>
              <img src="images/ads/<?= htmlspecialchars(basename($ad['image'])) ?>" 
                   alt="<?= htmlspecialchars($ad['title'] ?? 'Advertisement') ?>"
                   loading="lazy">
            <?php endif; ?>
          </div>
        <?php endforeach; ?>
        
        <?php if (count($ad_sidebar) > 1): ?>
          <div class="slideshow-controls ads-controls">
            <button class="slide-btn" onclick="changeAdSlide(-1)" aria-label="Previous ad">
              <svg width="20" height="20" fill="currentColor" viewBox="0 0 16 16">
                <path fill-rule="evenodd" d="M11.354 1.646a.5.5 0 0 1 0 .708L5.707 8l5.647 5.646a.5.5 0 0 1-.708.708l-6-6a.5.5 0 0 1 0-.708l6-6a.5.5 0 0 1 .708 0z"/>
              </svg>
            </button>
            <div class="slide-dots">
              <?php for($i = 0; $i < count($ad_sidebar); $i++): ?>
                <span class="dot <?= $i === 0 ? 'active' : '' ?>" onclick="goToAdSlide(<?= $i ?>)"></span>
              <?php endfor; ?>
            </div>
            <button class="slide-btn" onclick="changeAdSlide(1)" aria-label="Next ad">
              <svg width="20" height="20" fill="currentColor" viewBox="0 0 16 16">
                <path fill-rule="evenodd" d="M4.646 1.646a.5.5 0 0 1 .708 0l6 6a.5.5 0 0 1 0 .708l-6 6a.5.5 0 0 1-.708-.708L10.293 8 4.646 2.354a.5.5 0 0 1 0-.708z"/>
              </svg>
            </button>
          </div>
        <?php endif; ?>
      </div>
    </div>
  <?php endif; ?>
  
  <!-- Events Slideshow Widget (with images) -->
  <?php if (!empty($upcoming_events_slideshow)): ?>
    <div class="widget sidebar-widget events-slideshow-widget">
      <h3>Featured Events</h3>
      <div class="events-slideshow">
        <?php foreach($upcoming_events_slideshow as $index => $event): ?>
          <div class="event-slide <?= $index === 0 ? 'active' : '' ?>" data-slide="<?= $index ?>">
            <?php if (!empty($event['link'])): ?>
              <a href="<?= htmlspecialchars($event['link']) ?>" target="_blank" rel="noopener">
                <img src="images/events/<?= htmlspecialchars(basename($event['image'])) ?>" 
                     alt="<?= htmlspecialchars($event['name']) ?>"
                     loading="lazy">
              </a>
            <?php else: ?>
              <img src="images/events/<?= htmlspecialchars(basename($event['image'])) ?>" 
                   alt="<?= htmlspecialchars($event['name']) ?>"
                   loading="lazy">
            <?php endif; ?>
            <div class="event-info">
              <h4><?= htmlspecialchars($event['name']) ?></h4>
              <p class="event-date">
                <svg width="14" height="14" fill="currentColor" viewBox="0 0 16 16" style="vertical-align: -2px;">
                  <path d="M3.5 0a.5.5 0 0 1 .5.5V1h8V.5a.5.5 0 0 1 1 0V1h1a2 2 0 0 1 2 2v11a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2V3a2 2 0 0 1 2-2h1V.5a.5.5 0 0 1 .5-.5zM1 4v10a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1V4H1z"/>
                </svg>
                <?= date('M j, Y', strtotime($event['start_date'])) ?>
                <?php if ($event['end_date'] && $event['end_date'] !== $event['start_date']): ?>
                  - <?= date('M j', strtotime($event['end_date'])) ?>
                <?php endif; ?>
              </p>
              <?php if (!empty($event['location'])): ?>
                <p class="event-location">
                  <svg width="14" height="14" fill="currentColor" viewBox="0 0 16 16" style="vertical-align: -2px;">
                    <path d="M8 16s6-5.686 6-10A6 6 0 0 0 2 6c0 4.314 6 10 6 10zm0-7a3 3 0 1 1 0-6 3 3 0 0 1 0 6z"/>
                  </svg>
                  <?= htmlspecialchars($event['location']) ?>
                </p>
              <?php endif; ?>
            </div>
          </div>
        <?php endforeach; ?>
        
        <?php if (count($upcoming_events_slideshow) > 1): ?>
          <div class="slideshow-controls">
            <button class="slide-btn" onclick="changeEventSlide(-1)" aria-label="Previous event">
              <svg width="20" height="20" fill="currentColor" viewBox="0 0 16 16">
                <path fill-rule="evenodd" d="M11.354 1.646a.5.5 0 0 1 0 .708L5.707 8l5.647 5.646a.5.5 0 0 1-.708.708l-6-6a.5.5 0 0 1 0-.708l6-6a.5.5 0 0 1 .708 0z"/>
              </svg>
            </button>
            <div class="slide-dots">
              <?php for($i = 0; $i < count($upcoming_events_slideshow); $i++): ?>
                <span class="dot <?= $i === 0 ? 'active' : '' ?>" onclick="goToEventSlide(<?= $i ?>)"></span>
              <?php endfor; ?>
            </div>
            <button class="slide-btn" onclick="changeEventSlide(1)" aria-label="Next event">
              <svg width="20" height="20" fill="currentColor" viewBox="0 0 16 16">
                <path fill-rule="evenodd" d="M4.646 1.646a.5.5 0 0 1 .708 0l6 6a.5.5 0 0 1 0 .708l-6 6a.5.5 0 0 1-.708-.708L10.293 8 4.646 2.354a.5.5 0 0 1 0-.708z"/>
              </svg>
            </button>
          </div>
        <?php endif; ?>
      </div>
    </div>
  <?php endif; ?>
  
  <!-- Upcoming Events List Widget -->
  <?php if (!empty($upcoming_events_list)): ?>
    <div class="widget sidebar-widget">
      <h3>Upcoming Events</h3>
      <ul class="events-list">
        <?php foreach($upcoming_events_list as $event): ?>
          <li>
            <span class="event-date">
              <?= date('M d', strtotime($event['start_date'])) ?>
              <?php if ($event['end_date'] && $event['end_date'] !== $event['start_date']): ?>
                - <?= date('d', strtotime($event['end_date'])) ?>
              <?php endif; ?>
            </span>
            <span class="event-name"><?= htmlspecialchars($event['name']) ?></span>
            <?php if (!empty($event['location'])): ?>
              <span class="event-mini-location"><?= htmlspecialchars($event['location']) ?></span>
            <?php endif; ?>
          </li>
        <?php endforeach; ?>
      </ul>
      <a href="things-to-do.php" class="widget-link">View All Events →</a>
    </div>
  <?php elseif (empty($upcoming_events_slideshow)): ?>
    <div class="widget sidebar-widget">
      <h3>Upcoming Events</h3>
      <p class="no-events">No upcoming events scheduled</p>
    </div>
  <?php endif; ?>
</aside>

</main>

<!-- LOCAL BUSINESS DIRECTORY TEASER -->
<section class="business-directory-teaser" itemscope itemtype="http://schema.org/LocalBusiness">
  <div class="container">
    <div class="teaser-content">
      <div class="teaser-text">
        <span class="teaser-badge">Explore Local</span>
        <h2 itemprop="name">Matatiele Business Directory</h2>
        <p itemprop="description">Discover local shops, restaurants, services, and businesses that make Matatiele unique. Support local and find everything you need during your visit.</p>
        <div class="business-categories">
          <span class="category-tag" itemprop="servesCuisine">🍽️ Restaurants</span>
          <span class="category-tag" itemprop="servesCuisine">🛍️ Shops</span>
          <span class="category-tag" itemprop="servesCuisine">⚙️ Services</span>
          <span class="category-tag" itemprop="servesCuisine">🏥 Healthcare</span>
          <span class="category-tag" itemprop="servesCuisine">⛽ Fuel Stations</span>
        </div>
        <a href="business-directory.php" class="btn btn-primary" itemprop="url">Browse Directory</a>
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
  font-size: clamp(0.9rem, 1.5vw, 1rem);
}

.btn-primary {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
}

.btn-primary:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
}

.btn-secondary {
  background: white;
  color: #667eea;
  border: 2px solid #667eea;
}

.btn-secondary:hover {
  background: #667eea;
  color: white;
}

.hero-image {
  position: relative;
  height: 100%;
  min-height: 400px;
}

.image-wrapper {
  position: relative;
  height: 100%;
  border-radius: 16px;
  overflow: hidden;
  box-shadow: 0 8px 24px rgba(0, 0, 0, 0.15);
}

.hero-img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.image-overlay {
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: linear-gradient(180deg, transparent 60%, rgba(0, 0, 0, 0.3) 100%);
}

/* ========== HERO OVERLAY AD ========== */
.hero-ad-overlay {
  position: absolute;
  bottom: 20px;
  right: 20px;
  z-index: 10;
  background: rgba(255, 255, 255, 0.98);
  border-radius: 12px;
  padding: 8px;
  box-shadow: 0 8px 24px rgba(0, 0, 0, 0.25);
  max-width: min(336px, calc(100% - 40px));
  animation: fadeInUp 0.8s ease-out;
}

.hero-ad-overlay img {
  display: block;
  width: 100%;
  height: auto;
  border-radius: 8px;
}

.ad-label-overlay {
  display: block;
  font-size: 0.65rem;
  color: #999;
  text-transform: uppercase;
  letter-spacing: 1px;
  margin-bottom: 6px;
  font-weight: 600;
  text-align: center;
}

@keyframes fadeInUp {
  from {
    opacity: 0;
    transform: translateY(20px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

/* ========== AD SECTIONS ========== */
.ad-banner-top {
  background: linear-gradient(180deg, #f8f9fa 0%, #ffffff 100%);
  padding: 2rem 0;
  border-bottom: 1px solid #e0e0e0;
  margin-bottom: 2rem;
}

.ad-container {
  position: relative;
  text-align: center;
  max-width: 970px;
  margin: 0 auto;
  padding: 0 1rem;
}

.ad-container img {
  width: 100%;
  height: auto;
  border-radius: 12px;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
  transition: transform 0.3s ease;
}

.ad-container a:hover img {
  transform: scale(1.02);
  box-shadow: 0 6px 16px rgba(0, 0, 0, 0.15);
}

.ad-label {
  display: inline-block;
  font-size: 0.7rem;
  color: #999;
  text-transform: uppercase;
  letter-spacing: 1px;
  margin-bottom: 0.8rem;
  font-weight: 600;
}

.ad-inline {
  padding: 2rem 0;
  text-align: center;
}

.ad-inline-container {
  display: inline-block;
  max-width: min(468px, 100%);
  width: 100%;
  padding: 0 1rem;
}

.ad-inline-container img {
  width: 100%;
  height: auto;
  border-radius: 8px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
  transition: all 0.3s ease;
}

.ad-inline-container a:hover img {
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
}

.ad-banner-bottom {
  background: linear-gradient(0deg, #f8f9fa 0%, #ffffff 100%);
  padding: 2rem 0;
  border-top: 1px solid #e0e0e0;
  margin-top: 3rem;
}

/* ========== SIDEBAR LAYOUT ========== */
.main-with-sidebar {
  display: grid;
  grid-template-columns: 1fr 300px;
  gap: 2rem;
  max-width: 1400px;
  margin: 0 auto;
  padding: 2rem 1rem;
}

.main-content {
  min-width: 0;
}

.sidebar {
  position: relative;
  display: flex;
  flex-direction: column;
  gap: 1.5rem;
}

.sidebar-widget {
  background: white;
  border-radius: 12px;
  padding: 1.5rem;
  box-shadow: 0 2px 8px rgba(0,0,0,0.08);
}

.sidebar-widget h3 {
  margin: 0 0 1rem 0;
  font-size: clamp(1rem, 2vw, 1.2rem);
  color: #333;
}

/* ========== ABOUT SECTION ========== */
.about-glance-section {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 3rem;
  padding: 2rem 0;
  align-items: start;
}

.about-content h2 {
  font-size: clamp(1.5rem, 3.5vw, 2rem);
  margin-bottom: 1rem;
  color: #333;
}

.about-content .lead {
  font-size: clamp(1rem, 2vw, 1.15rem);
  font-weight: 500;
  color: #555;
  margin-bottom: 1rem;
}

.about-content p {
  color: #666;
  line-height: 1.7;
  margin-bottom: 1rem;
  font-size: clamp(0.9rem, 1.8vw, 1rem);
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

.glance-video:fullscreen {
  max-width: none;
  width: 100%;
  height: 100%;
  border-radius: 0;
}

/* ========== FEATURED SECTIONS ========== */
.featured-section {
  padding: 2rem 0;
}

.featured-section h2 {
  font-size: clamp(1.5rem, 3.5vw, 2rem);
  margin-bottom: 0.5rem;
}

.section-description {
  color: #666;
  margin-bottom: 1.5rem;
  font-size: clamp(0.9rem, 1.8vw, 1rem);
}

.featured-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
  gap: 1.5rem;
}

.featured-card {
  background: white;
  border-radius: 12px;
  overflow: hidden;
  box-shadow: 0 4px 12px rgba(0,0,0,0.1);
  transition: transform 0.3s;
}

.featured-card:hover {
  transform: translateY(-5px);
}

.card-img-top {
  width: 100%;
  height: 200px;
  object-fit: cover;
}

.card-body {
  padding: 1.5rem;
}

.card-title {
  font-size: clamp(1.1rem, 2vw, 1.25rem);
  margin-bottom: 0.5rem;
}

.card-text {
  color: #666;
  font-size: clamp(0.85rem, 1.5vw, 0.95rem);
  margin-bottom: 1rem;
}

.section-footer {
  text-align: center;
  margin-top: 2rem;
}

/* ========== BUSINESS DIRECTORY TEASER (FIXED) ========== */
.business-directory-teaser {
  background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
  padding: 4rem 1rem;
  color: white;
  margin-top: 3rem;
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

/* FIXED: Business Preview Grid */
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
}

.gallery-teaser h2 {
  font-size: clamp(1.5rem, 3.5vw, 2rem);
  margin-bottom: 0.5rem;
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

.sponsors-section h2 {
  text-align: center;
  font-size: clamp(1.5rem, 3.5vw, 2rem);
  margin-bottom: 0.5rem;
  color: #333;
}

.section-subtitle {
  text-align: center;
  color: #666;
  margin-bottom: 2.5rem;
  font-size: clamp(0.95rem, 2vw, 1.1rem);
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

.sponsor-card:hover {
  transform: translateY(-5px);
  box-shadow: 0 4px 16px rgba(0,0,0,0.12);
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

/* ========== SLIDESHOW WIDGETS ========== */
.events-slideshow-widget,
.ads-slideshow-widget {
  padding: 0 !important;
  overflow: hidden;
}

.events-slideshow-widget h3,
.ads-slideshow-widget .ad-label {
  padding: 1.5rem 1.5rem 1rem 1.5rem;
  margin: 0;
}

.ads-slideshow-widget {
  position: relative;
}

.ads-slideshow-widget .ad-label {
  display: block;
  font-size: 0.75rem;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 0.05em;
  color: #6b7280;
  background: #f9fafb;
  border-bottom: 1px solid #e5e7eb;
}

.events-slideshow,
.ads-slideshow {
  position: relative;
  width: 100%;
  overflow: hidden;
}

.event-slide,
.ad-slide {
  display: none;
  width: 100%;
}

.event-slide.active,
.ad-slide.active {
  display: block;
  animation: fadeIn 0.5s ease-in-out;
}

@keyframes fadeIn {
  from { opacity: 0; }
  to { opacity: 1; }
}

.event-slide img,
.ad-slide img {
  width: 100%;
  height: 300px;
  object-fit: cover;
  display: block;
}

.event-slide a,
.ad-slide a {
  display: block;
  text-decoration: none;
  transition: opacity 0.3s ease;
}

.event-slide a:hover,
.ad-slide a:hover {
  opacity: 0.9;
}

.event-info {
  padding: 1.5rem;
  background: white;
}

.event-info h4 {
  margin: 0 0 0.75rem 0;
  font-size: clamp(1rem, 2vw, 1.1rem);
  color: #1f2937;
  line-height: 1.3;
}

.event-date,
.event-location {
  font-size: clamp(0.8rem, 1.5vw, 0.875rem);
  color: #6b7280;
  margin: 0.5rem 0;
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.slideshow-controls {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 1rem 1.5rem;
  background: white;
  border-top: 1px solid #e5e7eb;
}

.ads-controls {
  position: absolute;
  bottom: 0;
  left: 0;
  right: 0;
  background: rgba(255, 255, 255, 0.95);
  backdrop-filter: blur(10px);
}

.slide-btn {
  background: transparent;
  border: 1px solid #d1d5db;
  border-radius: 50%;
  width: 36px;
  height: 36px;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  transition: all 0.2s ease;
  color: #6b7280;
}

.slide-btn:hover {
  background: #667eea;
  border-color: #667eea;
  color: white;
  transform: scale(1.05);
}

.slide-dots {
  display: flex;
  gap: 0.5rem;
  align-items: center;
}

.dot {
  width: 8px;
  height: 8px;
  border-radius: 50%;
  background: #d1d5db;
  cursor: pointer;
  transition: all 0.2s ease;
}

.dot.active,
.dot:hover {
  background: #667eea;
  transform: scale(1.2);
}

/* ========== EVENTS & QUICK LINKS ========== */
.quick-links {
  list-style: none;
  padding: 0;
  margin: 0;
}

.quick-links li {
  border-bottom: 1px solid #e5e7eb;
}

.quick-links li:last-child {
  border-bottom: none;
}

.quick-links a {
  display: block;
  padding: 0.75rem 0;
  color: #1f2937;
  text-decoration: none;
  transition: color 0.2s ease;
  font-weight: 500;
  font-size: clamp(0.9rem, 1.5vw, 1rem);
}

.quick-links a:hover {
  color: #667eea;
}

.events-list {
  list-style: none;
  padding: 0;
  margin: 0;
}

.events-list li {
  padding: 0.75rem 0;
  border-bottom: 1px solid #e5e7eb;
  display: flex;
  flex-direction: column;
  gap: 0.25rem;
}

.events-list li:last-child {
  border-bottom: none;
}

.events-list .event-date {
  font-size: clamp(0.7rem, 1.2vw, 0.75rem);
  font-weight: 600;
  color: #667eea;
  text-transform: uppercase;
  letter-spacing: 0.05em;
}

.events-list .event-name {
  font-weight: 500;
  color: #1f2937;
  line-height: 1.4;
  font-size: clamp(0.85rem, 1.5vw, 0.95rem);
}

.events-list .event-mini-location {
  font-size: clamp(0.7rem, 1.2vw, 0.75rem);
  color: #6b7280;
}

.no-events {
  color: #6b7280;
  font-style: italic;
  text-align: center;
  padding: 1rem 0;
}

.widget-link {
  display: inline-block;
  margin-top: 1rem;
  color: #667eea;
  text-decoration: none;
  font-weight: 600;
  font-size: clamp(0.85rem, 1.5vw, 0.9rem);
  transition: color 0.2s ease;
}

.widget-link:hover {
  color: #5568d3;
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

.lightbox-close:hover {
  color: #bbb;
}

/* ============================================
   RESPONSIVE BREAKPOINTS - MOBILE FIRST
   ============================================ */

/* Extra Small Phones (320px - 480px) */
@media (max-width: 480px) {
  .hero-split,
  .about-glance-section,
  .teaser-content {
    grid-template-columns: 1fr;
    gap: 1.5rem;
  }
  
  .hero-image {
    min-height: 250px;
  }
  
  .hero-ad-overlay {
    position: relative;
    bottom: auto;
    right: auto;
    max-width: 100%;
    margin-top: 1rem;
  }
  
  .featured-grid {
    grid-template-columns: 1fr;
  }
  
  .glance-grid {
    grid-template-columns: 1fr;
  }
  
  .business-preview-grid {
    grid-template-columns: 1fr;
  }
  
  .gallery-grid {
    grid-template-columns: repeat(2, 1fr);
  }
  
  .sponsors-grid {
    grid-template-columns: repeat(2, 1fr);
    gap: 1rem;
  }
  
  .btn {
    width: 100%;
    text-align: center;
  }
  
  .hero-actions {
    flex-direction: column;
  }
  
  .glance-video-wrapper {
    justify-content: center;
  }
}

/* Large Phones (481px - 768px) */
@media (min-width: 481px) and (max-width: 768px) {
  .hero-split,
  .about-glance-section,
  .teaser-content {
    grid-template-columns: 1fr;
    gap: 2rem;
  }
  
  .hero-image {
    min-height: 300px;
  }
  
  .featured-grid {
    grid-template-columns: repeat(2, 1fr);
  }
  
  .gallery-grid {
    grid-template-columns: repeat(3, 1fr);
  }
  
  .sponsors-grid {
    grid-template-columns: repeat(3, 1fr);
  }
  
  .glance-video-wrapper {
    justify-content: center;
  }
}

/* Tablets (769px - 1024px) */
@media (min-width: 769px) and (max-width: 1024px) {
  .main-with-sidebar {
    grid-template-columns: 1fr;
  }
  
  .sidebar {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 1.5rem;
  }
  
  .about-glance-section {
    grid-template-columns: 1fr;
  }
  
  .teaser-content {
    gap: 2rem;
  }
  
  .glance-video-wrapper {
    justify-content: center;
    max-width: 100%;
  }
}

/* Standard Tablets and Small Laptops */
@media (max-width: 1024px) {
  .hero-ad-overlay {
    max-width: min(280px, calc(100% - 40px));
  }
}

/* Mobile Devices (max-width: 768px) */
@media (max-width: 768px) {
  .main-with-sidebar {
    grid-template-columns: 1fr;
    padding: 1rem;
  }
  
  .sidebar {
    grid-template-columns: 1fr;
  }
  
  .business-directory-teaser {
    padding: 3rem 1rem;
  }
  
  .gallery-teaser,
  .sponsors-section {
    padding: 3rem 1rem;
  }
  
  .ad-banner-top,
  .ad-banner-bottom {
    padding: 1.5rem 0;
  }
  
  .sponsor-card {
    padding: 1rem;
    min-height: 100px;
  }
  
  .sponsor-card img {
    max-height: 60px;
  }
}

/* Mobile-specific adjustments */
@media (max-width: 768px) {
  .hero-content {
    padding: 1rem;
  }
  
  .about-content,
  .glance-cards {
    padding: 0 0.5rem;
  }
  
  .featured-section {
    padding: 1.5rem 0;
  }
}
</style>

<script>
// Events Slideshow
let currentEventSlide = 0;
const eventSlides = document.querySelectorAll('.event-slide');
const eventDots = document.querySelectorAll('.events-slideshow .dot');
let eventSlideInterval;

function showEventSlide(n) {
    if (eventSlides.length === 0) return;
    
    if (n >= eventSlides.length) {
        currentEventSlide = 0;
    }
    if (n < 0) {
        currentEventSlide = eventSlides.length - 1;
    }
    
    eventSlides.forEach(slide => slide.classList.remove('active'));
    eventDots.forEach(dot => dot.classList.remove('active'));
    
    eventSlides[currentEventSlide].classList.add('active');
    if (eventDots[currentEventSlide]) {
        eventDots[currentEventSlide].classList.add('active');
    }
}

function changeEventSlide(n) {
    currentEventSlide += n;
    showEventSlide(currentEventSlide);
    resetEventSlideInterval();
}

function goToEventSlide(n) {
    currentEventSlide = n;
    showEventSlide(currentEventSlide);
    resetEventSlideInterval();
}

function autoEventSlide() {
    if (eventSlides.length > 1) {
        currentEventSlide++;
        showEventSlide(currentEventSlide);
    }
}

function resetEventSlideInterval() {
    clearInterval(eventSlideInterval);
    if (eventSlides.length > 1) {
        eventSlideInterval = setInterval(autoEventSlide, 5000);
    }
}

// Ads Slideshow
let currentAdSlide = 0;
const adSlides = document.querySelectorAll('.ad-slide');
const adDots = document.querySelectorAll('.ads-slideshow .dot');
let adSlideInterval;

function showAdSlide(n) {
    if (adSlides.length === 0) return;
    
    if (n >= adSlides.length) {
        currentAdSlide = 0;
    }
    if (n < 0) {
        currentAdSlide = adSlides.length - 1;
    }
    
    adSlides.forEach(slide => slide.classList.remove('active'));
    adDots.forEach(dot => dot.classList.remove('active'));
    
    adSlides[currentAdSlide].classList.add('active');
    if (adDots[currentAdSlide]) {
        adDots[currentAdSlide].classList.add('active');
    }
}

function changeAdSlide(n) {
    currentAdSlide += n;
    showAdSlide(currentAdSlide);
    resetAdSlideInterval();
}

function goToAdSlide(n) {
    currentAdSlide = n;
    showAdSlide(currentAdSlide);
    resetAdSlideInterval();
}

function autoAdSlide() {
    if (adSlides.length > 1) {
        currentAdSlide++;
        showAdSlide(currentAdSlide);
    }
}

function resetAdSlideInterval() {
    clearInterval(adSlideInterval);
    if (adSlides.length > 1) {
        adSlideInterval = setInterval(autoAdSlide, 6000);
    }
}

// Initialize both slideshows
document.addEventListener('DOMContentLoaded', function() {
    // Initialize events slideshow
    if (eventSlides.length > 0) {
        showEventSlide(currentEventSlide);
        if (eventSlides.length > 1) {
            eventSlideInterval = setInterval(autoEventSlide, 5000);
        }
    }
    
    // Initialize ads slideshow
    if (adSlides.length > 0) {
        showAdSlide(currentAdSlide);
        if (adSlides.length > 1) {
            adSlideInterval = setInterval(autoAdSlide, 6000);
        }
    }
});

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