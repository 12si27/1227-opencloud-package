<?php
/*
    substyling.php
    vtt, srt 자막 태그 인식 & CSS 출력 스크립트
    (반드시 index.php 내에서 실행되어야 함)

    Written by 1227
    rev. 20220512
*/
if ($rev == null) {
    echo 'invalid access';
    exit;
}
# 자막의 FONT 태그 읽어들여 컬러 CSS를 출력하는 스크립트

# SRT 자막 아니면 패스
if (!endsWith($caption, '.srt')) {
    goto styling_pass;
}

# midReturn 함수
function midReturn($string, $start, $end) {
    $len = mb_strlen($start, 'utf-8');
    $sp = mb_strpos($string, $start) + $len;
    $ep = mb_strpos($string, $end, $sp, 'utf-8') - $sp;
    $result = mb_substr($string, $sp, $ep, 'utf-8');
    return $result;
}

echo "<style>\n";

# 사용된 컬러 종류 담는 배열
$colors = array();

# sub태그 사용여부
$has_sub = false;

# 내용 스캔
$fh = fopen($startloc.$caption, 'r');
while ($line = fgets($fh)) {

    # echo($line);

    # 컬러 태그 맞을 경우
    if (strpos($line, '<font color') !== false) {

        $tags = explode('</font>', $line);

        foreach ($tags as $t) {

            if (strpos($t, '<font color') !== false) {

                $color = midReturn($t, '<font color=', '>');
                # echo ($color."\n");
                
                $color = str_replace(' ', '', $color);
                $color = str_replace('"', '', $color);
                $color = str_replace('\'', '', $color);
                $color = str_replace('=', '', $color);

                if (!in_array($color, $colors)) {

                    # echo ($color."\n");
                    array_push($colors, $color);
                    
                    # CSS 출력 시작
                    ?>video::cue(c.c<?=str_replace('#','',$color)?>), .vjs-text-track-cue span.c<?=str_replace('#','',$color)?> { color: <?=$color?>; } <?php

                }
            }
        }
    # sub태그 있을 경우
    } elseif (strpos($line, '<sub>') !== false) {
        $has_sub = true;
    }
}
    
fclose($fh);

# sub css 추가
if ($has_sub) { ?>video::cue(c.sub), .vjs-text-track-cue span.sub { font-size: 75%; line-height: 75%; background-color: transparent; }<?php }

echo "\n</style>";


styling_pass:
?>
