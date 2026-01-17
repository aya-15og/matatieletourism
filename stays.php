<?php
$page_title = "Where to Stay";
$hero_folder = "images/hero/stays/";
$hero_heading = "Discover Your Perfect Getaway";
$hero_text = "Handpicked accommodation in the heart of the Eastern Cape mountains";

include 'header.php';
require 'includes/config.php';

// Fetch stays with all details
$stmt = $pdo->query("SELECT * FROM stays ORDER BY featured DESC, sort_order ASC");
$stays = $stmt->fetchAll(PDO::FETCH_ASSOC);

function get_stay_image_path($image) {
    if (empty($image)) {
        return 'images/placeholder.jpg';
    }
    $filename = basename($image);
    return 'images/stays/' . $filename;
}

function format_price($price) {
    return $price ? 'R ' . number_format($price, 2) : 'Contact for pricing';
}
?>

<main class="stays-main">
    <div class="container">
        <!-- Header Section -->
        <section class="stays-header">
            <h1 class="section-title">Accommodation in Matatiele</h1>
            <p class="section-subtitle">Experience authentic Eastern Cape hospitality in carefully selected hotels, guesthouses, and lodges nestled in breathtaking mountain landscapes</p>
        </section>

        <!-- Filter Section -->
        <div class="filter-section">
            <div class="search-wrapper">
                <svg class="search-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="11" cy="11" r="8"></circle>
                    <path d="m21 21-4.35-4.35"></path>
                </svg>
                <input type="text" id="searchBar" placeholder="Search by name or location..." class="search-input">
            </div>
            
            <div class="filters-row">
                <select id="categoryFilter" class="filter-select">
                    <option value="all">All Categories</option>
                    <option value="hotel">Hotels</option>
                    <option value="guesthouse">Guesthouses</option>
                    <option value="bnb">B&Bs</option>
                    <option value="cottage">Cottages</option>
                    <option value="farmstay">Farmstays</option>
                    <option value="self-catering">Self-Catering</option>
                </select>

                <select id="priceFilter" class="filter-select">
                    <option value="all">All Prices</option>
                    <option value="budget">Budget (Under R500)</option>
                    <option value="mid">Mid-range (R500 - R1000)</option>
                    <option value="luxury">Luxury (R1000+)</option>
                </select>

                <button id="resetFilters" class="reset-btn">Reset Filters</button>
            </div>
        </div>

        <!-- Results Count -->
        <div class="results-info">
            <span id="resultsCount"><?= count($stays) ?></span> accommodation options available
        </div>

        <!-- Stays Grid -->
        <div class="stays-grid" id="staysList">
            <?php foreach ($stays as $index => $stay): ?>
            <article class="stay-card <?= $index >= 9 ? 'hidden' : '' ?>" 
                     data-category="<?= htmlspecialchars($stay['category']) ?>" 
                     data-price="<?= htmlspecialchars($stay['price_per_night'] ?? 0) ?>"
                     data-id="<?= htmlspecialchars($stay['id']) ?>">
                
                <div class="card-image-container">
                    <img src="<?= htmlspecialchars(get_stay_image_path($stay['image'])) ?>" 
                         alt="<?= htmlspecialchars($stay['name']) ?>"
                         class="card-image"
                         onerror="handleImageError(this)">
                    
                    <?php if ($stay['featured']): ?>
                        <span class="featured-badge">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor">
                                <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
                            </svg>
                            Featured
                        </span>
                    <?php endif; ?>
                    
                    <span class="category-badge"><?= ucfirst($stay['category'] ?? 'Other') ?></span>
                    
                    <?php if ($stay['is_partner']): ?>
                        <span class="partner-badge" title="Book instantly with instant confirmation">
                            <svg width="12" height="12" viewBox="0 0 24 24" fill="currentColor">
                                <path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41z"/>
                            </svg>
                            Instant Booking
                        </span>
                    <?php endif; ?>
                </div>

                <div class="card-content">
                    <div class="card-header">
                        <h3 class="card-title"><?= htmlspecialchars($stay['name']) ?></h3>
                        <div class="card-location">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path>
                                <circle cx="12" cy="10" r="3"></circle>
                            </svg>
                            <?= htmlspecialchars($stay['location']) ?>
                        </div>
                    </div>

                    <p class="card-description"><?= htmlspecialchars(substr($stay['description'], 0, 120)) ?>...</p>

                    <?php if (!empty($stay['amenities'])): 
                        $amenities = array_slice(explode(',', $stay['amenities']), 0, 3);
                    ?>
                    <div class="amenities-preview">
                        <?php foreach ($amenities as $amenity): ?>
                            <span class="amenity-tag"><?= htmlspecialchars(trim($amenity)) ?></span>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>

                    <div class="card-footer">
                        <div class="price-section">
                            <?php if ($stay['price_per_night']): ?>
                                <span class="price"><?= format_price($stay['price_per_night']) ?></span>
                                <span class="price-label">per night</span>
                            <?php else: ?>
                                <span class="price-contact">Contact for rates</span>
                            <?php endif; ?>
                        </div>
                        
                        <a href="booking.php?stay_id=<?= $stay['id'] ?>" class="book-btn">
                            <?= $stay['is_partner'] ? 'Book Now' : 'Send Inquiry' ?>
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <line x1="5" y1="12" x2="19" y2="12"></line>
                                <polyline points="12 5 19 12 12 19"></polyline>
                            </svg>
                        </a>
                    </div>
                </div>
            </article>
            <?php endforeach; ?>
        </div>

        <!-- View More Button -->
        <?php if (count($stays) > 9): ?>
        <div class="view-more-container">
            <button id="viewMoreBtn" class="view-more-btn">
                Show More Accommodations
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <polyline points="6 9 12 15 18 9"></polyline>
                </svg>
            </button>
        </div>
        <?php endif; ?>

        <!-- Empty State -->
        <div id="emptyState" class="empty-state" style="display: none;">
            <svg width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                <circle cx="11" cy="11" r="8"></circle>
                <path d="m21 21-4.35-4.35"></path>
            </svg>
            <h3>No accommodations found</h3>
            <p>Try adjusting your filters or search terms</p>
        </div>
    </div>
</main>

<style>
:root {
    --primary-color: #2563eb;
    --primary-dark: #1e40af;
    --secondary-color: #0ea5e9;
    --success-color: #10b981;
    --warning-color: #f59e0b;
    --text-dark: #1f2937;
    --text-medium: #6b7280;
    --text-light: #9ca3af;
    --border-color: #e5e7eb;
    --bg-light: #f9fafb;
    --shadow-sm: 0 1px 2px 0 rgb(0 0 0 / 0.05);
    --shadow-md: 0 4px 6px -1px rgb(0 0 0 / 0.1);
    --shadow-lg: 0 10px 15px -3px rgb(0 0 0 / 0.1);
    --shadow-xl: 0 20px 25px -5px rgb(0 0 0 / 0.1);
}

.stays-main {
    padding: 3rem 0 5rem;
    background: linear-gradient(to bottom, #ffffff 0%, var(--bg-light) 100%);
}

/* Header Section */
.stays-header {
    text-align: center;
    margin-bottom: 3rem;
}

.section-title {
    font-size: 2.5rem;
    font-weight: 700;
    color: var(--text-dark);
    margin-bottom: 1rem;
    letter-spacing: -0.025em;
}

.section-subtitle {
    font-size: 1.125rem;
    color: var(--text-medium);
    max-width: 700px;
    margin: 0 auto;
    line-height: 1.7;
}

/* Filter Section */
.filter-section {
    background: white;
    padding: 2rem;
    border-radius: 16px;
    box-shadow: var(--shadow-md);
    margin-bottom: 2.5rem;
}

.search-wrapper {
    position: relative;
    margin-bottom: 1.5rem;
}

.search-icon {
    position: absolute;
    left: 1.25rem;
    top: 50%;
    transform: translateY(-50%);
    color: var(--text-light);
    pointer-events: none;
}

.search-input {
    width: 100%;
    padding: 1rem 1rem 1rem 3rem;
    font-size: 1rem;
    border: 2px solid var(--border-color);
    border-radius: 12px;
    transition: all 0.2s;
    outline: none;
}

.search-input:focus {
    border-color: var(--primary-color);
    box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
}

.filters-row {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 1rem;
}

.filter-select {
    padding: 0.875rem 1rem;
    font-size: 0.95rem;
    border: 2px solid var(--border-color);
    border-radius: 12px;
    background: white;
    cursor: pointer;
    transition: all 0.2s;
    outline: none;
}

.filter-select:hover {
    border-color: var(--text-medium);
}

.filter-select:focus {
    border-color: var(--primary-color);
    box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
}

.reset-btn {
    padding: 0.875rem 1.5rem;
    background: var(--bg-light);
    border: 2px solid var(--border-color);
    border-radius: 12px;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.2s;
    color: var(--text-medium);
}

.reset-btn:hover {
    background: white;
    border-color: var(--text-medium);
    color: var(--text-dark);
}

/* Results Info */
.results-info {
    margin-bottom: 2rem;
    color: var(--text-medium);
    font-size: 0.95rem;
}

#resultsCount {
    font-weight: 600;
    color: var(--primary-color);
}

/* Stays Grid */
.stays-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
    gap: 2rem;
    margin-bottom: 3rem;
}

.stay-card {
    background: white;
    border-radius: 16px;
    overflow: hidden;
    box-shadow: var(--shadow-md);
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    display: flex;
    flex-direction: column;
}

.stay-card:hover {
    transform: translateY(-8px);
    box-shadow: var(--shadow-xl);
}

.stay-card.hidden {
    display: none;
}

.stay-card.fade-in {
    animation: fadeInUp 0.5s ease-out forwards;
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

/* Card Image */
.card-image-container {
    position: relative;
    height: 240px;
    overflow: hidden;
    background: var(--bg-light);
}

.card-image {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.4s ease;
}

.stay-card:hover .card-image {
    transform: scale(1.08);
}

.card-image.image-error {
    opacity: 0.3;
}

/* Badges */
.featured-badge, .category-badge, .partner-badge {
    position: absolute;
    padding: 0.4rem 0.875rem;
    border-radius: 20px;
    font-size: 0.75rem;
    font-weight: 600;
    display: flex;
    align-items: center;
    gap: 0.35rem;
    backdrop-filter: blur(8px);
}

.featured-badge {
    top: 12px;
    left: 12px;
    background: rgba(245, 158, 11, 0.95);
    color: white;
    box-shadow: var(--shadow-md);
}

.category-badge {
    bottom: 12px;
    right: 12px;
    background: rgba(37, 99, 235, 0.95);
    color: white;
    box-shadow: var(--shadow-md);
}

.partner-badge {
    top: 12px;
    right: 12px;
    background: rgba(16, 185, 129, 0.95);
    color: white;
    box-shadow: var(--shadow-md);
}

/* Card Content */
.card-content {
    padding: 1.5rem;
    display: flex;
    flex-direction: column;
    flex-grow: 1;
}

.card-header {
    margin-bottom: 1rem;
}

.card-title {
    font-size: 1.375rem;
    font-weight: 700;
    color: var(--text-dark);
    margin-bottom: 0.5rem;
    line-height: 1.3;
}

.card-location {
    display: flex;
    align-items: center;
    gap: 0.4rem;
    color: var(--text-medium);
    font-size: 0.9rem;
}

.card-description {
    color: var(--text-medium);
    line-height: 1.6;
    margin-bottom: 1rem;
    flex-grow: 1;
}

/* Amenities */
.amenities-preview {
    display: flex;
    flex-wrap: wrap;
    gap: 0.5rem;
    margin-bottom: 1.25rem;
}

.amenity-tag {
    padding: 0.35rem 0.75rem;
    background: var(--bg-light);
    border-radius: 8px;
    font-size: 0.8rem;
    color: var(--text-medium);
    font-weight: 500;
}

/* Card Footer */
.card-footer {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding-top: 1.25rem;
    border-top: 1px solid var(--border-color);
    margin-top: auto;
}

.price-section {
    display: flex;
    flex-direction: column;
}

.price {
    font-size: 1.5rem;
    font-weight: 700;
    color: var(--text-dark);
    line-height: 1;
}

.price-label {
    font-size: 0.8rem;
    color: var(--text-light);
    margin-top: 0.25rem;
}

.price-contact {
    font-size: 0.95rem;
    color: var(--text-medium);
    font-weight: 500;
}

.book-btn {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.75rem 1.5rem;
    background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
    color: white;
    font-weight: 600;
    border-radius: 10px;
    text-decoration: none;
    transition: all 0.3s;
    box-shadow: 0 4px 12px rgba(37, 99, 235, 0.3);
}

.book-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(37, 99, 235, 0.4);
}

/* View More Button */
.view-more-container {
    text-align: center;
    margin-top: 3rem;
}

.view-more-btn {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 1rem 2.5rem;
    background: white;
    color: var(--primary-color);
    border: 2px solid var(--primary-color);
    border-radius: 12px;
    font-weight: 600;
    font-size: 1rem;
    cursor: pointer;
    transition: all 0.3s;
}

.view-more-btn:hover {
    background: var(--primary-color);
    color: white;
    transform: translateY(-2px);
    box-shadow: var(--shadow-lg);
}

/* Empty State */
.empty-state {
    text-align: center;
    padding: 4rem 2rem;
}

.empty-state svg {
    color: var(--text-light);
    margin-bottom: 1rem;
}

.empty-state h3 {
    font-size: 1.5rem;
    color: var(--text-dark);
    margin-bottom: 0.5rem;
}

.empty-state p {
    color: var(--text-medium);
}

/* Responsive */
@media (max-width: 768px) {
    .section-title {
        font-size: 2rem;
    }
    
    .stays-grid {
        grid-template-columns: 1fr;
        gap: 1.5rem;
    }
    
    .filter-section {
        padding: 1.5rem;
    }
    
    .filters-row {
        grid-template-columns: 1fr;
    }
}
</style>

<script>
function handleImageError(img) {
    img.onerror = null;
    if (!img.src.includes('placeholder.jpg')) {
        img.src = 'images/placeholder.jpg';
    } else {
        img.classList.add('image-error');
    }
}

function updateResultsCount() {
    const visibleCards = document.querySelectorAll('.stay-card:not([style*="display: none"])').length;
    const hiddenCards = document.querySelectorAll('.stay-card.hidden').length;
    const totalVisible = visibleCards - hiddenCards;
    document.getElementById('resultsCount').textContent = totalVisible;
    
    const emptyState = document.getElementById('emptyState');
    const staysList = document.getElementById('staysList');
    
    if (totalVisible === 0) {
        emptyState.style.display = 'block';
        staysList.style.display = 'none';
    } else {
        emptyState.style.display = 'none';
        staysList.style.display = 'grid';
    }
}

function filterCards() {
    const search = document.getElementById('searchBar').value.toLowerCase();
    const category = document.getElementById('categoryFilter').value;
    const priceRange = document.getElementById('priceFilter').value;
    const cards = document.querySelectorAll('.stay-card');

    cards.forEach(card => {
        const title = card.querySelector('.card-title').innerText.toLowerCase();
        const location = card.querySelector('.card-location').innerText.toLowerCase();
        const cat = card.dataset.category.toLowerCase();
        const price = parseFloat(card.dataset.price) || 0;

        const matchSearch = title.includes(search) || location.includes(search);
        const matchCat = category === 'all' || cat === category;
        
        let matchPrice = true;
        if (priceRange === 'budget') matchPrice = price < 500 || price === 0;
        else if (priceRange === 'mid') matchPrice = price >= 500 && price <= 1000;
        else if (priceRange === 'luxury') matchPrice = price > 1000;

        card.style.display = (matchSearch && matchCat && matchPrice) ? 'flex' : 'none';
    });

    updateResultsCount();
}

document.getElementById('searchBar').addEventListener('input', filterCards);
document.getElementById('categoryFilter').addEventListener('change', filterCards);
document.getElementById('priceFilter').addEventListener('change', filterCards);

document.getElementById('resetFilters').addEventListener('click', () => {
    document.getElementById('searchBar').value = '';
    document.getElementById('categoryFilter').value = 'all';
    document.getElementById('priceFilter').value = 'all';
    filterCards();
});

const viewMoreBtn = document.getElementById('viewMoreBtn');
if (viewMoreBtn) {
    viewMoreBtn.addEventListener('click', () => {
        const hiddenCards = document.querySelectorAll('.stay-card.hidden');
        const step = 6;
        let count = 0;
        
        hiddenCards.forEach(card => {
            if (count < step) {
                card.classList.remove('hidden');
                card.classList.add('fade-in');
                count++;
            }
        });

        if (!document.querySelector('.stay-card.hidden')) {
            viewMoreBtn.style.display = 'none';
        }
        
        updateResultsCount();
    });
}

updateResultsCount();
</script>

<?php include 'footer.php'; ?>