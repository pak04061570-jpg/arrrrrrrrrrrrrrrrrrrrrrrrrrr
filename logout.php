<?php
session_start();
session_destroy(); // ล้างความจำทั้งหมด
header("Location: login.php");
exit;
?>