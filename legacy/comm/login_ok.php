<?php session_start(); ?>
<link rel="stylesheet" type="text/css" href="./bootstrap/css/bootstrap.css">
<link rel="stylesheet" type="text/css" href="https://cdnjs.cloudflare.com/ajax/libs/sweetalert/1.1.3/sweetalert.min.css" />
<script type="text/javascript" src="./js/jquery-2.1.3.min.js"></script>
<script type="text/javascript" src="./bootstrap/js/bootstrap.js"></script>
<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/sweetalert/1.1.3/sweetalert.min.js"></script>

<?php
include("dbconfig.php");
error_reporting(E_ALL);
ini_set("display_errors", 1);

if(!isset($_POST['mem_id']) || !isset($_POST['mem_pwd'])) exit;
if(empty($_POST['mem_id']) || empty($_POST['mem_pwd'])){
    echo "<script>
          $(window).load(function(){
            $(document).ready(function () {
              swal({
                title: '아이디 또는 패스워드가 입력되지 않았습니다.',
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

$mem_id = $_POST['mem_id'];
$mem_pwd = $_POST['mem_pwd'];

try{
  $pdo = new PDO($dsn, $dbUser, $dbPwd);
  $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
  //$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMORE_EXCEPTION);

  $sql = "SELECT mem_idx, mem_id, mem_nick, mem_email, mem_intro, mem_emblem, mem_img, mem_join, mem_status
          from tbl_member
          where mem_status in ('1','2')
          and mem_id='$mem_id'
          and mem_pwd='$mem_pwd'";
  $stm = $pdo->prepare($sql);
  $stm->execute();
  $result = $stm->fetchAll(PDO::FETCH_ASSOC);

  if(empty($result)){
    echo "<script>
          $(window).load(function(){
            $(document).ready(function () {
              swal({
                title: '아이디 또는 패스워드가 일치하지 않습니다.',
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
    foreach($result as $row){
      $_SESSION['mem_idx'] = $row['mem_idx'];
      $_SESSION['mem_id'] = $row['mem_id'];
      $_SESSION['mem_nick'] = $row['mem_nick'];
      $_SESSION['mem_email'] = $row['mem_email'];
      $_SESSION['mem_intro'] = $row['mem_intro'];
      $_SESSION['mem_emblem'] = $row['mem_emblem'];
      $_SESSION['mem_img'] = $row['mem_img'];
      $_SESSION['mem_join'] = $row['mem_join'];
      $_SESSION['mem_status'] = $row['mem_status'];
    }
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
  exit();
}
?>
<meta http-equiv='refresh' content='0;url=./sidebar.php'>
