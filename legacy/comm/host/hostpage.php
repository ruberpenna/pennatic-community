<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8" />
	<title>Penna - Host</title>
	<link rel="stylesheet" type="text/css" href="../bootstrap/css/bootstrap.css">
	<link rel="stylesheet" type="text/css" href="https://cdnjs.cloudflare.com/ajax/libs/sweetalert/1.1.3/sweetalert.min.css" />
	<link href="../summernote/summernote.css" rel="stylesheet">
	<script type="text/javascript" src="../js/jquery-2.1.3.min.js"></script>
	<script type="text/javascript" src="../bootstrap/js/bootstrap.js"></script>
	<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/sweetalert/1.1.3/sweetalert.min.js"></script>
	<script src="../summernote/summernote.js"></script>
	<script src="../summernote/lang/summernote-ko-KR.js"></script>
	<?php
	session_start();

	if(isset($_SESSION['mem_id'])){
		$mem_id = $_SESSION['mem_id'];
	} else {
		$mem_id = '';
	}
	?>
  <style type="text/css">
    .imgtop {
      text-align: center;
			height: 200px;
    }

    body {
      font-size: 1.5em;
      background: #eee;
    }

		.modal {
        text-align: center;
    }

    @media screen and (min-width: 768px) {
            .modal:before {
                    display: inline-block;
                    vertical-align: middle;
                    content: " ";
                    height: 100%;
            }
    }

    .modal-dialog {
            display: inline-block;
            text-align: left;
            vertical-align: middle;
    }

  </style>
  <script language=javascript>
		$(function() {
			$('.summernote').summernote({
				height: 300,          // 기본 높이값
				minHeight: null,      // 최소 높이값(null은 제한 없음)
				maxHeight: null,      // 최대 높이값(null은 제한 없음)
				focus: true,          // 페이지가 열릴때 포커스를 지정함
				lang: 'ko-KR'         // 한국어 지정(기본값은 en-US)
			});
		});

		function goMailModal(){
			$("#mailModal").modal();
		}

		function goMsgModal(mem_id){
			if(mem_id == null || mem_id == ''){
				swal("로그인이 필요합니다.");
			} else {
				$("#msgModal").modal();
			}

		}
  </script>
</head>
<body>
  <div style="height: 100%; overflow: hidden; margin-left: 10%; margin-right: 10%;">
    <div class="imgtop">
      <a href="../board/notice.php" target="content"><img src="../images/plogo.png" style="width: 150px; height: 150px; margin-top: 25px;"/></a>
    </div>

    <div style="text-align: center;">
      <h1>주인장 프로필</h1>
    </div>

    <br/><br/>

    <div class="container-fluid well span6" style="width: 50%;">
    	<div class="row-fluid">
        <div class="span2" style="float: left;">
		        <img src="../files/pink.jpg" style="width: 150px; height: 150px; margin-right: 50px;" class="img-circle">
        </div>

        <div class="span8" style="float: left;">
            <h3>페나(penna)</h3>
            <br/>
            <h5>이메일 : mr_kururu@naver.com</h5>
            <h5>국적 : 헬조선</h5>
            <h5>직업 : 백수</h5>
        </div>

        <div class="span2" style="float: right;">
            <div class="btn-group">
                <a class="btn dropdown-toggle btn-info" data-toggle="dropdown" href="#">
                    서브메뉴
                    <span class="icon-cog icon-white"></span><span class="caret"></span>
                </a>
                <ul class="dropdown-menu">
                    <li><a href="javascript:goMailModal();"><span class="icon-wrench"></span> 메일보내기</a></li>
                    <li><a href="javascript:goMsgModal('<?php echo $mem_id ?>');"><span class="icon-trash"></span> 쪽지보내기</a></li>
                </ul>
            </div>
        </div>
      </div>
    </div>
  </div>
</body>
</html>

<!-- 운영자한테 메일 보내기용 Modal -->
<div class="modal fade" id="mailModal" role="dialog">
  <div class="modal-dialog modal-lg">

    <!-- Modal content-->
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal">×</button>
        <h4 class="modal-title">메일 보내기</h4>
      </div>
      <div class="modal-body">
        <form class="form-inline" method="post" action="../host_sendmail.php">
					운영자 메일 : <input type="text" class="form-control" style="width: 20%; margin-bottom: 1%;" value="mr_kururu@naver.com" readonly  />
					&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
					보내는 메일 : <input type="text" class="form-control" id="sendmail" name="sendmail" style="width: 20%; margin-bottom: 1%;" maxlength="50" />
					<input type="text" class="form-control" id="mail_title" name="mail_title" style="width: 100%; margin-bottom: 1%;" maxlength="100" minlength="1" placeholder="메일 제목" />
          <textarea class="summernote" id="mail_contents" name="mail_contents" style="width: 100%;"></textarea>
          <br/>
					<div style="text-align: center;">
          	<button type="submit" class="btn btn-sm btn-success">보내기</button>
					</div>
        </form>
      </div>
      <div class="modal-footer">
        <!-- <button type="button" class="btn btn-default" data-dismiss="modal">Close</button> -->
      </div>
    </div>

  </div>
</div>

<!-- 운영자한테 쪽지 보내기용 Modal -->
<div class="modal fade" id="msgModal" role="dialog">
  <div class="modal-dialog">

    <!-- Modal content-->
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal">×</button>
        <h4 class="modal-title">쪽지 보내기</h4>
      </div>
      <div class="modal-body">
        <form class="form-inline" method="post" action="../msg/msg_write_ok.php">
				 	수신자 : <input type="text" class="form-control" id="recv_id" name="recv_id" style="width: 20%; margin-bottom: 1%;" value="adminpenna" readonly />
					&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
					발신자 : <input type="text" class="form-control" id="send_id" name="send_id" style="width: 20%; margin-bottom: 1%;" value="<?php echo $mem_id ?>" readonly />
					<input type="text" class="form-control" id="msg_title" name="msg_title" style="width: 100%; margin-bottom: 1%;" maxlength="100" minlength="1" placeholder="쪽지 제목" />
          <textarea class="form-control" id="msg_contents" name="msg_contents" style="width: 100%; height: 300px; resize: none;"></textarea>
          <br/><br/>
					<div style="text-align: center;">
          	<button type="submit" class="btn btn-sm btn-success">쪽지 수신</button>
					</div>
        </form>
      </div>
      <div class="modal-footer">
        <!-- <button type="button" class="btn btn-default" data-dismiss="modal">Close</button> -->
      </div>
    </div>

  </div>
</div>
