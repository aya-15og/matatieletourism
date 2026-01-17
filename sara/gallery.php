<?php
session_start();
$page = 'gallery';
include 'includes/header.php';

// Configuration
$base_gallery_dir = 'images/gallery/';
$categories = [
    'rooms' => 'Rooms',
    'exterior' => 'Exterior',
];
$all_image_files = [];

// Function to create a title from a filename
function get_title_from_filename($filename) {
    $name = pathinfo($filename, PATHINFO_FILENAME);
    // Replace hyphens/underscores with spaces and capitalize words
    $title = ucwords(str_replace(['-', '_'], ' ', $name));
    return $title;
}

// Loop through categories and find images in their respective subdirectories
foreach ($categories as $dir_name => $display_name) {
    $current_dir = $base_gallery_dir . $dir_name . '/';
    // Find all image files in the subdirectory
    // Note: The path is relative to the current script's execution
    $image_files = glob($current_dir . '*.{jpg,jpeg,png,gif}', GLOB_BRACE);
    
    foreach ($image_files as $image_path) {
        $all_image_files[] = [
            'path' => $image_path,
            'category' => $dir_name,
            'title' => get_title_from_filename($image_path),
            'alt' => "Image of " . get_title_from_filename($image_path) . " in the " . $display_name . " category",
        ];
    }
}

// Fallback is removed as the user explicitly requested only 'rooms' and 'exterior' subdirectories.

?>

<!-- Page Header -->
<section class="page-header">
    <div class="page-header-overlay"></div>
    <div class="page-header-content">
        <h1>Photo Gallery</h1>
        <p>Explore Sara-Lee Guesthouse</p>
    </div>
</section>

<!-- Gallery Section -->
<section class="gallery-section">
    <div class="container">
        <!-- Filter buttons updated to only show 'All Photos', 'Rooms', and 'Exterior' -->
        <div class="gallery-filter">
            <button class="filter-btn active" data-filter="all">All Photos</button>
            <?php foreach ($categories as $dir_name => $display_name): ?>
                <button class="filter-btn" data-filter="<?php echo $dir_name; ?>"><?php echo $display_name; ?></button>
            <?php endforeach; ?>
        </div>

        <div class="gallery-grid">
            <?php foreach ($all_image_files as $image): ?>
                <div class="gallery-item" data-category="<?php echo $image['category']; ?>">
                    <img src="<?php echo $image['path']; ?>" alt="<?php echo $image['alt']; ?>">
                    <div class="gallery-overlay">
                        <h4><?php echo $image['title']; ?></h4>
                        <p>Click to view full image</p>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- Lightbox Modal (HTML is kept as is) -->
<div class="lightbox" id="lightbox">
    <span class="lightbox-close" onclick="closeLightbox()">&times;</span>
    <div class="lightbox-content">
        <img id="lightbox-img" src="" alt="">
    </div>
</div>

<script>
// --- Gallery Filter Logic ---
const filterButtons = document.querySelectorAll('.filter-btn');
const galleryItems = document.querySelectorAll('.gallery-item');

filterButtons.forEach(button => {
    button.addEventListener('click', function() {
        // Remove active class from all buttons
        filterButtons.forEach(btn => btn.classList.remove('active'));
        // Add active class to clicked button
        this.classList.add('active');
        
        const filter = this.getAttribute('data-filter');
        
        galleryItems.forEach(item => {
            // Check if the item's category matches the filter or if the filter is 'all'
            const itemCategory = item.getAttribute('data-category');
            if (filter === 'all' || itemCategory === filter) {
                item.style.display = 'block';
                setTimeout(() => {
                    item.style.opacity = '1';
                    item.style.transform = 'scale(1)';
                }, 10);
            } else {
                item.style.opacity = '0';
                item.style.transform = 'scale(0.8)';
                setTimeout(() => {
                    item.style.display = 'none';
                }, 300);
            }
        });
    });
});

// --- Lightbox functionality ---
const lightbox = document.getElementById('lightbox');
const lightboxImg = document.getElementById('lightbox-img');

// Re-select gallery items to ensure event listeners are attached to the dynamically generated content
const dynamicGalleryItems = document.querySelectorAll('.gallery-item');

dynamicGalleryItems.forEach(item => {
    item.addEventListener('click', function() {
        const imgSrc = this.querySelector('img').src;
        lightboxImg.src = imgSrc;
        lightbox.classList.add('active');
        document.body.style.overflow = 'hidden';
    });
});

function closeLightbox() {
    lightbox.classList.remove('active');
    document.body.style.overflow = 'auto';
}

// Close lightbox on background click
lightbox.addEventListener('click', function(e) {
    if (e.target === lightbox) {
        closeLightbox();
    }
});

// Close lightbox with Escape key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape' && lightbox.classList.contains('active')) {
        closeLightbox();
    }
});
</script>

<?php include 'includes/footer.php'; ?>
