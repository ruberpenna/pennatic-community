<link rel="stylesheet" type="text/css" href="../bootstrap/css/bootstrap.css">
<link rel="stylesheet" type="text/css" href="https://cdnjs.cloudflare.com/ajax/libs/sweetalert/1.1.3/sweetalert.min.css" />
<script type="text/javascript" src="../js/jquery-2.1.3.min.js"></script>
<script type="text/javascript" src="../bootstrap/js/bootstrap.js"></script>
<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/sweetalert/1.1.3/sweetalert.min.js"></script>
<?php
include("../dbconfig.php");
session_start();

if(!isset($_POST['re_recv_id']) || !isset($_POST['re_msg_title']) || !isset($_POST['re_msg_contents'])) exit;
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
if(empty($_POST['re_recv_id'])){
    echo "<script>
          $(window).load(function(){
            $(document).ready(function () {
              swal({
                title: '쪽지를 받을 상대방 아이디를 입력해주세요.',
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
if(empty($_POST['re_msg_title']) || empty($_POST['re_msg_contents'])){
    echo "<script>
          $(window).load(function(){
            $(document).ready(function () {
              swal({
                title: '제목 혹은 내용이 입력되지 않았습니다.',
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

$re_recv_id = $_POST['re_recv_id'];
$re_msg_title = $_POST['re_msg_title'];
$re_msg_contents = nl2br($_POST['re_msg_contents']);
$fk_mem_id = $_SESSION['mem_id'];

try{
  $pdo = new PDO($dsn, $dbUser, $dbPwd);
  $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
  //$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMORE_EXCEPTION);

  $sql = "SELECT mem_id FROM tbl_member WHERE mem_id = '$re_recv_id'";
  $stm = $pdo->prepare($sql);
  $stm->execute();
  $result = $stm->fetchAll(PDO::FETCH_ASSOC);

  foreach ($result as $row) {
    $check = $row['mem_id'];
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

//아이디가 존재하지 않을 경우
if(empty($check)){
  echo "<script>
        $(window).load(function(){
          $(document).ready(function () {
            swal({
              title: '수신인 아이디가 존재하지 않는 아이디입니다.',
              type: 'warning',
              confirmButtonClass: 'btn-warning',
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

  $sql = "insert into tbl_message (msg_recvid, msg_sendid, msg_title, msg_contents, msg_sentdate)
          values ('$re_recv_id', '$fk_mem_id', '$re_msg_title', '$re_msg_contents', now())";
  $stm = $pdo->prepare($sql);
  $stm->execute();

  echo "<script>
        $(window).load(function(){
          $(document).ready(function () {
            swal({
              title: '답장 전송이 완료되었습니다.',
              type: 'success',
              confirmButtonClass: 'btn-success',
              confirmButtonText: '확인',
            },
            function(isConfirm) {
              if (isConfirm) {
               location.href='./msglist.php';
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
