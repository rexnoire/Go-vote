<?php
$conn = new mysqli("localhost", "root", "", "govote");
if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($id <= 0) { header("Location: master gouni.php"); exit; }

$sql = "SELECT * FROM contestants WHERE id = $id";
$result = $conn->query($sql);
if ($result->num_rows == 0) { header("Location: master gouni.php"); exit; }
$contestant = $result->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
  <title>GoVote — <?= htmlspecialchars($contestant['name']) ?></title>
  <link rel="stylesheet" href="contestant.css">
  <link href="https://fonts.googleapis.com/css2?family=Inter:opsz,wght@14..32,300;400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>
<div class="top-bar">
  <button class="back-button back-btn" aria-label="Back">
    <svg class="back-icon" viewBox="0 0 24 24" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round">
      <path d="M15 18l-6-6 6-6" />
    </svg>
  </button>
</div>

<div class="voting-card">
  <div class="image-placeholder" id="dynamicImagePlaceholder" style="background-image: url('<?= htmlspecialchars($contestant['image_url']) ?>'); background-size: cover; background-position: center;"></div>

  <div class="card-content">
    <div class="gradient"></div>
    <div class="contestant-name" id="contestantName"><?= htmlspecialchars($contestant['name']) ?></div>
    <div class="contestant-detail" id="contestantDetail"><?= htmlspecialchars($contestant['contestant_detail']) ?></div>

    <div class="vote-digits" id="digitContainer">
      <div class="digit-box one" id="digit1">0</div>
      <div class="digit-box" id="digit2">0</div>
      <div class="digit-box" id="digit3">0</div>
      <div class="digit-box two" id="digit4">0</div>
    </div>

    <div class="live-label">Live Vote Counter</div>

    <button class="vote-btn" id="voteNowBtn">Vote Now</button>

    <div class="about-title">About Contestant</div>
    <div class="about-text" id="aboutText"><?= htmlspecialchars($contestant['about']) ?></div>

    <div class="footer-note">GoVote • real‑time counter</div>
  </div>
</div>

<script>
  const contestantId = <?= $contestant['id'] ?>;
  let currentVotes = <?= intval($contestant['votes']) ?>;

  const digits = {
    0: document.getElementById('digit1'),
    1: document.getElementById('digit2'),
    2: document.getElementById('digit3'),
    3: document.getElementById('digit4')
  };

  function updateDisplay() {
    let val = Math.min(Math.max(currentVotes, 0), 9999);
    let padded = String(val).padStart(4, '0');
    for (let i = 0; i < 4; i++) digits[i].innerText = padded[i];
  }
  updateDisplay();

  function fetchLiveVotes() {
    fetch(`get_votes.php?id=${contestantId}`)
      .then(res => res.json())
      .then(data => { if (data.success) { currentVotes = data.votes; updateDisplay(); } })
      .catch(console.error);
  }
  setInterval(fetchLiveVotes, 3000);

  document.getElementById('voteNowBtn').addEventListener('click', () => {
    window.location.href = `voting.php?id=${contestantId}`;
  });

  document.querySelector('.back-btn').addEventListener('click', () => window.history.back());
</script>
</body>
</html>
<?php $conn->close(); ?>