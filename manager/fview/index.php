<?php
session_start();

# 로그인이 안돼있다면
if(!isset($_SESSION['userid']))
{
	header ('Location: ../login?ourl='.urlencode($_SERVER[REQUEST_URI]));
	exit();
}

$user_id = $_SESSION['userid'];
$user_name = $_SESSION['username'];

# DB, 설정 로드
require('../../src/dbconn.php');
require('../../src/settings.php');
$startloc = '../'.$startloc;

# 현재 페이지 (사이드바 메뉴 출력용)
$curr_page = 'fview';

# 현재 경로 유효 여부
$is_valid = true;

# 현재 탐색 위치
if ($_GET['d'] != null) {
    $curr_dir = $_GET['d'];
}


?>

<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

	<title>12:27 백업클라우드 관리 Page</title>

	<link href="../css/app.css" rel="stylesheet">
	<link href="../css/style.css" rel="stylesheet">

	<style>
        .item
		{
			box-shadow: 0 0 0.5rem rgba(0,0,0,.15);
			cursor: pointer;
			transition: transform .2s;
			position: relative;
            border-radius: 6px;
		}

		.item:hover {
			transform: scale(1.01);
		}

		#ellipWrap {
				width: 100px;
				background-color: #ccc;
		}
		.ell {
			overflow: hidden;
			text-overflow: ellipsis;
			word-wrap: break-word;
			display: -webkit-box;
			-webkit-line-clamp: 2; /* ellipsis line */
			-webkit-box-orient: vertical;

		}
	</style>
</head>

<body>
	<div class="wrapper">
		<!-- 사이드바 -->
		<?php require('../src/sidebar.php')?>

		<div class="main">
			<!-- 네비게이션 -->
			<?php require('../src/navbar.php')?>

			<main class="content">
                <span hidden id="loaddir"><?=$curr_dir?></span>
                <div class="container-fluid p-0">
                    <div class="mb-3">
                        <span class="fs-3"><b>파일 탐색기</b> <span id="currdir"></span></span>
                    </div>
                    <div class="card">
                        <div class="px-4 pt-4">
                            <div class="input-group flex-nowrap">
                                <span class="input-group-text" id="startloc"><?=$startloc?></span>
                                <input type="text" class="form-control" id="currdirInput" placeholder="현재 경로..." value="<?=$curr_dirr?>">
                                <button class="btn btn-outline-secondary" type="button" id="dirBt">이동</button>
                            </div>
                        </div>

                        <div class="card-body" method="GET" action="./">
                            <!-- 파일 탐색 영역 -->
                            <div id="dir" class="row row-cols-1 row-cols-xl-2 row-cols-xxl-3 g-4 p-3" style="overflow: auto; overflow-y: hidden;">
                                    
                            </div>
                        </div>

                        <div class="d-flex justify-content-between align-items-center p-2">
                            <span class="fs-6 ms-3">이 위치에서...</span>
                            <div class="flex">
                                <button type="button ms-2" class="btn btn-secondary">파일 업로드</button>
                                <button type="button ms-2" class="btn btn-secondary">폴더 생성</button>
                                <button type="button ms-2" class="btn btn-danger">파일 파일</button>
                                <button type="button ms-2" class="btn btn-danger">폴더 삭제</button>
                            </div>
                        </div>
                    </div>
                </div>
			</main>

			<!-- footer 영역 -->
			<?php require('../src/footer.php')?>
		</div>
	</div>

	<script src="../js/app.js"></script>
    <script src="./js/script.js?rev=0.17"></script>
</body>

</html>