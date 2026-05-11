<?php
// voting.php
$conn = new mysqli("localhost", "root", "", "govote");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$contestant_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($contestant_id <= 0) {
    header("Location: mister-gouni.php");
    exit;
}

$result = $conn->query("SELECT name FROM contestants WHERE id = $contestant_id");
if ($result->num_rows == 0) {
    header("Location: mister-gouni.php");
    exit;
}
$contestant = $result->fetch_assoc();
$contestant_name = htmlspecialchars($contestant['name']);
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover, user-scalable=yes">
  <title>GoVote — Voting for <?php echo $contestant_name; ?></title>
  <link rel="stylesheet" href="voting.css">
  <link href="https://fonts.googleapis.com/css2?family=Inter:opsz,wght@14..32,300;400;500;600;700&display=swap" rel="stylesheet">
  <script src="https://js.paystack.co/v2/inline.js"></script>
</head>
<body>

<div class="vote-container">
  <div class="top-bar">
      <button class="back-button back-btn" aria-label="Back">
        <svg class="back-icon" viewBox="0 0 24 24" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round">
          <path d="M15 18l-6-6 6-6" />
        </svg>
      </button>
    </div>

    <div class="small-label">CONFIRM YOUR VOTE</div>
    <div class="main-title">
      Voting Section
    </div>
    <div class="description">
      Vote for <?php echo $contestant_name; ?> – your support matters!
    </div>

  <!-- Preset cards -->
  <div class="cards-grid" id="cardsGrid">
    <div class="vote-card one" data-votes="1" data-price="0" id="freeVoteCard">
      <div class="vote-amount">1 Vote</div>
      <div class="vote-badge">Free & Verified</div>
    </div>
    <div class="vote-card" data-votes="1" data-price="50">
      <div class="vote-amount">1 Vote</div>
      <div class="vote-price">₦50</div>
    </div>
    <div class="vote-card" data-votes="3" data-price="100">
      <div class="vote-amount">3 Votes</div>
      <div class="vote-price">₦100</div>
    </div>
    <div class="vote-card" data-votes="8" data-price="200">
      <div class="vote-amount">8 Votes</div>
      <div class="vote-price">₦200</div>
    </div>
    <div class="vote-card" data-votes="21" data-price="500">
      <div class="vote-amount">21 Votes</div>
      <div class="vote-price">₦500</div>
    </div>
  </div>

  <!-- Custom amount section -->
  <div class="custom-section">
    <div class="custom-label">CUSTOM AMOUNT (₦)</div>
    <div class="custom-input-wrapper">
      <span class="currency-symbol">₦</span>
      <input type="number" id="customAmountInput" class="custom-input" placeholder="Enter amount" step="1" min="0">
      <div class="votes-placeholder" id="estimatedVotesLabel">0 Votes</div>
    </div>
    <div style="font-size: 11px; color: #6A6A6A; margin-top: 8px;">
      * ₦50 = 1 vote | ₦100 = 3 | ₦200 = 8 | ₦500 = 21 | Above ₦500: +6 votes per ₦100 (e.g., ₦700 → 33 votes)
    </div>
  </div>

  <button class="proceed-btn" id="proceedBtn">Proceed to Payment</button>
  <div class="footer-copyright">© 2026 GoVote — By Obi-Dev & SkillMynte</div>
</div>

<script>
  (function() {
    const urlParams = new URLSearchParams(window.location.search);
    const contestantId = urlParams.get('id');
    if (!contestantId) {
      alert("No contestant specified. Returning to homepage.");
      window.location.href = "mister-gouni.php";
    }

    // ----- FREE VOTE RESTRICTION -----
    const FREE_VOTE_KEY = `freeVoteUsed_${contestantId}`;
    let freeVoteUsed = localStorage.getItem(FREE_VOTE_KEY) === 'true';

    const freeVoteCard = document.getElementById('freeVoteCard');
    if (freeVoteUsed) {
      // Disable free vote card
      freeVoteCard.style.opacity = '0.5';
      freeVoteCard.style.pointerEvents = 'none';
      freeVoteCard.classList.add('disabled');
      // Optionally add a tooltip or badge
      const badge = document.createElement('div');
      badge.className = 'vote-badge';
      badge.innerText = 'Already Used';
      badge.style.backgroundColor = '#555';
      badge.style.marginTop = '8px';
      freeVoteCard.appendChild(badge);
    }

    // Helper to add free vote via AJAX (updates MySQL directly)
    async function addFreeVotes(voteCount) {
      let formData = new URLSearchParams();
      formData.append('contestant_id', contestantId);
      formData.append('votes', voteCount);
      let res = await fetch('update_votes.php', { method: 'POST', body: formData });
      let data = await res.json();
      if (data.success) return true;
      throw new Error(data.error || 'Failed to add votes');
    }

    // --- Vote calculation for custom amounts ---
    function computeVotesForCustomAmount(amountNaira) {
      if (amountNaira <= 0) return 0;
      if (amountNaira < 50) return 1;
      if (amountNaira >= 500) {
        const extra = Math.floor((amountNaira - 500) / 100);
        return 21 + (extra * 6);
      }
      if (amountNaira >= 200) return 8;
      if (amountNaira >= 100) return 3;
      return 1;
    }

    // UI elements
    const cards = document.querySelectorAll('.vote-card');
    const customInput = document.getElementById('customAmountInput');
    const estimatedVotesSpan = document.getElementById('estimatedVotesLabel');
    const proceedBtn = document.getElementById('proceedBtn');

    function updateCustomVotesDisplay() {
      const raw = customInput.value.trim();
      if (raw === "") {
        estimatedVotesSpan.innerText = "0 Votes";
        return;
      }
      const naira = parseFloat(raw);
      if (isNaN(naira) || naira < 0) {
        estimatedVotesSpan.innerText = "0 Votes";
        return;
      }
      const votes = computeVotesForCustomAmount(naira);
      estimatedVotesSpan.innerText = `${votes} Vote${votes !== 1 ? 's' : ''}`;
    }

    function clearCardActiveStates() {
      cards.forEach(card => card.classList.remove('active'));
    }

    function onCustomInputChange() {
      const raw = customInput.value.trim();
      const hasPositive = raw !== "" && !isNaN(parseFloat(raw)) && parseFloat(raw) > 0;
      if (hasPositive) clearCardActiveStates();
      updateCustomVotesDisplay();
    }

    function onCardClick(e) {
      const card = e.currentTarget;
      // If it's the free vote card and it's already used, prevent selection
      if (card.id === 'freeVoteCard' && freeVoteUsed) {
        alert("You have already used your free vote for this contestant.");
        e.stopPropagation();
        return;
      }
      const customRaw = customInput.value.trim();
      if (customRaw !== "" && !isNaN(parseFloat(customRaw)) && parseFloat(customRaw) > 0) {
        customInput.value = "";
        updateCustomVotesDisplay();
      }
      clearCardActiveStates();
      card.classList.add('active');
    }

    cards.forEach(card => {
      card.removeEventListener('click', onCardClick);
      card.addEventListener('click', onCardClick);
    });
    customInput.addEventListener('input', onCustomInputChange);
    customInput.addEventListener('change', onCustomInputChange);

    customInput.value = "";
    updateCustomVotesDisplay();
    clearCardActiveStates();

    // --- Paystack integration (inline) ---
    const PAYSTACK_PUBLIC_KEY = "pk_live_c92d63fed8ce6f22416cb3fb89e1a58c4119826c";

    function generateReference() {
      return "GV" + Date.now() + Math.floor(Math.random() * 1000000);
    }

    function initiatePayment(amountInNaira, voteCount) {
      const amountInKobo = Math.round(amountInNaira * 100);
      let userEmail = localStorage.getItem('voterEmail');
      if (!userEmail) {
        userEmail = prompt("Please enter your email address:", "voter@example.com");
        if (userEmail && userEmail.includes('@')) localStorage.setItem('voterEmail', userEmail);
        else userEmail = "voter@example.com";
      }
      const handler = PaystackPop.setup({
        key: PAYSTACK_PUBLIC_KEY,
        email: userEmail,
        amount: amountInKobo,
        currency: "NGN",
        ref: generateReference(),
        metadata: { 
          custom_fields: [
            { display_name: "Contestant ID", value: contestantId },
            { display_name: "Votes", value: voteCount }
          ] 
        },
        callback: (response) => {
          window.location.href = `success.php?contestant_id=${contestantId}&votes=${voteCount}&amount=${amountInNaira}&reference=${response.reference}`;
        },
        onClose: () => alert("Payment window closed. Transaction not completed.")
      });
      handler.openIframe();
      return true;
    }

    // --- Proceed button logic (Free vote or paid) ---
    proceedBtn.addEventListener('click', async (e) => {
      e.preventDefault();

      let activeCard = null;
      for (let card of cards) if (card.classList.contains('active')) { activeCard = card; break; }

      let customAmountNaira = null;
      let customVotes = 0;
      const customRaw = customInput.value.trim();
      if (customRaw !== "") {
        const parsed = parseFloat(customRaw);
        if (!isNaN(parsed) && parsed > 0) {
          customAmountNaira = parsed;
          customVotes = computeVotesForCustomAmount(parsed);
        }
      }

      let voteCount = 0, price = 0;
      let isFreeVote = false;

      if (activeCard && (customAmountNaira === null || customAmountNaira === 0)) {
        voteCount = parseInt(activeCard.getAttribute('data-votes'), 10);
        price = parseInt(activeCard.getAttribute('data-price'), 10);
        if (price === 0) isFreeVote = true;
      } else if (customAmountNaira !== null && customAmountNaira > 0) {
        voteCount = customVotes;
        price = customAmountNaira;
      } else {
        alert('Please select a vote package or enter a custom amount (₦) to proceed.');
        return;
      }

      if (voteCount === 0) {
        alert('Please select a valid vote amount (minimum 1 vote).');
        return;
      }

      // Free vote (price = 0)
      if (isFreeVote) {
        // Check again if free vote already used (in case UI was bypassed)
        if (freeVoteUsed) {
          alert("You have already used your free vote for this contestant.");
          return;
        }
        try {
          await addFreeVotes(voteCount);
          // Mark free vote as used in localStorage
          localStorage.setItem(FREE_VOTE_KEY, 'true');
          freeVoteUsed = true;
          // Disable the free vote card visually
          freeVoteCard.style.opacity = '0.5';
          freeVoteCard.style.pointerEvents = 'none';
          freeVoteCard.classList.add('disabled');
          // Remove active class if it had it
          freeVoteCard.classList.remove('active');
          alert(`🎉 Thank you for your free vote! ${voteCount} vote(s) added.`);
          window.location.href = `contestants.php?id=${contestantId}`;
        } catch (err) {
          alert(err.message);
        }
        return;
      }

      // Paid flow – real Paystack
      initiatePayment(price, voteCount);
    });

    // Back button
    const backBtn = document.querySelector(".back-btn");
    if (backBtn) {
      backBtn.addEventListener("click", () => {
        window.history.back();
      });
    }
  })();
</script>
</body>
</html>
