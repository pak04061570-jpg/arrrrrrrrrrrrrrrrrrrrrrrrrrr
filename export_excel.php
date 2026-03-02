<?php
include 'db_connect.php';

// รับค่า ID โปรเจกต์
$pid = $_GET['id'];

// ดึงข้อมูลโปรเจกต์
$proj = $conn->query("SELECT * FROM projects WHERE id = $pid")->fetch_assoc();
if(!$proj) die("ไม่พบข้อมูล");

// ตั้งชื่อไฟล์ที่จะดาวน์โหลด
$filename = "Job_Export_" . $proj['project_name'] . "_" . date('Ymd') . ".xls";
$doc_number = "JOB-" . str_pad($proj['id'], 4, '0', STR_PAD_LEFT);

// สั่งให้ Browser รู้ว่าเป็นไฟล์ Excel
header("Content-Type: application/vnd.ms-excel");
header("Content-Disposition: attachment; filename=\"$filename\"");
header("Pragma: no-cache");
header("Expires: 0");

// ดึงรายการสินค้า
$sql = "SELECT s.serial_number, p.name, p.barcode, u.name AS unit, p.price_sell, s.date_added 
        FROM product_serials s 
        JOIN products p ON s.product_barcode = p.barcode 
        LEFT JOIN units u ON p.unit_id = u.id
        WHERE s.project_id = $pid 
        ORDER BY s.id DESC";
$result = $conn->query($sql);
?>

<html xmlns:o="urn:schemas-microsoft-com:office:office"
xmlns:x="urn:schemas-microsoft-com:office:excel"
xmlns="http://www.w3.org/TR/REC-html40">

<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<style>
    /* จัดสไตล์ตารางใน Excel */
    table { border-collapse: collapse; width: 100%; }
    th, td { border: 1px solid #000; padding: 5px; }
    th { background-color: #f0f0f0; text-align: center; }
    .text-center { text-align: center; }
</style>
</head>
<body>
    
    <table border="0" style="width: 100%;">
        <tr>
            <td colspan="6" style="font-size:20px; font-weight:bold; text-align:center;">ใบรายการเบิกสินค้า / Job Sheet</td>
        </tr>
        <tr>
            <td colspan="6" style="text-align:center;">บริษัท ซี.เอ็ม.เอส. คอนโทรล ซิสเต็ม จำกัด</td>
        </tr>
        <tr><td colspan="6">&nbsp;</td></tr>
        <tr>
            <td colspan="4"><strong>ชื่อโครงการ:</strong> <?php echo $proj['project_name']; ?></td>
            <td colspan="2"><strong>เลขที่เอกสาร:</strong> <?php echo $doc_number; ?></td>
        </tr>
        <tr>
            <td colspan="4"><strong>รหัสโครงการ:</strong> <?php echo $proj['project_code']; ?></td>
            <td colspan="2"><strong>วันที่สร้าง:</strong> <?php echo date('d/m/Y', strtotime($proj['created_at'])); ?></td>
        </tr>
        <tr>
            <td colspan="6"><strong>สถานะโครงการ:</strong> <?php echo $proj['status']; ?></td>
        </tr>
    </table>
    <br>

    <table border="1" cellspacing="0" cellpadding="5">
        <thead>
            <tr style="background-color: #eee;">
                <th style="width: 50px;">ลำดับ</th>
                <th style="width: 120px;">รหัสสินค้า (SKU)</th>
                <th style="width: 250px;">ชื่อสินค้า</th>
                <th style="width: 150px;">Serial Number</th>
                <th style="width: 80px;">หน่วย</th>
                <th style="width: 100px;">วันที่เบิก</th>
            </tr>
        </thead>
        <tbody>
            <?php 
            $i = 0;
            while($row = $result->fetch_assoc()): 
                $i++;
            ?>
            <tr>
                <td align="center"><?php echo $i; ?></td>
                <td align="center" style="mso-number-format:'\@'"><?php echo $row['barcode']; ?></td> 
                <td><?php echo $row['name']; ?></td>
                <td align="center" style="mso-number-format:'\@'"><?php echo $row['serial_number']; ?></td>
                <td align="center"><?php echo $row['unit']; ?></td>
                <td align="center"><?php echo date('d/m/Y H:i', strtotime($row['date_added'])); ?></td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
    <br><br>

    <table border="0" style="width: 100%; text-align: center; margin-top: 40px;">
        <tr>
            <td style="width: 33%; padding-top: 30px;"><br>_______________________<br><br>( ผู้อนุมัติ )<br><br>วันที่ ____/____/____</td>
            <td style="width: 33%; padding-top: 30px;"><br>_______________________<br><br>( ผู้เบิกสินค้า )<br><br>วันที่ ____/____/____</td>
            <td style="width: 33%; padding-top: 30px;"><br>_______________________<br><br>( ผู้จ่ายสินค้า )<br><br>วันที่ ____/____/____</td>
        </tr>
    </table>

</body>
</html>