<?php
/*
    index.php
    1227 OpenCloud Explorer INDEX PAGE

    Written by 1227
    rev. 20220522 (1.16.8)
*/

	define('PROJECT_ROOT', getcwd());
	$rev = '1.16.8';
?> 

<!DOCTYPE html>
<html> 
	<head lang="en">
		<meta charset="UTF-8">
		<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
		<meta name="mobile-web-app-capable" content="yes">
		<meta name="theme-color" content="#1c2c3b">
		<link rel="icon" sizes="192x192" href="img.png">
		<title>1227 백업 오픈클라우드</title>

		<!-- OpenCloud Explorer Script & Stylesheet -->
		<link href="assets/css/styles.css?ver=<?=$rev?>" rel="stylesheet"/> 
		<script src="https://code.jquery.com/jquery-3.2.1.js"></script>

		<!-- FancyBox --> 
		<link rel="stylesheet" href="assets/fancybox/jquery.fancybox.min.css?ver=1.0" />
		<script src="assets/fancybox/jquery.fancybox.min.js?ver=1.0"></script>

		<!-- Font Awesome -->
		<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.css" />

		<!-- Global site tag (gtag.js) - Google Analytics -->
		<script async src="https://www.googletagmanager.com/gtag/js?id=G-RGCW2QEFK9"></script>
		<script>
			window.dataLayer = window.dataLayer || [];
			function gtag(){dataLayer.push(arguments);}
			gtag('js', new Date());

			gtag('config', 'G-RGCW2QEFK9');
		</script>

	</head> 
	<body>
			<div class="filemanager">
			<div class="search">
				<input type="search" placeholder="파일 찾기.." />
			</div>
			<div class="breadcrumbs">12:<font color="5aa1ef">27</font> 백업 오픈클라우드</div>
			<a class="button folderName" id="backButton" href=""><i class="fa fa-arrow-left" aria-hidden="true"></i> 뒤로 가기</a>
			<a class="button" href="#Home"><i class="fa fa-home" aria-hidden="true"></i> 처음으로</a>

			<ul class="data animated"></ul>

			<div id="loading">
				<span>잠시만 기다려 주세요...</span>
			</div>
			<div class="nothingfound">
				<div class="nofiles"></div>
				<span>아무것도 없어요.<br></span>
			</div>

			<br>
			<div class="fixed" style="margin-top: 20px;">
				<div style="text-align: center;">
					<span style="color: #ffffff; opacity: 0.6;">by 1227<br>
					<div style="opacity: 0.15; margin-top: 12px; font-size: small;">rev. <?=$rev?></div>
				</div><br><br>
			</div>
				
	<script src="assets/js/script.js?ver=<?=$rev?>"></script>

	</body>
</html>

