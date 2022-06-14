<?php
# 세션 관련 스크립트

# 세션체크
function sess_check($allowed_perms) {

	global $_SESSION;
	$sess_pass = false;

	if (isset($_SESSION['type']) and in_array($_SESSION['type'], $allowed_perms)) {
		$sess_pass = true;
	}
	
	if (!$sess_pass): ?>
	<script>alert('해당 작업을 수행할 권한이 없습니다.'); history.back();</script>
	<?php exit; endif;
}