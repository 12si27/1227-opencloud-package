<?php
$urlchk = true;

require('../src/settings.php');
$caption = $_GET['c'];

# 요청 유효성 검사

if ($caption == '') { $urlchk = false; }
elseif ($caption == '.') { $urlchk = false; }
elseif ($caption == '..') { $urlchk = false; }
else {
    $chk = strpos($caption, '../');
    if ($chk !== false and $chk == 0) {
        $urlchk = false;
    }

    $chk = strpos($caption, '/../');
    if ($chk !== false) {
        $urlchk = false;
    }

    # *.mp4 컨테이너만 허용 (추후 webm 사용시 변경 필요)
    $urlchk = (endsWith($caption, '.vtt') OR endsWith($caption, '.srt'));
}
if ($urlchk) {
    $urlchk = file_exists($startloc.$caption);
}
if (!$urlchk) {
    exit();
}


# 문자열 접미사 (파일 확장자) 판단
function endsWith($string, $endString)
{
    $len = mb_strlen($endString, 'utf-8');
    if ($len == 0) {
        return true;
    }
    return (mb_substr($string, -$len, NULL, 'utf-8') === $endString);
}

# 자막 위치값 정리
$caption = $startloc.$caption;
$issrt = false;
$no_punct = ($_GET['np']==1);

# 출력 시작
header("Content-Type: text/plain");

# srt, vtt 둘중 뭐인지 판단
if (endsWith($caption, '.srt')) {
    echo("WEBVTT\n\n");
    $issrt = true;
}


# 내용 출력
# 라인 하나를 격차로 두고 시작하기
$prevline = '';
$is_ts = false;


$fh = fopen($caption, 'r');
while ($line = fgets($fh)) {

    # 타임스탬프 여부 확인
    if (substr_count($line, ' --> ') == 1 AND substr_count($line, ':') == 4) {
        
        # 여기서는 타임스탬프 맞음!!
        $is_ts = true;

        # srt일 경우 -> 초 구분 쉼표 수정 (vtt format 표준에 맞게)
        if($issrt) { $line = str_replace(',','.',$line); }

    } else {

        $is_ts = false;

        if($no_punct) {
            $line = str_replace('...', '!ELP!', $line);
            $line = str_replace('.', '', $line);
            $line = str_replace('~', '', $line);
            $line = str_replace('!ELP!', '...', $line);
            $line = str_replace('!', '', $line);
        }

    }

    if($issrt == false OR ($issrt AND $is_ts == false)) {
        echo($prevline);
    }
    $prevline = $line;
    
}
echo($prevline);
fclose($fh);


?>
