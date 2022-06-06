<?php
session_start();
$id=$_POST['id'];
$pw=$_POST['pw'];
$ourl=$_POST['ourl'];

$cilent_ip = $_SERVER['REMOTE_ADDR'];

if ($id==NULL || $pw==NULL) {
    header('Location: ./?fail=1');
    exit();
}

# 솔트값 지정 (긴 랜덤 문자열 권장)
$salt = 'salt';
$pw=hash('sha256', $salt.$pw);

require('../../src/dbconn.php');



# 안전 로그인 체크
# 최근 10번 시도 불러오기
$query = mysqli_query($conn, "SELECT success, DATE_ADD(time, INTERVAL '1' HOUR) as t FROM `login_log` WHERE time >= DATE_SUB(NOW(), INTERVAL '1' HOUR) AND ip_address = '$cilent_ip' ORDER BY time LIMIT 10");
$succ_count = 0;
$fail_count = 0;

while ($row = mysqli_fetch_array($query)) {
    if ($row['success'] == '1') {
        $succ_count++;
        break; # 한번만 있어도 되니까
    } else {
        $last_trial_1h_later = $row['t']; # 마지막 실패시도의 1시간 뒤 (로그인 가능 시간)
        $fail_count++;
    }
}

# 최근 1시간, 10번 시도동안 성공을 한번도 못했을시
if ($succ_count == 0) {
    # 열번째 시도 이상
    if ($fail_count >= 10) {
        # $login_ok = false; # 로그인 차단
        echo "login blocked";
        exit;
    }
}



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
                header('Location: '.$ourl);
            }
            mysqli_query($conn, "INSERT INTO `login_log` (`log_id`, `id`, `ip_address`, `time`, `success`) VALUES (NULL, '$id', '$cilent_ip', CURRENT_TIME(), '1')");
            exit();
        } else {
            # echo '<script>alert("세션 저장 실패");history.back();</script>';
            header('Location: ./?fail=3');
            mysqli_query($conn, "INSERT INTO `login_log` (`log_id`, `id`, `ip_address`, `time`, `success`) VALUES (NULL, '$id', '$cilent_ip', CURRENT_TIME(), '-1')");
            exit();
        }
    } else {
        header('Location: ./?fail=2');
        mysqli_query($conn, "INSERT INTO `login_log` (`log_id`, `id`, `ip_address`, `time`, `success`) VALUES (NULL, '$id', '$cilent_ip', CURRENT_TIME(), '-2')");
        exit();
    }
} else {
    header('Location: ./?fail=2');
    mysqli_query($conn, "INSERT INTO `login_log` (`log_id`, `id`, `ip_address`, `time`, `success`) VALUES (NULL, '$id', '$cilent_ip', CURRENT_TIME(), '-3')");
    exit();
}

?>
