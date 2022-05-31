function loadSub(directory) {

    document.querySelector("#subline").innerHTML = '';

    var r = new XMLHttpRequest;
    r.open('POST', './subscan.php');
    r.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
    r.send('c=' + directory);

    r.onreadystatechange = function() {

        if (r.readyState === XMLHttpRequest.DONE && r.status === 200) {

            var valid = true;

            try {
                var test = JSON.parse(r.responseText);
            } catch {
                valid = false;
            }

            if (valid) {
                const data = JSON.parse(r.responseText);

                if (data.error == 0) {

                    for (idx in data.timestamp) {

                        const lPanel = document.createElement("div");
                        lPanel.className = "row my-3";

                        // console.log(data.timestamp[idx]);
                        // console.log(data.lines[idx]);

                        const ts = data.timestamp[idx];
                        const line = data.lines[idx];

                        const lSource = `<div class="d-flex my-2">
                                <label class="text-light fw-bolder">#${parseInt(idx)+1}</label>
                                <div class="d-flex flex-grow-1 justify-content-end">                                
                                    <label id="ts-${idx}" class="text-secondary">${ts}</label>
                                    <button onclick="copyLine('${idx}');" class="btn btn-sm subcopy ms-2"><i class="fa-solid fa-clipboard"></i> 복사</button>
                                </div>
                            </div>
                            <textarea id="line-${idx}" class="sub-box" rows="1" placeholder="(공백)" disabled>${line}</textarea>`;

                        lPanel.innerHTML = lSource;

                        document.querySelector("#subline").append(lPanel); 
                    }

                    document.querySelector("#enc").innerText = data.encoding; 
                    document.querySelector("#ext").innerText = data.ext; 

                    const tx = document.getElementsByTagName("textarea");

                    for (let i = 0; i < tx.length; i++) {
                        tx[i].setAttribute("style", "height:" + (tx[i].scrollHeight) + "px;overflow-y:hidden;");
                        tx[i].addEventListener("input", OnInput, false);
                    }

                } else if (data.error == 1) {

                    document.querySelector("#subline").innerHTML = `<div class="row my-3 p-3 rounded-3 text-light" style="background-color:#aa3130;">
                    <div>올바르지 않은 요청입니다.</div>
                    <div style="font-size: small; opacity: 0.5;">주소가 올바르지 않거나 현재 서버에 자막이 없을 수 있습니다.</div>
                </div>`;

                } else if (data.error == 2) {

                    document.querySelector("#subline").innerHTML = `<div class="row my-3 p-3 rounded-3 text-light" style="background-color:#aa3130;">
                    <div>미리보기를 지원하지 않는 자막입니다. 직접 다운로드해 열람해 주세요.</div>
                    <div style="font-size: small; opacity: 0.5;">지원되지 않는 인코딩으로 저장되어 있거나 비어 있을 수 있습니다.</div>
                </div>`;

                } else {

                    document.querySelector("#subline").innerHTML = `<div class="row my-3 p-3 rounded-3 text-light" style="background-color:#aa3130;">
                    <div>알 수 없는 오류입니다.</div>
                    <div style="font-size: small; opacity: 0.5;">잠시 후 다시 시도해 주세요.</div>
                </div>`;

                }

            } else {

                document.querySelector("#subline").innerHTML = `<div class="row my-3 p-3 rounded-3 text-light" style="background-color:#aa3130;">
                    <div>서버와 통신에 실패하였습니다.</div>
                    <div style="font-size: small; opacity: 0.5;">잠시 후 다시 시도해 주세요.</div>
                </div>`;

            }

            document.querySelector("#loading").style = "display: none !important;";
        }
    }
}

function copyLine(order) {

    const timestamp = document.getElementById('ts-'+order).innerText;
    const line = document.getElementById('line-'+order).value;

    var content = '==== ' + (parseInt(order) + 1) + '번째 줄 ====\n';

    content += timestamp + '\n';
    content += '====\n';

    content += line + '\n';
    content += '====\n';

    copyClipboard(content);

}

function copyClipboard(val) {
    const t = document.createElement("textarea");
    document.body.appendChild(t);
    t.value = val;
    t.select();
    document.execCommand('copy');
    document.body.removeChild(t);
    alert('클립보드에 복사되었습니다');
}

function OnInput() {
    this.style.height = "auto";
    this.style.height = (this.scrollHeight) + "px";
}