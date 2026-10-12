<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8" />
	<title>Penna - Notice</title>
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
	  $(function () {
	  $('.button-checkbox').each(function () {

	          // Settings
	          var $widget = $(this),
	              $button = $widget.find('button'),
	              $checkbox = $widget.find('input:checkbox'),
	              color = $button.data('color'),
	              settings = {
	                  on: {
	                      icon: 'glyphicon glyphicon-check'
	                  },
	                  off: {
	                      icon: 'glyphicon glyphicon-unchecked'
	                  }
	              };

	          // Event Handlers
	          $button.on('click', function () {
	              $checkbox.prop('checked', !$checkbox.is(':checked'));
	              $checkbox.triggerHandler('change');
	              updateDisplay();
	          });
	          $checkbox.on('change', function () {
	              updateDisplay();
	          });

	          // Actions
	          function updateDisplay() {
	              var isChecked = $checkbox.is(':checked');

	              // Set the button's state
	              $button.data('state', (isChecked) ? "on" : "off");

	              // Set the button's icon
	              $button.find('.state-icon')
	                  .removeClass()
	                  .addClass('state-icon ' + settings[$button.data('state')].icon);

	              // Update the button's color
	              if (isChecked) {
	                  $button
	                      .removeClass('btn-default')
	                      .addClass('btn-' + color + ' active');
	              }
	              else {
	                  $button
	                      .removeClass('btn-' + color + ' active')
	                      .addClass('btn-default');
	              }
	          }

	          // Initialization
	          function init() {

	              updateDisplay();

	              // Inject the icon if applicable
	              if ($button.find('.state-icon').length == 0) {
	                  $button.prepend('<i class="state-icon ' + settings[$button.data('state')].icon + '"></i>');
	              }
	          }
	          init();
	      });
	    });

			function goRegister(){
				var frm = document.RegisterForm();

				var id = document.getElementById("mem_id");
				var nick = document.getElementById("mem_nick");
				var email1 = document.getElementById("mem_email1");
				var email2 = document.getElementById("mem_email2");
				var pwd = document.getElementById("mem_pwd");
				var repwd = document.getElementById("pwd_confirmation");
				var img = document.getElementById("mem_img");
				var ok = document.getElementById("t_and_c");

				var email = email1.value + "@" + email2.value;
				var emailcheck = /^[A-Za-z0-9_\.\-]+@[A-Za-z0-9\-]+\.[A-Za-z0-9\-]+/;

				if(img.value == "" || img.value == null){
					frm.mem_img.value = "default_img.png";
				}

				if(id.value == "" || id.value == null){
					swal("아이디를 입력하지 않았습니다.");
					return false;
				} else {
					if(nick.value == "" || nick.value == null){
						swal("닉네임을 입력하지 않았습니다.");
					} else {
						if(!emailcheck.test(email)){
							swal("이메일 형식이 올바르지 않습니다.");
							return false;
						} else {
							if(pwd.value == "" || pwd.value == null){
								swal("비밀번호를 입력하지 않았습니다.");
								return false;
							} else {
								if(pwd.value != repwd.value){
									swal("비밀번호와 비밀번호 확인란이 서로 일치하지 않습니다.");
									return false;
								} else {
									if(ok.checked == false){
										swal("확인 버튼을 눌러주세요.");
										return false;
									} else {
										return true;
									}
								}
							}
						}
					}
				}

			}


			function goVaccum(){
				var id = document.getElementById("mem_id");
				var nick = document.getElementById("mem_nick");
				var email1 = document.getElementById("mem_email1");
				var email2 = document.getElementById("mem_email2");
				var pwd = document.getElementById("mem_pwd");
				var repwd = document.getElementById("pwd_confirmation");
				var img = document.getElementById("mem_img");
				var intro = document.getElementById("mem_intro");

				id.value = "";
				nick.value = "";
				email1.value = "";
				email2.value = "";
				pwd.value = "";
				repwd.value = "";
				img.value = "";
				intro.value = "";
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
      		<form name="RegisterForm" role="form" method="post" action="register_ok.php" onsubmit="return goRegister()" enctype='multipart/form-data'>
      			<h2>회원가입</h2>
      			<hr class="colorgraph">
						<div class="row">
      				<div class="col-xs-12 col-sm-6 col-md-6">
      					<div class="form-group">
									<input type="text" name="mem_id" id="mem_id" class="form-control input-lg" pattern="[A-Za-z0-9]*" placeholder="아이디 (영문/숫자 5~20자)" minlength="5" maxlength="20" tabindex="1">
      					</div>
      				</div>
      				<div class="col-xs-12 col-sm-6 col-md-6">
      					<div class="form-group">
									<input type="text" name="mem_nick" id="mem_nick" class="form-control input-lg" placeholder="닉네임 (2~8자)" minlength="2" maxlength="8" tabindex="2">
      					</div>
      				</div>
      			</div>
            <div class="row">
      				<div class="col-xs-12 col-sm-6 col-md-6">
      					<div class="form-group">
      						<input type="text" name="mem_email1" id="mem_email1" class="form-control input-lg" placeholder="이메일 주소 @ 이전 입력" maxlength="20" tabindex="3">
      					</div>
      				</div>
      				<div class="col-xs-12 col-sm-6 col-md-6">
      					<div class="form-group">
      						<input type="text" name="mem_email2" id="mem_email2" class="form-control input-lg" placeholder="@ 이후 입력" tabindex="4">
      					</div>
      				</div>
      			</div>
      			<div class="row">
      				<div class="col-xs-12 col-sm-6 col-md-6">
      					<div class="form-group">
      						<input type="password" name="mem_pwd" id="mem_pwd" class="form-control input-lg" placeholder="비밀번호 (영문/숫자 8~20자)" minlength="8" maxlength="20" tabindex="5">
      					</div>
      				</div>
      				<div class="col-xs-12 col-sm-6 col-md-6">
      					<div class="form-group">
      						<input type="password" name="pwd_confirmation" id="pwd_confirmation" class="form-control input-lg" minlength="8" maxlength="20" placeholder="비밀번호 확인" tabindex="6">
      					</div>
      				</div>
      			</div>
            <div class="form-group">
      				<input type="file" name="mem_img" id="mem_img" class="form-control input-lg" placeholder="프로필 사진" tabindex="7">
      			</div>
            <div class="form-group">
      				<textarea name="mem_intro" id="mem_intro" class="form-control input-lg" maxlength="100" placeholder="소개글 (100자 이하로 입력)" rows="5" style="resize: none;" tabindex="8"></textarea>
      			</div>
      			<div class="row">
    					<span class="button-checkbox">
    						<button type="button" class="btn" data-color="info" tabindex="9">&nbsp;&nbsp;&nbsp;확인</button>
                            <input type="checkbox" name="t_and_c" id="t_and_c" class="hidden">
    					</span>
              <br/><br/>
              <span style="color: red;">＊</span>&nbsp;<span style="color: gray;">파일 선택 구간은 프로필 사진에 들어갑니다. (필수 아님)</span>
      			</div>

      			<hr class="colorgraph">
      			<div class="row">
      				<div class="col-xs-12 col-md-6"><input type="submit" value="가입하기" class="btn btn-primary btn-block btn-lg" tabindex="10"></div>
              <div class="col-xs-12 col-md-6"><a href="javascript:goVaccum();" class="btn btn-success btn-block btn-lg" tabindex="11">다시 입력</a></div>
      			</div>

						<input type="hidden" name="MAX_FILE_SIZE" value="3000000000" />
      		</form>
      	</div>
      </div>

    </div>
  </div>
</body>
</html>
