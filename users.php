<?php $admin_page='users'; $admin_title='Users'; include 'header.php'; ?>

<div style="margin-bottom:20px;">
  <input type="search" id="userSearch" class="form-control" style="width:280px;" placeholder="Search by name or email...">
</div>

<div class="admin-card">
  <div style="overflow-x:auto;">
    <table class="admin-table">
      <thead><tr><th>User</th><th>Email</th><th>Phone</th><th>Rentals</th><th>Joined</th><th>Actions</th></tr></thead>
      <tbody id="userBody"><tr><td colspan="6" style="text-align:center;padding:30px;color:var(--text-muted);">Loading...</td></tr></tbody>
    </table>
  </div>
</div>

<!-- User Detail Modal -->
<div class="admin-modal-overlay" id="userModal" style="display:none;" onclick="if(event.target===this)this.style.display='none'">
  <div class="admin-modal">
    <div class="modal-title">User Details</div>
    <div id="userDetail"></div>
    <div class="modal-footer">
      <button class="btn btn-outline" onclick="document.getElementById('userModal').style.display='none'">Close</button>
    </div>
  </div>
</div>

<script>
let users = [];

async function loadUsers() {
  const res = await apiCall(`${API}/user.php?action=all_users`);
  users = res.users || [];
  renderUsers(users);
}

function renderUsers(list) {
  const body = document.getElementById('userBody');
  if (!list.length) { body.innerHTML = '<tr><td colspan="6" style="text-align:center;padding:30px;color:var(--text-muted);">No users found</td></tr>'; return; }
  body.innerHTML = list.map(u => `
    <tr>
      <td>
        <div style="display:flex;align-items:center;gap:10px;">
          <div style="width:36px;height:36px;border-radius:50%;background:var(--primary);display:flex;align-items:center;justify-content:center;color:white;font-weight:700;font-size:0.9rem;flex-shrink:0;">${u.full_name.charAt(0).toUpperCase()}</div>
          <div><div style="font-weight:700;">${u.full_name}</div></div>
        </div>
      </td>
      <td>${u.email}</td>
      <td>${u.phone||'—'}</td>
      <td><span class="badge badge-active">${u.rental_count}</span></td>
      <td>${formatDate(u.created_at)}</td>
      <td><button class="btn btn-outline btn-sm" onclick='showUser(${JSON.stringify(u).replace(/'/g,"&#39;")})'>View</button></td>
    </tr>`).join('');
}

document.getElementById('userSearch').addEventListener('input', e => {
  const q = e.target.value.toLowerCase();
  renderUsers(users.filter(u => u.full_name.toLowerCase().includes(q) || u.email.toLowerCase().includes(q)));
});

function showUser(u) {
  document.getElementById('userDetail').innerHTML = `
    <div style="display:flex;flex-direction:column;gap:12px;">
      <div style="background:var(--gray-100);border-radius:10px;padding:16px;display:flex;gap:14px;align-items:center;">
        <div style="width:56px;height:56px;border-radius:50%;background:var(--primary);display:flex;align-items:center;justify-content:center;color:white;font-weight:700;font-size:1.3rem;">${u.full_name.charAt(0).toUpperCase()}</div>
        <div>
          <div style="font-weight:700;font-size:1rem;">${u.full_name}</div>
          <div style="color:var(--text-muted);font-size:0.85rem;">${u.email}</div>
        </div>
      </div>
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;">
        <div style="background:var(--gray-100);border-radius:10px;padding:12px;"><div style="font-size:0.75rem;color:var(--text-muted);font-weight:600;">Phone</div><div style="font-weight:700;">${u.phone||'—'}</div></div>
        <div style="background:var(--gray-100);border-radius:10px;padding:12px;"><div style="font-size:0.75rem;color:var(--text-muted);font-weight:600;">Total Rentals</div><div style="font-weight:700;color:var(--accent);">${u.rental_count}</div></div>
        <div style="background:var(--gray-100);border-radius:10px;padding:12px;"><div style="font-size:0.75rem;color:var(--text-muted);font-weight:600;">Joined</div><div style="font-weight:700;">${formatDate(u.created_at)}</div></div>
        <div style="background:var(--gray-100);border-radius:10px;padding:12px;"><div style="font-size:0.75rem;color:var(--text-muted);font-weight:600;">Address</div><div style="font-weight:700;font-size:0.85rem;">${u.address||'—'}</div></div>
      </div>
    </div>`;
  document.getElementById('userModal').style.display = 'flex';
}

loadUsers();
</script>
<?php include 'footer.php'; ?>