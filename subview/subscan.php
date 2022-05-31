<?php
# error_reporting( E_ALL );
# ini_set( "display_errors", 1 );
header('Content-type: application/json');

require('../src/settings.php');
require('../src/dbconn.php');

$rev = '0.3';

# 문자열 접미사 (파일 확장자) 판단
function endsWith($string, $endString)
{
    $len = mb_strlen($endString, 'utf-8');
    if ($len == 0) {
        return true;
    }
    return (mb_substr($string, -$len, NULL, 'utf-8') === $endString);
}

# midReturn 함수
function midReturn($string, $start, $end) {
    $len = mb_strlen($start, 'utf-8');
    $sp = mb_strpos($string, $start) + $len;
    $ep = mb_strpos($string, $end, $sp, 'utf-8') - $sp;
    $result = mb_substr($string, $sp, $ep, 'utf-8');
    return $result;
}

$urlchk = true;

require('../src/settings.php');
$sub = $_POST['c'];

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
    # 올바르지 않은 경로
    echo json_encode(array('error' => 1));
    exit();
}

# 경로 정리
$sub = $startloc.$sub;

# 파일명 정보
$path_parts = pathinfo($sub);
$subname = $path_parts['filename'];
$ext = strtolower($path_parts['extension']);


# 자막 내용
$timestamp = array();   # 타임스탬프
$content = array();     # 내용

$prev_timestamp = false;
$curr_line = '';

# 자막 스캔
$fh = fopen($sub, 'r');
$sub_data = '';
while ($read = fgets($fh)) { $sub_data .= $read; }
fclose($fh);

# 인코딩 감지
$enc = mb_detect_encoding($sub_data, 'EUC-KR,UTF-8,ASCII'); // 인코딩 감지
$sub_data = iconv($enc, "UTF-8//IGNORE", $sub_data);

foreach (explode("\n", $sub_data) as $line) {

    # srt 또는 vtt 일 경우
    if ($ext == 'srt' || $ext == 'vtt') {
        # 타임스탬프 여부 확인
        if (substr_count($line, ' --> ') == 1) { # AND substr_count($line, ':') == 4) {
            # $prev_timestamp = true; # 타임스탬프 플래그 세우기

            # 타임스탬프 이미 있다 -> 읽어놓은 curr_line 데이터 푸시
            if (count($timestamp) > 0) {
                array_push($content, $curr_line);
                $curr_line = '';
            }

            array_push($timestamp, trim($line));
        } else {
            if (count($timestamp) > 0) {
                # \n으로 쪼갰기 때문에 넣어줘야함
                $curr_line .= $line."\n";
            }
        }

    } elseif ($ext == 'smi') {
        if (strpos($line, '<SYNC Start=') !== false) {

            $text = $line;

            $sync = midReturn($line, '<SYNC Start=', '>');
            $junk = '<SYNC Start='.$sync.'>';
            $text = str_replace($junk, '', $text);

            $p_class = midReturn($line, '<P Class=', '>');
            $junk = '<P Class='.$p_class.'>';
            $text = str_replace($junk, '', $text);

            array_push($timestamp, $sync);
            array_push($content, $text);
        }  
    }

}


# 마무리 (srt, vtt 마지막에선 content 한번 더 푸시해야함)
if ($ext == 'srt' || $ext == 'vtt') {
    if (count($timestamp) > 0) {
        array_push($content, $curr_line);
        $curr_line = '';
    }
}

$tss = array();
$lines = array();

if (count($timestamp) > 0) {

    for ($i=0; $i<count($timestamp); $i++) {

        if ($ext == 'srt' || $ext == 'vtt') {
            $ts = $timestamp[$i];

            $line = explode("\n", $content[$i]);
            $line = array_slice($line, 0, count($line) - 2);
            $line = trim(join("\n", $line));

            $numOfLine = count(explode("\n", $line));

        } elseif ($ext == 'smi') {

            # timestamp 처리
            $ts = gmdate("H:i:s", (int)$timestamp[$i] / 1000);
            $ts .= '.'.sprintf('%03d', (int)$timestamp[$i] % 1000);
            $ts .= ' ('.$timestamp[$i].')';

            # content 처리
            $line = trim($content[$i]);

            if ($line == '&nbsp;') {
                $line = '';
            } else {
                $line = str_replace('</br>', "\n", $line);
                $line = str_replace('</ br>', "\n", $line);
                $line = str_replace('<br>', "\n", $line);
            }

        }

        array_push($tss, $ts);
        array_push($lines, $line);
    }
} else {
    # 자막 내용이 없음
    echo json_encode(array('error' => 2));
    exit;
}

echo json_encode(array('timestamp' => $tss,
                       'lines' => $lines,
                       'ext' => $ext,
                       'encoding' => $enc,
                       'error' => 0), JSON_UNESCAPED_UNICODE);