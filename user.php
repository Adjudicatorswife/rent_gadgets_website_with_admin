<?php
error_reporting(0);
ini_set('display_errors', 0);
header('Content-Type: application/json');
require_once '../php/config.php';
requireLogin();
$action=$_POST['action']??$_GET['action']??'';

switch ($action) {
    case 'list': listNotifs(); break;
    case 'read': markRead(); break;
    case 'read_all': markAllRead(); break;
    case 'unread_count': unreadCount(); break;
    case 'profile': getProfile(); break;
    case 'update_profile': updateProfile(); break;
    case 'change_password': changePassword(); break;
    case 'stats': userStats(); break;
    case 'messages': getMessages(); break;
    case 'send_message': sendMessage(); break;
    case 'all_users': requireAdmin(); allUsers(); break;
    case 'admin_stats': requireAdmin(); adminStats(); break;
    default: jsonResponse(['error'=>'Invalid action'],400);
}

function listNotifs() {
    $uid=$_SESSION['user_id'];
    $db=getDB(); $limit=intval($_GET['limit']??20);
    $stmt=$db->prepare("SELECT * FROM notifications WHERE user_id=? ORDER BY created_at DESC LIMIT ?");
    $stmt->bind_param('ii',$uid,$limit); $stmt->execute();
    jsonResponse(['notifications'=>$stmt->get_result()->fetch_all(MYSQLI_ASSOC)]);
}

function markRead() {
    $id=intval($_POST['id']??0); $uid=$_SESSION['user_id'];
    $db=getDB(); $stmt=$db->prepare("UPDATE notifications SET is_read=1 WHERE id=? AND user_id=?");
    $stmt->bind_param('ii',$id,$uid); $stmt->execute(); jsonResponse(['success'=>true]);
}

function markAllRead() {
    $uid=$_SESSION['user_id']; $db=getDB();
    $stmt=$db->prepare("UPDATE notifications SET is_read=1 WHERE user_id=?");
    $stmt->bind_param('i',$uid); $stmt->execute(); jsonResponse(['success'=>true]);
}

function unreadCount() {
    $uid=$_SESSION['user_id']; $db=getDB();
    $stmt=$db->prepare("SELECT COUNT(*) as c FROM notifications WHERE user_id=? AND is_read=0");
    $stmt->bind_param('i',$uid); $stmt->execute();
    jsonResponse(['count'=>$stmt->get_result()->fetch_assoc()['c']]);
}

function getProfile() {
    $uid=$_SESSION['user_id']; $db=getDB();
    $stmt=$db->prepare("SELECT id,full_name,email,phone,address,avatar,created_at FROM users WHERE id=?");
    $stmt->bind_param('i',$uid); $stmt->execute();
    jsonResponse(['user'=>$stmt->get_result()->fetch_assoc()]);
}

function updateProfile() {
    $uid=$_SESSION['user_id']; $db=getDB();
    $name=sanitize($_POST['full_name']??''); $phone=sanitize($_POST['phone']??''); $addr=sanitize($_POST['address']??'');
    $avatar=$_SESSION['avatar'];
    if (isset($_FILES['avatar'])&&$_FILES['avatar']['error']===0) {
        $ext=pathinfo($_FILES['avatar']['name'],PATHINFO_EXTENSION);
        $avatar=uniqid().'.'.$ext;
        move_uploaded_file($_FILES['avatar']['tmp_name'],UPLOAD_PATH.'avatars/'.$avatar);
    }
    $stmt=$db->prepare("UPDATE users SET full_name=?,phone=?,address=?,avatar=? WHERE id=?");
    $stmt->bind_param('ssssi',$name,$phone,$addr,$avatar,$uid); $stmt->execute();
    $_SESSION['user_name']=$name; $_SESSION['avatar']=$avatar;
    jsonResponse(['success'=>true,'avatar'=>$avatar]);
}

function changePassword() {
    $uid=$_SESSION['user_id']; $db=getDB();
    $old=$_POST['old_password']??''; $new=$_POST['new_password']??'';
    if (!$old||!$new) jsonResponse(['success'=>false,'error'=>'Both passwords required'],400);
    if (strlen($new)<6) jsonResponse(['success'=>false,'error'=>'New password min 6 chars'],400);
    $stmt=$db->prepare("SELECT password FROM users WHERE id=?"); $stmt->bind_param('i',$uid); $stmt->execute();
    $user=$stmt->get_result()->fetch_assoc();
    if (!password_verify($old,$user['password'])) jsonResponse(['success'=>false,'error'=>'Current password incorrect'],401);
    $h=password_hash($new,PASSWORD_DEFAULT);
    $u=$db->prepare("UPDATE users SET password=? WHERE id=?"); $u->bind_param('si',$h,$uid); $u->execute();
    jsonResponse(['success'=>true]);
}

function userStats() {
    $uid=$_SESSION['user_id']; $db=getDB();
    $stmt=$db->prepare("SELECT COUNT(*) as total, SUM(total_amount) as spent, SUM(CASE WHEN status='active' THEN 1 ELSE 0 END) as active, SUM(CASE WHEN status='completed' THEN 1 ELSE 0 END) as completed FROM rentals WHERE user_id=?");
    $stmt->bind_param('i',$uid); $stmt->execute();
    jsonResponse(['stats'=>$stmt->get_result()->fetch_assoc()]);
}

function getMessages() {
    $uid=$_SESSION['user_id']; $db=getDB();
    $stmt=$db->prepare("SELECT * FROM messages WHERE user_id=? ORDER BY created_at DESC");
    $stmt->bind_param('i',$uid); $stmt->execute();
    jsonResponse(['messages'=>$stmt->get_result()->fetch_all(MYSQLI_ASSOC)]);
}

function sendMessage() {
    $uid=$_SESSION['user_id']; $db=getDB();
    $subject=sanitize($_POST['subject']??''); $msg=sanitize($_POST['message']??'');
    if (!$msg) jsonResponse(['success'=>false,'error'=>'Message required'],400);
    $stmt=$db->prepare("INSERT INTO messages (user_id,subject,message) VALUES (?,?,?)");
    $stmt->bind_param('iss',$uid,$subject,$msg); $stmt->execute();
    jsonResponse(['success'=>true]);
}

function allUsers() {
    $db=getDB();
    $stmt=$db->prepare("SELECT u.*,(SELECT COUNT(*) FROM rentals WHERE user_id=u.id) as rental_count FROM users u WHERE u.role='user' ORDER BY u.created_at DESC");
    $stmt->execute(); jsonResponse(['users'=>$stmt->get_result()->fetch_all(MYSQLI_ASSOC)]);
}

function adminStats() {
    $db=getDB();
    $r=$db->query("SELECT COUNT(*) as total_rentals, SUM(total_amount) as revenue, SUM(CASE WHEN status='active' THEN 1 ELSE 0 END) as active_rentals, SUM(CASE WHEN status='pending' THEN 1 ELSE 0 END) as pending_rentals FROM rentals")->fetch_assoc();
    $u=$db->query("SELECT COUNT(*) as total_users FROM users WHERE role='user'")->fetch_assoc();
    $p=$db->query("SELECT COUNT(*) as total_products FROM products")->fetch_assoc();
    $m=$db->query("SELECT COUNT(*) as open_messages FROM messages WHERE status='open'")->fetch_assoc();
    // Monthly revenue (last 6 months)
    $monthly=$db->query("SELECT DATE_FORMAT(created_at,'%b %Y') as month, SUM(total_amount) as revenue, COUNT(*) as rentals FROM rentals WHERE created_at>=DATE_SUB(NOW(),INTERVAL 6 MONTH) GROUP BY YEAR(created_at),MONTH(created_at) ORDER BY created_at")->fetch_all(MYSQLI_ASSOC);
    jsonResponse(['stats'=>array_merge($r,$u,$p,$m),'monthly'=>$monthly]);
}
