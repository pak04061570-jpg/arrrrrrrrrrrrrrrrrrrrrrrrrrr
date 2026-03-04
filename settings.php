<?php
include 'db_connect.php';

// ป้องกันไม่ให้ Staff หรือคนที่ยังไม่ล็อกอินเข้ามาหน้านี้
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: index.php");
    exit;
}

$status_msg = '';
$status_type = '';

// ==========================================
// 🚀 ส่วนประมวลผลการทำงาน (Backend / CRUD)
// ==========================================
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    
    // 1. จัดการผู้ใช้งาน (Users)
    if (isset($_POST['add_user'])) {
        $u = $conn->real_escape_string($_POST['username']);
        $p = md5($_POST['password']);
        $n = $conn->real_escape_string($_POST['name']);
        $r = $conn->real_escape_string($_POST['role']);
        
        $check = $conn->query("SELECT id FROM users WHERE username = '$u'");
        if($check->num_rows > 0) {
            $status_msg = "ชื่อผู้ใช้นี้มีในระบบแล้ว!"; $status_type = "error";
        } else {
            $conn->query("INSERT INTO users (username, password, name, role) VALUES ('$u', '$p', '$n', '$r')");
            $status_msg = "เพิ่มผู้ใช้งานเรียบร้อย"; $status_type = "success";
        }
    }
    if (isset($_POST['edit_user'])) {
        $id = $_POST['edit_id'];
        $name = $conn->real_escape_string($_POST['edit_name']);
        $role = $conn->real_escape_string($_POST['edit_role']);
        $conn->query("UPDATE users SET name = '$name', role = '$role' WHERE id = '$id'");
        $status_msg = "แก้ไขข้อมูลผู้ใช้งานเรียบร้อย"; $status_type = "success";
    }
    if (isset($_POST['delete_user'])) {
        $id = $_POST['delete_user'];
        if ($id == $_SESSION['user_id']) {
            $status_msg = "ไม่สามารถลบบัญชีที่กำลังล็อกอินได้!"; $status_type = "error";
        } else {
            $conn->query("DELETE FROM users WHERE id = '$id'");
            $status_msg = "ลบผู้ใช้งานเรียบร้อย"; $status_type = "success";
        }
    }

    // 2. จัดการรายชื่อผู้เบิกสินค้า (Withdrawers)
    if (isset($_POST['add_withdrawer'])) {
        $name = $conn->real_escape_string($_POST['withdrawer_name']);
        $conn->query("INSERT INTO withdrawers (name) VALUES ('$name')");
        $status_msg = "เพิ่มรายชื่อผู้เบิกเรียบร้อย"; $status_type = "success";
    }
    if (isset($_POST['edit_withdrawer'])) {
        $id = $_POST['edit_id'];
        $name = $conn->real_escape_string($_POST['edit_name']);
        $conn->query("UPDATE withdrawers SET name = '$name' WHERE id = '$id'");
        $status_msg = "แก้ไขชื่อผู้เบิกเรียบร้อย"; $status_type = "success";
    }
    if (isset($_POST['delete_withdrawer'])) {
        $id = $_POST['delete_withdrawer'];
        $conn->query("DELETE FROM withdrawers WHERE id = '$id'");
        $status_msg = "ลบรายชื่อผู้เบิกเรียบร้อย"; $status_type = "success";
    }

    // 3. จัดการหมวดหมู่ (Product Types)
    if (isset($_POST['add_type'])) {
        $name = $conn->real_escape_string($_POST['type_name']);
        $conn->query("INSERT INTO product_types (name) VALUES ('$name')");
        $status_msg = "เพิ่มหมวดหมู่เรียบร้อย"; $status_type = "success";
    }
    if (isset($_POST['edit_type'])) {
        $id = $_POST['edit_id'];
        $name = $conn->real_escape_string($_POST['edit_name']);
        $conn->query("UPDATE product_types SET name = '$name' WHERE id = '$id'");
        $status_msg = "แก้ไขชื่อหมวดหมู่เรียบร้อย"; $status_type = "success";
    }
    if (isset($_POST['delete_type'])) {
        $id = $_POST['delete_type'];
        $check = $conn->query("SELECT id FROM products WHERE type_id = '$id'");
        if($check->num_rows > 0) {
            $status_msg = "ลบไม่ได้! มีสินค้า ". $check->num_rows ." รายการกำลังใช้หมวดหมู่นี้อยู่"; $status_type = "warning";
        } else {
            $conn->query("DELETE FROM product_types WHERE id = '$id'");
            $status_msg = "ลบหมวดหมู่เรียบร้อย"; $status_type = "success";
        }
    }

    // 4. จัดการหน่วยนับ (Units)
    if (isset($_POST['add_unit'])) {
        $name = $conn->real_escape_string($_POST['unit_name']);
        $conn->query("INSERT INTO units (name) VALUES ('$name')");
        $status_msg = "เพิ่มหน่วยนับเรียบร้อย"; $status_type = "success";
    }
    if (isset($_POST['edit_unit'])) {
        $id = $_POST['edit_id'];
        $name = $conn->real_escape_string($_POST['edit_name']);
        $conn->query("UPDATE units SET name = '$name' WHERE id = '$id'");
        $status_msg = "แก้ไขชื่อหน่วยนับเรียบร้อย"; $status_type = "success";
    }
    if (isset($_POST['delete_unit'])) {
        $id = $_POST['delete_unit'];
        $check = $conn->query("SELECT id FROM products WHERE unit_id = '$id'");
        if($check->num_rows > 0) {
            $status_msg = "ลบไม่ได้! มีสินค้ากำลังใช้หน่วยนับนี้อยู่"; $status_type = "warning";
        } else {
            $conn->query("DELETE FROM units WHERE id = '$id'");
            $status_msg = "ลบหน่วยนับเรียบร้อย"; $status_type = "success";
        }
    }

    // 5. จัดการซัพพลายเออร์ (Suppliers)
    if (isset($_POST['add_supplier'])) {
        $name = $conn->real_escape_string($_POST['supplier_name']);
        $conn->query("INSERT INTO suppliers (name) VALUES ('$name')");
        $status_msg = "เพิ่มซัพพลายเออร์เรียบร้อย"; $status_type = "success";
    }
    if (isset($_POST['edit_supplier'])) {
        $id = $_POST['edit_id'];
        $name = $conn->real_escape_string($_POST['edit_name']);
        $conn->query("UPDATE suppliers SET name = '$name' WHERE id = '$id'");
        $status_msg = "แก้ไขชื่อซัพพลายเออร์เรียบร้อย"; $status_type = "success";
    }
    if (isset($_POST['delete_supplier'])) {
        $id = $_POST['delete_supplier'];
        $check = $conn->query("SELECT id FROM products WHERE supplier_id = '$id'");
        if($check->num_rows > 0) {
            $status_msg = "ลบไม่ได้! มีสินค้าที่รับมาจากซัพพลายเออร์รายนี้อยู่"; $status_type = "warning";
        } else {
            $conn->query("DELETE FROM suppliers WHERE id = '$id'");
            $status_msg = "ลบซัพพลายเออร์เรียบร้อย"; $status_type = "success";
        }
    }
}

// ดึงข้อมูลทั้งหมดมาแสดงในตาราง
$users = $conn->query("SELECT * FROM users");
$withdrawers_data = $conn->query("SELECT * FROM withdrawers ORDER BY name ASC");
$types = $conn->query("SELECT * FROM product_types");
$units = $conn->query("SELECT * FROM units");
$suppliers = $conn->query("SELECT * FROM suppliers");
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>ตั้งค่าระบบ - Stock</title>
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
        .card { border: none; border-radius: 12px; box-shadow: 0 4px 10px rgba(0,0,0,0.05); }
        .nav-tabs .nav-link { font-weight: bold; color: #64748b; border: none; border-bottom: 3px solid transparent; padding: 12px 20px; }
        .nav-tabs .nav-link.active { color: #3b82f6; background: transparent; border-bottom: 3px solid #3b82f6; }
        .table th { background-color: #f8fafc; color: #475569; font-weight: 600; }
    </style>
</head>
<body>

<?php include 'sidebar.php'; ?>

<div class="main-content">
    <h4 class="fw-bold mb-4 text-secondary"><i class="fas fa-cogs text-warning me-2"></i>ตั้งค่าข้อมูลพื้นฐาน (Master Data)</h4>

    <div class="card p-4">
        <ul class="nav nav-tabs mb-4" id="settingsTab" role="tablist">
            <li class="nav-item"><button class="nav-link active" data-bs-toggle="tab" data-bs-target="#tab-users"><i class="fas fa-users me-1"></i> ผู้ใช้งาน</button></li>
            <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-withdrawers"><i class="fas fa-user-hard-hat me-1"></i> รายชื่อผู้เบิก</button></li>
            <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-types"><i class="fas fa-tags me-1"></i> หมวดหมู่</button></li>
            <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-units"><i class="fas fa-weight-hanging me-1"></i> หน่วยนับ</button></li>
            <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-suppliers"><i class="fas fa-store me-1"></i> ซัพพลายเออร์</button></li>
        </ul>

        <div class="tab-content" id="settingsTabContent">
            
            <div class="tab-pane fade show active" id="tab-users">
                <form method="POST" class="row g-2 mb-4 align-items-end p-3 bg-light rounded">
                    <div class="col-md-3"><label class="small fw-bold">Username</label><input type="text" name="username" class="form-control" required></div>
                    <div class="col-md-3"><label class="small fw-bold">Password</label><input type="password" name="password" class="form-control" required></div>
                    <div class="col-md-3"><label class="small fw-bold">ชื่อ-นามสกุล (ที่โชว์ในระบบ)</label><input type="text" name="name" class="form-control" required></div>
                    <div class="col-md-2">
                        <label class="small fw-bold">สิทธิ์การใช้งาน</label>
                        <select name="role" class="form-select">
                            <option value="staff">พนักงาน (Staff)</option>
                            <option value="admin">ผู้ดูแล (Admin)</option>
                        </select>
                    </div>
                    <div class="col-md-1"><button type="submit" name="add_user" class="btn btn-primary w-100"><i class="fas fa-plus"></i></button></div>
                </form>

                <table class="table table-bordered table-hover">
                    <thead><tr><th class="text-center" width="8%">ลำดับ</th><th>Username</th><th>ชื่อ-นามสกุล</th><th>สิทธิ์ (Role)</th><th class="text-center" width="15%">จัดการ</th></tr></thead>
                    <tbody>
                        <?php $i=1; while($row = $users->fetch_assoc()): ?>
                        <tr>
                            <td class="text-center text-muted"><?php echo $i++; ?></td>
                            <td><span class="fw-bold text-primary"><?php echo htmlspecialchars($row['username']); ?></span></td>
                            <td><?php echo htmlspecialchars($row['name']); ?></td>
                            <td>
                                <?php if($row['role'] == 'admin') echo '<span class="badge bg-danger">Admin</span>'; else echo '<span class="badge bg-secondary">Staff</span>'; ?>
                            </td>
                            <td class="text-center">
                                <button class="btn btn-sm btn-outline-warning me-1" onclick="editUser('<?php echo $row['id']; ?>', '<?php echo htmlspecialchars($row['name']); ?>', '<?php echo $row['role']; ?>')"><i class="fas fa-edit"></i></button>
                                <form method="POST" class="d-inline" onsubmit="return confirm('ยืนยันการลบผู้ใช้นี้?');">
                                    <input type="hidden" name="delete_user" value="<?php echo $row['id']; ?>">
                                    <button type="submit" class="btn btn-sm btn-outline-danger"><i class="fas fa-trash"></i></button>
                                </form>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>

            <div class="tab-pane fade" id="tab-withdrawers">
                <form method="POST" class="row g-2 mb-4 p-3 bg-light rounded w-75">
                    <div class="col-9"><input type="text" name="withdrawer_name" class="form-control" placeholder="ชื่อผู้เบิก / ช่าง / ผู้รับเหมา..." required></div>
                    <div class="col-3"><button type="submit" name="add_withdrawer" class="btn btn-primary w-100"><i class="fas fa-plus"></i> เพิ่ม</button></div>
                </form>
                <table class="table table-bordered w-75">
                    <thead><tr><th class="text-center" width="10%">ลำดับ</th><th>ชื่อผู้เบิกสินค้า</th><th class="text-center" width="20%">จัดการ</th></tr></thead>
                    <tbody>
                        <?php $i=1; while($row = $withdrawers_data->fetch_assoc()): ?>
                        <tr>
                            <td class="text-center text-muted"><?php echo $i++; ?></td>
                            <td><?php echo htmlspecialchars($row['name']); ?></td>
                            <td class="text-center">
                                <button class="btn btn-sm btn-outline-warning me-1" onclick="editItem('withdrawer', '<?php echo $row['id']; ?>', '<?php echo htmlspecialchars($row['name']); ?>')"><i class="fas fa-edit"></i></button>
                                <form method="POST" class="d-inline" onsubmit="return confirm('ยืนยันการลบรายชื่อผู้เบิกนี้?');">
                                    <input type="hidden" name="delete_withdrawer" value="<?php echo $row['id']; ?>">
                                    <button type="submit" class="btn btn-sm btn-outline-danger"><i class="fas fa-trash"></i></button>
                                </form>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>

            <div class="tab-pane fade" id="tab-types">
                <form method="POST" class="row g-2 mb-4 p-3 bg-light rounded w-75">
                    <div class="col-9"><input type="text" name="type_name" class="form-control" placeholder="ชื่อหมวดหมู่ใหม่..." required></div>
                    <div class="col-3"><button type="submit" name="add_type" class="btn btn-primary w-100"><i class="fas fa-plus"></i> เพิ่ม</button></div>
                </form>
                <table class="table table-bordered w-75">
                    <thead><tr><th class="text-center" width="10%">ลำดับ</th><th>ชื่อหมวดหมู่</th><th class="text-center" width="20%">จัดการ</th></tr></thead>
                    <tbody>
                        <?php $i=1; while($row = $types->fetch_assoc()): ?>
                        <tr>
                            <td class="text-center text-muted"><?php echo $i++; ?></td>
                            <td><?php echo htmlspecialchars($row['name']); ?></td>
                            <td class="text-center">
                                <button class="btn btn-sm btn-outline-warning me-1" onclick="editItem('type', '<?php echo $row['id']; ?>', '<?php echo htmlspecialchars($row['name']); ?>')"><i class="fas fa-edit"></i></button>
                                <form method="POST" class="d-inline" onsubmit="return confirm('ยืนยันการลบหมวดหมู่นี้?');">
                                    <input type="hidden" name="delete_type" value="<?php echo $row['id']; ?>">
                                    <button type="submit" class="btn btn-sm btn-outline-danger"><i class="fas fa-trash"></i></button>
                                </form>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>

            <div class="tab-pane fade" id="tab-units">
                <form method="POST" class="row g-2 mb-4 p-3 bg-light rounded w-75">
                    <div class="col-9"><input type="text" name="unit_name" class="form-control" placeholder="ชื่อหน่วยนับใหม่... (เช่น ชุด, เมตร)" required></div>
                    <div class="col-3"><button type="submit" name="add_unit" class="btn btn-primary w-100"><i class="fas fa-plus"></i> เพิ่ม</button></div>
                </form>
                <table class="table table-bordered w-75">
                    <thead><tr><th class="text-center" width="10%">ลำดับ</th><th>ชื่อหน่วยนับ</th><th class="text-center" width="20%">จัดการ</th></tr></thead>
                    <tbody>
                        <?php $i=1; while($row = $units->fetch_assoc()): ?>
                        <tr>
                            <td class="text-center text-muted"><?php echo $i++; ?></td>
                            <td><?php echo htmlspecialchars($row['name']); ?></td>
                            <td class="text-center">
                                <button class="btn btn-sm btn-outline-warning me-1" onclick="editItem('unit', '<?php echo $row['id']; ?>', '<?php echo htmlspecialchars($row['name']); ?>')"><i class="fas fa-edit"></i></button>
                                <form method="POST" class="d-inline" onsubmit="return confirm('ยืนยันการลบหน่วยนับนี้?');">
                                    <input type="hidden" name="delete_unit" value="<?php echo $row['id']; ?>">
                                    <button type="submit" class="btn btn-sm btn-outline-danger"><i class="fas fa-trash"></i></button>
                                </form>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>

            <div class="tab-pane fade" id="tab-suppliers">
                <form method="POST" class="row g-2 mb-4 p-3 bg-light rounded w-75">
                    <div class="col-9"><input type="text" name="supplier_name" class="form-control" placeholder="ชื่อบริษัทซัพพลายเออร์..." required></div>
                    <div class="col-3"><button type="submit" name="add_supplier" class="btn btn-primary w-100"><i class="fas fa-plus"></i> เพิ่ม</button></div>
                </form>
                <table class="table table-bordered w-75">
                    <thead><tr><th class="text-center" width="10%">ลำดับ</th><th>ชื่อซัพพลายเออร์</th><th class="text-center" width="20%">จัดการ</th></tr></thead>
                    <tbody>
                        <?php $i=1; while($row = $suppliers->fetch_assoc()): ?>
                        <tr>
                            <td class="text-center text-muted"><?php echo $i++; ?></td>
                            <td><?php echo htmlspecialchars($row['name']); ?></td>
                            <td class="text-center">
                                <button class="btn btn-sm btn-outline-warning me-1" onclick="editItem('supplier', '<?php echo $row['id']; ?>', '<?php echo htmlspecialchars($row['name']); ?>')"><i class="fas fa-edit"></i></button>
                                <form method="POST" class="d-inline" onsubmit="return confirm('ยืนยันการลบซัพพลายเออร์นี้?');">
                                    <input type="hidden" name="delete_supplier" value="<?php echo $row['id']; ?>">
                                    <button type="submit" class="btn btn-sm btn-outline-danger"><i class="fas fa-trash"></i></button>
                                </form>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>

        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    // แจ้งเตือนเมื่อมีการกด เพิ่ม/แก้ไข/ลบ ข้อมูล
    <?php if($status_msg != ''): ?>
        Swal.fire({
            icon: '<?php echo $status_type; ?>',
            title: '<?php echo $status_msg; ?>',
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 3000
        });
    <?php endif; ?>

    // ทำให้เมื่อ Refresh หน้าเว็บแล้วยังอยู่ที่ Tab เดิม
    $(document).ready(function(){
        $('button[data-bs-toggle="tab"]').on('show.bs.tab', function(e) {
            localStorage.setItem('activeTab', $(e.target).attr('data-bs-target'));
        });
        var activeTab = localStorage.getItem('activeTab');
        if(activeTab){
            $('#settingsTab button[data-bs-target="' + activeTab + '"]').tab('show');
        }
    });

    // 🌟 ฟังก์ชันแก้ไขข้อมูล (หมวดหมู่, หน่วยนับ, ซัพพลายเออร์, ผู้เบิก)
    async function editItem(type, id, oldName) {
        let titleText = '';
        let inputName = '';
        
        if(type === 'withdrawer') { titleText = 'แก้ไขชื่อผู้เบิก'; inputName = 'edit_withdrawer'; }
        if(type === 'type') { titleText = 'แก้ไขชื่อหมวดหมู่'; inputName = 'edit_type'; }
        if(type === 'unit') { titleText = 'แก้ไขชื่อหน่วยนับ'; inputName = 'edit_unit'; }
        if(type === 'supplier') { titleText = 'แก้ไขชื่อซัพพลายเออร์'; inputName = 'edit_supplier'; }

        const { value: newName } = await Swal.fire({
            title: titleText,
            input: 'text',
            inputValue: oldName,
            showCancelButton: true,
            confirmButtonText: 'บันทึก',
            cancelButtonText: 'ยกเลิก',
            inputValidator: (value) => {
                if (!value) return 'กรุณากรอกข้อมูลให้ครบถ้วน!'
            }
        });

        if (newName && newName !== oldName) {
            let form = document.createElement('form');
            form.method = 'POST';
            form.innerHTML = `
                <input type="hidden" name="${inputName}" value="1">
                <input type="hidden" name="edit_id" value="${id}">
                <input type="hidden" name="edit_name" value="${newName}">
            `;
            document.body.appendChild(form);
            form.submit();
        }
    }

    // 🌟 ฟังก์ชันแก้ไขข้อมูลผู้ใช้งาน (แก้ไขชื่อ และ สิทธิ์)
    async function editUser(id, oldName, oldRole) {
        const { value: formValues } = await Swal.fire({
            title: 'แก้ไขข้อมูลผู้ใช้งาน',
            html: `
                <div class="text-start mb-1 fw-bold small">ชื่อ-นามสกุล</div>
                <input id="swal-input-name" class="swal2-input w-100 m-0 mb-3" value="${oldName}" placeholder="ชื่อ-นามสกุล">
                <div class="text-start mb-1 fw-bold small">สิทธิ์การใช้งาน</div>
                <select id="swal-input-role" class="swal2-select w-100 m-0" style="display:flex;">
                    <option value="staff" ${oldRole === 'staff' ? 'selected' : ''}>พนักงาน (Staff)</option>
                    <option value="admin" ${oldRole === 'admin' ? 'selected' : ''}>ผู้ดูแล (Admin)</option>
                </select>
            `,
            focusConfirm: false,
            showCancelButton: true,
            confirmButtonText: 'บันทึก',
            cancelButtonText: 'ยกเลิก',
            preConfirm: () => {
                let n = document.getElementById('swal-input-name').value;
                if(!n) { Swal.showValidationMessage('กรุณากรอกชื่อ!'); return false; }
                return {
                    name: n,
                    role: document.getElementById('swal-input-role').value
                }
            }
        });

        if (formValues) {
            let form = document.createElement('form');
            form.method = 'POST';
            form.innerHTML = `
                <input type="hidden" name="edit_user" value="1">
                <input type="hidden" name="edit_id" value="${id}">
                <input type="hidden" name="edit_name" value="${formValues.name}">
                <input type="hidden" name="edit_role" value="${formValues.role}">
            `;
            document.body.appendChild(form);
            form.submit();
        }
    }
</script>
</body>
</html>