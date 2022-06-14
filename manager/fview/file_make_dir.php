<?php
# =======================================================================================
# ================================ 디렉토리 생성 스크립트 ================================
# =======================================================================================

session_start();

# 로그인이 안돼있다면
if(!isset($_SESSION['userid'])) {
	header ('Location: ../login?ourl='.urlencode($_SERVER[REQUEST_URI]));
	exit();
}

# 세션 체크 - 관리자 및 모더 허용
require_once('../src/session.php');
sess_check(array('admin', 'mod'));

# 설정 로드
require('../../src/settings.php');
$startloc = '../'.$startloc;


$curr_dir = $_POST['d'];        # 생성할 경로
$folder_name = $_POST['f'];     # 새 폴더 이름

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
    if ($folder_name == null) { # 폴더명 입력 없을 시에
        $result[] = array('result' => -2,
                          'message' => 'no folder name');

    } else { # 폴더가 이미 존재할때
        if (file_exists($startloc.$curr_dir.'/'.$folder_name)) {
            $result[] = array('result' => -3,
                              'message' => 'the folder is already exists');

        } elseif (strpos($folder_name, '/') !== false) {
            $result[] = array('result' => -4,
                              'message' => 'no slash allowed for name');

        } else {
            $make = mkdir($startloc.$curr_dir.'/'.$folder_name, 0777, true);

            if ($make) {
                $result[] = array('result' => 0,
                                'message' => 'OK');
            } else {
                $result[] = array('result' => -5,
                                'message' => 'failed to make a directory');
            }
        }  
    }
}

echo(json_encode($result));
exit;