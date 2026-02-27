<?php $page_title = 'Search - RELAPSE'; include 'header.php'; ?>

<div class="main-content">
  <div class="animate-fadeInUp" style="margin-bottom:20px;">
    <div class="search-bar" style="border:2px solid var(--primary);">
      <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
      <input type="text" id="q" placeholder="Search laptops, brands, models..." autofocus style="flex:1;border:none;outline:none;font-family:var(--font-body);font-size:0.95rem;">
      <button id="clearBtn" style="display:none;background:none;border:none;cursor:pointer;color:var(--text-muted);">✕</button>
    </div>
  </div>
  <div id="hint" style="color:var(--text-muted);text-align:center;padding:40px;font-size:0.9rem;">Type to search for gadgets...</div>
  <div class="products-grid" id="results" style="display:none;"></div>
</div>

<?php include 'footer.php'; ?>
<script>
const input = document.getElementById('q');
const clear = document.getElementById('clearBtn');
const results = document.getElementById('results');
const hint = document.getElementById('hint');

// Check URL param
const q = new URLSearchParams(window.location.search).get('q');
if (q) { input.value = q; doSearch(q); }

input.addEventListener('input', e => {
  clear.style.display = e.target.value ? 'block' : 'none';
  if (!e.target.value) { results.style.display = 'none'; hint.style.display = 'block'; hint.textContent = 'Type to search for gadgets...'; return; }
  doSearch(e.target.value);
});
clear.addEventListener('click', () => { input.value = ''; clear.style.display = 'none'; results.style.display = 'none'; hint.style.display = 'block'; hint.textContent = 'Type to search for gadgets...'; });

const doSearch = debounce(async (q) => {
  hint.style.display = 'block'; hint.textContent = 'Searching...'; results.style.display = 'none';
  const res = await searchProducts(q);
  const products = res.products || [];
  if (!products.length) { hint.textContent = `No results for "${q}"`; return; }
  hint.style.display = 'none'; results.style.display = 'flex';
  results.innerHTML = products.map((p,i) => renderProductCard(p, Math.min(i+1,5))).join('');
  observeAnimations();
}, 350);
</script>
</body>
</html>