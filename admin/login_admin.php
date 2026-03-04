<?php
session_start();
include("../config/dbcon.php");

// Định nghĩa hàm redirect nếu chưa có
if(!function_exists('redirect')) {
    function redirect($url, $message = '') {
        if(!empty($message)) {
            $_SESSION['message'] = $message;
        }
        header("Location: " . $url);
        exit();
    }
}

if(isset($_POST['login_btn'])) {
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = mysqli_real_escape_string($conn, $_POST['password']);
    
    $login_query = "SELECT u.*, r.name as role_name, r.permissions 
                    FROM users u 
                    LEFT JOIN roles r ON u.role_id = r.id 
                    WHERE u.email = '$email' AND u.password = '$password' AND u.is_active = 1";
    
    $login_query_run = mysqli_query($conn, $login_query);
    
    if(mysqli_num_rows($login_query_run) > 0) {
        $userdata = mysqli_fetch_array($login_query_run);
        
        // Kiểm tra quyền admin
        if($userdata['role'] == 'admin' || $userdata['role_name'] == 'admin') {
            $_SESSION['auth_admin'] = true;
            $_SESSION['auth_admin']['user_id'] = $userdata['id'];
            $_SESSION['auth_admin']['name'] = $userdata['name'];
            $_SESSION['auth_admin']['email'] = $userdata['email'];
            $_SESSION['auth_admin']['role'] = $userdata['role'];
            $_SESSION['auth_admin']['role_id'] = $userdata['role_id'];
            $_SESSION['auth_admin']['permissions'] = json_decode($userdata['permissions'], true);
            
            // Cập nhật thông tin đăng nhập
            $update_login = "UPDATE users SET last_login = NOW(), login_count = login_count + 1 WHERE id = '".$userdata['id']."'";
            mysqli_query($conn, $update_login);
            
            redirect("dashboard.php");
        } else {
            redirect("login_admin.php");
        }
    } else {
        redirect("login_admin.php");
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng nhập Admin - Fashion Shop</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #1a1a2e 0%, #16213e 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .login-container {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            padding: 3rem;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            border: 1px solid rgba(255, 255, 255, 0.1);
            width: 100%;
            max-width: 450px;
        }
        
        .login-header {
            text-align: center;
            margin-bottom: 2rem;
        }
        
        .login-logo {
            color: white;
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 1rem;
        }
        
        .login-subtitle {
            color: rgba(255, 255, 255, 0.8);
            font-size: 1rem;
        }
        
        .form-control {
            background: rgba(255, 255, 255, 0.1);
            border: 2px solid rgba(255, 255, 255, 0.2);
            color: white;
            border-radius: 10px;
            padding: 0.75rem 1rem;
            transition: all 0.3s ease;
        }
        
        .form-control:focus {
            background: rgba(255, 255, 255, 0.15);
            border-color: #e94560;
            box-shadow: 0 0 0 0.2rem rgba(233, 69, 96, 0.25);
            color: white;
        }
        
        .form-control::placeholder {
            color: rgba(255, 255, 255, 0.6);
        }
        
        .input-group-text {
            background: rgba(255, 255, 255, 0.1);
            border: 2px solid rgba(255, 255, 255, 0.2);
            border-right: none;
            color: rgba(255, 255, 255, 0.8);
        }
        
        .btn-login {
            background: linear-gradient(135deg, #e94560 0%, #d63384 100%);
            border: none;
            color: white;
            padding: 0.75rem 2rem;
            border-radius: 10px;
            font-weight: 600;
            transition: all 0.3s ease;
            width: 100%;
        }
        
        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(233, 69, 96, 0.3);
            color: white;
        }
        
        .alert {
            border-radius: 10px;
            border: none;
            background: rgba(220, 53, 69, 0.1);
            color: #ff6b7a;
            border-left: 4px solid #dc3545;
        }
        
        .login-footer {
            text-align: center;
            margin-top: 2rem;
        }
        
        .login-footer a {
            color: rgba(255, 255, 255, 0.8);
            text-decoration: none;
            transition: color 0.3s ease;
        }
        
        .login-footer a:hover {
            color: #e94560;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-header">
            <div class="login-logo">
                <i class="fas fa-crown me-2"></i>
                Admin Login
            </div>
            <div class="login-subtitle">
                Đăng nhập với tài khoản quản trị viên
            </div>
        </div>
        
        <?php if(isset($_SESSION['message'])): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <?= $_SESSION['message']; unset($_SESSION['message']); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
        
        <form action="" method="POST">
            <div class="mb-3">
                <div class="input-group">
                    <span class="input-group-text">
                        <i class="fas fa-envelope"></i>
                    </span>
                    <input type="email" class="form-control" name="email" placeholder="Email" required>
                </div>
            </div>
            
            <div class="mb-4">
                <div class="input-group">
                    <span class="input-group-text">
                        <i class="fas fa-lock"></i>
                    </span>
                    <input type="password" class="form-control" name="password" placeholder="Mật khẩu" required>
                </div>
            </div>
            
            <button type="submit" name="login_btn" class="btn btn-login">
                <i class="fas fa-sign-in-alt me-2"></i>
                Đăng nhập
            </button>
        </form>
        
        <div class="login-footer">
            <a href="../login.php">← Quay lại trang chủ</a>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
