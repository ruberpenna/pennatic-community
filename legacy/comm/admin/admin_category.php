<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8" />
	<title>Penna - Category(ADMIN)</title>
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

	//전체 폴더
	try{
		$pdo = new PDO($dsn, $dbUser, $dbPwd);
	  $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
	  //$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMORE_EXCEPTION);

		$sql = "SELECT DISTINCT board_folder FROM tbl_board WHERE board_status = 3";
		$stm = $pdo->prepare($sql);
		$stm->execute();
		$f_result = $stm->fetchAll(PDO::FETCH_ASSOC);

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

    form {
      display:inline
    }

  </style>
	<script type="text/javascript">

    $(function() {

			$("form#folder_add_frm").bind("submit", function () {
        //.val()로 값을 가지고 올수 있으나 .trim()하여 비교
        //하면 빈값이나 스페이스값 빈값에 개행까지 잡아냄
        if ($("input#category_folder").val().trim() == "") {
         swal("폴더 추가 칸이 비어있는 상태입니다.");
         $("input#category_folder").focus();
         return false;
        }
      });

			$("form#group_add_frm").bind("submit", function () {
        //.val()로 값을 가지고 올수 있으나 .trim()하여 비교
        //하면 빈값이나 스페이스값 빈값에 개행까지 잡아냄
        if ($("input#category_group").val().trim() == "") {
         swal("게시판 추가 칸이 비어있는 상태입니다.");
         $("input#category_group").focus();
         return false;
        }
      });

			$("form#subject_add_frm").bind("submit", function () {
        //.val()로 값을 가지고 올수 있으나 .trim()하여 비교
        //하면 빈값이나 스페이스값 빈값에 개행까지 잡아냄
        if ($("input#category_subject").val().trim() == "") {
         swal("말머리 추가 칸이 비어있는 상태입니다.");
         $("input#category_subject").focus();
         return false;
        }
      });

      $('#folderDel_btn').attr('disabled',true);
      $('#groupDel_btn').attr('disabled',true);
      $('#subjectDel_btn').attr('disabled',true);

      $('#groupAdd_btn').attr('disabled',true);
      $('#subjectAdd_btn').attr('disabled',true);

      $("#folders").change(function(){
        $('#groupAdd_btn').attr('disabled',false);

        var fsel1 = document.getElementById("category_fsel1");
				var fsel2 = document.getElementById("category_fsel2");
        fsel1.value = $("#folders option:selected").val();
				fsel2.value = $("#folders option:selected").val();


         var params = {};
         params['folder'] = $("#folders option:selected").val();

         $.ajax({
              type: 'POST',     // POST 또는 GET
              dataType: 'json',
              url: 'admin_category_list.php',    // 요청할 url 주소
              data: params,    // 서버에 전달하는 parameters
              success: function (data) {
                $("#groups").find("option").remove();
                $("#subjects").find("option").remove();

                $.each(data, function(key, val) {
                  $("#groups").append("<option value='"+val.board_group+"'>"+val.board_group+"</option>");
                });

                if(data.Data.response.length == 0){
                  $('#folderDel_btn').attr('disabled',false);
                }

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

       });


       $("#groups").change(function(){
         $('#groupDel_btn').attr('disabled',false);
         $('#subjectAdd_btn').attr('disabled',false);

         var gsel = document.getElementById("category_gsel");
         gsel.value = $("#groups option:selected").val();

          var params = {};
          params['group'] = $("#groups option:selected").val();

          $.ajax({
               type: 'POST',     // POST 또는 GET
               dataType: 'json',
               url: 'admin_category_list.php',    // 요청할 url 주소
               data: params,    // 서버에 전달하는 parameters
               success: function (data) {
                 $("#subjects").find("option").remove();

                 $.each(data, function(key, val) {
                   $("#subjects").append("<option value='"+val.board_subject+"'>"+val.board_subject+"</option>");
                 });

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

        });

        $("#subjects").change(function(){
          $('#subjectDel_btn').attr('disabled',false);

        });
    });

    function goCategoryDel(type){
      var frm = document.getElementById('category_del_frm');

      frm.category_type.value = type;

      frm.submit();
    }

	</script>
</head>
<body>
<div style="height: 100%; overflow: hidden; text-align: center;">
	<div class="imgtop">
    <a href="./notice.php" target="content"><img src="../images/plogo.png" style="width: 150px; height: 150px; margin-top: 25px;"/></a>
  </div>

	<div style="text-align: center;">
    <h1>카테고리 관리</h1>
  </div>

	<br/>

  <div style="margin-left: 20%; width: 60%;" class="container">
    <div style="float: left;">
      <form id="category_del_frm" class="form-inline" method="post" action="admin_category_del.php">
        <select name='folders' id="folders" style="width: 150px; height: 300px;" multiple>
          <?php
            foreach ($f_result as $f_row) {
          ?>
            <option value='<?php echo $f_row['board_folder'] ?>'><?php echo $f_row['board_folder'] ?></option>
          <?php
            }
          ?>
        </select>
        <select name='groups' id="groups" style="width: 150px; height: 300px;" multiple></select>
        <select name='subjects' id="subjects" style="width: 150px; height: 300px;" multiple></select>
        <input type="hidden" id="category_type" name="category_type" />
      </form>
      <br/><br/>
      <button type="button" id="folderDel_btn" onclick="javascript:goCategoryDel('folder');" class="btn btn-danger">폴더 삭제</button>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
      <button type="button" id="groupDel_btn" onclick="javascript:goCategoryDel('group');" class="btn btn-danger">게시판 삭제</button>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
      <button type="button" id="subjectDel_btn" onclick="javascript:goCategoryDel('subject');" class="btn btn-danger">말머리 삭제</button>
    </div>
    <div style="float: right;">
      <form id="folder_add_frm" class="form-inline" method="post" action="admin_category_add.php">
        <input type="text" id="category_folder" name="category_folder" placeholder="폴더 입력칸" class="form-control" style="width: 300px;" /><br/><br/>
        <button type="submit" id="folderAdd_btn" class="btn btn-success">폴더 추가</button><br/><br/><br/>
      </form>
      <form id="group_add_frm" class="form-inline" method="post" action="admin_category_add.php">
        <input type="hidden" id="category_fsel1" name="category_fsel1" />

        <input type="text" id="category_group" name="category_group" placeholder="게시판 입력칸" class="form-control" style="width: 300px;" /><br/><br/>
        <button type="submit" id="groupAdd_btn" class="btn btn-success">게시판 추가</button><br/><br/><br/>
      </form>
      <form id="subject_add_frm" class="form-inline" method="post" action="admin_category_add.php">
        <input type="hidden" id="category_fsel2" name="category_fsel2" />
        <input type="hidden" id="category_gsel" name="category_gsel" />

        <input type="text" id="category_subject" name="category_subject" placeholder="말머리 입력칸" class="form-control" style="width: 300px;" /><br/><br/>
        <button type="submit" id="subjectAdd_btn" class="btn btn-success">말머리 추가</button><br/>
      </form>
    </div>
  </div>

</div>

<br/><br/>

</body>
</html>
