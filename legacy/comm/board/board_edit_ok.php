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

if(!isset($_POST['board_idx']) || !isset($_POST['board_folder']) || !isset($_POST['board_group']) || !isset($_POST['board_subject']) || !isset($_POST['board_title']) || !isset($_POST['board_contents']) || !isset($_POST['board_status'])) exit;
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
if(empty($_POST['board_idx']) || empty($_POST['board_folder']) || empty($_POST['board_group']) || empty($_POST['board_subject']) || empty($_POST['board_title']) || empty($_POST['board_contents']) || !isset($_POST['board_status'])){
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

$idx = $_POST['board_idx'];
$folder = $_POST['board_folder'];
$group = $_POST['board_group'];
$subject = $_POST['board_subject'];
$title = $_POST['board_title'];
$contents = $_POST['board_contents'];
$status = $_POST['board_status'];
if(isset($_POST['file_idx'])){
  $file_idx = $_POST['file_idx'];
}

try{
  $pdo = new PDO($dsn, $dbUser, $dbPwd);
  $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
  $pdo->beginTransaction();
  //$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMORE_EXCEPTION);

  $sql = "UPDATE tbl_board set board_subject = '$subject', board_title = '$title', board_content = '$contents', board_edate = now()
          where board_idx = '$idx' and board_status = 1";
  $stm = $pdo->prepare($sql);
  $stm->execute();

  //기존 파일 항목 업데이트

  if($_FILES['boardfile_update']){
    foreach($_FILES['boardfile_update']['error'] as $key => $error){
      for($i = 0; $i < count($file_idx); $i++){
        if ($error == UPLOAD_ERR_OK) {
          //echo "<script>alert(".$file_idx[$i].");</script>";
          $today = date("Ymd");
          $random = mt_rand(100000, 999999);

          //$img_error = $_FILES['boardfile_insert']['error'][$key];
          $img_origin = basename($_FILES['boardfile_update']['name'][$key]);
          $img_tmp = $_FILES['boardfile_update']['tmp_name'][$key];
          $img_new = $today . "_" . $random . "_" . $_FILES['boardfile_update']['name'][$key];
          $img_size = $_FILES["boardfile_update"]["size"][$key];

          if(move_uploaded_file($img_tmp, "../files/$img_new")){
            $pdo = new PDO($dsn, $dbUser, $dbPwd);
            $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
            //$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMORE_EXCEPTION);

            $sql = "UPDATE tbl_file set file_oldname = '$img_origin', file_newname = '$img_new', file_size = '$img_size'
                     where file_idx = '$file_idx[$i]'";
            $stm = $pdo->prepare($sql);
            $stm->execute();
          } else {
            echo "<script>
        					$(window).load(function(){
        						$(document).ready(function () {
        							swal({
        								title: '이미지 업로드에 실패하였습니다.',
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
            exit();
          }

        }
      }
    }
  }

  //기존 파일 이외의 새로운 파일 추가
  if($_FILES['boardfile_insert']){
    foreach($_FILES['boardfile_insert']['error'] as $key => $error){
      if ($error == UPLOAD_ERR_OK) {
        $today = date("Ymd");
        $random = mt_rand(100000, 999999);

        //$img_error = $_FILES['boardfile_insert']['error'][$key];
        $img_origin = basename($_FILES['boardfile_insert']['name'][$key]);
        $img_tmp = $_FILES['boardfile_update']['tmp_name'][$key];
        $img_new = $today . "_" . $random . "_" . $_FILES['boardfile_insert']['name'][$key];
        $img_size = $_FILES["boardfile_insert"]["size"][$key];

        if(move_uploaded_file($img_tmp, "../files/".$img_new)){
          $pdo = new PDO($dsn, $dbUser, $dbPwd);
          $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
          //$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMORE_EXCEPTION);

          $sql = "INSERT into tbl_file (file_oldname, file_newname, file_size, file_count, fk_board_idx)
                  values ('$img_origin', '$img_new', '$img_size', '0', '$idx')";
          $stm = $pdo->prepare($sql);
          $stm->execute();
        } else {
          echo "<script>
      					$(window).load(function(){
      						$(document).ready(function () {
      							swal({
      								title: '이미지 업로드에 실패하였습니다.',
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
          exit();
        }

      }
    }
  }

  echo "<script>location.href='./board_detail.php?board_folder=".$folder."&board_group=".$group."&board_idx=".$idx."&board_page=1&board_status=".$status."'</script>";

} catch (Exception $e){
  $pdo->rollBack();
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
