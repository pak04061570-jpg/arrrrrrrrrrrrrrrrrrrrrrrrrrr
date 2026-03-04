<?php
include 'db_connect.php';

$json_data = file_get_contents('php://input');
$items = json_decode($json_data, true);

if (empty($items)) {
    echo json_encode(['status' => 'error', 'msg' => 'ไม่พบรายการที่เลือก']);
    exit;
}

// [✨] ดึงชื่อผู้ทำรายการจาก Session อัตโนมัติ
$operator = isset($_SESSION['name']) ? $_SESSION['name'] : 'Unknown';

$success_count = 0;
$errors = [];

foreach ($items as $item) {
    $barcode = $conn->real_escape_string($item['barcode']);
    $sn = $conn->real_escape_string($item['sn']);

    $check = $conn->query("SELECT id FROM product_serials WHERE serial_number = '$sn'");
    if($check->num_rows > 0) {
        $errors[] = "$sn: มีในระบบแล้ว";
        continue;
    }

    $sql = "INSERT INTO product_serials (product_barcode, serial_number, status, date_added) 
            VALUES ('$barcode', '$sn', 'available', NOW())";
    
    if($conn->query($sql)) {
        $conn->query("UPDATE products SET quantity = quantity + 1 WHERE barcode = '$barcode'");

        // บันทึกประวัติพร้อมชื่อคนที่ล็อกอิน
        $stmt = $conn->prepare("INSERT INTO product_history (serial_number, action_type, operator, note) VALUES (?, 'import', ?, '')");
        $stmt->bind_param("ss", $sn, $operator);
        $stmt->execute();
        
        $success_count++;
    } else {
        $errors[] = "$sn: บันทึกไม่สำเร็จ (" . $conn->error . ")";
    }
}

if (count($errors) == 0) {
    echo json_encode(['status' => 'success', 'msg' => "รับเข้าสำเร็จครบ $success_count รายการ"]);
} else {
    echo json_encode(['status' => 'partial_error', 'msg' => "สำเร็จ $success_count รายการ, มีปัญหา " . count($errors), 'errors' => $errors]);
}
?>