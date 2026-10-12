<link rel="stylesheet" type="text/css" href="./bootstrap/css/bootstrap.css">
<link rel="stylesheet" type="text/css" href="https://cdnjs.cloudflare.com/ajax/libs/sweetalert/1.1.3/sweetalert.min.css" />
<script type="text/javascript" src="./js/jquery-2.1.3.min.js"></script>
<script type="text/javascript" src="./bootstrap/js/bootstrap.js"></script>
<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/sweetalert/1.1.3/sweetalert.min.js"></script>

<?php
include("../dbconfig.php");
error_reporting(E_ALL);
ini_set("display_errors", 1);


if(isset($_POST['folder'])){
  //게시판 리스트
  $folder = $_POST['folder'];
  try{
  	$pdo = new PDO($dsn, $dbUser, $dbPwd);
    $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
    //$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMORE_EXCEPTION);

  	$sql = "SELECT DISTINCT board_group FROM tbl_board WHERE board_folder = '$folder' AND board_status = 3";
  	$stm = $pdo->prepare($sql);
  	$stm->execute();
  	$g_result = $stm->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($g_result);

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
} else {
  //말머리 리스트
  $group = $_POST['group'];
  try{
  	$pdo = new PDO($dsn, $dbUser, $dbPwd);
    $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
    //$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMORE_EXCEPTION);

  	$sql = "SELECT DISTINCT board_subject FROM tbl_board WHERE board_group = '$group' AND board_status = 3";
  	$stm = $pdo->prepare($sql);
  	$stm->execute();
  	$s_result = $stm->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($s_result);

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
