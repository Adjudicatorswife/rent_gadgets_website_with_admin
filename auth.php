<?php
error_reporting(0);
ini_set('display_errors', 0);
header('Content-Type: application/json');
require_once '../php/config.php';
$action = $_POST['action'] ?? $_GET['action'] ?? '';

switch ($action) {
    case 'login': login(); break;
    case 'register': register(); break;
    case 'logout': logout(); break;
    case 'check':
        jsonResponse(['logged_in'=>isLoggedIn(),'user'=>isLoggedIn()?['id'=>$_SESSION['user_id'],'name'=>$_SESSION['user_name'],'role'=>$_SESSION['role'],'avatar'=>$_SESSION['avatar']]:null]);
        break;
    default: jsonResponse(['error'=>'Invalid action'],400);
}

function login() {
    $email = sanitize($_POST['email'] ?? '');
    $pass = $_POST['password'] ?? '';
    if (!$email || !$pass) jsonResponse(['success'=>false,'error'=>'All fields required'],400);
    $db = getDB();
    $stmt = $db->prepare("SELECT id,full_name,email,password,role,avatar FROM users WHERE email=?");
    $stmt->bind_param('s',$email); $stmt->execute();
    $user = $stmt->get_result()->fetch_assoc();
    if (!$user || !password_verify($pass,$user['password'])) jsonResponse(['success'=>false,'error'=>'Invalid email or password'],401);
    $_SESSION['user_id']=$user['id']; $_SESSION['user_name']=$user['full_name'];
    $_SESSION['user_email']=$user['email']; $_SESSION['role']=$user['role']; $_SESSION['avatar']=$user['avatar'];
    jsonResponse(['success'=>true,'user'=>['id'=>$user['id'],'name'=>$user['full_name'],'role'=>$user['role'],'avatar'=>$user['avatar']],'redirect'=>$user['role']==='admin'?BASE_URL.'/admin/dashboard.php':BASE_URL.'/pages/home.php']);
}

function register() {
    $name=sanitize($_POST['name']??''); $email=sanitize($_POST['email']??'');
    $pass=$_POST['password']??''; $phone=sanitize($_POST['phone']??'');
    if (!$name||!$email||!$pass) jsonResponse(['success'=>false,'error'=>'All fields required'],400);
    if (!filter_var($email,FILTER_VALIDATE_EMAIL)) jsonResponse(['success'=>false,'error'=>'Invalid email'],400);
    if (strlen($pass)<6) jsonResponse(['success'=>false,'error'=>'Password min 6 chars'],400);
    $db=getDB();
    $c=$db->prepare("SELECT id FROM users WHERE email=?"); $c->bind_param('s',$email); $c->execute();
    if ($c->get_result()->num_rows>0) jsonResponse(['success'=>false,'error'=>'Email already registered'],409);
    $h=password_hash($pass,PASSWORD_DEFAULT);
    $s=$db->prepare("INSERT INTO users (full_name,email,password,phone) VALUES (?,?,?,?)");
    $s->bind_param('ssss',$name,$email,$h,$phone);
    if ($s->execute()) { sendNotification($db->insert_id,'Welcome to RELAPSE!','Your account is ready. Start renting today!','system'); jsonResponse(['success'=>true,'message'=>'Account created!']); }
    else jsonResponse(['success'=>false,'error'=>'Registration failed'],500);
}

function logout() { session_destroy(); jsonResponse(['success'=>true,'redirect'=>BASE_URL.'/pages/index.php']);}
