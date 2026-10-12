<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8" />
	<title>Penna - Attendance</title>
  <link rel="stylesheet" type="text/css" href="../bootstrap/css/bootstrap.css">
  <link rel="stylesheet" type="text/css" href="https://cdnjs.cloudflare.com/ajax/libs/sweetalert/1.1.3/sweetalert.min.css" />
  <link href="../css/calendar.css" rel="stylesheet">
  <script type="text/javascript" src="../js/jquery-2.1.3.min.js"></script>
  <script type="text/javascript" src="../bootstrap/js/bootstrap.js"></script>
  <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/sweetalert/1.1.3/sweetalert.min.js"></script>
  <script src="../js/calendar.js"></script>
  <?php
  include("../dbconfig.php");
  error_reporting(E_ALL);
  ini_set("display_errors", 1);

  session_start();
  if(isset($_SESSION['mem_id'])){
    $mem_id = $_SESSION['mem_id'];
  } else {
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
    exit();
  }
  // =====================================================================
  //페이지 get 변수가 있다면 받아오고, 없다면 1페이지를 보여준다.
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
  	$sql = "SELECT count(*) as cnt FROM tbl_attend WHERE att_status = 1 AND fk_mem_id = '$mem_id' ORDER BY att_idx DESC";
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
  $onePage = 5; // 한 페이지에 보여줄 게시글의 수.
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
  	$paging .= '<a href="./attendance.php?page=1" class="btn btn-xs btn-default">◀◀</a>';
  }
  //첫 섹션이 아니라면 이전 버튼을 생성
  if($currentSection != 1) {
  	$paging .= '<a href="./attendance.php?page=' . $prevPage . '" class="btn btn-xs btn-default">◀</a>';
  }

  for($i = $firstPage; $i <= $lastPage; $i++) {
  	if($i == $page) {
  		$paging .= '<a href="#" class="btn btn-xs btn-primary">' . $i . '</a>';
  	} else {
  		$paging .= '<a href="./attendance.php?page=' . $i . '" class="btn btn-xs btn-default">' . $i . '</a>';
  	}
  }

  //마지막 섹션이 아니라면 다음 버튼을 생성
  if($currentSection != $allSection) {
  	$paging .= '<a href="./attendance.php?page=' . $nextPage . '" class="btn btn-xs btn-default">▶</a>';
  }
  //마지막 페이지가 아니라면 끝 버튼을 생성
  if($page != $allPage) {
  	$paging .= '<a href="./attendance.php?page=' . $allPage . '" class="btn btn-xs btn-default">▶▶</a>';
  }
  //$paging .= '</ul>';


  $currentLimit = ($onePage * $page) - $onePage; //몇 번째의 글부터 가져오는지
  $sqlLimit = ' limit ' . $currentLimit . ', ' . $onePage; //limit sql 구문

  try{
  	$pdo = new PDO($dsn, $dbUser, $dbPwd);
    $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
    //$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMORE_EXCEPTION);

  	$sql = "SELECT * FROM tbl_attend where att_status = 1 and fk_mem_id = '$mem_id' order by att_idx desc" . $sqlLimit;
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

  </style>
	<script type="text/javascript">
    window.onload = function () {
      kCalendar('kCalendar');
    };
	</script>
</head>
<body>
<div style="height: 100%; overflow: hidden; margin-left: 10%; margin-right: 10%; text-align: center;">
	<div class="imgtop">
    <a href="./notice.php" target="content"><img src="../images/plogo.png" style="width: 150px; height: 150px; margin-top: 25px;"/></a>
  </div>

	<div style="text-align: center;">
    <h1>출석 체크</h1>
  </div>

	<br/>

  <div style="margin-left: 23%; width: 50%;" class="container">
    <div style="margin-top: 11%; float: left;">
      <div id="kCalendar"></div>
      <br/>
      <div>
        <?php
        // 12시간제
        echo "오늘 날짜 : " . date("Y-m-d");
        ?>
      </div>
    </div>
    <br/>
    <div style="float: right;">
      <a href="./attendance_check.php?mem_id=<?php echo $mem_id ?>" class="btn btn-success"><span class="glyphicon glyphicon-ok"></span> 출석체크</a>
      <br/>
      <h3>출석 로그</h3>
      <?php
        $checkNo = 1;
        if(empty($result)){
      ?>
        <div class="list-group">
          <a href="#" class="list-group-item clearfix">
            <span class="glyphicon glyphicon-remove-circle"></span>
            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;로그가 존재하지 않습니다.&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
            <span class="glyphicon glyphicon-remove-sign"></span>
          </a>
        </div>
      <?php
        } else {
          foreach($result as $row){
      ?>
        <div class="list-group">
          <a href="#" class="list-group-item clearfix">
            <span class="glyphicon glyphicon-ok-circle"></span>
            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<?php echo $row['att_date'] ?>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
            <span class="glyphicon glyphicon-ok-sign"></span>
          </a>
        </div>
      <?php
          }
      ?>
        <div class="paging">
          <?php echo $paging ?>
        </div>
      <?php
        }
      ?>

    </div>
  </div>

</div>

<br/><br/>

</body>
</html>
