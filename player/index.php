<?php
/*
    index.php
    1227 CloudPlayer INDEX PAGE

    Written by 1227
    rev. 20220612 (0.22.15)
*/

# 리비전 -> 이거 없으면 실행 안됨 (index.php를 통해 실행했는지 체크여부도 겸함)
$rev = '0.22.15';
require('./src/preload.php');

# 출력 압축을 위해 버퍼 미리 시작
ob_start();
?>
<!doctype html>
<html lang="ko">
    <head>
        <!-- Required meta tags -->
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
        <meta name="mobile-web-app-capable" content="yes">
        <meta name="theme-color" content="#1c2c3b">
        <link rel="icon" sizes="192x192" href="../img.png">

        <!-- CSS -->
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-1BmE4kWBq78iYhFldvKuhfTAU6auU8tT94WrHftjDbrCEXSU1oBoqyl2QvZ6jIW3" crossorigin="anonymous">
        <link href="./css/style.css?rev=<?=$rev?>" rel="stylesheet">
        <script src="https://kit.fontawesome.com/b435844d6f.js" crossorigin="anonymous"></script>

        <!-- 자막 스타일 CSS 출력 (srt만) -->
        <?php require('./src/substyling.php') ?>

        <!-- video.js CSS load -->
        <?php if ($nplayer == false) { ?>
        <link href="https://vjs.zencdn.net/7.18.1/video-js.css" rel="stylesheet" />
        <link href="./css/theme.css?rev=<?=$rev?>" rel="stylesheet">
        <link href="https://cdn.jsdelivr.net/npm/videojs-mobile-ui/dist/videojs-mobile-ui.css" rel="stylesheet">
        <?php } ?>

        <title><?=$path_parts['filename']?> - 1227 클라우드플레이어</title>
        
    </head>
    <body <?php if (!$direct) { ?>onunload="opener.vidAccent(<?=$order+1?>);"<?php } ?>>
        <style id="bodyStyle"></style>
        <nav class="navbar navbar-dark" id="nav">
            <div class="container">
                <a class="navbar-brand mb-0 ps-1 fw-bolder" href="../" style="color: rgb(150,150,150);">
                    12:<span style="color: #5aa1ef;">27</span> CloudPlayer <?php
                    if ($test_mode) { echo '<span style="font-size: x-small;">TEST MODE</span>'; } ?>
                </a>
                <div class="d-flex">
                <?php if ($direct) { ?>
                    <a class="btn btn-outline-light" onclick="window.stop();" href="../#Home%2F<?=urlencode($currdir)?>"><i class="fa-solid fa-grip"></i> 목록으로</a>
                <?php } else { ?>
                    <a class="btn btn-outline-light" onclick="window.close();"><i class="fa-solid fa-door-open"></i> 나가기</a>
                <?php } ?>
                </div>
            </div>
        </nav>
        <?php

        # 크레딧 타임 존재할때 프리로드
        if ($credit_time !== null) { ?> <input hidden id="credit-time" value="<?=$credit_time?>"> <?php }
        ?>

        <!-- 플레이어 -->
        <div class="bg-black">
            <?php require('./src/player.php') ?>
        </div>

        <?php
        // 제목 처리
        $title = $path_parts['filename'];
        # 대쉬가 있을 경우
        if (strpos($title, '-') !== false) {
            $series = trim(substr($title, 0, strpos($title, '-')));
            $name = trim(substr($title, strpos($title, '-') + 1));

            # 분류태그 ([]) 도 (짝이 맞게) 있을 경우
            if (strpos($title, '[') !== false) {
                if (mb_substr_count($title, '[', 'utf-8') == mb_substr_count($title, ']', 'utf-8')) {
                    # 제목에서 태그 떼기
                    $name = trim(substr($name, 0, strpos($name, '[')));

                    $tag = trim(substr($title, strpos($title, '[')));
                    $tag = str_replace('[', '<span class="badge bg-secondary" style="position:relative; bottom: 1.5pt;">', $tag);
                    $tag = str_replace(']', '</span>', $tag);
                    $tag = str_replace('</span> ', '</span>&nbsp;', $tag);
                }
            }
        }
        ?>

        

        <!-- 메인 컨트롤 -->
        <div class="main-control shadow-sm">
            <div class="container py-1">
                <div class="d-flex flex-wrap">
                    <div class="p-2 fw-bolder title">
                        <?php
                        // 제목 출력
                        if ($series != NULL and $name != NULL) {
                            ?>
                            <div style="font-size: 10pt; font-weight: 500;"><?=$series.' '.$tag?></div>
                            <div style="font-size: 14pt; word-break: break-all;"><?=$name?></div>
                            <?php
                        } else {
                            ?> <span class="fs-5"> <?php
                            $title = str_replace('[', '<span class="badge bg-secondary">', $title);
                            $title = str_replace(']', '</span>', $title);
                            echo $title;
                            ?> </span> <?php
                        }

                        // 잠금 아이콘 표시 (잠금 비디오일때)
                        echo '<span style="opacity: 0.5;">';
                        if ($lock_video) {
                            if ($key_passed) {
                                echo '<i class="fa-solid fa-lock-open"></i>';
                            } else {
                                echo '<i class="fa-solid fa-lock"></i>';
                            }
                        }
                        echo '</span>';
                        ?>
                    </div>

                    <div class="p-2 flex-fill d-flex justify-content-end align-items-center">
                        <!-- 다음 버튼부터는 ms-2 넣기 -->
                        <span class="btn btn-sm" style="background-color: #3f3f3f; color: #cdcdcd;" disabled
                        data-bs-toggle="tooltip" data-bs-placement="bottom" title="이 영상을 열람한 횟수">
                        <i class="fa-solid fa-eye"></i><span class="bt-text"> <?=number_format($views)?></span></span>

                        <!-- 아래는 영상이 재생 가능할때만 표시 -->
                        <?php if ($key_passed) { ?>

                        <button class="btn btn-sm btn-dark ms-2" onclick="switchPlayerSize();"
                        title="창에 플레이어가 꽉 차도록 셋팅합니다." id="playSizeBt"><i class="fa-solid fa-expand"></i><span class="bt-text"> 꽉차게</span></button>

                        <a class="btn btn-sm btn-dark ms-2" href="?video=<?=urlencode($_GET['video'])?>&np=<?=($nplayer?0:1)?><?=($direct?'&direct=1':'')?>&r=1"
                           data-bs-toggle="tooltip" onclick="window.stop();" data-bs-html="true" data-bs-placement="bottom" title="<?php
                            if ($nplayer) { echo '1227 백업클라우드에서 제공하는 자체 플레이어로 재생합니다.</br><sub>대부분의 환경에서 추천합니다.</sub>'; }
                                     else { echo '브라우저에 내장된 플레이어로 재생합니다.</br><sub>iOS/레거시 환경에서 추천합니다.</sub>'; }
                            ?>"><?php if ($nplayer) { echo '<i class="fa-solid fa-circle-play"></i><span class="bt-text"> 자체</span>'; }
                                               else { echo '<i class="fas fa-tv"></i><span class="bt-text"> 기본</span>'; } ?></a>

                        <button class="btn btn-sm btn-dark ms-2" onclick="copyClipboard('https://cloud.1227.kr/v/?id=<?=$vidid?>')"
                        data-bs-toggle="tooltip" data-bs-placement="bottom" title="이 영상으로 들어갈 수 있는 짧은 링크를 복사합니다.">
                        <i class="fas fa-link"></i><span class="bt-text"> 링크</span></button>

                        <a class="btn btn-sm btn-dark ms-2"
                        href="<?=searchUrlGen($path_parts['filename'])?>"
                        target="_blank"
                        data-bs-toggle="tooltip" data-bs-html="true" data-bs-placement="bottom"
                        title="실시간 검색을 통해 해당 영상의 1227.kr 포스트로 접속합니다.">
                        <img src="./naverblog.svg" width=18px height=18px></img><span class="bt-text"> 검색</span></a>

                        <?php } ?>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="container mt-3">

            <?php
            if(!$isitfirst) {
                $prev_link = '?video='.urlencode($currdir.'/'.$files[$order-1]).($direct?'&direct=1':'').'&r=1';
            } elseif ($smart_prev_fname != '') {
                $prev_link = '?video='.urlencode($currsubdir_v.$smart_prev.'/'.$smart_prev_fname).($direct?'&direct=1':'').'&r=1';
            }

            if(!$isitlast) {
                $next_link = '?video='.urlencode($currdir.'/'.$files[$order+1]).($direct?'&direct=1':'').'&r=1';
            } elseif ($smart_next_fname != '') {
                $next_link = '?video='.urlencode($currsubdir_v.$smart_next.'/'.$smart_next_fname).($direct?'&direct=1':'').'&r=1';
            }
            ?>

            <!-- 비디오 탐색 버튼 -->
            <div class="row">
                <div class="col-md-6">
                    <div class="shadow px-3 py-2 mb-2 switch-bt prev-bt" id="prev-bt" thumb="<?=urlenc_wos($prev_thumb)?>">
                        <?php if ($prev_link != NULL) { ?> <a class="switch-a" id="prev-link" onclick="window.stop();" href="<?=$prev_link?>"> <?php } ?>
                            <div class="fw-bold switch-title">이전 비디오<?=($smart_prev_fname!=''?' <small><i>스마트 추천</i></small>':'')?></div>
                            <div class="text-wrap switch-content"><?php
                            if ($smart_prev_fname != '') {    
                                echo substr($smart_prev_fname, 0, strrpos($smart_prev_fname, '.'));
                            } elseif ($isitfirst) {
                                echo "<span style='color: rgb(40,40,40);'>첫번째 비디오입니다</span>";
                            } else {
                                echo substr($files[$order-1], 0, strrpos($files[$order-1], '.'));
                            }
                            ?></div>
                        <?php if ($prev_link != NULL) { ?> </a> <?php } ?>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="shadow px-3 py-2 mb-2 switch-bt next-bt" id="next-bt" thumb="<?=urlenc_wos($next_thumb)?>">
                        <?php if ($next_link != NULL) { ?> <a class="switch-a" id="next-link" onclick="window.stop();" href="<?=$next_link?>"> <?php } ?>
                            <div class="fw-bold switch-title">다음 비디오<?=($smart_next_fname!=''?' <small><i>스마트 추천</i></small>':'')?></div>
                            <div class="text-wrap switch-content" id="next-title"><?php
                            if ($smart_next_fname != '') {    
                                echo substr($smart_next_fname, 0, strrpos($smart_next_fname, '.'));
                            } elseif ($isitlast) {
                                echo "<span style='color: rgb(40,40,40);'>마지막 비디오입니다</span>";
                            } else {
                                echo substr($files[$order+1], 0, strrpos($files[$order+1], '.'));
                            }
                            ?></div>
                        <?php if ($next_link != NULL) { ?> </a> <?php } ?>
                    </div>
                </div>
            </div>

            <!-- 비디오 관련 토스트 -->
            <?php
            if (strpos($path_parts['filename'], '[HD+]') !== false) {
                showAlert('HD+ 화질', '트래픽 부담을 줄이기 위해 약간 낮은 화질(900p)로 제공됩니다.<br><span style="font-size: x-small; opacity: 0.3;">원본 화질 시청을 원하실 경우 \'포스트 검색\'을 눌러 포스트 영상을 보시기 바랍니다.</span>');
            } elseif (strpos($path_parts['filename'], '[LQ]') !== false) {
                showAlert('저화질 영상','아직 고화질 영상이 존재하지 않는 비디오(480p 이하)입니다. 양해 부탁 드립니다.');
            }

            if (strpos($path_parts['filename'], '[EN]') !== false) {
                showAlert('영어 자막', '본 영상은 영어 자막이 포함되어 있습니다. 시청 시 참고 바랍니다.');
            }
            ?>

            <?php // 아래 자막 옵션, 비디오, 자막 정보는 키 검증을 통과하여야 출력됨
            if ($key_passed) { ?>

            <!-- 내장 자막 옵션 -->
            <?php if ($caption != '') { ?>

            <div class="card mt-2">
                <div class="card-body">
                <h6 class="card-subtitle mb-2 text-muted">내장 자막 설정</h6>
                    <form id="subSetForm" class="d-flex flex-wrap align-items-center" method="GET" onsubmit="addVidTime(); window.stop();">
                        <div class="form-check form-switch me-3" style="color: #afafaf;">
                            <input class="form-check-input" type="checkbox" name="no_punct" value="1" id="no_punct" <?=($no_punct=='1'?'checked':'')?>>
                            <label class="form-check-label" for="no_punct">문장 부호 ( '.' , '~' , '!' 등) 최소화</label>
                            <span style="opacity: 0.5;" data-bs-toggle="tooltip" data-bs-html="true" data-bs-placement="bottom"
                            title="보편적인 자막 형식에 맞춰 마침표나 느낌표 등의 문장 부호를 최소화하여 출력합니다.">&nbsp;<b><u>?</u></b></span>
                        </div>
                        <div class="form-check form-switch me-3" style="color: #afafaf;" <?=($nplayer?'hidden':'')?>>
                            <input class="form-check-input" type="checkbox" name="no_sub_bg" value="1" id="no_sub_bg" <?=($no_sub_bg=='1'?'checked':'')?> <?=($no_vttjs?'disabled':'')?>>
                            <label class="form-check-label" for="no_sub_bg">반투명 배경 상자 숨기기</label>
                        </div>
                        <div class="form-check form-switch" style="color: #afafaf;" <?=($nplayer?'hidden':'')?>>
                            <input class="form-check-input" type="checkbox" name="no_vttjs" value="1" id="no_vttjs" <?=($no_vttjs=='1'?'checked':'')?>>
                            <label class="form-check-label" for="no_vttjs">레거시 자막 엔진 <span style="font-size: small; opacity: 0.5;">저사양 추천</span></label>
                        </div>
                        <input hidden name="video" value="<?=$_GET['video']?>">
                        <?php if($direct) { ?><input hidden name="direct" value="1"><?php } ?>
                        <input hidden name="apply" value="1">
                        <div class="d-flex justify-content-end flex-grow-1 mt-2">
                            <a class="btn btn-sm subview" href="../subview/?c=<?=urlencode($caption)?>" target="_blank">뷰어 (오류 제보)</a>
                            <button type="submit" class="btn btn-sm btn-outline-light ms-1">적용</button>
                        </div>
                    </form>
                </div>
            </div>
            <?php
            }
            ?>

            <div class="row mt-3">
                <!-- 비디오 정보 -->
                <div class="col<?=($caption==''?'':'-sm-6')?>">
                    <div class="card mt-2">
                        <div class="card-body">
                        <h6 class="card-subtitle mb-2 text-muted">비디오 정보</h6>
                            <div style="font-size:small; color: gray;">
                            비디오 형식: <?=$path_parts['extension']?><span id="vid_length"></span><span id="vid_res"></span></br>
                            비디오 용량: <span id='vid_size'><?=filesize($video)?></span>B (<?=round(filesize($video)/1024/1024,2)?>MB<span id="bitrate_info"></span>) </br>
                            마지막으로 수정된 날짜: <?=date("Y-m-d H:i:s", filemtime($video));?> </br>
                            마지막으로 열람된 날짜: <?=$last_chk?>
                            </div>
                        </div>
                    </div>
                </div>

                <?php if ($caption != '') { ?>
                <div class="col-sm-6">
                <!-- 자막 정보 -->
                <div class="card mt-2">
                    <div class="card-body">
                        <h6 class="card-subtitle mb-2 text-muted">내장 자막 정보</h6>
                        <div style="font-size:small; color: gray;">
                        자막 형식: <?=$captype?> · 언어: <?=$caplang?></br>
                        자막 용량: <?=filesize($startloc.$caption).'B ('.round(filesize($startloc.$caption)/1024,2).'KB)'?> </br>
                        마지막으로 수정된 날짜: <?=date("Y-m-d H:i:s", filemtime($startloc.$caption))?> </br>
                        문장 부호 숨김 여부: <?=($no_punct==1?'Y':'N')?>
                        </div>
                    </div>
                </div>
                </div>
            <?php } ?>
            </div>
        </div>
        <?php } ?>

        <div class="fixed my-3">
            <div style="text-align: center; color: #ffffff; opacity: 0.3;">
                <div>by 1227 · <a href="javascript:void(0);" style="color: #ffffff;" data-bs-toggle="modal" data-bs-target="#keyModal">단축키</a></div>
                <div class="mt-1" style="font-size: x-small;">rev. <?=$rev?> · 뷰 ID: <?=strtoupper($viewid)?></div>
            </div>
        </div>

        <!-- 단축키 Modal -->
        <div class="modal fade" id="keyModal" tabindex="-1" aria-labelledby="keyModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header pb-0">
                        <h5 class="modal-title" id="keyModalLabel">플레이어 단축키</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                    <table class="table">
                        <thead>
                            <tr>
                                <th scope="col">키</th>
                                <th scope="col">설명</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <th>스페이스 바 <sub>또는</sub> 클릭 *</th>
                                <td>영상 재생/정지</td>
                            </tr>
                            <tr>
                                <th>↑, ↓ 키 *</th>
                                <td>볼륨 증가, 감소</td>
                            </tr>
                            <tr>
                                <th>←, → 키 <sub>또는</sub> 좌/우 더블 탭 *</th>
                                <td>10초 전/후 이동</td>
                            </tr>
                            <tr>
                                <th>F <sub>또는</sub> 더블 클릭 *</th>
                                <td>전체화면</td>
                            </tr>
                            <tr>
                                <th>M *</th>
                                <td>음소거</td>
                            </tr>
                            <tr>
                                <th>T</th>
                                <td>플레이어 확장/축소</td>
                            </tr>
                            <tr>
                                <th><(,) / >(.) **</th>
                                <td>이전/다음 비디오</td>
                            </tr>
                        </tbody>
                        </table>                        
                    </div>
                    <div class="modal-footer pt-0 mt-0 d-flex justify-content-between">
                        <div style="font-size: x-small">* 플레이어가 선택된 상태에서만 사용 가능합니다<br>** 재생 후 일정 시간이 지나야 사용 가능합니다</div>
                        <button type="button" class="btn btn-outline-light" data-bs-dismiss="modal">확인</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Bootstrap Bundle with Popper -->
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-ka7Sk0Gln4gmtz2MlQnikT1wXgYsOg+OMhuP+IlRH9sENBO0LRn5q+8nbTov4+1p" crossorigin="anonymous"></script>
        <script src="./js/script.js?rev=<?=$rev?>"></script>
    </body>
    
    <?php if(!$direct): # 다이렉트가 아닐 경우에만 ?>
    <script> 
        <?php # 현재 비디오의 로케이션 - 만약 startloc에 ../이 두개이상일경우 따로 설정!!
        $curr_hash = str_replace('../', '', $startloc.$currdir); ?>
        opener.currVid = <?=$order+1?>;

        // 탐색기 디렉토리 자동설정 -> 매칭 안할때
        if (opener.location.hash != "#<?=urlencode($curr_hash)?>") {
            opener.location.hash = "#<?=urlencode($curr_hash)?>";
        } else { opener.vidAccent(<?=$order+1?>); }
    </script>
    <?php endif ?>
    
</html>
<?php

# 출력 압축 처리
require_once('./src/optimizer.php');