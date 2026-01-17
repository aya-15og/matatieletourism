<?php
require 'includes/config.php';

// Get attraction ID
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if (!$id) {
    header("Location: attractions.php");
    exit;
}

// Fetch attraction details
$stmt = $pdo->prepare("SELECT * FROM attractions WHERE id = ?");
$stmt->execute([$id]);
$attraction = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$attraction) {
    header("Location: attractions.php");
    exit;
}

// Get related attractions (same category)
$related_stmt = $pdo->prepare("SELECT * FROM attractions WHERE category = ? AND id != ? ORDER BY RAND() LIMIT 3");
$related_stmt->execute([$attraction['category'], $id]);
$related = $related_stmt->fetchAll(PDO::FETCH_ASSOC);

$page_title = $attraction['name'] . " - Attractions";
$hero_folder = "images/hero/attractions/";
$hero_heading = htmlspecialchars($attraction['name']);
$hero_text = htmlspecialchars(substr($attraction['description'], 0, 150)) . '...';

include 'header.php';

$labels = [
    'nature' => 'Nature & Wildlife',
    'culture' => 'Cultural & Historical',
    'adventure' => 'Adventure & Sports',
    'scenic' => 'Scenic Routes'
];

$category_label = $labels[$attraction['category']] ?? 'Other';
$image = !empty($attraction['image']) ? htmlspecialchars($attraction['image']) : 'images/placeholder.jpg';
?>

<main class="container attraction-detail">
    <!-- Breadcrumb -->
    <nav class="breadcrumb">
        <a href="index.php">Home</a> / 
        <a href="attractions.php">Attractions</a> / 
        <span><?php echo htmlspecialchars($attraction['name']); ?></span>
    </nav>

    <!-- Main Content -->
    <div class="detail-wrapper">
        <!-- Main Image Gallery -->
        <section class="image-section">
            <div class="main-image">
                <img src="<?php echo $image; ?>" alt="<?php echo htmlspecialchars($attraction['name']); ?>">
                <span class="category-badge"><?php echo $category_label; ?></span>
            </div>
        </section>

        <!-- Content Section -->
        <section class="content-section">
            <div class="header-section">
                <h1><?php echo htmlspecialchars($attraction['name']); ?></h1>
                <?php if ($attraction['featured']): ?>
                    <span class="featured-badge">⭐ Featured Attraction</span>
                <?php endif; ?>
            </div>

            <!-- Info Cards -->
            <div class="info-grid">
                <?php if (!empty($attraction['location'])): ?>
                <div class="info-card">
                    <div class="icon">📍</div>
                    <div class="info-content">
                        <h3>Location</h3>
                        <p><?php echo htmlspecialchars($attraction['location']); ?></p>
                    </div>
                </div>
                <?php endif; ?>

                <?php if (!empty($attraction['contact'])): ?>
                <div class="info-card">
                    <div class="icon">📞</div>
                    <div class="info-content">
                        <h3>Contact</h3>
                        <p><?php echo nl2br(htmlspecialchars($attraction['contact'])); ?></p>
                    </div>
                </div>
                <?php endif; ?>

                <div class="info-card">
                    <div class="icon">🏷️</div>
                    <div class="info-content">
                        <h3>Category</h3>
                        <p><?php echo $category_label; ?></p>
                    </div>
                </div>
            </div>

            <!-- Description -->
            <div class="description-section">
                <h2>About This Attraction</h2>
                <div class="description-content">
                    <?php echo nl2br(htmlspecialchars($attraction['description'])); ?>
                </div>
            </div>

            <!-- Additional Information Sections -->
            <div class="extra-info">
                <?php if ($attraction['category'] === 'nature'): ?>
                <div class="info-block">
                    <h3>🌿 What to Expect</h3>
                    <ul>
                        <li>Spectacular natural scenery and wildlife viewing opportunities</li>
                        <li>Well-maintained hiking trails suitable for various fitness levels</li>
                        <li>Photography opportunities with stunning mountain backdrops</li>
                        <li>Guided tours available upon request</li>
                    </ul>
                </div>
                <?php elseif ($attraction['category'] === 'culture'): ?>
                <div class="info-block">
                    <h3>🏛️ Cultural Experience</h3>
                    <ul>
                        <li>Rich historical significance and cultural heritage</li>
                        <li>Educational tours and interpretive displays</li>
                        <li>Local artisan demonstrations and crafts</li>
                        <li>Traditional music and storytelling experiences</li>
                    </ul>
                </div>
                <?php elseif ($attraction['category'] === 'adventure'): ?>
                <div class="info-block">
                    <h3>⛰️ Adventure Activities</h3>
                    <ul>
                        <li>Thrilling outdoor activities for adventure seekers</li>
                        <li>Professional guides and safety equipment provided</li>
                        <li>Suitable for various skill levels and experience</li>
                        <li>Advance booking recommended during peak season</li>
                    </ul>
                </div>
                <?php elseif ($attraction['category'] === 'scenic'): ?>
                <div class="info-block">
                    <h3>🚗 Scenic Journey</h3>
                    <ul>
                        <li>Breathtaking panoramic views along the route</li>
                        <li>Multiple viewpoints and photo stops available</li>
                        <li>Best visited during clear weather conditions</li>
                        <li>Allow extra time for sightseeing and photography</li>
                    </ul>
                </div>
                <?php endif; ?>

                <div class="info-block">
                    <h3>ℹ️ Visitor Information</h3>
                    <ul>
                        <li>Please respect the environment and local communities</li>
                        <li>Wear appropriate footwear and clothing for outdoor activities</li>
                        <li>Bring water, sunscreen, and insect repellent</li>
                        <li>Check weather conditions before visiting</li>
                        <li>Keep to designated paths and follow safety guidelines</li>
                    </ul>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="action-buttons">
                <a href="attractions.php" class="btn btn-secondary">← Back to All Attractions</a>
                <a href="contact.php?subject=Inquiry about <?php echo urlencode($attraction['name']); ?>" class="btn btn-primary">Contact for More Info</a>
            </div>
        </section>
    </div>

    <!-- Related Attractions -->
    <?php if (!empty($related)): ?>
    <section class="related-section">
        <h2>You May Also Like</h2>
        <div class="related-grid">
            <?php foreach ($related as $rel): ?>
            <div class="related-card">
                <div class="img-container">
                    <img src="<?php echo !empty($rel['image']) ? htmlspecialchars($rel['image']) : 'images/placeholder.jpg'; ?>" 
                         alt="<?php echo htmlspecialchars($rel['name']); ?>">
                </div>
                <div class="card-content">
                    <h3><?php echo htmlspecialchars($rel['name']); ?></h3>
                    <p><?php echo htmlspecialchars(substr($rel['description'], 0, 100)) . '...'; ?></p>
                    <a href="attraction.php?id=<?php echo $rel['id']; ?>" class="view-link">View Details →</a>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </section>
    <?php endif; ?>
</main>

<style>
    .attraction-detail { padding: 2rem 0 4rem; }
    
    /* Breadcrumb */
    .breadcrumb {
        padding: 1rem 0;
        font-size: 0.9rem;
        color: #666;
    }
    .breadcrumb a {
        color: #28a745;
        text-decoration: none;
        transition: color 0.3s;
    }
    .breadcrumb a:hover { color: #20c997; }
    .breadcrumb span { color: #333; font-weight: 500; }

    /* Detail Wrapper */
    .detail-wrapper {
        display: grid;
        grid-template-columns: 1fr;
        gap: 2rem;
        margin-top: 1.5rem;
    }

    /* Image Section */
    .image-section { position: relative; }
    .main-image {
        position: relative;
        border-radius: 20px;
        overflow: hidden;
        box-shadow: 0 10px 30px rgba(0,0,0,0.1);
    }
    .main-image img {
        width: 100%;
        height: 450px;
        object-fit: cover;
        display: block;
    }
    .category-badge {
        position: absolute;
        top: 20px;
        right: 20px;
        background: rgba(0, 123, 255, 0.9);
        color: white;
        padding: 0.6rem 1.2rem;
        border-radius: 20px;
        font-weight: 600;
        font-size: 0.9rem;
        box-shadow: 0 4px 12px rgba(0,0,0,0.2);
    }

    /* Content Section */
    .content-section { padding: 1rem 0; }
    .header-section {
        display: flex;
        align-items: center;
        gap: 1rem;
        flex-wrap: wrap;
        margin-bottom: 2rem;
    }
    .header-section h1 {
        font-size: 2.5rem;
        color: #333;
        margin: 0;
    }
    .featured-badge {
        background: linear-gradient(45deg, #ffd700, #ffed4e);
        color: #333;
        padding: 0.5rem 1rem;
        border-radius: 20px;
        font-weight: 600;
        font-size: 0.85rem;
        box-shadow: 0 2px 8px rgba(255, 215, 0, 0.3);
    }

    /* Info Grid */
    .info-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 1.5rem;
        margin: 2rem 0;
    }
    .info-card {
        display: flex;
        align-items: flex-start;
        gap: 1rem;
        padding: 1.5rem;
        background: #f8f9fa;
        border-radius: 15px;
        border-left: 4px solid #28a745;
        transition: transform 0.3s, box-shadow 0.3s;
    }
    .info-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 6px 20px rgba(0,0,0,0.1);
    }
    .info-card .icon {
        font-size: 2rem;
        flex-shrink: 0;
    }
    .info-card h3 {
        margin: 0 0 0.5rem;
        color: #333;
        font-size: 1.1rem;
    }
    .info-card p {
        margin: 0;
        color: #666;
        line-height: 1.6;
    }

    /* Description */
    .description-section {
        margin: 3rem 0;
        padding: 2rem;
        background: white;
        border-radius: 15px;
        box-shadow: 0 4px 15px rgba(0,0,0,0.05);
    }
    .description-section h2 {
        color: #333;
        margin-bottom: 1.5rem;
        font-size: 1.8rem;
    }
    .description-content {
        color: #555;
        line-height: 1.8;
        font-size: 1.05rem;
    }

    /* Extra Info */
    .extra-info {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
        gap: 2rem;
        margin: 3rem 0;
    }
    .info-block {
        padding: 2rem;
        background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
        border-radius: 15px;
        border: 1px solid #dee2e6;
    }
    .info-block h3 {
        color: #333;
        margin-bottom: 1rem;
        font-size: 1.3rem;
    }
    .info-block ul {
        list-style: none;
        padding: 0;
        margin: 0;
    }
    .info-block li {
        padding: 0.7rem 0;
        padding-left: 1.5rem;
        position: relative;
        color: #555;
        line-height: 1.6;
    }
    .info-block li:before {
        content: '✓';
        position: absolute;
        left: 0;
        color: #28a745;
        font-weight: bold;
    }

    /* Action Buttons */
    .action-buttons {
        display: flex;
        gap: 1rem;
        flex-wrap: wrap;
        margin: 3rem 0;
    }
    .btn {
        padding: 1rem 2rem;
        border-radius: 30px;
        text-decoration: none;
        font-weight: 600;
        transition: all 0.3s;
        display: inline-block;
    }
    .btn-primary {
        background: linear-gradient(45deg, #28a745, #20c997);
        color: white;
        box-shadow: 0 4px 15px rgba(40, 167, 69, 0.3);
    }
    .btn-primary:hover {
        background: linear-gradient(45deg, #20c997, #28a745);
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(40, 167, 69, 0.4);
    }
    .btn-secondary {
        background: white;
        color: #333;
        border: 2px solid #dee2e6;
    }
    .btn-secondary:hover {
        background: #f8f9fa;
        border-color: #28a745;
        color: #28a745;
    }

    /* Related Section */
    .related-section {
        margin-top: 4rem;
        padding-top: 3rem;
        border-top: 2px solid #e9ecef;
    }
    .related-section h2 {
        text-align: center;
        font-size: 2rem;
        margin-bottom: 2rem;
        color: #333;
    }
    .related-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
        gap: 2rem;
    }
    .related-card {
        border-radius: 15px;
        overflow: hidden;
        background: white;
        box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        transition: transform 0.3s, box-shadow 0.3s;
    }
    .related-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 8px 20px rgba(0,0,0,0.15);
    }
    .related-card .img-container img {
        width: 100%;
        height: 180px;
        object-fit: cover;
    }
    .related-card .card-content {
        padding: 1.3rem;
    }
    .related-card h3 {
        margin: 0 0 0.8rem;
        color: #333;
        font-size: 1.2rem;
    }
    .related-card p {
        color: #666;
        font-size: 0.95rem;
        line-height: 1.6;
        margin-bottom: 1rem;
    }
    .view-link {
        color: #28a745;
        text-decoration: none;
        font-weight: 600;
        transition: color 0.3s;
    }
    .view-link:hover { color: #20c997; }

    /* Responsive */
    @media (max-width: 768px) {
        .header-section h1 { font-size: 1.8rem; }
        .main-image img { height: 300px; }
        .info-grid { grid-template-columns: 1fr; }
        .action-buttons { flex-direction: column; }
        .btn { text-align: center; }
    }
</style>

<?php include 'footer.php'; ?>