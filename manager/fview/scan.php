<?php
session_start();

# 로그인이 안돼있다면
if(!isset($_SESSION['userid'])) {
	header ('Location: ../login?ourl='.urlencode($_SERVER[REQUEST_URI]));
	exit();
}

# 세션 체크 - 관리자 및 모더 허용
require_once('../src/session.php');
sess_check(array('admin', 'mod'));

require('../../src/settings.php');

$curr_dir = $_POST['d'];
$startloc = '../'.$startloc;
$dirchk = true;

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
if ($dirchk) {
    $dirchk = file_exists($startloc.$curr_dir);
}
if (!$dirchk) {
    echo ("not a valid directory");
	exit();
}

$cfile = scandir($startloc.$curr_dir);
$dir = $startloc.$curr_dir;


$files = array();
$folders = array();

foreach($cfile as $f) {

    if($f == '.' || $f == '..') {
        if ($f == '..') {
            $folders[] = array(
                "name" => $f,
                "type" => "parent",
                "path" => substr($curr_dir, 0, strrpos($curr_dir, '/')),
                "items" => 0 // Recursively get the contents of the folder
            );
        }
        continue; // Ignore hidden files
    }
		
    if(is_dir($dir . '/' . $f)) {

        // The path is a folder

        $folders[] = array(
            "name" => $f,
            "type" => "folder",
            "path" => $curr_dir . '/' .  $f,
            "items" => count(scandir($dir . '/' . $f)) // Recursively get the contents of the folder
        );
    } 
    
    else if (is_file($dir . '/' . $f)) {

        // It is a file
        $ext = substr($f, strrpos($f, '.')+1);

        # 확장자가 만약 비디오라면

        if ($ext == 'mp4' || $ext == 'webm') {
            $files[] = array(
                "name" => $f,
                "type" => "video",
                "path" => $curr_dir . '/' . $f,
                "size" => filesize($dir . '/' . $f) // Gets the size of this file
            );
        } elseif ($ext == 'jpg' || $ext == 'png') {
            $files[] = array(
                "name" => $f,
                "type" => "image",
                "path" => $curr_dir . '/' . $f,
                "size" => filesize($dir . '/' . $f)
            );
        } elseif ($ext == 'srt' || $ext == 'vtt' || $ext == 'smi') {
            $files[] = array(
                "name" => $f,
                "type" => "subtitle",
                "path" => $curr_dir . '/' . $f,
                "size" => filesize($dir . '/' . $f)
            );
        } else {
            $files[] = array(
                "name" => $f,
                "type" => "file",
                "path" => $curr_dir . '/' . $f,
                "size" => filesize($dir . '/' . $f)
            );
        }
    }
}

header('Content-type: application/json');
echo json_encode(array_merge($folders, $files));

