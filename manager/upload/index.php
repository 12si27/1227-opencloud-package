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
$curr_page = 'upload';

# 페이지 타이틀
$page_title = '비디오 업로드';

# 현재 경로 유효 여부
$is_valid = true;
$dirr = '';

# 현재 탐색 위치
if ($_GET['d'] != null) {
    $dirr = $_GET['d'];

    # 요청 유효성 검사
    if ($dirr == '') {
        $is_valid = false;
    } elseif ($dirr == '.') {
        $is_valid = false;
    } elseif ($dirr == '..') {
        $is_valid = false;
    } else {
        $chk = strpos($dirr, '../');
        if ($chk !== false and $chk == 0) {
            $is_valid = false;
        }

        $chk = strpos($dirr, '/../');
        if ($chk !== false) {
            $is_valid = false;
        }

        # 쓸데없는 2개 이상 슬래쉬는 허용 X
        $chk = strpos($dirr, '//');
        if ($chk !== false) {
            $is_valid = false;
        }  
    }
    if (!$is_valid) {
        $dirr = '';
    }
}


?>

<!DOCTYPE html>
<html>
<head>
	<!-- 헤더 -->
	<?php require('../src/header.php')?>

</head>

<body>
	<div class="wrapper">
		<!-- 사이드바 -->
		<?php require('../src/sidebar.php')?>

		<div class="main">
			<!-- 네비게이션 -->
			<?php require('../src/navbar.php')?>
			<main class="content">
                <div class="container p-0">

                    <div id="error">
                        
                    </div>


                    <div class="mb-3">
                        <span class="fs-3">비디오 업로드</span>
                    </div>

                    <div class="row">
                        <div class="col-12 col-lg-6">

                            <div class="card">
                                <div class="card-body">
                                    <h3 class="card-title mb-3">업로드 디렉토리</h3>
                                    <input type="text" id="vid_dir" class="form-control" placeholder="디렉토리 입력..." value="<?=$dirr?>" required>
                                    <div class="d-flex flex-row-reverse mt-2">
                                        <a type="button" class="btn btn-secondary" href="../fview?from_upload=1">탐색기에서 선택...</a>
                                    </div>
								</div>
                            </div>

                            <div class="card">
                                <div class="card-body">
                                    <h3 class="card-title mb-3">비디오 파일</h3>
                                    <input type="file" class="form-control" id="vid_file" name="vid_file" accept=".mp4,.webm" required>
								</div>
                            </div>

                            <div class="card">
                                <div class="card-body">
                                    <h3 class="card-title mb-3">비디오 제목</h3>
                                    <input type="text" class="form-control" id="vid_name" name="vid_name" placeholder="(업로드 파일명 그대로 유지)">
								</div>
                            </div>
                            
                        </div>

                        <div class="col-12 col-lg-6">

                            <div class="card">
                                <div class="card-body">
                                    <h3 class="card-title mb-3">자막 파일</h3>
                                    <input type="file" class="form-control" id="cap_file" name="cap_file" accept=".vtt,.srt">
								</div>
                            </div>

                            <div class="card">
                                <div class="card-body">
                                    <h3 class="card-title mb-3">썸네일 파일</h3>
                                    <input type="file" class="form-control" id="thumb_file" name="thumb_file" accept=".jpg,.jpeg">
								</div>
                            </div>
                    
                            <div class="card">
                                <div class="card-body">
                                    <h3 class="card-title mb-3">비디오 DB 설정</h3>

                                    <div class="my-3 form-check form-switch">
                                        <input class="form-check-input" type="checkbox" id="autoDBChk" checked>
                                        <label class="form-check-label" for="autoDBChk">업로드 후 자동으로 DB에 비디오 등록</label>
                                    </div>
								</div>
                            </div>

                            <div class="card">
                                <div class="card-body d-flex justify-content-between">
                                    <div class="align-middle">
                                        <div id="upload-status" hidden>
                                            <progress class="progress-bar" role="progressbar" id="progressBar" value="0" max="100"></progress>
                                            <div id="status" class="fs-6"></div>
                                        </div>
                                    </div>
                                    <button class="btn btn-lg btn-primary" id="uploadBt" onclick="uploadFile()">
                                        <i class="align-middle me-1" data-feather="upload"></i>업로드 시작
                                    </button>
                                </div>
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
    <script src="./js/script.js?rev=0.1"></script>
</body>

</html>