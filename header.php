<?php
require_once dirname(__DIR__) . '/php/config.php';
requireLogin();
// Redirect admin to admin panel
if (isAdmin()) { header('Location: ' . BASE_URL . '/admin/dashboard.php'); exit; }
$current_page = basename($_SERVER['PHP_SELF'], '.php');
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
<title><?= $page_title ?? 'RELAPSE' ?></title>
<link rel="stylesheet" href="<?= BASE_URL ?>/css/style.css">
<link href="https://fonts.googleapis.com/css2?family=Rajdhani:wght@400;500;600;700&family=Nunito:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>

<nav class="navbar">
  <a href="<?= BASE_URL ?>/pages/home.php" class="navbar-brand-container" style="display:flex;align-items:center;gap:10px;text-decoration:none;">
    <img src="<?= BASE_URL ?>/uploads/logo.png" alt="RELAPSE" 
         style="height:36px;width:auto;object-fit:contain;"
         class="logo-spin-slow">
    <span class="navbar-brand-text" style="font-family:var(--font-display);font-size:1.5rem;font-weight:700;letter-spacing:2px;background:linear-gradient(#000000,#808080,#ffffff);-webkit-background-clip:text;-webkit-text-fill-color:transparent;animation:pulse 2s ease-in-out infinite;">RELAPSE</span>
  </a>
  <div class="navbar-actions">
    <a href="<?= BASE_URL ?>/pages/search.php" class="nav-btn" title="Search">
      <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
    </a>
    <a href="<?= BASE_URL ?>/pages/notifications.php" class="nav-btn" title="Notifications">
      <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/></svg>
      <span class="nav-badge notif-badge" style="display:none;">0</span>
    </a>
    <a href="<?= BASE_URL ?>/pages/profile.php" class="nav-btn" title="Profile">
      <?php if (!empty($_SESSION['avatar']) && $_SESSION['avatar'] !== 'default.png'): ?>
      <img src="<?= BASE_URL ?>/uploads/avatars/<?= $_SESSION['avatar'] ?>" style="width:32px;height:32px;border-radius:50%;object-fit:cover;" alt="">
      <?php else: ?>
      <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
      <?php endif; ?>
    </a>
  </div>
</nav>

<div class="toast-container"></div>
<button class="scroll-top" onclick="window.scrollTo({top:0,behavior:'smooth'})">
  <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><polyline points="18 15 12 9 6 15"/></svg>
</button>