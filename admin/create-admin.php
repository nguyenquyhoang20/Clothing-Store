<?php
session_start();
include(__DIR__ . "/../config/dbcon.php");
include(__DIR__ . "/../functions/myfunctions.php");

$success_message = "";
$error_message = "";

if(isset($_POST['create_employee_btn'])) {
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $phone = mysqli_real_escape_string($conn, $_POST['phone']);
    $address = mysqli_real_escape_string($conn, $_POST['address'] ?? '');
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    
    // Validation
    if(empty($name) || empty($email) || empty($phone) || empty($password)) {
        $error_message = "Vui lòng điền đầy đủ thông tin!";
    } elseif($password !== $confirm_password) {
        $error_message = "Mật khẩu xác nhận không khớp!";
    } elseif(strlen($password) < 6) {
        $error_message = "Mật khẩu phải có ít nhất 6 ký tự!";
    } else {
        // Check if email already exists in users
        $check_users = "SELECT * FROM users WHERE email='$email'";
        $result_users = mysqli_query($conn, $check_users);
        
        if(mysqli_num_rows($result_users) > 0) {
            $error_message = "Email đã tồn tại trong hệ thống!";
        } else {
            // Create employee registration request (pending approval)
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            
            // Tạo user với role_as = 0 (nhân viên) và is_active = 0 (pending - chờ duyệt)
            $insert_query = "INSERT INTO users (name, email, phone, address, password, role_as, is_active) 
                           VALUES ('$name', '$email', '$phone', '$address', '$hashed_password', 0, 0)";
            
            if(mysqli_query($conn, $insert_query)) {
                $success_message = "Đăng ký tài khoản nhân viên thành công! Yêu cầu của bạn đang chờ admin duyệt. Bạn sẽ nhận được thông báo khi được duyệt.";
            } else {
                $error_message = "Có lỗi xảy ra khi đăng ký: " . mysqli_error($conn);
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tạo tài khoản Admin - Fashion Shop</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            padding: 20px 0;
        }
        
        .create-container {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
            overflow: hidden;
            max-width: 800px;
            width: 100%;
            margin: 20px;
        }
        
        .create-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 40px;
            text-align: center;
        }
        
        .create-body {
            padding: 40px;
        }
        
        .form-control {
            border: 2px solid #e9ecef;
            border-radius: 10px;
            padding: 15px 20px;
            font-size: 16px;
            transition: all 0.3s ease;
        }
        
        .form-control:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
        }
        
        .input-group-text {
            background: #f8f9fa;
            border: 2px solid #e9ecef;
            border-right: none;
            border-radius: 10px 0 0 10px;
        }
        
        .input-group .form-control {
            border-left: none;
            border-radius: 0 10px 10px 0;
        }
        
        .btn-create {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            border-radius: 10px;
            padding: 15px;
            font-size: 16px;
            font-weight: 600;
            color: white;
            width: 100%;
            transition: all 0.3s ease;
        }
        
        .btn-create:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(102, 126, 234, 0.3);
            color: white;
        }
        
        .btn-back {
            background: #6c757d;
            border: none;
            border-radius: 10px;
            padding: 15px;
            font-size: 16px;
            font-weight: 600;
            color: white;
            width: 100%;
            transition: all 0.3s ease;
        }
        
        .btn-back:hover {
            background: #5a6268;
            color: white;
        }
        
        .alert {
            border-radius: 10px;
            border: none;
        }
        
        .password-strength {
            margin-top: 5px;
            font-size: 14px;
        }
        
        .strength-weak { color: #dc3545; }
        .strength-medium { color: #ffc107; }
        .strength-strong { color: #28a745; }
    </style>
</head>
<body>
    <div class="create-container">
        <div class="create-header">
            <i class="fas fa-user-tie fa-3x mb-3"></i>
            <h2>Đăng ký tài khoản Nhân viên</h2>
            <p class="mb-0">Đăng ký tài khoản nhân viên (cần duyệt từ admin)</p>
        </div>
        
        <div class="create-body">
            <?php if($success_message): ?>
                <div class="alert alert-success" role="alert">
                    <i class="fas fa-check-circle me-2"></i>
                    <?= $success_message ?>
                    <div class="mt-3">
                        <a href="login.php" class="btn btn-success">
                            <i class="fas fa-sign-in-alt me-2"></i>
                            Đăng nhập ngay
                        </a>
                    </div>
                </div>
            <?php endif; ?>
            
            <?php if($error_message): ?>
                <div class="alert alert-danger" role="alert">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    <?= $error_message ?>
                </div>
            <?php endif; ?>
            
            <form method="POST">
                <div class="row">
                    <div class="col-md-6 mb-4">
                        <label for="name" class="form-label">Họ và tên</label>
                        <div class="input-group">
                            <span class="input-group-text">
                                <i class="fas fa-user"></i>
                            </span>
                            <input type="text" class="form-control" id="name" name="name" 
                                   placeholder="Nhập họ và tên" required>
                        </div>
                    </div>
                    
                    <div class="col-md-6 mb-4">
                        <label for="email" class="form-label">Email</label>
                        <div class="input-group">
                            <span class="input-group-text">
                                <i class="fas fa-envelope"></i>
                            </span>
                            <input type="email" class="form-control" id="email" name="email" 
                                   placeholder="admin@example.com" required>
                        </div>
                    </div>
                </div>
                
                <div class="mb-4">
                    <label for="phone" class="form-label">Số điện thoại</label>
                    <div class="input-group">
                        <span class="input-group-text">
                            <i class="fas fa-phone"></i>
                        </span>
                        <input type="tel" class="form-control" id="phone" name="phone" 
                               placeholder="0123456789" required>
                    </div>
                </div>
                
                <div class="mb-4">
                    <label for="address" class="form-label">Địa chỉ</label>
                    <div class="input-group">
                        <span class="input-group-text">
                            <i class="fas fa-map-marker-alt"></i>
                        </span>
                        <input type="text" class="form-control" id="address" name="address" 
                               placeholder="Nhập địa chỉ của bạn">
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-6 mb-4">
                        <label for="password" class="form-label">Mật khẩu</label>
                        <div class="input-group">
                            <span class="input-group-text">
                                <i class="fas fa-lock"></i>
                            </span>
                            <input type="password" class="form-control" id="password" name="password" 
                                   placeholder="Tối thiểu 6 ký tự" required>
                        </div>
                        <div class="password-strength" id="password-strength"></div>
                    </div>
                    
                    <div class="col-md-6 mb-4">
                        <label for="confirm_password" class="form-label">Xác nhận mật khẩu</label>
                        <div class="input-group">
                            <span class="input-group-text">
                                <i class="fas fa-lock"></i>
                            </span>
                            <input type="password" class="form-control" id="confirm_password" name="confirm_password" 
                                   placeholder="Nhập lại mật khẩu" required>
                        </div>
                        <div class="password-strength" id="password-match"></div>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <button type="submit" name="create_employee_btn" class="btn btn-create">
                            <i class="fas fa-user-tie me-2"></i>
                            Đăng ký tài khoản Nhân viên
                        </button>
                    </div>
                    <div class="col-md-6 mb-3">
                        <a href="login.php" class="btn btn-back">
                            <i class="fas fa-arrow-left me-2"></i>
                            Quay lại đăng nhập
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Password strength checker
        document.getElementById('password').addEventListener('input', function() {
            const password = this.value;
            const strengthDiv = document.getElementById('password-strength');
            
            if (password.length === 0) {
                strengthDiv.innerHTML = '';
                return;
            }
            
            let strength = 0;
            let strengthText = '';
            let strengthClass = '';
            
            if (password.length >= 6) strength++;
            if (password.match(/[a-z]/)) strength++;
            if (password.match(/[A-Z]/)) strength++;
            if (password.match(/[0-9]/)) strength++;
            if (password.match(/[^a-zA-Z0-9]/)) strength++;
            
            if (strength < 3) {
                strengthText = 'Mật khẩu yếu';
                strengthClass = 'strength-weak';
            } else if (strength < 5) {
                strengthText = 'Mật khẩu trung bình';
                strengthClass = 'strength-medium';
            } else {
                strengthText = 'Mật khẩu mạnh';
                strengthClass = 'strength-strong';
            }
            
            strengthDiv.innerHTML = `<span class="${strengthClass}">${strengthText}</span>`;
        });
        
        // Password match checker
        document.getElementById('confirm_password').addEventListener('input', function() {
            const password = document.getElementById('password').value;
            const confirmPassword = this.value;
            const matchDiv = document.getElementById('password-match');
            
            if (confirmPassword.length === 0) {
                matchDiv.innerHTML = '';
                return;
            }
            
            if (password === confirmPassword) {
                matchDiv.innerHTML = '<span class="strength-strong">Mật khẩu khớp</span>';
            } else {
                matchDiv.innerHTML = '<span class="strength-weak">Mật khẩu không khớp</span>';
            }
        });
    </script>
</body>
</html>
