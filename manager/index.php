<?php
session_start();

# 로그인이 안돼있다면
if(!isset($_SESSION['userid']))
{
	header ('Location: ./login');
	exit();
}


$user_id = $_SESSION['userid'];
$user_name = $_SESSION['username'];

require('../src/dbconn.php');

$totalspace = disk_total_space("/var/www/html/1227cloud/Home");
$freespace = disk_free_space("/var/www/html/1227cloud/Home");

# 현재 페이지 (사이드바 메뉴 출력용)
$curr_page = 'index';
?>

<!DOCTYPE html>
<html>
<head>
	<!-- 헤더 -->
	<?php require('./src/header.php')?>
</head>

<body>
	<div class="wrapper">
		<!-- 사이드바 -->
		<?php require('./src/sidebar.php')?>

		<div class="main">
			<!-- 네비게이션 -->
			<?php require('./src/navbar.php')?>

			<!-- 메인 화면 -->
			<main class="content">
				<div class="container-fluid p-0">

					<h1 class="h3 mb-3"><strong>스트리밍</strong> 요약</h1>

					<div class="row">
						<div class="col-sm-6">
							<div class="card flex-fill w-100">
								<div class="card-header">

									<h5 class="card-title mb-0">요일별 시청수 (누적)</h5>
								</div>
								<div class="card-body d-flex w-100">
									<div class="align-self-center chart chart-lg">
										<canvas id="chart-1"></canvas>
									</div>
								</div>
							</div>
						</div>
						<div class="col-sm-6">
							<div class="card flex-fill w-100">
								<div class="card-header">

									<h5 class="card-title mb-0">시간별 시청수 (누적)</h5>
								</div>
								<div class="card-body d-flex w-100">
									<div class="align-self-center chart chart-lg">
										<canvas id="chart-2"></canvas>
									</div>
								</div>
							</div>
						</div>
					</div>

					<div class="row">
						<div class="col-sm-6">
							<div class="card flex-fill w-100">
								<div class="card-header">
									<h5 class="card-title mb-0">최근 24시간 조회수</h5>
								</div>
								<div class="card-body d-flex w-100">
									<div class="align-self-center chart chart-lg">
										<canvas id="chart-3"></canvas>
									</div>
								</div>
							</div>
						</div>
						
					</div>

					<div class="row">
						<div class="col-sm-6">
							<div class="card flex-fill">
								<div class="card-header pb-0">
									<h5 class="card-title mb-0">최근 조회 영상</h5>
								</div>
								<div class="table-responsive d-flex w-100">
									<table class="table table-sm table-hover m-2">
										<thead>
											<tr>
												<th scope="col">VIDID</th>
												<th scope="col">Location</th>
												<th scope="col">Views</th>
												<th scope="col">Last Checked</th>
											</tr>
										</thead>
											<tbody>
												<?php
												$sql = "SELECT id, views, file_loc, last_checked FROM videos";
												$sql .= " ORDER BY last_checked DESC";
												$sql .=" LIMIT 10";
												
												$query = mysqli_query($conn, $sql);

												while($data = mysqli_fetch_array($query)) {
													?>
													<tr onclick="location.href='https://cloud.1227.kr/manager/videdit/?id=<?=$data['id']?>';">
														<th scope="row"><?=$data['id']?></th>
														<td><?=$data['file_loc']?></td>
														<td><?=$data['views']?></td>
														<td><?=$data['last_checked']?></td>
													</tr>
													<?php
												}
												?>
											</tbody>
									</table>
								</div>
							</div>
						</div>
						<div class="col-sm-6">
							<div class="card flex-fill w-100">
								<div class="card-header pb-0">
									<h5 class="card-title">최근 7일 최다 시청</h5>
								</div>
								<div class="table-responsive d-flex w-100">
									<table class="table table-sm table-hover m-2" style="overflow: hidden;">
										<thead>
											<tr>
												<th scope="col">VIDID</th>
												<th scope="col">Location</th>
												<th scope="col">Views</th>
											</tr>
										</thead>
											<tbody>
												<?php 
												$sql = "SELECT id, sum, file_loc FROM ( SELECT id, SUM(views) AS sum FROM `hourly_view` WHERE DATE BETWEEN DATE_ADD(NOW(), INTERVAL -1 WEEK) AND NOW() GROUP BY id ORDER BY sum DESC LIMIT 15 ) a NATURAL JOIN ( SELECT file_loc, id FROM videos ) b";

												$query = mysqli_query($conn, $sql);
												while($data = mysqli_fetch_array($query)) {
													?>
													<tr onclick="location.href='https://cloud.1227.kr/manager/videdit/?id=<?=$data['id']?>';">
														<th scope="row"><?=$data['id']?></th>
														<td><?=$data['sum']?></td>
														<td><?=$data['file_loc']?></td>
													</tr>
													<?php
												}
												?>
											</tbody>
									</table>
								</div>
							</div>
						</div>
					</div>


					<h1 class="h3 mb-3"><strong>스토리지</strong> 요약</h1>

					<div class="row">
						<div class="col-12 col-md-6 col-xxl-3 d-flex order-2 order-xxl-3">
							<div class="card flex-fill w-100">
								<div class="card-header">

									<h5 class="card-title mb-0">디스크 사용 현황</h5>
								</div>
								<div class="card-body d-flex">
									<div class="align-self-center w-100">
										<div class="py-3">
											<div class="chart chart-xs"><div class="chartjs-size-monitor"><div class="chartjs-size-monitor-expand"><div class=""></div></div><div class="chartjs-size-monitor-shrink"><div class=""></div></div></div>
												<canvas id="chart-capacity" width="376" height="250" style="display: block; height: 200px; width: 301px;" class="chartjs-render-monitor"></canvas>
											</div>
										</div>

										<table class="table mb-0">
											<tbody>
												<tr>
													<td>사용 중</td>
													<td class="text-end"><?=round(($totalspace-$freespace)/1024/1024/1024,2)?>GB (<?= number_format(round(($totalspace-$freespace)/1024/1024,1))?>MB)</td>
												</tr>
												<tr>
													<td>여유 공간</td>
													<td class="text-end"><?=round(($freespace)/1024/1024/1024,1)?>GB (<?=number_format(round(($freespace)/1024/1024,2))?>MB)</td>
												</tr>
												<tr>
													<td>총합</td>
													<td class="text-end"><?=round(($totalspace)/1024/1024/1024,1)?>GB (<?=number_format(round(($totalspace)/1024/1024,2))?>MB)</td>
												</tr>
											</tbody>
										</table>
									</div>
								</div>
							</div>
						</div>
					</div>
					

				</div>
			</main>

			<!-- footer 영역 -->
			<?php require('./src/footer.php')?>
		</div>
	</div>

	<script src="js/app.js"></script>

	<script>

		// 요일별 시청수

		<?php
		$sql = 'SELECT dayofweek(date) as d, sum(views) as s FROM `hourly_view` GROUP BY d';
		$q = mysqli_query($conn, $sql);
		$result = array();
		while($data = mysqli_fetch_array($q)) {
			array_push($result, $data['s']);
		}
		?>

		document.addEventListener("DOMContentLoaded", function() {
			// Bar chart
			new Chart(document.getElementById("chart-1"), {
				type: "bar",
				data: {
					labels: ['일','월','화','수','목','금','토'],
					datasets: [{
						label: "시청수",
						backgroundColor: window.theme.primary,
						borderColor: window.theme.primary,
						hoverBackgroundColor: window.theme.primary,
						hoverBorderColor: window.theme.primary,
						data: [<?=join(',',$result)?>],
						barPercentage: .75,
						categoryPercentage: .5
					}]
				},
				options: {
					maintainAspectRatio: false,
					legend: {
						display: false
					},
				}
			});
		});


		// 시간별 시청수

		<?php
		$sql = 'SELECT hour, sum(views) as s FROM `hourly_view` GROUP BY hour';
		$q = mysqli_query($conn, $sql);
		$result = array();
		$result2 = array();
		while($data = mysqli_fetch_array($q)) {
			array_push($result, $data['hour']);
			array_push($result2, $data['s']);
		}
		?>

		document.addEventListener("DOMContentLoaded", function() {
			// Bar chart
			new Chart(document.getElementById("chart-2"), {
				type: "line",
				data: {
					labels: [<?=join(',',$result)?>],
					datasets: [{
						label: "시청수",
						backgroundColor: window.theme.primary,
						borderColor: window.theme.primary,
						hoverBackgroundColor: window.theme.primary,
						hoverBorderColor: window.theme.primary,
						data: [<?=join(',',$result2)?>],
						barPercentage: .75,
						categoryPercentage: .5
					}]
				},
				options: {
					maintainAspectRatio: false,
					legend: {
						display: false
					},
				}
			});
		});


		// 최근 24시간 시청수

		<?php
		$sql = 'SELECT date, hour, sum(views) as sum FROM `hourly_view` GROUP BY date, hour ORDER BY date DESC, hour DESC LIMIT 24';

		/* 출력예시:
		date		hour	sum(views)
		2022-05-30	17		32
		2022-05-30	16		4
		2022-05-30	15		9
		2022-05-30	11		2
		2022-05-30	10		1
		*/

		$q = mysqli_query($conn, $sql);
		$day_date = array();
		$day_hour = array();
		$day_sum = array();

		$day_curr_date = '';
		$day_curr_hour = -1;

		while ($data = mysqli_fetch_array($q)) {

			# 맨 처음 접근이 아닐때
			if ($day_curr_hour != -1) {

				# 현재 불러온 시간값에 1 더한거 불러오기
				$day_tmp_hour = $data['hour'] + 1;

				# 만약 1을 더했는데도 이전 시작보다 작은 경우
				# -> 차이가 -1보다 더 크기때문에 빈 0값을 계속 어레이에 추가 (빈 공간 메꾸기)
				while ($day_tmp_hour < $day_curr_hour) {
					$day_curr_hour -= 1;

					array_push($day_date, $day_curr_date);
					array_push($day_hour, $day_curr_hour);
					array_push($day_sum, 0);

				}
			}

			$day_curr_hour = $data['hour'];
			$day_curr_date = $data['date'];

			array_push($day_date, $day_curr_date);
			array_push($day_hour, $day_curr_hour);
			array_push($day_sum, $data['sum']);

			if (count($day_date) > 24) {
				break;
			}
		}

		$day_label = array();
		$day_data = array();
		for ($i=count($day_date)-1; $i>=0; $i--) {
			$newdate = substr($day_date[$i], strpos($day_date[$i], '-')+1).' '.$day_hour[$i];
			array_push($day_label, $newdate);
			array_push($day_data, $day_sum[$i]);
		}

		?>

		document.addEventListener("DOMContentLoaded", function() {
			// Bar chart
			new Chart(document.getElementById("chart-3"), {
				type: "bar",
				data: {
					labels: ["<?=join('","',$day_label)?>"],
					datasets: [{
						label: "시청수",
						backgroundColor: window.theme.primary,
						borderColor: window.theme.primary,
						hoverBackgroundColor: window.theme.primary,
						hoverBorderColor: window.theme.primary,
						data: [<?=join(',',$day_data)?>],
						barPercentage: .75,
						categoryPercentage: .5
					}]
				},
				options: {
					maintainAspectRatio: false,
					legend: {
						display: false
					},
				}
			});
		});


		// 여유 공간

		document.addEventListener("DOMContentLoaded", function() {
			// Pie chart
			new Chart(document.getElementById("chart-capacity"), {
				type: "pie",
				data: {
					labels: ["사용 중", "여유 공간"],
					datasets: [{
						data: [<?=$totalspace-$freespace?>, <?=$freespace?>],
						backgroundColor: [
							window.theme.warning,
							window.theme.primary
						],
						borderWidth: 3
					}]
				},
				options: {
					responsive: !window.MSInputMethodContext,
					maintainAspectRatio: false,
					legend: {
						display: false
					},
					cutoutPercentage: 75
				}
			});
		});
	</script>


</body>

</html>