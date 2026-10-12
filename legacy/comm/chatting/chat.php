<!DOCTYPE html>
<html lang="ko">
<head>
<title>Penna - Chatting</title>
<meta charset="utf-8">
<link rel="stylesheet" type="text/css" href="../bootstrap/css/bootstrap.css">
<link rel="stylesheet" type="text/css" href="https://cdnjs.cloudflare.com/ajax/libs/sweetalert/1.1.3/sweetalert.min.css" />
<script type="text/javascript" src="../js/jquery-2.1.3.min.js"></script>
<script type="text/javascript" src="../bootstrap/js/bootstrap.js"></script>
<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/sweetalert/1.1.3/sweetalert.min.js"></script>
<script type="text/javascript" src="chat.js"></script>
<link rel="stylesheet" type="text/css" href="chat.css" />
</head>
<?php
include("../dbconfig.php");
error_reporting(E_ALL);
ini_set("display_errors", 1);
session_start();

if(!isset($_SESSION['mem_id'])){
	echo "<script>
				$(window).load(function(){
					$(document).ready(function () {
						swal({
							title: '로그인이 필요합니다.',
							type: 'info',
							confirmButtonClass: 'btn-primary',
							confirmButtonText: '확인',
						},
						function(isConfirm) {
							if (isConfirm) {
							 window.close();
							}
						});
					});
				});
			</script>";
}
?>
<body>
<dl id="list"></dl>
<form class="form-inline" onsubmit="chatManager.write(this); return false;">
		<input name="name" id="name" type="text" class="form-control" style="display: inline-block;" value="<?php echo $_SESSION['mem_id'] ?>" readonly />
		<input name="msg" id="msg" type="text" class="form-control" style="display: inline-block;" maxlength="123" />
		<input name="btn" id="btn" type="submit" class="btn btn-xs btn-primary" style="display: inline-block;" value="입력" />
</form>
</body>
</html>
