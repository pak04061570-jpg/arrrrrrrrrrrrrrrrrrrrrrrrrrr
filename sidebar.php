<?php
// [✨] ป้องกันหน้าเว็บ! ถ้ายังไม่ล็อกอิน ให้เด้งไปหน้า login.php
if(!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}
$page = basename($_SERVER['PHP_SELF']);
?>
<div class="sidebar d-flex flex-column justify-content-between">
    <div>
        <h4 class="text-white text-center fw-bold mb-5 mt-2"><i class="fas fa-cubes me-2"></i>Stock</h4>
        <nav class="nav flex-column">
            <a class="nav-link <?php echo ($page == 'index.php') ? 'active' : ''; ?>" href="index.php">
                <i class="fas fa-warehouse"></i> รับสินค้าเข้า
            </a>
            <a class="nav-link <?php echo ($page == 'projects.php' || $page == 'project_manage.php' || $page == 'print_job.php') ? 'active' : ''; ?>" href="projects.php">
                <i class="fas fa-hard-hat"></i> รายการโครงการ
            </a>
            <a class="nav-link <?php echo ($page == 'products.php' || $page == 'product_details.php') ? 'active' : ''; ?>" href="products.php">
                <i class="fas fa-boxes"></i> ภาพรวมคลังสินค้า
            </a>
            <a class="nav-link <?php echo ($page == 'history_log.php' || $page == 'history_view.php') ? 'active' : ''; ?>" href="history_log.php">
                <i class="fas fa-history"></i> ประวัติรับเข้า-เบิกออก
            </a>
            
            <?php if(isset($_SESSION['role']) && $_SESSION['role'] == 'admin'): ?>
            <a class="nav-link <?php echo ($page == 'settings.php') ? 'active' : ''; ?>" href="settings.php" style="margin-top: 20px; border-top: 1px solid rgba(255,255,255,0.1); border-radius: 0;">
                <i class="fas fa-cogs text-warning"></i> ตั้งค่าข้อมูลพื้นฐาน
            </a>
            <?php endif; ?>
        </nav>
    </div>
    
    <div class="p-3 mt-auto border-top border-secondary">
        <div class="d-flex align-items-center text-white mb-3">
            <i class="fas fa-user-circle fs-2 me-2"></i>
            <div>
                <div class="fw-bold" style="font-size: 0.9rem;"><?php echo $_SESSION['name']; ?></div>
                <div class="text-muted" style="font-size: 0.8rem; text-transform: uppercase;"><?php echo $_SESSION['role']; ?></div>
            </div>
        </div>
        <a href="logout.php" class="btn btn-outline-light btn-sm w-100"><i class="fas fa-sign-out-alt me-1"></i> ออกจากระบบ</a>
    </div>
</div>