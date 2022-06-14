<?php
# =======================================================================================
# =============================== 비디오 잠금 설정 스크립트 ===============================
# =======================================================================================

session_start();

# 로그인이 안돼있다면
if(!isset($_SESSION['userid'])) {
	header ('Location: ../login?ourl='.urlencode($_SERVER[REQUEST_URI]));
	exit();
}

# 세션 체크 - 관리자 및 모더 허용
require_once('../src/session.php');
sess_check(array('admin', 'mod'));

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

$id = mysqli_real_escape_string($conn, $id);
$query = mysqli_query($conn, "SELECT * FROM videos WHERE id='$id'");

$pass_key = $_POST['pass_key'];
$active = $_POST['pass_key_active'];

if ($active == '1') {
    $active = '1';
} else {
    $active = '0';
}


if ($pass_key == null AND $active == '1') {
    # 키값 비어있고 active == 1일때
    header('Location: '.$url.'&fail=20');
    exit();
} else {
    $pass_key = mysqli_real_escape_string($conn, $pass_key);
}

# DB에 비디오ID가 있으면
if(mysqli_num_rows($query) == 1) {

    # 이미 잠금 테이블이 있나 확인
    $query = mysqli_query($conn, "SELECT id, active, vid_key, allowed_user FROM locked WHERE id = '$id'");

    # 이미 있을 경우
    if (mysqli_num_rows($query) > 0) {
        $sql = "UPDATE `locked` SET `vid_key` = '$pass_key', `active` = '$active' WHERE `locked`.`id` = '$id'";

    # 없을 경우
    } else {

        # 만약 활성화일때 -> 새로 생성
        if ($active == '1') {
            $sql = "INSERT INTO `locked` (`id`, `active`, `vid_key`, `allowed_user`) VALUES ('$id', '$active', '$pass_key', NULL)";
        
        # 만약 비활성화일때 -> 아무것도 안함
        } else {
            # 그냥 돌아가기
            header('Location: '.$url);
            exit();
        }
    }

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