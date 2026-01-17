<?php
$page_title = "Matatiele Business Directory";
$hero_folder = "images/hero/business/";
$hero_heading = "Local Business Directory";
$hero_text = "Supporting local businesses and connecting you with quality services in Matatiele";

include 'header.php';
require 'includes/config.php';

// Fetch businesses from database
try {
    $businesses = $pdo->query("SELECT * FROM businesses WHERE visible=1 ORDER BY category ASC, featured DESC, name ASC")->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    // Fallback to empty array if table doesn't exist
    $businesses = [];
    error_log("Businesses query error: " . $e->getMessage());
}

// Get unique categories
$categories = array_unique(array_column($businesses, 'category'));
sort($categories);
?>

<main class="container">
  <section class="directory-intro">
    <h2>Matatiele Business Directory</h2>
    <p class="lead">Your comprehensive guide to local businesses and services in Matatiele.</p>
    <p>Support local! Find essential services, shopping, healthcare, and more. For accommodation options, visit our <a href="stays.php" style="color: #667eea; font-weight: 600;">Where to Stay</a> page. For dining, check out our <a href="things-to-do.php" style="color: #667eea; font-weight: 600;">Things to Do</a> section.</p>
  </section>

  <div class="directory-search">
    <input type="text" id="searchInput" placeholder="Search businesses by name, category, or service..." class="search-box">
    <select id="categoryFilter" class="category-filter">
      <option value="all">All Categories</option>
      <?php foreach($categories as $cat): ?>
        <option value="<?= htmlspecialchars($cat) ?>"><?= htmlspecialchars($cat) ?></option>
      <?php endforeach; ?>
    </select>
  </div>

  <div class="directory-stats">
    <div class="stat-card">
      <div class="stat-number"><?= count($businesses) ?>+</div>
      <div class="stat-label">Local Businesses</div>
    </div>
    <div class="stat-card">
      <div class="stat-number"><?= count($categories) ?></div>
      <div class="stat-label">Categories</div>
    </div>
    <div class="stat-card">
      <div class="stat-number">100%</div>
      <div class="stat-label">Local Support</div>
    </div>
  </div>

  <div class="directory-grid" id="businessList">
    <?php 
    // Group businesses by category
    $grouped = [];
    foreach($businesses as $business) {
        $grouped[$business['category']][] = $business;
    }
    
    foreach($grouped as $category => $items): 
    ?>
      <div class="category-section" data-category="<?= htmlspecialchars($category) ?>">
        <h3 class="category-heading">
          <span class="category-icon">
            <?php
            $icons = [
                'Shopping' => '🛍️',
                'Healthcare' => '⚕️',
                'Funeral Services' => '🕊️',
                'Financial Services' => '💰',
                'Government' => '🏛️',
                'Services' => '⚙️',
                'Fuel' => '⛽',
                'Education' => '📚',
                'Automotive' => '🚗',
                'Professional Services' => '💼',
                'Personal Services' => '💅',
                'Food Services' => '🍰',
                'Entertainment' => '🎭',
                'Agriculture' => '🌾'
            ];
            echo $icons[$category] ?? '📍';
            ?>
          </span>
          <?= htmlspecialchars($category) ?>
        </h3>
        
        <div class="business-cards">
          <?php foreach($items as $business): ?>
            <div class="business-card" data-name="<?= htmlspecialchars($business['name']) ?>" data-category="<?= htmlspecialchars($category) ?>" data-services="<?= htmlspecialchars($business['services'] ?? '') ?>">
              <div class="business-header">
                <h4><?= htmlspecialchars($business['name']) ?></h4>
                <span class="business-type"><?= htmlspecialchars($business['type']) ?></span>
              </div>
              
              <p class="business-description"><?= htmlspecialchars($business['description']) ?></p>
              
              <div class="business-details">
                <?php if(!empty($business['address'])): ?>
                  <div class="detail-item">
                    <span class="detail-icon">📍</span>
                    <span><?= htmlspecialchars($business['address']) ?></span>
                  </div>
                <?php endif; ?>
                
                <?php if(!empty($business['phone'])): ?>
                  <div class="detail-item">
                    <span class="detail-icon">📞</span>
                    <a href="tel:<?= htmlspecialchars($business['phone']) ?>"><?= htmlspecialchars($business['phone']) ?></a>
                  </div>
                <?php endif; ?>
                
                <?php if(!empty($business['website'])): ?>
                  <div class="detail-item">
                    <span class="detail-icon">🌐</span>
                    <a href="https://<?= htmlspecialchars($business['website']) ?>" target="_blank"><?= htmlspecialchars($business['website']) ?></a>
                  </div>
                <?php endif; ?>
              </div>
              
              <?php if(!empty($business['services'])): ?>
                <div class="business-services">
                  <strong>Services:</strong> <?= htmlspecialchars($business['services']) ?>
                </div>
              <?php endif; ?>
            </div>
          <?php endforeach; ?>
        </div>
      </div>
    <?php endforeach; ?>
  </div>

  <div class="no-results" id="noResults" style="display: none;">
    <p>No businesses found matching your search. Try different keywords or select a different category.</p>
  </div>

  <section class="add-business-cta">
    <h3>Is Your Business Missing?</h3>
    <p>Get your business listed in our directory and reach more customers in Matatiele.</p>
    <a href="contact.php?subject=Add Business" class="btn btn-primary">Add Your Business</a>
  </section>
</main>

<style>
.directory-intro {
  text-align: center;
  padding: 3rem 0 2rem;
}

.directory-intro h2 {
  font-size: 2.5rem;
  margin-bottom: 1rem;
  color: #333;
}

.directory-intro .lead {
  font-size: 1.2rem;
  color: #666;
  margin-bottom: 1rem;
}

.directory-search {
  display: flex;
  gap: 1rem;
  max-width: 800px;
  margin: 2rem auto;
  flex-wrap: wrap;
}

.search-box {
  flex: 1;
  min-width: 250px;
  padding: 1rem;
  border: 2px solid #e0e0e0;
  border-radius: 8px;
  font-size: 1rem;
}

.category-filter {
  padding: 1rem;
  border: 2px solid #e0e0e0;
  border-radius: 8px;
  font-size: 1rem;
  min-width: 200px;
}

.directory-stats {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: 2rem;
  max-width: 800px;
  margin: 3rem auto;
}

.stat-card {
  text-align: center;
  padding: 2rem;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
  border-radius: 12px;
  box-shadow: 0 4px 12px rgba(0,0,0,0.1);
}

.stat-number {
  font-size: 3rem;
  font-weight: 700;
  margin-bottom: 0.5rem;
}

.stat-label {
  font-size: 1.1rem;
  opacity: 0.9;
}

.category-section {
  margin-bottom: 3rem;
}

.category-heading {
  font-size: 1.8rem;
  color: #333;
  margin-bottom: 1.5rem;
  padding-bottom: 0.5rem;
  border-bottom: 3px solid #667eea;
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.category-icon {
  font-size: 2rem;
}

.business-cards {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
  gap: 2rem;
}

.business-card {
  background: white;
  border-radius: 12px;
  padding: 1.5rem;
  box-shadow: 0 2px 8px rgba(0,0,0,0.08);
  transition: all 0.3s;
  border: 1px solid #e0e0e0;
}

.business-card:hover {
  transform: translateY(-5px);
  box-shadow: 0 8px 20px rgba(0,0,0,0.12);
  border-color: #667eea;
}

.business-header {
  display: flex;
  justify-content: space-between;
  align-items: start;
  margin-bottom: 1rem;
  gap: 1rem;
}

.business-header h4 {
  margin: 0;
  font-size: 1.3rem;
  color: #333;
  flex: 1;
}

.business-type {
  background: #f0f0f0;
  padding: 0.3rem 0.8rem;
  border-radius: 15px;
  font-size: 0.85rem;
  color: #666;
  white-space: nowrap;
}

.business-description {
  color: #666;
  line-height: 1.6;
  margin-bottom: 1rem;
}

.business-details {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
  margin-bottom: 1rem;
}

.detail-item {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  font-size: 0.95rem;
}

.detail-icon {
  font-size: 1.2rem;
}

.detail-item a {
  color: #667eea;
  text-decoration: none;
}

.detail-item a:hover {
  text-decoration: underline;
}

.business-services {
  background: #f8f9fa;
  padding: 0.8rem;
  border-radius: 6px;
  font-size: 0.9rem;
  color: #555;
  margin-top: 1rem;
}

.business-services strong {
  color: #333;
}

.no-results {
  text-align: center;
  padding: 4rem 2rem;
  color: #999;
  font-size: 1.1rem;
}

.add-business-cta {
  background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
  color: white;
  text-align: center;
  padding: 3rem 2rem;
  border-radius: 16px;
  margin: 4rem 0;
}

.add-business-cta h3 {
  font-size: 2rem;
  margin-bottom: 1rem;
}

.add-business-cta p {
  font-size: 1.1rem;
  margin-bottom: 2rem;
  opacity: 0.95;
}

@media (max-width: 768px) {
  .business-cards {
    grid-template-columns: 1fr;
  }
  
  .directory-intro h2 {
    font-size: 1.8rem;
  }
  
  .category-heading {
    font-size: 1.5rem;
  }
}
</style>

<script>
const searchInput = document.getElementById('searchInput');
const categoryFilter = document.getElementById('categoryFilter');
const businessList = document.getElementById('businessList');
const noResults = document.getElementById('noResults');

function filterBusinesses() {
  const searchTerm = searchInput.value.toLowerCase();
  const selectedCategory = categoryFilter.value;
  
  const categories = businessList.querySelectorAll('.category-section');
  let hasVisibleResults = false;
  
  categories.forEach(categorySection => {
    const categoryName = categorySection.dataset.category;
    const cards = categorySection.querySelectorAll('.business-card');
    let categoryHasVisible = false;
    
    // Filter by category
    if (selectedCategory !== 'all' && categoryName !== selectedCategory) {
      categorySection.style.display = 'none';
      return;
    }
    
    cards.forEach(card => {
      const name = card.dataset.name.toLowerCase();
      const services = card.dataset.services.toLowerCase();
      const description = card.querySelector('.business-description').textContent.toLowerCase();
      
      const matchesSearch = name.includes(searchTerm) || 
                           services.includes(searchTerm) || 
                           description.includes(searchTerm);
      
      if (matchesSearch) {
        card.style.display = 'block';
        categoryHasVisible = true;
        hasVisibleResults = true;
      } else {
        card.style.display = 'none';
      }
    });
    
    categorySection.style.display = categoryHasVisible ? 'block' : 'none';
  });
  
  noResults.style.display = hasVisibleResults ? 'none' : 'block';
}

searchInput.addEventListener('input', filterBusinesses);
categoryFilter.addEventListener('change', filterBusinesses);
</script>

<?php include 'footer.php'; ?>