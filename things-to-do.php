<?php
$page_title = "Things to Do - Activities in Matatiele";
$hero_folder = "images/hero/activities/";
$hero_heading = "Activities & Adventures Year-Round";
$hero_text = "From outdoor adventures to cultural celebrations, discover exciting activities in Matatiele throughout the year.";

include 'header.php';
require 'includes/config.php';

// Get filter parameters
$category_filter = $_GET['category'] ?? 'all';
$difficulty_filter = $_GET['difficulty'] ?? 'all';
$search_query = $_GET['search'] ?? '';

// Fetch activities with images
$sql = "SELECT * FROM activities WHERE image IS NOT NULL AND image != '' AND visible=1";
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

$sql .= " ORDER BY featured DESC, sort_order ASC, name ASC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$activities = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<main class="container">
  <section class="activities-section">
    <h2>Activities & Adventures in Matatiele</h2>
    <p>Experience adventure, culture, and community spirit through year-round activities in Matatiele and surrounding areas. From mountain hiking to cultural tours, there's something for everyone.</p>

    <!-- Enhanced Filter Bar -->
    <div class="filter-bar">
      <input type="text" 
             id="searchBar" 
             placeholder="Search activities..." 
             value="<?= htmlspecialchars($search_query) ?>"
             class="search-input">
      
      <select id="categoryFilter" class="filter-select">
        <option value="all" <?= $category_filter === 'all' ? 'selected' : '' ?>>All Categories</option>
        <option value="outdoor" <?= $category_filter === 'outdoor' ? 'selected' : '' ?>>Outdoor Adventures</option>
        <option value="adventure" <?= $category_filter === 'adventure' ? 'selected' : '' ?>>Adventure</option>
        <option value="nature" <?= $category_filter === 'nature' ? 'selected' : '' ?>>Nature & Wildlife</option>
        <option value="cultural" <?= $category_filter === 'cultural' ? 'selected' : '' ?>>Cultural & Community</option>
        <option value="sports" <?= $category_filter === 'sports' ? 'selected' : '' ?>>Sports & Recreation</option>
        <option value="family" <?= $category_filter === 'family' ? 'selected' : '' ?>>Family Activities</option>
      </select>

      <select id="difficultyFilter" class="filter-select">
        <option value="all" <?= $difficulty_filter === 'all' ? 'selected' : '' ?>>All Difficulty Levels</option>
        <option value="Easy" <?= $difficulty_filter === 'Easy' ? 'selected' : '' ?>>Easy</option>
        <option value="Moderate" <?= $difficulty_filter === 'Moderate' ? 'selected' : '' ?>>Moderate</option>
        <option value="Challenging" <?= $difficulty_filter === 'Challenging' ? 'selected' : '' ?>>Challenging</option>
      </select>

      <button onclick="applyFilters()" class="filter-btn">Apply</button>
      <button onclick="resetFilters()" class="filter-btn reset-btn">Reset</button>
    </div>

    <!-- Results Count -->
    <div class="results-info">
      <p><?= count($activities) ?> <?= count($activities) === 1 ? 'activity' : 'activities' ?> found</p>
    </div>

    <!-- Activities Grid -->
    <div class="card-grid" id="activitiesList">
      <?php 
      $labels = [
        'outdoor' => 'Outdoor',
        'adventure' => 'Adventure', 
        'nature' => 'Nature',
        'cultural' => 'Cultural',
        'seasonal' => 'Seasonal',
        'sports' => 'Sports',
        'family' => 'Family'
      ];
      
      foreach ($activities as $index => $activity): 
        if (empty($activity['image'])) continue;
        
        $difficulty_class = !empty($activity['difficulty']) ? 'difficulty-' . strtolower($activity['difficulty']) : '';
      ?>
        <div class="card activity-card <?= $index >= 6 ? 'hidden' : '' ?>" 
             data-category="<?= htmlspecialchars($activity['category']) ?>"
             data-activity-id="<?= $activity['id'] ?>">
          
          <div class="img-container">
            <img src="<?= htmlspecialchars($activity['image']) ?>" 
                 alt="<?= htmlspecialchars($activity['name']) ?>"
                 loading="lazy">
            
            <!-- Featured Badge -->
            <?php if ($activity['featured'] == 1): ?>
              <span class="featured-badge">⭐ Featured</span>
            <?php endif; ?>
            
            <!-- Category Label -->
            <span class="category-label"><?= $labels[$activity['category']] ?? 'Other' ?></span>
          </div>
          
          <div class="content">
            <h3><?= htmlspecialchars($activity['name']) ?></h3>
            <p class="desc"><?= htmlspecialchars($activity['description']) ?></p>
            
            <!-- Activity Meta Info -->
            <div class="activity-meta">
              <?php if (!empty($activity['duration'])): ?>
                <div class="meta-item">
                  <span class="icon">⏱️</span>
                  <span><?= htmlspecialchars($activity['duration']) ?></span>
                </div>
              <?php endif; ?>
              
              <?php if (!empty($activity['difficulty'])): ?>
                <div class="meta-item">
                  <span class="icon">📊</span>
                  <span class="difficulty-badge <?= $difficulty_class ?>">
                    <?= htmlspecialchars($activity['difficulty']) ?>
                  </span>
                </div>
              <?php endif; ?>
              
              <?php if (!empty($activity['price'])): ?>
                <div class="meta-item price-item">
                  <span class="icon">💰</span>
                  <span class="price"><?= htmlspecialchars($activity['price']) ?></span>
                </div>
              <?php endif; ?>
              
              <?php if (!empty($activity['season'])): ?>
                <div class="meta-item">
                  <span class="icon">🌤️</span>
                  <span><?= htmlspecialchars($activity['season']) ?></span>
                </div>
              <?php endif; ?>
              
              <?php if (!empty($activity['rating'])): ?>
                <div class="meta-item">
                  <span class="icon">⭐</span>
                  <span><?= htmlspecialchars($activity['rating']) ?> / 5</span>
                </div>
              <?php endif; ?>
            </div>
            
            <button onclick="openActivityModal(<?= $activity['id'] ?>)" class="details-btn">
              View Details
            </button>
          </div>
        </div>
      <?php endforeach; ?>
    </div>

    <?php if (empty($activities)): ?>
      <div class="no-results">
        <h3>No activities found</h3>
        <p>Try adjusting your filters or search terms.</p>
        <button onclick="resetFilters()" class="filter-btn">Reset Filters</button>
      </div>
    <?php endif; ?>

    <!-- View More Button -->
    <?php if (count($activities) > 6): ?>
      <div class="view-more-container">
        <button id="viewMoreBtn" onclick="showMore()" class="view-more-btn">
          View More Activities
        </button>
      </div>
    <?php endif; ?>
  </section>
</main>

<!-- Activity Details Modal -->
<div id="activityModal" class="activity-modal">
  <div class="modal-content">
    <span class="modal-close" onclick="closeActivityModal()">&times;</span>
    <div id="modalContent">
      <!-- Content loaded dynamically -->
    </div>
  </div>
</div>

<style>
/* Activities Section */
.activities-section { padding: 3rem 0; }
.activities-section h2 { 
  text-align: center; 
  font-size: 2rem; 
  margin-bottom: .5rem; 
  color: var(--brown);
}
.activities-section > p { 
  text-align: center; 
  color: #555; 
  max-width: 700px; 
  margin: 0 auto 2rem;
  line-height: 1.6;
}

/* Filter Bar */
.filter-bar { 
  display: flex; 
  justify-content: center; 
  flex-wrap: wrap; 
  gap: 1rem; 
  margin-bottom: 1.5rem;
  background: white;
  padding: 1.5rem;
  border-radius: 12px;
  box-shadow: 0 2px 8px rgba(0,0,0,0.08);
}

.search-input, .filter-select {
  padding: .8rem 1rem; 
  border-radius: 8px; 
  border: 2px solid #e0e0e0; 
  font-size: .95rem; 
  outline: none;
  font-family: 'Poppins', sans-serif;
  transition: border-color 0.3s;
}

.search-input:focus, .filter-select:focus {
  border-color: var(--green);
}

.search-input {
  min-width: 250px;
}

.filter-btn {
  padding: .8rem 1.5rem;
  border: none;
  border-radius: 8px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.3s;
  background: var(--green);
  color: white;
  font-family: 'Poppins', sans-serif;
}

.filter-btn:hover {
  background: var(--gold);
  color: var(--brown);
  transform: translateY(-2px);
}

.reset-btn {
  background: transparent;
  border: 2px solid var(--green);
  color: var(--green);
}

.reset-btn:hover {
  background: var(--green);
  color: white;
}

/* Results Info */
.results-info {
  text-align: center;
  margin-bottom: 1.5rem;
  color: #666;
  font-weight: 500;
}

/* Card Grid */
.card-grid { 
  display: grid; 
  grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); 
  gap: 2rem;
  margin-bottom: 2rem;
}

.activity-card {
  border-radius: 12px; 
  overflow: hidden; 
  background: #fff;
  box-shadow: var(--card-shadow);
  transition: all .3s ease; 
  opacity: 1;
  position: relative;
}

.activity-card:hover {
  transform: translateY(-5px);
  box-shadow: 0 12px 30px rgba(0,0,0,0.16);
}

.activity-card.hidden { 
  display: none; 
}

.activity-card.fade-in { 
  animation: fadeIn 0.6s ease-in-out forwards; 
}

@keyframes fadeIn {
  from {opacity: 0; transform: translateY(10px);}
  to {opacity: 1; transform: translateY(0);}
}

/* Image Container */
.img-container { 
  position: relative; 
  height: 200px;
  overflow: hidden;
}

.img-container img { 
  width: 100%; 
  height: 100%; 
  object-fit: cover; 
  display: block;
  transition: transform 0.3s;
}

.activity-card:hover .img-container img {
  transform: scale(1.05);
}

.featured-badge {
  position: absolute;
  top: 10px;
  left: 10px;
  background: var(--gold);
  color: white;
  padding: .4rem .8rem;
  border-radius: 20px;
  font-size: .75rem;
  font-weight: 600;
  box-shadow: 0 2px 8px rgba(0,0,0,0.2);
  z-index: 2;
}

.category-label {
  position: absolute; 
  bottom: 10px; 
  right: 10px;
  background: rgba(46, 111, 58, 0.9);
  color: #fff;
  padding: .4rem .9rem;
  border-radius: 12px;
  font-size: .75rem;
  font-weight: 600;
  box-shadow: 0 2px 6px rgba(0,0,0,0.3);
  z-index: 2;
}

/* Card Content */
.activity-card .content { 
  padding: 1.3rem; 
}

.activity-card h3 { 
  margin: 0 0 .8rem 0; 
  color: var(--brown);
  font-size: 1.2rem;
  line-height: 1.3;
}

.activity-card .desc { 
  color: #666; 
  font-size: .95rem;
  line-height: 1.5;
  margin-bottom: 1rem;
}

/* Activity Meta */
.activity-meta {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(140px, 1fr));
  gap: .8rem;
  margin-bottom: 1rem;
  padding: 1rem;
  background: #f8f9fa;
  border-radius: 8px;
}

.meta-item {
  display: flex;
  align-items: center;
  gap: .5rem;
  font-size: .85rem;
  color: #555;
}

.meta-item .icon {
  font-size: 1.1rem;
}

.price-item {
  grid-column: 1 / -1;
}

.price {
  font-weight: 700;
  color: var(--green);
  font-size: 1rem;
}

/* Difficulty Badges */
.difficulty-badge {
  display: inline-block;
  padding: .25rem .6rem;
  border-radius: 12px;
  font-size: .75rem;
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

/* Details Button */
.details-btn {
  display: block;
  width: 100%;
  margin-top: 1rem; 
  padding: .7rem 1.2rem;
  background: var(--green);
  color: #fff; 
  font-weight: 600; 
  border: none;
  border-radius: 8px;
  cursor: pointer;
  transition: all .3s;
  font-family: 'Poppins', sans-serif;
}

.details-btn:hover { 
  background: var(--gold);
  color: var(--brown);
  transform: translateY(-2px);
}

/* View More Button */
.view-more-container { 
  text-align: center; 
  margin-top: 2rem; 
}

.view-more-btn {
  padding: 0.9rem 2rem; 
  font-size: 1rem; 
  color: #fff;
  background: var(--green);
  border: none; 
  border-radius: 8px; 
  cursor: pointer; 
  transition: all .3s;
  font-weight: 600;
  box-shadow: 0 4px 12px rgba(46, 111, 58, 0.2);
}

.view-more-btn:hover { 
  background: var(--gold);
  color: var(--brown);
  transform: translateY(-2px);
}

/* No Results */
.no-results {
  text-align: center;
  padding: 3rem 2rem;
  background: white;
  border-radius: 12px;
  box-shadow: var(--card-shadow);
}

.no-results h3 {
  color: var(--brown);
  margin-bottom: 1rem;
}

.no-results p {
  color: #666;
  margin-bottom: 1.5rem;
}

/* Modal Styles */
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

.modal-header-img {
  width: 100%;
  height: 300px;
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

.activity-category {
  display: inline-block;
  background: rgba(46, 111, 58, 0.1);
  color: var(--green);
  padding: 6px 14px;
  border-radius: 12px;
  font-size: 13px;
  font-weight: 600;
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

/* Responsive */
@media (max-width: 768px) {
  .filter-bar {
    flex-direction: column;
    gap: .8rem;
  }
  
  .search-input {
    min-width: 100%;
  }
  
  .card-grid {
    grid-template-columns: 1fr;
    gap: 1.5rem;
  }
  
  .activity-meta {
    grid-template-columns: 1fr;
  }
  
  .modal-body {
    padding: 20px;
  }
  
  .modal-meta-grid {
    grid-template-columns: 1fr;
  }
}
</style>

<script>
const step = 6;

// Activity data for modal
const activitiesData = <?= json_encode($activities) ?>;

function applyFilters() {
  const search = document.getElementById('searchBar').value;
  const category = document.getElementById('categoryFilter').value;
  const difficulty = document.getElementById('difficultyFilter').value;
  
  let url = 'activities.php?';
  const params = [];
  
  if (search) params.push('search=' + encodeURIComponent(search));
  if (category !== 'all') params.push('category=' + encodeURIComponent(category));
  if (difficulty !== 'all') params.push('difficulty=' + encodeURIComponent(difficulty));
  
  window.location.href = url + params.join('&');
}

function resetFilters() {
  window.location.href = 'activities.php';
}

function showMore() {
  const cards = document.querySelectorAll('.activity-card.hidden');
  let count = 0;
  cards.forEach(card => {
    if (count < step) {
      card.classList.remove('hidden');
      card.classList.add('fade-in');
      count++;
    }
  });
  if (document.querySelectorAll('.activity-card.hidden').length === 0) {
    document.getElementById('viewMoreBtn').style.display = 'none';
  }
}

// Modal Functions
function openActivityModal(activityId) {
  const activity = activitiesData.find(a => a.id == activityId);
  if (!activity) return;
  
  const difficultyClass = (activity.difficulty || 'moderate').toLowerCase().replace(/\s+/g, '-');
  
  let includesList = '';
  if (activity.includes) {
    const includes = activity.includes.split(',');
    includesList = includes.map(item => `<li>${item.trim()}</li>`).join('');
  }
  
  const modalHTML = `
    ${activity.image ? `<img src="${activity.image}" alt="${activity.name}" class="modal-header-img">` : ''}
    <div class="modal-body">
      <h2 class="modal-title">${activity.name}</h2>
      
      ${activity.category ? `<span class="activity-category">${activity.category.charAt(0).toUpperCase() + activity.category.slice(1)}</span>` : ''}
      
      <div class="modal-meta-grid">
        ${activity.duration ? `
          <div class="meta-item">
            <span class="icon">⏱️</span>
            <div>
              <div style="font-size: 12px; color: #999;">Duration</div>
              <div style="font-weight: 600;">${activity.duration}</div>
            </div>
          </div>
        ` : ''}
        
        ${activity.difficulty ? `
          <div class="meta-item">
            <span class="icon">📊</span>
            <div>
              <div style="font-size: 12px; color: #999;">Difficulty</div>
              <span class="difficulty-badge difficulty-${difficultyClass}">${activity.difficulty}</span>
            </div>
          </div>
        ` : ''}
        
        ${activity.price ? `
          <div class="meta-item">
            <span class="icon">💰</span>
            <div>
              <div style="font-size: 12px; color: #999;">Price Range</div>
              <div style="font-weight: 700; color: var(--green);">${activity.price}</div>
            </div>
          </div>
        ` : ''}
        
        ${activity.season ? `
          <div class="meta-item">
            <span class="icon">🌤️</span>
            <div>
              <div style="font-size: 12px; color: #999;">Best Season</div>
              <div style="font-weight: 600;">${activity.season}</div>
            </div>
          </div>
        ` : ''}
        
        ${activity.location ? `
          <div class="meta-item">
            <span class="icon">📍</span>
            <div>
              <div style="font-size: 12px; color: #999;">Location</div>
              <div style="font-weight: 600;">${activity.location}</div>
            </div>
          </div>
        ` : ''}
        
        ${activity.rating ? `
          <div class="meta-item">
            <span class="icon">⭐</span>
            <div>
              <div style="font-size: 12px; color: #999;">Rating</div>
              <div style="font-weight: 600;">${activity.rating} / 5.0</div>
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
            <span class="icon">📞</span>
            <a href="tel:${activity.contact}">${activity.contact}</a>
          </div>
        ` : ''}
        <div class="contact-item">
          <span class="icon">✉️</span>
          <a href="mailto:tours@matatiele.co.za">tours@matatiele.co.za</a>
        </div>
        ${activity.website ? `
          <div class="contact-item">
            <span class="icon">🌐</span>
            <a href="${activity.website}" target="_blank" rel="noopener">Visit Website</a>
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

// Close modal on outside click
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

// Enter key search
document.getElementById('searchBar').addEventListener('keypress', function(event) {
  if (event.key === 'Enter') {
    applyFilters();
  }
});
</script>

<?php include 'footer.php'; ?>