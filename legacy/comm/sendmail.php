<link rel="stylesheet" type="text/css" href="./bootstrap/css/bootstrap.css">
<link rel="stylesheet" type="text/css" href="https://cdnjs.cloudflare.com/ajax/libs/sweetalert/1.1.3/sweetalert.min.css" />
<script type="text/javascript" src="./js/jquery-2.1.3.min.js"></script>
<script type="text/javascript" src="./bootstrap/js/bootstrap.js"></script>
<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/sweetalert/1.1.3/sweetalert.min.js"></script>

<?php
include_once('./mailer.lib.php');

$mail = $_GET['mail'];

if(empty($mail)){
  echo "<script>
        $(window).load(function(){
          $(document).ready(function () {
            swal({
              title: '이메일을 입력해주세요.',
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
    $confirm = mt_rand(1000, 9999);
    mailer("Penna", "mr_kururu@naver.com", $mail, "Penna에서 보내는 비밀번호 찾기 인증메일입니다.", "인증번호 [".$confirm."]를 입력하세요.", 1);
    echo "<script>
          $(window).load(function(){
            $(document).ready(function () {
              swal({
                title: '인증번호를 메일로 전송하였습니다.',
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

    session_start();
    $_SESSION['confirm_num'] = $confirm;
    exit;
}
?>
