<?php require_once "config/database.php"; require_once "config/auth.php";
$report=null;$messages=[];$error="";
$code=strtoupper(trim($_GET["code"]??""));
if($code!==""){
 $q=$pdo->prepare("SELECT r.*,c.category_name FROM reports r JOIN concern_categories c ON r.category_id=c.category_id WHERE r.report_code=?");
 $q->execute([$code]);$report=$q->fetch();
 if($report){$q=$pdo->prepare("SELECT * FROM report_messages WHERE report_id=? ORDER BY created_at");$q->execute([$report["report_id"]]);$messages=$q->fetchAll();}else $error="Report not found.";
}
if($_SERVER["REQUEST_METHOD"]==="POST" && ($_POST["action"]??"")==="message"){
 $code=strtoupper(trim($_POST["report_code"]??""));$msg=trim($_POST["message"]??"");
 $q=$pdo->prepare("SELECT report_id FROM reports WHERE report_code=?");$q->execute([$code]);$r=$q->fetch();
 if($r && $msg!==""){$q=$pdo->prepare("INSERT INTO report_messages(report_id,sender_type,message_content) VALUES(?,'student',?)");$q->execute([$r["report_id"],$msg]);header("Location: track.php?code=".urlencode($code));exit;}
}
?>
<!doctype html><html><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Track Report | PAMATI</title><link rel="stylesheet" href="assets/style.css"></head>
<body><header class="top"><div class="brand">♡ PAMATI</div><nav class="nav"><a href="index.php">Home</a><a href="report.php">Report</a><a href="track.php">Track</a><a href="login.php">Admin</a></nav></header>
<main class="wrap"><div class="page-title"><span class="eyebrow">STUDENT VIEW</span><h1>Track Your Report</h1><p class="muted">Enter your reference code to check status and communicate anonymously.</p></div>
<div class="grid"><div class="card"><form method="get"><label>Report Reference Code</label><input name="code" value="<?=h($code)?>" placeholder="Example: PMT-10231" required><br><br><button class="btn primary">Check Status</button></form>
<?php if($error):?><div class="error" style="margin-top:20px"><?=h($error)?></div><?php endif;?>
<?php if($report):?><div class="success" style="margin-top:20px"><strong>Status: <?=h($report["status"])?></strong><br>Category: <?=h($report["category_name"]) ?><br>Priority: <?=h($report["priority"]) ?><br>Last updated: <?=h($report["updated_at"])?></div><?php endif;?></div>
<div class="card"><h2>Anonymous Messages</h2><?php if(!$report):?><p class="muted">Enter a valid reference code first.</p><?php else:foreach($messages as $m):?><div class="message <?=h($m["sender_type"])?>"><strong><?= $m["sender_type"]==="student"?"You":"Personnel" ?></strong><br><?=nl2br(h($m["message_content"]))?><small><?=h($m["created_at"])?></small></div><?php endforeach;?><form method="post"><input type="hidden" name="action" value="message"><input type="hidden" name="report_code" value="<?=h($code)?>"><textarea name="message" rows="3" required placeholder="Type an anonymous message..."></textarea><br><br><button class="btn primary">Send Message</button></form><?php endif;?></div></div></main></body></html>