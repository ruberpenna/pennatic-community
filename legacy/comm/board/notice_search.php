<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8" />
	<title>Penna - Notice</title>
	<link rel="stylesheet" type="text/css" href="../bootstrap/css/bootstrap.css">
	<link rel="stylesheet" type="text/css" href="https://cdnjs.cloudflare.com/ajax/libs/sweetalert/1.1.3/sweetalert.min.css" />
	<link rel="stylesheet" href="../css/normalize.css" />
	<link rel="stylesheet" href="../css/board.css" />
	<script type="text/javascript" src="../js/jquery-2.1.3.min.js"></script>
	<script type="text/javascript" src="../bootstrap/js/bootstrap.js"></script>
	<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/sweetalert/1.1.3/sweetalert.min.js"></script>
	<?php
	include("../dbconfig.php");
	error_reporting(E_ALL);
	ini_set("display_errors", 1);

	//session_start();

	/* 페이징 시작 */

	//페이지 get 변수가 있다면 받아오고, 없다면 1페이지를 보여준다.

	$subject = $_GET['subject'];
	$searchString = $_GET['searchString'];

	if(isset($_GET['page'])) {
		$page = $_GET['page'];
	} else {
		$page = 1;
	}

	try{
		$pdo = new PDO($dsn, $dbUser, $dbPwd);
	  $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
	  //$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMORE_EXCEPTION);

		//$sql = "SELECT * FROM tbl_main ORDER BY main_idx DESC LIMIT $no, $page_size";
	  if($subject == "제목"){
	    $sql = "SELECT count(*) as cnt FROM tbl_main WHERE main_status = 1 AND main_title LIKE '%$searchString%' ORDER BY main_idx DESC";
	  } else if ($subject == "내용") {
	    $sql = "SELECT count(*) as cnt FROM tbl_main WHERE main_status = 1 AND main_content LIKE '%$searchString%'  ORDER BY main_idx DESC";
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
		$paging .= '<a href="./notice_search.php?page=1" class="btn btn-default">◀◀</a>';
	}
	//첫 섹션이 아니라면 이전 버튼을 생성
	if($currentSection != 1) {
		$paging .= '<a href="./notice_search.php?page=' . $prevPage . '" class="btn btn-default">◀</a>';
	}

	for($i = $firstPage; $i <= $lastPage; $i++) {
		if($i == $page) {
			$paging .= '<a href="#" class="btn btn-primary">' . $i . '</a>';
		} else {
			$paging .= '<a href="./notice_search.php?page=' . $i . '" class="btn btn-default">' . $i . '</a>';
		}
	}

	//마지막 섹션이 아니라면 다음 버튼을 생성
	if($currentSection != $allSection) {
		$paging .= '<a href="./notice_search.php?page=' . $nextPage . '" class="btn btn-default">▶</a>';
	}
	//마지막 페이지가 아니라면 끝 버튼을 생성
	if($page != $allPage) {
		$paging .= '<a href="./notice_search.php?page=' . $allPage . '" class="btn btn-default">▶▶</a>';
	}
	//$paging .= '</ul>';

	/* 페이징 끝 */
	$currentLimit = ($onePage * $page) - $onePage; //몇 번째의 글부터 가져오는지
	$sqlLimit = ' limit ' . $currentLimit . ', ' . $onePage; //limit sql 구문

	try{
		$pdo = new PDO($dsn, $dbUser, $dbPwd);
	  $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
	  //$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMORE_EXCEPTION);

	  if($subject == "제목"){
	    $sql = "SELECT * FROM tbl_main where main_status = 1 AND main_title LIKE '%$searchString%' order by main_idx desc" . $sqlLimit;
	  } else if ($subject == "내용") {
	    $sql = "SELECT * FROM tbl_main where main_status = 1 AND main_content LIKE '%$searchString%' order by main_idx desc" . $sqlLimit;
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
				 location.href="./notice_del.php?main_idx="+idx;
			  }
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
      <h1>공지사항</h1>
    </div>

		<br/>

		<?php
			session_start();
			if(isset($_SESSION['mem_status'])){
				$status = $_SESSION['mem_status'];
				if($status == '2'){
					echo '<div style="text-align: left;" class="content">';
					echo '<a href="./notice_write.php" target="content" class="btn btn-sm btn-primary"><span class="glyphicon glyphicon-pencil"></span> 글쓰기</a>';
					echo '</div>';
				}
			}

		?>

		<br/>

		<?php
		$notice_no = 1;
		foreach($result as $row){
		?>

    <div class="content">
      <div>
        <input type="checkbox" id="notices<?php echo $notice_no ?>" name="n" class="questions">
        <div class="plus">+</div>
        <label for="notices<?php echo $notice_no ?>" class="question">
          <?php echo $row['main_title'] ?>
        </label>
        <div class="answers">
					<div>
						<span style="color: gray; font-size: 10pt;">작성일자 : <?php echo $row['main_date'] ?></span><br/>
	          <?php echo $row['main_content'] ?>
					</div>
					<?php
						if(isset($_SESSION['mem_status'])){
							$status = $_SESSION['mem_status'];
							$main_idx = $row['main_idx'];
							if($status == '2'){
								echo '<br/>';
								echo '<div style="float: right;">';
								echo '<a href="./notice_edit.php?main_idx=' . $main_idx . '" class="btn btn-xs btn-info"><span class="glyphicon glyphicon-edit"></span> 수정</a>';
								echo '<a href="javascript:goDel(' . $main_idx .')" class="btn btn-xs btn-danger"><span class="glyphicon glyphicon-remove"></span> 삭제</a>';
								echo '</div>';
							}
						}
					?>
        </div>
      </div>
    </div>

		<?php
			$notice_no = $notice_no + 1;
		}
		?>

		<br/>

		<div class="paging" style="text-align: center;">
			<?php echo $paging ?>
		</div>

		<br/>

		<div style="text-align: center;">
			<form class="form-inline" method="get" action="notice_search.php">
        <?php
          if($subject == "제목"){
            $selected1 = "selected";
            $selected2 = "";
          } else if ($subject == "내용") {
            $selected2 = "selected";
            $selected1 = "";
          }
        ?>
        <select id="subject" name="subject" class="form-control">
					<option value="제목" <?php echo $selected1 ?>>제목</option>
					<option value="내용" <?php echo $selected2 ?>>내용</option>
				</select>
				<input type="text" class="form-control" id="searchString" name="searchString" maxlength="20" minlength="2" style="width: 200px;" value="<?php echo $searchString ?>" />
				<button type="submit" class="btn btn-default"><span class="glyphicon glyphicon-search"></span></button>
			</form>
		</div>

    <br/><br/>

  </div>
</body>
</html>
