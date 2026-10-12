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
	error_reporting(E_ALL);
	ini_set("display_errors", 1);

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
	}

	if($_SESSION['mem_status'] != 2){
		echo "<script>
	        $(window).load(function(){
	          $(document).ready(function () {
	            swal({
	              title: '운영자만 접근이 가능합니다.',
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

	  $sql = "SELECT count(*) as cnt FROM tbl_file ORDER BY file_idx DESC";
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
		$paging .= '<a href="./admin_file.php?page=1" class="btn btn-default">◀◀</a>';
	}
	//첫 섹션이 아니라면 이전 버튼을 생성
	if($currentSection != 1) {
		$paging .= '<a href="./admin_file.php?page=' . $prevPage . '" class="btn btn-default">◀</a>';
	}

	for($i = $firstPage; $i <= $lastPage; $i++) {
		if($i == $page) {
			$paging .= '<a href="#" class="btn btn-primary">' . $i . '</a>';
		} else {
			$paging .= '<a href="./admin_file.php?page=' . $i . '" class="btn btn-default">' . $i . '</a>';
		}
	}

	//마지막 섹션이 아니라면 다음 버튼을 생성
	if($currentSection != $allSection) {
		$paging .= '<a href="./admin_file.php?page=' . $nextPage . '" class="btn btn-default">▶</a>';
	}
	//마지막 페이지가 아니라면 끝 버튼을 생성
	if($page != $allPage) {
		$paging .= '<a href="./admin_file.php?page=' . $allPage . '" class="btn btn-default">▶▶</a>';
	}
	//$paging .= '</ul>';

	/* 페이징 끝 */
	$currentLimit = ($onePage * $page) - $onePage; //몇 번째의 글부터 가져오는지
	$sqlLimit = ' limit ' . $currentLimit . ', ' . $onePage; //limit sql 구문

	try{
		$pdo = new PDO($dsn, $dbUser, $dbPwd);
	  $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
	  //$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMORE_EXCEPTION);

	  $sql = "SELECT * FROM tbl_file order by file_idx desc" . $sqlLimit;
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

  </style>
	<script type="text/javascript">
		function goDel(file_idx){
			swal({
			  title: "파일을 완전히 삭제하시겠습니까?",
			  type: "danger",
			  showCancelButton: true,
			  confirmButtonClass: "btn-danger",
			  confirmButtonText: "확인",
			},
			function(isConfirm) {
			  if (isConfirm) {
				 location.href="./admin_file_del.php?file_idx="+file_idx;
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
      <h1>파일 관리</h1>
    </div>

		<br/>

    <table class="table table-hover" style="width: 90%; margin-left: 5%;">
      <thead style="border-top: 1px solid gray; background-color: #F2F2F2;">
        <tr>
          <th width="5%">번호</th>
          <th width="25%">파일원제</th>
          <th width="25%">파일신제</th>
          <th width="10%">크기</th>
          <th width="10%">다운 수</th>
          <th width="15%">게시판번호</th>
          <th width="10%">옵션</th>
        </tr>
      </thead>
      <tbody style="border-bottom: 1px solid gray;">
        <?php
          foreach ($result as $row) {
        ?>
          <tr>
            <td style="vertical-align: middle;"><?php echo $row['file_idx'] ?></td>
            <td style="vertical-align: middle;"><?php echo $row['file_oldname'] ?></td>
            <td style="vertical-align: middle;"><?php echo $row['file_newname'] ?></td>
            <td style="vertical-align: middle;"><?php echo $row['file_size'] ?>KB</td>
            <td style="vertical-align: middle;"><?php echo $row['file_count'] ?></td>
            <td style="vertical-align: middle;"><?php echo $row['fk_board_idx'] ?></td>
            <td style="vertical-align: middle;">
              <a href="javascript:goDel();" class="btn btn-xs btn-danger">삭제</a>
            </td>
          </tr>
        <?php
          }
        ?>
      </tbody>
    </table>

    <div class="paging" style="text-align: center;">
      <?php echo $paging ?>
    </div>

		<br/>

		<div style="text-align: center;">
			<div>
				<form class="form-inline" method="get" action="admin_file_search.php">
					 <select id="searchSubject" name="searchSubject" class="form-control">
            <option value="파일번호">파일번호</option>
						<option value="게시판번호">게시판번호</option>
            <option value="원제">원제</option>
            <option value="신제">신제</option>
  				 </select>
          <input type="text" class="form-control" id="searchString" name="searchString" style="width: 200px;" />
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
