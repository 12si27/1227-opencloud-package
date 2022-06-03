$(function() {

	var filemanager = $('.filemanager'),
		breadcrumbs = $('.breadcrumbs'),
		fileList = filemanager.find('.data');

	// Start by fetching the file data from scan.php with an AJAX request

	$.get('scan.php', function(data) {

		var response = [data],
			currentPath = '',
			breadcrumbsUrls = [];

		var folders = [],
			files = [];

		// This event listener monitors changes on the URL. We use it to
		// capture back/forward navigation in the browser.

		$(window).on('hashchange', function() {

			goto(window.location.hash);

			// We are triggering the event. This will execute 
			// this function on page load, so that we show the correct folder:

		}).trigger('hashchange');


		// Hiding and showing the search box

		filemanager.find('.search').click(function() {

			var search = $(this);

			search.find('span').hide();
			search.find('input[type=search]').show().focus();

		});


		// Listening for keyboard input on the search field.
		// We are using the "input" event which detects cut and paste
		// in addition to keyboard input.

		filemanager.find('input').on('input', function(e) {

			folders = [];
			files = [];

			var value = this.value.trim();

			if (value.length) {

				filemanager.addClass('searching');

				// Update the hash on every key stroke
				window.location.hash = 'search=' + value.trim();

			} else {

				filemanager.removeClass('searching');
				window.location.hash = encodeURIComponent(currentPath);

			}

		}).on('keyup', function(e) {

			// Clicking 'ESC' button triggers focusout and cancels the search

			var search = $(this);

			if (e.keyCode == 27) {

				search.trigger('focusout');

			}

		}).focusout(function(e) {

			// Cancel the search

			var search = $(this);

			if (!search.val().trim().length) {

				window.location.hash = encodeURIComponent(currentPath);
				search.hide();
				search.parent().find('span').show();

			}

		});


		// Clicking on folders

		fileList.on('click', 'li.folders', function(e) {
			e.preventDefault();

			var nextDir = $(this).find('a.folders').attr('href');

			if (filemanager.hasClass('searching')) {

				// Building the breadcrumbs

				breadcrumbsUrls = generateBreadcrumbs(nextDir);

				filemanager.removeClass('searching');
				filemanager.find('input[type=search]').val('').hide();
				filemanager.find('span').show();
			} else {
				breadcrumbsUrls.push(nextDir);
			}

			window.location.hash = encodeURIComponent(nextDir);
			currentPath = nextDir;
		});


		// Clicking on breadcrumbs

		breadcrumbs.on('click', 'a', function(e) {
			e.preventDefault();

			var index = breadcrumbs.find('a').index($(this)),
				nextDir = breadcrumbsUrls[index];

			breadcrumbsUrls.length = Number(index);

			window.location.hash = encodeURIComponent(nextDir);

		});


		// Navigates to the given hash (path)

		function goto(hash) {

			hash = decodeURIComponent(hash).slice(1).split('=');

			if (hash.length) {
				var rendered = '';

				// if hash has search in it

				if (hash[0] === 'search') {

					filemanager.addClass('searching');
					rendered = searchData(response, hash[1].toLowerCase());

					if (rendered.length) {
						currentPath = hash[0];
						render(rendered);
					} else {
						render(rendered);
					}

				}

				// if hash is some path
				else if (hash[0].trim().length) {

					rendered = searchByPath(hash[0]);

					/*
					if (rendered.length) {

						currentPath = hash[0];
						breadcrumbsUrls = generateBreadcrumbs(hash[0]);
						render(rendered);

					}
					else {
						currentPath = hash[0];
						breadcrumbsUrls = generateBreadcrumbs(hash[0]);
						render(rendered);
					}*/

					currentPath = hash[0];
					breadcrumbsUrls = generateBreadcrumbs(hash[0]);
					fileList.fadeOut(200, function() {
						render(rendered);
					})


				}

				// if there is no hash
				else {
					currentPath = data.path;
					breadcrumbsUrls.push(data.path);
					render(searchByPath(data.path));
				}
			}
		}

		// Splits a file path and turns it into clickable breadcrumbs

		function generateBreadcrumbs(nextDir) {
			var path = nextDir.split('/').slice(0);
			for (var i = 1; i < path.length; i++) {
				path[i] = path[i - 1] + '/' + path[i];
			}
			return path;
		}


		// Locates a file by path

		function searchByPath(dir) {
			var path = dir.split('/'),
				demo = response,
				flag = 0;

			for (var i = 0; i < path.length; i++) {
				for (var j = 0; j < demo.length; j++) {
					if (demo[j].name === path[i]) {
						flag = 1;
						demo = demo[j].items;
						break;
					}
				}
			}

			demo = flag ? demo : [];
			return demo;
		}


		// Recursively search through the file tree

		function searchData(data, searchTerms) {

			data.forEach(function(d) {
				if (d.type === 'folder') {

					searchData(d.items, searchTerms);

					if (d.name.toLowerCase().match(searchTerms)) {
						folders.push(d);
					}
				} else if (d.type === 'file') {
					if (d.name.toLowerCase().match(searchTerms)) {
						files.push(d);
					}
				}
			});
			return {
				folders: folders,
				files: files
			};
		}


		// Render the HTML for the file manager

		function render(data) {

			var scannedFolders = [],
				scannedFiles = [];

			if (Array.isArray(data)) {

				data.forEach(function(d) {

					if (d.type === 'folder') {
						scannedFolders.push(d);
					} else if (d.type === 'file') {
						scannedFiles.push(d);
					}

				});

			} else if (typeof data === 'object') {

				scannedFolders = data.folders;
				scannedFiles = data.files;

			}


			// Empty the old result and make the new one

			fileList.empty().hide();

			if (!scannedFolders.length && !scannedFiles.length) {
				filemanager.find('.nothingfound').show();
			} else {
				filemanager.find('.nothingfound').hide();
			}

			if (scannedFolders.length) {

				scannedFolders.forEach(function(f) {

					var itemsLength = f.items.length,
						name = escapeHTML(f.name),

						icon = '<span class="icon folder"></span>';

					if (itemsLength) {
						icon = '<span class="icon folder full"></span>';
					}


					if (itemsLength == 1) {
						itemsLength += ' 항목';
					} else if (itemsLength > 1) {
						itemsLength += ' 항목';
					} else {
						itemsLength = '비어 있음';
					}

					var folder = $('<li class="folders"><a href="' + f.path + '" title="' + f.path + '" class="folders">' + icon + '<span class="name">' + name + '</span> <span class="details">' + itemsLength + '</span></a></li>');
					fileList.append(folder)

				});

			}

			if (scannedFiles.length) {

				var count = 0;

				scannedFiles.forEach(function(f) {

					var fileSize = bytesToSize(f.size),
						name = escapeHTML(f.name),
						fileType = name.split('.'),
						icon = '<span class="icon file"></span>';

					fileType = fileType[fileType.length - 1];

					if (fileType == "db") {
						return;
					}

					if (fileType == "jpg") {
						icon = '<div style="display:inline-block;margin:20px 30px 0px 25px;border-radius:8px;width:60px;height:60px;background-position: center center;background-size: cover; background-repeat:no-repeat;background-image: url(\'' + f.path + '\');"></div>';
					} else if (fileType == "jpeg") {
						icon = '<div style="display:inline-block;margin:20px 30px 0px 25px;border-radius:8px;width:60px;height:60px;background-position: center center;background-size: cover; background-repeat:no-repeat;background-image: url(\'' + f.path + '\');"></div>';
					} else if (fileType == "png") {
						icon = '<div style="display:inline-block;margin:20px 30px 0px 25px;border-radius:8px;width:60px;height:60px;background-position: center center;background-size: cover; background-repeat:no-repeat;background-image: url(\'' + f.path + '\');"></div>';
					} else if (fileType == "gif") {
						icon = '<div style="display:inline-block;margin:20px 30px 0px 25px;border-radius:8px;width:60px;height:60px;background-position: center center;background-size: cover; background-repeat:no-repeat;background-image: url(\'' + f.path + '\');"></div>';
					} else if (fileType == "mp4") {
						count += 1;

						const floc = '/' + f.path.substring(0, f.path.lastIndexOf('/')) + '/';
						const fname = f.path.substring(f.path.lastIndexOf('/') + 1, f.path.length - 4);
						const thumb = '.' + floc + '.THUMB/' + fname + '.jpg';
						icon = '<span class="icon file f-' + fileType + '" id="video-' + count + '">.' + fileType + '</span>';

						// 백그라운드에서 썸네일 확인
						// 다만 (지금은) 클넢, 평범쇼에서만 한정적으로 작동하도록 설정
						if (floc.indexOf('/CloseEnough/') != -1 || floc.indexOf('/RegularShow/') != -1) {
							checkThumbnail(thumb, 'video-' + count);
						}
					} else {
						icon = '<span class="icon file f-' + fileType + '">.' + fileType + '</span>';
					}


					if (fileType == "jpg") {
						var file = $('<li class="files"><a data-fancybox="images" href="' + f.path + '" title="' + f.path + '" target="_blank" class="files">' + icon + '<span class="name">' + name + '</span> <span class="details">' + fileSize + '</span></a></li>');
					} else if (fileType == "jpeg") {
						var file = $('<li class="files"><a data-fancybox="images" href="' + f.path + '" title="' + f.path + '" target="_blank" class="files">' + icon + '<span class="name">' + name + '</span> <span class="details">' + fileSize + '</span></a></li>');
						file.appendTo(fileList);
					} else if (fileType == "png") {
						var file = $('<li class="files"><a data-fancybox="images" href="' + f.path + '" title="' + f.path + '" class="files">' + icon + '<span class="name">' + name + '</span> <span class="details">' + fileSize + '</span></a></li>');
						file.appendTo(fileList);
					} else if (fileType == "gif") {
						var file = $('<li class="files"><a data-fancybox="images" href="' + f.path + '" title="' + f.path + '" class="files">' + icon + '<span class="name">' + name + '</span> <span class="details">' + fileSize + '</span></a></li>');
						file.appendTo(fileList);
					} else if (fileType == "pdf") {
						var file = $('<li class="files"><a data-fancybox data-type="iframe" data-src="' + f.path + '" title="' + f.path + '" href="javascript:;" class="files">' + icon + '<span class="name">' + name + '</span> <span class="details">' + fileSize + '</span></a></li>');
					} else if (fileType == "mp4") {
						var newdir = "./player?video=" + encodeURIComponent(f.path.substring(f.path.indexOf('/') + 1));
						name = name.substring(name.indexOf('-') + 1, name.indexOf('.mp4')).trim();
						var file = $('<li class="files"><a href="' + newdir + '" target="_blank" title="' + f.path + '" class="files">' + icon + '<span class="name">' + name + '</span> <span class="details">' + fileSize + '</span></a></li>');
					} else if (fileType == "srt" || fileType == "vtt" || fileType == "smi") {
						var newdir = "./subview?c=" + encodeURIComponent(f.path.substring(f.path.indexOf('/') + 1));
						var file = $('<li class="files"><a href="' + newdir + '" target="_blank" title="' + f.path + '" class="files">' + icon + '<span class="name">' + name + '</span> <span class="details">' + fileSize + '</span></a></li>');
					} else if (fileType == "m4a") {
						var file = $('<li class="files"><a data-fancybox="audio" data-type="iframe" data-src="' + f.path + '" title="' + f.path + '" class="files" style>' + icon + '<span class="name">' + name + '</span> <span class="details">' + fileSize + '</span></a></li>');
					} else if (fileType == "htm") {
						var file = $('<li class="files"><a data-fancybox="page" data-type="iframe" data-src="' + f.path + '" data-caption="' + name + '" title="' + f.path + '" class="files" style>' + icon + '<span class="name">' + name + '</span> <span class="details">' + fileSize + '</span></a></li>');
					} else {
						var file = $('<li class="files"><a href="' + f.path + '" title="' + f.path + '" target="_blank" class="files">' + icon + '<span class="name">' + name + '</span> <span class="details">' + fileSize + '</span></a></li>');
					}

					file.appendTo(fileList);
				});

			}


			// Generate the breadcrumbs

			var url = '';

			if (filemanager.hasClass('searching')) {

				url = '<span>: </span>';
				// 일단 줌 애니메이션은 임시적으로 비활성화
				// fileList.removeClass('animated');

			} else {

				// fileList.addClass('animated');


				breadcrumbsUrls.forEach(function(u, i) {

					var name = u.split('/');

					if (i !== breadcrumbsUrls.length - 1) {
						url += '<a href="' + u + '"><span class="folderName">' + name[name.length - 1] + '</span></a> <span class="arrow">→</span> ';
						document.getElementById("backButton").href = "#" + u;

					} else {
						url += '<span class="folderName">' + name[name.length - 1] + '</span>';
					}

				});

				/////맞춤 배경 기능/////

				if (url.indexOf('RegularShow') != -1) {
					document.body.style.backgroundImage = "url('images/RegularShow.jpg')";
					document.body.style.backgroundAttachment = "fixed";
				} else if (url.indexOf('CloseEnough') != -1) {
					document.body.style.backgroundImage = "url('images/CloseEnough.jpg')";
					document.body.style.backgroundAttachment = "fixed";
				} else if (url.indexOf('MLaaTR') != -1) {
					document.body.style.backgroundImage = "url('images/MLaaTR.png')";
					document.body.style.backgroundAttachment = "fixed";
				} else if (url.indexOf('(1227 tv팟)') != -1) {
					document.body.style.backgroundImage = "url('images/oldvid.jpg')";
					document.body.style.backgroundAttachment = "fixed";
				} else {
					document.body.style.backgroundImage = "url('images/background.png')";
					document.body.style.backgroundAttachment = "scroll";
				}

			}

			if (breadcrumbsUrls.length == 1) {
				breadcrumbs.text('').append('12:<font color="5aa1ef">27</font> 백업 오픈클라우드');
				document.title = '1227 백업 오픈클라우드';
			} else {
				breadcrumbs.text('').append(url);
				document.title = breadcrumbsUrls[breadcrumbsUrls.length - 1].replace('Home/', '') + ' - 1227 백업 오픈클라우드';
			}

			// 로딩 스크린 지우기
			$('#loading').fadeOut('fast', function() {

				// 처음 실행이면 (animated) 있으면
				if (fileList.hasClass('animated')) {
					// 첫 등장이후 확대애니메이션 끄기
					fileList.fadeIn(700, function() {
						fileList.removeClass('animated');
					});
				} else {
					// Show the generated elements
					fileList.fadeIn();
				}
			});



		}

		function checkThumbnail(img, id) {
			img = encodeURIComponent(img).replace(/\%2F/gi, '/');
			$.ajax({
				url: img,
				type: 'HEAD',
				error: function() {
					// 아무 것도 안함
				},
				success: function() {
					$('#' + id).text('');
					$('#' + id).removeClass();
					$('#' + id).css({
						'display': 'inline-block',
						'margin': '20px 30px 0px 20px',
						'border-radius': '8px',
						'width': '65px',
						'height': '60px',
						'background-position': 'center center',
						'background-size': 'cover',
						'background-repeat': 'no-repeat',
						'background-image': 'url("' + img + '")'
					});
				}
			});
		}

		// This function escapes special html characters in names
		function escapeHTML(text) {
			return text.replace(/\&/g, '&amp;').replace(/\</g, '&lt;').replace(/\>/g, '&gt;');
		}


		// Convert file sizes from bytes to human readable units

		function bytesToSize(bytes) {
			var sizes = ['B', 'KB', 'MB', 'GB', 'TB'];
			if (bytes == 0) return '0 Bytes';
			var i = parseInt(Math.floor(Math.log(bytes) / Math.log(1024)));
			return Math.round(bytes / Math.pow(1024, i), 2) + ' ' + sizes[i];
		}
	});
});
