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

  //session_start();


  if(isset($_GET['searchFolder']) && isset($_GET['searchGroup']) && isset($_GET['searchSubject']) && isset($_GET['searchString'])){
    $folder = $_GET['searchFolder'];
    $group = $_GET['searchGroup'];
    $subject = $_GET['searchSubject'];
    $searchString = $_GET['searchString'];
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

  /* 페이징 시작 */

  //페이지 get 변수가 있다면 받아오고, 없다면 1페이지를 보여준다.

  if(isset($_GET['board_page'])) {
  	$page = $_GET['board_page'];
  } else {
  	$page = 1;
  }

  try{
  	$pdo = new PDO($dsn, $dbUser, $dbPwd);
    $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
    //$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMORE_EXCEPTION);

  	//board_status가 2 -> 공지사항, 3 -> 게시판 그룹 생성용
    switch ($subject) {
      case '제목':
        $sql = "SELECT count(*) as cnt
                FROM tbl_board
                WHERE board_status = 1
                AND board_folder = '$folder'
                AND board_group = '$group'
                AND board_title LIKE '%$searchString%'
                ORDER BY board_idx DESC";
        break;
      case '내용':
        $sql = "SELECT count(*) as cnt
                FROM tbl_board
                WHERE board_status = 1
                AND board_folder = '$folder'
                AND board_group = '$group'
                AND board_content LIKE '%$searchString%'
                ORDER BY board_idx DESC";
        break;
      case '제목+내용':
        $sql = "SELECT *
                FROM tbl_board
                WHERE board_status = 1
                AND board_folder = '$folder'
                AND board_group = '$group'
                AND (board_title LIKE '%$searchString%' or board_content LIKE '%$searchString%')
                ORDER BY board_idx DESC";
        break;
      case '말머리':
        $sql = "SELECT count(*) as cnt
                FROM tbl_board
                WHERE board_status = 1
                AND board_folder = '$folder'
                AND board_group = '$group'
                AND board_subject LIKE '%$searchString%'
                ORDER BY board_idx DESC";
        break;
      case '아이디':
        $sql = "SELECT count(*) as cnt
                FROM tbl_board join tbl_member
                on tbl_board.fk_mem_id = tbl_member.mem_id
                WHERE board_status = 1
                AND board_folder = '$folder'
                AND board_group = '$group'
                AND mem_id LIKE '%$searchString%'
                ORDER BY board_idx DESC";
        break;
      case '닉네임':
        $sql = "SELECT count(*) as cnt
                FROM tbl_board join tbl_member
                on tbl_board.fk_mem_id = tbl_member.mem_id
                WHERE board_status = 1
                AND board_folder = '$folder'
                AND board_group = '$group'
                AND mem_nick LIKE '%$searchString%'
                ORDER BY board_idx DESC";
        break;
      default:
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
        break;
    }
    $sql = "SELECT count(*) as cnt FROM tbl_board WHERE board_status = 1 AND board_folder = '" . $folder . "' AND board_group = '" . $group . "' ORDER BY board_idx DESC";
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
  	$paging .= '<a href="./board_search.php?board_folder='.$folder.'&board_group='.$group.'&page=1" class="btn btn-default">◀◀</a>';
  }
  //첫 섹션이 아니라면 이전 버튼을 생성
  if($currentSection != 1) {
  	$paging .= '<a href="./board_search.php?board_folder='.$folder.'&board_group='.$group.'&page=' . $prevPage . '" class="btn btn-default">◀</a>';
  }

  for($i = $firstPage; $i <= $lastPage; $i++) {
  	if($i == $page) {
  		$paging .= '<a href="#" class="btn btn-primary">' . $i . '</a>';
  	} else {
  		$paging .= '<a href="./board_search.php?board_folder='.$folder.'&board_group='.$group.'&page=' . $i . '" class="btn btn-default">' . $i . '</a>';
  	}
  }

  //마지막 섹션이 아니라면 다음 버튼을 생성
  if($currentSection != $allSection) {
  	$paging .= '<a href="./board_search.php?board_folder='.$folder.'&board_group='.$group.'&page=' . $nextPage . '" class="btn btn-default">▶</a>';
  }
  //마지막 페이지가 아니라면 끝 버튼을 생성
  if($page != $allPage) {
  	$paging .= '<a href="./board_search.php?board_folder='.$folder.'&board_group='.$group.'&page=' . $allPage . '" class="btn btn-default">▶▶</a>';
  }
  //$paging .= '</ul>';

  /* 페이징 끝 */
  $currentLimit = ($onePage * $page) - $onePage; //몇 번째의 글부터 가져오는지
  $sqlLimit = ' limit ' . $currentLimit . ', ' . $onePage; //limit sql 구문

  try{
  	$pdo = new PDO($dsn, $dbUser, $dbPwd);
    $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
    //$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMORE_EXCEPTION);

    switch ($subject) {
      case '제목':
        $sql = "SELECT *
                FROM tbl_board
                WHERE board_status = 1
                AND board_folder = '$folder'
                AND board_group = '$group'
                AND board_title LIKE '%$searchString%'
                ORDER BY board_idx DESC" . $sqlLimit;
        break;
      case '내용':
        $sql = "SELECT *
                FROM tbl_board
                WHERE board_status = 1
                AND board_folder = '$folder'
                AND board_group = '$group'
                AND board_content LIKE '%$searchString%'
                ORDER BY board_idx DESC" . $sqlLimit;
        break;
      case '제목+내용':
        $sql = "SELECT *
                FROM tbl_board
                WHERE board_status = 1
                AND board_folder = '$folder'
                AND board_group = '$group'
                AND (board_title LIKE '%$searchString%' or board_content LIKE '%$searchString%')
                ORDER BY board_idx DESC" . $sqlLimit;
        break;
      case '말머리':
        $sql = "SELECT *
                FROM tbl_board
                WHERE board_status = 1
                AND board_folder = '$folder'
                AND board_group = '$group'
                AND board_subject LIKE '%$searchString%'
                ORDER BY board_idx DESC" . $sqlLimit;
        break;
      case '아이디':
        $sql = "SELECT *
                FROM tbl_board join tbl_member
                on tbl_board.fk_mem_id = tbl_member.mem_id
                WHERE board_status = 1
                AND board_folder = '$folder'
                AND board_group = '$group'
                AND mem_id LIKE '%$searchString%'
                ORDER BY board_idx DESC" . $sqlLimit;
        break;
      case '닉네임':
        $sql = "SELECT *
                FROM tbl_board join tbl_member
                on tbl_board.fk_mem_id = tbl_member.mem_id
                WHERE board_status = 1
                AND board_folder = '$folder'
                AND board_group = '$group'
                AND mem_nick LIKE '%$searchString%'
                ORDER BY board_idx DESC" . $sqlLimit;
        break;
      default:
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

    th {
      text-align: center;
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

    a:link { color: white; text-decoration: none;}
    a:hover { color: darkblue; text-decoration: underline;}

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

  </style>
	<script type="text/javascript">
		function goDel(idx){
      swal({
			  title: "해당 게시물을 삭제하시겠습니까?",
			  type: "danger",
			  showCancelButton: true,
			  confirmButtonClass: "btn-danger",
			  confirmButtonText: "확인",
			},
			function(isConfirm) {
			  if (isConfirm) {
				 location.href="./board_del.php?main_idx="+idx;
			  }
			});

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


    <table class="table table-hover" style="width: 80%; margin-left: 10%;">
      <thead style="border-top: 1px solid gray; background-color: #F2F2F2;">
        <?php
          if(!empty($result)){
        ?>
            <tr>
              <th width="10%">말머리</th>
              <th width="50%">제목</th>
              <th width="20%">작성자</th>
              <th width="10%">작성일</th>
              <th width="5%">추천</th>
              <th width="5%">조회</th>
            </tr>
        <?php
          }
        ?>
      </thead>
      <tbody style="border-bottom: 1px solid gray;">
        <?php
        $board_no = 1;
        if(empty($result)){
        ?>
          </tbody>
        </table>
          <div class="list-group" style="width: 80%; text-align: center; border: 1px solid lightgray; padding: 5%; margin-left: 10%;">
              <br/><br/><br/>
              <span class="glyphicon glyphicon-remove-circle"></span>
              &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;게시물이 존재하지 않습니다.&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
              <span class="glyphicon glyphicon-remove-sign"></span>
              <br/><br/><br/>
          </div>
          <br/><br/>
          <div style="text-align: center;">
      			<div style="float: left; margin-left: 10%;">
      				<?php
      					session_start();
      					if(isset($_SESSION['mem_id'])){
      						echo '<a href="./board_write.php?board_folder='.$folder.'&board_group='.$group.'" target="content" class="btn btn-sm btn-primary"><span class="glyphicon glyphicon-pencil"></span> 글쓰기</a>';
      					}
      				?>
      			</div>
      			<div style="float: right; margin-right: 10%;">
      				<form class="form-inline" method="get" action="board_search.php">
      					<select id="searchSubject" name="searchSubject" class="form-control">
                  <?php
                    switch ($subject) {
                      case '제목':
                        $selected1 = "selected";
                        $selected2 = "";
                        $selected3 = "";
                        $selected4 = "";
                        $selected5 = "";
                        $selected6 = "";
                        break;
                      case '내용':
                        $selected1 = "";
                        $selected2 = "selected";
                        $selected3 = "";
                        $selected4 = "";
                        $selected5 = "";
                        $selected6 = "";
                        break;
                      case '제목+내용':
                        $selected1 = "";
                        $selected2 = "";
                        $selected3 = "selected";
                        $selected4 = "";
                        $selected5 = "";
                        $selected6 = "";
                        break;
                      case '아이디':
                        $selected1 = "";
                        $selected2 = "";
                        $selected3 = "";
                        $selected4 = "selected";
                        $selected5 = "";
                        $selected6 = "";
                        break;
                      case '닉네임':
                        $selected1 = "";
                        $selected2 = "";
                        $selected3 = "";
                        $selected4 = "";
                        $selected5 = "selected";
                        $selected6 = "";
                        break;
                      case '말머리':
                        $selected1 = "";
                        $selected2 = "";
                        $selected3 = "";
                        $selected4 = "";
                        $selected5 = "";
                        $selected6 = "selected";
                        break;
                    }
                  ?>
      						<option value="제목" <?php echo $selected1 ?>>제목</option>
      						<option value="내용" <?php echo $selected2 ?>>내용</option>
                  <option value="제목+내용" <?php echo $selected3 ?>>제목+내용</option>
      						<option value="아이디" <?php echo $selected4 ?>>아이디</option>
                  <option value="닉네임" <?php echo $selected5 ?>>닉네임</option>
                  <option value="닉네임" <?php echo $selected6 ?>>말머리</option>
      					</select>
      					<input type="text" class="form-control" id="searchString" name="searchString" maxlength="20" minlength="2" style="width: 200px;" value="<?php echo $searchString ?>" />
      					<button type="submit" class="btn btn-default"><span class="glyphicon glyphicon-search"></span></button>
                <input type="hidden" id="searchFolder" name="searchFolder" value="<?php echo $folder ?>" />
                <input type="hidden" id="searchGroup" name="searchGroup" value="<?php echo $group ?>" />
      				</form>
      			</div>
      		</div>
        <?php
        } else {
          foreach($result as $row){
        ?>
          <tr>
            <td style="text-align: center; vertical-align: middle;"><span style="font-weight: bold; font-family: 맑은 고딕; font-size: 10pt;"><?php echo $row['board_subject'] ?></span></td>
            <td style="vertical-align: middle;">
              <a href="./board_detail.php?board_folder=<?php echo $folder ?>&board_group=<?php echo $group ?>&board_idx=<?php echo $row['board_idx'] ?>&board_page=<?php echo $page ?>" target="content"><?php echo $row['board_title'] ?></a>
            </td>
            <td style="text-align: center; vertical-align: middle;">
							<?php
								if($row['fk_mem_id'] == 'adminpenna'){
									echo '<img src="../images/adminlogo.png" style="width: 25px; height: 25px;"/>운영자';
								} else {
									echo '<img src="../images/member_emblem.png" style="width: 25px; height: 25px;"/>' . $row['fk_mem_id'];
								}
							?>
						</td>
            <td style="text-align: center; vertical-align: middle;">
							<?php
								//$date = date("Y-m-d");
								echo $row['board_wdate'];
							?>
						</td>
            <td style="text-align: center; vertical-align: middle;">
              <?php
                //추천 수 조회
                $board_idx = $row['board_idx'];
                try{
                  $pdo = new PDO($dsn, $dbUser, $dbPwd);
                  $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
                  //$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMORE_EXCEPTION);

                  $sql = "SELECT count(*) as recomm1 FROM tbl_board_recomm WHERE board_recomm_status = 1 AND fk_board_idx = " . $board_idx;
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
                echo $recomm1;
              ?>
            </td>
            <td style="text-align: center; vertical-align: middle;"><?php echo $row['board_count']; ?></td>
          </tr>
        <?php
          $board_no = $board_no + 1;
          }
        ?>
      </tbody>
    </table>

    <div class="paging" style="text-align: center;">
      <?php echo $paging ?>
    </div>

		<br/>

		<div style="text-align: center;">
			<div style="float: left; margin-left: 10%;">
				<?php
					session_start();
					if(isset($_SESSION['mem_id'])){
						echo '<a href="./board_write.php?board_folder='.$folder.'&board_group='.$group.'" target="content" class="btn btn-sm btn-primary"><span class="glyphicon glyphicon-pencil"></span> 글쓰기</a>';
					}
				?>
			</div>
			<div style="float: right; margin-right: 10%;">
				<form class="form-inline" method="get" action="board_search.php">
					<select id="searchSubject" name="searchSubject" class="form-control">
            <?php
              switch ($subject) {
                case '제목':
                  $selected1 = "selected";
                  $selected2 = "";
                  $selected3 = "";
                  $selected4 = "";
                  $selected5 = "";
                  $selected6 = "";
                  break;
                case '내용':
                  $selected1 = "";
                  $selected2 = "selected";
                  $selected3 = "";
                  $selected4 = "";
                  $selected5 = "";
                  $selected6 = "";
                  break;
                case '제목+내용':
                  $selected1 = "";
                  $selected2 = "";
                  $selected3 = "selected";
                  $selected4 = "";
                  $selected5 = "";
                  $selected6 = "";
                  break;
                case '아이디':
                  $selected1 = "";
                  $selected2 = "";
                  $selected3 = "";
                  $selected4 = "selected";
                  $selected5 = "";
                  $selected6 = "";
                  break;
                case '닉네임':
                  $selected1 = "";
                  $selected2 = "";
                  $selected3 = "";
                  $selected4 = "";
                  $selected5 = "selected";
                  $selected6 = "";
                  break;
                case '말머리':
                  $selected1 = "";
                  $selected2 = "";
                  $selected3 = "";
                  $selected4 = "";
                  $selected5 = "";
                  $selected6 = "selected";
                  break;
              }
            ?>
            <option value="제목" <?php echo $selected1 ?>>제목</option>
            <option value="내용" <?php echo $selected2 ?>>내용</option>
            <option value="제목+내용" <?php echo $selected3 ?>>제목+내용</option>
            <option value="아이디" <?php echo $selected4 ?>>아이디</option>
            <option value="닉네임" <?php echo $selected5 ?>>닉네임</option>
            <option value="말머리" <?php echo $selected6 ?>>말머리</option>
					</select>
					<input type="text" class="form-control" id="searchString" name="searchString" maxlength="20" minlength="2" style="width: 200px;" value="<?php echo $searchString ?>" />
					<button type="submit" class="btn btn-default"><span class="glyphicon glyphicon-search"></span></button>
          <input type="hidden" id="searchFolder" name="searchFolder" value="<?php echo $folder ?>" />
          <input type="hidden" id="searchGroup" name="searchGroup" value="<?php echo $group ?>" />
				</form>
			</div>
		</div>

    <div id="footer">
      &nbsp;&nbsp;
      <button type="button" onclick="goTop();" class="btn btn-primary btn-default"><span class="glyphicon glyphicon-arrow-up"></span> </button>
      <button type="button" onclick="goBottom();" href="#" class="btn btn-primary btn-default"><span class="glyphicon glyphicon-arrow-down"></span> </button>
    </div>

    <?php
      }
    ?>

		<br/><br/>

		<br/><br/>



  </div>
</body>
</html>
