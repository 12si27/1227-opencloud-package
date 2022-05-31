var currdir = '';

        document.querySelector("#autoDBChk").addEventListener('change', (e) => {
            if (e.target.checked) {
                document.getElementById("vidID").disabled = false;
                document.getElementById("views").disabled = false;
            } else {
                document.getElementById("vidID").disabled = true;
                document.getElementById("views").disabled = true;
            }
        });

        function doc(el) {
            return document.getElementById(el);
        }

        function uploadFile() {
            document.querySelector("#upload-status").hidden = false;
            document.querySelector("#uploadBt").disabled = true;
            document.querySelector("#error").innerHTML = "";

            var file = doc("vid_file").files[0];
            var name = doc("vid_name").value.trim();
            var dir = doc("vid_dir").value.trim();

            currdir = dir;

            var autoDB = doc("autoDBChk").checked;

            var cap = doc("cap_file").files[0];
            var thumb = doc("thumb_file").files[0];


            var formdata = new FormData();
            formdata.append("vid_file", file);
            formdata.append("vid_name", name);
            formdata.append("vid_dir", dir);
            formdata.append("auto_db", autoDB);

            if (cap != null) {
                formdata.append("cap_file", cap);
            }

            if (thumb != null) {
                formdata.append("thumb_file", thumb);
            }

            var ajax = new XMLHttpRequest();
            ajax.upload.addEventListener("progress", progressHandler, false);
            ajax.addEventListener("load", completeHandler, false);
            ajax.addEventListener("error", errorHandler, false);
            ajax.addEventListener("abort", abortHandler, false);
            ajax.open("POST", "file_vid_upload.php");
            ajax.send(formdata);
        }

        function progressHandler(event) {
            var percent = Math.round((event.loaded / event.total) * 100);
            doc("progressBar").value = percent;

            if (percent == 100) {
                doc("status").innerHTML = "마무리 작업 중...";
            } else {
                doc("status").innerHTML = "업로드 중... (" + Math.round(percent) + "%)";
            }
            
        }

        function completeHandler(event) {
            // doc("status").innerHTML = event.target.responseText;
            var valid = true;

            try {
                const test = JSON.parse(event.target.responseText);
            } catch {
                valid = false;
            }

            var title = '';
            var cmt = '';


            if (valid) {

                const data = JSON.parse(event.target.responseText);

                const result = data[0].result;
                const autodb = data[0].autodb;
                const vidid = data[0].vidid;

                
                if (result == 0) { // 모든 것이 완벽하게 성공

                    if (vidid != '') {
                        location.href = '../videdit/?id=' + vidid;
                    } else {
                        location.href = '../fview?d=' + encodeURIComponent(currdir);
                    }

                } else if (result == 1) { // 일부만 성공

                    if (vidid != '') {
                        location.href = '../videdit/?fail=19&id=' + vidid;
                    } else {
                        location.href = '../fview?uploaderror=1&d=' + encodeURIComponent(currdir);
                    }

                } else {
                    valid = false;
                    document.querySelector("#uploadBt").disabled = false;
                    
                    switch (result) {
                        case -1:    // 너무 큰 비디오 사이즈
                            title = '너무 큰 비디오 사이즈';
                            cmt = '비디오 용량을 줄여 다시 시도해 주세요.';
                            break;
                        case -2:    // 비디오 업로드 파일 없음
                            title = '비디오 파일 없음';
                            cmt = '비디오 파일이 존재하는지 확인하세요.';
                            break;
                        case -3:    // 업로드 실패
                            title = '업로드 실패';
                            cmt = '잠시 후 다시 시도해 주세요.';
                            break;
                        case -4:    // 허용되지 않는 확장자
                            title = '허용되지 않는 확장자';
                            cmt = '가능하면 MP4 컨테이너를 이용해 주세요.';
                            break;
                        case -5:    // 이미 존재하는 비디오 파일
                            title = '이미 존재하는 비디오 파일';
                            cmt = '해당 비디오를 삭제하거나 다른 위치/이름으로 변경 후 시도해 주세요.';
                            break;
                        case -6:    // 잘못된 업로드 경로
                            title = '잘못된 업로드 경로';
                            cmt = '올바른 업로드 경로인지 확인 후 다시 시도해 주세요.';
                            break;
                        case -7:    // 업로드 파일 저장 실패
                            title = '업로드 저장 실패';
                            cmt = '잠시 후 다시 시도해 주세요.';
                            break;
                    }
                }
            } else {
                title = "서버 통신 실패";
                cmt = "서버로부터 응답을 받을 수 없었습니다. 잠시 후 다시 시도해 주세요.";
            }

            if (!valid) {
                const msg = `<svg xmlns="http://www.w3.org/2000/svg" style="display: none;">
                            <symbol id="exclamation-triangle-fill" fill="currentColor" viewBox="0 0 16 16">
                                <path d="M8.982 1.566a1.13 1.13 0 0 0-1.96 0L.165 13.233c-.457.778.091 1.767.98 1.767h13.713c.889 0 1.438-.99.98-1.767L8.982 1.566zM8 5c.535 0 .954.462.9.995l-.35 3.507a.552.552 0 0 1-1.1 0L7.1 5.995A.905.905 0 0 1 8 5zm.002 6a1 1 0 1 1 0 2 1 1 0 0 1 0-2z"/>
                            </symbol>
                        </svg>
                        <div class="alert text-light d-flex align-items-center p-3 my-3 rounded-3" role="alert" style="background-color: #f14254;">
                            <svg class="bi flex-shrink-0 me-2" width="24" height="24" role="img" aria-label="Danger:"><use xlink:href="#exclamation-triangle-fill"/></svg>
                            <div>
                                <b>${title}</b></br>${cmt}
                            </div>
						</div>`;

                document.querySelector("#error").innerHTML = msg;
            }
        }

        function errorHandler(event) {
            doc("status").innerHTML = "Upload Failed";
        }

        function abortHandler(event) {
            doc("status").innerHTML = "Upload Aborted";
        }