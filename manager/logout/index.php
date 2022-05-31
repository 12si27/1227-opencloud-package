<?php
session_start();
#$res=session_destroy();

unset($_SESSION['userid']);
header('Location: ../');

?>