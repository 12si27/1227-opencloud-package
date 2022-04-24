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
$curr_page = 'videos';
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

		.vid-card
		{
			box-shadow: 0 0 0.5rem rgba(0,0,0,.15);
			cursor: pointer;
			transition: transform .2s;
			position: relative; 
		}

		.vid-card:hover {
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
						<h1 class="h3 mb-3">비디오 목록 <small class="text-secondary">총 <?=$totalcount?>개</small></h1>
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
					

					<div class="row row-cols-1 row-cols-xl-2 row-cols-xxl-3 g-4 pb-4" style="overflow: auto; overflow-y: hidden;">
					<?php

					for ($i=0; $i<count($vid_id); $i++) {

						$vid_name = substr($vid_loc[$i], strrpos($vid_loc[$i], '/')+1);
						$vid_name = substr($vid_name, 0, strrpos($vid_name, '.'));
						$thumblink = $startloc.substr($vid_loc[$i], 0, strrpos($vid_loc[$i], '/')).'/.THUMB/'.$vid_name.'.jpg';

						if (!file_exists($thumblink)) {
							$thumblink = './no_thumb.png';
						}

						?>

						<div class="col">
							<div id="video-<?=$vid_id[$i]?>" class="card vid-card m-n1" onclick="location.href='../videdit?id=<?=$vid_id[$i]?><?=($sq!=''?'&sq='.$sq:'')?><?=($pg!=''?'&page='.$pg:'')?><?=($order!=''?'&order='.$order:'')?>'"
								 style="min-width: 350px; max-height: 113px;<?=($_GET['pid']==$vid_id[$i]?' background-color: #fcf4bf;':'')?>">
								<div class="d-flex">
									<div class="ratio ratio-16x9" style="width: 200px; min-width: 200px; height: 113px;">
										<img src="<?=$thumblink?>" class="rounded-start">
									</div>
									<div class="d-flex align-items-center mx-3">
										<div class="d-flex flex-column">
											<div class="card-title mb-0 ell" style="max-height: 50px;"><?=$vid_name?></div>
											<div class="card-text" style="font-size: small;">조회수 <?=$vid_views[$i]?></br>최근 열람 <?=$vid_ctime[$i]?></div>
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