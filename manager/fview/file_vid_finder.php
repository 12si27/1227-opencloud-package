<?php
session_start();

# 로그인이 안돼어있다면
if(!isset($_SESSION['userid']))
{
	header ('Location: ../login?ourl='.urlencode($_SERVER[REQUEST_URI]));
	exit();
}

$urlchk = true;

require('../../src/settings.php');
$startloc = '../'.$startloc;
$video = $_GET['f'];
$assign = ($_GET['assign'] == '1');


# single quote 수정
$video = str_replace("%27", "'", $video);

# 요청 유효성 검사

if ($video == '') {
    $urlchk = false;
} elseif ($video == '.') {
    $urlchk = false;
} elseif ($video == '..') {
    $urlchk = false;
} else {
    $chk = strpos($video, '../');
    if ($chk !== false and $chk == 0) {
        $urlchk = false;
    }

    $chk = strpos($video, '/../');
    if ($chk !== false) {
        $urlchk = false;
    }

    # 쓸데없는 2개 이상 슬래쉬는 허용 X
    $chk = strpos($video, '//');
    if ($chk !== false) {
        $urlchk = false;
    }  
}
if ($urlchk) {
    $urlchk = file_exists($startloc.$video);
}
if (!$urlchk) {
    echo "<script>alert('$startloc.$video');history.back();</script>";
    exit();
}


# 비디오ID 체크 및 등록

require('../../src/dbconn.php');
$floc = mysqli_real_escape_string($conn, $video);
$query = mysqli_query($conn, "SELECT id, views, last_checked FROM videos WHERE file_loc = '".$floc."'");

$views = 0;
$last_chk = 'N/A';


# 비디오 위치값 정리
$orgv = $video;
$video = $startloc.$video;

# 비디오 정보 조회
if (mysqli_num_rows($query) == 1) {
    $query = mysqli_fetch_array($query);
    $vidid = $query['id'];
    
    header('Location: ../videdit?id='.$vidid."&viaexp=1");
} else {
    $orgv = urlencode($orgv);

    if ($assign) {

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
        $result = mysqli_query($conn,$sql);

        header('Location: ../videdit?id='.$vidid."&viaexp=1");

    } else {
        echo
"<!DOCTYPE html>
<html>
<head>
	<meta charset=\"utf-8\">
	<meta http-equiv=\"X-UA-Compatible\" content=\"IE=edge\">
	<meta name=\"viewport\" content=\"width=device-width, initial-scale=1, shrink-to-fit=no\">
</head>
<body>
    <h3>등록되지 않은 비디오입니다!</h3>
    <p><a href='./file_vid_finder.php?f=$orgv&assign=1'>이 링크</a>를 눌러 비디오를 DB에 등록하세요. </p>
    <p>등록을 원치 않는 경우, <a href='javascript:history.back()'>여기</a>를 눌러 뒤로 돌아갑니다.</p>
</body>
</html>";
    }

    
    
}
