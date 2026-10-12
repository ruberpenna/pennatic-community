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

if(!isset($_POST['board_idx']) || !isset($_POST['recomm_status'])) exit;

if(empty($_POST['board_idx']) || empty($_POST['recomm_status'])){
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
$status = $_POST['recomm_status'];
$mem_id = $_SESSION['mem_id'];

try{
  $pdo = new PDO($dsn, $dbUser, $dbPwd);
  $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
  //$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMORE_EXCEPTION);

  $sql = "SELECT count(*) AS cnt
          FROM tbl_board_recomm
          where fK_board_idx = '$idx' and board_recomm_status = '$status'";
  $stm = $pdo->prepare($sql);
  $stm->execute();
  $result = $stm->fetchAll(PDO::FETCH_ASSOC);

  foreach ($result as $row) {
    $recommcnt = $row['cnt'];
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

try{
  $pdo = new PDO($dsn, $dbUser, $dbPwd);
  $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
  //$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMORE_EXCEPTION);

  $sql = "SELECT count(*) AS cnt
          FROM tbl_board_recomm
          where fK_board_idx = '$idx' and fk_mem_id = '$mem_id'";
  $stm = $pdo->prepare($sql);
  $stm->execute();
  $result = $stm->fetchAll(PDO::FETCH_ASSOC);

  foreach ($result as $row) {
    $check = $row['cnt'];
  }

  if($check == '1'){
    echo $recommcnt;
    echo "<script>
					$(window).load(function(){
						$(document).ready(function () {
							swal({
								title: '중복 추천/비추천은 불가능합니다.',
								type: 'danger',
								confirmButtonClass: 'btn-danger',
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


try{
  $pdo = new PDO($dsn, $dbUser, $dbPwd);
  $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
  //$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMORE_EXCEPTION);

  $sql = "INSERT into tbl_board_recomm (board_recomm_status, fk_board_idx, fk_mem_id)
          values ('$status', '$idx', '$mem_id')";
  $stm = $pdo->prepare($sql);
  $stm->execute();

  $recommcnt = $recommcnt + 1;
  echo $recommcnt;

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
