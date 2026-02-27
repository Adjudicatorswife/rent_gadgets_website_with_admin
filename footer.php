<?php $current_page = $current_page ?? basename($_SERVER['PHP_SELF'], '.php'); ?>

<nav class="bottom-nav">
  <a href="<?= BASE_URL ?>/pages/home.php" class="bottom-nav-item <?= $current_page==='home'?'active':'' ?>">
    <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="m3 9 9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
    <span>Home</span>
  </a>
  <a href="<?= BASE_URL ?>/pages/browse.php" class="bottom-nav-item <?= $current_page==='browse'?'active':'' ?>">
    <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><rect x="2" y="4" width="20" height="12" rx="2"/><path d="M8 20h8M12 16v4"/></svg>
    <span>Browse</span>
  </a>
  <a href="<?= BASE_URL ?>/pages/my-rentals.php" class="bottom-nav-item <?= $current_page==='my-rentals'?'active':'' ?>">
    <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M9 5H7a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2h-2"/><rect x="9" y="3" width="6" height="4" rx="1" ry="1"/><line x1="9" y1="12" x2="15" y2="12"/><line x1="9" y1="16" x2="11" y2="16"/></svg>
    <span>My Rentals</span>
  </a>
  <a href="<?= BASE_URL ?>/pages/messages.php" class="bottom-nav-item <?= $current_page==='messages'?'active':'' ?>">
    <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
    <span>Messages</span>
  </a>
  <a href="<?= BASE_URL ?>/pages/profile.php" class="bottom-nav-item <?= $current_page==='profile'?'active':'' ?>">
    <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
    <span>Profile</span>
  </a>
</nav>

<script src="<?= BASE_URL ?>/js/app.js"></script>
<script>
// Update notif badge
updateNotifBadge();
// Observe scroll animations
setTimeout(observeAnimations, 100);
</script>