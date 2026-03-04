<?php
include 'db_connect.php';
if(isset($_SESSION['user_id'])) { header("Location: index.php"); exit; }

$error = '';
if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $conn->real_escape_string($_POST['username']);
    $password = md5($_POST['password']); // เข้ารหัสผ่านก่อนไปเทียบใน DB

    $result = $conn->query("SELECT * FROM users WHERE username='$username' AND password='$password'");
    if($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['name'] = $user['name'];
        $_SESSION['role'] = $user['role'];
        header("Location: index.php");
        exit;
    } else {
        $error = 'ชื่อผู้ใช้หรือรหัสผ่านไม่ถูกต้อง!';
    }
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>เข้าสู่ระบบ - Stock System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Sarabun:wght@300;400;700&display=swap" rel="stylesheet">
    <style> body { font-family: 'Sarabun', sans-serif; background-color: #f1f5f9; display: flex; align-items: center; justify-content: center; height: 100vh; } .login-box { background: #fff; padding: 40px; border-radius: 16px; box-shadow: 0 10px 25px rgba(0,0,0,0.1); width: 100%; max-width: 400px; } </style>
</head>
<body>
    <div class="login-box text-center">
        <h3 class="fw-bold text-primary mb-4">📦 Stock System</h3>
        <?php if($error): ?><div class="alert alert-danger py-2"><?php echo $error; ?></div><?php endif; ?>
        <form method="POST">
            <input type="text" name="username" class="form-control mb-3" placeholder="ชื่อผู้ใช้ (Username)" required>
            <input type="password" name="password" class="form-control mb-4" placeholder="รหัสผ่าน (Password)" required>
            <button type="submit" class="btn btn-primary w-100 fw-bold">เข้าสู่ระบบ</button>
        </form>
    </div>
</body>
</html>