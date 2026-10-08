<?php
require_once "config/database.php"; require_once "config/auth.php"; require_admin();
if($_SERVER["REQUEST_METHOD"]==="POST"){
 $action=$_POST["action"]??"";$id=(int)($_POST["report_id"]??0);
 if($action==="update"){
  $status=$_POST["status"]??"New";$priority=$_POST["priority"]??"Medium";$remarks=trim($_POST["remarks"]??"");
  $q=$pdo->prepare("SELECT status FROM reports WHERE report_id=?");$q->execute([$id]);$old=$q->fetchColumn();
  $q=$pdo->prepare("UPDATE reports SET status=?,priority=?,assigned_user_id=?,updated_at=NOW() WHERE report_id=?");$q->execute([$status,$priority,$_SESSION["admin_id"],$id]);
  if($old!==$status){$q=$pdo->prepare("INSERT INTO report_status_history(report_id,changed_by,old_status,new_status,remarks) VALUES(?,?,?,?,?)");$q->execute([$id,$_SESSION["admin_id"],$old,$status,$remarks]);}
 }
 if($action==="message"){ $msg=trim($_POST["message"]??"");if($msg!==""){$q=$pdo->prepare("INSERT INTO report_messages(report_id,sender_type,message_content) VALUES(?,'personnel',?)");$q->execute([$id,$msg]);}}
 header("Location: admin.php?view=".$id);exit;
}
$total=$pdo->query("SELECT COUNT(*) FROM reports")->fetchColumn();
$new=$pdo->query("SELECT COUNT(*) FROM reports WHERE status='New'")->fetchColumn();
$review=$pdo->query("SELECT COUNT(*) FROM reports WHERE status='In Review'")->fetchColumn();
$resolved=$pdo->query("SELECT COUNT(*) FROM reports WHERE status='Resolved'")->fetchColumn();
$reports=$pdo->query("SELECT r.*,c.category_name FROM reports r JOIN concern_categories c ON r.category_id=c.category_id ORDER BY r.created_at DESC")->fetchAll();
$view=(int)($_GET["view"]??0);$detail=null;$messages=[];
if($view){$q=$pdo->prepare("SELECT r.*,c.category_name FROM reports r JOIN concern_categories c ON r.category_id=c.category_id WHERE r.report_id=?");$q->execute([$view]);$detail=$q->fetch();if($detail){$q=$pdo->prepare("SELECT * FROM report_messages WHERE report_id=? ORDER BY created_at");$q->execute([$view]);$messages=$q->fetchAll();}}
?>
<!doctype html><html><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Dashboard | PAMATI</title><link rel="stylesheet" href="assets/style.css"></head>
<body><header class="top"><div class="brand">♡ PAMATI Admin</div><nav class="nav"><a href="index.php">Public Site</a><a href="logout.php">Logout</a></nav></header><main class="wrap">
<div class="page-title"><span class="eyebrow">AUTHORIZED PERSONNEL</span><h1>Dashboard</h1><p class="muted">Welcome, <?=h($_SESSION["admin_name"]??"Administrator")?>.</p></div>
<div class="stats"><div class="stat"><span>Total Reports</span><strong><?=$total?></strong></div><div class="stat"><span>New</span><strong><?=$new?></strong></div><div class="stat"><span>In Review</span><strong><?=$review?></strong></div><div class="stat"><span>Resolved</span><strong><?=$resolved?></strong></div></div>
<div class="card"><h2>Recent Reports</h2><div class="table-wrap"><table><tr><th>Code</th><th>Category</th><th>Priority</th><th>Status</th><th>Date</th><th></th></tr><?php foreach($reports as $r):?><tr><td><strong><?=h($r["report_code"])?></strong></td><td><?=h($r["category_name"])?></td><td class="<?=strtolower(h($r["priority"]))?>"><?=h($r["priority"])?></td><td><span class="badge <?=strtolower(str_replace(" ","",$r["status"]))?>"><?=h($r["status"])?></span></td><td><?=h($r["created_at"])?></td><td><a class="btn secondary" href="admin.php?view=<?=$r["report_id"]?>">View</a></td></tr><?php endforeach;?></table></div></div>
<?php if($detail):?><div class="card" style="margin-top:20px"><h2><?=h($detail["report_code"])?> — <?=h($detail["concern_title"])?></h2><p class="muted"><?=h($detail["category_name"])?> · Submitted <?=h($detail["created_at"])?></p><div class="notice"><?=nl2br(h($detail["concern_description"]))?></div>
<form method="post"><input type="hidden" name="action" value="update"><input type="hidden" name="report_id" value="<?=$detail["report_id"]?>"><div class="grid"><div><label>Status</label><select name="status"><?php foreach(["New","In Review","Resolved"] as $s):?><option <?=$detail["status"]===$s?"selected":""?>><?=$s?></option><?php endforeach;?></select></div><div><label>Priority</label><select name="priority"><?php foreach(["Low","Medium","High"] as $p):?><option <?=$detail["priority"]===$p?"selected":""?>><?=$p?></option><?php endforeach;?></select></div><div class="full"><label>Remarks</label><textarea name="remarks" rows="3"></textarea></div></div><br><button class="btn primary">Save Status / Priority</button></form>
<h3>Anonymous Conversation</h3><?php foreach($messages as $m):?><div class="message <?=h($m["sender_type"])?>"><strong><?= $m["sender_type"]==="student"?"Student":"Personnel"?></strong><br><?=nl2br(h($m["message_content"]))?><small><?=h($m["created_at"])?></small></div><?php endforeach;?><form method="post"><input type="hidden" name="action" value="message"><input type="hidden" name="report_id" value="<?=$detail["report_id"]?>"><textarea name="message" rows="3" required placeholder="Send an anonymous response..."></textarea><br><br><button class="btn primary">Send Response</button></form></div><?php endif;?>
</main></body></html>