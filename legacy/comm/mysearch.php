<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8" />
	<title>Penna - ID/PW 찾기</title>
  <link rel="stylesheet" type="text/css" href="./bootstrap/css/bootstrap.css">
	<link rel="stylesheet" type="text/css" href="https://cdnjs.cloudflare.com/ajax/libs/sweetalert/1.1.3/sweetalert.min.css" />
	<script type="text/javascript" src="./js/jquery-2.1.3.min.js"></script>
	<script type="text/javascript" src="./bootstrap/js/bootstrap.js"></script>
	<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/sweetalert/1.1.3/sweetalert.min.js"></script>
	<?php
	  include("dbconfig.php");
		error_reporting(E_ALL);
		ini_set("display_errors", 1);

	  if($_SERVER["REQUEST_METHOD"] == "POST"){

	    if(isset($_POST['email1']) && isset($_POST['nick']) && !isset($_POST['mem_id']) && !isset($_POST['email2']) && !isset($_POST['keynum'])){
	      $email1=addslashes($_POST['email1']);
	      $nick=addslashes($_POST['nick']);

	      if(empty($email1) || empty($nick)){
	        echo "<script>
	              $(window).load(function(){
	                $(document).ready(function () {
	                  swal({
	                    title: '이메일과 닉네임 모두 입력해주세요.',
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
	      } else {
	        try{
	          $pdo = new PDO($dsn, $dbUser, $dbPwd);
	          $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
	          //$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMORE_EXCEPTION);

	          $sql = "select mem_id
	                  from tbl_member
	                  where mem_status = 1
	                  and mem_email='$email1'
	                  and mem_nick='$nick'";
	          $stm = $pdo->prepare($sql);
	          $stm->execute();
	          $result = $stm->fetchAll(PDO::FETCH_ASSOC);

	          if(empty($result)){
	            $searchString1 = "<br/><br/>검색하신 내용에서는 아이디가 존재하지 않습니다.";
	          } else {
	            foreach($result as $row){
	              $searchString1 = "<br/><br/>회원님의 아이디는 <span style='color: red; font-weight: bold;'>" . $row['mem_id'] . "</span> 입니다.";
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
	      }
	    } elseif(isset($_POST['mem_id']) && isset($_POST['email2']) && isset($_POST['keynum']) && !isset($_POST['email1']) && !isset($_POST['nick'])){

	      $id=addslashes($_POST['mem_id']);
	      $email2=addslashes($_POST['email2']);
	      $keynum=addslashes($_POST['keynum']);

	      if (empty($id)){
	        echo "<script>
	              $(window).load(function(){
	                $(document).ready(function () {
	                  swal({
	                    title: '아이디를 입력하지 않았습니다.',
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
	      } elseif(empty($email2)){
	        echo "<script>
	              $(window).load(function(){
	                $(document).ready(function () {
	                  swal({
	                    title: '이메일을 입력하지 않았습니다.',
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
	      } elseif(empty($keynum)){
	        echo "<script>
	              $(window).load(function(){
	                $(document).ready(function () {
	                  swal({
	                    title: '인증번호를 입력해주세요.',
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
	      } else {
	        session_start();
	        $confirm = $_SESSION['confirm_num'];

	        if($keynum != $confirm){
	          echo "<script>
	                $(window).load(function(){
	                  $(document).ready(function () {
	                    swal({
	                      title: '인증번호가 일치하지 않습니다.',
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

	        try{
	          $pdo = new PDO($dsn, $dbUser, $dbPwd);
	          $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
	          //$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMORE_EXCEPTION);

	          $sql = "select mem_id
	                  from tbl_member
	                  where mem_status = 1
	                  and mem_email='$email2'
	                  and mem_id='$id'";
	          $stm = $pdo->prepare($sql);
	          $stm->execute();
	          $result = $stm->fetchAll(PDO::FETCH_ASSOC);

	          if(empty($result)){
	            $searchString2 = "<br/><br/>해당하는 아이디와 등록된 이메일이 일치하지 않습니다.";
	          } else {
	            $searchString2 = '<br/><br/><form name="changeFrm" action="./pwd_change.php" method="post" onsubmit="return changePwd()">
	                                새 비밀번호 : <input type="password" class="form-control" id="new_pwd" name="new_pwd" placeholder="영문/숫자 8~20자" minlength="8" maxlength="20" style="width: 150px; display: inline;" /><br/><br/>
	                                새 비밀번호 확인 : <input type="password" class="form-control" id="new_repwd" name="new_repwd" placeholder="영문/숫자 8~20자" minlength="8" maxlength="20" style="width: 150px; display: inline;" /><br/><br/>
	                                <input type="hidden" id="member_id" name="member_id" value="'.$id;
	            $searchString2 .= '">';
	            $searchString2 .= '<button type="submit" class="btn btn-default btn-primary">변경</button>
	                                </form>';
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

	      }
	    }
	  }
	?>
  <style type="text/css">

  .panel.with-nav-tabs .panel-heading{
      padding: 5px 5px 0 5px;
  }
  .panel.with-nav-tabs .nav-tabs{
  	border-bottom: none;
  }
  .panel.with-nav-tabs .nav-justified{
  	margin-bottom: -1px;
  }
  /********************************************************************/
  /*** PANEL DEFAULT ***/
  .with-nav-tabs.panel-default .nav-tabs > li > a,
  .with-nav-tabs.panel-default .nav-tabs > li > a:hover,
  .with-nav-tabs.panel-default .nav-tabs > li > a:focus {
      color: #777;
  }
  .with-nav-tabs.panel-default .nav-tabs > .open > a,
  .with-nav-tabs.panel-default .nav-tabs > .open > a:hover,
  .with-nav-tabs.panel-default .nav-tabs > .open > a:focus,
  .with-nav-tabs.panel-default .nav-tabs > li > a:hover,
  .with-nav-tabs.panel-default .nav-tabs > li > a:focus {
      color: #777;
  	background-color: #ddd;
  	border-color: transparent;
  }
  .with-nav-tabs.panel-default .nav-tabs > li.active > a,
  .with-nav-tabs.panel-default .nav-tabs > li.active > a:hover,
  .with-nav-tabs.panel-default .nav-tabs > li.active > a:focus {
  	color: #555;
  	background-color: #fff;
  	border-color: #ddd;
  	border-bottom-color: transparent;
  }
  .with-nav-tabs.panel-default .nav-tabs > li.dropdown .dropdown-menu {
      background-color: #f5f5f5;
      border-color: #ddd;
  }
  .with-nav-tabs.panel-default .nav-tabs > li.dropdown .dropdown-menu > li > a {
      color: #777;
  }
  .with-nav-tabs.panel-default .nav-tabs > li.dropdown .dropdown-menu > li > a:hover,
  .with-nav-tabs.panel-default .nav-tabs > li.dropdown .dropdown-menu > li > a:focus {
      background-color: #ddd;
  }
  .with-nav-tabs.panel-default .nav-tabs > li.dropdown .dropdown-menu > .active > a,
  .with-nav-tabs.panel-default .nav-tabs > li.dropdown .dropdown-menu > .active > a:hover,
  .with-nav-tabs.panel-default .nav-tabs > li.dropdown .dropdown-menu > .active > a:focus {
      color: #fff;
      background-color: #555;
  }
  /********************************************************************/
  /*** PANEL PRIMARY ***/
  .with-nav-tabs.panel-primary .nav-tabs > li > a,
  .with-nav-tabs.panel-primary .nav-tabs > li > a:hover,
  .with-nav-tabs.panel-primary .nav-tabs > li > a:focus {
      color: #fff;
  }
  .with-nav-tabs.panel-primary .nav-tabs > .open > a,
  .with-nav-tabs.panel-primary .nav-tabs > .open > a:hover,
  .with-nav-tabs.panel-primary .nav-tabs > .open > a:focus,
  .with-nav-tabs.panel-primary .nav-tabs > li > a:hover,
  .with-nav-tabs.panel-primary .nav-tabs > li > a:focus {
  	color: #fff;
  	background-color: #3071a9;
  	border-color: transparent;
  }
  .with-nav-tabs.panel-primary .nav-tabs > li.active > a,
  .with-nav-tabs.panel-primary .nav-tabs > li.active > a:hover,
  .with-nav-tabs.panel-primary .nav-tabs > li.active > a:focus {
  	color: #428bca;
  	background-color: #fff;
  	border-color: #428bca;
  	border-bottom-color: transparent;
  }
  .with-nav-tabs.panel-primary .nav-tabs > li.dropdown .dropdown-menu {
      background-color: #428bca;
      border-color: #3071a9;
  }
  .with-nav-tabs.panel-primary .nav-tabs > li.dropdown .dropdown-menu > li > a {
      color: #fff;
  }
  .with-nav-tabs.panel-primary .nav-tabs > li.dropdown .dropdown-menu > li > a:hover,
  .with-nav-tabs.panel-primary .nav-tabs > li.dropdown .dropdown-menu > li > a:focus {
      background-color: #3071a9;
  }
  .with-nav-tabs.panel-primary .nav-tabs > li.dropdown .dropdown-menu > .active > a,
  .with-nav-tabs.panel-primary .nav-tabs > li.dropdown .dropdown-menu > .active > a:hover,
  .with-nav-tabs.panel-primary .nav-tabs > li.dropdown .dropdown-menu > .active > a:focus {
      background-color: #4a9fe9;
  }
  /********************************************************************/
  /*** PANEL SUCCESS ***/
  .with-nav-tabs.panel-success .nav-tabs > li > a,
  .with-nav-tabs.panel-success .nav-tabs > li > a:hover,
  .with-nav-tabs.panel-success .nav-tabs > li > a:focus {
  	color: #3c763d;
  }
  .with-nav-tabs.panel-success .nav-tabs > .open > a,
  .with-nav-tabs.panel-success .nav-tabs > .open > a:hover,
  .with-nav-tabs.panel-success .nav-tabs > .open > a:focus,
  .with-nav-tabs.panel-success .nav-tabs > li > a:hover,
  .with-nav-tabs.panel-success .nav-tabs > li > a:focus {
  	color: #3c763d;
  	background-color: #d6e9c6;
  	border-color: transparent;
  }
  .with-nav-tabs.panel-success .nav-tabs > li.active > a,
  .with-nav-tabs.panel-success .nav-tabs > li.active > a:hover,
  .with-nav-tabs.panel-success .nav-tabs > li.active > a:focus {
  	color: #3c763d;
  	background-color: #fff;
  	border-color: #d6e9c6;
  	border-bottom-color: transparent;
  }
  .with-nav-tabs.panel-success .nav-tabs > li.dropdown .dropdown-menu {
      background-color: #dff0d8;
      border-color: #d6e9c6;
  }
  .with-nav-tabs.panel-success .nav-tabs > li.dropdown .dropdown-menu > li > a {
      color: #3c763d;
  }
  .with-nav-tabs.panel-success .nav-tabs > li.dropdown .dropdown-menu > li > a:hover,
  .with-nav-tabs.panel-success .nav-tabs > li.dropdown .dropdown-menu > li > a:focus {
      background-color: #d6e9c6;
  }
  .with-nav-tabs.panel-success .nav-tabs > li.dropdown .dropdown-menu > .active > a,
  .with-nav-tabs.panel-success .nav-tabs > li.dropdown .dropdown-menu > .active > a:hover,
  .with-nav-tabs.panel-success .nav-tabs > li.dropdown .dropdown-menu > .active > a:focus {
      color: #fff;
      background-color: #3c763d;
  }
  /********************************************************************/
  /*** PANEL INFO ***/
  .with-nav-tabs.panel-info .nav-tabs > li > a,
  .with-nav-tabs.panel-info .nav-tabs > li > a:hover,
  .with-nav-tabs.panel-info .nav-tabs > li > a:focus {
  	color: #31708f;
  }
  .with-nav-tabs.panel-info .nav-tabs > .open > a,
  .with-nav-tabs.panel-info .nav-tabs > .open > a:hover,
  .with-nav-tabs.panel-info .nav-tabs > .open > a:focus,
  .with-nav-tabs.panel-info .nav-tabs > li > a:hover,
  .with-nav-tabs.panel-info .nav-tabs > li > a:focus {
  	color: #31708f;
  	background-color: #bce8f1;
  	border-color: transparent;
  }
  .with-nav-tabs.panel-info .nav-tabs > li.active > a,
  .with-nav-tabs.panel-info .nav-tabs > li.active > a:hover,
  .with-nav-tabs.panel-info .nav-tabs > li.active > a:focus {
  	color: #31708f;
  	background-color: #fff;
  	border-color: #bce8f1;
  	border-bottom-color: transparent;
  }
  .with-nav-tabs.panel-info .nav-tabs > li.dropdown .dropdown-menu {
      background-color: #d9edf7;
      border-color: #bce8f1;
  }
  .with-nav-tabs.panel-info .nav-tabs > li.dropdown .dropdown-menu > li > a {
      color: #31708f;
  }
  .with-nav-tabs.panel-info .nav-tabs > li.dropdown .dropdown-menu > li > a:hover,
  .with-nav-tabs.panel-info .nav-tabs > li.dropdown .dropdown-menu > li > a:focus {
      background-color: #bce8f1;
  }
  .with-nav-tabs.panel-info .nav-tabs > li.dropdown .dropdown-menu > .active > a,
  .with-nav-tabs.panel-info .nav-tabs > li.dropdown .dropdown-menu > .active > a:hover,
  .with-nav-tabs.panel-info .nav-tabs > li.dropdown .dropdown-menu > .active > a:focus {
      color: #fff;
      background-color: #31708f;
  }
  /********************************************************************/
  /*** PANEL WARNING ***/
  .with-nav-tabs.panel-warning .nav-tabs > li > a,
  .with-nav-tabs.panel-warning .nav-tabs > li > a:hover,
  .with-nav-tabs.panel-warning .nav-tabs > li > a:focus {
  	color: #8a6d3b;
  }
  .with-nav-tabs.panel-warning .nav-tabs > .open > a,
  .with-nav-tabs.panel-warning .nav-tabs > .open > a:hover,
  .with-nav-tabs.panel-warning .nav-tabs > .open > a:focus,
  .with-nav-tabs.panel-warning .nav-tabs > li > a:hover,
  .with-nav-tabs.panel-warning .nav-tabs > li > a:focus {
  	color: #8a6d3b;
  	background-color: #faebcc;
  	border-color: transparent;
  }
  .with-nav-tabs.panel-warning .nav-tabs > li.active > a,
  .with-nav-tabs.panel-warning .nav-tabs > li.active > a:hover,
  .with-nav-tabs.panel-warning .nav-tabs > li.active > a:focus {
  	color: #8a6d3b;
  	background-color: #fff;
  	border-color: #faebcc;
  	border-bottom-color: transparent;
  }
  .with-nav-tabs.panel-warning .nav-tabs > li.dropdown .dropdown-menu {
      background-color: #fcf8e3;
      border-color: #faebcc;
  }
  .with-nav-tabs.panel-warning .nav-tabs > li.dropdown .dropdown-menu > li > a {
      color: #8a6d3b;
  }
  .with-nav-tabs.panel-warning .nav-tabs > li.dropdown .dropdown-menu > li > a:hover,
  .with-nav-tabs.panel-warning .nav-tabs > li.dropdown .dropdown-menu > li > a:focus {
      background-color: #faebcc;
  }
  .with-nav-tabs.panel-warning .nav-tabs > li.dropdown .dropdown-menu > .active > a,
  .with-nav-tabs.panel-warning .nav-tabs > li.dropdown .dropdown-menu > .active > a:hover,
  .with-nav-tabs.panel-warning .nav-tabs > li.dropdown .dropdown-menu > .active > a:focus {
      color: #fff;
      background-color: #8a6d3b;
  }
  /********************************************************************/
  /*** PANEL DANGER ***/
  .with-nav-tabs.panel-danger .nav-tabs > li > a,
  .with-nav-tabs.panel-danger .nav-tabs > li > a:hover,
  .with-nav-tabs.panel-danger .nav-tabs > li > a:focus {
  	color: #a94442;
  }
  .with-nav-tabs.panel-danger .nav-tabs > .open > a,
  .with-nav-tabs.panel-danger .nav-tabs > .open > a:hover,
  .with-nav-tabs.panel-danger .nav-tabs > .open > a:focus,
  .with-nav-tabs.panel-danger .nav-tabs > li > a:hover,
  .with-nav-tabs.panel-danger .nav-tabs > li > a:focus {
  	color: #a94442;
  	background-color: #ebccd1;
  	border-color: transparent;
  }
  .with-nav-tabs.panel-danger .nav-tabs > li.active > a,
  .with-nav-tabs.panel-danger .nav-tabs > li.active > a:hover,
  .with-nav-tabs.panel-danger .nav-tabs > li.active > a:focus {
  	color: #a94442;
  	background-color: #fff;
  	border-color: #ebccd1;
  	border-bottom-color: transparent;
  }
  .with-nav-tabs.panel-danger .nav-tabs > li.dropdown .dropdown-menu {
      background-color: #f2dede; /* bg color */
      border-color: #ebccd1; /* border color */
  }
  .with-nav-tabs.panel-danger .nav-tabs > li.dropdown .dropdown-menu > li > a {
      color: #a94442; /* normal text color */
  }
  .with-nav-tabs.panel-danger .nav-tabs > li.dropdown .dropdown-menu > li > a:hover,
  .with-nav-tabs.panel-danger .nav-tabs > li.dropdown .dropdown-menu > li > a:focus {
      background-color: #ebccd1; /* hover bg color */
  }
  .with-nav-tabs.panel-danger .nav-tabs > li.dropdown .dropdown-menu > .active > a,
  .with-nav-tabs.panel-danger .nav-tabs > li.dropdown .dropdown-menu > .active > a:hover,
  .with-nav-tabs.panel-danger .nav-tabs > li.dropdown .dropdown-menu > .active > a:focus {
      color: #fff; /* active text color */
      background-color: #a94442; /* active bg color */
  }

  </style>
  <script language=javascript>
    // function idSearch(){
    //   var email1 = document.getElementById("email1");
    //   var nick = document.getElementById("nick");
    //
    //   if(email1.value == "" || email1.value == null){
    //     alert("이메일을 입력해주세요.");
    //     return false;
    //   } else if (nick.value == "" || nick.value == null){
    //     alert("닉네임을 입력해주세요.");
    //     retrun false;
    //   } else {
    //     return true;
    //   }
    // }

    // function pwSearch(){
    //   var id = document.getElementById("mem_id");
    //   var email2 = document.getElementById("email2");
    //   var keynum = document.getElementById("keynum");
    //
    //   if(id.value == "" || id.value == null){
    //     alert("아이디를 입력해주세요.");
    //     return false;
    //   } else if(email2.value == "" || email2.value == null){
    //     alert("이메일을 입력해주세요.");
    //     return false;
    //   } else if (keynum.value == "" || keynum.value == null){
    //     alert("인증번호를 입력해주세요.");
    //     retrun false;
    //   } else {
    //     return true;
    //   }
    // }

    function copymail(){
      var text_mail = document.getElementById("email2");
      var hidden_mail = document.getElementById("h_email2");

      hidden_mail.value = text_mail.value;
    }

    function sendmail(){
      var hidden_mail = document.getElementById("h_email2");

      location.href="./sendmail.php?mail="+hidden_mail.value;
    }

  </script>
</head>
<body>
  <div class="container">
    <div class="row">
        <div class="col-md-6">
            <div class="panel with-nav-tabs panel-primary">
                <div class="panel-heading">
                        <ul class="nav nav-tabs">
                            <li class="active"><a href="#tab1primary" data-toggle="tab">아이디 찾기</a></li>
                            <li><a href="#tab2primary" data-toggle="tab">비밀번호 찾기</a></li>
                        </ul>
                </div>
                <div class="panel-body">
                    <div class="tab-content">
                        <div class="tab-pane fade in active" id="tab1primary" style="text-align: center;">
                          <br/><br/>
                          <!-- <form name="id_searchFrm" action="" method="post" onsubmit="return idSearch()"> -->
                          <form name="id_searchFrm" action="" method="post">
                            이메일 : <input type="text" class="form-control" id="email1" name="email1" style="width: 200px; display: inline;" /><br/><br/>
                            닉네임 : <input type="text" class="form-control" id="nick" name="nick" style="width: 200px; display: inline;" /><br/><br/><br/>
                            <button type="submit" class="btn btn-default btn-primary">조회</button><br/>
                          </form>
                          <?php
                            if(isset($searchString1)){
                              echo $searchString1;
                            }
                          ?>
                          <br/><br/>
                        </div>
                        <div class="tab-pane fade" id="tab2primary" style="text-align: center;">
                          <br/><br/>
                          <!-- <form name="pw_searchFrm" action="" method="post" onsubmit="return pwSearch()"> -->
                          <form name="pw_searchFrm" action="" method="post">
                            아이디 : <input type="text" class="form-control" id="mem_id" name="mem_id" style="width: 150px; display: inline;" /><br/><br/>
                            이메일 : <input type="text" class="form-control" id="email2" name="email2" style="width: 200px; display: inline;" onkeyup="copymail();" />
                            <a href="javascript:sendmail();" class="btn btn-sm btn-primary">발송</a><br/><br/>
                            인증번호 : <input type="text" class="form-control" id="keynum" name="keynum" style="width: 150px; display: inline;" /><br/><br/><br/>
                            <button type="submit" class="btn btn-default btn-primary">조회</button><br/>
                          </form>
                          <input type="hidden" class="form-control" id="h_email2" name="h_email2" style="width: 200px; display: inline;" />
                          <?php
                            if(isset($searchString2)){
                              echo $searchString2;
                            }
                          ?>
                          <br/><br/>
                        </div>
                    </div>
                </div>
            </div>
        </div>
	</div>
</div>
</body>
</html>
