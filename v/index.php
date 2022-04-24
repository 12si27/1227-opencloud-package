<?php
# 비디오 db 테이블의 id를 통해 바로 접속이 가능하도록 한 페이지
$id = $_GET['id'];

require('../src/dbconn.php');
$id = mysqli_real_escape_string($conn, $id);

$query = mysqli_query($conn, "SELECT file_loc FROM videos WHERE id = '$id'");

if (mysqli_num_rows($query) < 1) {
    echo "<script>alert('잘못된 요청입니다.');history.back();</script>";
    exit();
} else {
    $query = mysqli_fetch_array($query);
    echo "<script>location.href='../player/?video=".urlencode($query['file_loc'])."';</script>";
    exit();
}

?>