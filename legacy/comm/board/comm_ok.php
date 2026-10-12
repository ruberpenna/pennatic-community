<link rel="stylesheet" type="text/css" href="../bootstrap/css/bootstrap.css">
<link rel="stylesheet" type="text/css" href="https://cdnjs.cloudflare.com/ajax/libs/sweetalert/1.1.3/sweetalert.min.css" />
<script type="text/javascript" src="../js/jquery-2.1.3.min.js"></script>
<script type="text/javascript" src="../bootstrap/js/bootstrap.js"></script>
<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/sweetalert/1.1.3/sweetalert.min.js"></script>

<?php
include("../dbconfig.php");
session_start();

if(!isset($_POST['comment_contents']) || !isset($_POST['board_folder']) || !isset($_POST['board_group']) || !isset($_POST['board_idx']) || !isset($_POST['board_page'])) exit;
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
if(empty($_POST['comment_contents'])){
  echo "<script>
        $(window).load(function(){
          $(document).ready(function () {
            swal({
              title: '댓글 내용이 입력되지 않았습니다.',
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

$folder = $_POST['board_folder'];
$group = $_POST['board_group'];
$idx = $_POST['board_idx'];
$page = $_POST['board_page'];

$contents = $_POST['comment_contents'];
$idx = $_POST['board_idx'];
$fk_mem_id = $_SESSION['mem_id'];

try{
  $pdo = new PDO($dsn, $dbUser, $dbPwd);
  $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
  //$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMORE_EXCEPTION);

  $sql = "insert into tbl_comment (comm_content, comm_wdate, comm_recomm, fk_board_idx, fk_mem_id)
          values ('$contents', now(), 0, '$idx', '$fk_mem_id')";
  $stm = $pdo->prepare($sql);
  $stm->execute();

  echo "<script>location.href='./board_detail.php?board_folder=".$folder."&board_group=".$group."&board_idx=".$idx."&board_page=".$page."'</script>";

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
