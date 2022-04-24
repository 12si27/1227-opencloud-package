<?php
# =======================================================================================
# =============================== 캡션 파일 업로드 스크립트 ===============================
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
$sq = $_POST['sq'];
$page = $_POST['page'];
$url = './?id='.$id.'&sq='.$sq.'&page='.$page;

$id = mysqli_real_escape_string($conn, $_POST['id']);

# DB에서 ID에 따른 파일 경로 찾아보기
$query = mysqli_query($conn, "SELECT * FROM videos WHERE id='$id'");

if(mysqli_num_rows($query) == 1) {

    $row = mysqli_fetch_array($query);
    $file_loc = $row['file_loc'];

    $fdir = substr($file_loc, 0, strrpos($file_loc, '/'));          # 경로 (EX: RegularShow/S2)
    $fname = substr($file_loc, strrpos($file_loc, '/') + 1);        # 파일 이름 (EX: Regular Show - S02E25 첫날 (First Day) [LQ] [480p].mp4)
    $fname_noext = substr($fname, 0, strrpos($fname, '.'));         # 파일 이름 (확장자X) (EX: Regular Show - S02E25 첫날 (First Day) [LQ] [480p])

} else {

    # DB 검색결과에 ID가 없음
    header('Location: '.$url.'&fail=13');
    exit();

}

// 설정
$uploads_dir = '';
$allowed_ext = array('vtt', 'srt');
 
// 변수 정리
$error = $_FILES['cap_file']['error'];
$name = $_FILES['cap_file']['name'];
$ext = array_pop(explode('.', $name));
 
// 오류 확인
if( $error != UPLOAD_ERR_OK ) {
	switch( $error ) {
		case UPLOAD_ERR_INI_SIZE:
		case UPLOAD_ERR_FORM_SIZE:
			# 파일이 너무 큼
            header('Location: '.$url.'&fail=12');
			break;
		case UPLOAD_ERR_NO_FILE:
			# 파일 첨부 안됨
            header('Location: '.$url.'&fail=11');
			break;
		default:
			# 업로드 문제
            header('Location: '.$url.'&fail=14');
	}
	exit;
}
 
// 확장자 확인
if( !in_array($ext, $allowed_ext) ) {
	header('Location: '.$url.'&fail=15');
	exit;
}

# 자막 폴더 (.SUB) 없을시 만들기
if (!file_exists($startloc.$fdir.'/.SUB')) {
    mkdir($startloc.$fdir.'/.SUB', 0777, true);

} else { # 이미 있을때

    # vtt 자막이 이미 있을때 -> 지우기
    if(file_exists($startloc.$fdir.'/.SUB/'.$fname_noext.'.vtt')) 
    {
        chmod($startloc.$fdir.'/.SUB/'.$fname_noext.'.vtt',0755); 
        unlink($startloc.$fdir.'/.SUB/'.$fname_noext.'.vtt');
    }

    # vtt 자막이 이미 있을때 -> 지우기
    if(file_exists($startloc.$fdir.'/.SUB/'.$fname_noext.'.srt')) 
    {
        chmod($startloc.$fdir.'/.SUB/'.$fname_noext.'.srt',0755); 
        unlink($startloc.$fdir.'/.SUB/'.$fname_noext.'.srt');
    }
}
 
// 파일 이동
if (!move_uploaded_file($_FILES['cap_file']['tmp_name'], $startloc.$fdir.'/.SUB/'.$fname_noext.'.'.$ext)) {
    header('Location: '.$url.'&fail=17');
	exit;
}

# 성공했으므로 뒤로 복구
header('Location: '.$url);

?>