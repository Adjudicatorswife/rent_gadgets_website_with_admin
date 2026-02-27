<?php $admin_page='dashboard'; $admin_title='Dashboard'; include 'header.php'; ?>

<div class="stats-grid" id="statsGrid">
  <div class="stat-box primary" style="animation:fadeInUp 0.5s ease both;">
    <div class="stat-box-icon"><svg width="22" height="22" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M9 5H7a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2h-2"/><rect x="9" y="3" width="6" height="4" rx="1" ry="1"/></svg></div>
    <div class="stat-box-value" id="s_total_rentals">—</div>
    <div class="stat-box-label">Total Rentals</div>
  </div>
  <div class="stat-box accent" style="animation:fadeInUp 0.5s 0.1s ease both;opacity:0;animation-fill-mode:both;">
    <div class="stat-box-icon"><svg width="22" height="22" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="1" y="4" width="22" height="16" rx="2" ry="2"/><line x1="1" y1="10" x2="23" y2="10"/></svg></div>
    <div class="stat-box-value" id="s_revenue">—</div>
    <div class="stat-box-label">Total Revenue</div>
  </div>
  <div class="stat-box teal" style="animation:fadeInUp 0.5s 0.2s ease both;opacity:0;animation-fill-mode:both;">
    <div class="stat-box-icon"><svg width="22" height="22" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/></svg></div>
    <div class="stat-box-value" id="s_total_users">—</div>
    <div class="stat-box-label">Total Users</div>
  </div>
  <div class="stat-box gold" style="animation:fadeInUp 0.5s 0.3s ease both;opacity:0;animation-fill-mode:both;">
    <div class="stat-box-icon"><svg width="22" height="22" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="2" y="4" width="20" height="12" rx="2"/><path d="M8 20h8M12 16v4"/></svg></div>
    <div class="stat-box-value" id="s_total_products">—</div>
    <div class="stat-box-label">Products</div>
  </div>
</div>

<div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;margin-bottom:24px;">
  <!-- Revenue Chart -->
  <div class="admin-card">
    <div class="admin-card-header">
      <div class="admin-card-title">Monthly Revenue</div>
    </div>
    <div class="chart-bar" id="revenueChart">
      <div class="loader" style="width:100%;"><div class="spinner" style="width:24px;height:24px;border-width:2px;"></div></div>
    </div>
  </div>
  <!-- Quick Stats -->
  <div class="admin-card">
    <div class="admin-card-header"><div class="admin-card-title">Rental Status</div></div>
    <div id="statusChart">
      <div class="loader"><div class="spinner" style="width:24px;height:24px;border-width:2px;"></div></div>
    </div>
  </div>
</div>

<!-- Recent Rentals -->
<div class="admin-card">
  <div class="admin-card-header">
    <div class="admin-card-title">Recent Rentals</div>
    <a href="rentals.php" class="btn btn-outline btn-sm">View All</a>
  </div>
  <div style="overflow-x:auto;">
    <table class="admin-table" id="recentTable">
      <thead><tr><th>ID</th><th>User</th><th>Product</th><th>Dates</th><th>Amount</th><th>Status</th><th>Action</th></tr></thead>
      <tbody id="recentBody"><tr><td colspan="7" style="text-align:center;padding:30px;color:var(--text-muted);">Loading...</td></tr></tbody>
    </table>
  </div>
</div>

<script>
async function loadDashboard() {
  const res = await apiCall(`${API}/user.php?action=admin_stats`);
  if (res.stats) {
    const s = res.stats;
    document.getElementById('s_total_rentals').textContent = s.total_rentals || 0;
    document.getElementById('s_revenue').textContent = formatPrice(s.revenue || 0);
    document.getElementById('s_total_users').textContent = s.total_users || 0;
    document.getElementById('s_total_products').textContent = s.total_products || 0;

    // Status donut chart (simple)
    document.getElementById('statusChart').innerHTML = `
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;">
        <div style="background:#f8f9fa;border-radius:10px;padding:14px;text-align:center;">
          <div style="font-size:1.8rem;font-weight:700;font-family:var(--font-display);color:#f5c518;">${s.pending_rentals||0}</div>
          <div style="font-size:0.8rem;color:var(--text-muted);font-weight:600;">Pending</div>
        </div>
        <div style="background:#f8f9fa;border-radius:10px;padding:14px;text-align:center;">
          <div style="font-size:1.8rem;font-weight:700;font-family:var(--font-display);color:#00d4aa;">${s.active_rentals||0}</div>
          <div style="font-size:0.8rem;color:var(--text-muted);font-weight:600;">Active</div>
        </div>
        <div style="background:#f8f9fa;border-radius:10px;padding:14px;text-align:center;">
          <div style="font-size:1.8rem;font-weight:700;font-family:var(--font-display);color:var(--primary);">${s.open_messages||0}</div>
          <div style="font-size:0.8rem;color:var(--text-muted);font-weight:600;">Open Messages</div>
        </div>
        <div style="background:#f8f9fa;border-radius:10px;padding:14px;text-align:center;">
          <div style="font-size:1.8rem;font-weight:700;font-family:var(--font-display);color:var(--accent);">${s.total_products||0}</div>
          <div style="font-size:0.8rem;color:var(--text-muted);font-weight:600;">Products</div>
        </div>
      </div>`;
  }

  // Monthly chart
  if (res.monthly?.length) {
    const months = res.monthly;
    const maxRev = Math.max(...months.map(m => parseFloat(m.revenue || 0)));
    document.getElementById('revenueChart').innerHTML = months.map(m => {
      const pct = maxRev > 0 ? Math.max(8, (parseFloat(m.revenue||0)/maxRev)*100) : 8;
      return `<div class="chart-bar-item">
        <div class="chart-bar-value">₱${(parseFloat(m.revenue||0)/1000).toFixed(0)}k</div>
        <div class="chart-bar-fill" style="height:${pct}%;min-height:8px;"></div>
        <div class="chart-bar-label">${m.month.split(' ')[0]}</div>
      </div>`;
    }).join('');
  } else {
    document.getElementById('revenueChart').innerHTML = '<div style="color:var(--text-muted);text-align:center;width:100%;font-size:0.85rem;">No data yet</div>';
  }

  // Recent rentals
  const rentalsRes = await apiCall(`${API}/rentals.php?action=all`);
  const recent = (rentalsRes.rentals || []).slice(0, 10);
  document.getElementById('recentBody').innerHTML = recent.length ? recent.map(r => `
    <tr>
      <td style="font-weight:700;">#${r.id.toString().padStart(5,'0')}</td>
      <td>${r.user_name}</td>
      <td>${r.product_name}</td>
      <td>${formatDate(r.rental_start)} → ${formatDate(r.rental_end)}</td>
      <td style="font-weight:700;color:var(--accent);">${formatPrice(r.total_amount)}</td>
      <td><span class="badge badge-${r.status}">${r.status}</span></td>
      <td><a href="rentals.php?id=${r.id}" class="btn btn-outline btn-sm">View</a></td>
    </tr>`).join('') : '<tr><td colspan="7" style="text-align:center;padding:24px;color:var(--text-muted);">No rentals yet</td></tr>';
}

loadDashboard();
</script>

<?php include 'footer.php'; ?>