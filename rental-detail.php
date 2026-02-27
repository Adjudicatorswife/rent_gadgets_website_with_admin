<?php
$page_title = 'Rental Detail - RELAPSE';
require_once dirname(__DIR__) . '/php/config.php';
requireLogin();
if (isAdmin()) { header('Location: ' . BASE_URL . '/admin/dashboard.php'); exit; }
$rental_id = intval($_GET['id'] ?? 0);
if (!$rental_id) { header('Location: my-rentals.php'); exit; }
$current_page = 'my-rentals';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
<title>Rental Detail - RELAPSE</title>
<link rel="stylesheet" href="<?= BASE_URL ?>/css/style.css">
<link href="https://fonts.googleapis.com/css2?family=Rajdhani:wght@400;500;600;700&family=Nunito:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>
<nav class="navbar">
  <a href="my-rentals.php" class="nav-btn" style="margin-right:auto;">
    <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><polyline points="15 18 9 12 15 6"/></svg>
  </a>
  <span style="font-family:var(--font-display);font-size:1.1rem;font-weight:700;color:white;letter-spacing:2px;position:absolute;left:50%;transform:translateX(-50%);">Rental Detail</span>
  <div></div>
</nav>
<div class="toast-container"></div>
<div id="content" class="main-content" style="padding-bottom:120px;">
  <div class="loader"><div class="spinner"></div></div>
</div>
<div id="actionBar" style="display:none;" class="sticky-footer">
  <div id="actionButtons"></div>
</div>

<!-- Cancel confirm modal -->
<div class="modal-overlay center" id="cancelModal" style="display:none;">
  <div class="modal center">
    <div class="modal-body" style="text-align:center;padding:32px 24px;">
      <div style="font-size:3rem;margin-bottom:12px;">⚠️</div>
      <div style="font-family:var(--font-display);font-size:1.3rem;font-weight:700;margin-bottom:8px;">Cancel Rental?</div>
      <div style="color:var(--text-muted);margin-bottom:24px;">This action cannot be undone.</div>
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
        <button class="btn btn-outline" onclick="closeModal('cancelModal')">Keep It</button>
        <button class="btn btn-danger" id="confirmCancelBtn" onclick="doCancel()">Yes, Cancel</button>
      </div>
    </div>
  </div>
</div>

<script src="<?= BASE_URL ?>/js/app.js"></script>
<script>
const RID = <?= $rental_id ?>;
let rentalData = null;

async function loadRental() {
  const res = await getRental(RID);
  if (!res.rental) { document.getElementById('content').innerHTML = '<div class="empty-state"><div class="empty-title">Rental not found</div></div>'; return; }
  rentalData = res.rental;
  const r = rentalData;
  document.getElementById('content').innerHTML = `
    <div class="animate-fadeInUp">
      <!-- Product Card -->
      <div class="card" style="margin-bottom:16px;">
        <div style="display:flex;gap:14px;align-items:center;">
          <div style="width:64px;height:64px;border-radius:12px;background:linear-gradient(135deg,#1a2744,#243357);display:flex;align-items:center;justify-content:center;flex-shrink:0;">
            <svg width="28" height="28" fill="none" stroke="rgba(255,255,255,0.4)" stroke-width="1.5" viewBox="0 0 24 24"><rect x="2" y="4" width="20" height="12" rx="2"/><path d="M8 20h8M12 16v4"/></svg>
          </div>
          <div style="flex:1;">
            <div style="font-family:var(--font-display);font-size:1.1rem;font-weight:700;">${r.product_name}</div>
            <div style="color:var(--text-muted);font-size:0.8rem;">${r.brand||''} ${r.model||''}</div>
            <div style="margin-top:6px;display:flex;gap:8px;align-items:center;">
              <span class="badge badge-${r.status}">${r.status.toUpperCase()}</span>
              <span class="badge badge-${r.payment_status}">${r.payment_status.toUpperCase()}</span>
            </div>
          </div>
        </div>
      </div>

      <!-- Rental Info -->
      <div class="card animate-fadeInUp delay-1">
        <div class="card-title" style="margin-bottom:16px;">Rental Details</div>
        <div style="display:grid;gap:12px;">
          <div style="display:flex;justify-content:space-between;padding-bottom:10px;border-bottom:1px solid var(--gray-100);">
            <span style="color:var(--text-muted);font-size:0.9rem;">Rental ID</span>
            <span style="font-weight:700;">#${r.id.toString().padStart(5,'0')}</span>
          </div>
          <div style="display:flex;justify-content:space-between;padding-bottom:10px;border-bottom:1px solid var(--gray-100);">
            <span style="color:var(--text-muted);font-size:0.9rem;">Start Date</span>
            <span style="font-weight:600;">${formatDate(r.rental_start)}</span>
          </div>
          <div style="display:flex;justify-content:space-between;padding-bottom:10px;border-bottom:1px solid var(--gray-100);">
            <span style="color:var(--text-muted);font-size:0.9rem;">End Date</span>
            <span style="font-weight:600;">${formatDate(r.rental_end)}</span>
          </div>
          <div style="display:flex;justify-content:space-between;padding-bottom:10px;border-bottom:1px solid var(--gray-100);">
            <span style="color:var(--text-muted);font-size:0.9rem;">Duration</span>
            <span style="font-weight:600;">${r.total_days} day${r.total_days>1?'s':''}</span>
          </div>
          <div style="display:flex;justify-content:space-between;padding-bottom:10px;border-bottom:1px solid var(--gray-100);">
            <span style="color:var(--text-muted);font-size:0.9rem;">Rate per Day</span>
            <span style="font-weight:600;">${formatPrice(r.price_per_day)}</span>
          </div>
          <div style="display:flex;justify-content:space-between;padding-bottom:10px;border-bottom:1px solid var(--gray-100);">
            <span style="color:var(--text-muted);font-size:0.9rem;">Payment Method</span>
            <span style="font-weight:600;">${(r.payment_method||'').toUpperCase()}</span>
          </div>
          <div style="display:flex;justify-content:space-between;">
            <span style="color:var(--text-muted);font-size:0.9rem;">Total Amount</span>
            <span style="font-weight:700;font-size:1.1rem;color:var(--accent);">${formatPrice(r.total_amount)}</span>
          </div>
        </div>
      </div>

      ${r.delivery_address ? `<div class="card animate-fadeInUp delay-2">
        <div class="card-title" style="margin-bottom:12px;">Delivery Address</div>
        <p style="color:var(--text-muted);font-size:0.9rem;line-height:1.6;">${r.delivery_address}</p>
      </div>` : ''}

      ${r.notes ? `<div class="card animate-fadeInUp delay-3">
        <div class="card-title" style="margin-bottom:12px;">Notes</div>
        <p style="color:var(--text-muted);font-size:0.9rem;">${r.notes}</p>
      </div>` : ''}

      <div style="color:var(--text-muted);font-size:0.8rem;text-align:center;padding:8px 0;">Booked on ${formatDate(r.created_at)}</div>
    </div>`;

  // Action buttons
  const actions = [];
  if (r.payment_status === 'unpaid' && r.status === 'pending') {
    actions.push(`<button class="btn btn-success btn-full" onclick="payNow()" style="margin-bottom:10px;">Pay Now - ${formatPrice(r.total_amount)}</button>`);
  }
  if (['pending','active'].includes(r.status)) {
    actions.push(`<button class="btn btn-outline btn-full" onclick="openModal('cancelModal')">Cancel Rental</button>`);
  }
  if (actions.length) {
    document.getElementById('actionBar').style.display = 'block';
    document.getElementById('actionButtons').innerHTML = actions.join('');
  }
}

async function payNow() {
  const res = await apiCall(`${API}/rentals.php`, 'POST', { action: 'pay', id: RID });
  if (res.success) { showToast('Payment confirmed!', 'success'); setTimeout(() => loadRental(), 1000); }
  else showToast('Payment failed', 'error');
}

async function doCancel() {
  const btn = document.getElementById('confirmCancelBtn');
  btn.disabled = true; btn.innerHTML = '...';
  const res = await cancelRental(RID);
  if (res.success) { closeModal('cancelModal'); showToast('Rental cancelled', 'info'); setTimeout(() => loadRental(), 800); }
  else { showToast(res.error || 'Cancel failed', 'error'); btn.disabled = false; btn.innerHTML = 'Yes, Cancel'; }
}

loadRental();
</script>
</body>
</html>