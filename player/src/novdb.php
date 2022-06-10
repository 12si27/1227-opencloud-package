<?php
/*
    novdb.php
    1227 CloudPlayer 미등록 안내 페이지
    (반드시 index.php 내에서 실행되어야 함)

    Written by 1227
    rev. 20220528
*/
if ($rev == null) {
    echo 'invalid access';
    exit;
}

?>
<!DOCTYPE html>
<html>
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
        <style>
            @import url('https://rsms.me/inter/inter.css');
            @import url(//spoqa.github.io/spoqa-han-sans/css/SpoqaHanSansNeo.css);

            :root {
                font-family: 'Inter', 'Spoqa Han Sans Neo', 'Noto Sans Serif CJK', '맑은 고딕';
            }

            .container {
                width: 80%;
                margin: auto;
                margin-top: 5%;
                position: relative;
                min-height: 60vh;
            }

            .main {
                font-weight: 700;
                font-size: 60pt;
                line-height: 100%;
                color: #5aa1ef;
            }

            .sub {
                margin-top: 10pt;
                font-weight: 800;
                font-size: 30pt;
                color: grey;
            }

            footer {
                width: 100%;
                height: 100px;
                bottom: 0px;
                text-align: right;
                position: absolute;
                z-index: -1;
            }

            a {
                font-weight: 800;
                font-size: 20pt;
                color: #5a5a5a;
            }
        </style>
        <title>미등록 비디오</title>
    </head>
    <body>
        <div class="container">
            <div class="main">
            미등록
            </div>

            <div class="sub">
            > Unregistered Video
            </div>

            <div class="content">
            <br>아직 서버에 완전히 등록되지 않았거나 현재 처리중인 비디오입니다.
            <br>문제라고 생각될 시 관리자에게 문의해 주시기 바랍니다.
            <p><?php if ($direct) { ?>
                    <a href="../#Home%2F<?=urlencode($currdir)?>">목록으로</a>
                <?php } else { ?>
                    <a href="javascript:void(0);" onclick="window.close();">나가기</a>
                <?php } ?></p>
            </div>
            
            <footer><img src="https://museum.1227.kr/2021/delreq/dbmo.png" width="300px"></footer>
        </div>
    </body>
</html>