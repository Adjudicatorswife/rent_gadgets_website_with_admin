// RELAPSE - Main JavaScript
const BASE_URL = window.location.origin + '/relapse';
const API = BASE_URL + '/api';
// =================== API CALLS ===================
async function apiCall(endpoint, method = 'GET', data = null) {
  const opts = { method, credentials: 'include' };
  if (data instanceof FormData) {
    opts.body = data;
  } else if (data) {
    opts.headers = { 'Content-Type': 'application/x-www-form-urlencoded' };
    opts.body = new URLSearchParams(data).toString();
  }
  try {
    const res = await fetch(endpoint, opts);
    const text = await res.text();
    console.log('API Response:', text); // Add this to see what's returned
    return JSON.parse(text);
  } catch (e) {
    console.error('API Error:', e);
    console.log('Endpoint:', endpoint); // Add this
    return { error: 'Network error' };
  }
}

// =================== TOAST ===================
function showToast(msg, type = 'info') {
  let container = document.querySelector('.toast-container');
  if (!container) {
    container = document.createElement('div');
    container.className = 'toast-container';
    document.body.appendChild(container);
  }
  const icons = { success: '✓', error: '✕', info: 'ℹ' };
  const t = document.createElement('div');
  t.className = `toast ${type}`;
  t.innerHTML = `<span>${icons[type]||'ℹ'}</span><span class="toast-msg">${msg}</span>`;
  container.appendChild(t);
  setTimeout(() => { t.style.animation = 'fadeIn 0.3s ease reverse'; setTimeout(() => t.remove(), 300); }, 3000);
}

// =================== AUTH ===================
async function checkAuth() {
  const res = await apiCall(`${API}/auth.php?action=check`);
  return res;
}

async function login(email, password) {
  return await apiCall(`${API}/auth.php`, 'POST', { action: 'login', email, password });
}

async function register(name, email, password, phone) {
  return await apiCall(`${API}/auth.php`, 'POST', { action: 'register', name, email, password, phone });
}

async function logout() {
  const res = await apiCall(`${API}/auth.php`, 'POST', { action: 'logout' });
  if (res.success) window.location.href = res.redirect;
}

// =================== PRODUCTS ===================
async function getProducts(params = {}) {
  const q = new URLSearchParams({ action: 'list', ...params }).toString();
  return await apiCall(`${API}/products.php?${q}`);
}

async function getFeatured() {
  return await apiCall(`${API}/products.php?action=featured`);
}

async function getProduct(id) {
  return await apiCall(`${API}/products.php?action=detail&id=${id}`);
}

async function searchProducts(q) {
  return await apiCall(`${API}/products.php?action=search&q=${encodeURIComponent(q)}`);
}

async function getCategories() {
  return await apiCall(`${API}/products.php?action=categories`);
}

// =================== RENTALS ===================
async function createRental(data) {
  return await apiCall(`${API}/rentals.php`, 'POST', { action: 'create', ...data });
}

async function getRentals(status = '') {
  return await apiCall(`${API}/rentals.php?action=list&status=${status}`);
}

async function getRental(id) {
  return await apiCall(`${API}/rentals.php?action=detail&id=${id}`);
}

async function cancelRental(id) {
  return await apiCall(`${API}/rentals.php`, 'POST', { action: 'cancel', id });
}

// =================== NOTIFICATIONS ===================
async function getNotifications() {
  return await apiCall(`${API}/user.php?action=list`);
}

async function getUnreadCount() {
  const res = await apiCall(`${API}/user.php?action=unread_count`);
  return res.count || 0;
}

async function markNotifRead(id) {
  return await apiCall(`${API}/user.php`, 'POST', { action: 'read', id });
}

async function markAllRead() {
  return await apiCall(`${API}/user.php`, 'POST', { action: 'read_all' });
}

// =================== USER ===================
async function getProfile() {
  return await apiCall(`${API}/user.php?action=profile`);
}

async function getUserStats() {
  return await apiCall(`${API}/user.php?action=stats`);
}

// =================== UTILS ===================
function formatPrice(amount) {
  return '₱' + parseFloat(amount || 0).toLocaleString('en-PH', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
}

function formatDate(dateStr) {
  if (!dateStr) return '';
  return new Date(dateStr).toLocaleDateString('en-PH', { month: 'short', day: 'numeric', year: 'numeric' });
}

function timeAgo(dateStr) {
  if (!dateStr) return '';
  const diff = Date.now() - new Date(dateStr).getTime();
  const mins = Math.floor(diff / 60000);
  if (mins < 1) return 'Just now';
  if (mins < 60) return `${mins}m ago`;
  const hrs = Math.floor(mins / 60);
  if (hrs < 24) return `${hrs}h ago`;
  const days = Math.floor(hrs / 24);
  if (days < 7) return `${days}d ago`;
  return formatDate(dateStr);
}

function productImageSrc(img) {
  if (!img || img === 'default_product.jpg') return null;
  return `${BASE_URL}/uploads/products/${img}`;
}

function avatarSrc(avatar) {
  if (!avatar || avatar === 'default.png') return null;
  return `${BASE_URL}/uploads/avatars/${avatar}`;
}

function debounce(fn, ms = 300) {
  let timer;
  return (...args) => { clearTimeout(timer); timer = setTimeout(() => fn(...args), ms); };
}

// =================== MODAL ===================
function openModal(id) {
  const el = document.getElementById(id);
  if (el) { el.style.display = 'flex'; document.body.style.overflow = 'hidden'; }
}

function closeModal(id) {
  const el = document.getElementById(id);
  if (el) { el.style.display = 'none'; document.body.style.overflow = ''; }
}

// Close modals on overlay click
document.addEventListener('click', e => {
  if (e.target.classList.contains('modal-overlay')) {
    e.target.style.display = 'none';
    document.body.style.overflow = '';
  }
});

// =================== SCROLL TO TOP ===================
window.addEventListener('scroll', () => {
  const btn = document.querySelector('.scroll-top');
  if (btn && btn.style) btn.classList.toggle('visible', window.scrollY > 300);
});
// =================== INTERSECTION OBSERVER (animations on scroll) ===================
const observer = new IntersectionObserver(entries => {
  entries.forEach(entry => {
    if (entry.isIntersecting) {
      entry.target.style.opacity = '1';
      entry.target.style.transform = 'translateY(0)';
      observer.unobserve(entry.target);
    }
  });
}, { threshold: 0.1 });

function observeAnimations() {
  document.querySelectorAll('.product-card, .rental-card, .stat-card, .card').forEach(el => {
    if (!el.style.opacity) {
      el.style.opacity = '0';
      el.style.transform = 'translateY(20px)';
      el.style.transition = 'all 0.5s ease';
      observer.observe(el);
    }
  });
}

// Render product card HTML
function renderProductCard(p, delay = 0) {
  const img = productImageSrc(p.image);
  const specs = p.specs || {};
  const specTags = Object.entries(specs).slice(0, 3).map(([k,v]) => `<span class="spec-tag">${v}</span>`).join('');
  return `
    <a href="${BASE_URL}/pages/product-detail.php?id=${p.id}" class="product-card animate-fadeInUp delay-${delay}">
      <div class="product-card-img">
        ${img ? `<img src="${img}" alt="${p.name}" loading="lazy">` : `<svg xmlns="http://www.w3.org/2000/svg" width="64" height="64" fill="none" viewBox="0 0 24 24" stroke="currentColor"><rect x="2" y="4" width="20" height="12" rx="2" stroke-width="1.5"/><path d="M8 20h8M12 16v4" stroke-width="1.5"/></svg>`}
      </div>
      <div class="product-card-body">
        <div class="product-card-brand">${p.brand || p.category_name || ''}</div>
        <div class="product-card-name">${p.name}</div>
        <div class="product-card-specs">${specTags}</div>
        <div class="product-card-footer">
          <div class="product-price">${formatPrice(p.price_per_day)}<span>/day</span></div>
          <div class="product-condition">
            <div class="condition-dot ${(p.condition_rating||'').toLowerCase()}"></div>
            <span class="condition-label">${p.condition_rating || ''}</span>
          </div>
        </div>
      </div>
    </a>`;
}

// Update notification badge
async function updateNotifBadge() {
  const count = await getUnreadCount();
  const badge = document.querySelector('.notif-badge');
  if (badge) { badge.textContent = count; badge.style.display = count > 0 ? 'flex' : 'none'; }
}
window.addEventListener('load', () => {
  const bar = document.getElementById('progressBar');
  if (bar) { bar.style.width = '100%'; }
  
  const splash = document.getElementById('splashScreen');
  if (!splash) return; // Exit if no splash screen (admin pages)
  
  setTimeout(() => {
    splash.style.transition = 'opacity 0.5s ease';
    splash.style.opacity = '0';
    
    // Stop logo spinning
    const logo = splash.querySelector('.logo-spin-slow');
    if (logo) {
      logo.style.animation = 'none';
    }
    
    setTimeout(() => {
      splash.style.display = 'none';
      const onboard = document.getElementById('onboardPage');
      onboard.style.display = 'flex';
    }, 500);
  }, 2200);
});