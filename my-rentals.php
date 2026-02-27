<?php $page_title = 'My Rentals - RELAPSE'; include 'header.php'; ?>

<div class="main-content">
  <div style="font-family:var(--font-display);font-size:1.6rem;font-weight:700;margin-bottom:20px;" class="animate-fadeInUp">My Rentals</div>

  <!-- Status tabs -->
  <div style="display:flex;gap:8px;overflow-x:auto;padding-bottom:8px;margin-bottom:20px;scrollbar-width:none;" class="animate-fadeInUp delay-1">
    <button class="btn btn-primary btn-sm status-tab active" data-status="" onclick="filterStatus(this,'')">All</button>
    <button class="btn btn-outline btn-sm status-tab" data-status="pending" onclick="filterStatus(this,'pending')">Pending</button>
    <button class="btn btn-outline btn-sm status-tab" data-status="active" onclick="filterStatus(this,'active')">Active</button>
    <button class="btn btn-outline btn-sm status-tab" data-status="completed" onclick="filterStatus(this,'completed')">Completed</button>
    <button class="btn btn-outline btn-sm status-tab" data-status="cancelled" onclick="filterStatus(this,'cancelled')">Cancelled</button>
  </div>

  <div id="rentalsContainer">
    <div class="skeleton" style="height:80px;border-radius:var(--radius);margin-bottom:12px;"></div>
    <div class="skeleton" style="height:80px;border-radius:var(--radius);margin-bottom:12px;"></div>
    <div class="skeleton" style="height:80px;border-radius:var(--radius);margin-bottom:12px;"></div>
  </div>
</div>

<?php include 'footer.php'; ?>
<script>
let currentStatus = '';

function filterStatus(btn, status) {
  currentStatus = status;
  document.querySelectorAll('.status-tab').forEach(b => {
    b.classList.remove('btn-primary'); b.classList.add('btn-outline');
  });
  btn.classList.remove('btn-outline'); btn.classList.add('btn-primary');
  loadRentals();
}

async function loadRentals() {
  const container = document.getElementById('rentalsContainer');
  container.innerHTML = '<div class="loader"><div class="spinner"></div></div>';
  const res = await getRentals(currentStatus);
  const rentals = res.rentals || [];
  if (!rentals.length) {
    container.innerHTML = `<div class="empty-state">
      <div class="empty-icon"><svg width="40" height="40" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path d="M9 5H7a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2h-2"/><rect x="9" y="3" width="6" height="4" rx="1" ry="1"/></svg></div>
      <div class="empty-title">No rentals${currentStatus ? ' (' + currentStatus + ')' : ''}</div>
      <div class="empty-msg">Start browsing our gadgets to make your first rental!</div>
      <a href="browse.php" class="btn btn-primary btn-sm" style="margin-top:16px;">Browse Gadgets</a>
    </div>`;
    return;
  }
  container.innerHTML = rentals.map((r,i) => `
    <a href="rental-detail.php?id=${r.id}" class="rental-card ${r.status} delay-${Math.min(i+1,5)}">
      <div style="width:72px;height:72px;border-radius:var(--radius-sm);background:linear-gradient(135deg,#1a2744,#243357);display:flex;align-items:center;justify-content:center;flex-shrink:0;">
        <svg width="28" height="28" fill="none" stroke="rgba(255,255,255,0.4)" stroke-width="1.5" viewBox="0 0 24 24"><rect x="2" y="4" width="20" height="12" rx="2"/><path d="M8 20h8M12 16v4"/></svg>
      </div>
      <div class="rental-card-info">
        <div class="rental-card-name">${r.product_name}</div>
        <div class="rental-card-dates">📅 ${formatDate(r.rental_start)} → ${formatDate(r.rental_end)} · ${r.total_days} day${r.total_days>1?'s':''}</div>
        <div class="rental-card-footer">
          <span class="badge badge-${r.status}">${r.status.toUpperCase()}</span>
          <strong style="color:var(--accent);">${formatPrice(r.total_amount)}</strong>
        </div>
      </div>
    </a>`).join('');
  observeAnimations();
}
loadRentals();
</script>
</body>
</html>