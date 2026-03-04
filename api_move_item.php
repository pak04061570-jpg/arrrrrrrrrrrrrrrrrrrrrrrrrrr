<?php
include 'db_connect.php';

$action = isset($_POST['action']) ? $_POST['action'] : '';

if ($action == 'check_status') {
    $sn = $conn->real_escape_string($_POST['sn']);

    $sql = "SELECT s.*, p.name as product_name 
            FROM product_serials s 
            JOIN products p ON s.product_barcode = p.barcode 
            WHERE s.serial_number = '$sn'";
    
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        if ($row['status'] == 'available') {
            echo json_encode(['status' => 'available', 'product_name' => $row['product_name']]);
        } else {
            echo json_encode(['status' => 'unavailable', 'msg' => "ไม่ว่าง ({$row['status']})"]);
        }
    } else {
        echo json_encode(['status' => 'not_found', 'msg' => 'ไม่พบ S/N นี้']);
    }
}
?>