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

function urlValuesPrint() {
	global $vidid, $sq, $page, $order, $viaexp;
	?> <input hidden id="vidid" name="id" value="<?=$vidid?>">
	<input hidden id="sq" name="sq" value="<?=$sq?>">
	<input hidden id="page" name="page" value="<?=$page?>">
	<input hidden id="order" name="order" value="<?=$order?>"> <?php
	if ($viaexp) { ?> <input hidden id="viaexp" name="viaexp" value="1"> <?php }
}

function urlenc_wos($url) {
	return str_replace('%2F','/',rawurlencode($url));
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

# 페이지 타이틀
$page_title = '비디오 수정';
?>

<!DOCTYPE html>
<html>
<head>
	<!-- 헤더 -->
	<?php require('../src/header.php')?>

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
	$vid_ext = substr($vid_name, strrpos($vid_name, '.')+1);

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
	} else {
		# 스트림 URL 지정
		$stream_url = './video_prev_stream.php?video='.urlencode($floc).'&t='.filemtime($startloc.$floc);
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


	# 잠금 영상인지 체크
    $query = mysqli_query($conn, "SELECT id, active, vid_key, allowed_user FROM locked WHERE id = '$vidid'");

    # 잠금 비디오일 경우
    if (mysqli_num_rows($query) > 0) {
		$query = mysqli_fetch_array($query);
		$pass_key = $query['vid_key'];
		$pass_key_active = $query['active'];
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
									} elseif ($fail == 19) {
										echo "영상 업로드는 성공하였으나 자막 또는 썸네일이 정상적으로 적용되지 않았습니다. 수동으로 적용해 주시기 바랍니다.";
									} elseif ($fail == 20) {
										echo "설정할 키 값을 입력해 주십시오.";
									} elseif ($fail == 21) {
										echo "영상 잠금 설정에 실패하였습니다. (키 값이 너무 길 수도 있습니다)";
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
									<?=urlValuesPrint()?>
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
                                        <video id="my_video" controls>
                                            <source src="<?=$stream_url?>" type='video/mp4' />
                                            <?php if ($caption != '') { captionTagPrint($caption); } ?>
                                        </video>
                                    </div>
                                </div>
                            </div>

                            <div class="card">
                                <div class="card-body">
                                    <h3 class="card-title mb-3">비디오</h3>
									<div id="error"></div>
									<div class="input-group">
										<?php if ($fail != 18) { ?>
										<a class="btn btn-outline-secondary" type="button" href="<?=urlenc_wos($startloc.$floc)?>" download="">다운로드</a>
										<?php } ?>
										<input type="text" class="form-control" placeholder="(비디오 없음)" value="<?=$floc?>" disabled>
									</div>
                                    <div class="input-group mt-2">
										<?=urlValuesPrint()?>
                                        <input type="file" class="form-control" id="vid_file" name="vid_file" accept=".<?=$vid_ext?>" required>

										<div class="d-flex justify-content-between">
											<button class="btn btn-outline-secondary" type="submit" id="uploadBt" onclick="uploadFile()">바꾸기</button>
										</div>
									</div>
									<div hidden id="upload-status">
										<div class="d-flex justify-content-end mt-2">
											<div>
												<progress class="progress-bar" role="progressbar" id="progressBar" value="0" max="100"></progress>
												<div id="status" class="fs-6 text-end"></div>
											</div>
										</div>										
									</div>
                                </div>
                            </div>
                        </div>

                        <div class="col-12 col-lg-6">
                            <div class="card">
                                <div class="card-body">
                                    <h3 class="card-title mb-3">자막 (캡션)</h3>
									<div class="input-group">
										<?php if ($caption != '') { ?>
										<a class="btn btn-outline-secondary" type="button" href="<?=urlenc_wos($startloc.$caption)?>" download="">다운로드</a>
										<?php } ?>
										<input type="text" class="form-control" placeholder="(자막 없음)" value="<?=$caption?>" disabled>
										<form action="./caption_reset.php" method="POST">
											<?=urlValuesPrint()?>
											<button class="btn btn-danger" type="input" id="inputCapFile" <?=($caption==''?'disabled':'')?>>초기화 (제거)</button>
										</form>
									</div>
                                    <form enctype="multipart/form-data" class="input-group mt-2" action="./caption_upload.php" method="POST">
										<?=urlValuesPrint()?>
                                        <input type="file" class="form-control" id="inputCapFile" name="cap_file" accept=".vtt,.srt" required>

										<div class="d-flex justify-content-between">
											<button class="btn btn-outline-secondary" type="submit" id="inputCapFile">적용</button>
										</div>
									</form>
                                </div>
                            </div>

                            <div class="card">
                                <div class="card-body">
                                    <h3 class="card-title mb-3">썸네일 (대표사진)</h3>
									<div class="input-group">
										<?php if ($thumblink != $def_thumb) { ?>
										<a class="btn btn-outline-secondary" type="button" href="<?=$thumblink?>" download="">다운로드</a>
										<?php } ?>
										<input type="text" class="form-control" placeholder="(썸네일 없음)" value="<?=($thumblink==$def_thumb?'':str_replace($startloc, '', $thumblink))?>" disabled>
										<form action="./thumb_reset.php" method="POST">
											<?=urlValuesPrint()?>
											<button class="btn btn-danger" type="input" id="inputThumbFile" <?=($thumblink==$def_thumb?' disabled':'')?>>초기화 (제거)</button>
										</form>
									</div>
                                    <form enctype="multipart/form-data" class="input-group mt-2" action="./thumb_upload.php" method="POST">
										<?=urlValuesPrint()?>
                                        <input type="file" class="form-control" id="inputThumbFile" name="thumb_file"  accept=".jpg,.jpeg" required>
										<div class="d-flex justify-content-between">
											<button class="btn btn-outline-secondary" type="submit" id="inputThumbFile">적용</button>
										</div>
									</form>
                                </div>
                            </div>

							<div class="card">
                                <div class="card-body">
                                    <h3 class="card-title mb-3">썸네일 미리보기</h3>
									<img class="container ratio ratio-16x9" style="padding-left: 0; padding-right: 0;" src="<?=urlenc_wos($thumblink)?>">
                                </div>
                            </div>

							<div class="card">
                                <div class="card-body">
									<div class="d-flex justify-content-between">
										<h3 class="card-title mb-2">디렉토리</h3>
										<a class="btn btn-sm btn-primary mb-2" href="../fview?d=<?=urlenc_wos(str_replace($startloc, '', $vid_dir))?>">
											<i class="align-middle me-1" data-feather="folder"></i>탐색기로 이동
										</a>
									</div>
                                    <input type="text" class="form-control" placeholder="Input" value="<?=str_replace($startloc, '', $vid_dir)?>" disabled>
                                </div>
                            </div>

							<div class="card">
                                <form class="card-body" method="POST" action="./vid_lock_set.php">
                                    <h3 class="card-title mb-3">비디오 잠금 설정</h3>
									<?=urlValuesPrint()?>
									<div class="form-check me-3">
										<input class="form-check-input" onchange="vidLockChkChange();" type="checkbox" name="pass_key_active" value="1" id="pass_key_active" <?=($pass_key_active=='1'?'checked':'')?>>
										<label class="form-check-label" for="pass_key_active">비디오 잠금 활성화</label>
									</div>
                                    <div class="input-group mt-2">
                                        <input type="text" placeholder="여기에 키 값 입력" class="form-control" id="pass_key"
										name="pass_key" value="<?=htmlentities($pass_key)?>" required <?=($pass_key_active=='1'?'':'disabled')?>>
										<button class="btn btn-outline-secondary" type="submit" id="uploadBt">적용</button>
									</div>
								</form>
                            </div>

							<div class="card">
                                <div class="card-body">
                                    <h3 class="card-title mb-3">비디오 · DB 삭제</h3>

									<div class="d-flex justify-content-end">
										<button class="btn btn-outline-danger ms-2" type="button" id="delVidBt" onclick="deleteVideo(true);">비디오 파일 삭제</button>									
										<form action="./db_reset.php" method="POST">
											<?=urlValuesPrint()?>
											<button class="btn btn-outline-danger ms-2" type="submit" id="delDBBt">DB 데이터 삭제</button>
										</form>
										<button class="btn btn-danger ms-2" type="button" id="delAllBT" onclick="deleteVideo(false);">전체 삭제</button>
									</div>
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
	<script src="./js/script.js"></script>
	<script>
		function vidLockChkChange() {
			if (document.getElementById('pass_key_active').checked) {
				document.getElementById('pass_key').disabled = false;
			} else {
				document.getElementById('pass_key').disabled = true;
			}
		}
	</script>
</body>

</html>