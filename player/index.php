<?php
setlocale(LC_ALL, 'ko_KR.UTF-8');

$urlchk = true;

require('../src/settings.php');
$video = $_GET['video'];


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
    echo "<script>alert('잘못된 주소 또는 삭제 처리된 영상입니다.');history.back();</script>";
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
                <span style='font-size: small;'><?=$content?></span>
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
function searchUrlGen($title) {
    return "https://duckduckgo.com/?q=!ducky+site:blog.naver.com+%2212si27%22".urlencode($title); #<-덕덕고 검색 (잘안됨)
    #return "https://search.naver.com/search.naver?query=site%3Ablog.naver.com%2F12si27+".urlencode($title);
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


# 비디오ID 체크 및 등록

require('../src/dbconn.php');
$floc = mysqli_real_escape_string($conn, $video);
$query = mysqli_query($conn, "SELECT id, views, last_checked FROM videos WHERE file_loc = '".$floc."'");

$views = 0;
$last_chk = 'N/A';


# 비디오 위치값 정리

$video = $startloc.$video;
$path_parts = pathinfo($video);
$currdir = mb_substr($path_parts['dirname'],  mb_strlen($startloc, 'utf-8'), NULL, 'utf-8');


################################ 비디오 정보 등록 ################################

# 비디오가 등록되어 있지 않은 경우
if (mysqli_num_rows($query) < 1) {
    
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

# 등록되어 있는 경우
} else {
    $query = mysqli_fetch_array($query);
    $vidid = $query['id'];
    $last_chk = $query['last_checked'];
    $add = isCountable($vidid);
    $views = $query['views'] + $add;
    $sql = "UPDATE videos SET views = IFNULL(views, 0) + $add, last_checked = NOW() WHERE id = '$vidid'";
    mysqli_query($conn,$sql);
}

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

?>
<!doctype html>
<html lang="en">
    <head>
        <!-- Required meta tags -->
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
        <meta name="mobile-web-app-capable" content="yes">
        <meta name="theme-color" content="#1c2c3b">
        <link rel="icon" sizes="192x192" href="../img.png">

        <!-- Bootstrap CSS -->
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-1BmE4kWBq78iYhFldvKuhfTAU6auU8tT94WrHftjDbrCEXSU1oBoqyl2QvZ6jIW3" crossorigin="anonymous">
        <link href="./css/style.css?rev=0.27" rel="stylesheet">
        <script src="https://kit.fontawesome.com/b435844d6f.js" crossorigin="anonymous"></script>

        <link href="https://cdn.jsdelivr.net/npm/videojs-mobile-ui/dist/videojs-mobile-ui.css" rel="stylesheet">

        <!-- Global site tag (gtag.js) - Google Analytics -->
        <script async src="https://www.googletagmanager.com/gtag/js?id=G-RGCW2QEFK9"></script>
        <script>
            window.dataLayer = window.dataLayer || [];
            function gtag(){dataLayer.push(arguments);}
            gtag('js', new Date());

            gtag('config', 'G-RGCW2QEFK9');
        </script>

        <?php
            if ($nplayer == false) {
                ?> <link href="https://vjs.zencdn.net/7.18.1/video-js.css" rel="stylesheet" />
                <link href="./css/theme.css?rev=0.13" rel="stylesheet"> <?php
            }
        ?>
        
        <title><?=$path_parts['filename']?> - 1227 클라우드플레이어</title>
    </head>
    <body>
        
        <nav class="navbar navbar-dark">
            <div class="container">
                <a class="navbar-brand mb-0 fw-bolder" href="../" style="color: rgb(150,150,150);">
                    12:<span style="color: #5aa1ef;">27</span> CloudPlayer
                </a>
                <div class="d-flex">
                    <a class="btn btn-outline-light" onclick="window.close();"><i class="fa-solid fa-door-open"></i> 나가기</a>
                </div>
            </div>
        </nav>

        <!-- 플레이어 -->
        <div class="bg-black">
            
            <?php
            if ($nplayer == false) { ?>
            <div class="container" style="padding-left: 0; padding-right: 0;">
                <video id="my_video"
                       class="video-js vjs-theme-1227 vjs-big-play-centered"
                       width="160px" height="90px" controls controlsList="nodownload"
                       <?php if($thumb != "") {echo 'poster="'.$thumb.'"';}?>
                       preload="true" data-setup='{ "aspectRatio":"16:9"}'
                       oncontextmenu="return false;" autoplay>
                    <source src="<?=$video?>" type='video/mp4' />
                    <?php if ($caption != '') { captionTagPrint($path_parts['filename'], $caption, $no_punct, $caplang); } ?>
                </video>
                <!--<script src="https://vjs.zencdn.net/7.18.1/video.min.js"></script>-->
                <script src="./js/video.min.js?rev=1.7"></script>
                <script src="./js/video.fullscreen.js"></script>
                <script src="./js/video.mobileui.js?rev=1"></script>
            </div> <?php
            } else { ?>
            <div class="container ratio ratio-16x9" style="padding-left: 0; padding-right: 0;">
                <video id="my_video_html5_api" autoplay controls oncontextmenu="return false;" controlsList="nodownload">
                    <source src="<?=$video?>" type='video/mp4' />
                    <?php if ($caption != '') { captionTagPrint($path_parts['filename'], $caption, $no_punct, $caplang); } ?>
                </video>
            </div>
            <?php }
            ?>
            
        </div>

        <!-- 메인 컨트롤 -->
        <div class="main-control shadow-sm">
            <div class="container py-1">
                <div class="d-flex flex-wrap">
                    <div class="p-2 fw-bolder title">
                        <span class="fs-5">
                            <?php
                            $title = $path_parts['filename'];
                            $title = str_replace('[', '<span class="badge bg-secondary">', $title);
                            $title = str_replace(']', '</span>', $title);
                            echo $title;
                            ?>
                        </span>
                    </div>

                    <div class="p-2 flex-fill d-flex justify-content-end align-items-center">
                        <!-- 다음 버튼부터는 ms-2 넣기 -->
                        <span type="button" class="btn btn-sm" style="background-color: #3f3f3f; color: #cdcdcd;" disabled
                        data-bs-toggle="tooltip" data-bs-placement="bottom" title="이 영상을 열람한 횟수">
                        <i class="fa-solid fa-eye"></i> <span class="bt-text"><?=number_format($views)?></span></span>

                        <a class="btn btn-sm btn-dark ms-2" href="?video=<?=urlencode($_GET['video'])?>&np=<?=($nplayer?0:1)?>"
                        data-bs-toggle="tooltip" data-bs-html="true" data-bs-placement="bottom" title="<?php
                            if ($nplayer) {
                                echo '1227 백업클라우드에서 제공하는 자체 플레이어로 재생합니다.</br><sub>대부분의 환경에서 추천합니다.</sub>';
                            } else {
                                echo '브라우저에 내장된 플레이어로 재생합니다.</br><sub>레거시/스트림 환경에서 추천합니다.</sub>';
                            }
                            ?>"><?php
                            if ($nplayer) {
                                echo '<i class="fa-solid fa-circle-play"></i> <span class="bt-text">자체</span>';
                            } else {
                                echo '<i class="fas fa-tv"></i> <span class="bt-text">기본</span>';
                            }
                        ?></a>

                        <button type="button" class="btn btn-sm btn-dark ms-2"
                        onclick="copyClipboard('https://cloud.1227.kr/v/?id=<?=$vidid?>')"
                        data-bs-toggle="tooltip" data-bs-placement="bottom" title="이 영상으로 들어갈 수 있는 짧은 링크를 복사합니다.">
                        <i class="fas fa-link"></i> <span class="bt-text">링크</span></button>

                        <a type="button" class="btn btn-sm btn-dark ms-2"
                        href="<?=searchUrlGen($path_parts['filename'])?>"
                        target="_blank"
                        data-bs-toggle="tooltip" data-bs-html="true" data-bs-placement="bottom"
                        title="duckduckgo 엔진을 통해 1227.kr 블로그 영상을 검색하여 접속합니다.</br><sub>주의: 원본 글이 없거나 검색이 되지 않는 경우 부적절한 글이 나올 수 있습니다.</sub>">
                        <img src="./naverblog.svg" width=18px height=18px></img> <span class="bt-text">포스트 검색</span></a>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="container mt-3">

            <!-- 비디오 탐색 버튼 -->
            <div class="row">
                <div class="col-sm-6">
                    <div class="shadow px-3 py-2 mb-2 switch-bt prev-bt"<?php
                        if(!$isitfirst) {
                            ?> onclick="location.href='./?video=<?=urlencode($currdir.'/'.$files[$order-1])?>';" <?php
                        } elseif ($smart_prev_fname != '') {
                            ?> onclick="location.href='./?video=<?=urlencode($currsubdir_v.$smart_prev.'/'.$smart_prev_fname)?>';" <?php
                        }?>>
                        <div class="fw-bold switch-title">이전 비디오<?=($smart_prev_fname!=''?' <small><i>스마트 추천</i></small>':'')?></div>
                        <div class="text-wrap switch-content"><?php
                        if ($smart_prev_fname != '') {    
                            echo substr($smart_prev_fname, 0, strrpos($smart_prev_fname, '.'));
                        } elseif ($isitfirst) {
                            echo "<span style='color: rgb(40,40,40);'>첫번째 비디오입니다</span>";
                        } else {
                            echo substr($files[$order-1], 0, strrpos($files[$order-1], '.'));
                        }
                        ?></div>
                    </div>
                </div>
                <div class="col-sm-6">
                    <div class="shadow px-3 py-2 mb-2 switch-bt next-bt"<?php
                        if(!$isitlast) {
                            ?> onclick="location.href='./?video=<?=urlencode($currdir.'/'.$files[$order+1])?>';" <?php
                        } elseif ($smart_next_fname != '') {
                            ?> onclick="location.href='./?video=<?=urlencode($currsubdir_v.$smart_next.'/'.$smart_next_fname)?>';" <?php
                        }?>>
                        <div class="fw-bold switch-title">다음 비디오<?=($smart_next_fname!=''?' <small><i>스마트 추천</i></small>':'')?></div>
                        <div class="text-wrap switch-content"><?php
                        if ($smart_next_fname != '') {    
                            echo substr($smart_next_fname, 0, strrpos($smart_next_fname, '.'));
                        } elseif ($isitlast) {
                            echo "<span style='color: rgb(40,40,40);'>마지막 비디오입니다</span>";
                        } else {
                            echo substr($files[$order+1], 0, strrpos($files[$order+1], '.'));
                        }
                        ?></div>
                    </div>
                </div>
            </div>

            <!-- 비디오 관련 토스트 -->
            <?php
            if (strpos($path_parts['filename'], '[HD+]') !== false) {
                showAlert('HD+ 화질', '트래픽 부담을 줄이기 위해 약간 낮은 화질(900p)로 제공됩니다. 추후 원본 화질로 대체될 예정입니다.');
            } elseif (strpos($path_parts['filename'], '[LQ]') !== false) {
                showAlert('저화질 영상','아직 고화질 영상이 존재하지 않는 비디오(480p 이하)입니다. 양해 부탁 드립니다.');
            }

            if (strpos($path_parts['filename'], '[EN]') !== false) {
                showAlert('영어 자막', '본 영상은 영어 자막이 포함되어 있습니다. 시청 시 참고 바랍니다.');
            }
            ?>

            <!-- 내장 자막 옵션 -->
            <?php
            if ($caption != '') {
            ?>

            <div class="card mt-2">
                <div class="card-body">
                <h6 class="card-subtitle mb-2 text-muted">내장 자막 설정</h6>
                    <form class="d-flex justify-content-between align-items-center" method="GET">
                        <div class="form-check form-switch" style="color: #afafaf;">
                            <input class="form-check-input" type="checkbox" name="no_punct" value="1" id="flexSwitchCheckDefault" <?=($no_punct=='1'?'checked':'')?>>
                            <label class="form-check-label" for="flexSwitchCheckDefault">문장 부호 ( '.' , '~' , '!' 등) 숨기기</label>
                        </div>
                        <input hidden name="video" value="<?=$_GET['video']?>">
                        <input hidden name="apply" value="1">
                        <button type="submit" class="btn btn-sm btn-outline-light ms-2">적용</span></button>
                    </form>
                </div>
            </div>

            <?php
            }
            ?>

           
            <div class="row mt-3">

                <!-- 비디오 정보 -->
                <div class="col<?=($caption==''?'':'-sm-6')?>">
                    <div class="card mt-2">
                        <div class="card-body">
                        <h6 class="card-subtitle mb-2 text-muted">비디오 정보</h6>
                            <div style="font-size:small; color: gray;">
                            비디오 형식: <?=$path_parts['extension']?> <span id="vid_length"></span> </br>
                            비디오 용량: <span id='vid_size'><?=filesize($video)?></span>B (<?=round(filesize($video)/1024/1024,2)?>MB<span id="bitrate_info"></span>) </br>
                            마지막으로 수정된 날짜: <?=date("Y-m-d H:i:s", filemtime($video));?> </br>
                            마지막으로 열람된 날짜: <?=$last_chk?>
                            </div>
                        </div>
                    </div>
                </div>
                

                <?php if ($caption != '') { ?>
                <div class="col-sm-6">
                <!-- 자막 정보 -->
                <div class="card mt-2">
                    <div class="card-body">
                    <h6 class="card-subtitle mb-2 text-muted">내장 자막 정보</h6>
                        <div style="font-size:small; color: gray;">
                        자막 형식: <?=$captype?> | 언어: <?=$caplang?></br>
                        자막 용량: <?=filesize($startloc.$caption).'B ('.round(filesize($startloc.$caption)/1024,2).'KB)'?> </br>
                        마지막으로 수정된 날짜: <?=date("Y-m-d H:i:s", filemtime($startloc.$caption))?> </br>
                        문장 부호 숨김 여부: <?=($no_punct==1?'Y':'N')?>
                        </div>
                    </div>
                </div>
                </div>
            <?php } ?>
            </div>
        </div>

        <div class="fixed mt-3">
            <p style="text-align: center;"><span style="color: #ffffff; opacity: 0.3;">by 1227<br>
        </div>

        <!-- Optional JavaScript; choose one of the two! -->

        <!-- Option 1: Bootstrap Bundle with Popper -->
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-ka7Sk0Gln4gmtz2MlQnikT1wXgYsOg+OMhuP+IlRH9sENBO0LRn5q+8nbTov4+1p" crossorigin="anonymous"></script>

        <!-- Option 2: Separate Popper and Bootstrap JS -->
        <!-- <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.min.js" integrity="sha384-QJHtvGhmr9XOIpI6YVutG+2QOK9T+ZnN4kzFN1RtK3zEFEIsxhlmWl5/YESvpZ13" crossorigin="anonymous"></script> -->

        <script src="./js/script.js?rev=0.12"></script>


        <style>

        <?php 
        if ($prev_thumb != '') {
            ?>
            .prev-bt {
                text-shadow: rgb(24, 24, 24) 0px 0 5px;
            }
            .prev-bt::before {
                border-radius: 5px;
                content: "";
                background: linear-gradient(to right, rgb(24, 24, 24) 60%, rgb(24, 24, 24, 0.7)), url("<?=$prev_thumb?>");
                background-size: cover;
                background-position: right;
                background-repeat: no-repeat;
                background-size: cover, 50%;
                position: absolute;
                top: 0px; left: 0px; right: 0px; bottom: 0px;
            }
            <?php
        }

        if ($next_thumb != '') {
            ?>
            .next-bt {
                text-shadow: rgb(24, 24, 24) 0px 0 5px;
            }
            .next-bt::before {
                border-radius: 5px;
                content: "";
                background: linear-gradient(to right, rgb(24, 24, 24) 60%, rgb(24, 24, 24, 0.7)), url("<?=$next_thumb?>");
                background-position: right;
                background-repeat: no-repeat;
                background-size: cover, 50%;
                position: absolute;
                top: 0px; left: 0px; right: 0px; bottom: 0px;
            }
            <?php
        }
        ?>

        </style>

    </body>
</html>