<?php
$page = 'profile';
$title = 'Profile - RELAPSE';
require_once '../includes/config.php';
requireLogin();
$db = getDB();
$uid = $_SESSION['user_id'];
$user = $db->prepare("SELECT * FROM users WHERE id=?");
$user->execute([$uid]);
$user = $user->fetch();

$success = $error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $type = $_POST['type'] ?? '';

    if ($type === 'profile') {
        $fn = trim($_POST['firstname']??'');
        $ln = trim($_POST['lastname']??'');
        $phone = trim($_POST['phone']??'');
        $address = trim($_POST['address']??'');

        // Handle file upload
        $pic = $user['profile_pic'];
        if (!empty($_FILES['profile_pic']['name'])) {
            $ext = strtolower(pathinfo($_FILES['profile_pic']['name'], PATHINFO_EXTENSION));
            if (in_array($ext, ['jpg','jpeg','png','gif','webp'])) {
                $newName = 'user_'.$uid.'.'.$ext;
                $dest = __DIR__.'/../assets/images/'.$newName;
                if (move_uploaded_file($_FILES['profile_pic']['tmp_name'], $dest)) {
                    $pic = $newName;
                }
            }
        }

        $stmt = $db->prepare("UPDATE users SET firstname=?,lastname=?,phone=?,address=?,profile_pic=? WHERE id=?");
        $stmt->execute([$fn,$ln,$phone,$address,$pic,$uid]);
        $_SESSION['firstname'] = $fn;
        $_SESSION['lastname'] = $ln;
        $_SESSION['profile_pic'] = $pic;
        $user['firstname']=$fn; $user['lastname']=$ln; $user['phone']=$phone; $user['address']=$address; $user['profile_pic']=$pic;
        $success = 'Profile updated successfully!';

    } elseif ($type === 'password') {
        $curr = $_POST['current_password']??'';
        $new = $_POST['new_password']??'';
        $conf = $_POST['confirm_password']??'';
        if (!password_verify($curr, $user['password'])) {
            $error = 'Current password is incorrect.';
        } elseif (strlen($new) < 6) {
            $error = 'New password must be at least 6 characters.';
        } elseif ($new !== $conf) {
            $error = 'Passwords do not match.';
        } else {
            $db->prepare("UPDATE users SET password=? WHERE id=?")->execute([password_hash($new, PASSWORD_DEFAULT), $uid]);
            $success = 'Password changed successfully!';
        }
    }
}
?>
<?php require_once '../includes/header.php'; ?>

<div class="app-layout">
  <aside class="sidebar">
    <div class="sidebar-section">
      <div class="sidebar-label">Main</div>
      <a href="home.php" class="sidebar-link"><span class="icon">🏠</span> Dashboard</a>
      <a href="../products.php" class="sidebar-link"><span class="icon">💻</span> Browse Catalog</a>
    </div>
    <div class="sidebar-section">
      <div class="sidebar-label">Rentals</div>
      <a href="rentals.php" class="sidebar-link"><span class="icon">📦</span> My Rentals</a>
    </div>
    <div class="sidebar-section">
      <div class="sidebar-label">Account</div>
      <a href="notifications.php" class="sidebar-link"><span class="icon">🔔</span> Notifications</a>
      <a href="profile.php" class="sidebar-link active"><span class="icon">👤</span> Profile</a>
      <a href="../logout.php" class="sidebar-link"><span class="icon">🚪</span> Logout</a>
    </div>
  </aside>

  <main class="main-content" style="max-width:700px">
    <div class="page-header">
      <h1 class="page-title">My Profile</h1>
      <p class="page-subtitle">Manage your account information</p>
    </div>

    <?php if($success): ?><div class="alert alert-success">✅ <?= sanitize($success) ?></div><?php endif; ?>
    <?php if($error): ?><div class="alert alert-danger">⚠️ <?= sanitize($error) ?></div><?php endif; ?>

    <!-- Profile Card -->
    <div class="card mb-3 animate-in">
      <div class="card-header">
        <h3 style="font-family:var(--font-display);font-size:18px;font-weight:600">Personal Information</h3>
      </div>
      <div class="card-body">
        <form method="POST" enctype="multipart/form-data">
          <input type="hidden" name="type" value="profile">
          <div style="display:flex;align-items:center;gap:24px;margin-bottom:28px">
            <div class="profile-avatar-wrapper">
              <img src="<?= SITE_URL ?>/assets/images/<?= $user['profile_pic']??'default.png' ?>" id="avatar-preview" class="profile-avatar" onerror="this.src='<?= SITE_URL ?>/assets/images/default.png'">
              <label for="pic-upload" class="profile-avatar-edit">✏️</label>
              <input type="file" id="pic-upload" name="profile_pic" style="display:none" accept="image/*" data-preview="avatar-preview">
            </div>
            <div>
              <div style="font-family:var(--font-display);font-size:20px;font-weight:600"><?= sanitize($user['firstname'].' '.$user['lastname']) ?></div>
              <div style="font-size:13px;color:var(--text-secondary)"><?= sanitize($user['email']) ?></div>
              <div style="font-size:11px;color:var(--text-muted);margin-top:4px">Member since <?= date('M Y', strtotime($user['created_at'])) ?></div>
            </div>
          </div>

          <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px">
            <div class="form-group" style="margin-bottom:0">
              <label class="form-label">First Name</label>
              <input type="text" name="firstname" class="form-control" value="<?= sanitize($user['firstname']) ?>" required>
            </div>
            <div class="form-group" style="margin-bottom:0">
              <label class="form-label">Last Name</label>
              <input type="text" name="lastname" class="form-control" value="<?= sanitize($user['lastname']) ?>" required>
            </div>
          </div>
          <div class="form-group mt-2">
            <label class="form-label">Email Address</label>
            <input type="email" class="form-control" value="<?= sanitize($user['email']) ?>" disabled style="opacity:0.6">
          </div>
          <div class="form-group">
            <label class="form-label">Phone Number</label>
            <input type="tel" name="phone" class="form-control" value="<?= sanitize($user['phone']??'') ?>">
          </div>
          <div class="form-group">
            <label class="form-label">Address</label>
            <textarea name="address" class="form-control" rows="2"><?= sanitize($user['address']??'') ?></textarea>
          </div>
          <button type="submit" class="btn btn-primary">Save Changes</button>
        </form>
      </div>
    </div>

    <!-- Change Password -->
    <div class="card animate-in delay-2">
      <div class="card-header">
        <h3 style="font-family:var(--font-display);font-size:18px;font-weight:600">Change Password</h3>
      </div>
      <div class="card-body">
        <form method="POST">
          <input type="hidden" name="type" value="password">
          <div class="form-group">
            <label class="form-label">Current Password</label>
            <input type="password" name="current_password" class="form-control" required>
          </div>
          <div class="form-group">
            <label class="form-label">New Password</label>
            <input type="password" name="new_password" class="form-control" required minlength="6">
          </div>
          <div class="form-group">
            <label class="form-label">Confirm New Password</label>
            <input type="password" name="confirm_password" class="form-control" required>
          </div>
          <button type="submit" class="btn btn-outline">Update Password</button>
        </form>
      </div>
    </div>
  </main>
</div>

<?php require_once '../includes/footer.php'; ?>
