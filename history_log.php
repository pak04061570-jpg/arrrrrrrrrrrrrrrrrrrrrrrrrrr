<?php include 'db_connect.php'; 

// รับค่าจากตัวกรอง (ถ้ามี)
$filter_type = isset($_GET['type']) ? $_GET['type'] : '';

?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>ประวัติรับเข้า/เบิกออก - Stock</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css" rel="stylesheet">
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
        .card-custom { border: none; border-radius: 16px; box-shadow: 0 4px 20px rgba(0,0,0,0.03); background: #fff; overflow: hidden; }
        .btn-edit-note { cursor: pointer; color: #fbbf24; transition: 0.2s; }
        .btn-edit-note:hover { color: #d97706; }
        
        .header-select {
            background-color: transparent;
            border: none;
            font-weight: bold;
            text-align: center;
            cursor: pointer;
            color: var(--bs-table-color);
            width: 100%;
            padding: 0;
        }
        .header-select:focus { outline: none; box-shadow: none; }
        .header-select option { color: #000; font-weight: normal; }

        /* [✨] เพิ่ม Effect ตอนชี้เมาส์ที่ลิงก์ S/N */
        .sn-link {
            transition: 0.2s;
            text-decoration: none;
        }
        .sn-link:hover {
            text-decoration: underline;
            color: #1d4ed8 !important; /* น้ำเงินเข้มขึ้นตอนชี้ */
        }
    </style>
</head>
<body>

<?php include 'sidebar.php'; ?>

<div class="main-content">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="fw-bold text-secondary m-0"><i class="fas fa-history me-2"></i>ประวัติการรับเข้า / เบิกออก (Log)</h4>
    </div>

    <div class="card card-custom">
        <div class="card-body">
            <table id="historyTable" class="table table-hover w-100 align-middle">
                <thead class="table-light">
                    <tr>
                        <th width="12%">วัน/เวลา</th>
                        <th width="10%" class="text-center p-0 align-middle">
                            <select id="filter_type" class="form-select form-select-sm header-select">
                                <option value="">ทุกประเภท</option>
                                <option value="import">รับเข้า</option>
                                <option value="export">เบิกออก</option>
                                <option value="return">รับคืน</option>
                            </select>
                        </th>
                        <th width="12%">ผู้ทำรายการ</th>
                        <th width="12%">S/N</th>
                        <th width="20%">ชื่อสินค้า</th>
                        <th width="15%">ที่อยู่ (โครงการ)</th> <th width="19%">หมายเหตุ</th>
                    </tr>
                </thead>
                <tbody>
                    
                </tbody>
            </table>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    $(document).ready(function(){
        let table = $('#historyTable').DataTable({
        "language": { "url": "//cdn.datatables.net/plug-ins/1.13.4/i18n/th.json" },
        "processing": true,
        "serverSide": true, // เปิดโหมด Server-Side
        "ajax": {
            "url": "api_history_ss.php", // ไฟล์ที่เราจะสร้างใหม่
            "type": "POST",
            "data": function(d) {
                // ส่งค่าจากช่อง Dropdown ไปให้ Server กรองด้วย
                d.filter_type = $('#filter_type').val();
            }
        },
        "order": [[ 0, "desc" ]],
        "columnDefs": [ { "orderable": false, "targets": [1, 2, 3, 4, 5, 6] } ]
    });

    // เมื่อเปลี่ยนประเภท (รับเข้า/เบิกออก) ให้ตารางโหลดข้อมูลใหม่ทันที
    $('#filter_type').change(function(){
        table.draw();
    });
    });
</script>
</body>
</html>