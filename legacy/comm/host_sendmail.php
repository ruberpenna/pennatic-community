<link rel="stylesheet" type="text/css" href="./bootstrap/css/bootstrap.css">
<link rel="stylesheet" type="text/css" href="https://cdnjs.cloudflare.com/ajax/libs/sweetalert/1.1.3/sweetalert.min.css" />
<script type="text/javascript" src="./js/jquery-2.1.3.min.js"></script>
<script type="text/javascript" src="./bootstrap/js/bootstrap.js"></script>
<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/sweetalert/1.1.3/sweetalert.min.js"></script>

<?php
include_once('./mailer.lib.php');
session_start();

$sendmail = $_POST['sendmail'];
$mail_title = $_POST['mail_title'];
$mail_contents = $_POST['mail_contents'];

if(isset($_SESSION['mem_id'])){
  $mem_id = $_SESSION['mem_id'];
} else {
  $mem_id = 'guest';
}

//이메일 형식 체크
$check_email=preg_match("/^[_\.0-9a-zA-Z-]+@([0-9a-zA-Z][0-9a-zA-Z-]+\.)+[a-zA-Z]{2,6}$/i", $sendmail);
if($check_email==false)
{
  echo "<script>
        $(window).load(function(){
          $(document).ready(function () {
            swal({
              title: '올바른 이메일 형식이 아닙니다.',
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

if(empty($sendmail) || empty($mail_title) || empty($mail_contents)){
  echo "<script>
        $(window).load(function(){
          $(document).ready(function () {
            swal({
              title: '빈 칸이 존재합니다. 전부 입력해주세요.',
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
} else {
    mailer($mem_id, "mr_kururu@naver.com", "mr_kururu@naver.com", $mail_title, "(Penna - ".$sendmail."에서 보내온 메일 전문입니다.)<br/>".$mail_contents, 1);
    echo "<script>
          $(window).load(function(){
            $(document).ready(function () {
              swal({
                title: '메일이 정상적으로 전송하였습니다.',
                type: 'success',
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
