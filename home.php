<?php
$page = 'home';
$title = 'Dashboard - RELAPSE';
require_once '../includes/config.php';
requireLogin();
if (isAdmin()) { header('Location: '.SITE_URL.'/admin/dashboard.php'); exit; }
$db = getDB();
$uid = $_SESSION['user_id'];

// Stats
$activeRentals = $db->prepare("SELECT COUNT(*) FROM rentals WHERE user_id=? AND status IN ('confirmed','active')"); $activeRentals->execute([$uid]); $activeRentals = $activeRentals->fetchColumn();
$totalRentals = $db->prepare("SELECT COUNT(*) FROM rentals WHERE user_id=?"); $totalRentals->execute([$uid]); $totalRentals = $totalRentals->fetchColumn();
$totalSpent = $db->prepare("SELECT SUM(total) FROM rentals WHERE user_id=? AND payment_status='paid'"); $totalSpent->execute([$uid]); $totalSpent = $totalSpent->fetchColumn() ?? 0;

// Recent rentals
$recentRentals = $db->prepare("SELECT r.*, p.name as product_name, p.image FROM rentals r JOIN products p ON r.product_id = p.id WHERE r.user_id = ? ORDER BY r.created_at DESC LIMIT 5");
$recentRentals->execute([$uid]);
$recentRentals = $recentRentals->fetchAll();

// Notifications
$notifs = $db->prepare("SELECT * FROM notifications WHERE user_id=? ORDER BY created_at DESC LIMIT 5");
$notifs->execute([$uid]);
$notifs = $notifs->fetchAll();

// Featured products
$featured = $db->query("SELECT * FROM products WHERE is_featured=1 AND is_active=1 LIMIT 4")->fetchAll();
?>
<?php require_once '../includes/header.php'; ?>

<div class="app-layout">
  <!-- Sidebar -->
  <aside class="sidebar">
    <div class="sidebar-section">
      <div style="padding:16px;margin-bottom:8px">
        <div style="display:flex;align-items:center;gap:12px">
          <img src="<?= SITE_URL ?>/assets/images/<?= $_SESSION['profile_pic']??'default.png' ?>" style="width:44px;height:44px;border-radius:50%;border:2px solid var(--border);object-fit:cover" onerror="this.src='<?= SITE_URL ?>/assets/images/default.png'">
          <div>
            <div style="font-weight:600;font-size:14px"><?= sanitize($_SESSION['firstname']??'') ?></div>
            <div style="font-size:11px;color:var(--text-secondary)">Member</div>
          </div>
        </div>
      </div>
      <div class="sidebar-label">Main</div>
      <a href="home.php" class="sidebar-link active"><span class="icon">🏠</span> Dashboard</a>
      <a href="../products.php" class="sidebar-link"><span class="icon">💻</span> Browse Catalog</a>
      <a href="cart.php" class="sidebar-link"><span class="icon">🛒</span> My Cart</a>
    </div>
    <div class="sidebar-section">
      <div class="sidebar-label">Rentals</div>
      <a href="rentals.php" class="sidebar-link"><span class="icon">📦</span> My Rentals</a>
      <a href="rental-history.php" class="sidebar-link"><span class="icon">📋</span> History</a>
    </div>
    <div class="sidebar-section">
      <div class="sidebar-label">Account</div>
      <a href="notifications.php" class="sidebar-link"><span class="icon">🔔</span> Notifications <?php if($activeRentals): ?><span class="badge"><?= $activeRentals ?></span><?php endif; ?></a>
      <a href="profile.php" class="sidebar-link"><span class="icon">👤</span> Profile</a>
      <a href="../logout.php" class="sidebar-link"><span class="icon">🚪</span> Logout</a>
    </div>
  </aside>

  <main class="main-content">
    <div class="page-header">
      <h1 class="page-title">Hey, <?= sanitize($_SESSION['firstname']??'') ?>! 👋</h1>
      <p class="page-subtitle">Welcome back to RELAPSE</p>
    </div>

    <!-- Stats -->
    <div class="stats-grid mb-3">
      <div class="stat-card animate-in delay-1">
        <div class="stat-icon">📦</div>
        <div class="stat-value"><?= $activeRentals ?></div>
        <div class="stat-label">Active Rentals</div>
      </div>
      <div class="stat-card animate-in delay-2">
        <div class="stat-icon">📋</div>
        <div class="stat-value"><?= $totalRentals ?></div>
        <div class="stat-label">Total Rentals</div>
      </div>
      <div class="stat-card animate-in delay-3">
        <div class="stat-icon">💰</div>
        <div class="stat-value" style="font-size:22px"><?= formatPrice($totalSpent) ?></div>
        <div class="stat-label">Total Spent</div>
      </div>
      <div class="stat-card animate-in delay-4">
        <div class="stat-icon">⭐</div>
        <div class="stat-value">VIP</div>
        <div class="stat-label">Member Status</div>
      </div>
    </div>

    <div style="display:grid;grid-template-columns:1fr 1fr;gap:24px">
      <!-- Recent Rentals -->
      <div class="card animate-in delay-2">
        <div class="card-header" style="display:flex;justify-content:space-between;align-items:center">
          <h3 style="font-family:var(--font-display);font-size:18px;font-weight:600">Recent Rentals</h3>
          <a href="rentals.php" style="font-size:13px;color:var(--accent);text-decoration:none">View All →</a>
        </div>
        <div style="padding:8px 0">
          <?php if (empty($recentRentals)): ?>
          <div class="empty-state" style="padding:32px">
            <div class="empty-icon" style="font-size:40px">📦</div>
            <div class="empty-title" style="font-size:16px">No rentals yet</div>
            <a href="../products.php" class="btn btn-primary btn-sm mt-2">Start Renting</a>
          </div>
          <?php else: ?>
          <?php foreach ($recentRentals as $r): ?>
          <div style="display:flex;align-items:center;gap:12px;padding:14px 24px;border-bottom:1px solid var(--border)">
            <img src="<?= SITE_URL ?>/assets/images/<?= $r['image'] ?>" style="width:44px;height:44px;object-fit:contain;background:var(--bg-secondary);border-radius:8px;padding:4px" onerror="this.src='<?= SITE_URL ?>/assets/images/product-placeholder.jpg'">
            <div style="flex:1;min-width:0">
              <div style="font-size:14px;font-weight:500;white-space:nowrap;overflow:hidden;text-overflow:ellipsis"><?= sanitize($r['product_name']) ?></div>
              <div style="font-size:11px;color:var(--text-secondary)"><?= date('M d',strtotime($r['rental_start'])) ?> - <?= date('M d, Y',strtotime($r['rental_end'])) ?></div>
            </div>
            <span class="status-badge status-<?= $r['status'] ?>"><?= ucfirst($r['status']) ?></span>
          </div>
          <?php endforeach; ?>
          <?php endif; ?>
        </div>
      </div>

      <!-- Notifications -->
      <div class="card animate-in delay-3">
        <div class="card-header" style="display:flex;justify-content:space-between;align-items:center">
          <h3 style="font-family:var(--font-display);font-size:18px;font-weight:600">Notifications</h3>
          <a href="notifications.php" style="font-size:13px;color:var(--accent);text-decoration:none">View All →</a>
        </div>
        <div style="padding:8px 0">
          <?php if (empty($notifs)): ?>
          <div class="empty-state" style="padding:32px">
            <div class="empty-icon" style="font-size:40px">🔔</div>
            <div class="empty-title" style="font-size:16px">No notifications</div>
          </div>
          <?php else: ?>
          <?php foreach ($notifs as $n): ?>
          <div style="padding:14px 24px;border-bottom:1px solid var(--border);<?= !$n['is_read']?'background:rgba(232,64,64,0.03)':'' ?>">
            <div style="display:flex;justify-content:space-between;margin-bottom:4px">
              <span style="font-size:13px;font-weight:<?= !$n['is_read']?'600':'400' ?>"><?= sanitize($n['title']) ?></span>
              <?php if(!$n['is_read']): ?><span class="notif-dot"></span><?php endif; ?>
            </div>
            <p style="font-size:12px;color:var(--text-secondary);line-height:1.4"><?= sanitize($n['message']) ?></p>
            <div style="font-size:11px;color:var(--text-muted);margin-top:4px"><?= date('M d, g:ia', strtotime($n['created_at'])) ?></div>
          </div>
          <?php endforeach; ?>
          <?php endif; ?>
        </div>
      </div>
    </div>

    <!-- Quick Browse -->
    <div style="margin-top:32px" class="animate-in delay-4">
      <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:20px">
        <h3 style="font-family:var(--font-display);font-size:20px;font-weight:600">Featured Products</h3>
        <a href="../products.php" class="btn btn-outline btn-sm">Browse All →</a>
      </div>
      <div class="product-grid" style="grid-template-columns:repeat(auto-fill,minmax(220px,1fr))">
        <?php foreach ($featured as $p): ?>
        <div class="product-card" onclick="window.location='../product.php?id=<?= $p['id'] ?>'">
          <div class="product-card-img" style="height:150px">
            <img src="<?= SITE_URL ?>/assets/images/<?= $p['image'] ?>" alt="<?= sanitize($p['name']) ?>" onerror="this.src='<?= SITE_URL ?>/assets/images/product-placeholder.jpg'">
          </div>
          <div class="product-card-body">
            <div class="product-brand"><?= sanitize($p['brand']??'') ?></div>
            <div class="product-name" style="font-size:15px"><?= sanitize($p['name']) ?></div>
            <div class="product-card-footer" style="padding-top:12px">
              <div class="product-price" style="font-size:16px"><?= formatPrice($p['price_per_day']) ?><span>/day</span></div>
            </div>
          </div>
        </div>
        <?php endforeach; ?>
      </div>
    </div>
  </main>
</div>

<?php require_once '../includes/footer.php'; ?>
