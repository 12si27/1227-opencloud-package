<!-- 로그인 페이지 -->

<?php
    session_start();

    if(isset($_SESSION['userid']))
    {
        echo '<script>alert("이미 로그인되어 있습니다");history.back();</script>';
    exit();
    }

    $fail = $_GET['fail'];
    $ourl = $_GET['ourl'];
?>

<!doctype html>
<html>
    <head>
        <!-- Required meta tags -->
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
        <meta name="mobile-web-app-capable" content="yes">
        <meta name="theme-color" content="#1c2c3b">
        <link rel="icon" sizes="192x192" href="../img.png">

        <!-- Bootstrap CSS -->
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-1BmE4kWBq78iYhFldvKuhfTAU6auU8tT94WrHftjDbrCEXSU1oBoqyl2QvZ6jIW3" crossorigin="anonymous">

        <script src="https://kit.fontawesome.com/b435844d6f.js" crossorigin="anonymous"></script>
        <title>1227 오픈클라우드 관리 Login</title>

        <style>

        @import url(//spoqa.github.io/spoqa-han-sans/css/SpoqaHanSansNeo.css);
        @import url('https://rsms.me/inter/inter.css');

        :root
        {
            --bs-font-sans-serif: 'Inter', 'Spoqa Han Sans Neo', Roboto, "Helvetica Neue", Arial, 'Noto Sans CJK', "Segoe UI", normal Arial, Helvetica, 'sans-serif';
            color-scheme: dark;
        }

        .login-bg {
            background-image: url('../../images/background.png');
            background-size: cover;
        }

        .form-control, .form-control:focus{
            border: solid, #373c43;
            background-color: #222529;
            color: white;
        }


    </style>
    </head>
    <body>
        
        <section class="vh-100 login-bg">
            <div class="container py-5 h-100">
                <div class="row d-flex justify-content-center align-items-center h-100">
                <div class="col-12 col-md-8 col-lg-6 col-xl-5">
                    <div class="card bg-dark text-white shadow" style="border-radius: 1rem;">
                        <div class="card-body p-5 text-center">

                            <form class="mb-md-5 mt-md-4 pb-5" method="POST" action="./login_check.php">

                                <h2 class="fw-bold mb-2 text-uppercase">로그인</h2>
                                <p class="text-white-50 mb-5">관리자 계정 로그인을 위해 ID/PW를 입력해 주세요</p>

                                <?php if ($ourl != null) {
                                    ?> <input hidden name="ourl" value="<?=$ourl?>"> <?php
                                } ?>
                                

                                <div class="form-floating mb-4">
                                    <input type="id" name="id" class="form-control" id="floatingInput" placeholder="id">
                                    <label for="floatingInput">ID</label>
                                </div>

                                <div class="form-floating mb-4">
                                    <input type="password" name="pw" class="form-control" id="floatingPassword" placeholder="Password">
                                    <label for="floatingPassword">Password</label>
                                </div>

                                <?php

                                if ($fail == 1 OR $fail == 2 OR $fail == 3) {
                                ?>
                                    <svg xmlns="http://www.w3.org/2000/svg" style="display: none;">
                                        <symbol id="exclamation-triangle-fill" fill="currentColor" viewBox="0 0 16 16">
                                            <path d="M8.982 1.566a1.13 1.13 0 0 0-1.96 0L.165 13.233c-.457.778.091 1.767.98 1.767h13.713c.889 0 1.438-.99.98-1.767L8.982 1.566zM8 5c.535 0 .954.462.9.995l-.35 3.507a.552.552 0 0 1-1.1 0L7.1 5.995A.905.905 0 0 1 8 5zm.002 6a1 1 0 1 1 0 2 1 1 0 0 1 0-2z"/>
                                        </symbol>
                                    </svg>

                                    <div class="alert alert-danger d-flex align-items-center" role="alert">
                                        <svg class="bi flex-shrink-0 me-2" width="24" height="24" role="img" aria-label="Danger:"><use xlink:href="#exclamation-triangle-fill"/></svg>
                                        <div>
                                            <?php
                                            if ($fail == 1) {
                                                echo "아이디와 비밀번호를 입력해 주세요.";
                                            } elseif ($fail == 2) {
                                                echo "아이디 또는 비밀번호가 올바르지 않거나 존재하지 않는 계정입니다.";
                                            } elseif ($fail == 3) {
                                                echo "세션 생성에 실패하였습니다.";
                                            }
                                            ?>
                                        </div>
                                    </div>
                                <?php
                                }
                                ?>

                                <button class="btn btn-outline-light btn-lg px-5" type="submit">로그인</button>

                            </form>

                            <div>
                                <p class="mb-0 text-secondary">이 페이지는 운영자 전용 페이지입니다</p>
                            </div>

                        </div>
                    </div>
                </div>
                </div>
            </div>
        </section>
        

        <!-- Option 1: Bootstrap Bundle with Popper -->
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-ka7Sk0Gln4gmtz2MlQnikT1wXgYsOg+OMhuP+IlRH9sENBO0LRn5q+8nbTov4+1p" crossorigin="anonymous"></script>

    </body>
</html>