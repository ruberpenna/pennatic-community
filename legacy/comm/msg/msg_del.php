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

if(!isset($_GET['msg_idx'])){
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

$idx = $_GET['msg_idx'];
$fk_mem_id = $_SESSION['mem_id'];

try{
  $pdo = new PDO($dsn, $dbUser, $dbPwd);
  $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
  //$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMORE_EXCEPTION);

  $sql = "SELECT msg_recvid, msg_sendid FROM tbl_message WHERE msg_idx = '$idx' AND (msg_recvstatus = 1 or msg_sentstatus = 1)";
  $stm = $pdo->prepare($sql);
  $stm->execute();
  $result = $stm->fetchAll(PDO::FETCH_ASSOC);

  foreach ($result as $row) {
    $chk_recv = $row['msg_recvid'];
    $chk_send = $row['msg_sendid'];
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
  exit;
}


//자기가 수신인 일경우, 자기가 발신인 일경우, 둘다 해당 안되는 경우
if($chk_recv == $fk_mem_id){
  try{
    $pdo = new PDO($dsn, $dbUser, $dbPwd);
    $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
    //$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMORE_EXCEPTION);


    $sql = "UPDATE tbl_message set msg_recvstatus = 0
            where msg_idx = '$idx'";
    $stm = $pdo->prepare($sql);
    $stm->execute();

    echo "<script>location.href='./msglist.php'</script>";

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
} elseif ($chk_send == $fk_mem_id){
  try{
    $pdo = new PDO($dsn, $dbUser, $dbPwd);
    $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
    //$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMORE_EXCEPTION);


    $sql = "UPDATE tbl_message set msg_sentstatus = 0
            where msg_idx = '$idx'";
    $stm = $pdo->prepare($sql);
    $stm->execute();

    echo "<script>location.href='./msglist.php'</script>";

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
} else {
  echo "<script>
        $(window).load(function(){
          $(document).ready(function () {
            swal({
              title: '잘못된 접근입니다. 운영자에게 문의하세요.',
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



?>
