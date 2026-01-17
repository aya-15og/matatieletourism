<?php
ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);
error_reporting(E_ALL);

require 'includes/config.php'; // $pdo

$page_title = "Activities & Things to Do in Matatiele - Adventure, Nature & Culture";
$page_description = "Discover exciting activities in Matatiele: hiking, fishing, cultural tours, mountain biking, bird watching, and more. Plan your adventure in the Eastern Cape highlands.";

// Get filter parameters
$category_filter = $_GET['category'] ?? 'all';
$difficulty_filter = $_GET['difficulty'] ?? 'all';
$search_query = $_GET['search'] ?? '';

// Fetch all activities from database
$sql = "SELECT * FROM activities WHERE 1=1";
$params = [];

if ($category_filter !== 'all') {
    $sql .= " AND category = ?";
    $params[] = $category_filter;
}

if ($difficulty_filter !== 'all') {
    $sql .= " AND difficulty = ?";
    $params[] = $difficulty_filter;
}

if (!empty($search_query)) {
    $sql .= " AND (name LIKE ? OR description LIKE ?)";
    $params[] = "%{$search_query}%";
    $params[] = "%{$search_query}%";
}

$sql .= " ORDER BY featured DESC, name ASC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$activities = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get categories for filter
$categories = $pdo->query("SELECT DISTINCT category FROM activities WHERE category IS NOT NULL ORDER BY category")->fetchAll(PDO::FETCH_COLUMN);

// Get difficulties for filter
$difficulties = $pdo->query("SELECT DISTINCT difficulty FROM activities WHERE difficulty IS NOT NULL ORDER BY 
    CASE difficulty 
        WHEN 'Easy' THEN 1 
        WHEN 'Moderate' THEN 2 
        WHEN 'Challenging' THEN 3 
        ELSE 4 
    END")->fetchAll(PDO::FETCH_COLUMN);

include 'header.php';
?>

<style>
/* Activities Page Specific Styles */
.page-hero {
    background: linear-gradient(135deg, var(--green) 0%, #1e4d28 100%);
    padding: 60px 0;
    color: white;
    text-align: center;
    margin-bottom: 40px;
}

.page-hero h1 {
    font-size: 42px;
    margin-bottom: 16px;
    color: white;
}

.page-hero p {
    font-size: 18px;
    opacity: 0.95;
    max-width: 700px;
    margin: 0 auto;
}

/* Filters Section */
.filters-section {
    background: white;
    padding: 24px;
    border-radius: 12px;
    box-shadow: var(--card-shadow);
    margin-bottom: 32px;
}

.filters-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 16px;
    margin-top: 16px;
}

.filter-group {
    display: flex;
    flex-direction: column;
    gap: 8px;
}

.filter-group label {
    font-weight: 600;
    color: var(--brown);
    font-size: 14px;
}

.filter-group select,
.filter-group input[type="text"] {
    padding: 10px 12px;
    border: 2px solid #e0e0e0;
    border-radius: 8px;
    font-size: 14px;
    font-family: 'Poppins', sans-serif;
    transition: border-color 0.3s;
}

.filter-group select:focus,
.filter-group input[type="text"]:focus {
    outline: none;
    border-color: var(--green);
}

.filter-buttons {
    display: flex;
    gap: 12px;
    margin-top: 16px;
}

.btn-filter {
    padding: 10px 24px;
    background: var(--green);
    color: white;
    border: none;
    border-radius: 8px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s;
}

.btn-filter:hover {
    background: var(--gold);
    color: var(--brown);
    transform: translateY(-2px);
}

.btn-reset {
    background: transparent;
    color: var(--green);
    border: 2px solid var(--green);
}

.btn-reset:hover {
    background: var(--green);
    color: white;
}

/* Category Pills */
.category-pills {
    display: flex;
    gap: 12px;
    flex-wrap: wrap;
    margin-bottom: 32px;
    justify-content: center;
}

.category-pill {
    padding: 10px 20px;
    background: white;
    border: 2px solid var(--green);
    color: var(--green);
    border-radius: 25px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s;
    text-decoration: none;
}

.category-pill:hover,
.category-pill.active {
    background: var(--green);
    color: white;
    transform: translateY(-2px);
}

/* Activities Grid */
.activities-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 24px;
    flex-wrap: wrap;
    gap: 16px;
}

.activities-count {
    font-size: 18px;
    color: var(--brown);
    font-weight: 600;
}

.view-toggle {
    display: flex;
    gap: 8px;
}

.view-btn {
    padding: 8px 12px;
    background: white;
    border: 2px solid #e0e0e0;
    border-radius: 6px;
    cursor: pointer;
    transition: all 0.3s;
}

.view-btn.active {
    background: var(--green);
    color: white;
    border-color: var(--green);
}

.activities-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
    gap: 24px;
    margin-bottom: 40px;
}

.activities-list {
    display: flex;
    flex-direction: column;
    gap: 24px;
    margin-bottom: 40px;
}

/* Activity Card */
.activity-card {
    background: white;
    border-radius: 12px;
    overflow: hidden;
    box-shadow: var(--card-shadow);
    transition: all 0.3s;
    cursor: pointer;
}

.activity-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 12px 30px rgba(0, 0, 0, 0.16);
}

.activity-image {
    width: 100%;
    height: 220px;
    object-fit: cover;
    background: linear-gradient(135deg, #e0e0e0 0%, #f0f0f0 100%);
}

.activity-featured-badge {
    position: absolute;
    top: 12px;
    right: 12px;
    background: var(--gold);
    color: white;
    padding: 6px 12px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 600;
    z-index: 1;
}

.activity-content {
    padding: 20px;
}

.activity-header {
    margin-bottom: 12px;
}

.activity-title {
    font-size: 20px;
    font-weight: 700;
    color: var(--brown);
    margin-bottom: 8px;
}

.activity-category {
    display: inline-block;
    background: rgba(46, 111, 58, 0.1);
    color: var(--green);
    padding: 4px 12px;
    border-radius: 12px;
    font-size: 12px;
    font-weight: 600;
    margin-bottom: 8px;
}

.activity-description {
    color: #555;
    font-size: 14px;
    line-height: 1.6;
    margin-bottom: 16px;
}

.activity-meta {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 12px;
    margin-bottom: 16px;
}

.meta-item {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 13px;
    color: #666;
}

.meta-icon {
    font-size: 18px;
}

.meta-label {
    font-weight: 600;
    color: var(--brown);
}

.difficulty-badge {
    display: inline-block;
    padding: 4px 10px;
    border-radius: 12px;
    font-size: 12px;
    font-weight: 600;
}

.difficulty-easy {
    background: #d4edda;
    color: #155724;
}

.difficulty-moderate {
    background: #fff3cd;
    color: #856404;
}

.difficulty-challenging {
    background: #f8d7da;
    color: #721c24;
}

.activity-footer {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding-top: 16px;
    border-top: 1px solid #e0e0e0;
}

.activity-price {
    font-size: 18px;
    font-weight: 700;
    color: var(--green);
}

.btn-details {
    padding: 8px 16px;
    background: var(--green);
    color: white;
    border-radius: 6px;
    font-size: 14px;
    font-weight: 600;
    text-decoration: none;
}

.btn-details:hover {
    background: var(--gold);
    color: var(--brown);
}

/* List View */
.activity-card-list {
    display: grid;
    grid-template-columns: 280px 1fr;
    gap: 24px;
    background: white;
    border-radius: 12px;
    overflow: hidden;
    box-shadow: var(--card-shadow);
    transition: all 0.3s;
}

.activity-card-list:hover {
    box-shadow: 0 12px 30px rgba(0, 0, 0, 0.16);
}

.activity-card-list .activity-image {
    width: 100%;
    height: 100%;
    min-height: 220px;
}

.activity-card-list .activity-content {
    padding: 24px;
    display: flex;
    flex-direction: column;
}

.activity-card-list .activity-meta {
    grid-template-columns: repeat(4, 1fr);
}

/* Modal */
.activity-modal {
    display: none;
    position: fixed;
    z-index: 9999;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    overflow: auto;
    background-color: rgba(0, 0, 0, 0.7);
    padding: 20px;
}

.modal-content {
    background-color: #fefefe;
    margin: auto;
    border-radius: 12px;
    max-width: 900px;
    width: 100%;
    max-height: 90vh;
    overflow-y: auto;
    position: relative;
    animation: slideDown 0.3s ease;
}

@keyframes slideDown {
    from {
        transform: translateY(-50px);
        opacity: 0;
    }
    to {
        transform: translateY(0);
        opacity: 1;
    }
}

.modal-close {
    position: sticky;
    top: 0;
    right: 0;
    float: right;
    font-size: 32px;
    font-weight: bold;
    color: #aaa;
    cursor: pointer;
    padding: 10px 15px;
    z-index: 10;
    background: white;
    border-radius: 0 12px 0 0;
}

.modal-close:hover {
    color: #000;
}

.modal-header {
    position: relative;
    height: 300px;
    overflow: hidden;
}

.modal-header img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.modal-body {
    padding: 32px;
}

.modal-title {
    font-size: 32px;
    color: var(--brown);
    margin-bottom: 16px;
}

.modal-meta-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 16px;
    margin-bottom: 24px;
    padding: 20px;
    background: #f8f9fa;
    border-radius: 8px;
}

.modal-section {
    margin-bottom: 24px;
}

.modal-section h3 {
    font-size: 20px;
    color: var(--brown);
    margin-bottom: 12px;
}

.modal-section p {
    line-height: 1.7;
    color: #555;
}

.includes-list {
    list-style: none;
    padding: 0;
}

.includes-list li {
    padding: 8px 0;
    padding-left: 24px;
    position: relative;
}

.includes-list li:before {
    content: "✓";
    position: absolute;
    left: 0;
    color: var(--green);
    font-weight: bold;
}

.contact-info {
    background: linear-gradient(135deg, var(--green) 0%, #1e4d28 100%);
    padding: 24px;
    border-radius: 8px;
    color: white;
}

.contact-info h3 {
    color: white;
    margin-bottom: 16px;
}

.contact-item {
    display: flex;
    align-items: center;
    gap: 12px;
    margin-bottom: 12px;
}

.contact-item a {
    color: white;
    text-decoration: underline;
}

.no-activities {
    text-align: center;
    padding: 60px 20px;
    background: white;
    border-radius: 12px;
    box-shadow: var(--card-shadow);
}

.no-activities h2 {
    color: var(--brown);
    margin-bottom: 16px;
}

.no-activities p {
    color: #666;
    margin-bottom: 24px;
}

/* Responsive */
@media (max-width: 768px) {
    .page-hero h1 {
        font-size: 32px;
    }
    
    .activities-grid {
        grid-template-columns: 1fr;
    }
    
    .activity-card-list {
        grid-template-columns: 1fr;
    }
    
    .activity-card-list .activity-image {
        height: 200px;
    }
    
    .filters-grid {
        grid-template-columns: 1fr;
    }
    
    .category-pills {
        justify-content: flex-start;
    }
    
    .modal-body {
        padding: 20px;
    }
    
    .modal-meta-grid {
        grid-template-columns: 1fr;
    }
}
</style>

<!-- Page Hero -->
<section class="page-hero">
    <div class="container">
        <h1>Activities & Things to Do</h1>
        <p>Discover the best adventures, cultural experiences, and outdoor activities in Matatiele</p>
    </div>
</section>

<main class="container">
    
    <!-- Filters Section -->
    <section class="filters-section">
        <h2 style="margin-bottom: 8px; color: var(--brown);">Find Your Perfect Activity</h2>
        <form method="GET" action="activities.php">
            <div class="filters-grid">
                <div class="filter-group">
                    <label for="category">Category</label>
                    <select name="category" id="category">
                        <option value="all" <?= $category_filter === 'all' ? 'selected' : '' ?>>All Categories</option>
                        <?php foreach($categories as $cat): ?>
                            <option value="<?= htmlspecialchars($cat) ?>" <?= $category_filter === $cat ? 'selected' : '' ?>>
                                <?= htmlspecialchars(ucfirst($cat)) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="filter-group">
                    <label for="difficulty">Difficulty</label>
                    <select name="difficulty" id="difficulty">
                        <option value="all" <?= $difficulty_filter === 'all' ? 'selected' : '' ?>>All Levels</option>
                        <?php foreach($difficulties as $diff): ?>
                            <option value="<?= htmlspecialchars($diff) ?>" <?= $difficulty_filter === $diff ? 'selected' : '' ?>>
                                <?= htmlspecialchars($diff) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="filter-group">
                    <label for="search">Search</label>
                    <input type="text" name="search" id="search" placeholder="Search activities..." value="<?= htmlspecialchars($search_query) ?>">
                </div>
            </div>
            
            <div class="filter-buttons">
                <button type="submit" class="btn-filter">Apply Filters</button>
                <a href="activities.php" class="btn-filter btn-reset">Reset</a>
            </div>
        </form>
    </section>
    
    <!-- Activities Header -->
    <div class="activities-header">
        <div class="activities-count">
            <?= count($activities) ?> <?= count($activities) === 1 ? 'Activity' : 'Activities' ?> Found
        </div>
    </div>
    
    <!-- Activities Grid -->
    <?php if (!empty($activities)): ?>
        <div class="activities-grid" id="activitiesContainer">
            <?php foreach($activities as $activity): 
                $image_path = "images/activities/" . (!empty($activity['image']) ? basename($activity['image']) : 'default.jpg');
                $difficulty_class = strtolower(str_replace(' ', '-', $activity['difficulty'] ?? 'moderate'));
            ?>
                <article class="activity-card" data-activity-id="<?= $activity['id'] ?>">
                    <?php if ($activity['featured'] == 1): ?>
                        <div class="activity-featured-badge">⭐ Featured</div>
                    <?php endif; ?>
                    
                    <div style="position: relative;">
                        <img src="<?= htmlspecialchars($image_path) ?>" 
                             alt="<?= htmlspecialchars($activity['name']) ?>" 
                             class="activity-image"
                             loading="lazy">
                    </div>
                    
                    <div class="activity-content">
                        <div class="activity-header">
                            <h3 class="activity-title"><?= htmlspecialchars($activity['name']) ?></h3>
                            <?php if (!empty($activity['category'])): ?>
                                <span class="activity-category"><?= htmlspecialchars(ucfirst($activity['category'])) ?></span>
                            <?php endif; ?>
                        </div>
                        
                        <p class="activity-description">
                            <?= htmlspecialchars(substr($activity['description'], 0, 120)) ?>...
                        </p>
                        
                        <div class="activity-meta">
                            <?php if (!empty($activity['duration'])): ?>
                                <div class="meta-item">
                                    <span class="meta-icon">⏱️</span>
                                    <span><?= htmlspecialchars($activity['duration']) ?></span>
                                </div>
                            <?php endif; ?>
                            
                            <?php if (!empty($activity['difficulty'])): ?>
                                <div class="meta-item">
                                    <span class="meta-icon">📊</span>
                                    <span class="difficulty-badge difficulty-<?= $difficulty_class ?>">
                                        <?= htmlspecialchars($activity['difficulty']) ?>
                                    </span>
                                </div>
                            <?php endif; ?>
                            
                            <?php if (!empty($activity['season'])): ?>
                                <div class="meta-item">
                                    <span class="meta-icon">🌤️</span>
                                    <span><?= htmlspecialchars($activity['season']) ?></span>
                                </div>
                            <?php endif; ?>
                            
                            <?php if (!empty($activity['rating'])): ?>
                                <div class="meta-item">
                                    <span class="meta-icon">⭐</span>
                                    <span><?= htmlspecialchars($activity['rating']) ?> / 5</span>
                                </div>
                            <?php endif; ?>
                        </div>
                        
                        <div class="activity-footer">
                            <?php if (!empty($activity['price'])): ?>
                                <div class="activity-price"><?= htmlspecialchars($activity['price']) ?></div>
                            <?php else: ?>
                                <div class="activity-price">Free</div>
                            <?php endif; ?>
                            <a href="#" class="btn-details" onclick="openActivityModal(<?= $activity['id'] ?>); return false;">
                                View Details
                            </a>
                        </div>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div class="no-activities">
            <h2>No Activities Found</h2>
            <p>Try adjusting your filters or search terms to find more activities.</p>
            <a href="activities.php" class="btn-filter">View All Activities</a>
        </div>
    <?php endif; ?>
    
</main>

<!-- Activity Modal -->
<div id="activityModal" class="activity-modal">
    <div class="modal-content">
        <span class="modal-close" onclick="closeActivityModal()">&times;</span>
        <div id="modalContent">
            <!-- Content will be loaded dynamically -->
        </div>
    </div>
</div>

<script>
// Activity data for modal
const activitiesData = <?= json_encode($activities) ?>;

function openActivityModal(activityId) {
    const activity = activitiesData.find(a => a.id == activityId);
    if (!activity) return;
    
    const imagePath = "images/activities/" + (activity.image ? activity.image.split('/').pop() : 'default.jpg');
    const difficultyClass = (activity.difficulty || 'moderate').toLowerCase().replace(/\s+/g, '-');
    
    // Build includes list
    let includesList = '';
    if (activity.includes) {
        const includes = activity.includes.split(',');
        includesList = includes.map(item => `<li>${item.trim()}</li>`).join('');
    }
    
    const modalHTML = `
        <div class="modal-header">
            <img src="${imagePath}" alt="${activity.name}">
        </div>
        <div class="modal-body">
            <h2 class="modal-title">${activity.name}</h2>
            
            ${activity.category ? `<span class="activity-category">${activity.category.charAt(0).toUpperCase() + activity.category.slice(1)}</span>` : ''}
            
            <div class="modal-meta-grid">
                ${activity.duration ? `
                    <div class="meta-item">
                        <span class="meta-icon">⏱️</span>
                        <div>
                            <div style="font-size: 12px; color: #999;">Duration</div>
                            <div class="meta-label">${activity.duration}</div>
                        </div>
                    </div>
                ` : ''}
                
                ${activity.difficulty ? `
                    <div class="meta-item">
                        <span class="meta-icon">📊</span>
                        <div>
                            <div style="font-size: 12px; color: #999;">Difficulty</div>
                            <span class="difficulty-badge difficulty-${difficultyClass}">${activity.difficulty}</span>
                        </div>
                    </div>
                ` : ''}
                
                ${activity.price ? `
                    <div class="meta-item">
                        <span class="meta-icon">💰</span>
                        <div>
                            <div style="font-size: 12px; color: #999;">Price Range</div>
                            <div class="meta-label">${activity.price}</div>
                        </div>
                    </div>
                ` : ''}
                
                ${activity.season ? `
                    <div class="meta-item">
                        <span class="meta-icon">🌤️</span>
                        <div>
                            <div style="font-size: 12px; color: #999;">Best Season</div>
                            <div class="meta-label">${activity.season}</div>
                        </div>
                    </div>
                ` : ''}
                
                ${activity.location ? `
                    <div class="meta-item">
                        <span class="meta-icon">📍</span>
                        <div>
                            <div style="font-size: 12px; color: #999;">Location</div>
                            <div class="meta-label">${activity.location}</div>
                        </div>
                    </div>
                ` : ''}
                
                ${activity.rating ? `
                    <div class="meta-item">
                        <span class="meta-icon">⭐</span>
                        <div>
                            <div style="font-size: 12px; color: #999;">Rating</div>
                            <div class="meta-label">${activity.rating} / 5.0</div>
                        </div>
                    </div>
                ` : ''}
            </div>
            
            <div class="modal-section">
                <h3>About This Activity</h3>
                <p>${activity.full_description || activity.description}</p>
            </div>
            
            ${includesList ? `
                <div class="modal-section">
                    <h3>What's Included</h3>
                    <ul class="includes-list">
                        ${includesList}
                    </ul>
                </div>
            ` : ''}
            
            <div class="contact-info">
                <h3>Contact & Booking</h3>
                ${activity.contact ? `
                    <div class="contact-item">
                        <span class="meta-icon">📞</span>
                        <a href="tel:${activity.contact}">${activity.contact}</a>
                    </div>
                ` : ''}
                <div class="contact-item">
                    <span class="meta-icon">✉️</span>
                    <a href="mailto:info@matatielemtourism.co.za">info@matatielemtourism.co.za</a>
                </div>
                ${activity.website ? `
                    <div class="contact-item">
                        <span class="meta-icon">🌐</span>
                        <a href="${activity.website}" target="_blank">Visit Website</a>
                    </div>
                ` : ''}
            </div>
        </div>
    `;
    
    document.getElementById('modalContent').innerHTML = modalHTML;
    document.getElementById('activityModal').style.display = 'block';
    document.body.style.overflow = 'hidden';
}

function closeActivityModal() {
    document.getElementById('activityModal').style.display = 'none';
    document.body.style.overflow = 'auto';
}

// Close modal when clicking outside
window.onclick = function(event) {
    const modal = document.getElementById('activityModal');
    if (event.target == modal) {
        closeActivityModal();
    }
}

// Close modal on escape key
document.addEventListener('keydown', function(event) {
    if (event.key === 'Escape') {
        closeActivityModal();
    }
});
</script>

<?php include 'footer.php'; ?>