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

// no_vttjs 체크시 -> no_sub_bg 비활성화
document.getElementById('no_vttjs').addEventListener("change", function () {
  if (this.checked) {
    document.getElementById('no_sub_bg').disabled = true;
  } else {
    document.getElementById('no_sub_bg').disabled = false;
  }
});


