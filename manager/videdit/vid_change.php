<?php
# =======================================================================================
# ================================== 영상 교체 스크립트 ==================================
# =======================================================================================

session_start();

# 로그인이 안돼있다면
if(!isset($_SESSION['userid']))
{
	header ('Location: ../login');
	exit();
}

# 설정 로드
require('../../src/settings.php');
$startloc = '../'.$startloc;



# ==================================== DB 체크 ====================================

# db 로드
require('../../src/dbconn.php');

# 비디오ID (지정안할시 빈칸)
$vidid = $_POST['id'];

$vidid = mysqli_real_escape_string($conn, $vidid);
$sql = "SELECT id, views, file_loc, last_checked FROM videos WHERE id = '$vidid'";

$query = mysqli_query($conn, $sql);
$vid = mysqli_fetch_array($query);

# 원본 파일의 위치 (바꿔치기하고 갖다버릴거)
$floc = $startloc.$vid['file_loc'];
$vid_ext = array_pop(explode('.', $floc));

# ==================================== 비디오 파일 업로드 ====================================

# 업로드 파일 정리
$fileTmpLoc = $_FILES["vid_file"]["tmp_name"];
$error = $_FILES["vid_file"]["error"];

 
// 변수 정리
$name = $_FILES['vid_file']['name'];
$ext = array_pop(explode('.', $name));


# 비디오 첨부여부 확인
if ($_FILES["vid_file"] == null) {
    $result[] = array('result' => -2,
                      'message' => 'failed to upload');
    echo(json_encode($result));
    exit;
}
 
// 확장자 확인
if( $ext != $vid_ext ) {
	# 허용되지 않는 확장자
    $result[] = array('result' => -4,
                      'message' => 'extension is not same (floc:'.$floc.' vidext:'.$vid_ext);
    echo(json_encode($result));
    exit;
}

// 오류 확인
if( $error != UPLOAD_ERR_OK ) {
	switch( $error ) {
		case UPLOAD_ERR_INI_SIZE:
		case UPLOAD_ERR_FORM_SIZE:
			# 파일이 너무 큼
            $result[] = array('result' => -1,
                              'message' => 'file size is too big');
			break;
		case UPLOAD_ERR_NO_FILE:
			# 파일 첨부 안됨
            $result[] = array('result' => -2,
                              'message' => 'no upload file');
			break;
		default:
			# 업로드 문제
            $result[] = array('result' => -3,
                              'message' => 'failed to upload');
	}
	echo(json_encode($result));
	exit;
}



# 비디오가 존재할때 (대부분의 케이스, 지우고 바꿔치기)

if (file_exists($floc)) {

    chmod($floc,0755); 

    if (!unlink($floc)) {
        # 삭제 실패
        $result[] = array('result' => -5,
                          'message' => 'failed to delete the original file');
        echo(json_encode($result));
        exit;
    }
}


# 이제 비디오 업로드

if(!move_uploaded_file($fileTmpLoc, $floc)){
    # 업로드 임시파일 이동 실패
    $result[] = array('result' => -7,
                      'message' => 'move_uploaded_file function failed (floc:'.$floc.' vidext:'.$vid_ext);
    echo(json_encode($result));
    exit;
}


$final_result[] = array('result' => 0, 'message' => 'OK');


header('Content-type: application/json');
echo(json_encode($final_result));
exit;
?>