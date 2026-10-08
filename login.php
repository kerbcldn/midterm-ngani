<?php require_once "config/database.php"; require_once "config/auth.php";
if(!empty($_SESSION["admin_id"])){header("Location: admin.php");exit;}
$error="";
if($_SERVER["REQUEST_METHOD"]==="POST"){
 $username=trim($_POST["username"]??"");$password=$_POST["password"]??"";
 $q=$pdo->prepare("SELECT * FROM users WHERE username=? AND status='active' LIMIT 1");$q->execute([$username]);$u=$q->fetch();
 $valid=$u && (hash("sha256",$password)===$u["password_hash"] || password_verify($password,$u["password_hash"]));
 if($valid){$_SESSION["admin_id"]=$u["user_id"];$_SESSION["admin_name"]=$u["full_name"];header("Location: admin.php");exit;}
 $error="Invalid username or password.";
}
?>
<!doctype html><html><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Admin Login | PAMATI</title><link rel="stylesheet" href="assets/style.css"></head><body>
<div class="wrap"><div class="card login"><h1>PAMATI Admin</h1><p class="muted">Authorized school personnel login</p><?php if($error):?><div class="error"><?=h($error)?></div><?php endif;?><form method="post"><label>Username</label><input name="username" required><br><br><label>Password</label><input type="password" name="password" required><br><br><button class="btn primary">Login</button></form><div class="notice">Demo login: <strong>admin</strong> / <strong>admin123</strong></div><a href="index.php">Back to PAMATI</a></div></div></body></html>