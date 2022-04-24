<?php
session_start();

# 로그인이 안돼어있다면
if(!isset($_SESSION['userid']))
{
    echo ("not a valid request");
	exit();
}

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

foreach($cfile as $f) {

    if($f == '.' || $f == '..') {
        if ($f == '..') {
            $files[] = array(
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

        $files[] = array(
            "name" => $f,
            "type" => "folder",
            "path" => $curr_dir . '/' .  $f,
            "items" => count(scandir($dir . '/' . $f)) // Recursively get the contents of the folder
        );
    } 
    
    else if (is_file($dir . '/' . $f)) {

        // It is a file
        $ext = substr($f, strrpos($f, '.')+1);

        # 확장자가 만약 mp4라면

        if ($ext == 'mp4') {
            $files[] = array(
                "name" => $f,
                "type" => "video",
                "path" => $curr_dir . '/' . $f,
                "size" => filesize($dir . '/' . $f) // Gets the size of this file
            );
        } elseif ($ext == 'jpg') {
            $files[] = array(
                "name" => $f,
                "type" => "image",
                "path" => $curr_dir . '/' . $f,
                "size" => filesize($dir . '/' . $f) // Gets the size of this file
            );
        } else {
            $files[] = array(
                "name" => $f,
                "type" => "file",
                "path" => $curr_dir . '/' . $f,
                "size" => filesize($dir . '/' . $f) // Gets the size of this file
            );
        }
    }
}

header('Content-type: application/json');
echo json_encode($files);
