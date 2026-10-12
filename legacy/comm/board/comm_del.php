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

if(!isset($_GET['comm_idx'])) exit;
if(empty($_GET['comm_idx'])){
  echo "<script>
        $(window).load(function(){
          $(document).ready(function () {
            swal({
              title: '존재하는 댓글이 아닙니다.',
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
  exit();
}

$idx = $_GET['comm_idx'];
$mem_id = $_SESSION['mem_id'];

try{
  $pdo = new PDO($dsn, $dbUser, $dbPwd);
  $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
  //$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMORE_EXCEPTION);

  $sql = "UPDATE tbl_comment set comm_status = 0
          where comm_idx = '$idx'
          and comm_status = 1
          and fk_mem_id = '$fk_mem_id'";
  $stm = $pdo->prepare($sql);
  $stm->execute();

  echo "<script>
        $(window).load(function(){
          $(document).ready(function () {
            swal({
              title: '댓글 삭제가 완료되었습니다.',
              type: 'success',
              confirmButtonClass: 'btn-success',
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

} catch (Exception $e){
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
  echo $e->getMessage();
  exit;
}

?>
