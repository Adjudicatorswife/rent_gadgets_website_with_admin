<?php
$page_title = 'Product - RELAPSE';
require_once dirname(__DIR__) . '/php/config.php';
requireLogin();
if (isAdmin()) { header('Location: ' . BASE_URL . '/admin/dashboard.php'); exit; }
$product_id = intval($_GET['id'] ?? 0);
if (!$product_id) { header('Location: browse.php'); exit; }
$current_page = 'browse';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
<title>Product - RELAPSE</title>
<link rel="stylesheet" href="<?= BASE_URL ?>/css/style.css">
<link href="https://fonts.googleapis.com/css2?family=Rajdhani:wght@400;500;600;700&family=Nunito:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>

<nav class="navbar">
  <a href="browse.php" class="nav-btn" title="Back" style="margin-right:auto;">
    <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><polyline points="15 18 9 12 15 6"/></svg>
  </a>
  <span style="font-family:var(--font-display);font-size:1.1rem;font-weight:700;color:white;letter-spacing:2px;position:absolute;left:50%;transform:translateX(-50%);">RELAPSE</span>
  <div></div>
</nav>

<div class="toast-container"></div>

<!-- Product image placeholder -->
<div class="product-detail-img" id="productImg">
  <div class="spinner"></div>
</div>

<div id="productContent">
  <div class="loader"><div class="spinner"></div></div>
</div>

<!-- Sticky rent button -->
<div class="sticky-footer" id="stickyFooter" style="display:none;">
  <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:12px;">
    <div>
      <div style="font-size:0.75rem;color:var(--text-muted);">Price per day</div>
      <div id="stickyPrice" style="font-family:var(--font-display);font-size:1.5rem;font-weight:700;color:var(--accent);"></div>
    </div>
    <div id="availabilityBadge"></div>
  </div>
  <button class="btn btn-primary btn-full" onclick="openRentModal()">Rent This Gadget</button>
</div>

<!-- Rent Modal -->
<div class="modal-overlay" id="rentModal" style="display:none;">
  <div class="modal">
    <div class="modal-header">
      <div class="modal-title">Rent This Gadget</div>
      <button class="modal-close" onclick="closeModal('rentModal')">✕</button>
    </div>
    <div class="modal-body">
      <div class="date-range-picker">
        <div class="form-group">
          <label class="form-label">Start Date</label>
          <input type="date" id="startDate" class="form-control" min="<?= date('Y-m-d') ?>" onchange="calcTotal()">
        </div>
        <div class="form-group">
          <label class="form-label">End Date</label>
          <input type="date" id="endDate" class="form-control" min="<?= date('Y-m-d', strtotime('+1 day')) ?>" onchange="calcTotal()">
        </div>
      </div>
      <div class="form-group">
        <label class="form-label">Delivery Address</label>
        <textarea id="deliveryAddr" class="form-control" rows="2" placeholder="Enter full delivery address..."></textarea>
      </div>
      <div class="form-group">
        <label class="form-label">Payment Method</label>
        <select id="paymentMethod" class="form-control">
          <option value="gcash">GCash</option>
          <option value="maya">Maya</option>
          <option value="cod">Cash on Delivery</option>
          <option value="bank">Bank Transfer</option>
        </select>
      </div>
      <div class="form-group">
        <label class="form-label">Notes (optional)</label>
        <input type="text" id="rentalNotes" class="form-control" placeholder="Any special instructions?">
      </div>
      <div class="rental-summary" id="rentalSummary" style="display:none;">
        <div class="rental-summary-row"><span>Duration</span><span id="sumDays">—</span></div>
        <div class="rental-summary-row"><span>Rate</span><span id="sumRate">—</span></div>
        <div class="rental-summary-row total"><span>Total</span><span id="sumTotal" style="color:var(--accent);">—</span></div>
      </div>
      <button class="btn btn-primary btn-full" id="confirmRentBtn" onclick="submitRental()">Confirm Rental</button>
    </div>
  </div>
</div>

<script src="<?= BASE_URL ?>/js/app.js"></script>
<script>
let productData = null;
const PID = <?= $product_id ?>;

async function loadProduct() {
  const res = await getProduct(PID);
  if (!res.product) { document.getElementById('productContent').innerHTML = '<div class="empty-state"><div class="empty-title">Product not found</div></div>'; return; }
  productData = res.product;
  const p = productData;

  // Image
  const imgEl = document.getElementById('productImg');
  const imgSrc = productImageSrc(p.image);
  if (imgSrc) {
    imgEl.innerHTML = `<img src="${imgSrc}" alt="${p.name}" style="width:100%;height:260px;object-fit:cover;">`;
  } else {
    imgEl.innerHTML = `<svg width="80" height="80" fill="none" stroke="rgba(255,255,255,0.15)" stroke-width="1" viewBox="0 0 24 24"><rect x="2" y="4" width="20" height="12" rx="2"/><path d="M8 20h8M12 16v4"/></svg>`;
  }

  // Content
  const specs = p.specs || {};
  const specHtml = Object.entries(specs).map(([k,v]) => `<div class="spec-item"><div class="key">${k}</div><div class="val">${v}</div></div>`).join('');
  const reviews = p.reviews || [];
  const reviewHtml = reviews.length ? reviews.map(r => `
    <div style="padding:14px 0;border-bottom:1px solid var(--gray-100);">
      <div style="display:flex;align-items:center;gap:8px;margin-bottom:6px;">
        <div style="width:32px;height:32px;border-radius:50%;background:var(--primary);display:flex;align-items:center;justify-content:center;color:white;font-weight:700;font-size:0.8rem;">${r.full_name.charAt(0)}</div>
        <div><div style="font-weight:700;font-size:0.9rem;">${r.full_name}</div><div style="color:var(--text-muted);font-size:0.75rem;">${timeAgo(r.created_at)}</div></div>
        <div style="margin-left:auto;">★ ${r.rating}/5</div>
      </div>
      <div style="font-size:0.9rem;color:var(--text-muted);">${r.comment||''}</div>
    </div>`).join('') : '<div style="color:var(--text-muted);font-size:0.9rem;padding:16px 0;">No reviews yet. Be the first to review!</div>';

  document.getElementById('productContent').innerHTML = `
    <div class="detail-header animate-fadeInUp">
      <div style="display:flex;align-items:flex-start;justify-content:space-between;margin-bottom:6px;">
        <div>
          <div style="color:var(--text-muted);font-size:0.8rem;font-weight:600;letter-spacing:1px;text-transform:uppercase;">${p.brand||''} · ${p.category_name||''}</div>
          <div style="font-family:var(--font-display);font-size:1.5rem;font-weight:700;line-height:1.2;">${p.name}</div>
        </div>
        <div class="product-condition" style="flex-shrink:0;margin-top:4px;">
          <div class="condition-dot ${(p.condition_rating||'').toLowerCase()}"></div>
          <span style="font-size:0.8rem;color:var(--text-muted);">${p.condition_rating||''}</span>
        </div>
      </div>
      <div style="display:flex;align-items:center;gap:12px;">
        <div class="detail-price">${formatPrice(p.price_per_day)}<span>/day</span></div>
        <div>${p.avg_rating > 0 ? `<span style="color:var(--gold);">★</span> <span style="font-weight:700;">${parseFloat(p.avg_rating).toFixed(1)}</span> <span style="color:var(--text-muted);font-size:0.8rem;">(${reviews.length})</span>` : ''}</div>
      </div>
    </div>

    ${p.description ? `<div class="detail-section animate-fadeInUp delay-1">
      <div class="detail-section-title">Description</div>
      <p style="color:var(--text-muted);line-height:1.7;font-size:0.9rem;">${p.description}</p>
    </div>` : ''}

    ${specHtml ? `<div class="detail-section animate-fadeInUp delay-2">
      <div class="detail-section-title">Specifications</div>
      <div class="specs-grid">${specHtml}</div>
    </div>` : ''}

    <div class="detail-section animate-fadeInUp delay-3">
      <div class="detail-section-title">Reviews (${reviews.length})</div>
      ${reviewHtml}
    </div>
    <div style="height:100px;"></div>`;

  // Sticky footer
  document.getElementById('stickyPrice').textContent = formatPrice(p.price_per_day) + '/day';
  document.getElementById('availabilityBadge').innerHTML = p.is_available && p.stock > 0
    ? `<span class="badge badge-active">✓ Available</span>`
    : `<span class="badge badge-cancelled">✗ Unavailable</span>`;
  document.getElementById('stickyFooter').style.display = 'block';
}

function openRentModal() {
  if (!productData?.is_available) { showToast('This product is not available','error'); return; }
  // Set min date
  const today = new Date().toISOString().split('T')[0];
  const tomorrow = new Date(Date.now() + 86400000).toISOString().split('T')[0];
  document.getElementById('startDate').value = today;
  document.getElementById('endDate').value = tomorrow;
  calcTotal();
  openModal('rentModal');
}

function calcTotal() {
  const start = document.getElementById('startDate').value;
  const end = document.getElementById('endDate').value;
  if (!start || !end || !productData) return;
  const days = Math.round((new Date(end) - new Date(start)) / 86400000);
  if (days <= 0) { document.getElementById('rentalSummary').style.display = 'none'; return; }
  const total = days * productData.price_per_day;
  document.getElementById('sumDays').textContent = `${days} day${days > 1 ? 's' : ''}`;
  document.getElementById('sumRate').textContent = formatPrice(productData.price_per_day) + '/day';
  document.getElementById('sumTotal').textContent = formatPrice(total);
  document.getElementById('rentalSummary').style.display = 'block';
}

async function submitRental() {
  const start = document.getElementById('startDate').value;
  const end = document.getElementById('endDate').value;
  const addr = document.getElementById('deliveryAddr').value.trim();
  const method = document.getElementById('paymentMethod').value;
  const notes = document.getElementById('rentalNotes').value.trim();
  if (!start || !end) { showToast('Please select rental dates','error'); return; }
  if (!addr) { showToast('Please enter delivery address','error'); return; }
  const btn = document.getElementById('confirmRentBtn');
  btn.disabled = true; btn.innerHTML = '<div class="spinner" style="width:20px;height:20px;border-width:2px;"></div>';
  const res = await createRental({ product_id: PID, rental_start: start, rental_end: end, delivery_address: addr, payment_method: method, notes });
  if (res.success) {
    closeModal('rentModal');
    showToast('Rental created! Total: ' + formatPrice(res.total), 'success');
    setTimeout(() => window.location.href = 'my-rentals.php', 1500);
  } else {
    showToast(res.error || 'Failed to create rental', 'error');
    btn.disabled = false; btn.innerHTML = 'Confirm Rental';
  }
}

loadProduct();
</script>
</body>
</html>