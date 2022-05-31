<?php
/**
 * Description of VideoStream
 *
 * @author Rana
 * @link http://codesamplez.com/programming/php-html5-video-streaming-tutorial
 */

 
class VideoStream
{
    private $path = "";
    private $stream = "";
    private $buffer = 102400;
    private $start  = -1;
    private $end    = -1;
    private $size   = 0;
 
    function __construct($filePath) 
    {
        $this->path = $filePath;
    }
     
    /**
     * Open stream
     */
    private function open()
    {
        if (!($this->stream = fopen($this->path, 'rb'))) {
            die('Could not open stream for reading');
        }
         
    }
     
    /**
     * Set proper header to serve the video content
     */
    private function setHeader()
    {
        session_write_close(); // 브라우저 프리징 이슈 해결
        ob_get_clean();
        header("X-LIGHTTPD-KBytes-per-second: 1000"); // 비디오 스트리밍 대역폭 -> 1000kb/s로 해놓기 (많아봤자 2000kbps -> 250kb/s)
        header("Content-Type: video/mp4");
        header("Cache-Control: max-age=2592000, public");
        header("Expires: ".gmdate('D, d M Y H:i:s', time()+2592000) . ' GMT');
        header("Last-Modified: ".gmdate('D, d M Y H:i:s', @filemtime($this->path)) . ' GMT' );
        $this->start = 0;
        $this->size  = filesize($this->path);
        $this->end   = $this->size - 1;
        header("Accept-Ranges: 0-".$this->end);
         
        if (isset($_SERVER['HTTP_RANGE'])) {
  
            $c_start = $this->start;
            $c_end = $this->end;
 
            list(, $range) = explode('=', $_SERVER['HTTP_RANGE'], 2);
            if (strpos($range, ',') !== false) {
                header('HTTP/1.1 416 Requested Range Not Satisfiable');
                header("Content-Range: bytes $this->start-$this->end/$this->size");
                exit;
            }
            if ($range == '-') {
                $c_start = $this->size - substr($range, 1);
            }else{
                $range = explode('-', $range);
                $c_start = $range[0];
                 
                $c_end = (isset($range[1]) && is_numeric($range[1])) ? $range[1] : $c_end;
            }
            $c_end = ($c_end > $this->end) ? $this->end : $c_end;
            if ($c_start > $c_end || $c_start > $this->size - 1 || $c_end >= $this->size) {
                header('HTTP/1.1 416 Requested Range Not Satisfiable');
                header("Content-Range: bytes $this->start-$this->end/$this->size");
                exit;
            }
            $this->start = $c_start;
            $this->end = $c_end;
            $length = $this->end - $this->start + 1;
            fseek($this->stream, $this->start);
            header('HTTP/1.1 206 Partial Content');
            header("Content-Length: ".$length);
            header("Content-Range: bytes $this->start-$this->end/".$this->size);
        }
        else
        {
            header("Content-Length: ".$this->size);
        }  
         
    }
    
    /**
     * close curretly opened stream
     */
    private function end()
    {
        fclose($this->stream);
        exit;
    }
     
    /**
     * perform the streaming of calculated range
     */
    private function stream()
    {
        $i = $this->start;
        set_time_limit(0);
        while(!feof($this->stream) && $i <= $this->end) {
            $bytesToRead = $this->buffer;
            if(($i+$bytesToRead) > $this->end) {
                $bytesToRead = $this->end - $i + 1;
            }
            $data = fread($this->stream, $bytesToRead);
            print( $data ) ; // echo -> print
            flush();
            $i += $bytesToRead;
        }
    }
     
    /**
     * Start streaming video content
     */
    function start()
    {
        $this->open();
        $this->setHeader();
        $this->stream();
        $this->end();
    }
}

require('../src/settings.php');
require('../src/dbconn.php');

$vidid = $_GET['v'];
$viewid = $_GET['vid'];

$stream_ok = false;

# 없거나 빈 GET PARAMETER가 있을 경우
if ($vidid == null || $vidid == '' || $viewid == null || $viewid == '') {
    header("Content-Type: text/plain");
    echo "Invaild Request";
    exit;
}

# 외부 무단 스트림 방지용 (세션 체크)
session_start();
if(!isset($_SESSION['viewid'])) { # 뷰 ID 생성되지 않았을때
    http_response_code(403);
    header("Content-Type: text/plain");
    echo "Invaild Session";
    exit;
} else {
    if ($_SESSION['viewid'] != $viewid) {
        http_response_code(403);
        header("Content-Type: text/plain");
        echo "Invaild View ID";
        exit;
    }
}

# 비디오 ID SQL ESCAPE
$vidid = mysqli_real_escape_string($conn, $vidid);

# DB에서 잠금 비디오인지 여부 조회
$query = mysqli_query($conn, "SELECT id, vid_key, allowed_user FROM locked WHERE id = '$vidid' AND active = 1");

# 잠금 비디오일 경우
if (mysqli_num_rows($query) > 0) {
    $query = mysqli_fetch_array($query);
    if ($_SESSION['key'] != $query['vid_key']) { # 세션 키가 불일치할시
        http_response_code(403);
        header("Content-Type: text/plain");
        echo "Invalid Key Value";
        exit;
    }
}

# 이제 비디오ID에서 경로 추출
$query = mysqli_query($conn, "SELECT file_loc FROM videos WHERE id = '$vidid'");

# 비디오 ID 결과 있을때
if (mysqli_num_rows($query) > 0) {
    $query = mysqli_fetch_array($query);
    $video = $query['file_loc'];
} else {
    http_response_code(404);
    header("Content-Type: text/plain");
    echo "Invaild Video ID";
    exit;
}

# 다운로드 방지용
if(isset($_SERVER["HTTP_REFERER"]) && $_SERVER['HTTP_SEC_FETCH_SITE'] == 'same-origin') {
    $stream_ok = true;
    
} else {
    if(!isset($_SESSION['userid'])) { # 운영자ID 없을때
        http_response_code(403);
        header("Content-Type: text/plain");
        echo "Invaild Access";
        exit;
    } else {
        $stream_ok = true;
    }
}


if ($stream_ok) {
    $stream = new VideoStream($startloc.$video);
    $stream->start();
}

