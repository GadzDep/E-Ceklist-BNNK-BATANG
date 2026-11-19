<?php
require_once __DIR__.'/../config/koneksi.php';
if($_SERVER['REQUEST_METHOD']!=='POST') redirect('auth/login.php');
verify_csrf();
$username=trim($_POST['username']??''); $password=$_POST['password']??'';
$stmt=$conn->prepare('SELECT id,nama_lengkap,username,password,role FROM users WHERE username=? LIMIT 1');
$stmt->bind_param('s',$username); $stmt->execute(); $user=$stmt->get_result()->fetch_assoc();
if($user && password_verify($password,$user['password'])){
 session_regenerate_id(true); unset($user['password']); $_SESSION['user']=$user; redirect(dashboard_path());
}
redirect('auth/login.php?error=1');
