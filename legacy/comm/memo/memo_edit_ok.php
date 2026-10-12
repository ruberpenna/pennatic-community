<link rel="stylesheet" type="text/css" href="../bootstrap/css/bootstrap.css">
<link rel="stylesheet" type="text/css" href="https://cdnjs.cloudflare.com/ajax/libs/sweetalert/1.1.3/sweetalert.min.css" />
<script type="text/javascript" src="../js/jquery-2.1.3.min.js"></script>
<script type="text/javascript" src="../bootstrap/js/bootstrap.js"></script>
<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/sweetalert/1.1.3/sweetalert.min.js"></script>

<?php
include("../dbconfig.php");
session_start();

if(!isset($_POST['memo_subject']) || !isset($_POST['memo_title']) || !isset($_POST['memo_contents'])) exit;
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
if(empty($_POST['memo_subject']) || empty($_POST['memo_title']) || empty($_POST['memo_contents'])){
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

if(isset($_POST['memo_subject'])){
  $memo_subject = $_POST['memo_subject'];
}
if(isset($_POST['newSubject'])){
  $newSubject = $_POST['newSubject'];
}
$idx = $_POST['memo_idx'];
$title = $_POST['memo_title'];
$contents = $_POST['memo_contents'];
$fk_mem_id = $_SESSION['mem_id'];

if(memo_subject == "새분류"){
  $subject = $newSubject;
} else {
  $subject = $memo_subject;
}


try{
  $pdo = new PDO($dsn, $dbUser, $dbPwd);
  $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
  //$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMORE_EXCEPTION);


  $sql = "UPDATE tbl_memo set memo_subject = '$subject', memo_title = '$title', memo_content = '$contents', memo_edate = now()
          where memo_idx = '$idx'";
  $stm = $pdo->prepare($sql);
  $stm->execute();

  echo "<script>location.href='./memolist.php'</script>";

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
