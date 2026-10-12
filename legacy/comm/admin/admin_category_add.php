<link rel="stylesheet" type="text/css" href="../bootstrap/css/bootstrap.css">
<link rel="stylesheet" type="text/css" href="https://cdnjs.cloudflare.com/ajax/libs/sweetalert/1.1.3/sweetalert.min.css" />
<script type="text/javascript" src="../js/jquery-2.1.3.min.js"></script>
<script type="text/javascript" src="../bootstrap/js/bootstrap.js"></script>
<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/sweetalert/1.1.3/sweetalert.min.js"></script>

<?php
include("../dbconfig.php");
error_reporting(E_ALL);
ini_set("display_errors", 1);

if(isset($_POST['category_folder'])){
  //폴더 추가
  $folder = $_POST['category_folder'];

  try{
  	$pdo = new PDO($dsn, $dbUser, $dbPwd);
    $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
    //$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMORE_EXCEPTION);

    $sql = "SELECT count(*) as cnt FROM tbl_board WHERE board_folder = '$folder' AND board_status = 3";
  	$stm = $pdo->prepare($sql);
  	$stm->execute();
    $result = $stm->fetchAll(PDO::FETCH_ASSOC);

    foreach ($result as $row) {
      $cnt = $row['cnt'];
    }

    if($cnt < 1){
      $sql = "INSERT INTO tbl_board (board_folder, board_title, board_content, board_wdate, board_count, board_status, fk_mem_id)
              values ('$folder', '생성용', '생성용', now(), 0, 3, 'adminpenna')";
    	$stm = $pdo->prepare($sql);
    	$stm->execute();

      echo "<script>
            $(window).load(function(){
              $(document).ready(function () {
                swal({
                  title: '폴더가 생성되었습니다.',
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
    } else {
      echo "<script>
            $(window).load(function(){
              $(document).ready(function () {
                swal({
                  title: '중복된 카테고리 폴더가 존재합니다.',
                  type: 'warning',
                  confirmButtonClass: 'btn-warning',
                  confirmButtonText: '확인',
                },
                function(isConfirm) {
                  if (isConfirm) {

                  }
                });
              });
            });
          </script>";
      exit;
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
} else if(isset($_POST['category_group'])){
  //게시판 추가
  $group = $_POST['category_group'];
  $f_sel1 = $_POST['category_fsel'];

  try{
  	$pdo = new PDO($dsn, $dbUser, $dbPwd);
    $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
    //$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMORE_EXCEPTION);

    $sql = "SELECT count(*) as cnt FROM tbl_board WHERE board_group = '$group' AND board_status = 3";
  	$stm = $pdo->prepare($sql);
  	$stm->execute();
    $result = $stm->fetchAll(PDO::FETCH_ASSOC);

    foreach ($result as $row) {
      $cnt = $row['cnt'];
    }

    if($cnt < 1){
      $sql = "INSERT INTO tbl_board (board_folder, board_group, board_title, board_content, board_wdate, board_count, board_status, fk_mem_id)
              values ('$f_sel', '$group', '생성용', '생성용', now(), 0, 3, 'adminpenna')";
      $stm = $pdo->prepare($sql);
      $stm->execute();

      echo "<script>
            $(window).load(function(){
              $(document).ready(function () {
                swal({
                  title: '게시판이 생성되었습니다.',
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
    } else {
      echo "<script>
            $(window).load(function(){
              $(document).ready(function () {
                swal({
                  title: '중복된 게시판이 존재합니다.',
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
} else if(isset($_POST['category_subject'])){
  //말머리 추가
  $subject = $_POST['category_subject'];
  $f_sel2 = $_POST['category_fsel'];
  $g_sel = $_POST['category_gsel'];

  try{
  	$pdo = new PDO($dsn, $dbUser, $dbPwd);
    $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
    //$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMORE_EXCEPTION);

    $sql = "SELECT count(*) as cnt FROM tbl_board WHERE board_folder = '$f_sel' AND board_group = '$g_sel' AND board_subject = '$subject' AND board_status = 3";
  	$stm = $pdo->prepare($sql);
  	$stm->execute();
    $result = $stm->fetchAll(PDO::FETCH_ASSOC);

    foreach ($result as $row) {
      $cnt = $row['cnt'];
    }

    if($cnt < 1){
      $sql = "INSERT INTO tbl_board (board_folder, board_group, board_subject, board_title, board_content, board_wdate, board_count, board_status, fk_mem_id)
              values ('$f_sel', '$g_sel', '$subject', '생성용', '생성용', now(), 0, 3, 'adminpenna')";
    	$stm = $pdo->prepare($sql);
    	$stm->execute();

      echo "<script>
            $(window).load(function(){
              $(document).ready(function () {
                swal({
                  title: '말머리가 생성되었습니다.',
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
    } else {
      echo "<script>
            $(window).load(function(){
              $(document).ready(function () {
                swal({
                  title: '게시판에서 중복된 말머리가 존재합니다.',
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
}

?>
