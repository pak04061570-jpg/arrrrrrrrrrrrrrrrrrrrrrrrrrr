<?php 
include 'db_connect.php';

$pid = isset($_GET['id']) ? $_GET['id'] : 0;

// ดึงข้อมูลโปรเจกต์
$proj = $conn->query("SELECT * FROM projects WHERE id = $pid")->fetch_assoc();
if(!$proj) die("ไม่พบข้อมูลโปรเจกต์");

$is_closed = ($proj['status'] == 'Closed');

// [✨] ดึงรายชื่อผู้เบิกสินค้า
$withdrawers = $conn->query("SELECT * FROM withdrawers ORDER BY name ASC");
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>จัดการโครงการ: <?php echo htmlspecialchars($proj['project_name']); ?></title>
    
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
        .card-header-custom { background: #fff; padding: 20px 25px; border-bottom: 1px solid #f1f5f9; }
        .form-control-lg { border-radius: 10px; border: 2px solid #e2e8f0; }
        .form-control-lg:focus { border-color: var(--accent); box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.1); }
        .closed-job { opacity: 0.8; background-color: #f8f9fa; }
        .closed-job .btn-action, .closed-job .form-check-input { display: none !important; }
        
        .select-col { display: none; }
        .form-check-input.big-checkbox { width: 1.4em; height: 1.4em; margin-top: 0.2em; border: 2px solid #94a3b8; cursor: pointer; }
        .form-check-input.big-checkbox:checked { background-color: #dc3545; border-color: #dc3545; }
    </style>
</head>
<body>

<?php include 'sidebar.php'; ?>

<div class="main-content">
    
    <div class="d-flex justify-content-between align-items-start mb-4">
        <div>
            <a href="projects.php" class="btn btn-outline-secondary mb-4 rounded-pill px-4">
                <i class="fas fa-arrow-left me-1"></i> ย้อนกลับ 
            </a>

            <div class="d-flex flex-column gap-2">
                <div class="d-flex align-items-center gap-3">
                    <h3 class="fw-bold text-primary m-0">
                        <i class="fas fa-clipboard-check me-2"></i><?php echo htmlspecialchars($proj['project_name']); ?>
                    </h3>
                    
                    <?php if(!$is_closed): ?>
                        <button class="btn btn-light btn-sm text-muted rounded-pill border" onclick="editProjectInfo()">
                            <i class="fas fa-pen"></i> แก้ไขข้อมูลโครงการ
                        </button>
                    <?php endif; ?>
                </div>

                <div class="d-flex gap-2 align-items-center">
                    <span class="badge bg-secondary fs-6">Job: <?php echo htmlspecialchars($proj['project_code']); ?></span>
                    <?php if($is_closed): ?>
                        <span class="badge bg-danger"><i class="fas fa-lock"></i> ปิดงานแล้ว (Closed)</span>
                    <?php else: ?>
                        <span class="badge bg-success">กำลังดำเนินการ (Open)</span>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="d-flex gap-2 align-items-center mt-4">
            <a href="print_job.php?id=<?php echo $pid; ?>" target="_blank" class="btn btn-outline-secondary rounded-pill px-3">
                <i class="fas fa-print me-2"></i> พิมพ์ใบเบิก/ใบคืน
            </a>
            <?php if(!$is_closed): ?>
            <button class="btn btn-danger rounded-pill px-3" onclick="closeJob()">
                <i class="fas fa-flag-checkered me-2"></i> ปิดโครงงานนี้
            </button>
            <?php endif; ?>
        </div>
    </div>

    <?php if(!$is_closed): ?>
    <div class="row g-4 mb-4">
        <div class="col-md-12">
            <div class="card card-custom">
                <div class="card-header-custom bg-primary text-white">
                    <h5 class="m-0 fw-bold"><i class="fas fa-dolly me-2"></i>เบิกของเข้าโครงการนี้</h5>
                </div>
                <div class="card-body p-4">
                    <div class="row">
                        <div class="col-md-6 border-end pe-4">
                            <label class="form-label fw-bold text-muted">วิธีที่ 1: สแกนเพื่อเบิก (Scan to Withdraw)</label>
                            
                            <div class="input-group">
                                <input type="text" id="scanInput" class="form-control form-control-lg" placeholder="ยิง S/N ที่นี่ (กด Enter เพื่อเพิ่ม)" autofocus autocomplete="off">
                                <button class="btn btn-primary" onclick="addToQueue()"><i class="fas fa-plus"></i> เพิ่มรายการ</button>
                            </div>
                            <div class="form-text text-muted mb-4"></div>

                            <div class="text-start mt-3 pt-3 border-top">
                                <span class="text-muted me-2">วิธีที่ 2: เลือกจากรายการที่มี</span>
                                <button class="btn btn-outline-primary btn-sm rounded-pill px-3" data-bs-toggle="modal" data-bs-target="#browseModal">
                                    <i class="fas fa-search me-1"></i> ค้นหาและเลือกสินค้า 
                                </button>
                            </div>
                        </div>

                        <div class="col-md-6 ps-4">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <h6 class="fw-bold text-secondary m-0">รายการรอเบิก (Waiting List)</h6>
                            </div>
                            <div class="border rounded p-0 mb-3" style="height: 180px; overflow-y: auto; background: #f8f9fa;">
                                <table class="table table-sm table-striped mb-0">
                                    <thead class="table-light sticky-top">
                                        <tr>
                                            <th class="ps-3">S/N</th>
                                            <th>ชื่อสินค้า</th>
                                            <th class="text-center">ลบ</th>
                                        </tr>
                                    </thead>
                                    <tbody id="queueBody"></tbody>
                                </table>
                                <div id="emptyMsg" class="text-center text-muted mt-4 small">ยังไม่มีรายการ<br>กรุณาสแกน S/N ทางซ้ายมือ</div>
                            </div>

                            <div class="bg-light p-3 rounded border shadow-sm">
                                <label class="fw-bold small mb-2 text-primary"><i class="fas fa-user-hard-hat me-1"></i> ระบุชื่อผู้เบิกสินค้า:</label>
                                <select id="withdrawer_name" class="form-select mb-3">
                                    <option value="">-- เลือกช่าง / ผู้เบิกสินค้า --</option>
                                    <?php while($w = $withdrawers->fetch_assoc()): ?>
                                        <option value="<?php echo htmlspecialchars($w['name']); ?>"><?php echo htmlspecialchars($w['name']); ?></option>
                                    <?php endwhile; ?>
                                </select>

                                <button id="btnConfirm" class="btn btn-success w-100 fw-bold shadow-sm" onclick="confirmWithdrawBatch()" disabled>
                                    <i class="fas fa-check-circle me-1"></i> ยืนยันเบิกทั้งหมด (<span id="qCount">0</span>)
                                </button>
                            </div>

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <div class="card card-custom <?php echo $is_closed ? 'closed-job' : ''; ?>">
        <div class="card-header-custom d-flex justify-content-between align-items-center">
            <h5 class="m-0 fw-bold text-secondary"><i class="fas fa-list-ul me-2"></i>รายการสินค้าที่เบิกแล้ว</h5>
            
            <div class="d-flex gap-2">
                <?php if(!$is_closed): ?>
                <button class="btn btn-danger btn-sm rounded-pill px-3 fw-bold" id="btnToggleReturn" onclick="toggleReturnMode()">
                    <i class="fas fa-undo me-1"></i> โหมดคืนสินค้า
                </button>
                
                <button class="btn btn-danger btn-sm rounded-pill px-3 fw-bold" id="btnReturnSelected" onclick="returnSelected()" style="display:none;">
                    <i class="fas fa-check-circle me-1"></i> ยืนยันคืน (<span id="countSelected">0</span>)
                </button>
                <?php endif; ?>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <?php if(!$is_closed): ?>
                            <th class="text-center select-col" width="50">
                                <input type="checkbox" class="form-check-input big-checkbox" id="checkAll" onclick="toggleAllChecks()">
                            </th>
                            <?php endif; ?>

                            <th class="ps-4 text-center" width="50">No.</th>
                            <th class="<?php echo $is_closed ? 'ps-4' : ''; ?>">ชื่อสินค้า</th>
                            <th>Serial Number</th>
                            <th class="text-center">วันที่เบิก</th>
                            <th class="text-center" width="100">สถานะ</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $sql_items = "SELECT s.*, p.name FROM product_serials s JOIN products p ON s.product_barcode = p.barcode WHERE s.project_id = $pid ORDER BY s.id DESC";
                        $items = $conn->query($sql_items);
                        
                        $i = 0; 

                        if($items->num_rows > 0):
                            while($item = $items->fetch_assoc()):
                                $i++; 
                        ?>
                        <tr>
                            <?php if(!$is_closed): ?>
                            <td class="text-center select-col">
                                <input type="checkbox" class="form-check-input big-checkbox item-check" value="<?php echo $item['serial_number']; ?>" onclick="updateSelectCount()">
                            </td>
                            <?php endif; ?>

                            <td class="ps-4 text-center text-muted"><?php echo $i; ?></td>
                            <td class="<?php echo $is_closed ? 'ps-4 fw-bold' : 'fw-bold'; ?>"><?php echo htmlspecialchars($item['name']); ?></td>
                            <td><span class="badge bg-light text-dark border px-3 py-2 fs-6"><?php echo $item['serial_number']; ?></span></td>
                            <td class="text-center text-muted small"><?php echo date('d/m/Y H:i', strtotime($item['date_added'])); ?></td>
                            <td class="text-center">
                                <?php if($is_closed): ?>
                                <span class="text-muted small"><i class="fas fa-lock"></i> ล็อก</span>
                                <?php else: ?>
                                <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill">ใช้งาน</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endwhile; else: ?>
                        <tr><td colspan="6" class="text-center py-5 text-muted">ยังไม่มีสินค้าในโปรเจกต์นี้</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>

<div class="modal fade" id="browseModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title fw-bold">เลือกสินค้าจากคลัง</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body bg-light">
                <div class="d-flex gap-2 mb-3">
                    <input type="text" id="filterInput" class="form-control" placeholder="พิมพ์ค้นหาชื่อสินค้า...">
                    <button class="btn btn-success" onclick="$('#browseModal').modal('hide')">เสร็จสิ้น</button>
                </div>
                
                <div class="accordion" id="stockAccordion">
                    <?php
                    $av_items = $conn->query("SELECT s.*, p.name FROM product_serials s JOIN products p ON s.product_barcode = p.barcode WHERE s.status = 'available' ORDER BY p.name ASC");
                    $groups = [];
                    while($row = $av_items->fetch_assoc()) $groups[$row['name']][] = $row;
                    
                    if(count($groups) > 0): $i=0; foreach($groups as $name => $list): $i++;
                    ?>
                    <div class="accordion-item search-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#c<?php echo $i; ?>">
                                <span class="prod-name fw-bold"><?php echo htmlspecialchars($name); ?></span>
                                <span class="badge bg-success ms-auto"><?php echo count($list); ?></span>
                            </button>
                        </h2>
                        <div id="c<?php echo $i; ?>" class="accordion-collapse collapse" data-bs-parent="#stockAccordion">
                            <div class="accordion-body p-0">
                                <table class="table table-sm mb-0">
                                    <?php foreach($list as $it): ?>
                                    <tr>
                                        <td class="ps-4"><?php echo $it['serial_number']; ?></td>
                                        <td class="text-end">
                                            <button class="btn btn-sm btn-primary rounded-pill btn-select-sn" 
                                                    data-sn="<?php echo $it['serial_number']; ?>" 
                                                    onclick="selectFromModal('<?php echo $it['serial_number']; ?>', this)">
                                                เลือก
                                            </button>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </table>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; else: ?>
                    <div class="text-center py-5">ไม่มีสินค้าว่าง</div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    let pid = <?php echo $pid; ?>;
    let pendingList = []; 

    // --- ส่วนจัดการ Checkbox คืนของ ---
    let returnMode = false;
    function toggleReturnMode() {
        returnMode = !returnMode;
        if(returnMode) {
            $('.select-col').fadeIn(); 
            $('#btnToggleReturn').removeClass('btn-danger').addClass('btn-secondary').html('<i class="fas fa-times me-1"></i> ยกเลิก');
        } else {
            $('.select-col').hide(); 
            $('#btnToggleReturn').removeClass('btn-secondary').addClass('btn-danger').html('<i class="fas fa-undo me-1"></i> โหมดคืนสินค้า');
            $('.item-check').prop('checked', false);
            $('#checkAll').prop('checked', false);
            updateSelectCount();
        }
    }

    function toggleAllChecks() {
        let isChecked = $('#checkAll').prop('checked');
        $('.item-check').prop('checked', isChecked);
        updateSelectCount();
    }

    function updateSelectCount() {
        let count = $('.item-check:checked').length;
        if(count > 0) {
            $('#btnReturnSelected').fadeIn().css('display', 'inline-block');
            $('#countSelected').text(count);
        } else {
            $('#btnReturnSelected').fadeOut();
        }
        let all = $('.item-check').length;
        $('#checkAll').prop('checked', count == all && all > 0);
    }

    function returnSelected() {
        let selectedSNs = [];
        $('.item-check:checked').each(function() { selectedSNs.push($(this).val()); });
        if(selectedSNs.length == 0) return;

        Swal.fire({
            title: 'คืนสินค้า ' + selectedSNs.length + ' รายการ?',
            html: '<input id="swal_return_note" class="swal2-input" placeholder="ระบุหมายเหตุ (ถ้ามี) เช่น คืนยกชุด, ของเหลือ">',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            confirmButtonText: 'ยืนยันคืนเข้าคลัง',
            cancelButtonText: 'ยกเลิก',
            preConfirm: () => { return document.getElementById('swal_return_note').value; }
        }).then((result) => {
            if (result.isConfirmed) {
                let note = result.value; 
                fetch('api_return_batch.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ items: selectedSNs, note: note }) 
                })
                .then(response => response.json())
                .then(data => {
                    if(data.status == 'success') { Swal.fire('สำเร็จ', data.msg, 'success').then(() => location.reload()); } 
                    else if(data.status == 'partial_error') { Swal.fire('มีข้อผิดพลาดบางรายการ', data.errors.join('<br>'), 'warning').then(() => location.reload()); } 
                    else { Swal.fire('Error', data.msg, 'error'); }
                });
            }
        });
    }

    // --- ส่วนการเบิกของ (รอเบิก) ---
    $('#filterInput').on('keyup', function() {
        let val = $(this).val().toLowerCase();
        $('.search-item').filter(function() {
            $(this).toggle($(this).text().toLowerCase().indexOf(val) > -1)
        });
    });

    $('#browseModal').on('show.bs.modal', function () {
        $('.btn-select-sn').each(function() {
            let sn = $(this).data('sn');
            let exists = pendingList.some(item => item.sn == sn);
            if(exists) {
                $(this).prop('disabled', true).removeClass('btn-primary').addClass('btn-success').html('<i class="fas fa-check"></i> เลือกแล้ว');
            } else {
                $(this).prop('disabled', false).addClass('btn-primary').removeClass('btn-success').html('เลือก');
            }
        });
    });

    $('#scanInput').keypress(function(e){ 
        if(e.which == 13) addToQueue(); 
    });

    function selectFromModal(sn, btn) {
        $(btn).prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i>');
        $.post("api_move_item.php", { sn: sn, action: 'check_status' }, function(res){
            try {
                let data = JSON.parse(res);
                if(data.status == 'available') {
                    pendingList.push({ sn: sn, product_name: data.product_name });
                    renderQueue();
                    $(btn).removeClass('btn-primary').addClass('btn-success').html('<i class="fas fa-check"></i> เลือกแล้ว');
                } else {
                    Swal.fire('เพิ่มไม่ได้', data.msg, 'error');
                    $(btn).prop('disabled', false).html('เลือก');
                }
            } catch(e) { console.log(res); $(btn).prop('disabled', false).html('เลือก'); }
        });
    }

    function addToQueue() {
        let sn = $('#scanInput').val().trim();
        if(sn == "") return;

        let exists = pendingList.some(item => item.sn === sn);
        if(exists) {
            const Toast = Swal.mixin({toast: true, position: 'top-end', showConfirmButton: false, timer: 1500});
            Toast.fire({icon: 'warning', title: 'S/N นี้อยู่ในรายการรอแล้ว'});
            $('#scanInput').val("");
            return;
        }

        $('#scanInput').prop('disabled', true);
        $.post("api_move_item.php", { sn: sn, action: 'check_status' }, function(res){
            $('#scanInput').prop('disabled', false).focus(); 
            try {
                let data = JSON.parse(res);
                if(data.status == 'available') {
                    pendingList.push({ sn: sn, product_name: data.product_name });
                    renderQueue();
                    $('#scanInput').val("");
                } else {
                    Swal.fire({ icon: 'error', title: 'เพิ่มไม่ได้', text: data.msg, timer: 2000, showConfirmButton: false });
                    $('#scanInput').select();
                }
            } catch(e) { Swal.fire('Error', 'เกิดข้อผิดพลาด', 'error'); }
        });
    }

    function renderQueue() {
        let html = '';
        if(pendingList.length > 0) {
            $('#emptyMsg').hide();
            $('#btnConfirm').prop('disabled', false);
            pendingList.forEach((item, index) => {
                html += `<tr><td class="ps-3 fw-bold text-primary">${item.sn}</td><td><small>${item.product_name}</small></td><td class="text-center"><button class="btn btn-sm text-danger" onclick="removeFromQueue(${index})"><i class="fas fa-times"></i></button></td></tr>`;
            });
        } else {
            $('#emptyMsg').show();
            $('#btnConfirm').prop('disabled', true);
        }
        $('#queueBody').html(html);
        $('#qCount').text(pendingList.length);
    }

    function removeFromQueue(index) {
        pendingList.splice(index, 1);
        renderQueue();
        $('#scanInput').focus();
    }

    // [✨] ฟังก์ชันยืนยันเบิก (ส่งชื่อผู้เบิกไปด้วย)
    function confirmWithdrawBatch() {
        if(pendingList.length == 0) return;

        let withdrawer = $('#withdrawer_name').val();
        if(!withdrawer) {
            Swal.fire('แจ้งเตือน', 'กรุณาระบุ "ผู้เบิกสินค้า" ก่อนกดยืนยันครับ!', 'warning');
            return;
        }

        Swal.fire({
            title: 'ยืนยันการเบิกสินค้า',
            text: `คุณกำลังเบิกของให้ "${withdrawer}" จำนวน ${pendingList.length} รายการ ใช่หรือไม่?`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'ยืนยันเบิก',
            cancelButtonText: 'ยกเลิก',
            confirmButtonColor: '#10b981'
        }).then((result) => {
            if (result.isConfirmed) {
                $('#btnConfirm').prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> กำลังบันทึก...');

                const itemsToSend = pendingList.map(item => ({ sn: item.sn }));

                fetch('api_move_batch.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ project_id: pid, items: itemsToSend, withdrawer: withdrawer })
                })
                .then(response => response.json())
                .then(data => {
                    if(data.status == 'success') {
                        Swal.fire({ icon: 'success', title: 'เบิกสำเร็จ!', text: data.msg, timer: 1500, showConfirmButton: false }).then(() => location.reload());
                    } else if (data.status == 'partial_error') {
                        Swal.fire({ icon: 'warning', title: 'บันทึกแล้ว (แต่มีข้อผิดพลาด)', html: data.errors.join('<br>') }).then(() => location.reload());
                    } else {
                        Swal.fire('Error', data.msg, 'error');
                        $('#btnConfirm').prop('disabled', false).html('<i class="fas fa-check-circle me-1"></i> ยืนยันเบิกทั้งหมด');
                    }
                })
                .catch(err => { 
                    Swal.fire('Error', 'เกิดข้อผิดพลาดในการเชื่อมต่อ', 'error'); 
                    $('#btnConfirm').prop('disabled', false).html('<i class="fas fa-check-circle me-1"></i> ยืนยันเบิกทั้งหมด'); 
                });
            }
        });
    }
    
    // --- ส่วนแก้ไข/ปิดโปรเจกต์ ---
    async function editProjectInfo() {
        const { value: formValues } = await Swal.fire({
            title: 'แก้ไขข้อมูลโครงงาน',
            html: `
                <div class="text-start mb-2"><label class="fw-bold">รหัสโครงการ</label></div>
                <input id="swal-input1" class="swal2-input mb-3 w-100 m-0" placeholder="เช่น JOB-2024-A01" value="<?php echo htmlspecialchars($proj['project_code']); ?>">
                <div class="text-start mb-2"><label class="fw-bold">ชื่อโครงการ</label></div>
                <input id="swal-input2" class="swal2-input w-100 m-0" placeholder="ชื่อโครงการ" value="<?php echo htmlspecialchars($proj['project_name']); ?>">
            `,
            focusConfirm: false,
            showCancelButton: true,
            confirmButtonText: 'บันทึก',
            cancelButtonText: 'ยกเลิก',
            preConfirm: () => {
                return [
                    document.getElementById('swal-input1').value,
                    document.getElementById('swal-input2').value
                ]
            }
        });

        if (formValues) {
            let newCode = formValues[0];
            let newName = formValues[1];
            if(!newCode || !newName) { Swal.fire('แจ้งเตือน', 'กรุณากรอกข้อมูลให้ครบ', 'warning'); return; }
            
            $.post("api_project.php", { action: 'edit_info', id: pid, code: newCode, name: newName }, function(res) {
                if(res.trim() === 'success') {
                    Swal.fire('สำเร็จ', 'บันทึกข้อมูลเรียบร้อย', 'success').then(() => { location.reload(); });
                } else {
                    Swal.fire('Error', 'เกิดข้อผิดพลาด: ' + res, 'error');
                }
            });
        }
    }

    function closeJob() {
        Swal.fire({
            title: 'ยืนยันปิดจ็อบงานนี้?',
            text: "เมื่อปิดแล้วจะไม่สามารถเบิกของเพิ่ม หรือแก้ไขได้อีก",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            confirmButtonText: 'ยืนยัน ปิดงาน!'
        }).then((result) => {
            if (result.isConfirmed) {
                $.post("api_project.php", { action: 'close_job', id: pid }, function() { location.reload(); });
            }
        })
    }

    document.addEventListener('keydown', function(event) {
        if (event.key === "Escape") {
            let isModalOpen = document.body.classList.contains('modal-open');
            let isSwalOpen = document.body.classList.contains('swal2-shown');
            if (!isModalOpen && !isSwalOpen) {
                window.location.href = 'projects.php';
            }
        }
    });
</script>
</body>
</html>