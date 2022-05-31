<?php
# =======================================================================================
# =============================== 비디오 이름 변경 스크립트 ===============================
# =======================================================================================

session_start();

# 로그인이 안돼있다면
if(!isset($_SESSION['userid']))
{
	header ('Location: ../login');
	exit();
}

# DB, 설정 로드
require('../../src/dbconn.php');
require('../../src/settings.php');
$startloc = '../'.$startloc;

$id = mysqli_real_escape_string($conn, $_POST['id']);

# 이건 사용자 편의상 설정 보존하려고 두는 변수들임
$id = $_POST['id'];

# 탐색기를 통해 온 경우
if ($_POST['viaexp'] == '1') {
    $url = './?id='.$id.'&viaexp=1';

# 비디오 목록에서 온 경우
} else {
    $sq = $_POST['sq'];
    $page = $_POST['page'];
    $order = $_POST['order'];
    $url = './?id='.$id.'&sq='.$sq.'&page='.$page.'&order='.$order;
}

$query = mysqli_query($conn, "SELECT * FROM videos WHERE id='$id'");

$new_fname = $_POST['name'];

if ($new_fname == null) {
    # 바꿀이름 비어있음
    header('Location: '.$url.'&fail=1');
    exit();
}

if(mysqli_num_rows($query) == 1) {

    $row = mysqli_fetch_array($query);
    $file_loc = $row['file_loc'];

    $old_dir = substr($file_loc, 0, strrpos($file_loc, '/'));           # 기존 경로 (EX: RegularShow/S2)
    $old_fname = substr($file_loc, strrpos($file_loc, '/') + 1);        # 기존 파일 이름 (EX: Regular Show - S02E25 첫날 (First Day) [LQ] [480p].mp4)
    $old_fname_noext = substr($old_fname, 0, strrpos($old_fname, '.'));  # 기존 파일이름 (확장자X) (EX: Regular Show - S02E25 첫날 (First Day) [LQ] [480p])
    $extension = substr($old_fname, strrpos($old_fname, '.'));          # 기존 확장자 (EX: .mp4)

    # echo '확장자:'.$extension.'</br>';
    # echo '기존파일:'.$startloc.$old_dir.'/'.$old_fname.'</br>';
    # echo '새파일:'.$startloc.$old_dir.'/'.$new_fname.$extension.'</br>';

    # 일단 기존 영상 파일의 존재부터 확인
    if (file_exists($startloc.$old_dir.'/'.$old_fname)) {
        if (file_exists($startloc.$old_dir.'/'.$new_fname.$extension)) {
            # 이미 이름바꿀 파일이 존재
            header('Location: '.$url.'&fail=3');
            exit();

        } else {
            $renaming = rename($startloc.$old_dir.'/'.$old_fname, $startloc.$old_dir.'/'.$new_fname.$extension);
            if (!$renaming) {
                # 영상파일 이름 바꾸기 실패
                header('Location: '.$url.'&fail=2');
                exit();
            };
            $new_floc_value = mysqli_real_escape_string($conn, $old_dir.'/'.$new_fname.$extension);
            $sql = "UPDATE `videos` SET `file_loc` = '$new_floc_value' WHERE `videos`.`id` = '$id'";
            $query = mysqli_query($conn, $sql);


            # vtt 자막 존재여부 체크
            if (file_exists($startloc.$old_dir.'/.SUB/'.$old_fname_noext.'.vtt')) {
                $renaming = rename($startloc.$old_dir.'/.SUB/'.$old_fname_noext.'.vtt',
                                   $startloc.$old_dir.'/.SUB/'.$new_fname.'.vtt');
                if (!$renaming) {
                    # vtt 이름 바꾸기 실패
                    header('Location: '.$url.'&fail=8');
                    exit();
                };
            }

            # srt 자막 존재여부 체크
            if (file_exists($startloc.$old_dir.'/.SUB/'.$old_fname_noext.'.srt')) {
                $renaming = rename($startloc.$old_dir.'/.SUB/'.$old_fname_noext.'.srt',
                                   $startloc.$old_dir.'/.SUB/'.$new_fname.'.srt');
                if (!$renaming) {
                    # srt 이름 바꾸기 실패
                    header('Location: '.$url.'&fail=9');
                    exit();
                };
            }

            # 썸네일 존재여부 체크
            if (file_exists($startloc.$old_dir.'/.THUMB/'.$old_fname_noext.'.jpg')) {
                $renaming = rename($startloc.$old_dir.'/.THUMB/'.$old_fname_noext.'.jpg',
                                   $startloc.$old_dir.'/.THUMB/'.$new_fname.'.jpg');
                if (!$renaming) {
                    # srt 이름 바꾸기 실패
                    header('Location: '.$url.'&fail=10');
                    exit();
                };
            }


            if ($query) {
                # 쿼리쏘기 OK
                header('Location: '.$url);
                exit();
            } else {
                # 쿼리쏘기 NG
                header('Location: '.$url.'&fail=2');
                exit();
            }
        }
    } else {
        # DB 검색결과에 ID가 없음
        header('Location: '.$url.'&fail=18');
        exit();
    }
} else {
    # DB 검색결과에 ID가 없음
    header('Location: '.$url.'&fail=2');
    exit();
}

?>