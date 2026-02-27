<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
<title>RELAPSE - Premium Gadget Rentals</title>
<link rel="stylesheet" href="../css/style.css">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Rajdhani:wght@400;500;600;700&family=Nunito:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>
<?php
require_once dirname(__DIR__) . '/php/config.php';
if (isLoggedIn()) {
    header('Location: ' . BASE_URL . (isAdmin() ? '/admin/dashboard.php' : '/pages/home.php'));
    exit;
}
?>

<div class="splash-screen" id="splashScreen">
  <div style="position:absolute;inset:0;overflow:hidden;">
    <div style="position:absolute;width:400px;height:400px;background:#003049;border-radius:50%;opacity:0.05;top:-150px;right:-150px;animation:floatUp 4s ease-in-out infinite;"></div>
    <div style="position:absolute;width:250px;height:250px;background:#003049;border-radius:50%;opacity:0.05;bottom:-80px;left:-80px;animation:floatUp 4s 1s ease-in-out infinite;"></div>
  </div>
  <div class="splash-logo" style="text-align:center;z-index:1;">
    <img src="/relapse/uploads/logo.png" alt="RELAPSE" 
     style="width:200px;height:200px;object-fit:contain;margin:0 auto 20px;"
     class="logo-spin-slow">
    <div class="splash-tagline" style="margin-top:8px;">RELAPSE</div>
  </div>
  <div class="splash-progress"><div class="splash-progress-bar" id="progressBar"></div></div>
</div>

<div id="onboardPage" style="display:none;min-height:100vh;background:linear-gradient(160deg,#07101f 0%,#0f1a2e 40%,#1a2744 100%);display:flex;flex-direction:column;">
  <div style="flex:1;display:flex;flex-direction:column;align-items:center;justify-content:center;padding:48px 24px 20px;text-align:center;position:relative;">
    <div style="position:absolute;inset:0;overflow:hidden;pointer-events:none;">
      <div style="position:absolute;width:300px;height:300px;background:#e84040;border-radius:50%;opacity:0.06;top:-80px;right:-80px;animation:floatUp 5s ease-in-out infinite;"></div>
      <div style="position:absolute;width:180px;height:180px;background:#00d4aa;border-radius:50%;opacity:0.06;bottom:60px;left:-40px;animation:floatUp 4s 2s ease-in-out infinite;"></div>
    </div>
    <div style="position:relative;z-index:1;animation:fadeInUp 0.7s ease both;">
     
     <img src="<?= BASE_URL ?> /uploads/logo.png" alt="RELAPSE" style="width:100px;height:100px;object-fit:contain;margin:0 auto 16px;display:block;">
      <div style="color:rgb(255, 255, 255);letter-spacing:3px;font-size:1.75rem;font-weight:700;text-transform:uppercase;margin-top:6px;">RELAPSE</div>
    </div>

    <div style="margin-top:40px;width:100%;max-width:300px;position:relative;z-index:1;animation:fadeInUp 0.7s 0.2s ease both;opacity:0;animation-fill-mode:both;">
      <div style="background:rgba(255,255,255,0.06);border:1px solid rgba(255,255,255,0.1);border-radius:20px;padding:20px;backdrop-filter:blur(10px);">
        <div style="display:flex;gap:12px;align-items:center;margin-bottom:14px;">
          <div style="width:52px;height:52px;background:linear-gradient(135deg,#1e3a6e,#243357);border-radius:12px;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
            <svg width="26" height="26" fill="none" stroke="rgba(255,255,255,0.6)" stroke-width="1.5" viewBox="0 0 24 24"><rect x="2" y="4" width="20" height="12" rx="2"/><path d="M8 20h8M12 16v4"/></svg>
          </div>
          <div><div style="color:white;font-weight:700;font-size:0.9rem;">ASUS ROG G14</div><div style="color:#e84040;font-size:0.85rem;font-weight:700;">₱750<span style="color:rgba(255,255,255,0.4);font-size:0.75rem;">/day</span></div></div>
        </div>
        <div style="background:rgba(0,212,170,0.12);border:1px solid rgba(0,212,170,0.25);color:#00d4aa;font-size:0.75rem;padding:5px 14px;border-radius:50px;font-weight:600;display:inline-block;margin-bottom:12px;">✓ Available Now</div>
        <div style="display:flex;align-items:center;gap:8px;">
          <div style="flex:1;height:5px;background:rgba(255,255,255,0.08);border-radius:3px;overflow:hidden;"><div style="width:70%;height:100%;background:linear-gradient(90deg,#e84040,#ff6b35);border-radius:3px;"></div></div>
          <span style="color:rgba(255,255,255,0.35);font-size:0.7rem;">RTX3060</span>
        </div>
      </div>
    </div>
  </div>

  <div style="background:rgba(255,255,255,0.08);border-radius:32px 32px 0 0;padding:32px 24px 48px;animation:slideInFromBottom 0.6s 0.1s cubic-bezier(0.4,0,0.2,1) both; backdrop-filter: blur(30px);">
    <div style="display:flex;justify-content:center;gap:6px;margin-bottom:24px;">
      <div style="width:24px;height:8px;border-radius:4px;background:#e84040;"></div>
      <div style="width:8px;height:8px;border-radius:50%;background:#dee2e6;"></div>
      <div style="width:8px;height:8px;border-radius:50%;background:#dee2e6;"></div>
    </div>
    <div style="font-family:'Rajdhani',sans-serif;font-size:2rem;font-weight:700;color:#FFFFFF;line-height:1.2;margin-bottom:10px;">Rent Premium<br>Gadgets Today</div>
    <div style="color:#7a8ba8;font-size:0.9rem;line-height:1.65;margin-bottom:28px;">Access the latest laptops, gaming gear, and tech without the commitment. Rent daily, return easily.</div>
    <a href="<?= BASE_URL ?>/pages/register.php" class="btn btn-primary btn-full" style="margin-bottom:12px;font-size:1rem;">
      <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><line x1="19" y1="8" x2="19" y2="14"/><line x1="22" y1="11" x2="16" y2="11"/></svg>
      Create Account
    </a>
    <a href="<?= BASE_URL ?>/pages/login.php" class="btn btn-outline btn-full" style="font-size:1rem;">Sign In</a>
    <p style="text-align:center;color:#FFFFFF;font-size:0.78rem;margin-top:20px;">By continuing, you agree to our Terms & Privacy Policy</p>
  </div>
</div>

<script>
window.addEventListener('load', () => {
  const bar = document.getElementById('progressBar');
  if (bar) { bar.style.width = '100%'; }
  setTimeout(() => {
    const splash = document.getElementById('splashScreen');
    splash.style.transition = 'opacity 0.5s ease';
    splash.style.opacity = '0';
    setTimeout(() => {
      splash.style.display = 'none';
      const onboard = document.getElementById('onboardPage');
      onboard.style.display = 'flex';
    }, 500);
  }, 2200);
});
</script>
</body>
</html>