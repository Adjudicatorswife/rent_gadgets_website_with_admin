<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
<title>Sign In - RELAPSE</title>
<link rel="stylesheet" href="../css/style.css">
<link href="https://fonts.googleapis.com/css2?family=Rajdhani:wght@400;500;600;700&family=Nunito:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>
<?php require_once '../php/config.php'; if (isLoggedIn()) { header('Location: ' . BASE_URL . (isAdmin() ? '/admin/dashboard.php' : '/pages/home.php')); exit; } ?>

<div class="auth-page">
  <div class="auth-header">
    <a href="../index.php" style="text-decoration:none;">
      <div class="auth-logo">RELAPSE</div>
    </a>
    <div class="auth-tagline">WELCOME BACK</div>
  </div>
  <div class="auth-card">
    <div class="auth-title">Sign In</div>
    <div class="auth-subtitle">Enter your credentials to continue</div>

    <form id="loginForm" novalidate>
      <div class="form-group">
        <label class="form-label">Email Address</label>
        <div class="input-group">
          <span class="input-icon">
            <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
          </span>
          <input type="email" name="email" class="form-control" placeholder="you@example.com" required autocomplete="email">
        </div>
      </div>
      <div class="form-group">
        <label class="form-label">Password</label>
        <div class="input-group">
          <span class="input-icon">
            <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
          </span>
          <input type="password" name="password" id="passwordField" class="form-control" placeholder="Your password" required autocomplete="current-password">
          <button type="button" class="input-eye" onclick="togglePwd()">
            <svg id="eyeIcon" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
          </button>
        </div>
      </div>
      <div style="text-align:right;margin-bottom:20px;">
        <a href="#" style="color:var(--text-muted);font-size:0.85rem;">Forgot password?</a>
      </div>
      <button type="submit" class="btn btn-primary btn-full" id="loginBtn">
        <span>Sign In</span>
      </button>
    </form>

    <div class="divider">or continue with</div>
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:24px;">
      <button class="social-btn" onclick="showToast('Google login coming soon!','info')">
        <svg width="18" height="18" viewBox="0 0 18 18"><path d="M17.64 9.2c0-.637-.057-1.251-.164-1.84H9v3.481h4.844a4.14 4.14 0 01-1.796 2.716v2.259h2.908c1.702-1.567 2.684-3.875 2.684-6.615z" fill="#4285F4"/><path d="M9 18c2.43 0 4.467-.806 5.956-2.18l-2.908-2.259c-.806.54-1.837.86-3.048.86-2.344 0-4.328-1.584-5.036-3.711H.957v2.332A8.997 8.997 0 009 18z" fill="#34A853"/><path d="M3.964 10.71A5.41 5.41 0 013.682 9c0-.593.102-1.17.282-1.71V4.958H.957A8.996 8.996 0 000 9c0 1.452.348 2.827.957 4.042l3.007-2.332z" fill="#FBBC05"/><path d="M9 3.58c1.321 0 2.508.454 3.44 1.345l2.582-2.58C13.463.891 11.426 0 9 0A8.997 8.997 0 00.957 4.958L3.964 7.29C4.672 5.163 6.656 3.58 9 3.58z" fill="#EA4335"/></svg>
        Google
      </button>
      <button class="social-btn" onclick="showToast('Facebook login coming soon!','info')">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="#1877F2"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg>
        Facebook
      </button>
    </div>

    <p style="text-align:center;color:var(--text-muted);font-size:0.9rem;">
      Don't have an account? <a href="register.php" class="auth-link">Sign Up</a>
    </p>
  </div>
</div>

<div class="toast-container"></div>

<script src="../js/app.js"></script>
<script>
function togglePwd() {
  const f = document.getElementById('passwordField');
  f.type = f.type === 'password' ? 'text' : 'password';
}

document.getElementById('loginForm').addEventListener('submit', async e => {
  e.preventDefault();
  const btn = document.getElementById('loginBtn');
  const email = e.target.email.value.trim();
  const password = e.target.password.value;
  if (!email || !password) { showToast('Please fill in all fields', 'error'); return; }
  btn.disabled = true;
  btn.innerHTML = '<div class="spinner" style="width:20px;height:20px;border-width:2px;"></div>';
  const res = await login(email, password);
  if (res.success) {
    showToast('Welcome back!', 'success');
    setTimeout(() => window.location.href = res.redirect, 800);
  } else {
    showToast(res.error || 'Login failed', 'error');
    btn.disabled = false;
    btn.innerHTML = '<span>Sign In</span>';
  }
});
</script>
</body>
</html>