<?php
include 'db_connect.php';

$action = $_POST['action'];

// 1. สร้างโครงการ
if($action == 'create') {
    $name = $conn->real_escape_string($_POST['name']);
    $type = $conn->real_escape_string($_POST['type']); 
    $conn->query("INSERT INTO projects (project_name, type) VALUES ('$name', '$type')");
    echo 'success';
}

// 2. ฟังก์ชันแก้ไขชื่อโปรเจกต์
if ($action == 'edit_info') {  
    $id = $_POST['id'];
    $name = $conn->real_escape_string($_POST['name']);
    $code = $conn->real_escape_string($_POST['code']); 

    $sql = "UPDATE projects SET project_name = '$name', project_code = '$code' WHERE id = $id";
    if($conn->query($sql)){
        // อัปเดตชื่อในตารางประวัติด้วย
        $conn->query("UPDATE product_history SET project_name = '$name' WHERE project_id = $id");
        echo "success";
    } else {
        header("HTTP/1.1 500 Internal Server Error");
        echo "Error: " . $conn->error; 
    }
}

// 3. ฟังก์ชันปิดงาน
if($action == 'close_job') {
    $id = $_POST['id'];
    $conn->query("UPDATE projects SET status = 'Closed' WHERE id = $id");
    echo 'success';
}

// 4. ลบโครงการ
if($action == 'delete_project') {
    $id = $_POST['id'];
    $check = $conn->query("SELECT COUNT(*) as c FROM product_serials WHERE project_id = $id")->fetch_assoc()['c'];
    
    if($check > 0) {
        echo json_encode(['status'=>'error', 'msg'=>'ยังมีสินค้าคงค้างในโปรเจกต์ ไม่สามารถลบได้']);
    } else {
        $conn->query("DELETE FROM projects WHERE id = $id");
        echo json_encode(['status'=>'success']);
    }
}
?>