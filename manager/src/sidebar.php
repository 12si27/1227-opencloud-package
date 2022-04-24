<?php
if ($curr_page == null) {
    echo ("잘못된 요청입니다.");
    exit;
}
?>
<nav id="sidebar" class="sidebar js-sidebar">
    <div class="sidebar-content js-simplebar">
        <a class="sidebar-brand" href="<?=($curr_page=='index'?'':'.')?>./">
            12:<span style="color: #5aa1ef;">27</span> BackupCloud Manager
        </a>

        <ul class="sidebar-nav">
            <li class="sidebar-header">
                인사이트
            </li>

            <li class="sidebar-item <?=($curr_page=='index'?'active':'')?>">
                <a class="sidebar-link" href="<?=($curr_page=='index'?'':'.')?>./">
                    <i class="align-middle" data-feather="bar-chart-2"></i> <span class="align-middle">요약</span>
                </a>
            </li>
            <!-- <li class="sidebar-item">
                <a class="sidebar-link" href="index.html">
                    <i class="align-middle" data-feather="trending-up"></i> <span class="align-middle">최근 조회 영상</span>
                </a>
            </li> -->

            <li class="sidebar-header">
                비디오 관리
            </li>

            <li class="sidebar-item <?=($curr_page=='videos'?'active':'')?>">
                <a class="sidebar-link" href="<?=($curr_page=='index'?'':'.')?>./videos">
                    <i class="align-middle" data-feather="video"></i> <span class="align-middle">비디오 목록</span>
                </a>
            </li>
            <li class="sidebar-item <?=($curr_page=='videdit'?'active':'')?>">
                <a class="sidebar-link" href="<?=($curr_page=='index'?'':'.')?>./videdit">
                    <i class="align-middle" data-feather="edit"></i> <span class="align-middle">비디오 수정</span>
                </a>
            </li>

            <li class="sidebar-header">
                파일 관리
            </li>

            <li class="sidebar-item <?=($curr_page=='fview'?'active':'')?>">
                <a class="sidebar-link" href="<?=($curr_page=='index'?'':'.')?>./fview">
                    <i class="align-middle" data-feather="server"></i> <span class="align-middle">파일 탐색기</span>
                </a>
            </li>

    </div>
</nav>