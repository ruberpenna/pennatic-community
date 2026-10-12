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

if($_SESSION['mem_id'] == 'adminpenna'){
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
  exit();
}

$folder = $_POST['tmp_folder'];
$group = $_POST['tmp_group'];
$page = $_POST['tmp_page'];


if(empty($_POST['checkRow'])){
  echo "<script>swal('게시물을 선택하지 않았거나 게시물이 존재하지 않습니다.');</script>";
}
else {
  $checkitem = $_POST['checkRow'];
  for ($i=0; $i<count($checkitem); $i++)
    {
      try{
        $pdo = new PDO($dsn, $dbUser, $dbPwd);
        $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
        //$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMORE_EXCEPTION);
        $pdo->beginTransaction();

        $sql = "UPDATE tbl_board set board_status = 0
                where board_idx = '$checkitem[$i]'
                and board_status = 1
                and fk_mem_id = '$mem_id'";
        $stm = $pdo->prepare($sql);
        $stm->execute();

        echo "<script>alert('삭제가 완료되었습니다.');location.href='./board.php'</script>";
        echo "<script>
              $(window).load(function(){
                $(document).ready(function () {
                  swal({
                    title: '게시물의 삭제가 완료되었습니다.',
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
        $pdo->rollback();
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
   }
   $pdo->commit();

   echo "<script>location.href='./board.php?board_folder=".$folder."&board_group=".$group."&page=".$page."';</script>";
}

?>
