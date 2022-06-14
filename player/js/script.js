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

function isMobile() {
  return (navigator.userAgent.match(/Android/i) ||
          navigator.userAgent.match(/iPhone/i) ||
          navigator.userAgent.match(/like Mac OS X/i)) != null;
}

var setCookie = function(name, value, exp) {
  var date = new Date();
  date.setTime(date.getTime() + exp*24*60*60*1000);
  document.cookie = name + '=' + value + ';expires=' + date.toUTCString() + ';path=/';
};

var getCookie = function(name) {
  var value = document.cookie.match('(^|;) ?' + name + '=([^;]*)(;|$)');
  return value? value[2] : null;
};

var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
  return new bootstrap.Tooltip(tooltipTriggerEl)
})

var vp = document.getElementById('stream_video_html5_api'); // 영상 정보 처리
var nextBtShown = false;                                    // 다음편보기 보여짐 여부
var playerFilled = false;                                   // 플레이어 확장 여부
var vidRatio = '56.25%';                                    // 영상 비율 (기본 16:9)

const pContainer = document.getElementById('play_container'); // 플레이어 컨테이너
const navBar = document.getElementById('nav');                // 네비게이션 바
const playSizeBt = document.getElementById('playSizeBt');             // 확장버튼
const bodyStyle = document.getElementById('bodyStyle');       // body style 태그

const prevBt = document.getElementById('prev-bt');      // 이전 비디오 버튼
const nextBt = document.getElementById('next-bt');      // 다음 비디오 버튼


// 네이티브 플레이어가 아닐때
if (document.getElementById('stream_video') != null) {

    // 1227 자막 권장값 설정
    let player = videojs('stream_video');
    player.ready(function(){
        this.hotkeys({volumeStep: 0.1, seekStep: 10, enableModifiersForNumbers: false, enableHoverScroll: true});
        var settings = this.textTrackSettings;
        settings.setValues({
            "fontFamily": "shSans",
            "backgroundColor": "#000",
            "backgroundOpacity": (getCookie('no_sub_bg') == 1 ? '0' : '0.5'), // 반투명 여부
            "edgeStyle": "uniform",
            "fontPercent": "1.25"
        });
        settings.updateDisplay();
    });
    player.landscapeFullscreen();
    player.mobileUi();

    // Mobile - 볼륨컨트롤 숨기기
    if (isMobile()) { document.getElementsByClassName("vjs-volume-panel")[0].hidden = true; }
    vp = document.getElementById('stream_video_html5_api'); // vjs 로드 후 VP 다시설정

    // 다음편 링크가 있을 경우
    if (document.getElementById('next-link') != null) {

        // 다음 타이틀 불러오기
        var nextTitle = document.getElementById('next-title').innerText;
        if (nextTitle.indexOf('-') != -1) {
            nextTitle = nextTitle.substr(nextTitle.indexOf('-')+1).trim();
        }

        // 크레딧 타임 불러오기
        const creditTime = document.getElementById('credit-time').value;

        videojs.registerComponent("nextVid", videojs.extend(videojs.getComponent("Component")));
        var nbBtDom = player.addChild("nextVid", {}).el();
        nbBtDom.style = (isMobile() ? 'top: 5%;':'bottom: 7em;');
        nbBtDom.id = 'nextPopButton';
        nbBtDom.hidden = true;
        nbBtDom.innerHTML = `<div class='nextPopBt' id='nextPopBt' onclick="nextPopBtClick()">
                            <div class='wrapper'>
                            <div class='vidName'>${nextTitle}</div>
                            <div class='label'>다음 비디오 보기</div></div>
                            <button onclick="nextPopBtClose()">×</button></div>`;

        vp.addEventListener('timeupdate', (event) => {
            if (nextBtShown == false) {
                // 크레딧타임 이내일시
                if ((vp.duration - vp.currentTime) <= creditTime) {
                    nextBtShown = true
                    document.getElementById('nextPopButton').hidden = false;
                }
            }
        });

        // 다음편 팝업 셋팅
        const nextPopBt = document.getElementById('nextPopBt');
        
        var btStyle = `border-radius: 5px;
        content: "";
        background-position: right;
        background-repeat: no-repeat;
        background-size: cover, 50%;
        position: relative;
        top: 0px; left: 0px; right: 0px; bottom: 0px;`;

        prevBt.style.cssText += `background: linear-gradient(to right, rgb(24, 24, 24) 60%, rgb(24, 24, 24, 0.7)), url("${prevBt.getAttribute('thumb')}");` + btStyle;
        nextBt.style.cssText += `background: linear-gradient(to right, rgb(24, 24, 24) 60%, rgb(24, 24, 24, 0.7)), url("${nextBt.getAttribute('thumb')}");` + btStyle;
        nextPopBt.style.cssText = `background: linear-gradient(to right, rgba(24, 24, 24, 0.5) 0, rgba(24, 24, 24, 1)), url("${nextBt.getAttribute('thumb')}"); background-position: right; background-repeat: no-repeat; background-size: cover;`;

    }
}

// 메타데이터 불러오기 이벤트
vp.addEventListener('loadedmetadata', function() {
    const seconds = vp.duration;
    const bitrate = document.getElementById('vid_size').textContent / 1024 / seconds * 8;
    const vw = vp.videoWidth;
    const vh = vp.videoHeight;
    var hour = parseInt(seconds/3600) < 10 ? '0'+ parseInt(seconds/3600) : parseInt(seconds/3600);
    var min = parseInt((seconds%3600)/60) < 10 ? '0'+ parseInt((seconds%3600)/60) : parseInt((seconds%3600)/60);
    var sec = seconds % 60 < 10 ? '0'+seconds % 60 : seconds % 60;
    if (hour > 0) {
        document.getElementById('vid_length').textContent = ' · 재생 길이: ' + hour + ':' + min + ":" + Math.round(sec*100)/100;
    } else {
        document.getElementById('vid_length').textContent = ' · 재생 길이: ' + min + ":" + Math.round(sec*100)/100;
    }
    document.getElementById('bitrate_info').textContent = ', 평균 ' + Math.round(bitrate) + 'kbps';
    document.getElementById('vid_res').textContent = ' · 화질: ' + vh + 'p';

    // 비율계산 후 플레이어 크기 조정
    vidRatio = (vh/vw)*100 + '%';
    document.getElementById('play_container').style.cssText += '--bs-aspect-ratio: ' + vidRatio;
    vp.focus();
});

function nextPopBtClick() {
    document.querySelector('.nextPopBt button').hidden = true;
    document.querySelector('.nextPopBt .vidName').hidden = true;
    document.querySelector('.nextPopBt .label').innerHTML = '로드 중...';
    window.stop(); location.href = document.getElementById('next-link').href; }

function nextPopBtClose() { event.stopPropagation(); document.getElementById('nextPopButton').hidden = true; }

// 플레이어 꽉 채우기
function fillPlayer() {
    nav.hidden = true;
    pContainer.classList = 'container-fluid ratio ratio-16x9 px-0';
    pContainer.style = 'max-height: 100vh; --bs-aspect-ratio: ' + vidRatio + ';';
    bodyStyle.innerHTML = "::-webkit-scrollbar { display: none; }";
    
    playSizeBt.innerHTML = '<i class="fa-solid fa-compress"></i> <span class="bt-text">원래대로</span>';
    playSizeBt.title = "플레이어를 원래 크기로 셋팅합니다.";
    playerFilled = true;
}

// 플레이어 정상화
function normPlayer() {
    nav.hidden = false;
    pContainer.classList = 'container ratio ratio-16x9 px-0';
    pContainer.style = 'max-height: calc(100vh - 128px); --bs-aspect-ratio: ' + vidRatio + ';';
    bodyStyle.innerHTML = '';
    
    playSizeBt.innerHTML = '<i class="fa-solid fa-expand"></i> <span class="bt-text">꽉차게</span>';
    playSizeBt.title = "창에 플레이어가 꽉 차도록 셋팅합니다.";
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


// 로드 후 스크립트 시작 //

// no_vttjs 체크시 -> no_sub_bg 비활성화
if (document.getElementById('no_vttjs') != null) {
  document.getElementById('no_vttjs').addEventListener("change", function () {
      document.getElementById('no_sub_bg').disabled = this.checked;
  });
}

// 플레이어 존재 시에만 (잠금이 풀렸을 시에만)
if (document.getElementById('play_container') != null) {
  if (getCookie('fill_player') == 'true') {
    fillPlayer();
  }
}

// 단축키 인식

var loaded = false; // 단축키 써도될만큼 플레이어 로드됐는지 플래그
vp.addEventListener('loadedmetadata', function() {
     setTimeout(function() {loaded = true;}, 3000); // 비디오 로드후 3초후 허용
});

document.onkeydown = function (e) {
    if (e.key == "t") { // T 키 - 확장/복구
        switchPlayerSize();
    } else if (e.key == "," && document.getElementById('prev-link') != null && loaded) { // < 키 - 이전 비디오
        window.stop(); location.href = document.getElementById('prev-link').href;
    } else if (e.key == "." && document.getElementById('next-link') != null && loaded) { // > 키 - 다음 비디오
        window.stop(); location.href = document.getElementById('next-link').href;
    }
}