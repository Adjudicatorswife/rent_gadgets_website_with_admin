// ============================================================
// RELAPSE - Main JavaScript
// ============================================================

// ---- TOAST NOTIFICATIONS ----
const Toast = {
  container: null,
  init() {
    this.container = document.createElement('div');
    this.container.className = 'toast-container';
    document.body.appendChild(this.container);
  },
  show(message, type = 'info', duration = 4000) {
    if (!this.container) this.init();
    const icons = { success: '✅', error: '❌', warning: '⚠️', info: 'ℹ️' };
    const toast = document.createElement('div');
    toast.className = `toast toast-${type}`;
    toast.innerHTML = `<span class="toast-icon">${icons[type] || icons.info}</span><span>${message}</span>`;
    this.container.appendChild(toast);
    setTimeout(() => {
      toast.classList.add('hiding');
      setTimeout(() => toast.remove(), 300);
    }, duration);
  },
  success: (msg) => Toast.show(msg, 'success'),
  error: (msg) => Toast.show(msg, 'error'),
  warning: (msg) => Toast.show(msg, 'warning'),
  info: (msg) => Toast.show(msg, 'info'),
};

// ---- MODAL ----
const Modal = {
  open(id) {
    const el = document.getElementById(id);
    if (el) { el.classList.add('active'); document.body.style.overflow = 'hidden'; }
  },
  close(id) {
    const el = document.getElementById(id);
    if (el) { el.classList.remove('active'); document.body.style.overflow = ''; }
  },
  closeAll() {
    document.querySelectorAll('.modal-overlay.active').forEach(m => {
      m.classList.remove('active');
    });
    document.body.style.overflow = '';
  }
};

// ---- NAVBAR SCROLL ----
function initNavbar() {
  const navbar = document.querySelector('.navbar');
  if (!navbar) return;
  window.addEventListener('scroll', () => {
    navbar.classList.toggle('scrolled', window.scrollY > 10);
  });
}

// ---- HAMBURGER MENU ----
function initHamburger() {
  const btn = document.querySelector('.hamburger');
  const links = document.querySelector('.nav-links');
  const sidebar = document.querySelector('.sidebar');
  if (btn) {
    btn.addEventListener('click', () => {
      btn.classList.toggle('active');
      if (links) links.style.display = links.style.display === 'flex' ? 'none' : 'flex';
      if (sidebar) sidebar.classList.toggle('open');
    });
  }
}

// ---- ANIMATION OBSERVER ----
function initAnimations() {
  const observer = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
      if (entry.isIntersecting) {
        entry.target.style.animationPlayState = 'running';
        entry.target.classList.add('animate-in');
      }
    });
  }, { threshold: 0.1 });

  document.querySelectorAll('[data-animate]').forEach(el => {
    el.style.opacity = '0';
    el.style.animationPlayState = 'paused';
    observer.observe(el);
  });
}

// ---- PARTICLE SYSTEM ----
function initParticles() {
  const container = document.querySelector('.splash-particles');
  if (!container) return;
  for (let i = 0; i < 30; i++) {
    const p = document.createElement('div');
    p.className = 'particle';
    const tx = (Math.random() - 0.5) * 300;
    const ty = (Math.random() - 0.5) * 300;
    p.style.cssText = `
      left:${Math.random()*100}%;
      top:${Math.random()*100}%;
      --tx:${tx}px;
      --ty:${ty}px;
      animation-delay:${Math.random()*5}s;
      animation-duration:${4+Math.random()*4}s;
      width:${2+Math.random()*4}px;
      height:${2+Math.random()*4}px;
      background:${Math.random()>0.5 ? '#e84040' : '#2563eb'};
      opacity:${0.3+Math.random()*0.4};
    `;
    container.appendChild(p);
  }
}

// ---- RENTAL DATE CALCULATION ----
function calcRental() {
  const start = document.getElementById('rental_start');
  const end = document.getElementById('rental_end');
  const daysEl = document.getElementById('rental_days');
  const totalEl = document.getElementById('rental_total');
  const pricePerDay = parseFloat(document.getElementById('price_per_day')?.value || 0);

  if (!start || !end) return;

  function update() {
    const s = new Date(start.value);
    const e = new Date(end.value);
    if (!start.value || !end.value || e <= s) {
      if (daysEl) daysEl.textContent = '0';
      if (totalEl) totalEl.textContent = '₱0.00';
      return;
    }
    const days = Math.ceil((e - s) / (1000*60*60*24));
    if (days < 1) return;
    const deposit = parseFloat(document.getElementById('deposit_val')?.value || 0);
    const total = (days * pricePerDay) + deposit;
    if (daysEl) daysEl.textContent = days;
    if (totalEl) totalEl.textContent = '₱' + total.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ',');
    const daysInput = document.getElementById('days_input');
    if (daysInput) daysInput.value = days;
  }

  start.addEventListener('change', () => {
    const s = new Date(start.value);
    s.setDate(s.getDate() + 1);
    end.min = s.toISOString().split('T')[0];
    update();
  });
  end.addEventListener('change', update);

  // Set min date to today
  const today = new Date().toISOString().split('T')[0];
  start.min = today;
}

// ---- API HELPER ----
const API = {
  async request(method, url, data = null) {
    const opts = {
      method,
      headers: { 'Content-Type': 'application/json' },
      credentials: 'same-origin'
    };
    if (data) opts.body = JSON.stringify(data);
    const res = await fetch(url, opts);
    return res.json();
  },
  get: (url) => API.request('GET', url),
  post: (url, data) => API.request('POST', url, data),
  put: (url, data) => API.request('PUT', url, data),
  delete: (url) => API.request('DELETE', url),
};

// ---- CART ----
const Cart = {
  async add(productId, startDate, endDate) {
    const res = await API.post('/relapse/api/cart.php', { action: 'add', product_id: productId, rental_start: startDate, rental_end: endDate });
    if (res.success) {
      Toast.success('Added to cart!');
      Cart.updateBadge(res.count);
    } else {
      Toast.error(res.message || 'Failed to add to cart');
    }
    return res;
  },
  async remove(cartId) {
    const res = await API.post('/relapse/api/cart.php', { action: 'remove', cart_id: cartId });
    if (res.success) { Toast.info('Removed from cart'); Cart.updateBadge(res.count); }
    return res;
  },
  updateBadge(count) {
    document.querySelectorAll('.cart-badge').forEach(el => {
      el.textContent = count;
      el.style.display = count > 0 ? 'flex' : 'none';
    });
  }
};

// ---- IMAGE PREVIEW ----
function initImagePreviews() {
  document.querySelectorAll('input[type="file"][data-preview]').forEach(input => {
    input.addEventListener('change', () => {
      const previewId = input.dataset.preview;
      const preview = document.getElementById(previewId);
      if (preview && input.files[0]) {
        const reader = new FileReader();
        reader.onload = e => preview.src = e.target.result;
        reader.readAsDataURL(input.files[0]);
      }
    });
  });
}

// ---- CLOSE MODALS ON OVERLAY CLICK ----
function initModalClose() {
  document.addEventListener('click', (e) => {
    if (e.target.classList.contains('modal-overlay')) Modal.closeAll();
    if (e.target.classList.contains('modal-close')) {
      const overlay = e.target.closest('.modal-overlay');
      if (overlay) overlay.classList.remove('active');
      document.body.style.overflow = '';
    }
  });
  document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') Modal.closeAll();
  });
}

// ---- CONFIRM DIALOG ----
function confirm(message, callback) {
  const id = 'confirm-modal';
  let modal = document.getElementById(id);
  if (!modal) {
    modal = document.createElement('div');
    modal.id = id;
    modal.className = 'modal-overlay';
    modal.innerHTML = `
      <div class="modal" style="max-width:400px">
        <div class="modal-body" style="text-align:center;padding:40px 24px">
          <div style="font-size:48px;margin-bottom:16px">⚠️</div>
          <p id="confirm-msg" style="font-size:16px;margin-bottom:24px"></p>
          <div style="display:flex;gap:12px;justify-content:center">
            <button class="btn btn-outline" onclick="Modal.close('confirm-modal')">Cancel</button>
            <button class="btn btn-primary" id="confirm-yes">Confirm</button>
          </div>
        </div>
      </div>`;
    document.body.appendChild(modal);
  }
  document.getElementById('confirm-msg').textContent = message;
  document.getElementById('confirm-yes').onclick = () => { Modal.close(id); callback(); };
  Modal.open(id);
}

// ---- COUNTER ANIMATION ----
function animateCounters() {
  document.querySelectorAll('[data-count]').forEach(el => {
    const target = parseInt(el.dataset.count);
    let current = 0;
    const step = target / 50;
    const timer = setInterval(() => {
      current += step;
      if (current >= target) { current = target; clearInterval(timer); }
      el.textContent = Math.floor(current).toLocaleString();
    }, 30);
  });
}

// ---- SEARCH FILTER ----
function initProductFilter() {
  const chips = document.querySelectorAll('.filter-chip');
  chips.forEach(chip => {
    chip.addEventListener('click', () => {
      chips.forEach(c => c.classList.remove('active'));
      chip.classList.add('active');
      const cat = chip.dataset.category;
      document.querySelectorAll('[data-category]').forEach(card => {
        const show = !cat || cat === 'all' || card.dataset.category === cat;
        card.style.display = show ? '' : 'none';
        if (show) card.classList.add('animate-in');
      });
    });
  });
}

// ---- MOBILE SIDEBAR OVERLAY ----
function initMobileSidebar() {
  const sidebar = document.querySelector('.sidebar');
  if (!sidebar) return;
  const overlay = document.createElement('div');
  overlay.style.cssText = 'position:fixed;inset:0;background:rgba(0,0,0,0.5);z-index:99;display:none;';
  document.body.appendChild(overlay);
  const hamburger = document.querySelector('.hamburger');
  if (hamburger) {
    hamburger.addEventListener('click', () => {
      const isOpen = sidebar.classList.contains('open');
      overlay.style.display = isOpen ? 'none' : 'block';
    });
  }
  overlay.addEventListener('click', () => {
    sidebar.classList.remove('open');
    overlay.style.display = 'none';
    document.querySelector('.hamburger')?.classList.remove('active');
  });
}

// ---- INIT ----
document.addEventListener('DOMContentLoaded', () => {
  Toast.init();
  initNavbar();
  initHamburger();
  initAnimations();
  initParticles();
  calcRental();
  initImagePreviews();
  initModalClose();
  initProductFilter();
  initMobileSidebar();

  // Trigger counters when visible
  const counterObserver = new IntersectionObserver(entries => {
    entries.forEach(e => { if (e.isIntersecting) { animateCounters(); counterObserver.disconnect(); } });
  });
  const statsSection = document.querySelector('.stats-grid');
  if (statsSection) counterObserver.observe(statsSection);
});
