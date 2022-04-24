<?php
session_start();

# 로그인이 안돼있다면
if(!isset($_SESSION['userid']))
{
	header ('Location: ../login?ourl='.urlencode($_SERVER[REQUEST_URI]));
	exit();
}

function captionTagPrint($caption) {
    echo '<track kind="subtitles" label="Caption" src="../../player/subscan.php?c='.urlencode($caption).'" srclang="ko" default="">';
}

function urlValuesPrint($vidid, $sq, $page) {
	?> <input hidden name="id" value="<?=$vidid?>">
	<input hidden name="sq" value="<?=$sq?>">
	<input hidden name="page" value="<?=$page?>"> <?php
}

$user_id = $_SESSION['userid'];
$user_name = $_SESSION['username'];


$vidid = $_GET['id'];
$viaexp = ($_GET['viaexp'] == 1); # 탐색기를 통해 왔을 경우
$fail = $_GET['fail'];

# DB, 설정 로드
require('../../src/dbconn.php');
require('../../src/settings.php');
$startloc = '../'.$startloc;

# 현재 페이지 (사이드바 메뉴 출력용)
$curr_page = 'videdit';
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
		.card-body h4 {
			color: #909090;
			margin: 7px 0 3px;
			letter-spacing: 3px;
			font-size: small;
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

<?php
$is_empty = ($vidid == null);

if (!$is_empty) {

	$vidid = mysqli_real_escape_string($conn, $vidid);
	$sql = "SELECT id, views, file_loc, last_checked FROM videos WHERE id = '$vidid'";

	$query = mysqli_query($conn, $sql);

	if (mysqli_num_rows($query) != 1) {
		header('Location: ./?fail=-1');
		exit();
	}

	$vid = mysqli_fetch_array($query);

	$views = $vid['views'];
	$floc = $vid['file_loc'];
	$lastchk = $vid['last_checked'];
	$vidid = $vid['id'];

	$vid_name = substr($floc, strrpos($floc, '/')+1);
	$vid_name = substr($vid_name, 0, strrpos($vid_name, '.'));
	$vid_dir = $startloc.substr($floc, 0, strrpos($floc, '/'));

	$thumblink = $vid_dir.'/.THUMB/'.$vid_name.'.jpg';
	$subloc = $vid_dir.'/.SUB/'.$vid_name;

	$def_thumb = './no_thumb_2.png';
	if (!file_exists($thumblink)) {
		$thumblink = $def_thumb;
	}

	# 영상이 존재하는지 체크 (존재 안해도 DB 있으면 계속하기)
	if (!file_exists($startloc.$floc)) {
		$fail = 18; # 없으면 에러코드 부여
	}

	#vtt(srt) 자막이 존재하는지 체크
	$caption = '';
	$captype = 'N/A';
	$caplang = 'N/A';

	if (file_exists($subloc.'.vtt')) {
		$captype = 'vtt';
		$caption = str_replace($startloc, '', $subloc).'.vtt';
	} elseif (file_exists($subloc.'.srt')) {
		$captype = 'srt';
		$caption = str_replace($startloc, '', $subloc).'.srt';
	}

}
?>
			<main class="content">
                <div class="container p-0">
					<?php

					if ($fail != null) {
						?>
							<svg xmlns="http://www.w3.org/2000/svg" style="display: none;">
								<symbol id="exclamation-triangle-fill" fill="currentColor" viewBox="0 0 16 16">
									<path d="M8.982 1.566a1.13 1.13 0 0 0-1.96 0L.165 13.233c-.457.778.091 1.767.98 1.767h13.713c.889 0 1.438-.99.98-1.767L8.982 1.566zM8 5c.535 0 .954.462.9.995l-.35 3.507a.552.552 0 0 1-1.1 0L7.1 5.995A.905.905 0 0 1 8 5zm.002 6a1 1 0 1 1 0 2 1 1 0 0 1 0-2z"/>
								</symbol>
							</svg>

							<div class="alert text-light d-flex align-items-center p-3 my-3 rounded-3" role="alert" style="background-color: #f14254;">
								<svg class="bi flex-shrink-0 me-2" width="24" height="24" role="img" aria-label="Danger:"><use xlink:href="#exclamation-triangle-fill"/></svg>
								<div>
									<?php
									if ($fail == -1) {
										echo "존재하는 비디오 ID가 아닙니다.";
									} elseif ($fail == 1) {
										echo "바꿀 이름을 입력하여 주세요.";
									} elseif ($fail == 2) {
										echo "이름 바꾸기에 실패하였습니다.";
									} elseif ($fail == 3) {
										echo "변경할 이름의 파일이 이미 존재합니다.";
									} elseif ($fail == 4) {
										echo "자막 추가/변경에 실패하였습니다. (해당 디렉토리 권한을 확인하세요)";
									} elseif ($fail == 5) {
										echo "자막 삭제에 실패하였습니다. (해당 디렉토리 권한을 확인하세요)";
									} elseif ($fail == 6) {
										echo "썸네일 추가/변경에 실패하였습니다. (해당 디렉토리 권한을 확인하세요)";
									} elseif ($fail == 7) {
										echo "썸네일 삭제에 실패하였습니다. (해당 디렉토리 권한을 확인하세요)";
									} elseif ($fail == 8) {
										echo "자막(vtt)이름 바꾸기에 실패하였습니다. (해당 디렉토리 권한을 확인하세요)";
									} elseif ($fail == 9) {
										echo "자막(srt)이름 바꾸기에 실패하였습니다. (해당 디렉토리 권한을 확인하세요)";
									} elseif ($fail == 10) {
										echo "썸네일 이름 바꾸기에 실패하였습니다. (해당 디렉토리 권한을 확인하세요)";
									} elseif ($fail == 11) {
										echo "업로드 파일을 찾을 수 없습니다.";
									} elseif ($fail == 12) {
										echo "업로드 파일이 너무 큽니다.";
									} elseif ($fail == 13) {
										echo "비디오 ID가 DB에 없습니다.";
									} elseif ($fail == 14) {
										echo "파일 업로드에 실패했습니다.";
									} elseif ($fail == 15) {
										echo "자막 파일은 srt 또는 vtt만 업로드 가능합니다.";
									} elseif ($fail == 16) {
										echo "썸네일 파일은 jpg만 업로드 가능합니다.";
									} elseif ($fail == 17) {
										echo "업로드 파일 저장에 실패하였습니다. (해당 디렉토리 권한을 확인하세요)";
									} elseif ($fail == 18) {
										echo "영상을 찾을 수 없습니다. 영상 파일이 지워지지 않았나요?";
									}
									?>
								</div>
							</div>
						<?php
					}


					if (!$is_empty) { ?>
						<div class="d-flex justify-content-between mb-3">
							<span class="fs-3"><b>#<?=$vidid?></b> 비디오 수정</span>
							<button class="btn btn-primary" onclick="<?php 
							if ($viaexp) {
								?> location.href='../fview?d=<?=urlencode(substr($floc, 0, strrpos($floc, '/')))?>' <?php
							} else {
								?> location.href='../videos?squery=<?=$_GET['sq']?>&order=<?=$_GET['order']?>&page=<?=$_GET['page']?>&pid=<?=$vidid?>#video-<?=$vidid?>' <?php
							}
							?>">
								<i class="align-middle me-1" data-feather="arrow-left"></i>돌아가기
							</button>
                   	 	</div>
					<?php } else { ?>
						<div class="d-flex mb-3">
							<span class="fs-3"><b>비디오 ID를 입력하여 주십시오</span>
						</div>
					<?php }

					if (!$is_empty) { ?>

                    <div class="row">
                        <div class="col-12 col-lg-6">
							<div class="card">
                                <div class="card-body">
                                    <h3 class="card-title">비디오 정보</h3>
									<div class="d-flex flex-wrap justify-content-between">
										<div>
											<h4>파일명</h4>
											<h2 class="mb-1"><?=substr($floc, strrpos($floc, '/')+1)?></h2>
										</div>
										<div>
											<h4>비디오 ID</h4>
											<h2 class="mb-1"><?=$vidid?></h2>
										</div>
										<div>
											<h4>조회수</h4>
											<h2 class="mb-1"><?=$views?></h2>
										</div>
										<div>
											<h4>내장 자막</h4>
											<h2 class="mb-1"><?=($caption==''?'없음':$captype)?></h2>
										</div>
										<div>
											<h4>썸네일</h4>
											<h2 class="mb-1"><?=($thumblink==$def_thumb?'없음':'존재')?></h2>
										</div>
										<div>
											<h4>최근 재생</h4>
											<h2 class="mb-1"><?=$lastchk?></h2>
										</div>
									</div>
								</div>
                            </div>

                            <div class="card">
                                <form class="card-body" method="POST" action="./vid_name_change.php">
                                    <h3 class="card-title mb-3">파일명</h3>
									<?=urlValuesPrint($vidid, $_GET['sq'], $_GET['page'])?>
                                    <input type="text" name="name" class="form-control" placeholder="Input" value="<?=$vid_name?>" required>
                                    <div class="d-flex flex-row-reverse mt-2">
                                        <button type="submit" class="btn btn-secondary">저장</button>
                                    </div>
								</form>
                            </div>

                            <div class="card">
                                <div class="card-body">
									<div class="d-flex justify-content-between">
										<h3 class="card-title mb-2">비디오 미리보기</h3>
										<a class="btn btn-sm btn-dark mb-2" href="../../v?id=<?=$vidid?>" target="_blank">
											<i class="align-middle me-1" data-feather="play"></i>플레이어에서 보기
										</a>
									</div>
                                    
                                    <div class="container ratio ratio-16x9" style="padding-left: 0; padding-right: 0;">
                                        <video id="my_video_html5_api" controls oncontextmenu="return false;" controlsList="nodownload">
                                            <source src="<?=$startloc.$floc?>" type='video/mp4' />
                                            <?php if ($caption != '') { captionTagPrint($caption); } ?>
                                        </video>
                                    </div>
                                </div>
                            </div>

                            <div class="card">
                                <div class="card-body">
                                    <h3 class="card-title mb-3">디렉토리</h3>
                                    <input type="text" class="form-control" placeholder="Input" value="<?=str_replace($startloc, '', $vid_dir)?>" disabled>
                                </div>
                            </div>
                        </div>

                        <div class="col-12 col-lg-6">
                            <div class="card">
                                <div class="card-body">
                                    <h3 class="card-title mb-3">자막 (캡션)</h3>
									<div class="input-group">
										<?php if ($caption != '') { ?>
										<a class="btn btn-outline-secondary" type="button" href="<?=$startloc.$caption?>" download="">다운로드</a>
										<?php } ?>
										<input type="text" class="form-control" placeholder="(자막 없음)" value="<?=$caption?>" disabled>
										<form action="./caption_reset.php" method="POST">
											<?=urlValuesPrint($vidid, $_GET['sq'], $_GET['page'])?>
											<button class="btn btn-danger" type="input" id="inputCapFile" <?=($caption==''?'disabled':'')?>>초기화 (제거)</button>
										</form>
									</div>
                                    <form enctype="multipart/form-data" class="input-group mt-2" action="./caption_upload.php" method="POST">
										<?=urlValuesPrint($vidid, $_GET['sq'], $_GET['page'])?>
                                        <input type="file" class="form-control" id="inputCapFile" name="cap_file" required>

										<div class="d-flex justify-content-between">
											<button class="btn btn-outline-secondary" type="submit" id="inputCapFile">적용</button>
										</div>
									</form>
                                </div>
                            </div>

                            <div class="card">
                                <div class="card-body">
                                    <h3 class="card-title mb-3">썸네일 미리보기</h3>
									<img class="container ratio ratio-16x9" style="padding-left: 0; padding-right: 0;" src="<?=$thumblink?>">
                                </div>
                            </div>

                            <div class="card">
                                <div class="card-body">
                                    <h3 class="card-title mb-3">썸네일 (대표사진)</h3>
									<div class="input-group">
										<?php if ($caption != '') { ?>
										<a class="btn btn-outline-secondary" type="button" href="<?=$thumblink?>" download="">다운로드</a>
										<?php } ?>
										<input type="text" class="form-control" placeholder="(썸네일 없음)" value="<?=($thumblink==$def_thumb?'':str_replace($startloc, '', $thumblink))?>" disabled>
										<form action="./thumb_reset.php" method="POST">
											<?=urlValuesPrint($vidid, $_GET['sq'], $_GET['page'])?>
											<button class="btn btn-danger" type="input" id="inputThumbFile" <?=($thumblink==$def_thumb?' disabled':'')?>>초기화 (제거)</button>
										</form>
									</div>
                                    <form enctype="multipart/form-data" class="input-group mt-2" action="./thumb_upload.php" method="POST">
										<?=urlValuesPrint($vidid, $_GET['sq'], $_GET['page'])?>
                                        <input type="file" class="form-control" id="inputThumbFile" name="thumb_file" required>
										<div class="d-flex justify-content-between">
											<button class="btn btn-outline-secondary" type="submit" id="inputThumbFile">적용</button>
										</div>
									</form>
                                </div>
                            </div>

                            
                        </div>
                    </div>

					<?php } else { ?>

					<div class="row">
                        <div class="col-12 col-lg-6">
							<div class="card">
                                <form class="card-body" method="GET" action="./">
									<div class="input-group">
										<input type="text" class="form-control" name="id" placeholder="비디오 ID 입력">
										<button class="btn btn-outline-secondary" type="submit" id="inputThumbFile">조회</button>
									</div>
								</form>
							</div>
						</div>
					</div>
						
						
						
					<?php } ?>

                </div>
			</main>

			<!-- footer 영역 -->
			<?php require('../src/footer.php')?>
		</div>
	</div>

	<script src="../js/app.js"></script>
</body>

</html>