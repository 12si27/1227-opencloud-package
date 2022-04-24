<?php 
	define('PROJECT_ROOT', getcwd());
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
		<link href="assets/css/styles.css?ver=1.8" rel="stylesheet"/> 
		<script src="https://code.jquery.com/jquery-3.2.1.js"></script>
		<script src="assets/js/script.js?ver=1.9"></script>

		<!-- FancyBox --> 
		<link rel="stylesheet" href="assets/fancybox/jquery.fancybox.min.css?ver=1.0" />
		<script src="assets/fancybox/jquery.fancybox.min.js?ver=1.0"></script>

		<!-- Font Awesome -->
		<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.css" />

	</head> 
	<body>
			<div class="filemanager">
			<div class="search">
				<input type="search" placeholder="파일 찾기.." />
			</div>
			<div class="breadcrumbs"></div>
			<a class="button folderName" id="backButton" href=""><i class="fa fa-arrow-left" aria-hidden="true"></i> 뒤로 가기</a>
			<a class="button" href="#Home"><i class="fa fa-home" aria-hidden="true"></i> 처음으로</a>

			<ul class="data"></ul>
			<div class="nothingfound">
				<div class="nofiles"></div>
				<span>아무것도 없어요.<br><font color="gray" size="2"><center>문제라고 생각되면<br>운영자에게 알려주세요</center></font></span>
			</div>
				
			</div>

			<br>
			<div class="fixed">
				<p style="text-align: center;"><span style="color: #ffffff; opacity: 0.5;">by 1227<br>
			</div>
	
	<script>
	$('.fancybox-media').fancybox({
		type: 'iframe',
		width: 800,
		height: 580,
		// add
		fitToView: false,
		iframe : {
		preload : false
		}
	});
	</script>

	</body>
</html>
