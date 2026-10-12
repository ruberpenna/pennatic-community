<link rel="stylesheet" type="text/css" href="./bootstrap/css/bootstrap.css">
<link rel="stylesheet" type="text/css" href="https://cdnjs.cloudflare.com/ajax/libs/sweetalert/1.1.3/sweetalert.min.css" />
<script type="text/javascript" src="./js/jquery-2.1.3.min.js"></script>
<script type="text/javascript" src="./bootstrap/js/bootstrap.js"></script>
<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/sweetalert/1.1.3/sweetalert.min.js"></script>

<?php
include("dbconfig.php");
error_reporting(E_ALL);
ini_set("display_errors", 1);

$mem_id = $_POST['mem_id'];
$mem_nick = $_POST['mem_nick'];
$mem_nick = $_POST['mem_nick'];
$mem_email = $_POST['mem_email'];
$mem_oldpwd = $_POST['oldpassword'];
$mem_pwd = $_POST['mem_pwd'];
$mem_img = $_POST['mem_img'];
$mem_intro = $_POST['mem_intro'];

try{
  $pdo = new PDO($dsn, $dbUser, $dbPwd);
  $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
  //$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMORE_EXCEPTION);

  $sql = "SELECT mem_pwd
          from tbl_member
          where mem_status in ('1','2')
          and mem_id = '$mem_id'
          and mem_pwd = '$mem_oldpwd'";
  $stm = $pdo->prepare($sql);
  $stm->execute();
  $result = $stm->fetchAll(PDO::FETCH_ASSOC);

  if(empty($result)){
    echo "<script>
          $(window).load(function(){
            $(document).ready(function () {
              swal({
                title: '현재 비밀번호가 일치하지 않습니다.',
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

} catch (Exception $e){
  echo "<script>
        $(window).load(function(){
          $(document).ready(function () {
            swal({
              title: '데이터베이스에 문제가 발생했습니다. 관리자에게 문의하세요.,
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

  $sql = "SELECT mem_nick
          from tbl_member
          where mem_status in ('1','2')
          and mem_nick='$mem_nick'";
  $stm = $pdo->prepare($sql);
  $stm->execute();
  $result = $stm->fetchAll(PDO::FETCH_ASSOC);

  if(!empty($result)){
    echo "<script>
          $(window).load(function(){
            $(document).ready(function () {
              swal({
                title: '중복된 닉네임입니다.',
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

//회원이미지 업로드

$today = date("Ymd");
$random = mt_rand(100000, 999999);
$allowed_ext = array('jpg','jpeg','png','gif');
$img_error = $_FILES['mem_img']['error'];
$img_name = $today . "_" . $random . basename($_FILES['mem_img']['name']);
$ext = array_pop(explode('.', $img_name));

$uploaddir = './files/';
$uploadfile = $uploaddir . $today . "_" . $random . basename($_FILES['mem_img']['name']);


if(!empty($_FILES['mem_img']['name'])) {
  if( $img_error != UPLOAD_ERR_OK ) {
  	switch( $img_error ) {
  		case UPLOAD_ERR_INI_SIZE:
  		case UPLOAD_ERR_FORM_SIZE:
        echo "<script>
              $(window).load(function(){
                $(document).ready(function () {
                  swal({
                    title: '이미지의 용량이 한도를 초과했습니다. (".$img_error.")',
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
  			break;
  		default:
        echo "<script>
              $(window).load(function(){
                $(document).ready(function () {
                  swal({
                    title: '이미지가 제대로 업로드되지 않았습니다. (".$img_error.")',
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
  	}
  	exit;
  }
  // 이미지 확장자 확인
  if( !in_array($ext, $allowed_ext) ) {
    echo "<script>
          $(window).load(function(){
            $(document).ready(function () {
              swal({
                title: '이 파일은 허용되지 않는 확장자입니다.',
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

  if (move_uploaded_file($_FILES['mem_img']['tmp_name'], $uploadfile)) {
    try{
      $pdo = new PDO($dsn, $dbUser, $dbPwd);
      $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
      //$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMORE_EXCEPTION);

      $sql = "update tbl_member set mem_img = '$img_name'
               where mem_id = '$mem_id'";
      $stm = $pdo->prepare($sql);
      $stm->execute();

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
  } else {
    echo "<script>
          $(window).load(function(){
            $(document).ready(function () {
              swal({
                title: '이미지 업로드에 실패하였습니다.',
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
}

try{
  $pdo = new PDO($dsn, $dbUser, $dbPwd);
  $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
  //$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMORE_EXCEPTION);

  $sql = "update tbl_member set mem_nick = '$mem_nick', mem_pwd = '$mem_pwd', mem_email = '$mem_email', mem_intro = '$mem_intro'
           where mem_id = '$mem_id'";
  $stm = $pdo->prepare($sql);
  $stm->execute();


  $sql = "SELECT mem_idx, mem_id, mem_nick, mem_email, mem_intro, mem_emblem, mem_img, mem_join, mem_status
          from tbl_member
          where mem_status in ('1','2')
          and mem_id='$mem_id'";
  $stm = $pdo->prepare($sql);
  $stm->execute();
  $result = $stm->fetchAll(PDO::FETCH_ASSOC);

  session_start();
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

  echo "<script>
        $(window).load(function(){
          $(document).ready(function () {
            swal({
              title: '회원정보의 수정이 완료되었습니다.',
              type: 'success',
              confirmButtonClass: 'btn-success',
              confirmButtonText: '확인',
            },
            function(isConfirm) {
              if (isConfirm) {
               location.href='./board/notice.php;
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
  exit;
}
?>
