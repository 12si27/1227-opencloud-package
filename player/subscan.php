<?php
/*
    subscan.php
    vtt, srt 자막 스캔 & 전처리 스크립트

    Written by 1227
    rev. 20220512
*/
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

# 캡션 컬러태그 접두사
# <c. 뒤에 c를 또 추가하는 이유는 숫자로 시작하면 클래스 인식이 안되기 때문
$ct = "<c.c";

# 자막 스캔
$fh = fopen($caption, 'r');
$sub_data = '';
while ($read = fgets($fh)) { $sub_data .= $read; }
fclose($fh);

# 인코딩 감지
$enc = mb_detect_encoding($sub_data, 'ASCII,EUC-KR,UTF-8'); // 인코딩 감지
$sub_data = iconv($enc, "UTF-8", $sub_data); // 무조건 UTF-8로 변환처리

foreach (explode("\n", $sub_data) as $line) {

    # 타임스탬프 여부 확인
    if (substr_count($line, ' --> ') == 1) { //AND substr_count($line, ':') == 4) { <-- 이건 위치 조정 태그에서 씹힐수도 있기때문에 조건에서 삭제
        
        # srt일 경우 -> 초 구분 쉼표 수정 (vtt format 표준에 맞게)
        if($issrt) { $line = str_replace(',','.',$line); }

    } else {

        if($no_punct) {

            # 필요없는 공백 정리
            $line = trim($line);

            ################# 예외 목록 #################

            # ?!
            $line = str_replace('?!', '#SUP1#', $line);

            # !?
            $line = str_replace('!?', '#SUP2#', $line);

            # 느낌표 (!!)
            $line = str_replace('!!', '#EXC#', $line);
            
            # 기존 VTT 스타일 태그
            $line = str_replace('<c.', '#CTAG#', $line);

            # 말줄임표 (점3개)
            $line = str_replace('...', '#ELP#', $line);

            # 잘 쓰이는 도메인 주소
            $line = str_replace('.com', '#DOTCOM#', $line);
            $line = str_replace('.kr', '#DOTKR#', $line);

            #############################################

            ############## 문장 끝 부호 제거 #############

            if (mb_substr($line, -1, 1, 'utf-8') == ',') {
                $line = mb_substr($line, 0, -1, 'utf-8');
            }

            #############################################

            ############### 제거 목록 (전) ###############

            # 점, 물결표 제거
            $line = str_replace('.', '', $line);
            $line = str_replace('~', '', $line);

            # 느낌표 제거
            $line = str_replace('!', '', $line);

            #############################################

            
            # 예외 목록 복구
            $line = str_replace('#SUP1#', '?!', $line);
            $line = str_replace('#SUP2#', '!?', $line);
            $line = str_replace('#EXC#', '!', $line);
            $line = str_replace('#CTAG#', '<c.', $line);
            $line = str_replace('#ELP#', '...', $line);
            $line = str_replace('#DOTCOM#', '.com', $line);
            $line = str_replace('#DOTKR#', '.kr', $line);

        }

        # srt일 경우 -> font color 태그 c.cXXX로 수정하기!!
        if($issrt) {

            # <sub> </sub> 처리
            $line = str_replace('<sub>', '<c.sub>', $line);
            $line = str_replace('</sub>', '</c>', $line);

            if (strpos($line, '<font color') !== false) {

                # 우선 </font> 부터 처리
                $line = str_replace('</font>', '</c>', $line);                

                # 샵 있는것들

                # <font color="#/
                $line = str_replace('<font color="#', $ct, $line);
                # <font color = "#/
                $line = str_replace('<font color = "#', $ct, $line);
                # <font color='#/
                $line = str_replace('<font color=\'#', $ct, $line);
                # <font color = '#/
                $line = str_replace('<font color = \'#', $ct, $line);
                # <font color = #/
                $line = str_replace('<font color = #', $ct, $line);
                # <font color=#/
                $line = str_replace('<font color=#', $ct, $line);


                # 샵 없는것들

                # <font color="#/
                $line = str_replace('<font color="', $ct, $line);
                # <font color = "#/
                $line = str_replace('<font color = "', $ct, $line);
                # <font color='#/
                $line = str_replace('<font color=\'', $ct, $line);
                # <font color = '#/
                $line = str_replace('<font color = \'', $ct, $line);
                # <font color = #/
                $line = str_replace('<font color = ', $ct, $line);
                # <font color=#/
                $line = str_replace('<font color=', $ct, $line);


                # 꼭다리 따옴표 지우기

                # ">
                $line = str_replace('">', '>', $line);
                # '>
                $line = str_replace('\'>', '>', $line);
            }
        }
    }
    
    echo($line."\n");
    
}


?>
