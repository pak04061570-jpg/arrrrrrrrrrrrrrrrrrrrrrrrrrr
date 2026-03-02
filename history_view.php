<?php include 'db_connect.php'; ?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>ประวัติสินค้า</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Sarabun:wght@300;400;500;700&display=swap" rel="stylesheet">
    <style> 
        :root { --primary: #0f172a; --accent: #3b82f6; --bg: #f1f5f9; }
        body { font-family: 'Sarabun', sans-serif; background-color: var(--bg); color: #334155; }
        
        .sidebar { background: var(--primary); min-height: 100vh; width: 260px; position: fixed; top: 0; left: 0; padding-top: 20px; z-index: 1000; }
        .nav-link { color: #94a3b8; padding: 12px 25px; margin: 4px 16px; border-radius: 8px; transition: 0.3s; }
        .nav-link:hover, .nav-link.active { background: rgba(255,255,255,0.1); color: #fff; }
        .nav-link.active { background: var(--accent); }
        
        .main-content { margin-left: 260px; padding: 30px; }
        .card-custom { border: none; border-radius: 16px; box-shadow: 0 4px 20px rgba(0,0,0,0.03); background: #fff; padding: 30px; }
        
        .product-info-box { background-color: #f8fafc; border-left: 4px solid var(--accent); border-radius: 8px; padding: 15px 20px; }
    </style>
</head>
<body>

<?php include 'sidebar.php'; ?>

<div class="main-content">
    <div class="card-custom">
        
        <button onclick="window.history.back();" class="btn btn-outline-secondary mb-4 rounded-pill px-4">
            <i class="fas fa-arrow-left me-1"></i> ย้อนกลับ
        </button>

        <h4 class="mb-4 fw-bold text-secondary"><i class="fas fa-history text-primary me-2"></i>Timeline ประวัติสินค้า</h4>

        <?php if(isset($_GET['sn']) && $_GET['sn'] != ''): 
            $sn = $conn->real_escape_string($_GET['sn']);
            
            // 1. Query ค้นหาชื่อสินค้าและ Barcode จากตาราง product_serials โยงไปตาราง products
            $sql_product = "SELECT p.name, p.barcode 
                            FROM product_serials s 
                            LEFT JOIN products p ON s.product_barcode = p.barcode 
                            WHERE s.serial_number = '$sn' LIMIT 1";
            $prod_result = $conn->query($sql_product);
            
            $product_name = "ไม่พบข้อมูลสินค้า (อาจถูกลบไปแล้ว)";
            $product_barcode = "";
            
            if($prod_result && $prod_result->num_rows > 0) {
                $p_data = $prod_result->fetch_assoc();
                if(!empty($p_data['name'])) {
                    $product_name = $p_data['name'];
                    $product_barcode = $p_data['barcode'];
                }
            }

            // 2. Query ดึงประวัติ Timeline
            $sql = "SELECT h.*, 
                           h.project_name AS snapshot_name, 
                           p.project_name AS current_name 
                    FROM product_history h 
                    LEFT JOIN projects p ON h.project_id = p.id 
                    WHERE h.serial_number = '$sn' 
                    ORDER BY h.id DESC";
            $result = $conn->query($sql);
        ?>
        
        <div class="product-info-box mb-4 shadow-sm">
            <h5 class="fw-bold text-dark mb-2"><?php echo htmlspecialchars($product_name); ?></h5>
            <div class="text-secondary small">
                <span class="me-4"><i class="fas fa-barcode me-1"></i> S/N: <strong class="text-primary fs-6"><?php echo htmlspecialchars($sn); ?></strong></span>
                <?php if($product_barcode): ?>
                <span><i class="fas fa-tag me-1"></i> SKU: <span class="badge bg-secondary"><?php echo htmlspecialchars($product_barcode); ?></span></span>
                <?php endif; ?>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-bordered mt-3 align-middle">
        <thead class="table-light">
            <tr>
                <th width="15%">วัน/เวลา</th>
                <th width="12%" class="text-center">เหตุการณ์</th>
                <th width="18%">ผู้ทำรายการ</th> <th width="35%">รายละเอียด / โครงการ</th>
                <th width="20%">หมายเหตุ (Note)</th>
            </tr>
        </thead>
        <tbody>
            <?php while($row = $result->fetch_assoc()): 
                $badge = '';
                if($row['action_type'] == 'import') $badge = '<span class="badge bg-success rounded-pill px-3">รับเข้า</span>';
                if($row['action_type'] == 'export') $badge = '<span class="badge bg-danger rounded-pill px-3">เบิกออก</span>';
                if($row['action_type'] == 'return') $badge = '<span class="badge bg-warning text-dark rounded-pill px-3">รับคืน</span>';

                // Logic: เลือกชื่อที่ถูกต้อง
                $show_name = !empty($row['snapshot_name']) ? $row['snapshot_name'] : $row['current_name'];
                
                if(empty($show_name) && $row['project_id'] > 0) {
                    $show_name = '<span class="text-muted fst-italic">(โครงการถูกลบ)</span>';
                } elseif(empty($show_name)) {
                    $show_name = "-";
                }

                // ✨ ดึงข้อมูลผู้ทำรายการ ถ้าไม่มีให้ใส่เครื่องหมาย -
                $operator = !empty($row['operator']) ? '<i class="fas fa-user-circle text-secondary me-1"></i> ' . htmlspecialchars($row['operator']) : '-';
            ?>
            <tr>
                <td class="small"><?php echo date('d/m/Y H:i', strtotime($row['action_date'])); ?></td>
                <td class="text-center"><?php echo $badge; ?></td>
                
                <td><?php echo $operator; ?></td>
                
                <td class="fw-bold text-dark">
                    <?php echo ($show_name != "-") ? '<i class="fas fa-hard-hat me-2 text-primary"></i> โครงการ: ' . $show_name : '<span class="text-muted"><i class="fas fa-warehouse me-1"></i> - คลังสินค้า -</span>'; ?>
                </td>
                <td>
                    <div class="d-flex justify-content-between align-items-center">
                        <span id="note_text_<?php echo $row['id']; ?>" class="text-muted fst-italic"><?php echo htmlspecialchars($row['note']); ?></span>
                        <button class="btn btn-sm text-warning" onclick="editNote(<?php echo $row['id']; ?>, '<?php echo htmlspecialchars($row['note'], ENT_QUOTES); ?>')">
                            <i class="fas fa-pen"></i>
                        </button>
                    </div>
                </td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
        </div>

        <?php else: ?>
            <div class="alert alert-warning text-center py-4 rounded-4">
                <i class="fas fa-exclamation-circle fs-3 mb-2 text-warning"></i><br>
                <strong>ไม่พบข้อมูล Serial Number ที่ต้องการค้นหา</strong><br>
                <span class="small text-muted">กรุณากดดูประวัติจากหน้ารายการสินค้า หรือหน้าประวัติรวม</span>
            </div>
        <?php endif; ?>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    function editNote(id, oldNote) {
        Swal.fire({
            title: 'แก้ไขหมายเหตุ',
            input: 'text',
            inputValue: oldNote,
            showCancelButton: true,
            confirmButtonText: 'บันทึก'
        }).then((result) => {
            if (result.isConfirmed) {
                $.post("api_update_history.php", { id: id, note: result.value }, function(res) {
                    if(res.trim() == 'success') {
                        document.getElementById('note_text_' + id).innerText = result.value;
                        Swal.fire({ icon: 'success', title: 'แก้ไขแล้ว', toast: true, position: 'top-end', showConfirmButton: false, timer: 1500 });
                    } else {
                        Swal.fire('Error', res, 'error');
                    }
                });
            }
        })
    }

    // ดักจับการกด Esc เพื่อย้อนกลับ
    document.addEventListener('keydown', function(event) {
        if (event.key === "Escape") {
            let isSwalOpen = document.body.classList.contains('swal2-shown');
            if (!isSwalOpen) {
                window.history.back();
            }
        }
    });
</script>

</body>
</html>