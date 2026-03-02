<?php
include 'db_connect.php';

// 1. รับค่าที่ DataTables ส่งมา
$draw = $_POST['draw'];
$row = $_POST['start'];
$rowperpage = $_POST['length']; // จำนวนต่อหน้า (10, 25, 50)
$searchValue = $conn->real_escape_string($_POST['search']['value']); // คำค้นหา
$filter_type = $conn->real_escape_string($_POST['filter_type']); // ตัวกรองประเภท

// 2. เงื่อนไขการค้นหา (Search)
$searchQuery = " ";
if($searchValue != ''){
    $searchQuery = " AND (
        h.serial_number LIKE '%$searchValue%' OR 
        pr.name LIKE '%$searchValue%' OR 
        p.project_name LIKE '%$searchValue%' OR 
        h.project_name LIKE '%$searchValue%' OR 
        h.note LIKE '%$searchValue%' OR 
        h.operator LIKE '%$searchValue%'
    ) ";
}

// 3. เงื่อนไขตัวกรองประเภท (Filter)
$filterQuery = " ";
if($filter_type != ''){
    $filterQuery = " AND h.action_type = '$filter_type' ";
}

// 4. SQL พื้นฐานสำหรับการ Join ตาราง
$sql_base = "FROM product_history h 
             LEFT JOIN projects p ON h.project_id = p.id 
             LEFT JOIN product_serials ps ON h.serial_number = ps.serial_number
             LEFT JOIN products pr ON ps.product_barcode = pr.barcode 
             WHERE 1=1 ";

// นับจำนวนทั้งหมด (ไม่กรอง)
$sel = $conn->query("SELECT COUNT(h.id) as allcount " . $sql_base);
$totalRecords = $sel->fetch_assoc()['allcount'];

// นับจำนวนเวลาค้นหา (มีกรอง)
$sel = $conn->query("SELECT COUNT(h.id) as allcount " . $sql_base . $searchQuery . $filterQuery);
$totalRecordwithFilter = $sel->fetch_assoc()['allcount'];

// 5. ดึงข้อมูลจริง (ใส่ LIMIT)
$sql_data = "SELECT h.*, h.project_name AS snapshot_name, p.project_name AS current_name, pr.name as product_name " 
            . $sql_base . $searchQuery . $filterQuery 
            . " ORDER BY h.id DESC LIMIT $row, $rowperpage";
$records = $conn->query($sql_data);

// 6. จัดรูปแบบข้อมูลส่งกลับไปหน้าเว็บ
$data = array();
while($row_data = $records->fetch_assoc()) {
    
    // จัดรูปแบบป้ายสถานะ
    $badge = '';
    if($row_data['action_type'] == 'import') $badge = '<span class="badge bg-success rounded-pill px-3">รับเข้า</span>';
    elseif($row_data['action_type'] == 'export') $badge = '<span class="badge bg-danger rounded-pill px-3">เบิกออก</span>';
    elseif($row_data['action_type'] == 'return') $badge = '<span class="badge bg-warning text-dark rounded-pill px-3">รับคืน</span>';
    
    // ผู้ทำรายการ
    $operator = $row_data['operator'] ? '<i class="fas fa-user-circle text-secondary me-1"></i> '.$row_data['operator'] : '-';
    
    // ชื่อสินค้า
    $pro_name = $row_data['product_name'] ? '<div class="fw-bold text-dark">'.$row_data['product_name'].'</div>' : '<span class="text-muted small">- ไม่พบชื่อสินค้า -</span>';
    
    // ชื่อโครงการ / ที่อยู่
    $display_project_name = !empty($row_data['snapshot_name']) ? $row_data['snapshot_name'] : $row_data['current_name'];
    if (empty($display_project_name) && !empty($row_data['project_id'])) {
        $display_project_name = '<span class="text-muted fst-italic">(โครงการถูกลบ)</span>';
    }
    
    $project_info = '-';
    if($row_data['action_type'] == 'import' || $row_data['action_type'] == 'return') {
        $project_info = '<div class="small text-muted fst-italic"><i class="fas fa-warehouse text-secondary me-1"></i>คลังสินค้า</div>';
    } elseif ($display_project_name) {
        $project_info = '<div class="small fw-bold"><i class="fas fa-hard-hat me-2 text-primary"></i>'.$display_project_name.'</div>';
    }

    // หมายเหตุ และปุ่มดินสอแก้ไข
    $note_html = '<div class="d-flex justify-content-between align-items-center">
                    <div style="max-width: 200px;" class="text-truncate text-muted small fst-italic">
                        <span id="note_'.$row_data['id'].'">'.$row_data['note'].'</span>
                    </div>
                    <i class="fas fa-pen btn-edit-note ms-2" style="flex-shrink: 0;" onclick="editNote('.$row_data['id'].')"></i>
                  </div>';

    // ใส่ข้อมูลลง Array เรียงตามคอลัมน์ (0-6)
    $data[] = array(
        '<span class="small">'.date('d/m/Y H:i', strtotime($row_data['action_date'])).'</span>',
        '<div class="text-center">'.$badge.'</div>',
        $operator,
        '<span class="fw-bold text-dark">'.$row_data['serial_number'].'</span>',
        $pro_name,
        $project_info,
        $note_html
    );
}

// 7. สรุปเป็น JSON คืน DataTables
$response = array(
    "draw" => intval($draw),
    "iTotalRecords" => $totalRecords,
    "iTotalDisplayRecords" => $totalRecordwithFilter,
    "aaData" => $data
);

echo json_encode($response);
?>