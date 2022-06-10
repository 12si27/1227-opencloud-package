<?php
# =======================================================================================
# =============================== 비디오 잠금 설정 스크립트 ===============================
# =======================================================================================

session_start();

# 로그인이 안돼있다면
if(!isset($_SESSION['userid']))
{
	header ('Location: ../login');
	exit();
}

# DB, 설정 로드
require('../../src/dbconn.php');
require('../../src/settings.php');
$startloc = '../'.$startloc;

# 이건 사용자 편의상 설정 보존하려고 두는 변수들임
$id = $_POST['id'];

# 탐색기를 통해 온 경우
if ($_POST['viaexp'] == '1') {
    $url = './?id='.$id.'&viaexp=1';

# 비디오 목록에서 온 경우
} else {
    $sq = $_POST['sq'];
    $page = $_POST['page'];
    $order = $_POST['order'];
    $url = './?id='.$id.'&sq='.$sq.'&page='.$page.'&order='.$order;
}



$credit_time = $_POST['credit_time'];

# 숫자꼴이 아니면 바로아웃
if (!is_numeric($credit_time)) {
    exit;
} 

$credit_time = mysqli_real_escape_string($conn, $credit_time);

$id = mysqli_real_escape_string($conn, $id);
$query = mysqli_query($conn, "SELECT * FROM videos WHERE id='$id'");


# DB에 비디오ID가 있으면
if(mysqli_num_rows($query) == 1) {

    # 이미 잠금 테이블이 있나 확인
    $sql = "UPDATE `videos` SET `credit_time` = '$credit_time' WHERE `videos`.`id` = '$id'";
    $query = mysqli_query($conn, $sql);

    if ($query) {
        # 쿼리쏘기 OK
        header('Location: '.$url);
        exit();
    } else {
        # 쿼리쏘기 NG
        header('Location: '.$url.'&fail=21');
        exit();
    }

} else {
    # DB 검색결과에 ID가 없음
    header('Location: '.$url.'&fail=13');
    exit();
}

?>