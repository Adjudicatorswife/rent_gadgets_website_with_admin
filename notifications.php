<?php
$page = 'notifications';
$title = 'Notifications - RELAPSE';
require_once '../includes/config.php';
requireLogin();
$db = getDB();
$uid = $_SESSION['user_id'];

// Mark all as read
if (isset($_GET['read_all'])) {
    $db->prepare("UPDATE notifications SET is_read=1 WHERE user_id=?")->execute([$uid]);
    header('Location: notifications.php'); exit;
}

$notifs = $db->prepare("SELECT * FROM notifications WHERE user_id=? ORDER BY created_at DESC");
$notifs->execute([$uid]);
$notifs = $notifs->fetchAll();

$typeIcons = ['rental'=>'📦','payment'=>'💰','system'=>'🔔','promo'=>'🎉'];
?>
<?php require_once '../includes/header.php'; ?>

<div class="app-layout">
  <aside class="sidebar">
    <div class="sidebar-section">
      <div class="sidebar-label">Main</div>
      <a href="home.php" class="sidebar-link"><span class="icon">🏠</span> Dashboard</a>
      <a href="../products.php" class="sidebar-link"><span class="icon">💻</span> Browse Catalog</a>
      <a href="cart.php" class="sidebar-link"><span class="icon">🛒</span> My Cart</a>
    </div>
    <div class="sidebar-section">
      <div class="sidebar-label">Rentals</div>
      <a href="rentals.php" class="sidebar-link"><span class="icon">📦</span> My Rentals</a>
    </div>
    <div class="sidebar-section">
      <div class="sidebar-label">Account</div>
      <a href="notifications.php" class="sidebar-link active"><span class="icon">🔔</span> Notifications</a>
      <a href="profile.php" class="sidebar-link"><span class="icon">👤</span> Profile</a>
      <a href="../logout.php" class="sidebar-link"><span class="icon">🚪</span> Logout</a>
    </div>
  </aside>

  <main class="main-content">
    <div class="page-header" style="display:flex;justify-content:space-between;align-items:flex-start">
      <div>
        <h1 class="page-title">Notifications 🔔</h1>
        <p class="page-subtitle">Stay updated with your rentals</p>
      </div>
      <a href="notifications.php?read_all=1" class="btn btn-ghost btn-sm">Mark all as read</a>
    </div>

    <?php if (empty($notifs)): ?>
    <div class="empty-state">
      <div class="empty-icon">🔔</div>
      <div class="empty-title">No notifications</div>
      <p class="empty-text">You're all caught up!</p>
    </div>
    <?php else: ?>
    <div style="display:grid;gap:8px">
      <?php foreach ($notifs as $n): ?>
      <div class="card animate-in" style="<?= !$n['is_read']?'border-color:rgba(232,64,64,0.3)':'' ?>" onclick="markRead(<?= $n['id'] ?>, this)">
        <div class="card-body" style="display:flex;gap:16px;align-items:flex-start;cursor:pointer">
          <div style="width:44px;height:44px;border-radius:50%;background:rgba(255,255,255,0.06);display:flex;align-items:center;justify-content:center;font-size:20px;flex-shrink:0">
            <?= $typeIcons[$n['type']] ?? '🔔' ?>
          </div>
          <div style="flex:1">
            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:4px">
              <span style="font-weight:<?= !$n['is_read']?'600':'400' ?>;font-size:14px"><?= sanitize($n['title']) ?></span>
              <?php if(!$n['is_read']): ?><span class="notif-dot"></span><?php endif; ?>
            </div>
            <p style="color:var(--text-secondary);font-size:13px;line-height:1.5"><?= sanitize($n['message']) ?></p>
            <div style="font-size:11px;color:var(--text-muted);margin-top:6px"><?= date('M d, Y · g:ia', strtotime($n['created_at'])) ?></div>
          </div>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
    <?php endif; ?>
  </main>
</div>

<script>
async function markRead(id, el) {
  await API.post('<?= SITE_URL ?>/api/notifications.php', { action: 'read', notif_id: id });
  el.style.borderColor = '';
  el.querySelector('.notif-dot')?.remove();
}
</script>

<?php require_once '../includes/footer.php'; ?>
