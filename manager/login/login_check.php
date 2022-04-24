<?php
session_start();
$id=$_POST['id'];
$pw=$_POST['pw'];
$ourl=$_POST['ourl'];

if ($id==NULL || $pw==NULL) {
    header('Location: ./?fail=1');
    exit();
}

# 솔트값 지정 (긴 랜덤 문자열 권장)
$salt = '1227cloud!';
$pw=hash('sha256', $salt.$pw);

require('../../src/dbconn.php');

$id = mysqli_real_escape_string($conn, $id);
$pw = mysqli_real_escape_string($conn, $pw);

$query = mysqli_query($conn, "SELECT * FROM admin WHERE id='$id'");

if(mysqli_num_rows($query) == 1) {

    $row = mysqli_fetch_array($query);

    if ($row['pw']==$pw) {
        $_SESSION['userid']=$id;
        $_SESSION['username']=$row['name'];

        $query = mysqli_query($conn, "UPDATE admin SET last_login = NOW() WHERE id = '$id'");

        if(isset($_SESSION['userid'])) {
            if ($ourl == null) {
                header('Location: ../');
            } else {
                echo "<script>$ourl</script>";
                header('Location: '.$ourl);
            }
            exit();
        } else {
            # echo '<script>alert("세션 저장 실패");history.back();</script>';
            header('Location: ./?fail=3');
            exit();
        }
    } else {
        header('Location: ./?fail=2');
        exit();
    }
} else {
    header('Location: ./?fail=2');
    exit();
}

?>