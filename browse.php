<?php $page_title = 'Browse - RELAPSE'; include 'header.php'; ?>

<div class="main-content">
  <div style="margin-bottom:20px;" class="animate-fadeInUp">
    <div class="search-bar">
      <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
      <input type="text" id="searchInput" placeholder="Search laptops, brands, specs..." style="flex:1;border:none;outline:none;font-family:var(--font-body);font-size:0.95rem;">
      <button id="clearSearch" style="display:none;background:none;border:none;cursor:pointer;color:var(--text-muted);">✕</button>
    </div>
  </div>

  <!-- Category filter -->
  <div class="category-scroll animate-fadeInUp delay-1" id="catFilter">
    <button class="category-pill active" data-cat="" onclick="filterCat(this,'')">
      <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/></svg>
      <span>All</span>
    </button>
  </div>

  <!-- Sort -->
  <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:16px;" class="animate-fadeInUp delay-2">
    <div id="resultCount" style="color:var(--text-muted);font-size:0.85rem;"></div>
    <select id="sortBy" class="form-control" style="width:auto;padding:8px 12px;font-size:0.85rem;">
      <option value="featured">Featured</option>
      <option value="price_asc">Price: Low to High</option>
      <option value="price_desc">Price: High to Low</option>
      <option value="newest">Newest</option>
    </select>
  </div>

  <div class="products-grid" id="productsGrid">
    <div class="skeleton skeleton-card"></div>
    <div class="skeleton skeleton-card"></div>
    <div class="skeleton skeleton-card"></div>
  </div>

  <div id="loadMoreWrap" style="text-align:center;padding:20px 0;display:none;">
    <button class="btn btn-outline" id="loadMoreBtn" onclick="loadMore()">Load More</button>
  </div>
</div>

<?php include 'footer.php'; ?>
<script>
let allProducts = []; let currentCat = ''; let offset = 0; const LIMIT = 10; let isSearching = false;
const catIcons = {
  'laptop': `<svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><rect x="2" y="4" width="20" height="12" rx="2"/><path d="M8 20h8M12 16v4"/></svg>`,
  'gamepad-2': `<svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><line x1="6" y1="12" x2="10" y2="12"/><line x1="8" y1="10" x2="8" y2="14"/><line x1="15" y1="13" x2="15.01" y2="13"/><line x1="18" y1="11" x2="18.01" y2="11"/><rect x="2" y="6" width="20" height="12" rx="2"/></svg>`,
  'tablet': `<svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><rect x="4" y="2" width="16" height="20" rx="2" ry="2"/><line x1="12" y1="18" x2="12.01" y2="18"/></svg>`,
  'keyboard': `<svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><rect x="2" y="6" width="20" height="12" rx="2"/></svg>`,
  'monitor': `<svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><rect x="2" y="3" width="20" height="14" rx="2"/><line x1="12" y1="17" x2="12" y2="21"/></svg>`
};

async function loadCategories() {
  const res = await getCategories();
  const row = document.getElementById('catFilter');
  if (res.categories) {
    res.categories.forEach(c => {
      const btn = document.createElement('button');
      btn.className = 'category-pill';
      btn.dataset.cat = c.id;
      btn.onclick = () => filterCat(btn, c.id);
      btn.innerHTML = `${catIcons[c.icon]||catIcons.laptop}<span>${c.name}</span>`;
      row.appendChild(btn);
    });
  }
}

function filterCat(btn, cat) {
  currentCat = cat; offset = 0;
  document.querySelectorAll('.category-pill').forEach(b => b.classList.remove('active'));
  btn.classList.add('active');
  loadProducts(true);
}

async function loadProducts(reset = false) {
  if (reset) { allProducts = []; offset = 0; document.getElementById('productsGrid').innerHTML = '<div class="skeleton skeleton-card"></div><div class="skeleton skeleton-card"></div>'; }
  const params = { limit: LIMIT, offset };
  if (currentCat) params.category = currentCat;
  const res = await getProducts(params);
  const products = res.products || [];
  const total = res.total || 0;
  allProducts = reset ? products : [...allProducts, ...products];
  offset = allProducts.length;
  renderProducts(allProducts);
  document.getElementById('resultCount').textContent = `${total} gadget${total !== 1 ? 's' : ''} found`;
  document.getElementById('loadMoreWrap').style.display = allProducts.length < total ? 'block' : 'none';
}

function renderProducts(products) {
  const grid = document.getElementById('productsGrid');
  if (!products.length) {
    grid.innerHTML = `<div class="empty-state" style="grid-column:1/-1;"><div class="empty-icon"><svg width="40" height="40" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><rect x="2" y="4" width="20" height="12" rx="2"/><path d="M8 20h8M12 16v4"/></svg></div><div class="empty-title">No gadgets found</div><div class="empty-msg">Try a different category or search</div></div>`;
    return;
  }
  grid.innerHTML = products.map((p,i) => renderProductCard(p, Math.min(i+1,5))).join('');
  observeAnimations();
}

function loadMore() { loadProducts(false); }

// Search
const searchInput = document.getElementById('searchInput');
const clearBtn = document.getElementById('clearSearch');
const doSearch = debounce(async (q) => {
  if (!q) { isSearching = false; loadProducts(true); return; }
  isSearching = true;
  document.getElementById('productsGrid').innerHTML = '<div class="skeleton skeleton-card"></div><div class="skeleton skeleton-card"></div>';
  const res = await searchProducts(q);
  const products = res.products || [];
  renderProducts(products);
  document.getElementById('resultCount').textContent = `${products.length} result${products.length !== 1 ? 's' : ''} for "${q}"`;
  document.getElementById('loadMoreWrap').style.display = 'none';
}, 400);

searchInput.addEventListener('input', e => {
  clearBtn.style.display = e.target.value ? 'block' : 'none';
  doSearch(e.target.value.trim());
});
clearBtn.addEventListener('click', () => {
  searchInput.value = ''; clearBtn.style.display = 'none'; isSearching = false; loadProducts(true);
});

// Check URL params
const urlParams = new URLSearchParams(window.location.search);
const catParam = urlParams.get('category');
if (catParam) currentCat = catParam;

loadCategories();
loadProducts(true);
</script>
</body>
</html>