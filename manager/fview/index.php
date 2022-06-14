<?php
session_start();

# 로그인이 안돼있다면
if(!isset($_SESSION['userid'])) {
	header ('Location: ../login?ourl='.urlencode($_SERVER[REQUEST_URI]));
	exit();
}

# 세션 체크 - 관리자 및 모더 허용
require_once('../src/session.php');
sess_check(array('admin', 'mod'));


$rev = '0.25';

$user_id = $_SESSION['userid'];
$user_name = $_SESSION['username'];

# DB, 설정 로드
require('../../src/dbconn.php');
require('../../src/settings.php');
$startloc = '../'.$startloc;

# 현재 페이지 (사이드바 메뉴 출력용)
$curr_page = 'fview';

# 페이지 타이틀
$page_title = '파일 탐색기';

# 현재 경로 유효 여부
$is_valid = true;

# 현재 탐색 위치
if ($_GET['d'] != null) {
    $curr_dir = $_GET['d'];
}

# 업로드 페이지에서 왔는지 여부
$from_upload = ($_GET['from_upload'] == 1);


?>

<!DOCTYPE html>
<html>
<head>
	<!-- 헤더 -->
	<?php require('../src/header.php')?>
	<link href="./css/style.css?rev=<?=$rev?>" rel="stylesheet">
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
                                <button class="btn btn-outline-secondary rounded-end" type="button" id="dirBt">이동</button>
								<span>
								<!-- 로딩 스피너 -->
								<div id="loader" class="loader ms-3"></div>
								</span>
                            </div>
                        </div>

                        <div class="card-body" method="GET" action="./">
                            <!-- 파일 탐색 영역 -->
                            <div id="dir" class="row row-cols-1 row-cols-xl-2 row-cols-xxl-3 g-4 p-3" style="overflow: auto; overflow-y: hidden;">
								
                            </div>
                        </div>

						<!-- 에러 표시 영역 -->
						<div id="error" class="d-flex justify-content-end px-2">
							<?php if ($_GET['uploaderror']==1) {?>
							<div class="alert fade show p-2 rounded-3" style="color: white; background-color: #f14254;" role="alert">
								영상 업로드는 성공하였으나 자막 또는 썸네일이 정상적으로 적용되지 않았습니다. 수동으로 적용해 주시기 바랍니다.
								<button type="button" class="btn-close btn-close-white align-middle" data-bs-dismiss="alert" aria-label="Close"></button>
							</div>
							<?php }?>
							<?php if ($_GET['delok']==1) {?>
							<div class="alert fade show p-2 rounded-3" style="color: white; background-color: #64b899;" role="alert">
								해당 비디오의 DB가 성공적으로 삭제되었습니다.
								<button type="button" class="btn-close btn-close-white align-middle" data-bs-dismiss="alert" aria-label="Close"></button>
							</div>
							<?php }?>
						</div>

						<div class="d-flex justify-content-between align-items-center p-2">
							<span class="fs-6 ms-3">현재 위치에서...</span>
							<span class="d-flex justify-content-end">
								<?php if (!$from_upload) { ?> <button type="button" class="btn btn-outline-secondary" onclick='location.href="../upload?d="+encodeURIComponent(currdir);'>업로드</button> <?php } ?>
								<div class="input-group ms-2" style="max-width: 300px;">
									<input type="text" class="form-control" id="newFolderName" placeholder="폴더 이름">
									<button class="btn btn-outline-secondary" type="button" id="newFolderBt">폴더 생성</button>
									<button class="btn btn-outline-danger" type="button" id="delFolderBt">폴더 삭제</button>
								</div>
							</span>
						</div>

						<?php if ($from_upload) { ?> 
						<div class="d-flex justify-content-end px-2 pb-2">
							<button class="btn btn-lg btn-primary" onclick='location.href="../upload?d="+encodeURIComponent(currdir);'>
								<i class="align-middle me-1" data-feather="check"></i>이 경로로 지정하기
							</button>
						</div>
						<?php } ?>
                    </div>
                </div>
			</main>

			<!-- footer 영역 -->
			<?php require('../src/footer.php')?>
		</div>
	</div>

	<script src="../js/app.js"></script>
    <script src="./js/script.js?rev=<?=$rev?>"></script>
</body>

</html>