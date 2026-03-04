<?php
// เช็คก่อนว่ามี Session ทำงานอยู่หรือยัง ถ้ายังไม่มีค่อย start
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$servername = "localhost";
$username = "root";
$password = "123456"; // <--- ลองแก้ตรงนี้เป็นรหัสของคุณ (ถ้าจำไม่ได้ลอง "root" หรือลบให้ว่าง)
$dbname = "inventory_system";

// สร้างการเชื่อมต่อ
$conn = new mysqli($servername, $username, $password, $dbname);
$conn->set_charset("utf8");

// เช็คว่า Error ไหม?
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>