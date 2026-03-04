<?php
session_start();
include(__DIR__ . "/../config/dbcon.php");
include(__DIR__ . "/../functions/myfunctions.php");

// Redirect if already logged in
if(isset($_SESSION['auth']) && ($_SESSION['auth_user']['role_as'] == 1 || $_SESSION['auth_user']['role_as'] == 0)) {
    header("Location: index.php");
    exit();
}

$error_message = "";

if(isset($_POST['login_btn'])) {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $role_type = isset($_POST['role_type']) ? intval($_POST['role_type']) : 1;
    
    // Rate limiting
    if (!check_rate_limit($email)) {
        $error_message = "Bạn đã đăng nhập sai quá nhiều lần. Vui lòng thử lại sau 5 phút.";
    } else {
        // Find user with prepared statement — NO auto-create
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ? AND role_as = ?");
        $stmt->execute([$email, $role_type]);
        $user = $stmt->fetch();
        
        if ($user) {
            $loginOk = false;
            
            // Try password_verify first (hashed passwords)
            if (password_verify($password, $user['password'])) {
                $loginOk = true;
            } else if ($user['password'] === $password) {
                // Legacy plaintext — auto-hash on success
                $loginOk = true;
                $new_hash = password_hash($password, PASSWORD_DEFAULT);
                $update = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
                $update->execute([$new_hash, $user['id']]);
            }

            if ($loginOk) {
                // Check if employee is approved
                if ($role_type == 0 && isset($user['is_active']) && $user['is_active'] != 1) {
                    if ($user['is_active'] == 0) {
                        $error_message = "Tài khoản của bạn đang chờ admin duyệt. Vui lòng đợi!";
                    } elseif ($user['is_active'] == 2) {
                        $error_message = "Tài khoản của bạn đã bị từ chối. Vui lòng liên hệ admin!";
                    } else {
                        $error_message = "Tài khoản của bạn đã bị khóa!";
                    }
                } else {
                    // Reset rate limit on success
                    reset_rate_limit($email);
                    
                    $_SESSION['auth'] = true;
                    $_SESSION['auth_user'] = [
                        'id' => $user['id'],
                        'name' => $user['name'],
                        'email' => $user['email'],
                        'role_as' => $user['role_as']
                    ];
                    
                    if ($role_type == 0) {
                        $_SESSION['auth_employee'] = $_SESSION['auth_user'];
                    }
                    
                    header("Location: index.php");
                    exit();
                }
            } else {
                record_attempt($email);
                $error_message = "Mật khẩu không đúng!";
            }
        } else {
            // Check if email exists with different role
            $stmt2 = $pdo->prepare("SELECT role_as FROM users WHERE email = ?");
            $stmt2->execute([$email]);
            $other = $stmt2->fetch();
            
            if ($other) {
                $role_name = $other['role_as'] == 1 ? 'Admin' : 'Nhân viên';
                $error_message = "Email này đã được đăng ký với quyền $role_name. Vui lòng chọn đúng quyền.";
            } else {
                record_attempt($email);
                $error_message = "Không tìm thấy tài khoản với email này!";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Đăng nhập Admin - Fashion Shop</title>
    <link rel="stylesheet" type="text/css" href="https://fonts.googleapis.com/css?family=Roboto:300,400,500,700,900|Roboto+Slab:400,700" />
    <script src="https://kit.fontawesome.com/42d5adcbca.js" crossorigin="anonymous"></script>
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons+Round" rel="stylesheet">
    <link id="pagestyle" href="assets/css/material-dashboard.min.css" rel="stylesheet" />
    <link id="pagestyle" href="assets/css/form.css" rel="stylesheet" />
    <style>
        .auth-wrapper { min-height: 100vh; }
        .auth-card { border-radius: 16px; box-shadow: 0 20px 40px rgba(0,0,0,0.1); }
        .auth-left { background: linear-gradient(180deg, #1f2937 0%, #0f172a 100%); }
        .auth-left .logo { width: 80px; height: 80px; background: rgba(255,255,255,0.2); border-radius: 50%; }
        .input-group-text { background: #f8f9fa; }
        .btn-check:checked + .btn-outline-primary { background-color: #0d6efd; border-color: #0d6efd; color: white; }
        .btn-check:checked + .btn-outline-danger { background-color: #dc3545; border-color: #dc3545; color: white; }
        .btn-check + .btn { transition: all 0.3s ease; }
        .btn-check + .btn:hover { transform: translateY(-2px); box-shadow: 0 4px 8px rgba(0,0,0,0.1); }
    </style>
    <link rel="stylesheet" href="//cdn.jsdelivr.net/npm/alertifyjs@1.13.1/build/css/alertify.min.css"/>
    <link rel="stylesheet" href="//cdn.jsdelivr.net/npm/alertifyjs@1.13.1/build/css/themes/bootstrap.min.css"/>
</head>
<body class="bg-gray-200">
    <div class="container auth-wrapper d-flex align-items-center">
        <div class="row w-100 justify-content-center">
            <div class="col-12 col-md-10 col-lg-8">
                <div class="card auth-card overflow-hidden">
                    <div class="row g-0">
                        <div class="col-lg-5 d-none d-lg-flex align-items-center justify-content-center text-white auth-left p-5">
                            <div class="text-center">
                                <div class="logo d-flex align-items-center justify-content-center mb-3">
                                    <i class="fas fa-crown fa-lg"></i>
                                </div>
                                <h4 class="mb-2">NHÓM 10 </h4>
                                <p class="mb-0 opacity-8">Quản lý cửa hàng thời trang chuyên nghiệp</p>
                            </div>
                        </div>
                        <div class="col-lg-7">
                            <div class="card-body p-4 p-md-5">
                                <h5 class="mb-4 text-center">Đăng nhập Hệ thống</h5>
                                <?php if($error_message): ?>
                                    <div class="alert alert-danger" role="alert">
                                        <i class="fas fa-exclamation-triangle me-2"></i>
                                        <?= e($error_message) ?>
                                    </div>
                                <?php endif; ?>
                                <form method="POST">
                                    <div class="mb-4">
                                        <label class="form-label mb-3">Chọn quyền đăng nhập:</label>
                                        <div class="row g-2">
                                            <div class="col-6">
                                                <input type="radio" class="btn-check" name="role_type" id="role_employee" value="0" checked>
                                                <label class="btn btn-outline-primary w-100 py-3" for="role_employee">
                                                    <i class="fas fa-user-tie fa-lg d-block mb-2"></i>
                                                    <strong>Nhân viên</strong>
                                                </label>
                                            </div>
                                            <div class="col-6">
                                                <input type="radio" class="btn-check" name="role_type" id="role_admin" value="1">
                                                <label class="btn btn-outline-danger w-100 py-3" for="role_admin">
                                                    <i class="fas fa-crown fa-lg d-block mb-2"></i>
                                                    <strong>Admin</strong>
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="email" class="form-label">Email</label>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                                            <input type="email" class="form-control" id="email" name="email" placeholder="email@example.com" required>
                                        </div>
                                    </div>
                                    <div class="mb-3">
                                        <label for="password" class="form-label">Mật khẩu</label>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="fas fa-lock"></i></span>
                                            <input type="password" class="form-control" id="password" name="password" placeholder="Nhập mật khẩu" required>
                                        </div>
                                    </div>
                                    <div class="d-grid">
                                        <button type="submit" name="login_btn" class="btn bg-gradient-primary">
                                            <i class="fas fa-sign-in-alt me-2"></i>
                                            Đăng nhập
                                        </button>
                                    </div>
                                </form>
                                <div class="text-center mt-3">
                                    <p class="mb-0">Chưa có tài khoản? <a class="text-primary" href="create-admin.php">Tạo tài khoản mới</a></p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="assets/js/bootstrap.bundle.min.js"></script>
</body>
</html>
