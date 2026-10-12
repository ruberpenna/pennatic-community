<?php
	error_reporting(E_ALL);
	ini_set("display_errors", 1);
	session_start();
?>

<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8" />
	<title>Penna - MyPage</title>
	<link rel="stylesheet" type="text/css" href="./bootstrap/css/bootstrap.css">
	<link rel="stylesheet" type="text/css" href="https://cdnjs.cloudflare.com/ajax/libs/sweetalert/1.1.3/sweetalert.min.css" />
	<script type="text/javascript" src="./js/jquery-2.1.3.min.js"></script>
	<script type="text/javascript" src="./bootstrap/js/bootstrap.js"></script>
	<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/sweetalert/1.1.3/sweetalert.min.js"></script>
  <style type="text/css">
    .imgtop {
      text-align: center;
			height: 200px;
    }

    body {
      font-size: 1.5em;
      background: #eee;
    }

    .colorgraph {
      height: 5px;
      border-top: 0;
      background: #c4e17f;
      border-radius: 5px;
      background-image: -webkit-linear-gradient(left, #c4e17f, #c4e17f 12.5%, #f7fdca 12.5%, #f7fdca 25%, #fecf71 25%, #fecf71 37.5%, #f0776c 37.5%, #f0776c 50%, #db9dbe 50%, #db9dbe 62.5%, #c49cde 62.5%, #c49cde 75%, #669ae1 75%, #669ae1 87.5%, #62c2e4 87.5%, #62c2e4);
      background-image: -moz-linear-gradient(left, #c4e17f, #c4e17f 12.5%, #f7fdca 12.5%, #f7fdca 25%, #fecf71 25%, #fecf71 37.5%, #f0776c 37.5%, #f0776c 50%, #db9dbe 50%, #db9dbe 62.5%, #c49cde 62.5%, #c49cde 75%, #669ae1 75%, #669ae1 87.5%, #62c2e4 87.5%, #62c2e4);
      background-image: -o-linear-gradient(left, #c4e17f, #c4e17f 12.5%, #f7fdca 12.5%, #f7fdca 25%, #fecf71 25%, #fecf71 37.5%, #f0776c 37.5%, #f0776c 50%, #db9dbe 50%, #db9dbe 62.5%, #c49cde 62.5%, #c49cde 75%, #669ae1 75%, #669ae1 87.5%, #62c2e4 87.5%, #62c2e4);
      background-image: linear-gradient(to right, #c4e17f, #c4e17f 12.5%, #f7fdca 12.5%, #f7fdca 25%, #fecf71 25%, #fecf71 37.5%, #f0776c 37.5%, #f0776c 50%, #db9dbe 50%, #db9dbe 62.5%, #c49cde 62.5%, #c49cde 75%, #669ae1 75%, #669ae1 87.5%, #62c2e4 87.5%, #62c2e4);
    }

  </style>
  <script language=javascript>
		function goMyEdit(){
			var frm = document.getElementById("editForm");

			var nick = document.getElementById("mem_nick");
			var oldpwd = document.getElementById("oldpassword");
			var pwd = document.getElementById("mem_pwd");
			var repwd = document.getElementById("password_confirmation");
			var email = document.getElementById("mem_email");
			var img = document.getElementById("mem_img");


			if(nick.value == "" || nick.value == null){
				swal("닉네임을 입력하지 않았습니다.");
			} else {
				if(email.value == "" || email.value == null){
					swal("이메일을 입력하지 않았습니다.");
					return false;
				} else {
					if(oldpwd.value == "" || oldpwd.value == null || pwd.value == "" || pwd.value == null){
						swal("비밀번호를 입력하지 않았습니다.");
						return false;
					} else {
						if(pwd.value != repwd.value){
							swal("비밀번호와 비밀번호 확인이 서로 일치하지 않습니다.");
							return false;
						} else {
							frm.submit();
							return true;
						}
					}
				}
			}

		}

		function goVaccum(){
			var nick = document.getElementById("mem_nick");
			var email = document.getElementById("mem_email");
			var oldpwd = document.getElementById("oldpassword");
			var new_pwd = document.getElementById("mem_pwd");
			var new_repwd = document.getElementById("password_confirmation");
			var img = document.getElementById("mem_img");
			var intro = document.getElementById("mem_intro");

			nick.value = "";
			email.value = "";
			oldpwd.value = "";
			new_pwd.value = "";
			new_repwd.value = "";
			repwd.value = "";
			img.value = "";
			intro.value = "";
		}

		function goWithdraw(){
			swal({
			  title: "회원 탈퇴",
			  text: "비밀번호를 입력해주세요.",
			  type: "input",
				inputType: "password",
			  showCancelButton: true,
			  closeOnConfirm: false,
			  inputPlaceholder: "비밀번호"
			}, function (inputValue) {
			  if (inputValue === false) return false;
			  if (inputValue === "") {
			    swal.showInputError("비밀번호를 입력하지 않았습니다!");
			    return false;
			  }
			  location.href="./mypage_withdraw.php?pwd="+inputValue;
			});
		}
  </script>
</head>
<body>
  <div style="height: 100%; overflow: hidden; margin-left: 10%; margin-right: 10%;">
    <div class="imgtop">
      <a href="./board/notice.php" target="content"><img src="./images/plogo.png" style="width: 150px; height: 150px; margin-top: 25px;"/></a>
    </div>

    <div class="content">
      <div class="row">
          <div class="col-xs-12 col-sm-8 col-md-6 col-sm-offset-2 col-md-offset-3" style="text-align: center;">
      		<form id="editForm" name="editForm" role="form" method="post" action="mypage_ok.php" onsubmit="return goEdit()" enctype="multipart/form-data">
      			<h2>내 정보 수정</h2>
      			<hr class="colorgraph">
						<div class="row">
      				<div class="col-xs-12 col-sm-6 col-md-6">
      					<div class="form-group">
									<input type="text" name="mem_id" id="mem_id" class="form-control input-lg" value="<?php echo $_SESSION['mem_id'] ?>" placeholder="아이디는 변경할 수 없습니다." maxlength="20" tabindex="1" readonly>
      					</div>
      				</div>
      				<div class="col-xs-12 col-sm-6 col-md-6">
      					<div class="form-group">
									<input type="text" name="mem_nick" id="mem_nick" class="form-control input-lg" value="<?php echo $_SESSION['mem_nick'] ?>" placeholder="닉네임 (2~8자)" minlength="2" maxlength="8" tabindex="2">
      					</div>
      				</div>
      			</div>
            <div class="form-group">
      				<input type="password" name="oldpassword" id="oldpassword" class="form-control input-lg" placeholder="현재 비밀번호" maxlength="20" tabindex="3">
      			</div>
      			<div class="row">
      				<div class="col-xs-12 col-sm-6 col-md-6">
      					<div class="form-group">
      						<input type="password" name="mem_pwd" id="mem_pwd" class="form-control input-lg" placeholder="새 비밀번호 (영문/숫자 20자 이하)" maxlength="20" tabindex="4">
      					</div>
      				</div>
      				<div class="col-xs-12 col-sm-6 col-md-6">
      					<div class="form-group">
      						<input type="password" name="password_confirmation" id="password_confirmation" class="form-control input-lg" placeholder="새 비밀번호 확인" tabindex="5">
      					</div>
      				</div>
      			</div>
						<div class="form-group">
      				<input type="text" name="mem_email" id="mem_email" class="form-control input-lg" value="<?php echo $_SESSION['mem_email'] ?>" placeholder="새로운 이메일로 수정" tabindex="6">
      			</div>
            <div class="form-group">
      				<input type="file" name="mem_img" id="mem_img" class="form-control input-lg" placeholder="프로필 사진" tabindex="7">
      			</div>
            <div class="form-group">
      				<textarea name="mem_intro" id="mem_intro" class="form-control input-lg" maxlength="100" placeholder="소개글 (100자 이하로 입력)" rows="5" style="resize: none;" tabindex="8"><?php echo $_SESSION['mem_intro'] ?></textarea>
      			</div>

            <div class="row">
              <br/>
              <span style="color: red;">＊</span>&nbsp;<span style="color: gray;">프로필 사진을 첨부하지 않았다면 사진은 변경되지 않습니다.</span>
      			</div>

      			<hr class="colorgraph">
      			<div class="row">
      				<div class="col-xs-12 col-md-4"><a href="javascript:goMyEdit();" class="btn btn-primary btn-block btn-lg" tabindex="9">정보 수정</a></div>
              <div class="col-xs-12 col-md-4"><a href="javascript:goVaccum();" class="btn btn-success btn-block btn-lg" tabindex="8">다시 입력</a></div>
							<div class="col-xs-12 col-md-4"><a href="javascript:goWithdraw();" class="btn btn-danger btn-block btn-lg" tabindex="8">회원 탈퇴</a></div>
      			</div>

						<input type="hidden" name="MAX_FILE_SIZE" value="3000000000" />
      		</form>
      	</div>
      </div>

    </div>
  </div>
</body>
</html>
