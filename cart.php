<?php
$page = 'cart';
$title = 'My Cart - RELAPSE';
require_once '../includes/config.php';
requireLogin();
$db = getDB();
$uid = $_SESSION['user_id'];

$stmt = $db->prepare("SELECT c.*, p.name as product_name, p.image, p.brand, p.price_per_day, p.deposit, p.available FROM cart c JOIN products p ON c.product_id = p.id WHERE c.user_id = ?");
$stmt->execute([$uid]);
$items = $stmt->fetchAll();

$subtotal = 0;
$totalDeposit = 0;
foreach ($items as $item) {
    $days = max(1, intval($item['days']));
    $subtotal += $item['price_per_day'] * $days;
    $totalDeposit += $item['deposit'];
}
?>
<?php require_once '../includes/header.php'; ?>

<div class="app-layout">
  <aside class="sidebar">
    <div class="sidebar-section">
      <div class="sidebar-label">Main</div>
      <a href="home.php" class="sidebar-link"><span class="icon">🏠</span> Dashboard</a>
      <a href="../products.php" class="sidebar-link"><span class="icon">💻</span> Browse Catalog</a>
      <a href="cart.php" class="sidebar-link active"><span class="icon">🛒</span> My Cart</a>
    </div>
    <div class="sidebar-section">
      <div class="sidebar-label">Rentals</div>
      <a href="rentals.php" class="sidebar-link"><span class="icon">📦</span> My Rentals</a>
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
      <h1 class="page-title">Rental Cart 🛒</h1>
      <p class="page-subtitle"><?= count($items) ?> item(s) in your cart</p>
    </div>

    <?php if (empty($items)): ?>
    <div class="empty-state">
      <div class="empty-icon">🛒</div>
      <div class="empty-title">Your cart is empty</div>
      <p class="empty-text">Browse our catalog and add gadgets to rent</p>
      <a href="../products.php" class="btn btn-primary mt-2">Browse Catalog</a>
    </div>
    <?php else: ?>
    <div style="display:grid;grid-template-columns:1fr 360px;gap:24px;align-items:start">
      <div>
        <?php foreach ($items as $item): ?>
        <?php $days = max(1, intval($item['days'])); $lineTotal = $item['price_per_day'] * $days; ?>
        <div class="card mb-2 animate-in" id="cart-item-<?= $item['id'] ?>">
          <div class="card-body" style="display:flex;gap:16px;align-items:center">
            <img src="<?= SITE_URL ?>/assets/images/<?= $item['image'] ?>" style="width:70px;height:70px;object-fit:contain;background:var(--bg-secondary);border-radius:8px;padding:6px" onerror="this.src='<?= SITE_URL ?>/assets/images/product-placeholder.jpg'">
            <div style="flex:1">
              <div style="font-size:11px;color:var(--accent);margin-bottom:2px"><?= sanitize($item['brand']??'') ?></div>
              <div style="font-weight:600;margin-bottom:4px"><?= sanitize($item['product_name']) ?></div>
              <div style="font-size:12px;color:var(--text-secondary)">
                <?= $item['rental_start'] ? date('M d',strtotime($item['rental_start'])).' – '.date('M d, Y',strtotime($item['rental_end'])) : 'No dates set' ?>
                · <?= $days ?> day(s)
              </div>
            </div>
            <div style="text-align:right">
              <div style="font-family:var(--font-display);font-size:18px;font-weight:700"><?= formatPrice($lineTotal) ?></div>
              <div style="font-size:11px;color:var(--text-secondary);margin-bottom:8px"><?= formatPrice($item['price_per_day']) ?>/day</div>
              <button class="btn btn-ghost btn-sm" style="color:var(--accent)" onclick="removeItem(<?= $item['id'] ?>)">Remove</button>
            </div>
          </div>
        </div>
        <?php endforeach; ?>
      </div>

      <!-- Order Summary -->
      <div class="card" style="position:sticky;top:80px">
        <div class="card-header">
          <h3 style="font-family:var(--font-display);font-size:18px;font-weight:600">Order Summary</h3>
        </div>
        <div class="card-body">
          <div class="rental-summary">
            <div class="rental-row">
              <span>Subtotal (<?= count($items) ?> items)</span>
              <span><?= formatPrice($subtotal) ?></span>
            </div>
            <div class="rental-row">
              <span>Total Deposit</span>
              <span><?= formatPrice($totalDeposit) ?></span>
            </div>
            <div class="rental-row" style="border-bottom:none;padding-top:12px">
              <span style="font-weight:600">Grand Total</span>
              <span style="font-family:var(--font-display);font-size:20px;font-weight:700;color:var(--accent)"><?= formatPrice($subtotal + $totalDeposit) ?></span>
            </div>
          </div>

          <div class="form-group mt-2">
            <label class="form-label">Payment Method</label>
            <select class="form-control" id="payment_method">
              <option value="cash">💵 Cash on Pickup</option>
              <option value="gcash">📱 GCash</option>
              <option value="maya">📱 Maya</option>
              <option value="bank_transfer">🏦 Bank Transfer</option>
            </select>
          </div>

          <button class="btn btn-primary btn-block btn-lg mt-1" onclick="checkoutAll()">
            Confirm All Rentals ⚡
          </button>
          <a href="../products.php" class="btn btn-ghost btn-block mt-1">Continue Browsing</a>
        </div>
      </div>
    </div>
    <?php endif; ?>
  </main>
</div>

<script>
async function removeItem(id) {
  const res = await Cart.remove(id);
  if (res.success) {
    const el = document.getElementById('cart-item-'+id);
    if (el) { el.style.opacity='0'; el.style.transform='translateX(20px)'; el.style.transition='all 0.3s'; setTimeout(()=>location.reload(), 300); }
  }
}
async function checkoutAll() {
  const pm = document.getElementById('payment_method').value;
  const res = await API.post('<?= SITE_URL ?>/api/rentals.php', { action: 'checkout_cart', payment_method: pm });
  if (res.success) {
    Toast.success('All rentals confirmed! Redirecting...');
    setTimeout(() => window.location = '<?= SITE_URL ?>/user/rentals.php', 1500);
  } else {
    Toast.error(res.message || 'Checkout failed');
  }
}
</script>

<?php require_once '../includes/footer.php'; ?>
