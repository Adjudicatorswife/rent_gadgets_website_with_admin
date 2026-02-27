<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
<title>Create Account - RELAPSE</title>
<link rel="stylesheet" href="../css/style.css">
<link href="https://fonts.googleapis.com/css2?family=Rajdhani:wght@400;500;600;700&family=Nunito:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>
<?php require_once '../php/config.php'; if (isLoggedIn()) { header('Location: ' . BASE_URL . '/pages/home.php'); exit; } ?>

<div class="auth-page">
  <div class="auth-header" style="padding-top:40px;padding-bottom:24px;">
    <a href="../index.php" style="text-decoration:none;"><div class="auth-logo">RELAPSE</div></a>
    <div class="auth-tagline">GROUP2 RENT GADGETS</div>
  </div>
  <div class="auth-card">
    <div class="auth-title">Create Account</div>
    <div class="auth-subtitle">Start renting premium gadgets today</div>

    <form id="registerForm" novalidate>
      <div class="form-group">
        <label class="form-label">Full Name</label>
        <div class="input-group">
          <span class="input-icon"><svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg></span>
          <input type="text" name="name" class="form-control" placeholder="Juan dela Cruz" required autocomplete="name">
        </div>
      </div>
      <div class="form-group">
        <label class="form-label">Email Address</label>
        <div class="input-group">
          <span class="input-icon"><svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg></span>
          <input type="email" name="email" class="form-control" placeholder="you@example.com" required autocomplete="email">
        </div>
      </div>
      <div class="form-group">
        <label class="form-label">Phone Number</label>
        <div class="input-group">
          <span class="input-icon"><svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07A19.5 19.5 0 0 1 4.69 13.1 19.79 19.79 0 0 1 1.62 4.53 2 2 0 0 1 3.59 2.34h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L7.91 9.91a16 16 0 0 0 6.08 6.08l.91-.91a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/></svg></span>
          <input type="tel" name="phone" class="form-control" placeholder="09XX XXX XXXX" autocomplete="tel">
        </div>
      </div>
      <div class="form-group">
        <label class="form-label">Password</label>
        <div class="input-group">
          <span class="input-icon"><svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg></span>
          <input type="password" name="password" id="pwdField" class="form-control" placeholder="Min. 6 characters" required autocomplete="new-password">
          <button type="button" class="input-eye" onclick="togglePwd()"><svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg></button>
        </div>
      </div>
      <div class="form-group">
        <label class="form-label">Confirm Password</label>
        <div class="input-group">
          <span class="input-icon"><svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg></span>
          <input type="password" name="confirm_password" id="confirmField" class="form-control" placeholder="Repeat password" required autocomplete="new-password">
        </div>
      </div>
      <div style="margin-bottom:20px;">
        <label style="display:flex;align-items:flex-start;gap:10px;cursor:pointer;font-size:0.85rem;color:var(--text-muted);">
          <input type="checkbox" id="agreeTerms" style="margin-top:2px;accent-color:var(--accent);">
          I agree to the <a href="#" class="auth-link" style="margin-left:4px;">Terms of Service</a> and <a href="#" class="auth-link" style="margin-left:4px;">Privacy Policy</a>
        </label>
      </div>
      <button type="submit" class="btn btn-primary btn-full" id="registerBtn">Create Account</button>
    </form>

    <p style="text-align:center;color:var(--text-muted);font-size:0.9rem;margin-top:20px;">
      Already have an account? <a href="login.php" class="auth-link">Sign In</a>
    </p>
  </div>
</div>

<div class="toast-container"></div>
<script src="../js/app.js"></script>
<script>
function togglePwd() {
  const f = document.getElementById('pwdField');
  f.type = f.type === 'password' ? 'text' : 'password';
}

document.getElementById('registerForm').addEventListener('submit', async e => {
  e.preventDefault();
  const btn = document.getElementById('registerBtn');
  const name = e.target.name.value.trim();
  const email = e.target.email.value.trim();
  const password = e.target.password.value;
  const confirm = e.target.confirm_password.value;
  const phone = e.target.phone.value.trim();
  if (!name || !email || !password) { showToast('Please fill in all required fields','error'); return; }
  if (password !== confirm) { showToast('Passwords do not match','error'); return; }
  if (!document.getElementById('agreeTerms').checked) { showToast('Please agree to the Terms of Service','error'); return; }
  btn.disabled = true;
  btn.innerHTML = '<div class="spinner" style="width:20px;height:20px;border-width:2px;"></div>';
  const res = await register(name, email, password, phone);
  if (res.success) {
    showToast('Account created! Signing you in...','success');
    setTimeout(async () => {
      const loginRes = await login(email, password);
      if (loginRes.success) window.location.href = loginRes.redirect;
    }, 1000);
  } else {
    showToast(res.error || 'Registration failed','error');
    btn.disabled = false;
    btn.innerHTML = 'Create Account';
  }
});
</script>
</body>
</html>