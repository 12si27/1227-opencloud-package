<?php
/*
    postfind.php
    1227 네이버 블로그 포스트 검색 스크립트

    Written by 1227
    rev. 20220512
*/
$query = $_GET['query'];
echo '<script>';

if($query != '') {

    # 검색에 방해되는 문자 제거
    $query = str_replace('"', ' ', $query);
    $query = str_replace('&', ' ', $query);

    exec('python3 ./src/postfind.py "'.$query.'"', $arr);
    $result = implode("\n", $arr)."\n";

    # 오류일 경우
    if (strpos($result, '<result>ERROR</result>') !== false) {
        echo "alert('포스트 조회 중 오류가 발생했습니다.');";
        echo "window.close();";
    # 검색결과 없을 경우
    } elseif (strpos($result, '<result>NOTFOUND</result>') !== false) {
        echo "alert('조회 결과 존재하지 않는 비디오입니다.\\n비공개 혹은 삭제 처리된 포스트일 수 있습니다.');";
        echo "window.close();";
    } else {
        $url = substr($result, strpos($result, '<result>') + 8 , strpos($result, '</result>') - 8);
        echo "location.href=\"$url\";";
    }
}
?></script>