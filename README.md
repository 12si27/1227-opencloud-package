# 1227-opencloud-package
1227 오픈백업클라우드 패키지 코드 (오픈 브라우저 + 플레이어 + 관리 매니저)

## 요구 조건
- MySQL, MariaDB, php 사용이 가능한 서버 환경
- 업로드/이름 변경/삭제 등이 가능한 디렉토리 (www-data)

## 설치 방법
1. database.sql 을 실행하여 DB를 생성하고 전용 계정을 생성
2. 저장 파일 전용 디렉토리 (예: 외장하드 심볼릭 링크 등) 생성 후 위치 지정*
3. `./src/dbconn.php`와 `./src/settings.php` 를 열어 아까 생성한 DB 계정과 디렉토리 입력 후 저장
4. DB에 관리용 계정 생성 후 ./login 페이지로 가 로그인하여 작동 확인하기**

<sub>\* 기본값은 '../Home/' 입니다.</sub>  
<sub>** 패스워드는 솔트값 + SHA256 해싱을 거친 후 DB에 저장되므로 (./manager/login/login_check.php 참고) 해당 조건에 맞춰 DB에 넣어주셔야 정상적으로 로그인됩니다.</sub>  

## 스크린샷
### 오픈 브라우저 (파일 탐색기)
![image](https://user-images.githubusercontent.com/88251502/164973451-90b28ea2-241b-443e-b9dd-7fdd33a5beb1.png)

### 비디오 플레이어
![image](https://user-images.githubusercontent.com/88251502/164973400-e0233d90-1ca7-46c5-beea-c0b58f38b2d8.png)

### 매니저 (관리 페이지)
![image](https://user-images.githubusercontent.com/88251502/164973467-7ccb7731-fba7-47f3-b1a9-812488e7cbba.png)
![image](https://user-images.githubusercontent.com/88251502/164973585-9c01de11-e97d-4d2d-a75b-e5c24484defb.png)
![image](https://user-images.githubusercontent.com/88251502/164973553-ea6f962b-bd54-4202-bc2b-ee05e869ae06.png)
![image](https://user-images.githubusercontent.com/88251502/164973567-99a47702-3be9-4233-a006-1d691f966e87.png)

## 개발 현황
### 개발 완료된 기능
- 내장 자막 표시
- 내장 자막 실시간 처리 (문장부호 숨김)
- srt -> vtt 변환
- 조회수 구현
- 일간, 시간당 조회수 구현
- 짧은 주소 구현
- 썸네일 구현
- 관리 페이지 구현
- 자막, 썸네일 업로드 & 관리

### 아직 개발되지 않은 기능 (예정중)
- 회원 가입 & 관리
- 비디오 파일 업로드
- 더욱 자세한 조회수 통계
- 디렉토리 생성, 수정
- 일괄 이름 변경
- 자막 내용 편집
- 비디오 파일 <-> DB 무결성 검사 및 자동 수정

### 개발하지 않을 예정인 기능
- php를 통한 미디어 스트림 (성능 문제)
- 실시간 썸네일 생성 (성능 문제)
- 댓글 남기기

## 사용된 프로젝트들
- 1227-opencloud-browser
  - https://github.com/12si27/1227-opencloud-browser
- Opencloud-Video-Player (+video.js)
  - https://github.com/pdjdev/Opencloud-Video-Player
- AdminKit
  - https://github.com/adminkit/adminkit
