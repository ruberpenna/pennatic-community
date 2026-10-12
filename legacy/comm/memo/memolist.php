<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8" />
	<title>Penna - Memo</title>
  <link rel="stylesheet" type="text/css" href="../bootstrap/css/bootstrap.css">
  <link rel="stylesheet" type="text/css" href="https://cdnjs.cloudflare.com/ajax/libs/sweetalert/1.1.3/sweetalert.min.css" />
  <link href="//netdna.bootstrapcdn.com/font-awesome/4.0.3/css/font-awesome.min.css" rel="stylesheet">
  <script type="text/javascript" src="../js/jquery-2.1.3.min.js"></script>
  <script type="text/javascript" src="../bootstrap/js/bootstrap.js"></script>
  <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/sweetalert/1.1.3/sweetalert.min.js"></script>
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

	if(isset($_GET['subject'])){
		$subject = $_GET['subject'];
	} else {
		$subject = '';
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

	  if(empty($subject)){
	    $sql = "SELECT count(*) as cnt FROM tbl_memo WHERE fk_mem_id = '$mem_id' ORDER BY memo_idx DESC";
	  } else {
	    $sql = "SELECT count(*) as cnt FROM tbl_memo WHERE fk_mem_id = '$mem_id' and memo_subject = '$subject' ORDER BY memo_idx DESC";
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
	$onePage = 10; // 한 페이지에 보여줄 게시글의 수.
	$allPage = ceil($allPost / $onePage); //전체 페이지의 수

	if($page < 1 || ($allPage && $page > $allPage)){
		echo "<script>alert('존재하지 않는 페이지입니다.');history.back();</script>";
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
		$paging .= '<a href="./memolist.php?page=1" class="btn btn-default">◀◀</a>';
	}
	//첫 섹션이 아니라면 이전 버튼을 생성
	if($currentSection != 1) {
		$paging .= '<a href="./memolist.php?page=' . $prevPage . '" class="btn btn-default">◀</a>';
	}

	for($i = $firstPage; $i <= $lastPage; $i++) {
		if($i == $page) {
			$paging .= '<a href="#" class="btn btn-primary">' . $i . '</a>';
		} else {
			$paging .= '<a href="./memolist.php?page=' . $i . '" class="btn btn-default">' . $i . '</a>';
		}
	}

	//마지막 섹션이 아니라면 다음 버튼을 생성
	if($currentSection != $allSection) {
		$paging .= '<a href="./memolist.php?page=' . $nextPage . '" class="btn btn-default">▶</a>';
	}
	//마지막 페이지가 아니라면 끝 버튼을 생성
	if($page != $allPage) {
		$paging .= '<a href="./memolist.php?page=' . $allPage . '" class="btn btn-default">▶▶</a>';
	}
	//$paging .= '</ul>';


	$currentLimit = ($onePage * $page) - $onePage; //몇 번째의 글부터 가져오는지
	$sqlLimit = ' limit ' . $currentLimit . ', ' . $onePage; //limit sql 구문

	try{
		$pdo = new PDO($dsn, $dbUser, $dbPwd);
	  $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
	  //$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMORE_EXCEPTION);

		if(empty($subject)){
	    $sql = "SELECT * FROM tbl_memo WHERE fk_mem_id = '$mem_id' ORDER BY memo_idx DESC" . $sqlLimit;
	  } else {
	    $sql = "SELECT * FROM tbl_memo WHERE fk_mem_id = '$mem_id' and memo_subject = '$subject' ORDER BY memo_idx DESC" . $sqlLimit;
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


	// 현재 분류 리스트
	try{
		$pdo = new PDO($dsn, $dbUser, $dbPwd);
	  $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
	  //$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMORE_EXCEPTION);

	  $sql = "SELECT DISTINCT memo_subject FROM tbl_memo where fk_mem_id = '$mem_id'";
		$stm = $pdo->prepare($sql);
		$stm->execute();
		$sublist = $stm->fetchAll(PDO::FETCH_ASSOC);

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

    a:hover, a:focus{
     outline: none;
     text-decoration: none;
    }
    #accordion .panel{
     border:0px none;
     box-shadow:none;
    }
    #accordion .panel-heading{
     padding:0;
     background: #fff;
    }
    #accordion .panel-title a{
     display: block;
     position: relative;
     background:transparent;
     color:#de4d4e;
     font-size:14px;
     font-weight: bolder;
     text-transform:uppercase;
     margin-bottom:15px;
     padding:15px 20px;
     border-bottom:1px solid #de4d4e;
     border-radius: 0 15px 0 15px;
     transition:all 0.10s linear 0s;
    }
    #accordion .panel-title a.collapsed{
     color:#808080;
     border-bottom:1px solid #d3d3d3;
     margin: 0;
    }
    #accordion .panel-title a i{
     color:#de4d4e;
     position: absolute;
     top: 14px;
     left:25px;
    }
    #accordion .panel-title a:before,
    #accordion .panel-title a.collapsed:before{
     content:"";
     position: absolute;
     bottom:-15px;
     left:36px;
     border:7px solid transparent;
     border-top:7px solid #de4d4e;
    }
    #accordion .panel-title a.collapsed:before{
     content:"";
     border: 0px none;
    }
    #accordion .panel-title a.collapsed:hover{
     color: #6a6060;
    }
    #accordion .panel-title a:after,
    #accordion .panel-title a.collapsed:after{
     content: "\f106";
     font-family: FontAwesome;
     color: #de4d4e;
     font-size: 20px;
     line-height: 20px;
     position: absolute;
     top: 14px;
     right:25px;
    }
    #accordion .panel-title a.collapsed:after{
     content: "\f107";
     color:#808080;
    }
    #accordion .panel-body{
     border-top:0 none;
     color:#808080;
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
		$(document).ready(function() {
			$("form#searchFrm").bind("submit", function () {

				//.val()로 값을 가지고 올수 있으나 .trim()하여 비교
				//하면 빈값이나 스페이스값 빈값에 개행까지 잡아냄
				if ($("input#searchString").val().trim() == "") {
				 swal("검색어를 입력해 주세요.");
				 $("input#searchString").focus();
				 return false;
				}
			});
		});

    function goDel(idx){
			swal({
		  title: "해당 메모를 삭제하시겠습니까?",
		  type: "danger",
		  showCancelButton: true,
		  confirmButtonClass: "btn-primary",
		  confirmButtonText: "확인",
			},
			function(isConfirm) {
			  if (isConfirm) {
				 location.href="./memo_del.php?memo_idx="+idx;
			  }
			});

    }

    function goTop(){
    	$('html').scrollTop(0);
    }

    function goBottom(){
      $("html").scrollTop($(document).height());
    }
	</script>
</head>
<body>
<div style="height: 100%; overflow: hidden; margin-left: 10%; margin-right: 10%;">
	<div class="imgtop">
    <a href="./notice.php" target="content"><img src="../images/plogo.png" style="width: 150px; height: 150px; margin-top: 25px;"/></a>
  </div>

	<div style="text-align: center;">
    <h1>메모 목록</h1>
  </div>

	<br/>

  <div class="container">
    <div class="row">
     <div class="col-md-8 col-md-offset-2">

       <div class="panel-group" id="accordion" role="tablist" aria-multiselectable="true">
         <?php
       		$memo_no = 1;
       		if(empty($result)){
       	 ?>
           <div class="list-group">
            <br/><br/>
            <div style="text-align: left;">
              <a href="./memo_write.php" target="content" class="btn btn-sm btn-primary"><span class="glyphicon glyphicon-pencil"></span> 메모작성</a>
            </div>
            <br/>
            <div style="text-align: center;">
       				<a class="list-group-item clearfix">
       					<span class="glyphicon glyphicon-remove-circle"></span>
       					&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;존재하는 메모글이 없습니다.&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
       					<span class="glyphicon glyphicon-remove-sign"></span>
       				</a>
            </div>
     			 </div>
         <?php
       		} else {
            echo '<a href="./memo_write.php" target="content" class="btn btn-sm btn-primary"><span class="glyphicon glyphicon-pencil"></span> 메모작성</a>&nbsp;';
            echo '<a href="./memolist.php" target="content" class="btn btn-sm btn-default">전체</a>&nbsp;';
            foreach($sublist as $row){
              echo '<a href="./memolist.php?subject=' . $row['memo_subject'] . '" target="content" class="btn btn-sm btn-default">' . $row['memo_subject'] .'</a>&nbsp;';
            }
            echo '<br/><br/>';
       			foreach($result as $row){
       	 ?>
           <div class="panel panel-default">
             <div class="panel-heading" role="tab" id="heading<?php echo $memo_no ?>">
               <h4 class="panel-title">
               <a class="" role="button" data-toggle="collapse" data-parent="#accordion" href="#collapse<?php echo $memo_no ?>" aria-expanded="true" aria-controls="collapse<?php echo $memo_no ?>">
                 <span style="color: navy;">[<?php echo $row['memo_subject'] ?>]</span>&nbsp;<?php echo $row['memo_title'] ?>
               </a>
               </h4>
             </div>
             <div id="collapse<?php echo $memo_no ?>" class="panel-collapse collapse in" role="tabpanel" aria-labelledby="heading<?php echo $memo_no ?>">
               <div class="panel-body">
                 <span style="color: gray; font-size: 10pt;">작성일 : <?php echo $row['memo_wdate'] ?>&nbsp;&nbsp;／&nbsp;&nbsp;수정일 : <?php echo $row['memo_edate'] ?></span><br/><br/>
                 <?php echo $row['memo_content'] ?>
                 <div style="float: right;">
                   <a href="./memo_edit.php?memo_idx=<?php echo $row['memo_idx'] ?>" class="btn btn-xs btn-info"><span class="glyphicon glyphicon-edit"></span> 수정</a>
                   <a href="javascript:goDel(<?php echo $row['memo_idx'] ?>)" class="btn btn-xs btn-danger"><span class="glyphicon glyphicon-remove"></span> 삭제</a>
                 </div>
               </div>
             </div>
           </div>
          <?php
            $memo_no = $memo_no + 1;
          }
          ?>
          <br/>
    			<div class="paging" style="text-align: center;">
    				<?php echo $paging ?>
    			</div>
					<br/>
					<div style="text-align: center;">
						<form class="form-inline" method="get" action="memo_search.php" id="searchFrm">
							 <select id="searchSubject" name="searchSubject" class="form-control">
		  						<option value="제목">제목</option>
		  						<option value="내용">내용</option>
		  				 </select>
		          <input type="text" class="form-control" id="searchString" name="searchString" maxlength="20" style="width: 200px;" />
							<input type="hidden" id="subject" name="subject" value="<?php echo $subject ?>" />
							<button type="submit" class="btn btn-default"><span class="glyphicon glyphicon-search"></span></button>
						</form>
					</div>
    		<?php
    		}
    		?>
				<div id="footer">
			    &nbsp;&nbsp;
			    <button type="button" onclick="goTop();" class="btn btn-primary btn-default"><span class="glyphicon glyphicon-arrow-up"></span> </button>
			    <button type="button" onclick="goBottom();" href="#" class="btn btn-primary btn-default"><span class="glyphicon glyphicon-arrow-down"></span> </button>
			  </div>
    		<br/>

        </div><!--./panel-group-->

       </div>
     </div>
  </div>



</div>

<br/><br/>

</body>
</html>
