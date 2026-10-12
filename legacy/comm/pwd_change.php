<?php
include("dbconfig.php");
error_reporting(E_ALL);
ini_set("display_errors", 1);

if(!isset($_POST['new_pwd']) || !isset($_POST['new_repwd'])) exit;
if(empty($_POST['new_pwd']) || empty($_POST['new_repwd'])){
    echo "<script>
          $(window).load(function(){
            $(document).ready(function () {
              swal({
                title: '새로운 비밀번호를 입력해주세요.',
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

$new_pwd = $_POST['new_pwd'];
$new_repwd = $_POST['new_repwd'];
$mem_id = $_POST['member_id'];

if($new_pwd != $new_repwd){
  echo "<script>
        $(window).load(function(){
          $(document).ready(function () {
            swal({
              title: '비밀번호와 비밀번호 확인이 서로 일치하지 않습니다.',
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

try{
  $pdo = new PDO($dsn, $dbUser, $dbPwd);
  $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
  //$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMORE_EXCEPTION);

  $sql = "update tbl_member set mem_pwd = '$new_pwd'
           where mem_id = '$mem_id'";
  $stm = $pdo->prepare($sql);
  $stm->execute();
  $result = $stm->fetchAll(PDO::FETCH_ASSOC);

  session_start();
  session_destroy();

  echo "<script>
        $(window).load(function(){
          $(document).ready(function () {
            swal({
              title: '비밀번호 변경에 성공하였습니다.',
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
