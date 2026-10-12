<?php session_start(); ?>
<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8" />
	<title>Penna - SideBar</title>
	<link rel="stylesheet" type="text/css" href="./bootstrap/css/bootstrap.css">
	<link rel="stylesheet" type="text/css" href="https://cdnjs.cloudflare.com/ajax/libs/sweetalert/1.1.3/sweetalert.min.css" />
	<script type="text/javascript" src="./js/jquery-2.1.3.min.js"></script>
	<script type="text/javascript" src="./bootstrap/js/bootstrap.js"></script>
	<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/sweetalert/1.1.3/sweetalert.min.js"></script>
	<?php
	include("./dbconfig.php");
	error_reporting(E_ALL);
	ini_set("display_errors", 1);

	if(isset($_SESSION['mem_id'])){
		$mem_id = $_SESSION['mem_id'];
	} else {
		$mem_id = '';
	}

	try{
		$pdo = new PDO($dsn, $dbUser, $dbPwd);
	  $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
	  //$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMORE_EXCEPTION);

	  $sql = "SELECT DISTINCT board_folder FROM tbl_board where board_status = 3";
		$stm = $pdo->prepare($sql);
		$stm->execute();
		$folders = $stm->fetchAll(PDO::FETCH_ASSOC);

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
    .imgmenu {
			text-align: center;
			height: 180px;
			overflow-x: auto;
		}

		#sidebar-wrapper {
	    height: 100%;
	    overflow-x: hidden;
	    overflow-y: auto;
	  }

		.sidebar-nav {
	    width: 235px;
	    margin: 0;
	    padding: 0;
	    list-style: none;
	  }

	  .sidebar-nav li {
	    text-indent: 1.5em;
	    line-height: 2.8em;
	  }

	  .sidebar-nav li a {
	    display: block;
	    text-decoration: none;
	    color: #ffffff;
	  }

	  .sidebar-content a:hover {
	    color: #fff;
	    background: rgba(255, 255, 255, 0.2);
	  }

	  .sidebar-nav > .sidebar-brand {
	    font-size: 1.5em;
	    line-height: 3em;
	  }

		.sidebar-brand {
			color: #fff;
		}

  </style>
	<script type="text/javascript">

		function mySearch(){
			window.open('mysearch.php','drdr','width=600,height=600,top=100,left=100');
		}

		function goChat(mem_id){
			if(mem_id == "" || mem_id == null){
				swal("로그인이 필요합니다.");
			} else {
				window.open('./chatting/chat.php','chatting','width=400,height=655,top=100,left=100');
			}

		}

	</script>
</head>
<body style="background-color: #39444b">
  <div style="height: 100%;">
    <div class="imgmenu">
      <a href="./board/notice.php" target="content"><img src="./images/plogo.png" style="width: 100px; height: 100px; margin-top: 25px;"/></a>
    </div>
    <div class="imgmenu">
			<?php
				if(!isset($_SESSION['mem_id'])){
					echo '<form class="form-inline" method="post" action="login_ok.php">';
						echo '<span style="color: #fff;">　아이디</span>&nbsp;&nbsp;<input type="text" class="form-control" name="mem_id" maxlength="20" style="width: 100px; display: inline;" /><br/><br/>';
						echo '<span style="color: #fff;">비밀번호</span>&nbsp;&nbsp;<input type="password" class="form-control" name="mem_pwd" maxlength="20" style="width: 100px; display: inline;" />';
						echo '<br/><br/>';
			      echo '<button type="submit" class="btn btn-sm btn-primary">로그인</button>&nbsp;';
			      echo '<a href="./register.php" target="content" class="btn btn-sm btn-success">회원가입</a>&nbsp;';
						echo '<a href="javascript:mySearch();" class="btn btn-sm btn-warning">?</a>';
					echo '</form>';
				} else {
					$userid = $_SESSION['mem_id'];
					$usernick = $_SESSION['mem_nick'];
					$userimg = $_SESSION['mem_img'];
					$userstatus = $_SESSION['mem_status'];
					if($userstatus == '2'){
						echo '<img src="./files/', $userimg, '" style="width: 50px; height: 50px; border-radius: 25px;" />';
			      echo '<br/><br/>';
			      echo '<span style="color: white;">', $userid, '(', $usernick, ')</span>';
						echo '<br/><br/>';
						echo '<a href="./mypage.php" target="content" class="btn btn-sm btn-primary">정보수정</a>&nbsp;';
						echo '<a href="./login_out.php" class="btn btn-sm btn-danger">로그아웃</a>&nbsp;';
						echo '<a href="./admin/admin_page.php" target="content" class="btn btn-sm btn-info">관리</a>';
					} else {
						echo '<img src="./files/', $userimg, '" style="width: 50px; height: 50px; border-radius: 25px;" />';
			      echo '<br/><br/>';
			      echo '<span style="color: white;">', $userid, '(', $usernick, ')</span>';
						echo '<br/><br/>';
						echo '<a href="./mypage.php" target="content" class="btn btn-sm btn-primary">정보수정</a>&nbsp;';
						echo '<a href="./login_out.php" class="btn btn-sm btn-danger">로그아웃</a>&nbsp;';
					}
				}
			?>
    </div>
		<div id="sidebar-wrapper">
	    <ul class="sidebar-nav">
				<?php
					foreach($folders as $f_row){
						$folder = $f_row['board_folder'];
						echo '<li class="sidebar-brand">' . $folder . '</li>';

						try{
							$pdo = new PDO($dsn, $dbUser, $dbPwd);
						  $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
						  //$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMORE_EXCEPTION);

						  $sql = "SELECT DISTINCT board_group FROM tbl_board where board_status = 3 and board_folder = '" . $folder . "'";
							$stm = $pdo->prepare($sql);
							$stm->execute();
							$groups = $stm->fetchAll(PDO::FETCH_ASSOC);

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

						if($folder == "Community"){
							echo '<li class="sidebar-content"><a href="./host/hostpage.php" target="content">주인장 소개</a></li>';
							echo '<li class="sidebar-content"><a href="./board/attendance.php" target="content">출석체크</a></li>';
							echo '<li class="sidebar-content"><a href="./memo/memolist.php" target="content">개인메모</a></li>';
							echo '<li class="sidebar-content"><a href="./msg/msglist.php" target="content">쪽지함</a></li>';
				?>
							<li class="sidebar-content"><a href="javascript:goChat('<?php echo $mem_id ?>');" target="content">채팅방</a></li>
				<?php
						}

						foreach($groups as $g_row){
							echo '<li class="sidebar-content"><a href="./board/board.php?board_folder=' . $folder . '&board_group=' . $g_row['board_group'] . '" target="content">' . $g_row['board_group'] . '</a></li>';
						}
					}
				?>
	    </ul>
  	</div>
  </div>
</body>
</html>
