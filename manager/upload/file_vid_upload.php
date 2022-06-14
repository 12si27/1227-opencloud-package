<?php
# =======================================================================================
# ================================= 영상 업로드 스크립트 =================================
# =======================================================================================

session_start();

# 로그인이 안돼있다면
if(!isset($_SESSION['userid'])) {
	header ('Location: ../login?ourl='.urlencode($_SERVER[REQUEST_URI]));
	exit();
}

# 세션 체크 - 관리자만 허용
require_once('../src/session.php');
sess_check(array('admin'));

# 설정 로드
require('../../src/settings.php');
$startloc = '../'.$startloc;


$noErrors = true;


# ==================================== 비디오 파일 업로드 ====================================

# 업로드 파일 정리
$fileTmpLoc = $_FILES["vid_file"]["tmp_name"];
$error = $_FILES["vid_file"]["error"];

// 비디오 업로드 설정
$vid_dir = $_POST["vid_dir"];
$allowed_ext = array('mp4', 'mkv', 'webm', 'avi', 'flv', 'mpg', 'mpeg', 'mp3', 'ogg');
 
// 변수 정리
$name = $_FILES['vid_file']['name'];
$name_noext = substr($name, 0, strrpos($name, '.'));
$ext = array_pop(explode('.', $name));


# 비디오 첨부여부 확인
if ($_FILES["vid_file"] == null) {
    $result[] = array('result' => -2,
                      'message' => 'failed to upload');
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

// 확장자 확인
if( !in_array($ext, $allowed_ext) ) {
	# 허용되지 않는 확장자
    $result[] = array('result' => -4,
                      'message' => 'video file extension not allowed');
    echo(json_encode($result));
    exit;
}

# 이름값 별도 설정된경우 바꾸기
# 비디오 파일명은 나중에 db에 넣어야하니까 따로 저장해두기
if ($_POST["vid_name"] == null) {
    $vid_fileName = $name; // The file name
} else {
    $vid_fileName = $_POST["vid_name"].'.'.$ext;
}

if (file_exists($startloc.$vid_dir)) {

    if (file_exists($startloc.$vid_dir.'/'.$vid_fileName)) {
        # 업로드할 위치에 이미 파일 있음
        $result[] = array('result' => -5,
                          'message' => 'upload file already exists');
        echo(json_encode($result));
        exit;
    }

} else {
    # 업로드할 경로가 존재안함
    $result[] = array('result' => -6,
                      'message' => 'invalid upload directory');
    echo(json_encode($result));
	exit;
}

if(!move_uploaded_file($fileTmpLoc, $startloc.$vid_dir.'/'.$vid_fileName)){
    # 업로드 임시파일 이동 실패
    $result[] = array('result' => -7,
                      'message' => 'move_uploaded_file function failed');
    echo(json_encode($result));
    exit;
}


# ==================================== 자막 파일 업로드 ====================================

if ($_FILES["cap_file"] != null) {

    # 업로드 파일 정리
    $fileTmpLoc = $_FILES["cap_file"]["tmp_name"];
    $error = $_FILES["cap_file"]["error"];

    // 자막 허용 확장자
    $allowed_ext = array('vtt', 'srt');
    
    // 변수 정리
    # $name = $_FILES['cap_file']['name']; <- 캡션은 어차피 제목정보 없을시 비디오 제목따라 지을거니까 저장할필요 X
    $ext = array_pop(explode('.', $_FILES['cap_file']['name']));
    
    // 오류 확인
    if( $error != UPLOAD_ERR_OK ) {
        switch( $error ) {
            case UPLOAD_ERR_INI_SIZE:
            case UPLOAD_ERR_FORM_SIZE:
                # 파일이 너무 큼
                $result[] = array('result' => -8,
                                'message' => 'caption file size is too big');
                break;
            case UPLOAD_ERR_NO_FILE:
                # 파일 첨부 안됨
                $result[] = array('result' => -9,
                                'message' => 'no upload caption');
                break;
            default:
                # 업로드 문제
                $result[] = array('result' => -10,
                                'message' => 'failed to upload caption');
        }

        $noErrors = false;
        goto makeThumb;
    }

    // 확장자 확인
    if( !in_array($ext, $allowed_ext) ) {
        # 허용되지 않는 확장자
        $result[] = array('result' => -11,
                        'message' => 'caption file extension not allowed');
        $noErrors = false;        
        goto makeThumb;
    }

    # 이름값 별도 설정된경우 바꾸기
    if ($_POST["vid_name"] == null) {
        $fileName = $name_noext.'.'.$ext;
    } else {
        $fileName = $_POST["vid_name"].'.'.$ext;
    }

    if (file_exists($startloc.$vid_dir.'/.SUB/'.$fileName)) {
        # 업로드할 위치에 이미 파일 있음
        $result[] = array('result' => -12,
                        'message' => 'upload caption file already exists');
        $noErrors = false;                
        goto makeThumb;
    }
    
    # .SUB 경로가 존재안함 -> 만들기
    # 업로드할 경로가 존재안함 -> 만들기
    if (!file_exists($startloc.$vid_dir.'/.SUB')) {
        if (!mkdir($startloc.$vid_dir.'/.SUB', 0777, true)) {
            # 자막 경로 만들기 실패
            $result[] = array('result' => -13,
                            'message' => 'failed to make caption directory');
            $noErrors = false;
            goto makeThumb;
        };
    }


    if(!move_uploaded_file($fileTmpLoc, $startloc.$vid_dir.'/.SUB/'.$fileName)){
        # 업로드 임시파일 이동 실패
        $result[] = array('result' => -14,
                        'message' => 'caption move_uploaded_file function failed');
        $noErrors = false;
        goto makeThumb;
    }

}


makeThumb:


# ==================================== 썸네일 파일 업로드 ====================================

if ($_FILES["thumb_file"] != null) {

    # 업로드 파일 정리
    $fileTmpLoc = $_FILES["thumb_file"]["tmp_name"];
    $error = $_FILES["thumb_file"]["error"];

    // 자막 허용 확장자
    $allowed_ext = array('jpg', 'jpeg');
    
    // 변수 정리
    $ext = array_pop(explode('.', $_FILES['thumb_file']['name']));
    
    // 오류 확인
    if( $error != UPLOAD_ERR_OK ) {
        switch( $error ) {
            case UPLOAD_ERR_INI_SIZE:
            case UPLOAD_ERR_FORM_SIZE:
                # 파일이 너무 큼
                $result[] = array('result' => -15,
                                'message' => 'thumnail file size is too big');
                break;
            case UPLOAD_ERR_NO_FILE:
                # 파일 첨부 안됨
                $result[] = array('result' => -16,
                                'message' => 'no upload thumnail');
                break;
            default:
                # 업로드 문제
                $result[] = array('result' => -17,
                                'message' => 'failed to upload thumnail');
        }
        
        $noErrors = false;
        goto assignDB;
    }

    // 확장자 확인
    if( !in_array($ext, $allowed_ext) ) {
        # 허용되지 않는 확장자
        $result[] = array('result' => -18,
                        'message' => 'thumnail file extension not allowed');
        
        $noErrors = false;
        goto assignDB;                
    }

    # 이름값 별도 설정된경우 바꾸기
    if ($_POST["vid_name"] == null) {
        $fileName = $name_noext.'.jpg';
    } else {
        $fileName = $_POST["vid_name"].'.jpg';
    }

    if (file_exists($startloc.$vid_dir.'/.THUMB/'.$fileName)) {
        # 업로드할 위치에 이미 파일 있음
        $result[] = array('result' => -19,
                        'message' => 'upload thumnail file already exists');
        
        $noErrors = false;
        goto assignDB;
    }

    # 업로드할 경로가 존재안함 -> 만들기
    if (!file_exists($startloc.$vid_dir.'/.THUMB')) {
        if (!mkdir($startloc.$vid_dir.'/.THUMB', 0777, true)) {
            # 썸네일 경로 만들기 실패
            $result[] = array('result' => -20,
                            'message' => 'failed to make thumnail directory');
            
            $noErrors = false;
            goto assignDB;
        };
    }

    if(!move_uploaded_file($fileTmpLoc, $startloc.$vid_dir.'/.THUMB/'.$fileName)){
        # 업로드 임시파일 이동 실패
        $result[] = array('result' => -21,
                        'message' => 'thumnail move_uploaded_file function failed');
        
        $noErrors = false;
        goto assignDB;
    }

}


assignDB:

# ==================================== DB 등록 ====================================

# 비디오ID (지정안할시 빈칸)
$vidid = '';

if ($_POST['auto_db'] == "true") {

    require('../../src/dbconn.php');
    $floc = mysqli_real_escape_string($conn, $vid_dir.'/'.$vid_fileName);
    $query = mysqli_query($conn, "SELECT id FROM videos WHERE file_loc = '".$floc."'");


    # 이미 경로에 따른 DB 존재시 -> 아이디 그냥 갖고오기
    if (mysqli_num_rows($query) == 1) {
        $query = mysqli_fetch_array($query);
        $vidid = $query['id'];

    } else {

        ################################ 비디오 정보 등록 ################################
        while(1) {

            # id생성
            $chars = "abcdefghijklmnopqrstuvwxyz0123456789";
            $var_size = strlen($chars);
            $vidid = '';
            for( $x = 0; $x < 6; $x++ ) { 
                $vidid .= $chars[ rand( 0, $var_size - 1 ) ]; 
            }
    
            # 중복체크
            $id_check = mysqli_query($conn, "SELECT id FROM videos WHERE id = '$vidid'");
            if (mysqli_num_rows($id_check) < 1) {
                break;
            }
        }
    
        $sql = "INSERT INTO `videos` (`file_loc`, `id`, `views`) VALUES ('$floc', '$vidid', '0')";
        $query = mysqli_query($conn,$sql);


        if (!$query) {
            # 업로드 임시파일 이동 실패
            $result[] = array('result' => -22,
                              'message' => 'failed to assign video to DB');
            
            $noErrors = false;
            goto taskFinish;
        }
    }
}

taskFinish:

if ($noErrors) {
    $final_result[] = array('result' => 0, 'message' => 'OK', 'autodb' => $_POST['auto_db'], 'vidid' => $vidid);
} else {
    $final_result[] = array('result' => 1, 'message' => 'video was uploaded, but caption or thumbnail had problem(s)', 'autodb' => $_POST['auto_db'], 'vidid' => $vidid);
}

header('Content-type: application/json');
echo(json_encode($final_result));
exit;
?>