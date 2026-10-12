<?php
include("dbconfig.php");
error_reporting(E_ALL);
ini_set("display_errors", 1);

$mem_id = $_POST['mem_id'];
$mem_nick = $_POST['mem_nick'];
$mem_email1 = $_POST['mem_email1'];
$mem_email2 = $_POST['mem_email2'];
$mem_pwd = $_POST['mem_pwd'];
$mem_img = $_POST['mem_img'];
$mem_intro = $_POST['mem_intro'];
$mem_email = $mem_email1."@".$mem_email2;

try{
  $pdo = new PDO($dsn, $dbUser, $dbPwd);
  $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
  //$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMORE_EXCEPTION);

  $sql = "select mem_id
          from tbl_member
          where mem_status in ('1','2')
          and mem_id='$mem_id'";
  $stm = $pdo->prepare($sql);
  $stm->execute();
  $result = $stm->fetchAll(PDO::FETCH_ASSOC);

  if(!empty($result)){
    echo "<script>
          $(window).load(function(){
            $(document).ready(function () {
              swal({
                title: '중복된 아이디입니다.',
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

  $sql = "select mem_nick
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

try{
  $pdo = new PDO($dsn, $dbUser, $dbPwd);
  $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
  //$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMORE_EXCEPTION);

  $sql = "insert into tbl_member (mem_id, mem_pwd, mem_nick, mem_email, mem_intro, mem_emblem, mem_img, mem_join, mem_withdraw, mem_status)
          values ('$mem_id', '$mem_pwd', '$mem_nick', '$mem_email', '$mem_intro', 'normal_grade.png', 'default_img.png', now(), null, 1)";
  $stm = $pdo->prepare($sql);
  $stm->execute();

  echo "<script>
        $(window).load(function(){
          $(document).ready(function () {
            swal({
              title: '회원가입을 축하드립니다.',
              type: 'success',
              confirmButtonClass: 'btn-success',
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

if(!empty($_FILES['mem_img']['name'])){
  if( $img_error != UPLOAD_ERR_OK ) {
  	switch( $img_error ) {
  		case UPLOAD_ERR_INI_SIZE:
  		case UPLOAD_ERR_FORM_SIZE:
        echo "<script>
              $(window).load(function(){
                $(document).ready(function () {
                  swal({
                    title: '이미지가 너무 큽니다. (".$img_error.")',
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
                    title: '이미지가 제대로 업로드되지 않았습니다.. (".$img_error.")',
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
                title: '허용되지 않는 확장자입니다.',
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
      echo "<script>alert('데이터베이스 쪽에 문제가 발생했습니다.');history.back();</script>";
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
    exit;
  }
}
?>
