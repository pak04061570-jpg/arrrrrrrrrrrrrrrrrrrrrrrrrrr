<?php
include 'db_connect.php';

$input = file_get_contents('php://input');
$data = json_decode($input, true);

if (!$data) {
    echo json_encode(['status' => 'error', 'msg' => 'ไม่พบข้อมูลส่งมา']);
    exit;
}

$project_id = $data['project_id'];
$items = $data['items'];
$withdrawer = $conn->real_escape_string($data['withdrawer']); // [✨] รับชื่อผู้เบิกจากหน้าเว็บ

// ดึงชื่อคนที่กำลังนั่งคีย์ข้อมูล (Admin/Staff)
$operator = isset($_SESSION['name']) ? $_SESSION['name'] : 'Unknown';
// สร้าง Note ให้รู้ว่าเบิกให้ใคร
$note = "ผู้เบิก: " . $withdrawer;

$proj_q = $conn->query("SELECT project_name FROM projects WHERE id = '$project_id'");
$project_name = ($proj_q && $proj_q->num_rows > 0) ? $proj_q->fetch_assoc()['project_name'] : 'Unknown Project';

$success_count = 0;
$errors = [];

foreach ($items as $item) {
    $sn = $conn->real_escape_string($item['sn']);

    $check = $conn->query("SELECT * FROM product_serials WHERE serial_number = '$sn'");
    if ($check->num_rows == 0) { $errors[] = "$sn: ไม่พบ S/N"; continue; }
    
    $row = $check->fetch_assoc();
    if ($row['status'] != 'available') { $errors[] = "$sn: ไม่สามารถเบิกได้"; continue; }

    $sql = "UPDATE product_serials SET project_id = '$project_id', status = 'sold', date_added = NOW() WHERE serial_number = '$sn'";
    
    if ($conn->query($sql)) {
        $p_barcode = $row['product_barcode'];
        $conn->query("UPDATE products SET quantity = quantity - 1 WHERE barcode = '$p_barcode'");

        // [✨] บันทึกประวัติ: operator = คนคีย์, note = คนเบิก
        $stmt = $conn->prepare("INSERT INTO product_history (serial_number, project_id, project_name, action_type, note, operator) VALUES (?, ?, ?, 'export', ?, ?)");
        $stmt->bind_param("sisss", $sn, $project_id, $project_name, $note, $operator);
        $stmt->execute();
        
        $success_count++;
    } else {
        $errors[] = "$sn: Database Error";
    }
}

if (count($errors) == 0) {
    echo json_encode(['status' => 'success', 'msg' => "เบิกให้ $withdrawer สำเร็จครบ $success_count รายการ"]);
} else {
    echo json_encode(['status' => 'partial_error', 'msg' => "สำเร็จ $success_count รายการ, มีข้อผิดพลาด " . count($errors), 'errors' => $errors]);
}
?>