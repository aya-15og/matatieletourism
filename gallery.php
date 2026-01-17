<?php
ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);
error_reporting(E_ALL);

require 'includes/config.php'; // $pdo

$page_title = "Gallery - Matatiele Tourism";

// Fetch all gallery images (using confirmed columns)
try {
    // FIX: Only select columns confirmed to exist in the database: id, filename, caption, featured
    $stmt = $pdo->query("SELECT id, filename, caption, featured FROM gallery ORDER BY created_at DESC");
    $gallery_images = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("Gallery fetch error: " . $e->getMessage());
    $gallery_images = [];
}

// NOTE: Category and filtering logic is removed as the 'category' column does not exist.

include 'header.php';
?>

<!-- HERO SECTION -->
<section class="hero-split">
  <div class="hero-content">
    <div class="hero-badge">Explore</div>
    <h1>Matatiele Gallery</h1>
    <p class="hero-description">Snapshots capturing Matatiele's breathtaking landscapes, rich culture, and warm people — discover the natural charm of our highland town.</p>
    <div class="hero-actions">
      <a href="index.php" class="btn btn-secondary">Back to Home</a>
    </div>
  </div>

  <div class="hero-image">
    <div class="image-wrapper">
      <img src="images/home/matatiele-royal-hotel.jpg" alt="Matatiele Gallery" class="hero-img">
      <div class="image-overlay"></div>
    </div>
  </div>
</section>

<main class="container">

  <!-- Gallery Grid -->
  <section id="gallery" style="margin-top: 3rem;">
    <?php if (!empty($gallery_images)): ?>
      <div class="card-grid">
        <?php foreach($gallery_images as $img): ?>
          <div class="card gallery-item" 
               data-featured="<?= $img['featured'] ? '1' : '0' ?>">
            <img src="images/gallery/<?= htmlspecialchars($img['filename']); ?>" 
                 alt="<?= htmlspecialchars($img['caption'] ?? 'Matatiele Gallery Image'); ?>" 
                 class="clickable-img">
            
            <?php if (!empty($img['caption']) || $img['featured']): ?>
            <div class="content">
              <?php if (!empty($img['caption'])): ?>
                <div class="meta"><?= htmlspecialchars($img['caption']); ?></div>
              <?php endif; ?>
              <?php if ($img['featured']): ?>
                <span style="display: inline-block; background: #ffd700; color: #333; padding: 0.2rem 0.6rem; border-radius: 10px; font-size: 0.75rem; font-weight: 600; margin-top: 0.5rem;">⭐ Featured</span>
              <?php endif; ?>
            </div>
            <?php endif; ?>
          </div>
        <?php endforeach; ?>
      </div>
      
    <?php else: ?>
      <p style="text-align: center; padding: 3rem; color: #999;">No gallery images available yet. Check back soon!</p>
    <?php endif; ?>
  </section>

</main>

<!-- LIGHTBOX -->
<div class="lightbox-overlay" id="lightbox">
  <div class="lightbox-content">
    <span class="lightbox-close" id="lightboxClose">&times;</span>
    <img id="lightboxImg" src="" alt="Full view">
  </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", function() {
  // Lightbox functionality
  const overlay = document.getElementById("lightbox");
  const img = document.getElementById("lightboxImg");
  const close = document.getElementById("lightboxClose");

  document.querySelectorAll(".clickable-img").forEach(i => {
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
</script>

<?php include 'footer.php'; ?>