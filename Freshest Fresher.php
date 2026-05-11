<?php
$conn = new mysqli("localhost", "root", "", "govote");
if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);

// Fetch top 3 male contestants
$top_sql = "SELECT id, name, image_url FROM contestants WHERE gender = 'male' ORDER BY votes DESC LIMIT 3";
$top_result = $conn->query($top_sql);
$top_contestants = $top_result->fetch_all(MYSQLI_ASSOC);

// Fetch all male contestants
$all_sql = "SELECT id, name, image_url FROM contestants WHERE gender = 'male' ORDER BY name ASC";
$all_result = $conn->query($all_sql);
$all_contestants = $all_result->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover, user-scalable=no">
  <title>GoVote — Freshest Fresher 2026</title>
  <link rel="stylesheet" href="Freshest Fresher.css">
  <link href="https://fonts.googleapis.com/css2?family=Inter:opsz,wght@14..32,400;14..32,500;14..32,600;14..32,700&display=swap" rel="stylesheet">
</head>
<body>
<div class="phone-container">
  <div class="app-content">
    <div class="top-bar">
      <button class="back-button back-btn" aria-label="Back">
        <svg class="back-icon" viewBox="0 0 24 24" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round">
          <path d="M15 18l-6-6 6-6" />
        </svg>
      </button>
    </div>

    <div class="small-label">UNIVERSITY STUDENT WEEK</div>
    <div class="main-title">
      Freshest Fresher <span class="title-accent">2026</span>
    </div>
    <div class="description">
      Browse through multiple categories and vote for your respective stars.
    </div>

    <div class="search-wrapper">
      <input type="text" class="search-input" placeholder="Search contestant">
      <button class="search-icon-btn" id="searchBtn" aria-label="Search">
        <svg class="search-icon-svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <circle cx="10.5" cy="10.5" r="6.5" />
          <line x1="15" y1="15" x2="21" y2="21" />
        </svg>
      </button>
    </div>

    <div class="section-spacing">
      <div class="section-header">
        <div class="section-label">TOP 3 CONTESTANTS</div>
      </div>
      <div class="top-three-row">
        <?php foreach ($top_contestants as $index => $c): ?>
        <a href="contestants.php?id=<?= $c['id'] ?>" class="top-card" data-id="<?= $c['id'] ?>" style="text-decoration: none; display: block;">
          <div class="card-number <?= $index === 0 ? 'accent-number' : 'muted-number' ?>">
            <?= str_pad($index+1, 2, '0', STR_PAD_LEFT) ?>
          </div>
        </a>
        <?php endforeach; ?>
      </div>
    </div>

    <div class="lineup-section">
      <div class="section-header">
        <div class="section-label">THIS YEAR'S LINEUP</div>
      </div>

      <div class="contestants-grid" id="contestantsContainer">
        <?php foreach ($all_contestants as $c): ?>
        <a href="contestants.php?id=<?= $c['id'] ?>" class="contestant-card" data-id="<?= $c['id'] ?>" style="text-decoration: none; color: inherit; display: block;">
          <div class="image-placeholder" style="background-image: url('<?= htmlspecialchars($c['image_url']) ?>'); background-size: cover; background-position: center;"></div>
          <div class="bottom-overlay">
            <span class="contestant-name"><?= htmlspecialchars($c['name']) ?></span>
            <button class="star-button star-toggle" data-star="<?= $c['id'] ?>" data-filled="false" onclick="event.stopPropagation(); event.preventDefault();">
              <svg class="star-icon star-outline" viewBox="0 0 24 24" fill="none">
                <path d="M12 17.27L18.18 21L16.54 13.97L22 9.24L14.81 8.63L12 2L9.19 8.63L2 9.24L7.46 13.97L5.82 21L12 17.27Z" fill="none" stroke="#9A9A9A" stroke-width="1.8"/>
              </svg>
            </button>
          </div>
        </a>
        <?php endforeach; ?>
      </div>
    </div>

    <div class="footer">
      © 2026 GoVote — By Obi-Dev & SkillMynte
    </div>
  </div>
</div>

<script>
  (function() {
    // Search button
    const searchBtn = document.getElementById('searchBtn');
    if (searchBtn) searchBtn.addEventListener('click', e => { e.preventDefault(); console.log("search clicked"); });

    // Star toggle logic
    const starButtons = document.querySelectorAll('.star-toggle');
    function updateStarIcon(btn, filled) {
      btn.innerHTML = filled ? `<svg class="star-icon star-filled" viewBox="0 0 24 24" fill="none"><path d="M12 17.27L18.18 21L16.54 13.97L22 9.24L14.81 8.63L12 2L9.19 8.63L2 9.24L7.46 13.97L5.82 21L12 17.27Z" fill="#C9A95C" stroke="#C9A95C" stroke-width="1"/></svg>` : `<svg class="star-icon star-outline" viewBox="0 0 24 24" fill="none"><path d="M12 17.27L18.18 21L16.54 13.97L22 9.24L14.81 8.63L12 2L9.19 8.63L2 9.24L7.46 13.97L5.82 21L12 17.27Z" fill="none" stroke="#9A9A9A" stroke-width="1.8"/></svg>`;
      btn.setAttribute('data-filled', filled);
    }
    starButtons.forEach(btn => {
      btn.addEventListener('click', e => { e.stopPropagation(); e.preventDefault(); updateStarIcon(btn, btn.getAttribute('data-filled') !== 'true'); });
    });

    // Back button
    const backBtn = document.querySelector(".back-btn");
    if (backBtn) backBtn.addEventListener("click", () => window.history.back());
  })();
</script>
</body>
</html>
<?php $conn->close(); ?>