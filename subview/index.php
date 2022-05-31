<?php
/*
    index.php
    1227 SubViewer INDEX PAGE

    Written by 1227
    rev. 20220522 (0.4)
*/

require('../src/settings.php');
require('../src/dbconn.php');

$rev = '0.4';

# 문자열 접미사 (파일 확장자) 판단
function endsWith($string, $endString)
{
    $len = mb_strlen($endString, 'utf-8');
    if ($len == 0) {
        return true;
    }
    return (mb_substr($string, -$len, NULL, 'utf-8') === $endString);
}

$urlchk = true;

require('../src/settings.php');
$sub = $_GET['c'];

# 요청 유효성 검사

if ($sub == '') {
    $urlchk = false;
} elseif ($sub == '.') {
    $urlchk = false;
} elseif ($sub == '..') {
    $urlchk = false;
} else {
    $chk = strpos($sub, '../');
    if ($chk !== false and $chk == 0) {
        $urlchk = false;
    }

    $chk = strpos($sub, '/../');
    if ($chk !== false) {
        $urlchk = false;
    }

    # 쓸데없는 2개 이상 슬래쉬는 허용 X
    $chk = strpos($sub, '//');
    if ($chk !== false) {
        $urlchk = false;
    }  

    # *.mp4 컨테이너만 허용 (추후 webm 사용시 변경 필요)
    if (!(endsWith($sub, '.vtt') || endsWith($sub, '.srt') || endsWith($sub, '.smi'))) {
        $urlchk = false;
    }   

}
if ($urlchk) {
    $urlchk = file_exists($startloc.$sub);
}
if (!$urlchk) {
    echo "<script>alert('존재하지 않거나 올바르지 않은 자막 파일입니다');history.back();</script>";
    exit();
}


# 표시를 위한 경로 저장
$subpath = substr($sub, 0, strrpos($sub, '/'));

# 비디오 확인을 위한 경로 저장
$vidpath = str_replace('/.SUB/', '/', $sub);

# 경로 정리
$sub = $startloc.$sub;

# 파일명 정보
$path_parts = pathinfo($sub);
$subname = $path_parts['filename'];
$ext = strtolower($path_parts['extension']);

# 비디오 확인을 위한 경로 저장 2
$vidpath = str_replace('.'.$ext, '.mp4', $vidpath);

if (!file_exists($startloc.$vidpath)) {$vidpath = '';}

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
        <link href="./css/style.css?rev=<?=$rev?>" rel="stylesheet">
        <script src="https://kit.fontawesome.com/b435844d6f.js" crossorigin="anonymous"></script>

        <title>12:27 SubViewer - <?=$subname?></title>
    </head>
    <body>
        
        <nav class="navbar navbar-dark">
            <div class="d-flex container">
                <a class="navbar-brand mb-0 ps-1 fw-bolder" href="../" style="color: rgb(150,150,150);">
                    12:<span style="color: #5aa1ef;">27</span> SubViewer
                </a>
                <div class="d-flex flex-grow-1 justify-content-end">
                    
                    <a class="btn btn-outline-light ms-2" onclick="window.close();"><i class="fa-solid fa-door-open"></i> 나가기</a>
                </div>
            </div>
        </nav>

        <!-- 메인 컨트롤 -->
        <div class="main-control shadow-sm">
            <div class="container p-3">
                <div style="font-size: small; color: #6f6f6f;">
                    <?=$subpath?>
                </div>
                <div class="fw-bolder title fs-5">
                    <?=$subname?>
                </div>
                <div class="title fs-6">
                    <span class="badge bg-secondary">자막 형식: <span id="ext"></span></span> 
                    <span class="badge bg-secondary">인코딩: <span id="enc"></span></span>
                </div>
            </div>
        </div>

        <div class="sub-control shadow-sm" style="background-color: rgb(40,40,40);">
            <div class="container py-1 px-3">
                <div class="d-flex flex-wrap align-items-center">
                    <div class="d-flex flex-grow-1 text-secondary ms-2 my-1" style="font-size: small;">자막 수정 날짜:</br><?=date("Y-m-d H:i:s", filemtime($sub))?></div>
                    <div class="d-flex px-2 justify-content-end my-1">
                        <?php
                        if ($vidpath != '') {
                            ?> <a class="btn btn-sm btn-outline-light me-1" href="../player?video=<?=urlencode($vidpath)?>"><i class="fa-solid fa-play"></i> 영상 보기</a> <?php
                        }
                        ?>
                        <a class="btn btn-sm btn-outline-primary" href="<?=$sub?>" download><i class="fa-solid fa-download me-1"></i> 다운로드</a>
                        <a class="btn ms-1 btn-sm btn-outline-danger" href="https://forms.gle/FNfJAS17BmkrfthS8" target="_blank"><i class="fa-solid fa-flag me-1"></i> 오류 제보</a>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="container px-6">
            <div class="col d-flex justify-content-center m-5" id="loading">
                <div class="fs-4 text-secondary">로드중...</div>
            </div>
            <div class="col m-3" id="subline"></div>            
        </div>

        <div class="fixed my-3">
            <div style="text-align: center; color: #ffffff; opacity: 0.3;">
                <div>by 1227</div>
                <div style="font-size: small;">rev. <?=$rev?></div>
            </div>
        </div>

        <!-- Option 1: Bootstrap Bundle with Popper -->
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-ka7Sk0Gln4gmtz2MlQnikT1wXgYsOg+OMhuP+IlRH9sENBO0LRn5q+8nbTov4+1p" crossorigin="anonymous"></script>
        <script src="./js/script.js?rev=<?=$rev?>"></script>

        <script>
            loadSub('<?=urlencode($_GET['c'])?>');
        </script>
    </body>
</html>