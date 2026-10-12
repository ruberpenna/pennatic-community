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

if(!isset($_POST['board_folder']) || !isset($_POST['board_group']) || !isset($_POST['board_subject']) || !isset($_POST['board_title']) || !isset($_POST['board_contents'])) exit;
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
if(empty($_POST['board_folder']) || empty($_POST['board_group']) || empty($_POST['board_subject']) || empty($_POST['board_title']) || empty($_POST['board_contents'])){
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

$folder = $_POST['board_folder'];
$group = $_POST['board_group'];
$subject = $_POST['board_subject'];
$title = $_POST['board_title'];
$contents = $_POST['board_contents'];
$fk_mem_id = $_SESSION['mem_id'];


try{
  $pdo = new PDO($dsn, $dbUser, $dbPwd);
  $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
  $pdo->beginTransaction();
  //$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMORE_EXCEPTION);

  $sql = "INSERT into tbl_board (board_folder, board_group, board_subject, board_title, board_content, board_wdate, board_edate, board_count, board_status, fk_mem_id)
          values ('$folder', '$group', '$subject', '$title', '$contents', now(), now(), 0, 1, '$fk_mem_id')";
  $stm = $pdo->prepare($sql);
  $stm->execute();


  $sql = "SELECT max(board_idx) as board_idx
          FROM tbl_board";
  $stm = $pdo->prepare($sql);
  $stm->execute();
  $result = $stm->fetchAll(PDO::FETCH_ASSOC);

  foreach ($result as $row) {
    $insert_idx = $row['board_idx'];
  }

  foreach ($_FILES["boardfile"]["error"] as $key => $error) {
      if ($error = UPLOAD_ERR_OK) {
          $tmp_name = $_FILES["boardfile"]["tmp_name"][$key];
          $name = $_FILES["boardfile"]["name"][$key];
          $size = $_FILES["boardfile"]["size"][$key];
          move_uploaded_file($tmp_name, "../files/$name");

          $sql = "INSERT into tbl_file (file_oldname, file_newname, file_size, file_count, fk_board_idx)
                  values ('$name', '$tmp_name', '$size', '0', '$insert_idx')";
          $stm = $pdo->prepare($sql);
          $stm->execute();
      }
  }

  $pdo->commit();

  echo "<script>location.href='./board.php?board_folder=".$folder."&board_group=".$group."';</script>";

} catch (Exception $e){
  $pdo->rollback();
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
