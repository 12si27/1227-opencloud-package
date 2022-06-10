function copyClipboard(val) {
  const t = document.createElement("textarea");
  document.body.appendChild(t);
  t.value = val;
  t.select();
  document.execCommand('copy');
  document.body.removeChild(t);
  alert('클립보드에 복사되었습니다');
}

function addVidTime() {
  const subSetForm = document.getElementById('subSetForm');
  const vp = document.getElementById('stream_video_html5_api');
  var vidtime = document.createElement('input');
  vidtime.hidden = true;
  vidtime.name = 't';
  vidtime.value = vp.currentTime;
  subSetForm.appendChild(vidtime);
}

var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
  return new bootstrap.Tooltip(tooltipTriggerEl)
})

// no_vttjs 체크시 -> no_sub_bg 비활성화
if (document.getElementById('no_vttjs') != null) {
  document.getElementById('no_vttjs').addEventListener("change", function () {
    if (this.checked) {
      document.getElementById('no_sub_bg').disabled = true;
    } else {
      document.getElementById('no_sub_bg').disabled = false;
    }
  });
}

var playerFilled = false;
const pContainer = document.getElementById('play_container');
const navBar = document.getElementById('nav');
const bt = document.getElementById('playSizeBt');
const bodyStyle = document.getElementById('bodyStyle');

var setCookie = function(name, value, exp) {
    var date = new Date();
    date.setTime(date.getTime() + exp*24*60*60*1000);
    document.cookie = name + '=' + value + ';expires=' + date.toUTCString() + ';path=/';
};

var getCookie = function(name) {
    var value = document.cookie.match('(^|;) ?' + name + '=([^;]*)(;|$)');
    return value? value[2] : null;
};

// 플레이어 꽉 채우기
function fillPlayer() {
    nav.hidden = true;
    pContainer.classList = 'container-fluid ratio ratio-16x9 px-0';
    pContainer.style = 'max-height: 100vh;';
    bodyStyle.innerHTML = "::-webkit-scrollbar { display: none; }";
    
    bt.innerHTML = '<i class="fa-solid fa-compress"></i> <span class="bt-text">원래대로</span>';
    bt.title = "플레이어를 원래 크기로 셋팅합니다.";
    playerFilled = true;
}

// 플레이어 정상화
function normPlayer() {
    nav.hidden = false;
    pContainer.classList = 'container ratio ratio-16x9 px-0';
    pContainer.style = 'max-height: calc(100vh - 128px);';
    bodyStyle.innerHTML = '';
    
    bt.innerHTML = '<i class="fa-solid fa-expand"></i> <span class="bt-text">꽉차게</span>';
    bt.title = "창에 플레이어가 꽉 차도록 셋팅합니다.";
    playerFilled = false;
}

function switchPlayerSize() {
    if (playerFilled) {
        normPlayer();
        setCookie('fill_player', false, 365);
    } else {
        fillPlayer();
        setCookie('fill_player', true, 365);
    }
}

// 플레이어 존재 시에만 (잠금이 풀렸을 시에만)
if (document.getElementById('play_container') != null) {
  if (getCookie('fill_player') == 'true') {
    fillPlayer();
  }
}