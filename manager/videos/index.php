<?php
session_start();

# 로그인이 안돼있다면
if(!isset($_SESSION['userid']))
{
	header ('Location: ../login?ourl='.urlencode($_SERVER[REQUEST_URI]));
	exit();
}

function urlenc_wos($url) {
	return str_replace('%2F','/',rawurlencode($url));
}

$user_id = $_SESSION['userid'];
$user_name = $_SESSION['username'];

# DB, 설정 로드
require('../../src/dbconn.php');
require('../../src/settings.php');
$startloc = '../'.$startloc;

# 현재 페이지 (사이드바 메뉴 출력용)
$curr_page = 'videos';

# 페이지 타이틀
$page_title = '비디오 목록';
?>

<!DOCTYPE html>
<html>
<head>
	<!-- 헤더 -->
	<?php require('../src/header.php')?>
	<link href="./css/style.css" rel="stylesheet">

</head>

<body>
	<div class="wrapper">
		<!-- 사이드바 -->
		<?php require('../src/sidebar.php')?>

		<div class="main">
			<!-- 네비게이션 -->
			<?php require('../src/navbar.php')?>

			<!-- 비디오 목록 출력 -->
<?php


### 우선 총합 구하기 ###
$sql = "SELECT count(*) as c FROM videos";

### 서치쿼리 있는지 확인 ###
if ($_GET['squery'] != null) {
	$sq = mysqli_real_escape_string($conn, $_GET['squery']);
	$sql .= " WHERE file_loc LIKE \"%$sq%\"";
}

$query = mysqli_query($conn, $sql);
$totalcount = mysqli_fetch_array($query)['c'];
$itemlimit = 24;

$totalpages = (int)($totalcount / $itemlimit) + 1;
if (is_numeric($_GET['page'])) {
	$pg = $_GET['page'];
} else {
	$pg = 1;
}

$startcount = $itemlimit * ($pg - 1);


$order = $_GET['order'];

if ($order == null) {
	$order = 1;
} elseif (!($order >= 1 and $order <= 5)) {
	$order = 1;
}


$sql = "SELECT id, views, file_loc, last_checked FROM videos";

if ($_GET['squery'] != null) {
	$sql .= " WHERE file_loc LIKE \"%$sq%\"";
}

$sql .= " ORDER BY ";

switch ($order) {
	case 1:
		$sql .= "last_checked DESC";
		break;
	case 2:
		$sql .= "last_checked";
		break;
	case 3:
		$sql .= "views DESC";
		break;
	case 4:
		$sql .= "views";
		break;
	case 5:
		$sql .= "file_loc";
		break;
	case 6:
		$sql .= "id";
		break;
	default:
		$sql .= "last_checked DESC";
}

$sql .=" LIMIT $startcount, $itemlimit";



$query = mysqli_query($conn, $sql);
$vid_id = array();
$vid_loc = array();
$vid_views = array();
$vid_ctime = array();

while($vid = mysqli_fetch_array($query)) {
	array_push($vid_id, $vid['id']);
	array_push($vid_loc, $vid['file_loc']);
	array_push($vid_views, $vid['views']);
	array_push($vid_ctime, $vid['last_checked']);
}

?>
			<main class="content">
				<div class="container-fluid p-0">

					<div class="d-flex justify-content-between">
						<span class="fs-3 mb-3"><b>비디오 목록</b> <small class="text-secondary">총 <?=$totalcount?>개</small></span>
						<form class="col-6" name="searchForm" style="max-width: 400px;" method="GET">
							<div class="input-group pb-3">
								<select class="form-select" style="max-width: 130px;" name="order" onchange="javascript:document.searchForm.submit();">
									<option <?php if ($order==1) { echo "selected"; } ?> value="1">최근 본 순</option>
									<option <?php if ($order==2) { echo "selected"; } ?> value="2">오래전 본 순</option>
									<option <?php if ($order==3) { echo "selected"; } ?> value="3">조회수 (내림차)</option>
									<option <?php if ($order==4) { echo "selected"; } ?> value="4">조회수 (오름차)</option>
									<option <?php if ($order==5) { echo "selected"; } ?> value="5">이름순</option>
									<option <?php if ($order==6) { echo "selected"; } ?> value="6">ID순</option>
								<input type="text" name="squery" class="form-control" placeholder="비디오 검색..." value="<?=$_GET['squery']?>">
								<button class="btn btn-secondary" type="submit" id="button-addon2"><i data-feather="search"></i></button>
							</div>
						</form>
					</div>

					<?php # 삭제 작업후 알림, 에러 출력

					$delok = $_GET['delok'];

					if ($delok != null) {

						$title = '';
						$cmt = '';

						if ($delok > 0) {
							# 체크 아이콘 (성공)
							$color = '64b899';
							$icon = '<path d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0zm-3.97-3.03a.75.75 0 0 0-1.08.022L7.477 9.417 5.384 7.323a.75.75 0 0 0-1.06 1.06L6.97 11.03a.75.75 0 0 0 1.079-.02l3.992-4.99a.75.75 0 0 0-.01-1.05z"/>';
						} else {
							# 느낌표 아이콘 (오류)
							$color = 'f14254';
							$icon = '<path d="M8.982 1.566a1.13 1.13 0 0 0-1.96 0L.165 13.233c-.457.778.091 1.767.98 1.767h13.713c.889 0 1.438-.99.98-1.767L8.982 1.566zM8 5c.535 0 .954.462.9.995l-.35 3.507a.552.552 0 0 1-1.1 0L7.1 5.995A.905.905 0 0 1 8 5zm.002 6a1 1 0 1 1 0 2 1 1 0 0 1 0-2z"/>';
						}

						switch ($delok) {
							case 1:
								$title = '비디오 DB 삭제 완료';
								$cmt = '다시 생성을 원할 경우 탐색기에서 비디오를 열거나 플레이어로 영상을 재생해 주세요.';
								break;
							case 2:
								$title = '비디오 전체 삭제 완료';
								$cmt = '해당 비디오 파일과 DB가 모두 삭제되었습니다.';
								break;
							case -1:
								$title = '시간별 조회수 DB 삭제 실패';
								$cmt = '해당 테이블이 없거나 DB에 문제가 있을 수 있습니다. DB 확인 후 다시 시도해 주세요.';
								break;
							case -2:
								$title = '비디오 DB 삭제 실패';
								$cmt = '비디오 테이블이 없거나 DB에 문제가 있을 수 있습니다. DB 확인 후 다시 시도해 주세요.';
								break;
							case -3:
								$title = '존재하지 않는 비디오 ID';
								$cmt = 'DB에서 해당 비디오 ID를 찾을 수 없었습니다. 이미 삭제되었을 수도 있습니다.';
								break;
						}

						if ($title != '' AND $cmt != '') {
							?>
							<div class="alert text-light d-flex justify-content-between p-3 my-3 rounded-3" role="alert" style="background-color: #<?=$color?>;">
								<span class="d-flex">					
									<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor" class="bi bi-exclamation-triangle-fill flex-shrink-0 me-2" viewBox="0 0 16 16" role="img" aria-label="Warning:">
										<?=$icon?>
									</svg>
									<div><b><?=$title?></b></br><?=$cmt?></div>
								</span>
								<button type="button" class="btn-close btn-close-white align-middle" data-bs-dismiss="alert" aria-label="Close"></button>
							</div>
							<?php
						}
					}

					?>

					

					<div class="row row-cols-1 row-cols-xl-2 row-cols-xxl-3 g-4 pb-4" style="overflow: auto; overflow-y: hidden;">
					<?php

					for ($i=0; $i<count($vid_id); $i++) {

						$vid_name = substr($vid_loc[$i], strrpos($vid_loc[$i], '/')+1);
						$vid_name = substr($vid_name, 0, strrpos($vid_name, '.'));
						$thumblink = $startloc.substr($vid_loc[$i], 0, strrpos($vid_loc[$i], '/')).'/.THUMB/'.$vid_name.'.jpg';

						# 동년일 경우 년수는 지움 처리
						$vid_ctime_cut = (strpos($vid_ctime[$i], date('Y').'-') === 0 ? substr($vid_ctime[$i], 5) : $vid_ctime[$i]);

						if (!file_exists($thumblink)) {
							$thumblink = './no_thumb.png';
						}

						?>

						<div class="col">
							<div id="video-<?=$vid_id[$i]?>" class="card vid-card m-n1" onclick="location.href='../videdit?id=<?=$vid_id[$i]?><?=($sq!=''?'&sq='.$sq:'')?><?=($pg!=''?'&page='.$pg:'')?><?=($order!=''?'&order='.$order:'')?>'"
								 style="min-width: 350px; max-height: 113px;<?=($_GET['pid']==$vid_id[$i]?' background-color: #fcf4bf;':'')?>">
								<div class="d-flex">
									<div class="ratio ratio-16x9" style="width: 200px; min-width: 200px; height: 113px;">
										<img src="<?=urlenc_wos($thumblink)?>" class="rounded-start">
									</div>
									<div class="d-flex align-items-center mx-3">
										<div class="d-flex flex-column">
											<div class="card-title mb-0 ell" style="max-height: 50px;"><?=$vid_name?></div>
											<div class="card-text" style="font-size: small;">조회수 <?=$vid_views[$i]?> · 최근 열람 <?=$vid_ctime_cut?></div>
										</div>
									</div>
								</div>
							</div>
						</div>

					<?php
					}	
					?>
					</div>
				</div>


				<!-- 페이지 버튼 영역 -->

                <div class="d-flex justify-content-center mt-4">
					<div class="btn-group">
						<a class="btn btn-secondary" href="?page=1<?php
						if ($_GET['squery']!='') { echo "&squery=".$_GET['squery']; }
						if ($_GET['order']!='') { echo "&order=".$_GET['order']; }
						?>" aria-label="First">
							<span aria-hidden="true">&laquo;</span>
							<span class="sr-only">처음</span>
						</a>

						<?php
							
							$i = 0;
							$pg = (int)$pg;

							if ($pg < 4) {
								while($i < 5) {
									if (!(($i + 1) > $totalpages) or $i==0) {
										echo "<a class=\"btn btn-secondary";
										if (($i + 1) == $pg) { echo " active"; }
										echo "\" href=\"?page=".($i + 1);
										if ($_GET['squery']!='') { echo "&squery=".$_GET['squery']; }
										if ($_GET['order']!='') { echo "&order=".$_GET['order']; }
										echo "\">";
										echo ($i + 1);
										echo "</a>";
									}
									$i++;
								}

							} else {
								while($i < 5) {
									if (!(($pg + $i - 2) > $totalpages) or $i==0) {
										echo "<a class=\"btn btn-secondary";
										if (($pg + $i - 2) == $pg) { echo " active"; }
										echo "\" href=\"?page=".($pg + $i - 2);
										if ($_GET['squery']!='') { echo "&squery=".$_GET['squery']; }
										if ($_GET['order']!='') { echo "&order=".$_GET['order']; }
										echo "\">";
										echo ($pg + $i - 2);
										echo "</a>";
									}

									$i++;
								}
							}

						?>
						<a class="btn btn-secondary" href="<?php echo "?page=".$totalpages;
							if ($_GET['squery']!='') { echo "&squery=".$_GET['squery']; } ?>" aria-label="Next">
							<span aria-hidden="true">&raquo;</span>
							<span class="sr-only">끝</span>
						</a>
					</div>
                </div>

			</main>

			<!-- footer 출력 -->
			<?php require('../src/footer.php')?>
		</div>
	</div>

	<script src="../js/app.js"></script>
</body>

</html>