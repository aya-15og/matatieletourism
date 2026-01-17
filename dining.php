<?php
ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);
error_reporting(E_ALL);

$page_title = "Where to Eat";
$hero_folder = "images/hero/dining/";
$hero_heading = "Taste the Flavors of Matatiele";
$hero_text = "From traditional African cuisine to family-friendly restaurants, discover the best dining experiences in Matatiele and surrounding areas.";
include 'header.php';
require 'includes/config.php';

// Fetch dining places from DB
$stmt = $pdo->query("SELECT * FROM dining ORDER BY sort_order ASC");
$restaurants = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<main class="container">
  <section class="dining-section">
    <h2>Dining & Restaurants</h2>
    <p>Savor delicious meals at restaurants, cafés, and eateries throughout Matatiele, Cedarville, and rural communities.</p>

    <div class="filter-bar">
      <input type="text" id="searchBar" placeholder="Search restaurants..." onkeyup="filterCards()" class="search-input">
      <select id="categoryFilter" onchange="filterCards()" class="filter-select">
        <option value="all">All Categories</option>
        <option value="restaurant">Restaurants</option>
        <option value="cafe">Cafés & Coffee Shops</option>
        <option value="fastfood">Fast Food & Takeaways</option>
        <option value="traditional">Traditional & Local</option>
        <option value="hotel">Hotel Dining</option>
      </select>
    </div>

    <div class="card-grid" id="diningList">
      <?php 
      $labels = [
        'restaurant' => 'Restaurant',
        'cafe' => 'Café',
        'fastfood' => 'Fast Food',
        'traditional' => 'Traditional',
        'hotel' => 'Hotel Dining'
      ];
      
      foreach ($restaurants as $index => $place): 
        $image_path = htmlspecialchars($place['img']);
        $final_image_src = empty($image_path) ? 'images/placeholder.jpg' : $image_path;
        $short_desc = strlen($place['desc']) > 100 ? substr($place['desc'], 0, 100) . '...' : $place['desc'];
      ?>
        <div class="card dining-card <?= $index >= 6 ? 'hidden' : '' ?>" data-category="<?= htmlspecialchars($place['category']) ?>">
          <div class="img-container">
            <img src="<?= $final_image_src ?>" alt="<?= htmlspecialchars($place['name']) ?>" onerror="this.onerror=null; this.src='images/placeholder.jpg';">
            <span class="category-label"><?= $labels[$place['category']] ?? 'Dining'; ?></span>
          </div>
          <div class="content">
            <h3><?= htmlspecialchars($place['name']) ?></h3>
            <p class="desc"><?= htmlspecialchars($short_desc) ?></p>
            <p class="location"><i>📍</i> <?= htmlspecialchars($place['location']) ?></p>
            
            <div class="card-actions">
              <button class="details-btn" onclick="showDetails(<?= $place['id'] ?>)">
                <span>View Details</span> →
              </button>
              <?php if (!empty($place['contact'])): ?>

              <?php endif; ?>
            </div>
          </div>
        </div>
      <?php endforeach; ?>
    </div>

    <div class="view-more-container">
      <button id="viewMoreBtn" onclick="showMore()" class="view-more-btn">View More Restaurants</button>
    </div>
  </section>
</main>

<!-- Modal for Details -->
<div id="detailsModal" class="modal">
  <div class="modal-content">
    <span class="close-btn" onclick="closeModal()">&times;</span>
    <div id="modalBody">
      <div class="modal-loading">Loading...</div>
    </div>
  </div>
</div>

<style>
.dining-section { padding: 3rem 0; }
.dining-section h2 { text-align: center; font-size: 2rem; margin-bottom: .5rem; color: #333; }
.dining-section p { text-align: center; color: #555; max-width: 600px; margin: 0 auto 2rem; line-height: 1.6; }

.filter-bar { 
  display: flex; 
  justify-content: center; 
  flex-wrap: wrap; 
  gap: 1rem; 
  margin-bottom: 2rem; 
}
.search-input, .filter-select { 
  padding: .9rem 1.2rem; 
  border-radius: 25px; 
  border: 2px solid #e1e8ed; 
  font-size: 1rem; 
  outline: none; 
  transition: border-color 0.3s;
}
.search-input:focus, .filter-select:focus {
  border-color: #DC3545;
}

.card-grid { 
  display: grid; 
  grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); 
  gap: 2rem; 
}
.dining-card { 
  border-radius: 15px; 
  overflow: hidden; 
  background: #fff; 
  box-shadow: 0 4px 15px rgba(0,0,0,0.08); 
  transition: all 0.3s ease; 
  opacity: 1; 
}
.dining-card:hover { 
  transform: translateY(-8px); 
  box-shadow: 0 12px 30px rgba(220, 53, 69, 0.15); 
}
.dining-card.hidden { display: none; }
.dining-card.fade-in { animation: fadeIn 0.6s ease-in-out forwards; }

@keyframes fadeIn { 
  from {opacity: 0; transform: translateY(10px);} 
  to {opacity: 1; transform: translateY(0);} 
}

.img-container { position: relative; overflow: hidden; }
.img-container img { 
  width: 100%; 
  height: 200px; 
  object-fit: cover; 
  display: block; 
  transition: transform 0.3s;
}
.dining-card:hover .img-container img {
  transform: scale(1.05);
}
.category-label { 
  position: absolute; 
  bottom: 12px; 
  right: 12px; 
  background: rgba(220, 53, 69, 0.9); 
  color: #fff; 
  padding: .4rem 1rem; 
  border-radius: 20px; 
  font-size: .75rem; 
  font-weight: 600; 
  text-transform: uppercase;
  letter-spacing: 0.5px;
  box-shadow: 0 2px 8px rgba(0,0,0,0.3); 
}

.dining-card .content { padding: 1.5rem; }
.dining-card h3 { 
  margin: 0 0 .8rem; 
  color: #333; 
  font-size: 1.4rem;
  font-weight: 700;
}
.dining-card .desc { 
  color: #666; 
  font-size: .95rem; 
  line-height: 1.6;
  margin-bottom: 1rem;
}
.dining-card .location { 
  font-size: .9rem; 
  color: #555; 
  margin: .8rem 0; 
  display: flex;
  align-items: center;
  gap: 0.5rem;
}
.dining-card .location i {
  font-style: normal;
}

.card-actions {
  display: flex;
  gap: 0.8rem;
  margin-top: 1.2rem;
  align-items: center;
}

.details-btn { 
  flex: 1;
  padding: .8rem 1.5rem; 
  background: linear-gradient(45deg, #DC3545, #FD7E14); 
  color: #fff; 
  font-weight: 600; 
  border-radius: 25px; 
  border: none;
  cursor: pointer;
  transition: all 0.3s; 
  box-shadow: 0 4px 12px rgba(220,53,69,0.3); 
  font-size: 0.95rem;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 0.5rem;
}
.details-btn:hover { 
  background: linear-gradient(45deg, #FD7E14, #DC3545); 
  transform: translateY(-2px); 
  box-shadow: 0 6px 16px rgba(220,53,69,0.4); 
}

.contact-btn {
  width: 45px;
  height: 45px;
  display: flex;
  align-items: center;
  justify-content: center;
  background: #f8f9fa;
  border-radius: 50%;
  text-decoration: none;
  font-size: 1.2rem;
  transition: all 0.3s;
  border: 2px solid #e1e8ed;
}
.contact-btn:hover {
  background: #28a745;
  border-color: #28a745;
  transform: scale(1.1);
}

.view-more-container { text-align: center; margin-top: 3rem; }
.view-more-btn { 
  padding: 1rem 2.5rem; 
  font-size: 1.05rem; 
  color: #fff; 
  background: linear-gradient(45deg, #DC3545, #FD7E14); 
  border: none; 
  border-radius: 30px; 
  cursor: pointer; 
  transition: all 0.3s; 
  font-weight: 600;
  box-shadow: 0 4px 15px rgba(220,53,69,0.3);
}
.view-more-btn:hover { 
  transform: translateY(-3px); 
  box-shadow: 0 6px 20px rgba(220,53,69,0.4); 
  background: linear-gradient(45deg, #FD7E14, #DC3545); 
}

/* Modal Styles */
.modal {
  display: none;
  position: fixed;
  z-index: 1000;
  left: 0;
  top: 0;
  width: 100%;
  height: 100%;
  background-color: rgba(0,0,0,0.7);
  animation: fadeIn 0.3s;
}

.modal-content {
  background-color: #fff;
  margin: 5% auto;
  padding: 0;
  border-radius: 20px;
  width: 90%;
  max-width: 700px;
  max-height: 85vh;
  overflow-y: auto;
  box-shadow: 0 10px 50px rgba(0,0,0,0.3);
  animation: slideDown 0.3s;
  position: relative;
}

@keyframes slideDown {
  from { transform: translateY(-50px); opacity: 0; }
  to { transform: translateY(0); opacity: 1; }
}

.close-btn {
  position: sticky;
  top: 0;
  right: 0;
  float: right;
  font-size: 2rem;
  font-weight: bold;
  color: #666;
  cursor: pointer;
  padding: 0.5rem 1rem;
  background: white;
  z-index: 10;
  border-radius: 0 20px 0 0;
  transition: color 0.3s;
}
.close-btn:hover { color: #DC3545; }

.modal-loading {
  text-align: center;
  padding: 3rem;
  color: #666;
  font-size: 1.1rem;
}

.modal-header {
  position: relative;
  height: 250px;
  overflow: hidden;
  border-radius: 20px 20px 0 0;
}
.modal-header img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}
.modal-header .category-badge {
  position: absolute;
  top: 20px;
  right: 20px;
  background: rgba(220, 53, 69, 0.95);
  color: white;
  padding: 0.6rem 1.2rem;
  border-radius: 20px;
  font-weight: 600;
  font-size: 0.9rem;
  text-transform: uppercase;
  letter-spacing: 0.5px;
  box-shadow: 0 4px 12px rgba(0,0,0,0.3);
}

.modal-body {
  padding: 2rem;
}
.modal-body h2 {
  font-size: 2rem;
  color: #333;
  margin-bottom: 1.5rem;
}

.modal-info-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: 1.5rem;
  margin: 2rem 0;
}
.modal-info-card {
  padding: 1.2rem;
  background: #f8f9fa;
  border-radius: 12px;
  border-left: 4px solid #DC3545;
}
.modal-info-card h3 {
  font-size: 0.85rem;
  color: #666;
  text-transform: uppercase;
  margin-bottom: 0.5rem;
  letter-spacing: 0.5px;
}
.modal-info-card p {
  color: #333;
  font-size: 1rem;
  font-weight: 500;
  margin: 0;
  line-height: 1.6;
}

.modal-description {
  margin: 2rem 0;
  padding: 1.5rem;
  background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
  border-radius: 12px;
  line-height: 1.8;
  color: #555;
}

.modal-actions {
  display: flex;
  gap: 1rem;
  margin-top: 2rem;
  flex-wrap: wrap;
}
.modal-btn {
  flex: 1;
  min-width: 150px;
  padding: 1rem 2rem;
  border-radius: 25px;
  text-decoration: none;
  text-align: center;
  font-weight: 600;
  transition: all 0.3s;
  border: none;
  cursor: pointer;
  font-size: 1rem;
}
.modal-btn-primary {
  background: linear-gradient(45deg, #28a745, #20c997);
  color: white;
  box-shadow: 0 4px 12px rgba(40, 167, 69, 0.3);
}
.modal-btn-primary:hover {
  background: linear-gradient(45deg, #20c997, #28a745);
  transform: translateY(-2px);
  box-shadow: 0 6px 16px rgba(40, 167, 69, 0.4);
}
.modal-btn-secondary {
  background: white;
  color: #DC3545;
  border: 2px solid #DC3545;
}
.modal-btn-secondary:hover {
  background: #DC3545;
  color: white;
}

/* Responsive */
@media (max-width: 768px) {
  .card-grid { grid-template-columns: 1fr; }
  .modal-content { width: 95%; margin: 10% auto; }
  .modal-header { height: 200px; }
  .modal-body { padding: 1.5rem; }
  .modal-info-grid { grid-template-columns: 1fr; }
  .card-actions { flex-direction: column; }
  .contact-btn { width: 100%; border-radius: 25px; height: 45px; }
}
</style>

<script>
const step = 6;
const allData = <?= json_encode($restaurants) ?>;

function filterCards() {
  const search = document.getElementById('searchBar').value.toLowerCase();
  const category = document.getElementById('categoryFilter').value;
  const cards = document.querySelectorAll('.dining-card');
  cards.forEach(card => {
    const title = card.querySelector('h3').innerText.toLowerCase();
    const cat = card.dataset.category.toLowerCase();
    card.style.display = (title.includes(search) && (category==='all'||cat===category)) ? 'block' : 'none';
  });
}

function showMore() {
  const cards = document.querySelectorAll('.dining-card.hidden');
  let count = 0;
  cards.forEach(card => {
    if(count<step){ 
      card.classList.remove('hidden'); 
      card.classList.add('fade-in'); 
      count++; 
    }
  });
  if(document.querySelectorAll('.dining-card.hidden').length===0){ 
    document.getElementById('viewMoreBtn').style.display='none'; 
  }
}

function showDetails(id) {
  const restaurant = allData.find(r => r.id == id);
  if (!restaurant) return;
  
  const labels = {
    'restaurant': 'Restaurant',
    'cafe': 'Café',
    'fastfood': 'Fast Food',
    'traditional': 'Traditional',
    'hotel': 'Hotel Dining'
  };
  
  const imageSrc = restaurant.img || 'images/placeholder.jpg';
  
  const modalBody = document.getElementById('modalBody');
  modalBody.innerHTML = `
    <div class="modal-header">
      <img src="${imageSrc}" alt="${restaurant.name}" onerror="this.src='images/placeholder.jpg'">
      <span class="category-badge">${labels[restaurant.category] || 'Dining'}</span>
    </div>
    <div class="modal-body">
      <h2>${restaurant.name}</h2>
      
      <div class="modal-info-grid">
        <div class="modal-info-card">
          <h3>📍 Location</h3>
          <p>${restaurant.location}</p>
        </div>
        <div class="modal-info-card">
          <h3>📞 Contact</h3>
          <p>${restaurant.contact}</p>
        </div>
        ${restaurant.hours ? `
        <div class="modal-info-card">
          <h3>🕒 Hours</h3>
          <p>${restaurant.hours}</p>
        </div>
        ` : ''}
      </div>
      
      <div class="modal-description">
        <p>${restaurant.desc}</p>
      </div>
      
      <div class="modal-actions">
        <a href="tel:${restaurant.contact}" class="modal-btn modal-btn-primary">
          📞 Call Now
        </a>
        <a href="contact.php?subject=Inquiry about ${encodeURIComponent(restaurant.name)}" class="modal-btn modal-btn-secondary">
          ✉️ Send Inquiry
        </a>
      </div>
    </div>
  `;
  
  document.getElementById('detailsModal').style.display = 'block';
  document.body.style.overflow = 'hidden';
}

function closeModal() {
  document.getElementById('detailsModal').style.display = 'none';
  document.body.style.overflow = 'auto';
}

// Close modal when clicking outside
window.onclick = function(event) {
  const modal = document.getElementById('detailsModal');
  if (event.target === modal) {
    closeModal();
  }
}

// Close modal with Escape key
document.addEventListener('keydown', function(event) {
  if (event.key === 'Escape') {
    closeModal();
  }
});
</script>

<?php include 'footer.php'; ?>