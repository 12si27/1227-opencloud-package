<?php
/*
    preload.php
    1227 CloudPlayer 프리로드 스크립트
    (반드시 index.php 내에서 실행되어야 함)

    Written by 1227
    rev. 20220528
*/
if ($rev == null) {
    echo 'invalid access';
    exit;
}

setlocale(LC_ALL, 'ko_KR.UTF-8');

# 세션 시작 - 무단 스트림 방지, 키 검증용
session_start();

$urlchk = true;

require('../src/settings.php');
$video = $_GET['video'];


# 크레딧 타임 (다음 비디오 뜨는 시점) -> 기본값: 30초
$credit_time = 30;


if ($video == null) {
    # video 파라미터 값 없으면 강 바로 상위페이지
    echo '<script>location.href="../"</script>';
}

# 다이렉트 링크로 접속했는지 여부 (ex. /v/?id=abc123 같은거)
$direct = ($_GET['direct'] == 1);

# 플레이어 -> 플레이어로 다시 리프레시 된것일때 (탐색기/외부 -> 플레이어가 아닐때)
$refreshed = ($_GET['r'] == 1);


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

    # *.mp4 컨테이너만 허용 (추후 webm 사용시 변경 필요)
    if (!endsWith($video, '.mp4')) {
        $urlchk = false;
    }   

    
}
if ($urlchk) {
    $urlchk = file_exists($startloc.$video);
}
if (!$urlchk) {
    echo "<script>alert('잘못된 주소 또는 삭제 처리된 영상입니다.";

    if (!$direct) {
        echo "\\n(이전 페이지에서 새로 고침 후 다시 시도해 보세요)";
    }

    echo "');";

    if ($refreshed) {
        echo "history.back();";
    } else {
        echo "window.close();";
    }

    echo "</script>";
    exit();
}


$nplayer = $_GET['np'];                 # 네이티브 플레이어 여부 (GET)
$nplayer_c = $_COOKIE['np'];            # 네이티브 플레이어 여부 (COOKIE)

if ($nplayer == null) { # get nplayer 값 없을때
    if ($nplayer_c == null) { # 쿠키값도 없을때
        $nplayer = 0; setcookie('np', 0, time()+3600*24*365, '/');
    } else { $nplayer = $nplayer_c; }
} else { setcookie('np', $nplayer, time()+3600*24*365, '/'); }



$no_punct = $_GET['no_punct'];                 # 문장부호 삭제 여부 (GET)
$no_punct_c = $_COOKIE['no_punct'];            # 문장부호 삭제 여부 (COOKIE)

if ($no_punct == null) { # get no_pucnt 값 없을때
    if ($no_punct_c == null) { # 쿠키값도 없을때
        # 기본값 (false) 으로 채우기
        $no_punct = 0; setcookie('no_punct', 0, time()+3600*24*365, '/');
    } else {
        # 쿠키 있음

        # apply 하는 상황일 경우
        if ($_GET['apply']) {
            # no_punct 강제 적용 (false도 적용해야 하니까)
            setcookie('no_punct', $no_punct, time()+3600*24*365, '/');
        } else {
            # 아니면 쿠키값 불러오기
            $no_punct = $no_punct_c; 
        }   
        
    }
} else { setcookie('no_punct', $no_punct, time()+3600*24*365, '/'); }



$no_sub_bg = $_GET['no_sub_bg'];                 # 배경박스 삭제 여부 (GET)
$no_sub_bg_c = $_COOKIE['no_sub_bg'];            # 배경박스 삭제 여부 (COOKIE)

if ($no_sub_bg == null) { # get no_pucnt 값 없을때
    if ($no_sub_bg_c == null) { # 쿠키값도 없을때
        # 기본값 (false) 으로 채우기
        $no_sub_bg = 0; setcookie('no_sub_bg', 0, time()+3600*24*365, '/');
    } else {
        # 쿠키 있음

        # apply 하는 상황일 경우
        if ($_GET['apply']) {
            # no_sub_bg 강제 적용 (false도 적용해야 하니까)
            setcookie('no_sub_bg', $no_sub_bg, time()+3600*24*365, '/');
        } else {
            # 아니면 쿠키값 불러오기
            $no_sub_bg = $no_sub_bg_c; 
        }   
        
    }
} else { setcookie('no_sub_bg', $no_sub_bg, time()+3600*24*365, '/'); }



$no_vttjs = $_GET['no_vttjs'];                 # vttjs 미사용 여부 (GET)
$no_vttjs_c = $_COOKIE['no_vttjs'];            # vttjs 미사용 여부 (COOKIE)

if ($no_vttjs == null) { # get no_pucnt 값 없을때
    if ($no_vttjs_c == null) { # 쿠키값도 없을때
        # 기본값 (false) 으로 채우기
        $no_vttjs = 0; setcookie('no_vttjs', 0, time()+3600*24*365, '/');
    } else {
        # 쿠키 있음

        # apply 하는 상황일 경우
        if ($_GET['apply']) {
            # no_vttjs 강제 적용 (false도 적용해야 하니까)
            setcookie('no_vttjs', $no_vttjs, time()+3600*24*365, '/');
        } else {
            # 아니면 쿠키값 불러오기
            $no_vttjs = $no_vttjs_c; 
        }   
        
    }
} else { setcookie('no_vttjs', $no_vttjs, time()+3600*24*365, '/'); }




# 문자열 접미사 (파일 확장자) 판단
function endsWith($string, $endString)
{
    $len = mb_strlen($endString, 'utf-8');
    if ($len == 0) {
        return true;
    }
    return (mb_substr($string, -$len, NULL, 'utf-8') === $endString);
}

# 자막 이름 지정
function captionTagPrint($filename, $caption, $no_punct, $caplang) {
    echo '<track kind="subtitles" label="';
    if ($caplang == 'N/A') {
        echo '자막 표시';
    } else {
        echo $caplang;
    }
    echo '" src="./subscan.php?c='.urlencode($caption).'&np='.$no_punct.'" srclang="ko" default="">';
}

# 영상, 자막관련 알림패널 표시
function showAlert($title, $content) {
?>
    <div class="alert alert-dismissible fade show" role="alert">
        <div class="d-flex">
            <div>
                <i class="fa-solid fa-circle-info me-2" style="font-size: x-large;"></i>
            </div>
            <div>
                <strong><?=$title?></strong></br>
                <div class='mt-1' style='font-size: small;'><?=$content?></div>
            </div>
        </div>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    <style>
        .alert { background-color: #3f3f3f; color: white; }
    </style>
    <?php
}

# 포스트 검색 URL 생성
# 덕덕고 -> 네이버 블로그 검색 (w/ python3)
function searchUrlGen($title) {
    #return "https://duckduckgo.com/?q=!ducky+site:blog.naver.com+%2212si27%22".urlencode($title); #<-덕덕고 검색 (잘안됨)
    #return "https://search.naver.com/search.naver?query=site%3Ablog.naver.com%2F12si27+".urlencode($title);
    return "./postfind.php?query=".urlencode($title);
}

# 조회수 카운트 여부 판별 (쿠키 이용)
function isCountable($vidid) {
    $pv_c = $_COOKIE['prev_vid'];       # 이전 비디오 ID (COOKIE)
    
    if ($pv_c == null) { # 이전 비디오값이 없을때
        setcookie('prev_vid', $vidid, time()+600, '/'); # 10분동안 쿠키 유지
        return 1; # 처음 본 것이므로 1 추가
    } else {
        if ($pv_c == $vidid) { # 이전 비디오 아이디와 현재아이디와 같을때
            return 0;
        } else {
            setcookie('prev_vid', $vidid, time()+600, '/'); # 10분동안 쿠키 유지
            return 1;
        }
    }
}

# 플레이어 비디오 URL 이스케이핑
function vidUrlEscape($string)
{
    $video = $string;
    $video = str_replace('%', '%25', $video);
    $video = str_replace('"', '%22', $video);
    $video = str_replace('?', '%3F', $video);

    return $video;
}

# 플레이어 비디오 URL 이스케이핑 (슬래시 빼고 - without slash)
function urlenc_wos($url) {
	return str_replace('%2F','/',rawurlencode($url));
}


# 유저에이전트 모바일 검사
function isMobileDevice() {
    return preg_match("/(android|avantgo|blackberry|bolt|boost|cricket|docomo|fone|hiptop|mini|mobi|palm|phone|pie|tablet|up\.browser|up\.link|webos|wos)/i", $_SERVER["HTTP_USER_AGENT"]);
}

# 비디오ID 체크 및 등록

require('../src/dbconn.php');
$floc = mysqli_real_escape_string($conn, $video);
$query = mysqli_query($conn, "SELECT id, views, last_checked, credit_time FROM videos WHERE file_loc = '".$floc."'");

$views = 0;
$last_chk = 'N/A';


# 비디오 위치값 정리

$video = $startloc.$video;

$path_parts = pathinfo($video);
$currdir = mb_substr($path_parts['dirname'],  mb_strlen($startloc, 'utf-8'), NULL, 'utf-8');


################################ 비디오 정보 등록 ################################

$newvid = false;

# 비디오가 등록되어 있지 않은 경우
if (mysqli_num_rows($query) < 1) {

    # 새로 생성하기는 관리 페이지 (/manager) 에서 하도록 함
    # (비디오 추가하고 잠금 설정 하기 전에 열람해버리면 곤란하니까)
    # 그냥 오류창 띄우고 치우게하기

    /*
    
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

    $sql = "INSERT INTO `videos` (`file_loc`, `id`, `views`) VALUES ('$floc', '$vidid', '1')";
    $result = mysqli_query($conn,$sql);
    $views = 1;

    # 새로 막 생성된 비디오 -> 기존 비디오가 아니므로 새비디오로 플래그 생성
    # > 이는 비공개 비디오 확인 절차를 생략하기 위함
    $newvid = true;

    */

    require_once('./src/novdb.php');
    exit;

# 등록되어 있는 경우
} else {
    $query = mysqli_fetch_array($query);
    $vidid = $query['id'];
    $last_chk = $query['last_checked'];
    $add = isCountable($vidid);
    $views = $query['views'] + $add;
    if ($query['credit_time'] != null) {
        $credit_time = $query['credit_time'];
    }
    $sql = "UPDATE videos SET views = IFNULL(views, 0) + $add, last_checked = NOW() WHERE id = '$vidid'";
    mysqli_query($conn,$sql);
}



####################################### 비공개 비디오 확인 #######################################

$lock_video = false;
$key_passed = true;
if (!$newvid) {
    # 비디오가 비공개(잠금) 비디오인지 확인
    $query = mysqli_query($conn, "SELECT id, vid_key, allowed_user FROM locked WHERE id = '$vidid' AND active = 1");

    # 잠금 비디오일 경우
    if (mysqli_num_rows($query) > 0) {
        $lock_video = true;
        $key_passed = false;
        $query = mysqli_fetch_array($query);


        # 우선 세션에 이미 키값 저장되어있나 확인
        if(isset($_SESSION['key'])) { # 이미 저장되어 있다면
            if ($_SESSION['key'] == $query['vid_key']) { # 세션키와 일치한다면 -> 패스 통과
                $key_passed = true;
            }
        }

        # 세션 키로 pass하지 않았다면
        if (!$key_passed) {

            if ($_POST['key'] == null) {
                if ($_POST['tried'] == '1') {
                    $key_fail = 1;
                }
            } else {
                if ($_POST['key'] == $query['vid_key']) {
                    $key_passed = true;
                } else {
                    $key_fail = 2;
                }
            }

            # 키 값 세션에 저장 (리프레시 시 자동해제용)
            if ($_POST['key'] != null AND $key_passed) { # 키 입력했으며 검사 통과했을때
                $_SESSION['key'] = $_POST['key']; # 세션 저장하고

                # 히스토리 변경 후 리프레시 (post warning 안 뜨게)
                ?><script>
                if (window.history.replaceState) { window.history.replaceState( null, null, window.location.href ); }
                window.location = window.location.href;
                </script><?php
            }
        }
    }
}

################################################################################################



################################ 비디오 시간당 조회수 등록 ################################

$query = mysqli_query($conn, "SELECT views FROM hourly_view WHERE id = '".$vidid."' AND date = CURDATE() AND hour = HOUR(NOW())");

# 조회수가 등록되어 있지 않은 경우
if (mysqli_num_rows($query) < 1) {
    
    $sql = "INSERT INTO `hourly_view` (`id`, `date`, `hour`, `views`) VALUES ('$vidid', CURDATE(), HOUR(NOW()), '1')";
    $result = mysqli_query($conn, $sql);

# 등록되어 있는 경우
} else {
    $query = mysqli_fetch_array($query);
    $hourly_views = $query['views'] + $add;
    $sql = "UPDATE hourly_view SET views = IFNULL(views, 0) + $add WHERE id = '".$vidid."' AND date = CURDATE() AND hour = HOUR(NOW())";
    mysqli_query($conn, $sql);
}

################################################################################################



######################################## 내장 자막 분석 ########################################

#vtt(srt) 자막이 존재하는지 체크
$caption = '';
$captype = 'N/A';
$caplang = 'N/A';
if (file_exists($path_parts['dirname'].'/.SUB/'.$path_parts['filename'].'.vtt')) {
    $captype = 'Web Video Text Tracks';
    $caption = str_replace($startloc, '', $path_parts['dirname']).'/.SUB/'.$path_parts['filename'].'.vtt';
} elseif (file_exists($path_parts['dirname'].'/.SUB/'.$path_parts['filename'].'.srt')) {
    $captype = 'SubRip Subtitle';
    $caption = str_replace($startloc, '', $path_parts['dirname']).'/.SUB/'.$path_parts['filename'].'.srt';
}


# 영자막일 경우 (제목에 [EN]이 있는 경우)
if (strpos($path_parts['filename'], '[EN]') !== false) {
    $caplang = "English";
} elseif (strpos($path_parts['filename'], '[KR]') !== false or strpos($path_parts['filename'], '[자막]') !== false or strpos($path_parts['filename'], '[FX]') !== false) {
    $caplang = "한국어";
}

###############################################################################################


######################################## 파일 목록 분석 ########################################

$isitfirst = false;
$isitlast = false;

$files = array_values(array_filter(scandir($path_parts['dirname']), function($item) {
    return endsWith($item, '.mp4');
}));


if (count($files) == 1) {
    $isitfirst = true;
    $isitlast = true;
} else {
    $order = array_search($path_parts['basename'], $files);
    if ($order == 0) {
        $isitfirst = true;
    } elseif ($order == count($files)-1) {
        $isitlast = true;
    }
}

###############################################################################################


####################################### 스마트 추천 분석 #######################################

$smart_next = '';
$smart_prev = '';
$smart_next_fname = '';
$smart_prev_fname = '';

# Case 1. 시즌 마지막 에피소드를 보았고 다음 시즌 첫화를 봐야할 때
if ($isitlast OR $isitfirst) { # 시즌 첫화 또는 막화일시 (폴더의 첫, 마지막 영상) 일시

    # 현재폴더명 추출 (예: S1)
    $currfolder = substr($path_parts['dirname'], strrpos($path_parts['dirname'], '/') + 1);

    # 현재섭폴더 추출 (예: ../Home/RegularShow/)
    $currsubdir = substr($path_parts['dirname'], 0, strrpos($path_parts['dirname'], '/') + 1);

    # 현재섭폴더가 시작폴더가 아닐 경우에만 (시작폴더일 경우 의미없음)
    if ($currsubdir != $startloc) {

        # 비디오 주소생성 위한 시작폴더 주소제거
        $currsubdir_v = str_replace($startloc, '', $currsubdir);
        $subdirlist = scandir($currsubdir);
        $subdir_order = -1;

        foreach ($subdirlist as $dirs) {
            $subdir_order++;
            if ($dirs == $currfolder) {
                break;
            }
        }


        if ($isitfirst AND $subdir_order > 2) {
            $smart_prev = $subdirlist[$subdir_order-1];
        } 
        
        if ($isitlast AND $subdir_order < count($subdirlist)) {
            $smart_next = $subdirlist[$subdir_order+1];
        }

        # 이전것이 디렉토리가 맞다면
        if ($smart_prev != '' AND is_dir($currsubdir.$smart_prev)) {
            $subdirlist = scandir($currsubdir.$smart_prev);

            # 맨 끝에놈을 찾아야 하니 break 없음
            foreach ($subdirlist as $file) {
                if (endsWith($file, '.mp4')) {
                    $smart_prev_fname = $file;
                }
            }
        }

        # 다음것이 디렉토리가 맞다면
        if ($smart_next != '' AND is_dir($currsubdir.$smart_next)) {
            $subdirlist = scandir($currsubdir.$smart_next);

            foreach ($subdirlist as $file) {
                if (endsWith($file, '.mp4')) {
                    $smart_next_fname = $file;
                    break;
                }
            }
        }
    }
}


###############################################################################################


###################################### 썸네일(포스터) 분석 ######################################

# 현재 영상 썸네일
$thumb = $path_parts['dirname'].'/.THUMB/'.$path_parts['filename'].'.jpg';
if (!file_exists($thumb)) {
    $thumb = '';
}

# 이전 영상 썸네일
$prev_thumb = '';
if (!$isitfirst) { # 첫번째 영상이 아닐 시에만
    $prev_thumb = $path_parts['dirname'].'/.THUMB/'.substr($files[$order-1], 0, strrpos($files[$order-1], '.')).'.jpg';    
} elseif ($smart_prev_fname != '') { # 추천 이전 영상 존재시에도
    $prev_thumb = $currsubdir.$smart_prev.'/.THUMB/'.substr($smart_prev_fname, 0, strrpos($smart_prev_fname, '.')).'.jpg';   
}


# 다음 영상 썸네일
$next_thumb = '';
if (!$isitlast) { # 마지막 영상이 아닐 때에만
    $next_thumb = $path_parts['dirname'].'/.THUMB/'.substr($files[$order+1], 0, strrpos($files[$order+1], '.')).'.jpg';   
} elseif ($smart_next_fname != '') { # 추천 이전 영상 존재시에도
    $next_thumb = $currsubdir.$smart_next.'/.THUMB/'.substr($smart_next_fname, 0, strrpos($smart_next_fname, '.')).'.jpg';   
}

################################################################################################



######################################## 영상 스트림 작업 #######################################

# 뷰 ID 저장 (스트림 검증용)
if(!isset($_SESSION['viewid'])) {
    # 뷰id생성 - 생성 후 stream.php에서 세션 부른 후 일치여부 체크할것임
    $chars = "abcdefghijklmnopqrstuvwxyz0123456789";
    $var_size = strlen($chars);
    $viewid = '';
    for( $x = 0; $x < 10; $x++ ) { 
        $viewid .= $chars[ rand( 0, $var_size - 1 ) ]; 
    }

    $_SESSION['viewid'] = $viewid; #vidid 세션값 저장
} else {
    $viewid = $_SESSION['viewid']; #vidid 세션값 불러오기
}

# 스트림 URL 지정
$stream_url = './stream.php?v='.urlencode($vidid).'&vid='.$viewid;

################################################################################################