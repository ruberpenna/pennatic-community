<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8" />
	<title>Penna - Notice</title>
	<link rel="stylesheet" type="text/css" href="../bootstrap/css/bootstrap.css">
	<link rel="stylesheet" type="text/css" href="https://cdnjs.cloudflare.com/ajax/libs/sweetalert/1.1.3/sweetalert.min.css" />
	<script type="text/javascript" src="../js/jquery-2.1.3.min.js"></script>
	<script type="text/javascript" src="../bootstrap/js/bootstrap.js"></script>
	<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/sweetalert/1.1.3/sweetalert.min.js"></script>
	<?php
	include("./dbconfig.php");
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
	               history.back();
	              }
	            });
	          });
	        });
	      </script>";
	}

	if($_SESSION['mem_status'] != 2){
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
	}

	?>
  <style type="text/css">
    .imgtop {
      text-align: center;
			height: 200px;
    }

    body {
      font-size: 1.5em;
      background: #eee;
    }

		table {
			width: 50%;
			margin-left: 25%;
			margin-top: 10%;
			text-align: center;
		}

		td {
			font-weight: bold;
		}

		a:link { color: black; text-decoration: none;}
    a:hover { color: black; text-decoration: none;}

  </style>
	<script type="text/javascript">

	</script>
</head>
<body>
  <div style="height: 100%; overflow: auto; margin-left: 10%; margin-right: 10%;">

    <div class="imgtop">
      <a href="../board/notice.php" target="content"><img src="../images/plogo.png" style="width: 150px; height: 150px; margin-top: 25px;"/></a>
    </div>

		<div style="text-align: center;">
      <h1>관리 페이지</h1>
    </div>

		<table>
			<tr>
				<td>
					<a href="./admin_category.php" target="content">
						<img src="../images/category_logo.png" style="width: 150px; height: 150px;" />
					</a>
				</td>
				<td>
					<a href="./admin_member.php" target="content">
						<img src="../images/member_logo.png" style="width: 150px; height: 150px;" />
					</a>
				</td>
				<td>
					<a href="./admin_file.php" target="content">
						<img src="../images/file_logo.png" style="width: 150px; height: 150px;" />
					</a>
				</td>
			</tr>
			<tr>
				<td>
					<br/>
					<a href="./admin_category.php" target="content">카테고리 관리</a>
				</td>
				<td>
					<br/>
					<a href="./admin_member.php" target="content">회원 관리</a>
				</td>
				<td>
					<br/>
					<a href="./admin_file.php" target="content">파일 관리</a>
				</td>
			</tr>
		</table>

  </div>
</body>
</html>
