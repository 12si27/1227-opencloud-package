<?php
# =======================================================================================
# =================================== DB 제거 스크립트 ===================================
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

$id = mysqli_real_escape_string($conn, $_POST['id']);

# DB에서 ID에 따른 파일 경로 찾아보기
$query = mysqli_query($conn, "SELECT * FROM videos WHERE id='$id'");

if(mysqli_num_rows($query) > 0) {

    $row = mysqli_fetch_array($query);

    # 시간별 조회수 지우기
    $query = mysqli_query($conn, "DELETE FROM hourly_view WHERE id='$id'");

    if (!$query) {
        header('Location: '.$url.'&delok=-1');
        exit;
    }

    # 비디오 DB 지우기
    $query = mysqli_query($conn, "DELETE FROM videos WHERE id='$id'");

    if (!$query) {
        header('Location: '.$url.'&delok=-2');
        exit;
    }

} else {

    # DB 검색결과에 ID가 없음
    if (!$query) {
        header('Location: '.$url.'&delok=-3');
        exit;
    }

}

# 성공했으므로 뒤로 복구
header('Location: '.$url.'&delok=1');
exit;

?>