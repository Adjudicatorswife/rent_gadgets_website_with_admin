<?php
$page = 'rentals';
$title = 'My Rentals - RELAPSE';
require_once '../includes/config.php';
requireLogin();
$db = getDB();
$uid = $_SESSION['user_id'];

$status = $_GET['status'] ?? '';
$where = "WHERE r.user_id = ?";
$params = [$uid];
if ($status) { $where .= " AND r.status = ?"; $params[] = $status; }

$stmt = $db->prepare("SELECT r.*, p.name as product_name, p.image, p.brand FROM rentals r JOIN products p ON r.product_id = p.id $where ORDER BY r.created_at DESC");
$stmt->execute($params);
$rentals = $stmt->fetchAll();

$statusCounts = $db->prepare("SELECT status, COUNT(*) as cnt FROM rentals WHERE user_id=? GROUP BY status");
$statusCounts->execute([$uid]);
$counts = [];
foreach ($statusCounts->fetchAll() as $row) $counts[$row['status']] = $row['cnt'];
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
      <a href="rentals.php" class="sidebar-link active"><span class="icon">📦</span> My Rentals</a>
      <a href="rental-history.php" class="sidebar-link"><span class="icon">📋</span> History</a>
    </div>
    <div class="sidebar-section">
      <div class="sidebar-label">Account</div>
      <a href="notifications.php" class="sidebar-link"><span class="icon">🔔</span> Notifications</a>
      <a href="profile.php" class="sidebar-link"><span class="icon">👤</span> Profile</a>
      <a href="../logout.php" class="sidebar-link"><span class="icon">🚪</span> Logout</a>
    </div>
  </aside>

  <main class="main-content">
    <div class="page-header">
      <h1 class="page-title">My Rentals</h1>
      <p class="page-subtitle">Track and manage your rentals</p>
    </div>

    <!-- Status Filter -->
    <div class="filter-bar mb-3">
      <a href="rentals.php" class="filter-chip <?= !$status?'active':'' ?>">All (<?= array_sum($counts) ?>)</a>
      <a href="rentals.php?status=pending" class="filter-chip <?= $status==='pending'?'active':'' ?>">Pending (<?= $counts['pending']??0 ?>)</a>
      <a href="rentals.php?status=confirmed" class="filter-chip <?= $status==='confirmed'?'active':'' ?>">Confirmed (<?= $counts['confirmed']??0 ?>)</a>
      <a href="rentals.php?status=active" class="filter-chip <?= $status==='active'?'active':'' ?>">Active (<?= $counts['active']??0 ?>)</a>
      <a href="rentals.php?status=returned" class="filter-chip <?= $status==='returned'?'active':'' ?>">Returned (<?= $counts['returned']??0 ?>)</a>
      <a href="rentals.php?status=cancelled" class="filter-chip <?= $status==='cancelled'?'active':'' ?>">Cancelled (<?= $counts['cancelled']??0 ?>)</a>
    </div>

    <?php if (empty($rentals)): ?>
    <div class="empty-state">
      <div class="empty-icon">📦</div>
      <div class="empty-title">No rentals found</div>
      <p class="empty-text">You haven't made any rentals yet.</p>
      <a href="../products.php" class="btn btn-primary mt-2">Browse Catalog</a>
    </div>
    <?php else: ?>
    <div style="display:grid;gap:16px">
      <?php foreach ($rentals as $r): ?>
      <div class="card animate-in">
        <div class="card-body" style="display:grid;grid-template-columns:auto 1fr auto;gap:20px;align-items:center">
          <img src="<?= SITE_URL ?>/assets/images/<?= $r['image'] ?>" style="width:80px;height:80px;object-fit:contain;background:var(--bg-secondary);border-radius:10px;padding:8px" onerror="this.src='<?= SITE_URL ?>/assets/images/product-placeholder.jpg'">
          <div>
            <div style="font-size:12px;color:var(--accent);margin-bottom:4px"><?= sanitize($r['brand']??'') ?> · #<?= str_pad($r['id'],6,'0',STR_PAD_LEFT) ?></div>
            <div style="font-weight:600;font-size:16px;margin-bottom:8px"><?= sanitize($r['product_name']) ?></div>
            <div style="display:flex;flex-wrap:wrap;gap:16px;font-size:13px;color:var(--text-secondary)">
              <span>📅 <?= date('M d',strtotime($r['rental_start'])) ?> – <?= date('M d, Y',strtotime($r['rental_end'])) ?></span>
              <span>⏱️ <?= $r['days'] ?> day(s)</span>
              <span>💰 <?= formatPrice($r['total']) ?></span>
              <span class="status-badge status-<?= $r['payment_status'] === 'paid' ? 'active' : 'pending' ?>"><?= ucfirst($r['payment_status']) ?></span>
            </div>
          </div>
          <div style="display:flex;flex-direction:column;align-items:flex-end;gap:10px">
            <span class="status-badge status-<?= $r['status'] ?>"><?= ucfirst($r['status']) ?></span>
            <?php if ($r['status'] === 'pending'): ?>
            <button class="btn btn-outline btn-sm" onclick="cancelRental(<?= $r['id'] ?>)">Cancel</button>
            <?php endif; ?>
            <a href="rental-detail.php?id=<?= $r['id'] ?>" class="btn btn-ghost btn-sm">Details</a>
          </div>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
    <?php endif; ?>
  </main>
</div>

<script>
async function cancelRental(id) {
  confirm('Are you sure you want to cancel this rental?', async () => {
    const res = await API.post('<?= SITE_URL ?>/api/rentals.php', { action: 'cancel', rental_id: id });
    if (res.success) { Toast.success('Rental cancelled'); setTimeout(()=>location.reload(), 1000); }
    else Toast.error(res.message || 'Failed to cancel');
  });
}
</script>

<?php require_once '../includes/footer.php'; ?>
