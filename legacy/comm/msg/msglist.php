<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8" />
	<title>Penna - Member(ADMIN)</title>
	<link rel="stylesheet" type="text/css" href="../bootstrap/css/bootstrap.css">
	<link rel="stylesheet" type="text/css" href="https://cdnjs.cloudflare.com/ajax/libs/sweetalert/1.1.3/sweetalert.min.css" />
	<script type="text/javascript" src="../js/jquery-2.1.3.min.js"></script>
	<script type="text/javascript" src="../bootstrap/js/bootstrap.js"></script>
	<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/sweetalert/1.1.3/sweetalert.min.js"></script>
	<?php
	include("../dbconfig.php");

	session_start();
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
	} else {
		$mem_id = $_SESSION['mem_id'];
	}

  //쪽지 분류
  if(isset($_GET['msg_kind'])){
    $msg_kind = $_GET['msg_kind'];
  } else {
    $msg_kind = '전체';
  }

	/* 페이징 시작 */

	//페이지 get 변수가 있다면 받아오고, 없다면 1페이지를 보여준다.

	if(isset($_GET['page'])) {
		$page = $_GET['page'];
	} else {
		$page = 1;
	}


	//회원리스트
	try{
		$pdo = new PDO($dsn, $dbUser, $dbPwd);
	  $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
	  //$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMORE_EXCEPTION);

		switch ($msg_kind) {
			case '전체':
				$sql = "SELECT count(*) as cnt from (SELECT * FROM tbl_message WHERE msg_recvid = '$mem_id' AND msg_recvstatus = 1 union SELECT * FROM tbl_message WHERE msg_sendid = '$mem_id' AND msg_sentstatus = 1) x order by msg_idx desc";
				break;
			case '안읽음':
				$sql = "SELECT count(*) as cnt FROM tbl_message WHERE msg_recvid = '$mem_id' AND msg_readdate is null AND msg_recvstatus = 1 order by msg_idx desc";
				break;
			case '수신':
				$sql = "SELECT count(*) as cnt FROM tbl_message WHERE msg_recvid = '$mem_id' AND msg_recvstatus = 1 ORDER BY msg_idx DESC";
				break;
			case '발신':
				$sql = "SELECT count(*) as cnt FROM tbl_message WHERE msg_sendid = '$mem_id' AND msg_sentstatus = 1 ORDER BY msg_idx DESC";
				break;
		}
		$stm = $pdo->prepare($sql);
		$stm->execute();
		$row = $stm->fetchAll(PDO::FETCH_ASSOC);

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

	foreach($row as $j){
		$allPost = $j['cnt']; //전체 게시글의 수
	}
	$onePage = 15; // 한 페이지에 보여줄 게시글의 수.
	$allPage = ceil($allPost / $onePage); //전체 페이지의 수

	if($page < 1 || ($allPage && $page > $allPage)){
		echo "<script>
	        $(window).load(function(){
	          $(document).ready(function () {
	            swal({
	              title: '존재하지 않는 페이지입니다.',
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

	$oneSection = 10; //한번에 보여줄 총 페이지 개수(1 ~ 10, 11 ~ 20 ...)
	$currentSection = ceil($page / $oneSection); //현재 섹션
	$allSection = ceil($allPage / $oneSection); //전체 섹션의 수
	$firstPage = ($currentSection * $oneSection) - ($oneSection - 1); //현재 섹션의 처음 페이지

	if($currentSection == $allSection) {
		$lastPage = $allPage; //현재 섹션이 마지막 섹션이라면 $allPage가 마지막 페이지가 된다.
	} else {
		$lastPage = $currentSection * $oneSection; //현재 섹션의 마지막 페이지
	}

	$prevPage = (($currentSection - 1) * $oneSection); //이전 페이지, 11~20일 때 이전을 누르면 10 페이지로 이동.
	$nextPage = (($currentSection + 1) * $oneSection) - ($oneSection - 1); //다음 페이지, 11~20일 때 다음을 누르면 21 페이지로 이동.

	$paging = ''; // 페이징을 저장할 변수

	//첫 페이지가 아니라면 처음 버튼을 생성
	if($page != 1) {
		$paging .= '<a href="./msglist.php?page=1" class="btn btn-default">◀◀</a>';
	}
	//첫 섹션이 아니라면 이전 버튼을 생성
	if($currentSection != 1) {
		$paging .= '<a href="./msglist.php?page=' . $prevPage . '&msg_kind=' . $msg_kind . '" class="btn btn-default">◀</a>';
	}

	for($i = $firstPage; $i <= $lastPage; $i++) {
		if($i == $page) {
			$paging .= '<a href="#" class="btn btn-primary">' . $i . '</a>';
		} else {
			$paging .= '<a href="./msglist.php?page=' . $i . '&msg_kind=' . $msg_kind . '" class="btn btn-default">' . $i . '</a>';
		}
	}

	//마지막 섹션이 아니라면 다음 버튼을 생성
	if($currentSection != $allSection) {
		$paging .= '<a href="./msglist.php?page=' . $nextPage . '&msg_kind=' . $msg_kind . '" class="btn btn-default">▶</a>';
	}
	//마지막 페이지가 아니라면 끝 버튼을 생성
	if($page != $allPage) {
		$paging .= '<a href="./msglist.php?page=' . $allPage . '&msg_kind=' . $msg_kind . '" class="btn btn-default">▶▶</a>';
	}
	//$paging .= '</ul>';

	/* 페이징 끝 */
	$currentLimit = ($onePage * $page) - $onePage; //몇 번째의 글부터 가져오는지
	$sqlLimit = ' limit ' . $currentLimit . ', ' . $onePage; //limit sql 구문

	try{
		$pdo = new PDO($dsn, $dbUser, $dbPwd);
	  $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
	  //$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMORE_EXCEPTION);

		switch ($msg_kind) {
			case '전체':
				$sql = "SELECT * from (SELECT * FROM tbl_message WHERE msg_recvid = '$mem_id' AND msg_recvstatus = 1 union SELECT * FROM tbl_message WHERE msg_sendid = '$mem_id' AND msg_sentstatus = 1) x order by msg_idx desc" . $sqlLimit;
				break;
			case '안읽음':
				$sql = "SELECT * FROM tbl_message WHERE msg_recvid = '$mem_id' AND msg_readdate is null AND msg_recvstatus = 1 order by msg_idx desc" . $sqlLimit;
				break;
			case '수신':
				$sql = "SELECT * FROM tbl_message WHERE msg_recvid = '$mem_id' AND msg_recvstatus = 1 order by msg_idx desc" . $sqlLimit;
				break;
			case '발신':
				$sql = "SELECT * FROM tbl_message WHERE msg_sendid = '$mem_id' AND msg_sentstatus = 1 order by msg_idx desc" . $sqlLimit;
				break;
		}
		$stm = $pdo->prepare($sql);
		$stm->execute();
		$result = $stm->fetchAll(PDO::FETCH_ASSOC);

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
  <style type="text/css">
    .imgtop {
      text-align: center;
			height: 200px;
    }

    body {
      font-size: 1.5em;
      background: #eee;
    }

    th, tr, td {
      text-align: center;
    }

    #footer {
       position:fixed;
       float: right;
       left:0px;
       bottom:0px;
       height:30px;
       width:20%;
       background:#EEE;
       color: white;
    }
    #footer p {
       text-align: center;
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
	<script type="text/javascript">

    $(document).ready(function(){
        $("#report tr:odd").addClass("odd");
        $("#report tr:not(.odd)").hide();
        $("#report tr:first-child").show(); //열머리글 보여주기

        $("#report tr.odd").click(function(){
            $(this).next("tr").toggle();
            $(this).find(".arrow").toggleClass("up");
        });
    });

    function goMsgWrite(){
      $("#writeModal").modal();
    }

    function goMsgRewrite(sendid, title, contents){
      var re_id = document.getElementById("re_recv_id");
      var re_title = document.getElementById("re_msg_title");
      var re_contents = document.getElementById("re_msg_contents");

      re_id.value = sendid;
      re_title.value = "Re : "+title;
      re_contents.value = "\n-------------------- [ Original Message ] --------------------\n"+contents;

      $("#rewriteModal").modal();
    }

    function goCheckMsgAll(frm){
			var checkitem = document.getElementsByName("checkRow[]");

      for (i=0; i<checkitem.length; i++)
        checkitem[i].checked = frm.checkMsgAll.checked;
		}

		function goChoiceDel(){
			var checkRow = "";
		  $( "input[name='checkRow[]']:checked" ).each (function (){
		    checkRow = checkRow + $(this).val()+"," ;
		  });
		  checkRow = checkRow.substring(0,checkRow.lastIndexOf( ",")); //맨끝 콤마 지우기

		  if(checkRow == ''){
		    swal("삭제할 쪽지를 선택하세요.");
		    return false;
		  }
			//console.log("### checkRow => {}"+checkRow);

			swal({
			  title: "선택한 쪽지들을 삭제하시겠습니까?",
			  type: "danger",
			  showCancelButton: true,
			  confirmButtonClass: "btn-danger",
			  confirmButtonText: "확인",
			},
			function(isConfirm) {
			  if (isConfirm) {
				 var frm = document.delFrm;
				 frm.submit;
			  }
			});
		}

		function goReading(msg_idx, msg_recvid, msg_readdate){

			var params = {};
			params['msg_idx'] = msg_idx;
			params['msg_recvid'] = msg_recvid;
			params['msg_readdate'] = msg_readdate;

			$.ajax({
					 type: 'POST',     // POST 또는 GET
					 url: 'msg_read.php',    // 요청할 url 주소
					 data: params,    // 서버에 전달하는 parameters
					 success: function (data) {
								// 정상적으로 완료시 호출

					 },
					 error: function (request, status, error) {
								// 오류 발생시 호출
								swal("문제 발생! 관리자에게 문의하세요.");
								return;
					 },
			});
		}

	</script>
</head>
<body>
  <div style="height: 100%; overflow: hidden; margin-left: 10%; margin-right: 10%;">
    <div class="imgtop">
      <a href="notice.php" target="content"><img src="../images/plogo.png" style="width: 150px; height: 150px; margin-top: 25px;"/></a>
    </div>

		<div style="text-align: center;">
      <h1>쪽지함</h1>
    </div>

		<br/>

    <div style="text-align: center; width: 70%; margin-left: 15%; float: left; margin-bottom: 1%;">
      <div style="width: 24%; display: inline-block;">
        <a href="msglist.php" class="btn btn-block btn-default">전체 쪽지</a>
      </div>
			<div style="width: 24%; display: inline-block;">
        <a href="msglist.php?msg_kind=안읽음" class="btn btn-block btn-default">안 읽은 쪽지</a>
      </div>
      <div style="width: 24%; display: inline-block;">
        <a href="msglist.php?msg_kind=발신" class="btn btn-block btn-danger">보낸 쪽지</a>
      </div>
      <div style="width: 24%; display: inline-block;">
        <a href="msglist.php?msg_kind=수신" class="btn btn-block btn-primary">받은 쪽지</a>
      </div>
    </div>

    <form name="delFrm" method="post" action="msg_choice_del.php">
    <table id="report" class="table table-hover" style="width: 70%; margin-left: 15%; margin-top: 1%;">
      <thead style="border-top: 1px solid gray; background-color: #F2F2F2;">
        <tr>
          <th width="4%"><input type='checkbox' id='checkMsgAll' name='checkMsgAll' onclick='javascript:goCheckMsgAll(delFrm)' /></th>
          <th width="8%">발신인</th>
					<th width="8%">수신인</th>
          <th width="50%">제목</th>
          <th width="15%">발신날짜</th>
          <th width="15%">읽은날짜</th>
        </tr>
      </thead>
      <tbody style="border-bottom: 1px solid gray;">
        <?php
          foreach ($result as $row) {
        ?>
          <tr style="cursor: pointer;" onclick="javascript:goReading('<?php echo $row['msg_idx'] ?>', '<?php echo $row['msg_recvid'] ?>', '<?php echo $row['msg_readdate'] ?>');">
            <td style="vertical-align: middle;"><input type='checkbox' name='checkRow[]' value='<?php echo $row['msg_idx'] ?>' /></td>
						<td style="vertical-align: middle;"><?php echo '<span style="color: #FF4000;">'.$row['msg_sendid'].'</span>'; ?></td>
            <td style="vertical-align: middle;"><?php echo '<span style="color: #0431B4;">'.$row['msg_recvid'].'</span>';?></td>
            <td style="vertical-align: middle;"><?php echo $row['msg_title'] ?></td>
            <td style="vertical-align: middle;"><?php echo $row['msg_sentdate'] ?></td>
            <td style="vertical-align: middle;">
							<?php
								if(empty($row['msg_readdate'])){
									echo '읽지 않음';
								} else {
									echo $row['msg_readdate'];
								}
							?>
						</td>
          </tr>
          <tr>
            <td colspan="6">
              <?php echo $row['msg_contents'] ?>
              <br/>
              <div style="float: right;">
                <?php
                if($row['msg_sendid'] != $mem_id){
                ?>
                  <a href="javascript:goMsgRewrite('<?php echo $row['msg_sendid'] ?>', '<?php echo $row['msg_title'] ?>', '<?php echo $row['msg_contents'] ?>')" class="btn btn-sm btn-primary">답장</a>
                <?php } ?>
                <a href="msg_del.php?msg_idx=<?php echo $row['msg_idx'] ?>" class="btn btn-sm btn-danger">삭제</a>
              </div>
            </td>
          </tr>
        <?php
          }
        ?>
      </tbody>
    </table>
    </form>

    <div class="paging" style="text-align: center;">
      <?php
        if(!empty($result)){
          echo $paging;
        }
      ?>
    </div>

		<br/>

		<div>
      <div style="float: left; margin-left: 15%;">
        <a href="javascript:goMsgWrite();" class="btn btn-sm btn-primary"><span class="glyphicon glyphicon-edit"></span> 쪽지쓰기</a>
        <a href="javascript:goChoiceDel();" class="btn btn-sm btn-danger">선택삭제</a>
      </div>
			<div style="float: right; margin-right: 15%;">
				<form class="form-inline" method="get" action="msglist_search.php">
					<?php
						switch ($msg_kind) {
							case '전체': ?>
							<select id="searchSubject" name="searchSubject" class="form-control">
							 <option value="제목">제목</option>
							 <option value="내용">내용</option>
							 <option value="수신인">수신인</option>
							 <option value="발신인">발신인</option>
							</select>
						 <?php
								break;
							case '안읽음': ?>
							<select id="searchSubject" name="searchSubject" class="form-control">
							 <option value="제목">제목</option>
							 <option value="내용">내용</option>
							 <option value="발신인">발신인</option>
							</select>
						 <?php
								break;
							case '수신': ?>
							<select id="searchSubject" name="searchSubject" class="form-control">
							 <option value="제목">제목</option>
							 <option value="내용">내용</option>
							 <option value="발신인">발신인</option>
						 </select>
						 <?php
								break;
							case '발신': ?>
							<select id="searchSubject" name="searchSubject" class="form-control">
	             <option value="제목">제목</option>
	             <option value="내용">내용</option>
							 <option value="수신인">수신인</option>
	   				 </select>
						 <?php
								break;
						}
					?>
          <input type="text" class="form-control" id="searchString" name="searchString" style="width: 200px;" />
          <input type="hidden" id="searchKind" name="searchKind" value="<?php echo $msg_kind ?>" />
					<button type="submit" class="btn btn-default"><span class="glyphicon glyphicon-search"></span></button>
				</form>
			</div>
		</div>

		<br/><br/><br/><br/>

    <div id="footer">
      &nbsp;&nbsp;
      <button type="button" onclick="goTop();" class="btn btn-primary btn-default"><span class="glyphicon glyphicon-arrow-up"></span> </button>
      <button type="button" onclick="goBottom();" href="#" class="btn btn-primary btn-default"><span class="glyphicon glyphicon-arrow-down"></span> </button>
    </div>

  </div>
</body>
</html>

<!-- 쪽지 작성용 Modal -->
<div class="modal fade" id="writeModal" role="dialog">
  <div class="modal-dialog">

    <!-- Modal content-->
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal">×</button>
        <h4 class="modal-title">쪽지 보내기</h4>
      </div>
      <div class="modal-body">
        <form class="form-inline" method="post" action="msg_write_ok.php">
				 	수신자 : <input type="text" class="form-control" id="recv_id" name="recv_id" style="width: 20%; margin-bottom: 1%;" />
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


<!-- 쪽지 답장용 Modal -->
<div class="modal fade" id="rewriteModal" role="dialog">
  <div class="modal-dialog">

    <!-- Modal content-->
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal">×</button>
        <h4 class="modal-title">답장 보내기</h4>
      </div>
      <div class="modal-body">
        <form class="form-inline" method="post" action="msg_rewrite_ok.php">
				 	수신자 : <input type="text" class="form-control" id="re_recv_id" name="re_recv_id" style="width: 20%; margin-bottom: 1%;" readonly />
					<input type="text" class="form-control" id="re_msg_title" name="re_msg_title" style="width: 100%; margin-bottom: 1%;" maxlength="100" minlength="1" placeholder="쪽지 제목" />
          <textarea class="form-control" id="re_msg_contents" name="re_msg_contents" style="width: 100%; height: 300px; resize: none;" placeholder="쪽지 내용"></textarea>
          <br/><br/>
					<div style="text-align: center;">
          	<button type="submit" class="btn btn-sm btn-success">답장 보내기</button>
					</div>
        </form>
      </div>
      <div class="modal-footer">
        <!-- <button type="button" class="btn btn-default" data-dismiss="modal">Close</button> -->
      </div>
    </div>

  </div>
</div>
