<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8" />
	<title>Penna - Board</title>
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
		exit;
	}
	if(isset($_GET['board_folder']) && isset($_GET['board_group'])){
	  $folder = $_GET['board_folder'];
	  $group = $_GET['board_group'];
	} else {
		echo "<script>
					$(window).load(function(){
						$(document).ready(function () {
							swal({
								title: '잘못된 접근입니다.',
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

	try{
		$pdo = new PDO($dsn, $dbUser, $dbPwd);
	  $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
	  //$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMORE_EXCEPTION);

	  $sql = "SELECT DISTINCT board_subject FROM tbl_board WHERE board_folder = '" . $folder . "' AND board_group = '" . $group ."'";
		$stm = $pdo->prepare($sql);
		$stm->execute();
		$sublist = $stm->fetchAll(PDO::FETCH_ASSOC);

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

		function goFileAdd() {
		  oTbl = document.getElementById("fileTable");
		  var oRow = oTbl.insertRow();
		  oRow.onmouseover=function(){oTbl.clickedRowIndex=this.rowIndex}; //clickedRowIndex - 클릭한 Row의 위치를 확인;
		  var oCell = oRow.insertCell();

		  //삽입될 Form Tag
		  var frmTag = "<input name='boardfile[]' type='file' class='form-control' />&nbsp;";
		  frmTag += '<a href="javascript:goFileRemove();" class="btn btn-xs btn-default"><span class="glyphicon glyphicon-minus"></span></a>';
		  oCell.innerHTML = frmTag;
		}

		function goFileRemove() {
		  oTbl.deleteRow(oTbl.clickedRowIndex);
		}
	</script>
</head>
<body>
<div style="height: 100%; overflow: hidden; margin-left: 10%; margin-right: 10%;">
	<div class="imgtop">
    <a href="./notice.php" target="content"><img src="../images/plogo.png" style="width: 150px; height: 150px; margin-top: 25px;"/></a>
  </div>

	<div style="text-align: center;">
    <h1>게시판 글쓰기</h1>
  </div>

	<br/>

	<div class="writeContents">
		<form class="form-inline" method="post" action="board_write_ok.php" enctype="multipart/form-data">
			<table border="0" align="center">
        <tr>
          <td style="width: 10%; text-align: center;">말머리</td>
					<td style="width: 90%;">
            <div style="float: left;">
              <select id="board_subject" name="board_subject" class="form-control">
                <?php
                  foreach($sublist as $row){
                    echo '<option value="' . $row['board_subject'] .'">' . $row['board_subject'] .'</option>';
                  }
                ?>
      				</select>
            </div>
					</td>
        </tr>
				<tr>
					<td style="width: 10%; text-align: center;">글제목</td>
					<td style="width: 90%;">
						<input type="text" class="form-control" id="board_title" name="board_title" maxlength="100" minlength="2" style="width: 100%;" />
					</td>
				</tr>
				<tr>
					<td style="width: 10%; text-align: center;">글내용</td>
					<td style="width: 90%;">
						<textarea class="summernote" id="board_contents" name="board_contents"></textarea>
					</td>
				</tr>
				<tr>
					<td style="width: 10%; text-align: center;">파일첨부</td>
					<td style="width: 90%;">
					 <table id="fileTable" width="400" cellspacing="0" cellpadding="0" bgcolor="#FFFFFF" border="0">
            <tr>
              <td>
							 <input name="boardfile[]" type="file" class="form-control" />
		 					 <a href="javascript:goFileAdd();" class="btn btn-xs btn-default"><span class="glyphicon glyphicon-plus"></span></a>
							</td>
              <td align="left"></td>
            </tr>
          </table>
					</td>
				</tr>
			</table>
			<br/>
			<div style="text-align: center;">
				<button type="submit" class="btn btn-lg btn-success">등록</button>
				<a href="javascript:history.back()" class="btn btn-lg btn-warning">뒤로가기</a>
			</div>

			<input type="hidden" id="board_folder" name="board_folder" value="<?php echo $folder ?>" />
			<input type="hidden" id="board_group" name="board_group" value="<?php echo $group ?>" />
		</form>
	</div>
</div>

<br/><br/>

</body>
</html>
