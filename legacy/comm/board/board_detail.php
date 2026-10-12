<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8" />
	<title>Penna - Board</title>
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

  if(isset($_GET['board_folder']) && isset($_GET['board_group']) && isset($_GET['board_idx']) && isset($_GET['board_page']) && isset($_GET['board_status'])){
    $folder = $_GET['board_folder'];
    $group = $_GET['board_group'];
    $idx = $_GET['board_idx'];
    $board_page = $_GET['board_page'];
    $status = $_GET['board_status'];
  } else {
    echo "<script>
					$(window).load(function(){
						$(document).ready(function () {
							swal({
								title: '잘못된 접근입니다.',
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

  //로그인이 되어있을 경우
  if(isset($_SESSION['mem_id'])){
    $session_id = $_SESSION['mem_id'];
  }

  //조회수 증가
  if(!empty($idx) && empty($_COOKIE['tbl_board_' . $idx])) {
    try{
    	$pdo = new PDO($dsn, $dbUser, $dbPwd);
      $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
      //$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMORE_EXCEPTION);

      $sql = 'UPDATE tbl_board set board_count = board_count + 1 where board_idx = ' . $idx;
    	$stm = $pdo->prepare($sql);
    	$stm->execute();

      setcookie('tbl_board_' . $idx, TRUE, time() + 86400 * 365, '/');

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

  //쿠키 유효기간 증가
  if ( isset($_COOKIE['tbl_board_' . $idx]) && $_COOKIE['tbl_board_' . $idx]-time()<86400*30 ) { // 쿠키가 있고, 유효 시간이 한 달 미만이면
      $exp = time() + 86400*365; // 다시 1년 유효
      setcookie('tbl_board_' . $idx, TRUE, $exp, '/');
      //setcookie('원하는쿠키','값',$exp);
  }

  //게시물 내용과 글쓴이의 정보 담기
  try{
  	$pdo = new PDO($dsn, $dbUser, $dbPwd);
    $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
    //$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMORE_EXCEPTION);

  	//board_status가 2 -> 공지사항, 3 -> 게시판 그룹 생성용
    $sql = "SELECT board_idx, board_folder, board_group, board_subject, board_title, board_content, board_wdate, board_edate,
                      board_count, board_status, fk_mem_id,
                      mem_idx, mem_id, mem_pwd, mem_nick, mem_email, mem_intro, mem_emblem,
                      mem_img, mem_join, mem_withdraw, mem_status
            FROM tbl_board join tbl_member
            on tbl_board.fk_mem_id = tbl_member.mem_id
            WHERE board_status = 1 AND board_idx = '" . $idx . "'";
  	$stm = $pdo->prepare($sql);
  	$stm->execute();
  	$result = $stm->fetchAll(PDO::FETCH_ASSOC);

    foreach($result as $row){
      $board_idx = $row['board_idx'];
      $board_folder = $row['board_folder'];
      $board_group = $row['board_group'];
      $board_subject = $row['board_subject'];
      $board_title = $row['board_title'];
      $board_content = $row['board_content'];
      $board_wdate = $row['board_wdate'];
      $board_edate = $row['board_edate'];
      $board_count = $row['board_count'];
      $board_status = $row['board_status'];

      $mem_idx = $row['mem_idx'];
      $mem_id = $row['mem_id'];
      $mem_pwd = $row['mem_pwd'];
      $mem_nick = $row['mem_nick'];
      $mem_email = $row['mem_email'];
      $mem_intro = $row['mem_intro'];
      $mem_emblem = $row['mem_emblem'];
      $mem_img = $row['mem_img'];
      $mem_join = $row['mem_join'];
      $mem_withdraw = $row['mem_withdraw'];
      $mem_status = $row['mem_status'];
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


  /* 댓글 페이징 시작 */
  if(isset($_GET['comm_page'])) {
  	$page = $_GET['comm_page'];
  } else {
  	$page = 1;
  }

  try{
  	$pdo = new PDO($dsn, $dbUser, $dbPwd);
    $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
    //$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMORE_EXCEPTION);

  	//board_status가 2 -> 공지사항, 3 -> 게시판 그룹 생성용
    $sql = "SELECT count(*) as cnt FROM tbl_comment WHERE comm_status = 1 AND fk_board_idx = '" . $idx . "' ORDER BY comm_idx ASC";
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
  	$allPost = $j['cnt']; //전체 댓글의 수
  }
  $onePage = 30; // 한 페이지에 보여줄 댓글의 수.
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
  	$paging .= '<a href="./board_detail.php?board_folder='.$folder.'&board_group='.$group.'&board_idx='.$idx.'&board_status='.$status.'&comm_page=1" class="btn btn-sm btn-default">◀◀</a>';
  }
  //첫 섹션이 아니라면 이전 버튼을 생성
  if($currentSection != 1) {
  	$paging .= '<a href="./board_detail.php?board_folder='.$folder.'&board_group='.$group.'&board_idx='.$idx.'&board_status='.$status.'&comm_page=' . $prevPage . '" class="btn btn-sm btn-default">◀</a>';
  }

  for($i = $firstPage; $i <= $lastPage; $i++) {
  	if($i == $page) {
  		$paging .= '<a href="#" class="btn btn-sm btn-primary">' . $i . '</a>';
  	} else {
  		$paging .= '<a href="./board_detail.php?board_folder='.$folder.'&board_group='.$group.'&board_idx='.$idx.'&board_status='.$status.'&comm_page=' . $i . '" class="btn btn-sm btn-default">' . $i . '</a>';
  	}
  }

  //마지막 섹션이 아니라면 다음 버튼을 생성
  if($currentSection != $allSection) {
  	$paging .= '<a href="./board_detail.php?board_folder='.$folder.'&board_group='.$group.'&board_idx='.$idx.'&board_status='.$status.'&comm_page=' . $nextPage . '" class="btn btn-sm btn-default">▶</a>';
  }
  //마지막 페이지가 아니라면 끝 버튼을 생성
  if($page != $allPage) {
  	$paging .= '<a href="./board_detail.php?board_folder='.$folder.'&board_group='.$group.'&board_idx='.$idx.'&board_status='.$status.'&comm_page=' . $allPage . '" class="btn btn-sm btn-default">▶▶</a>';
  }
  //$paging .= '</ul>';

  //페이징 끝
  $currentLimit = ($onePage * $page) - $onePage; //몇 번째의 글부터 가져오는지
  $sqlLimit = ' limit ' . $currentLimit . ', ' . $onePage; //limit sql 구문

  try{
  	$pdo = new PDO($dsn, $dbUser, $dbPwd);
    $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
    //$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMORE_EXCEPTION);

  	//board_status가 2 -> 공지사항, 3 -> 게시판 그룹 생성용
    $sql = "SELECT comm_idx, comm_content, comm_wdate, comm_reply,
                      comm_status, fk_board_idx, B.fk_mem_id, mem_nick, mem_img, mem_emblem
            FROM tbl_board A join tbl_comment B
            on A.board_idx = B.fk_board_idx
            join tbl_member C
            on B.fk_mem_id = C.mem_id
            WHERE comm_status = 1 AND fk_board_idx = '" . $idx . "' order by comm_idx ASC" . $sqlLimit;
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

  //이전글, 다음글
  try{
    $pdo = new PDO($dsn, $dbUser, $dbPwd);
    $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
    //$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMORE_EXCEPTION);

    $sql = "SELECT * FROM tbl_board where board_status = 1 AND board_folder = '$folder' AND board_group = '$group' AND board_idx IN (
             (SELECT board_idx from tbl_board where board_idx < '$idx' order by board_idx desc limit 1),
             (SELECT board_idx from tbl_board where board_idx > '$idx' order by board_idx limit 1))";
    $stm = $pdo->prepare($sql);
    $stm->execute();
    $PrevNext = $stm->fetchAll(PDO::FETCH_ASSOC);

    $rowcnt = 1;
    if(empty($PrevNext)){
      $prevBoard_folder = '';
      $prevBoard_group = '';
      $prevBoard_idx = '';
      $prevBoard_status = '';

      $nextBoard_folder = '';
      $nextBoard_group = '';
      $nextBoard_idx = '';
      $nextBoard_status = '';
    } else {
      foreach($PrevNext as $row){
        if($rowcnt == 1){
          $prevBoard_folder = $row['board_folder'];
          $prevBoard_group = $row['board_group'];
          $prevBoard_idx = $row['board_idx'];
          $prevBoard_status = $row['board_status'];

          $rowcnt = $rowcnt + 1;
        } else {
          $nextBoard_folder = $row['board_folder'];
          $nextBoard_group = $row['board_group'];
          $nextBoard_idx = $row['board_idx'];
          $nextBoard_status = $row['board_status'];
        }
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


  //게시물에 해당하는 파일 리스트
  try{
    $pdo = new PDO($dsn, $dbUser, $dbPwd);
    $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
    //$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMORE_EXCEPTION);

    //board_status가 2 -> 공지사항, 3 -> 게시판 그룹 생성용
    $sql = "SELECT * FROM tbl_file WHERE fk_board_idx = '$idx'";
    $stm = $pdo->prepare($sql);
    $stm->execute();
    $fileList = $stm->fetchAll(PDO::FETCH_ASSOC);

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

    th {
      text-align: center;
    }

    td {
      padding: 1%;
    }

    .content {
      width: 60%;
      padding: 20px;
      margin: 0 auto;
      padding: 0 0px 0 0;
    }

    .centerplease {
      margin: 0 auto;
      max-width: 270px;
      font-size: 40px;
    }

    .question {
      position: relative;
      background: #8FBC8F;
      margin: 0;
      padding: 10px 10px 10px 50px;
      display: block;
      width:100%;
      cursor: pointer;
    }

    .answers {
      background: lightgray;
      padding: 0px 20px;
      margin: 5px 0;
      height: 0;
      overflow: hidden;
      /* z-index: -1; */
      position: relative;
      opacity: 0;
      -webkit-transition: .7s ease;
      -moz-transition: .7s ease;
      -o-transition: .7s ease;
      transition: .7s ease;
    }

    .questions:checked ~ .answers{
      height: auto;
      opacity: 1;
      padding: 15px;
    }

    .plus {
      position: absolute;
      margin-left: 10px;
      z-index: 5;
      font-size: 2em;
      line-height: 100%;
      -webkit-user-select: none;
      -moz-user-select: none;
      -ms-user-select: none;
      -o-user-select: none;
      user-select: none;
      -webkit-transition: .3s ease;
      -moz-transition: .3s ease;
      -o-transition: .3s ease;
      transition: .3s ease;
    }

    .questions:checked ~ .plus {
      -webkit-transform: rotate(45deg);
      -moz-transform: rotate(45deg);
      -o-transform: rotate(45deg);
      transform: rotate(45deg);
    }

    .questions {
      display: none;
    }

		.boardArticle {width: 720px;}

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

    $(function() {

    });

    function goRecomm(idx, status, session_id){

      if(session_id.value == "" || session_id.value == null){
        swal("로그인이 필요합니다.");
        return;
      } else {
        var params = {};
        params['board_idx'] = idx;
        params['recomm_status'] = status;

        $.ajax({
             type: 'POST',     // POST 또는 GET
             url: 'board_recomm.php',    // 요청할 url 주소
             data: params,    // 서버에 전달하는 parameters
             success: function (data) {
                  // 정상적으로 완료시 호출
                  var html = "";
                  $('#board_recommDiv1').html(html);
                  $('#board_recommDiv1').html(data);

             },
             error: function (request, status, error) {
                  // 오류 발생시 호출
                  swal("문제 발생! 관리자에게 문의하세요.");
                  return;
             },
             complete: function () {
                  // 실행 완료시 무조건 호출
             }
        });
      }

    }

    function goNonRecomm(idx, status, session_id){

      if(session_id.value == "" || session_id.value == null){
        swal("로그인이 필요합니다.");
        return;
      } else {
        var params = {};
        params['board_idx'] = idx;
        params['recomm_status'] = status;

        $.ajax({
             type: 'POST',     // POST 또는 GET
             url: 'board_recomm.php',    // 요청할 url 주소
             data: params,    // 서버에 전달하는 parameters
             success: function (data) {
                  // 정상적으로 완료시 호출

                  var html = "";
                  $('#board_recommDiv2').html(html);
                  $('#board_recommDiv2').html(data);

             },
             error: function (request, status, error) {
                  // 오류 발생시 호출
                  swal("문제 발생! 관리자에게 문의하세요.");
                  return;
             },
             complete: function () {
                  // 실행 완료시 무조건 호출
             }
        });
      }
    }


    function goComm_Recomm(commidx, status, session_id){

      if(session_id.value == "" || session_id.value == null){
        swal("로그인이 필요합니다.");
        return;
      } else {
        var params = {};
        params['comm_idx'] = commidx;
        params['recomm_status'] = status;

        $.ajax({
             type: 'POST',     // POST 또는 GET
             url: 'comm_recomm.php',    // 요청할 url 주소
             data: params,    // 서버에 전달하는 parameters
             success: function (data) {
                  // 정상적으로 완료시 호출
                  var html = "";
                  $('#comm_recommDiv1').html(html);
                  $('#comm_recommDiv1').html(data);

             },
             error: function (request, status, error) {
                  // 오류 발생시 호출
                  swal("문제 발생! 관리자에게 문의하세요.");
                  return;
             },
             complete: function () {
                  // 실행 완료시 무조건 호출
             }
        });
      }
    }

    function goComm_NonRecomm(commidx, status, session_id){

      if(session_id.value == "" || session_id.value == null){
        swal("로그인이 필요합니다.");
        return;
      } else {
        var params = {};
        params['comm_idx'] = commidx;
        params['recomm_status'] = status;

        $.ajax({
             type: 'POST',     // POST 또는 GET
             url: 'comm_recomm.php',    // 요청할 url 주소
             data: params,    // 서버에 전달하는 parameters
             success: function (data) {
                  // 정상적으로 완료시 호출

                  var html = "";
                  $('#comm_recommDiv2').html(html);
                  $('#comm_recommDiv2').html(data);

             },
             error: function (request, status, error) {
                  // 오류 발생시 호출
                  swal("문제 발생! 관리자에게 문의하세요.");
                  return;
             },
             complete: function () {
                  // 실행 완료시 무조건 호출
             }
        });
      }
    }

		function goBoardDel(idx){
      swal({
			  title: "해당 게시물을 삭제하시겠습니까?",
			  type: "danger",
			  showCancelButton: true,
			  confirmButtonClass: "btn-danger",
			  confirmButtonText: "확인",
			},
			function(isConfirm) {
			  if (isConfirm) {
				 location.href="./board_del.php?board_idx="+idx;
			  }
			});

		}

    function goCommDel(idx){
      swal({
			  title: "해당 댓글을 삭제하시겠습니까?",
			  type: "danger",
			  showCancelButton: true,
			  confirmButtonClass: "btn-danger",
			  confirmButtonText: "확인",
			},
			function(isConfirm) {
			  if (isConfirm) {
				 location.href="./comm_del.php?comm_idx="+idx;
			  }
			});

		}

    function goEditModal(idx, divcnt){
      var comm_idx = idx;
      var comm_content = $("#commDiv_cnt"+divcnt).val();
      var content_area = document.getElementById('comment_contentsEdit');
      var content_idx = document.getElementById('comment_editidx');

      content_area.value = comm_content;
      content_idx.value = comm_idx;

      $("#commEditModal").modal();
    }

    function goPrev(idx, folder, group, status){
      var prevBoard_folder = idx;
      var prevBoard_group = folder;
      var prevBoard_idx = group;
      var prevBoard_status = status;

      if(prevBoard_idx.value == "" || prevBoard_idx.value == null){
        swal("이전글이 존재하지 않습니다.");
        return;
      } else {
        location.href="board_detail.php?board_folder="+prevBoard_folder.value+"&board_group="+prevBoard_group.value+"&board_idx="+prevBoard_idx.value+"&board_status="+prevBoard_status.value+"&board_page=1";
      }
    }

    function goNext(idx, folder, group, status){
      var nextBoard_folder = idx;
      var nextBoard_group = folder;
      var nextBoard_idx = group;
      var nextBoard_status = status;

      if(nextBoard_idx.value == "" || nextBoard_idx.value == null){
        swal("다음글이 존재하지 않습니다.");
        return;
      } else {
        location.href="board_detail.php?board_folder="+nextBoard_folder.value+"&board_group="+nextBoard_group.value+"&board_idx="+nextBoard_idx.value+"&board_status="+nextBoard_status.value+"board_page=1";
      }
    }

    function goDownload(fileidx, fileName){
      location.href = "./download.php?fileidx="+fileidx+"&fileName="+fileName;
    }

    function goBoardNotice(idx, staus, folder, group){
      location.href = "./board_notice.php?board_idx="+idx+"&board_status="+status+"&board_folder="+folder+"&board_group="+group;
    }

    function goTop(){
    	$('html').scrollTop(0);
    }

    function goComment(){
      location.href = "#commentTr";
    }

    function goBottom(){
      $("html").scrollTop($(document).height());
    }
	</script>
</head>
<body>
  <div style="height: 100%; overflow: hidden; margin-left: 10%; margin-right: 10%;">
    <div class="imgtop">
      <a href="notice.php" target="content"><img src="../images/plogo.png" style="width: 150px; height: 150px; margin-top: 25px;"/></a>
    </div>

		<div style="text-align: center;">
      <h1><?php echo $group ?></h1>
    </div>

		<br/>

    <table style="width: 80%; margin-left: 10%; border-top: 1px solid lightgray">
      <tr style="border-bottom: 1px solid lightgray">
        <td style="width: 10%; border-right: 3px solid lightgray; text-align: center; vertical-align: middle;">
          <img src="../files/<?php echo $mem_img ?>" style="width: 150px; height: 150px;" />
        </td>
        <td>
          <span style="font-weight: bolder;"><?php echo $board_title ?></span><br/><br/>
          <?php echo $board_idx ?>&nbsp;／&nbsp;<?php echo $board_folder ?>&nbsp;／&nbsp;<?php echo $board_group ?><br/>
          글쓴이 :
          <?php
            if($mem_id == 'adminpenna'){
              echo '<img src="../images/adminlogo.png" style="width: 25px; height: 25px;"/>운영자&nbsp';
            } else {
              echo '<img src="../images/member_emblem.png" style="width: 25px; height: 25px;"/>' . $mem_id . '&nbsp;';
            }
          ?>(
          <?php
            echo $mem_nick
          ?> )<br/>
          작성날짜 : <?php echo $board_wdate ?>&nbsp;／&nbsp;수정날짜 : <?php echo $board_edate ?><br/>
          조회수 : <?php echo $board_count ?>&nbsp;／&nbsp;
          <?php
            try{
              $pdo = new PDO($dsn, $dbUser, $dbPwd);
              $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
              //$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMORE_EXCEPTION);

              $sql = "SELECT count(*) as recomm1 FROM tbl_board_recomm WHERE board_recomm_status = 1 AND fk_board_idx = " . $idx;
              $stm = $pdo->prepare($sql);
              $stm->execute();
              $recomm = $stm->fetchAll(PDO::FETCH_ASSOC);

              foreach($recomm as $st1){
                $recomm1 = $st1['recomm1'];
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
          추천수 : <?php echo $recomm1 ?>
          <?php
            if(isset($_SESSION['mem_id'])){
              if($mem_id == $_SESSION['mem_id']){
                echo '<br/>';
                echo '<a href="board_edit.php?board_idx='.$idx.'&board_page='.$board_page.'&board_status='.$status.'" class="btn btn-xs btn-info">수정</a>&nbsp;';
                echo '<a href="javascript:goBoardDel('.$idx.')" class="btn btn-xs btn-danger">삭제</a>&nbsp;';
              }
              if($_SESSION['mem_id'] == 'adminpenna'){
                if($status == 1){
                  echo '<a href="javascript:goBoardNotice('.$idx.', '.$status.', '.$folder.', '.$group.');" class="btn btn-xs btn-success">공지지정</a>';
                } else {
                  echo '<a href="javascript:goBoardNotice('.$idx.', '.$status.', '.$folder.', '.$group.');" class="btn btn-xs btn-warning">공지해제</a>';
                }
              }
            }
          ?>
        </td>
      </tr>
      <tr style="border-bottom: 1px solid lightgray">
        <td colspan="2" style="padding-top : 3%; padding-bottom: 3%;">
          <?php echo $board_content ?>
          <br/><br/><br/>
          <div style="text-align: center;">
            <?php
              try{
                $pdo = new PDO($dsn, $dbUser, $dbPwd);
                $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
                //$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMORE_EXCEPTION);

                $sql = "SELECT count(*) as recomm2 FROM tbl_board_recomm WHERE board_recomm_status = 2 AND fk_board_idx = " . $idx;
                $stm = $pdo->prepare($sql);
                $stm->execute();
                $recomm = $stm->fetchAll(PDO::FETCH_ASSOC);

                foreach($recomm as $st2){
                  $recomm2 = $st2['recomm2'];
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
            <a href="javascript:goRecomm('<?php echo $idx ?>', '1', '<?php echo $session_id ?>');" class="btn btn-primary btn-default"><span class="glyphicon glyphicon-thumbs-up"></span> <div id="board_recommDiv1"><?php echo $recomm1 ?></div></a>
            <a href="javascript:goNonRecomm('<?php echo $idx ?>', '2', '<?php echo $session_id ?>');" class="btn btn-primary btn-danger"><span class="glyphicon glyphicon-thumbs-down"></span> <div id="board_recommDiv2"><?php echo $recomm2 ?></div></a>
          </div>
        </td>
      </tr>
      <tr style="border-bottom: 1px solid lightgray;">
        <td style="width: 10%; text-align: center;">
          <span style="font-weight: bold;">파일첨부</span>
        </td>
        <td id="filelistTd">
          <?php
            if(!empty($fileList)){
              foreach ($fileList as $filerow) {
              ?>
                <a href="javascript:goDownload('<?php echo $filerow['file_idx']; ?>', '<?php echo $filerow['file_newname'] ?>');">
                  <?php echo $filerow['file_oldname'] ?>&nbsp;<span style="font-weight: bold">(<?php echo $filerow['file_count'] ?>)</span>
                </a>&nbsp;&nbsp;
              <?php
              }
            }
          ?>
        </td>
      </tr>
      <tr style="border-bottom: 1px solid lightgray">
        <td colspan="2" style="padding-top : 1%; padding-bottom: 1%;">
          <div style="float: left;">
            <a href="javascript:goPrev('<?php echo $prevBoard_idx; ?>', '<?php echo $prevBoard_folder; ?>', '<?php echo $prevBoard_group; ?>');" class="btn btn-sm btn-default"><span class="glyphicon glyphicon-chevron-down"></span> 이전글</a>
            <a href="javascript:goNext('<?php echo $nextBoard_idx; ?>', '<?php echo $nextBoard_folder; ?>', '<?php echo $nextBoard_group; ?>');" class="btn btn-sm btn-default"><span class="glyphicon glyphicon-chevron-up"></span> 다음글</a>
          </div>
          <div style="float: right;">
            <a href="./board_write.php?board_folder=<?php echo $folder ?>&board_group=<?php echo $group ?>" target="content" class="btn btn-sm btn-primary"><span class="glyphicon glyphicon-pencil"></span> 글쓰기</a>
            <a href="./board.php?board_folder=<?php echo $folder ?>&board_group=<?php echo $group ?>&board_idx=<?php echo $idx ?>&board_page=<?php echo $board_page ?>" class="btn btn-sm btn-default"><span class="glyphicon glyphicon-list"></span> 목록보기</a>
          </div>
        </td>
      </tr>
      <?php
        if(empty($result)){
      ?>
      <tr id="commentTr" style="border-bottom: 1px solid lightgray">
        <td colspan="2" style="padding-top : 5%; padding-bottom: 5%; text-align: center; vertical-align: middle;">
          <span style="font-weight: bold">댓글이 존재하지 않습니다.</span>
        </td>
      </tr>
      <?php
        } else {
          foreach($result as $row){
      ?>
      <tr id="commentTr" style="border-bottom: 1px solid lightgray">
        <?php
          if($row['comm_status'] == 0){
        ?>
          <td colspan="2" style="padding: 1%; background-color: lightgray; text-align: center; vertical-align: middle;">
            <br/><br/>삭제된 댓글입니다.<br/><br/>
          </td>
        <?php
          } else {
        ?>
          <td style="width: 10%; border-right: 3px solid lightgray; text-align: center; padding: 1%;">
            <img src="../files/<?php echo $row['mem_img'] ?>" style="width: 100px; height: 100px;" /><br/>
            <?php
              if($mem_id == 'adminpenna'){
                echo '<img src="../images/adminlogo.png" style="width: 25px; height: 25px;"/>';
                echo '<span style="font-weight: bold">운영자<br/>('.$row['mem_nick'].')</span>';
              } else {
                echo '<img src="../images/member_emblem.png" style="width: 25px; height: 25px;"/>' . $mem_id . '&nbsp;';
                echo '<span style="font-weight: bold">'.$row['fk_mem_id'].'<br/>('.$row['mem_nick'].')</span>';
              }
            ?>
          </td>
          <td style="padding: 1%;">
            <div style="float: left; width: 80%;">
              <?php
                $reply = $row['comm_reply'];
                if($reply != null || $reply != ""){
                  echo "<span style='font-weight: bold;'>→&nbsp;".$row['comm_reply']."</span><br/>";
                }
                $commDiv_cnt = 1;
                echo $row['comm_content'];
              ?>
              <input type="hidden" id="commDiv_cnt<?php echo $commDiv_cnt; ?>" name="commDiv_cnt<?php echo $commDiv_cnt; ?>" value="<?php echo $row['comm_content']; ?>" />
            </div>
            <div style="float: right; width: 20%; text-align: center;">
              <?php echo $row['comm_wdate']; ?><br/><br/>
              <?php
                $comm_idx = $row['comm_idx'];
                $comm_content = $row['comm_content'];
                try{
                  $pdo = new PDO($dsn, $dbUser, $dbPwd);
                  $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
                  //$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMORE_EXCEPTION);

                  $sql = "SELECT count(*) as recomm1 FROM tbl_comment_recomm WHERE comment_recomm_status = 1 AND fk_comm_idx = " . $comm_idx;
                  $stm = $pdo->prepare($sql);
                  $stm->execute();
                  $comm_recomm = $stm->fetchAll(PDO::FETCH_ASSOC);

                  foreach($comm_recomm as $commst1){
                    $comm_recomm1 = $commst1['recomm1'];
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
                try{
                  $pdo = new PDO($dsn, $dbUser, $dbPwd);
                  $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
                  //$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMORE_EXCEPTION);

                  $sql = "SELECT count(*) as recomm2 FROM tbl_comment_recomm WHERE comment_recomm_status = 2 AND fk_comm_idx = " . $comm_idx;
                  $stm = $pdo->prepare($sql);
                  $stm->execute();
                  $comm_recomm = $stm->fetchAll(PDO::FETCH_ASSOC);

                  foreach($comm_recomm as $commst2){
                    $comm_recomm2 = $commst2['recomm2'];
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
              <a href="javascript:goComm_Recomm('<?php echo $comm_idx ?>', '1', '<?php echo $session_id ?>');" class="btn btn-xs btn-primary"><span class="glyphicon glyphicon-thumbs-up"></span> <div id="comm_recommDiv1"><?php echo $comm_recomm1 ?></div></a>
              <a href="javascript:goComm_NonRecomm('<?php echo $comm_idx ?>', '2', '<?php echo $session_id ?>');" class="btn btn-xs btn-danger"><span class="glyphicon glyphicon-thumbs-down"></span> <div id="comm_recommDiv2"><?php echo $comm_recomm2 ?></div></a><br/>
              <?php
                if(isset($_SESSION['mem_id'])){
                  if($row['fk_mem_id'] == $_SESSION['mem_id']){
                    echo '<br/>';
                    // echo '<a href="comm_edit.php?board_idx='.$comm_idx.'&board_page='.$board_page.'" class="btn btn-xs btn-info">수정</a>&nbsp;';
                    echo '<a href="javascript:goEditModal('.$comm_idx.', '.$commDiv_cnt.')" class="btn btn-xs btn-info">수정</a>&nbsp;';
                    echo '<a href="javascript:goCommDel('.$comm_idx.')" class="btn btn-xs btn-danger">삭제</a>&nbsp;';
                  } else if($_SESSION['mem_id'] == 'adminpenna'){
                    echo '<br/>';
                    echo '<a href="javascript:goCommDel('.$comm_idx.')" class="btn btn-xs btn-danger">삭제</a>&nbsp;';
                  }
                }
                $commDiv_cnt = $commDiv_cnt + 1;
              ?>
            </div>
          </td>
        <?php
          }
        ?>
      </tr>
      <?php
        }
      ?>
      <tr>
        <td colspan="2" style="border-bottom: 1px solid lightgray">
          <div class="paging" style="text-align: center;">
            <?php echo $paging ?>
          </div>
        </td>
      </tr>
      <?php
      }
      ?>
      <tr style="border-bottom: 1px solid lightgray">
        <td style="width: 10%; border-right: 3px solid lightgray; text-align: center; padding: 1%;">
          <?php
            if(isset($_SESSION['mem_id'])){
              echo '<img src="../files/' . $_SESSION['mem_img'] . '" style="width: 100px; height: 100px;" />';
            } else {
              echo '';
            }
          ?>
        </td>
        <td style="padding: 1%;">
          <form class="form-inline" method="post" action="comm_ok.php">
            <div style="width: 80%; float: left;">
              <?php
                if(isset($_SESSION['mem_id'])){
                  echo '<textarea class="form-control" id="comment_contents" name="comment_contents" style="width: 100%; height: 100px;" placeholder="타인에게 피해가 갈 수 있는 댓글은 자제해 주시기 바랍니다."></textarea>';
                } else {
                  echo '<textarea class="form-control" id="comment_contents" name="comment_contents" style="width: 100%; height: 100px;" placeholder="로그인이 필요합니다." readonly></textarea>';
                }
              ?>
            </div>
            <div style="width: 20%; float: right; text-align: center; padding-top: 2%;">
              <button type="submit" class="btn btn-lg btn-success">등록</button>
            </div>
            <input type="hidden" id="board_folder" name="board_folder" value="<?php echo $folder ?>" />
            <input type="hidden" id="board_group" name="board_group" value="<?php echo $group ?>" />
            <input type="hidden" id="board_idx" name="board_idx" value="<?php echo $idx ?>" />
            <input type="hidden" id="board_page" name="board_page" value="<?php echo $board_page ?>" />
          </form>
        </td>
      </tr>
      <tr style="border-bottom: 1px solid lightgray">
        <td colspan="2" style="padding-top : 1%; padding-bottom: 1%;">
          <div style="float: left;">
            <a href="javascript:goPrev('<?php echo $prevBoard_idx ?>', '<?php echo $prevBoard_folder ?>', '<?php echo $prevBoard_group ?>');" class="btn btn-sm btn-default"><span class="glyphicon glyphicon-chevron-down"></span> 이전글</a>
            <a href="javascript:goNext('<?php echo $nextBoard_idx ?>', '<?php echo $nextBoard_folder ?>', '<?php echo $nextBoard_group ?>');" class="btn btn-sm btn-default"><span class="glyphicon glyphicon-chevron-up"></span> 다음글</a>
          </div>
          <div style="float: right;">
            <a href="./board_write.php?board_folder=<?php echo $folder ?>&board_group=<?php echo $group ?>" target="content" class="btn btn-sm btn-primary"><span class="glyphicon glyphicon-pencil"></span> 글쓰기</a>
            <a href="./board.php?board_folder=<?php echo $folder ?>&board_group=<?php echo $group ?>&board_idx=<?php echo $idx ?>&board_page=<?php echo $board_page ?>" class="btn btn-sm btn-default"><span class="glyphicon glyphicon-list"></span> 목록보기</a>
          </div>
        </td>
      </tr>
    </table>

    <br/><br/><br/>

    <div id="footer">
      &nbsp;&nbsp;
      <button type="button" onclick="goTop();" class="btn btn-primary btn-default"><span class="glyphicon glyphicon-arrow-up"></span> </button>
      <button type="button" onclick="goComment();" class="btn btn-primary btn-default"><span class="glyphicon glyphicon-comment"></span> </button>
      <button type="button" onclick="goBottom();" href="#" class="btn btn-primary btn-default"><span class="glyphicon glyphicon-arrow-down"></span> </button>
    </div>

  </div>
</body>
</html>


<!-- 댓글 수정용 Modal -->
<div class="modal fade" id="commEditModal" role="dialog">
  <div class="modal-dialog">

    <!-- Modal content-->
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal">×</button>
        <h4 class="modal-title">댓글 수정</h4>
      </div>
      <div class="modal-body" style="text-align: center;">
        <form class="form-inline" method="post" action="comm_edit_ok.php">
          <textarea class="form-control" id="comment_contentsEdit" name="comment_contentsEdit" style="width: 100%; height: 100px;" placeholder="타인에게 피해가 갈 수 있는 댓글은 자제해 주시기 바랍니다."></textarea>
          <br/>
          <input type="hidden" id="comment_editidx" name="comment_editidx" />
          <button type="submit" class="btn btn-sm btn-success" id="comment_editer" name="comment_editer">수정</button>
        </form>
      </div>
      <div class="modal-footer">
        <!-- <button type="button" class="btn btn-default" data-dismiss="modal">Close</button> -->
      </div>
    </div>

  </div>
</div>
