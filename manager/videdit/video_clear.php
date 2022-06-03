<?php
# =======================================================================================
# =============================== 비디오 전체제거 스크립트 ===============================
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

$vidonly = ($_POST['video_only'] == 1); # 비디오만 지울 경우

$id = mysqli_real_escape_string($conn, $_POST['id']);

# DB에서 ID에 따른 파일 경로 찾아보기
$query = mysqli_query($conn, "SELECT * FROM videos WHERE id='$id'");

if(mysqli_num_rows($query) > 0) {

    $row = mysqli_fetch_array($query);
    $file_loc = $row['file_loc'];

    # 파일 정보 수집
    $fdir = substr($file_loc, 0, strrpos($file_loc, '/'));          # 경로 (EX: RegularShow/S2)
    $fname = substr($file_loc, strrpos($file_loc, '/') + 1);        # 파일 이름 (EX: Regular Show - S02E25 첫날 (First Day) [LQ] [480p].mp4)
    $fname_noext = substr($fname, 0, strrpos($fname, '.'));         # 파일 이름 (확장자X) (EX: Regular Show - S02E25 첫날 (First Day) [LQ] [480p])

    if (!$vidonly) {
    
        # 시간별 조회수 지우기
        $query = mysqli_query($conn, "DELETE FROM hourly_view WHERE id='$id'");

        # 키값 지우기
        $query = mysqli_query($conn, "DELETE FROM locked WHERE id='$id'");

        # 비디오 DB 지우기
        $query = mysqli_query($conn, "DELETE FROM videos WHERE id='$id'");

        if (!$query) {
            header('Location: '.$url.'&delok=-2');
            exit;
        }

    }

    # 비디오 파일 지우기
    if(file_exists($startloc.$file_loc)) 
    {
        chmod($startloc.$file_loc,0755); 
        if (!unlink($startloc.$file_loc)) {
            header('Location: '.$url.'&delok=-4');
            exit;
        };
    }

    if (!$vidonly) {

        # vtt 자막이 있을때 -> 지우기
        if(file_exists($startloc.$fdir.'/.SUB/'.$fname_noext.'.vtt')) 
        {
            chmod($startloc.$fdir.'/.SUB/'.$fname_noext.'.vtt',0755); 
            unlink($startloc.$fdir.'/.SUB/'.$fname_noext.'.vtt');
        }

        # srt 자막이 있을때 -> 지우기
        if(file_exists($startloc.$fdir.'/.SUB/'.$fname_noext.'.srt')) 
        {
            chmod($startloc.$fdir.'/.SUB/'.$fname_noext.'.srt',0755); 
            unlink($startloc.$fdir.'/.SUB/'.$fname_noext.'.srt');
        }

        # 썸네일이 있을때 -> 지우기
        if(file_exists($startloc.$fdir.'/.THUMB/'.$fname_noext.'.jpg')) 
        {
            chmod($startloc.$fdir.'/.THUMB/'.$fname_noext.'.jpg',0755); 
            unlink($startloc.$fdir.'/.THUMB/'.$fname_noext.'.jpg');
        }

    }


} else {

    # DB 검색결과에 ID가 없음
    if (!$query) {
        header('Location: '.$url.'&delok=-3');
        exit;
    }

}

# 성공했으므로 뒤로 복구
if ($vidonly) { # 비디오만 지울때 -> videdit으로 가기
    $url = './?id='.$id.'&sq='.$sq.'&page='.$page;
    header('Location: '.$url);
} else {
    header('Location: '.$url.'&delok=2');
}
exit;

?>
