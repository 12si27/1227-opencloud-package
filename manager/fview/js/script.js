var startloc = document.getElementById("startloc").innerText;
var loaddir = document.getElementById("loaddir").innerText;

window.onpopstate = function(e) { 
    console.log(e.state.d);
    getDir(e.state.d, true);
}

document.getElementById("dirBt").onclick = function() {
    getDir(document.getElementById("currdirInput").value, false);
}

function getDir(directory, backing) {

    var r = new XMLHttpRequest;
    r.open('POST', './scan.php');
    r.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
    r.send('d=' + directory);

    if (backing == false) {
        history.pushState({'d': directory }, '', './?d=' + encodeURIComponent(directory));
    }

    r.onreadystatechange = function() {
        if (r.readyState === XMLHttpRequest.DONE && r.status === 200) {
            document.getElementById("dir").innerHTML = '';
            document.getElementById("currdir").innerText = directory;
            document.getElementById("currdirInput").value = directory;

            var valid = true;

            try {
                var test = JSON.parse(r.responseText);
            } catch {
                valid = false;
            }

            if (valid) {
                const data = JSON.parse(r.responseText);
            
                for (idx in data) {
                    const fPanel = document.createElement("div");
                    fPanel.className = "col";

                    var path = data[idx].path.replace("'", '%27');
                    var name = data[idx].name;
                    var action = '';
                    var detail = '';
                    var thumb = '';

                    if (path[0] == '/') {
                        path = path.substring(1);
                    }

                    if (data[idx].type == 'folder' || data[idx].type == 'parent') {
                        if (name == '..') {
                            if (directory == '') {
                                continue;
                            }
                            name = '상위 폴더..';
                            thumb = './assets/up.svg';
                            action = `getDir('${path}', false);`;
                            detail = '';
                        } else {
                            thumb = './assets/folder.svg';
                            action = `getDir('${path}', false);`;
                            detail = '폴더';
                        }
                    } else if (data[idx].type == 'video') {
                        thumb = './assets/video.svg';
                        action = `location.href='./file_vid_finder.php?f=${encodeURIComponent(path)}';`;
                        detail = '비디오, ' + Math.round(data[idx].size/1024/1024*10)/10 + 'MB';
                    } else if (data[idx].type == 'image') {
                        thumb = './assets/image2.svg';
                        action = `location.href='${startloc+encodeURI(path)}';`;
                        detail = '이미지, ' + Math.round(data[idx].size/1024/1024*10)/10 + 'MB';
                    } else {
                        thumb = './assets/file.svg';
                        action = `location.href='${startloc+encodeURI(path)}';`;
                        detail = '파일, ' + Math.round(data[idx].size/1024/1024*10)/10 + 'MB';
                    }

                    const fSource = `
                    <div class="col">
                        <div id="filebt" class="item m-n1" onclick="${action}"
                                style="min-width: 100px; max-height: 113px;">
                            <div class="d-flex">
                                <div class="ms-2 p-3">
                                    <img src="${thumb}" width="50px" height="50px" class="rounded-start">
                                </div>
                                <div class="d-flex align-items-center mx-3">
                                    <div class="d-flex flex-column">
                                        <div class="card-title mb-0 ell" style="max-height: 50px;">${name}</div>
                                        <div class="card-text" style="font-size: small;">${detail}</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    `;

                    fPanel.innerHTML = fSource;
                    document.querySelector("#dir").append(fPanel);  
                }
            } else {
                const errmsg = `<svg xmlns="http://www.w3.org/2000/svg" style="display: none;">
                                    <symbol id="exclamation-triangle-fill" fill="currentColor" viewBox="0 0 16 16">
                                        <path d="M8.982 1.566a1.13 1.13 0 0 0-1.96 0L.165 13.233c-.457.778.091 1.767.98 1.767h13.713c.889 0 1.438-.99.98-1.767L8.982 1.566zM8 5c.535 0 .954.462.9.995l-.35 3.507a.552.552 0 0 1-1.1 0L7.1 5.995A.905.905 0 0 1 8 5zm.002 6a1 1 0 1 1 0 2 1 1 0 0 1 0-2z"/>
                                    </symbol>
                                </svg>

                                <div class="alert text-light d-flex align-items-center p-3 my-3 rounded-3" role="alert" style="background-color: #f14254;">
                                    <svg class="bi flex-shrink-0 me-2" width="24" height="24" role="img" aria-label="Danger:"><use xlink:href="#exclamation-triangle-fill"/></svg>
                                    <div>디렉토리 정보를 읽어오는 데 실패했습니다. 올바른 경로가 맞나요?</div>
                                </div>`

                const fPanel = document.createElement("div");
                fPanel.innerHTML = errmsg;
                document.querySelector("#dir").append(fPanel); 
            }
        }
    }
}

getDir(loaddir, false);