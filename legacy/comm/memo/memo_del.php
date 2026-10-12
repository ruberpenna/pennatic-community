<link rel="stylesheet" type="text/css" href="../bootstrap/css/bootstrap.css">
<link rel="stylesheet" type="text/css" href="https://cdnjs.cloudflare.com/ajax/libs/sweetalert/1.1.3/sweetalert.min.css" />
<script type="text/javascript" src="../js/jquery-2.1.3.min.js"></script>
<script type="text/javascript" src="../bootstrap/js/bootstrap.js"></script>
<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/sweetalert/1.1.3/sweetalert.min.js"></script>

<?php
include("../dbconfig.php");
error_reporting(E_ALL);
ini_set("display_errors", 1);

if(!isset($_GET['memo_idx'])) exit;
if(empty($_GET['memo_idx'])){
    echo "<script>
          $(window).load(function(){
            $(document).ready(function () {
              swal({
                title: '존재하는 메모가 아닙니다.',
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

$idx = $_GET['memo_idx'];

try{
  $pdo = new PDO($dsn, $dbUser, $dbPwd);
  $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
  //$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMORE_EXCEPTION);

  $sql = "DELETE from tbl_memo
          where memo_idx = '$idx'";
  $stm = $pdo->prepare($sql);
  $stm->execute();

  echo "<script>
        $(window).load(function(){
          $(document).ready(function () {
            swal({
              title: '메모 삭제가 완료되었습니다.',
              type: 'success',
              confirmButtonClass: 'btn-primary',
              confirmButtonText: '확인',
            },
            function(isConfirm) {
              if (isConfirm) {
               location.href='./memolist.php;
              }
            });
          });
        });
      </script>";

} catch (Exception $e){
  echo "<script>alert('데이터베이스 쪽에 문제가 발생했습니다.');</script>";
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
