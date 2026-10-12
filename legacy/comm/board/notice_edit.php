<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8" />
	<title>Penna - Notice</title>
	<link rel="stylesheet" type="text/css" href="../bootstrap/css/bootstrap.css">
	<link rel="stylesheet" type="text/css" href="https://cdnjs.cloudflare.com/ajax/libs/sweetalert/1.1.3/sweetalert.min.css" />
	<link href="../summernote/summernote.css" rel="stylesheet">
	<script type="text/javascript" src="../js/jquery-2.1.3.min.js"></script>
	<script type="text/javascript" src="../bootstrap/js/bootstrap.js"></script>
	<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/sweetalert/1.1.3/sweetalert.min.js"></script>
	<script src="../summernote/summernote.js"></script>
	<script src="../summernote/lang/summernote-ko-KR.js"></script>
	<?php
	include("../dbconfig.php");
	error_reporting(E_ALL);
	ini_set("display_errors", 1);

	session_start();
	if(isset($_SESSION['mem_status'])){
		$status = $_SESSION['mem_status'];
		if($status != '2'){
			echo "<script>
						$(window).load(function(){
							$(document).ready(function () {
								swal({
									title: '운영자만 접근이 가능합니다.',
									type: 'info',
									confirmButtonClass: 'btn-primary',
									confirmButtonText: '확인',
								},
								function(isConfirm) {
									if (isConfirm) {
									 history.back();
									}
								});
							});
						});
					</script>";
			exit;
		}
	}

	if(isset($_GET['main_idx'])){
	  $idx = $_GET['main_idx'];

	  try{
	    $pdo = new PDO($dsn, $dbUser, $dbPwd);
	    $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
	    //$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMORE_EXCEPTION);

	    $sql = "SELECT main_idx, main_title, main_content, main_date, main_status
	            from tbl_main
	            where main_status = 1
	            and main_idx = '$idx'";
	    $stm = $pdo->prepare($sql);
	    $stm->execute();
	    $result = $stm->fetchAll(PDO::FETCH_ASSOC);

	    if(empty($result)){
				echo "<script>
							$(window).load(function(){
								$(document).ready(function () {
									swal({
										title: '존재하는 공지글이 아닙니다.',
										type: 'info',
										confirmButtonClass: 'btn-primary',
										confirmButtonText: '확인',
									},
									function(isConfirm) {
										if (isConfirm) {
										 history.back();
										}
									});
								});
							});
						</script>";
	    } else {
	      foreach($result as $row){
	        $title = $row['main_title'];
	        $contents = $row['main_content'];
	      }
	    }

	  } catch (Exception $e){
			echo "<script>
						$(window).load(function(){
							$(document).ready(function () {
								swal({
									title: '데이터베이스에 문제가 발생했습니다. 관리자에게 문의하세요.',
									type: 'info',
									confirmButtonClass: 'btn-primary',
									confirmButtonText: '확인',
								},
								function(isConfirm) {
									if (isConfirm) {
									 history.back();
									}
								});
							});
						});
					</script>";
	    echo $e->getMessage();
	    exit();
	  }
	}

	?>
  <style type="text/css">
		.imgtop {
			text-align: center;
			height: 200px;
		}

		.writeContents {
			width: 70%;
			border: 1px solid lightgray;
			margin-left: 15%;
		 	padding-top: 3%;
			padding-bottom: 3%;
		}

		body {
			font-size: 1.5em;
			background: #eee;
		}

		table {
			width: 90%;
		}

		td {
			padding-top: 1%;
			padding-bottom: 1%;
		}
  </style>
	<script type="text/javascript">
	  $(function() {
	    $('.summernote').summernote({
	      height: 300,          // 기본 높이값
	      minHeight: null,      // 최소 높이값(null은 제한 없음)
	      maxHeight: null,      // 최대 높이값(null은 제한 없음)
	      focus: true,          // 페이지가 열릴때 포커스를 지정함
	      lang: 'ko-KR'         // 한국어 지정(기본값은 en-US)
	    });
	  });
	</script>
</head>
<body>
<div style="height: 100%; overflow: hidden; margin-left: 10%; margin-right: 10%;">
	<div class="imgtop">
    <a href="./notice.php" target="content"><img src="../images/plogo.png" style="width: 150px; height: 150px; margin-top: 25px;"/></a>
  </div>

	<div style="text-align: center;">
    <h1>공지사항 글수정</h1>
  </div>

	<br/>

	<div class="writeContents">
		<form class="form-inline" method="post" action="notice_edit_ok.php">
			<table border="0" align="center">
				<tr>
					<td style="width: 10%; text-align: center;">글제목</td>
					<td style="width: 90%;">
						<input type="text" class="form-control" id="title" name="title" maxlength="100" minlength="2" style="width: 100%;" value="<?php echo $title ?>" />
					</td>
				</tr>
				<tr>
					<td style="width: 10%; text-align: center;">글내용</td>
					<td style="width: 90%;">
						<textarea class="summernote" id="contents" name="contents"><?php echo $contents ?></textarea>
					</td>
				</tr>
			</table>
			<br/>
			<div style="text-align: center;">
				<button type="submit" class="btn btn-lg btn-success">수정</button>
				<a href="javascript:history.back()" class="btn btn-lg btn-warning">뒤로가기</a>
			</div>
      <input type="hidden" id="idx" name="idx" value="<?php echo $idx ?>" />
		</form>
	</div>
</div>

<br/><br/>

</body>
</html>
