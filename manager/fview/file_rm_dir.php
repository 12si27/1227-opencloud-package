<?php
# =======================================================================================
# ================================ 디렉토리 제거 스크립트 ================================
# =======================================================================================

session_start();

# 로그인이 안돼있다면
if(!isset($_SESSION['userid']))
{
	header ('Location: ../login');
	exit();
}

# 설정 로드
require('../../src/settings.php');
$startloc = '../'.$startloc;


$curr_dir = $_POST['d'];        # 제거할 경로
$parent_dir = substr($curr_dir, 0, strrpos($curr_dir, '/'));    # 부모 경로

$dirchk = true;

# 경로 체크
if ($curr_dir == '.') {
    $dirchk = false;
} elseif ($curr_dir == '..') {
    $dirchk = false;
} else {
    $chk = strpos($curr_dir, '../');
    if ($chk !== false and $chk == 0) {
        $dirchk = false;
    }

    $chk = strpos($curr_dir, '/../');
    if ($chk !== false) {
        $dirchk = false;
    }

    # 쓸데없는 2개 이상 슬래쉬는 허용 X
    $chk = strpos($curr_dir, '//');
    if ($chk !== false) {
        $dirchk = false;
    }  

}

if ($dirchk) { $dirchk = file_exists($startloc.$curr_dir); }

header('Content-type: application/json');

if (!$dirchk) { # 올바른 경로 아닐 시에
    $result[] = array('result' => -1,
                      'message' => 'not a valid directory');
} else {

    $rm = rmdir($startloc.$curr_dir);

    if ($rm) {
        $result[] = array('result' => 0,
                          'message' => 'OK',
                          'pdir' => $parent_dir);
    } else {
        $result[] = array('result' => -2,
                          'message' => 'failed to remove a directory');
    }

}

echo(json_encode($result));
exit;