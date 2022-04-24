function copyClipboard(val) {
    const t = document.createElement("textarea");
    document.body.appendChild(t);
    t.value = val;
    t.select();
    document.execCommand('copy');
    document.body.removeChild(t);
    alert('클립보드에 복사되었습니다');
  }

var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
  return new bootstrap.Tooltip(tooltipTriggerEl)
})


// 1227 자막 권장값 설정
let player = videojs('my_video');
player.ready(function(){
    var settings = this.textTrackSettings;
    settings.setValues({
        "fontFamily": "shSans",
        "backgroundColor": "#000",
        "backgroundOpacity": "0.5",
        "edgeStyle": "uniform",
        "fontPercent": "1.25"
    });
    settings.updateDisplay();
});

player.landscapeFullscreen();
player.mobileUi();


var vp = document.getElementById('my_video_html5_api');
vp.addEventListener('loadedmetadata', function() {
  const seconds = vp.duration;
  const bitrate = document.getElementById('vid_size').textContent / 1024 / seconds * 8;

  //3항 연산자를 이용하여 10보다 작을 경우 0을 붙이도록 처리 하였다.
  var hour = parseInt(seconds/3600) < 10 ? '0'+ parseInt(seconds/3600) : parseInt(seconds/3600);
  var min = parseInt((seconds%3600)/60) < 10 ? '0'+ parseInt((seconds%3600)/60) : parseInt((seconds%3600)/60);
  var sec = seconds % 60 < 10 ? '0'+seconds % 60 : seconds % 60;

  if (hour > 0) {
    document.getElementById('vid_length').textContent = '| 재생 길이: ' + hour + ':' + min + ":" + Math.round(sec*100)/100;
  } else {
    document.getElementById('vid_length').textContent = '| 재생 길이: ' + min + ":" + Math.round(sec*100)/100;
  }
  
  document.getElementById('bitrate_info').textContent = ', 평균 ' + Math.round(bitrate) + 'kbps';
});
