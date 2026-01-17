<?php
$page_title = "Attractions";
$hero_folder = "images/hero/attractions/";
$hero_heading = "Discover Matatiele's Hidden Treasures";
$hero_text = "From majestic mountains to rich cultural heritage, explore the best attractions in and around Matatiele.";

include 'header.php';
require 'includes/config.php';

// Fetch attractions from the database
$stmt = $pdo->query("SELECT * FROM attractions ORDER BY sort_order ASC");
$attractions = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<main class="container">
  <section class="attractions-section">
    <h2>Attractions in Matatiele</h2>
    <p>Experience breathtaking nature reserves, historical sites, and cultural landmarks in the heart of the Southern Drakensberg.</p>

    <!-- Filter & Search -->
    <div class="filter-bar">
      <input type="text" id="searchBar" placeholder="Search attractions..." onkeyup="filterCards()" class="search-input">
      <select id="categoryFilter" onchange="filterCards()" class="filter-select">
        <option value="all">All Categories</option>
        <option value="nature">Nature & Wildlife</option>
        <option value="culture">Cultural & Historical</option>
        <option value="adventure">Adventure & Sports</option>
        <option value="scenic">Scenic Routes</option>
      </select>
    </div>

    <div class="card-grid" id="attractionsList">
      <?php
      $labels = [
        'nature' => 'Nature & Wildlife',
        'culture' => 'Cultural & Historical',
        'adventure' => 'Adventure & Sports',
        'scenic' => 'Scenic Routes'
      ];

      foreach ($attractions as $index => $place):
        $name = $place['name'] ?? 'Untitled Attraction';
        $desc = $place['description'] ?? '';
        $loc = $place['location'] ?? '';
        $contact = $place['contact'] ?? '';
        $category = $place['category'] ?? 'other';

        // Only use placeholder if image field is empty
        $image = !empty($place['image']) ? htmlspecialchars($place['image']) : 'images/placeholder.jpg';
        
        // Truncate description for card view
        $short_desc = strlen($desc) > 120 ? substr($desc, 0, 120) . '...' : $desc;
      ?>
        <div class="card attraction-card <?php echo $index >= 6 ? 'hidden' : ''; ?>" 
             data-category="<?php echo htmlspecialchars(strtolower($category)); ?>">
          <div class="img-container">
            <img src="<?php echo $image; ?>" alt="<?php echo htmlspecialchars($name); ?>">
            <span class="category-label"><?php echo $labels[$category] ?? 'Other'; ?></span>
          </div>
          <div class="content">
            <h3><?php echo htmlspecialchars($name); ?></h3>
            <p class="desc"><?php echo htmlspecialchars($short_desc); ?></p>
            <p class="address"><strong>Location:</strong> <?php echo htmlspecialchars($loc); ?></p>
            
            <a href="attraction.php?id=<?php echo $place['id']; ?>" class="explore-btn">View Details →</a>
          </div>
        </div>
      <?php endforeach; ?>
    </div>

    <div class="view-more-container">
      <button id="viewMoreBtn" onclick="showMore()" class="view-more-btn">View More Attractions</button>
    </div>
  </section>
</main>

<style>
  .attractions-section { padding: 3rem 0; }
  .attractions-section h2 { text-align: center; font-size: 2rem; margin-bottom: .5rem; }
  .attractions-section p { text-align: center; color: #555; max-width: 600px; margin: 0 auto 2rem; }

  .filter-bar { display: flex; justify-content: center; flex-wrap: wrap; gap: 1rem; margin-bottom: 2rem; }
  .search-input, .filter-select {
    padding: .8rem 1rem; border-radius: 25px; border: 1px solid #ccc; font-size: 1rem; outline: none;
  }

  .card-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 2rem; }
  .attraction-card {
    border-radius: 15px; overflow: hidden; background: #fff;
    box-shadow: 0 4px 12px rgba(0,0,0,0.1); transition: .3s ease; opacity: 1;
  }
  .attraction-card:hover { transform: translateY(-5px); box-shadow: 0 8px 20px rgba(0,0,0,0.15); }
  .attraction-card.hidden { display: none; }
  .attraction-card.fade-in { animation: fadeIn 0.6s ease-in-out forwards; }

  @keyframes fadeIn {
    from {opacity: 0; transform: translateY(10px);}
    to {opacity: 1; transform: translateY(0);}
  }

  .img-container { position: relative; }
  .img-container img { width: 100%; height: 200px; object-fit: cover; display: block; }
  .category-label {
    position: absolute; bottom: 10px; right: 10px;
    background: rgba(0, 123, 255, 0.85);
    color: #fff;
    padding: .4rem .9rem;
    border-radius: 12px;
    font-size: .75rem;
    font-weight: 500;
    box-shadow: 0 2px 6px rgba(0,0,0,0.3);
  }

  .attraction-card .content { padding: 1.3rem; }
  .attraction-card h3 { margin: .5rem 0; color: #333; font-size: 1.3rem; }
  .attraction-card .desc { color: #666; font-size: .95rem; line-height: 1.5; margin: .8rem 0; }
  .attraction-card .address { font-size: .9rem; color: #444; margin-top: .5rem; }

  .explore-btn {
    display: inline-block; margin-top: 1rem; padding: .7rem 1.4rem;
    background: linear-gradient(45deg,#28a745,#20c997);
    color: #fff; font-weight: 600; border-radius: 25px;
    text-decoration: none; transition: .3s; box-shadow: 0 3px 10px rgba(40,167,69,0.2);
  }
  .explore-btn:hover { background: linear-gradient(45deg,#20c997,#28a745); transform: scale(1.05); }

  .view-more-container { text-align: center; margin-top: 2rem; }
  .view-more-btn {
    padding: 0.8rem 1.6rem; font-size: 1rem; color: #fff;
    background: linear-gradient(45deg,#28a745,#20c997);
    border: none; border-radius: 30px; cursor: pointer; transition: .3s;
  }
  .view-more-btn:hover { transform: scale(1.05); background: linear-gradient(45deg,#20c997,#28a745); }
</style>

<script>
  const step = 6;

  function filterCards() {
    const search = document.getElementById('searchBar').value.toLowerCase();
    const category = document.getElementById('categoryFilter').value;
    const cards = document.querySelectorAll('.attraction-card');
    cards.forEach(card => {
      const title = card.querySelector('h3').innerText.toLowerCase();
      const cat = card.dataset.category.toLowerCase();
      const matchSearch = title.includes(search);
      const matchCat = category === 'all' || cat === category;
      card.style.display = matchSearch && matchCat ? 'block' : 'none';
    });
  }

  function showMore() {
    const cards = document.querySelectorAll('.attraction-card.hidden');
    let count = 0;
    cards.forEach(card => {
      if (count < step) {
        card.classList.remove('hidden');
        card.classList.add('fade-in');
        count++;
      }
    });
    if (document.querySelectorAll('.attraction-card.hidden').length === 0) {
      document.getElementById('viewMoreBtn').style.display = 'none';
    }
  }
</script>

<?php include 'footer.php'; ?>