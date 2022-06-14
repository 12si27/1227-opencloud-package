<?php
/*
    player.php
    1227 CloudPlayer PLAYER OBJECT PAGE

    Written by 1227
    rev. 20220528 (0.21.1)
*/

if ($rev == null) {
    echo 'invalid access';
    exit;
}

// 잠금 영상일 경우 -> 영상 영역에서 암호입력폼 띄우기
if (!$key_passed) {
    ?>
    <!-- 잠금 영상 키제출폼 -->
    <style>
        .form-control, .form-control:focus{
            border: solid, #373c43;
            background-color: rgb(0,0,0,0.3);
            color: white;
        }
        .form-floating {
            color: white;
        }
        

        #locked_vid {
            background-image: url('<?=($thumb != "" ? urlenc_wos($thumb):'./res/default_thumb.png')?>');
            background-size: cover;
            background-repeat: no-repeat;
            
        }

        #locked_vid::before {
            backdrop-filter: blur(10px) brightness(50%);
        }
        </style>
    <div class="container ratio ratio-16x9 px-0" id="locked_vid">
        <div class="row d-flex justify-content-center align-items-center h-100">
            <div class="col-12 col-md-8 col-lg-6 col-xl-5">
                <form class="ps-4" method="POST" action="<?=$redir_url?>">
                    <h2 class="fw-bold text-light mb-2"><i class="fa-solid fa-lock"></i> 잠긴 영상</h2>
                    <p class="text-white-50 mb-3">비디오 열람을 위해 암호(키)가 필요합니다.</p>
                    <div class="form-floating mb-2">
                        <input type="" autocomplete="off" name="key" class="form-control" id="floatingInput" placeholder="key" tabindex="0" autofocus="autofocus" onfocus="this.select()" required>
                        <label for="floatingInput">여기에 입력해 주세요</label>
                    </div>
                    <input hidden name="tried" value="1">
                    <div class="d-flex justify-content-between">
                    
                    <button class="btn btn-outline-light btn-lg px-5" type="submit">제출</button>
                    <?php
                    if ($key_fail == 1 OR $key_fail == 2) {
                    ?>
                        <div class="fs-6 text-danger">
                            <?php
                            if ($key_fail == 1) {
                                echo "키 값을 입력해 주세요.";
                            } elseif ($key_fail == 2) {
                                echo "올바르지 않은 키값입니다.";
                            }
                            ?>
                        </div>
                    <?php } ?>
                    </div>
                </form>
            </div>
        </div>
    </div>
<?php
// 잠금 영상이 아니거나 키 패스한 경우
} else {

    // 만약 t= 파라미터 있으면 전달하기 위해 넣기
    if ($_GET['t'] != null) {
        if (is_numeric($_GET['t'])) {
            $video_t = '#t='.$_GET['t'];
        }
    }

    // 플레이어 컨테이너 출력
    ?><div class="container ratio ratio-16x9 px-0" id="play_container" style="max-height: calc(100vh - 128px);"><?php

    // 1227 자체 플레이어
    if ($nplayer == false) { ?>
    <video  id="stream_video"
            style="position: absolute; overflow: hidden;"
            class="video-js vjs-theme-1227 vjs-big-play-centered"
            width="100%" height="100%" preload="true"
            controls controlsList="nodownload"
            poster="<?=($thumb != "" ? urlenc_wos($thumb):'./res/default_thumb.png')?>"
            data-setup='{ "html5": {"nativeTextTracks": <?=($no_vttjs ? 'true' : 'false')?> } }'
            oncontextmenu="return false;" autoplay>
        <source src="<?=$stream_url.$video_t?>" type='video/mp4' />
    <?php if ($caption != '') { captionTagPrint($path_parts['filename'], $caption, $no_punct, $caplang); } ?>
    </video>
    <script src="./js/video.min.js?rev=<?=$rev?>"></script>
    <script src="./js/video.fullscreen.js"></script>
    <script src="./js/video.mobileui.js?rev=1"></script>
    <script src="./js/video.hotkeys.min.js"></script>
<?php
    // HTML5 브라우저 자체 플레이어
    } else { ?>
        <video id="stream_video_html5_api" width="100%" height="auto" autoplay controls
               poster="<?=($thumb != "" ? urlenc_wos($thumb):'./res/default_thumb.png')?>"
               oncontextmenu="return false;" controlsList="nodownload">
            <source src="<?=$stream_url.$video_t?>" type='video/mp4' />
            <?php if ($caption != '') { captionTagPrint($path_parts['filename'], $caption, $no_punct, $caplang); } ?>
        </video>
<?php } ?>
    </div>
<?php
}
    