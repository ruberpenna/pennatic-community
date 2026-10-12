<link rel="stylesheet" type="text/css" href="../bootstrap/css/bootstrap.css">
<link rel="stylesheet" type="text/css" href="https://cdnjs.cloudflare.com/ajax/libs/sweetalert/1.1.3/sweetalert.min.css" />
<script type="text/javascript" src="../js/jquery-2.1.3.min.js"></script>
<script type="text/javascript" src="../bootstrap/js/bootstrap.js"></script>
<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/sweetalert/1.1.3/sweetalert.min.js"></script>

<?php
include("../dbconfig.php");
error_reporting(E_ALL);
ini_set("display_errors", 1);
session_start();

if(!isset($_GET['board_idx']) || !isset($_GET['board_status']) || !isset($_GET['board_folder']) || !isset($_GET['board_group'])) exit;
if(!isset($_SESSION['mem_id'])){
  if($_SESSION['mem_id'] != 'adminpenna')
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
  exit();
}
if(empty($_GET'board_idx']) || empty($_GET['board_status']) || empty($_GET['board_folder']) || empty($_GET['board_group'])){
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
    exit();
}

$idx = $_GET['board_idx'];
$status = $_GET['board_status'];
$folder = $_GET['board_folder'];
$group = $_GET['board_group'];

if($status == 1){
  $change == 2;
} else {
  $change == 1;
}

try{
  $pdo = new PDO($dsn, $dbUser, $dbPwd);
  $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
  //$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMORE_EXCEPTION);

  $sql = "UPDATE tbl_board set board_status = '$change'
          where board_idx = '$idx' and board_status = '$status'";
  $stm = $pdo->prepare($sql);
  $stm->execute();

  echo "<script>location.href='./board.php?board_folder=".$folder."&board_group=".$group."'</script>";

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
  exit;
}

?>
