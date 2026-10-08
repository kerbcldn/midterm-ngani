<?php require_once "config/database.php"; require_once "config/auth.php";
$categories=$pdo->query("SELECT category_id,category_name FROM concern_categories WHERE status='active' ORDER BY category_name")->fetchAll();
$success=$error="";
if($_SERVER["REQUEST_METHOD"]==="POST"){
    $category=(int)($_POST["category_id"]??0); $title=trim($_POST["title"]??""); $desc=trim($_POST["description"]??"");
    if(!$category||$title===""||$desc==="") $error="Please complete all required fields.";
    else{
        do{$code="PMT-".random_int(10000,99999);$q=$pdo->prepare("SELECT COUNT(*) FROM reports WHERE report_code=?");$q->execute([$code]);}while($q->fetchColumn()>0);
        try{
            $pdo->beginTransaction();
            $q=$pdo->prepare("INSERT INTO reports(report_code,category_id,concern_title,concern_description) VALUES(?,?,?,?)");
            $q->execute([$code,$category,$title,$desc]); $reportId=$pdo->lastInsertId();
            if(!empty($_FILES["evidence"]["name"]) && $_FILES["evidence"]["error"]===UPLOAD_ERR_OK){
                $allowed=["jpg","jpeg","png","pdf","mp4"]; $ext=strtolower(pathinfo($_FILES["evidence"]["name"],PATHINFO_EXTENSION));
                if(in_array($ext,$allowed,true) && $_FILES["evidence"]["size"]<=10*1024*1024){
                    $safe=bin2hex(random_bytes(12)).".".$ext; $dest=__DIR__."/uploads/".$safe;
                    move_uploaded_file($_FILES["evidence"]["tmp_name"],$dest);
                    $q=$pdo->prepare("INSERT INTO report_attachments(report_id,file_name,file_path) VALUES(?,?,?)");
                    $q->execute([$reportId,$_FILES["evidence"]["name"],"uploads/".$safe]);
                }
            }
            $q=$pdo->prepare("INSERT INTO report_messages(report_id,sender_type,message_content) VALUES(?,'personnel',?)");
            $q->execute([$reportId,"Your report has been received. An authorized personnel member will review it."]);
            $q=$pdo->prepare("INSERT INTO report_status_history(report_id,new_status,remarks) VALUES(?,'New','Report submitted anonymously.')");
            $q->execute([$reportId]); $pdo->commit();
            $success="Report submitted successfully. Your reference code is: ".$code;
        }catch(Throwable $e){$pdo->rollBack();$error="Unable to submit the report. Please try again.";}
    }
}
?>
<!doctype html><html><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Submit Report | PAMATI</title><link rel="stylesheet" href="assets/style.css"></head>
<body><header class="top"><div class="brand">♡ PAMATI</div><nav class="nav"><a href="index.php">Home</a><a href="report.php">Report</a><a href="track.php">Track</a><a href="login.php">Admin</a></nav></header>
<main class="wrap"><div class="page-title"><span class="eyebrow">STUDENT VIEW</span><h1>Submit an Anonymous Report</h1><p class="muted">Your identity is not requested by this reporting form.</p></div>
<div class="card"><?php if($success): ?><div class="success"><?=h($success)?><br><a href="track.php">Track this report</a></div><?php endif;?><?php if($error): ?><div class="error"><?=h($error)?></div><?php endif;?>
<form method="post" enctype="multipart/form-data"><div class="grid"><div class="full"><label>Concern Category</label><select name="category_id" required><option value="">Select a category</option><?php foreach($categories as $c):?><option value="<?=$c["category_id"]?>"><?=h($c["category_name"])?></option><?php endforeach;?></select></div><div class="full"><label>Concern Title</label><input name="title" maxlength="200" required placeholder="Enter a short title"></div><div class="full"><label>Description</label><textarea name="description" rows="8" required placeholder="Describe what happened..."></textarea></div><div class="full"><label>Evidence (optional)</label><input type="file" name="evidence" accept=".jpg,.jpeg,.png,.pdf,.mp4"><small>Maximum 10 MB. Allowed: JPG, PNG, PDF, MP4.</small></div></div><div class="notice"><strong>Anonymous reporting:</strong> Do not include your name or unnecessary identifying details if you want to remain anonymous.</div><button class="btn primary" type="submit">Submit Anonymous Report</button></form></div></main></body></html>