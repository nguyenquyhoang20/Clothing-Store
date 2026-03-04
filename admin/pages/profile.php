<?php 
// ============================================
// TRANG HỒ SƠ CÁ NHÂN - ADMIN/NHÂN VIÊN
// ============================================
$pageTitle = "Hồ sơ cá nhân - NHÓM 10";

// Lấy thông tin user hiện tại
$user_id = $_SESSION['auth_user']['id'];
$user_query = "SELECT * FROM users WHERE id='$user_id'";
$user_result = mysqli_query($conn, $user_query);
$user_data = mysqli_fetch_assoc($user_result);

// Kiểm tra nếu không tìm thấy user
if(!$user_data) {
    $user_data = [
        'name' => $_SESSION['auth_user']['name'] ?? '',
        'email' => $_SESSION['auth_user']['email'] ?? '',
        'phone' => $_SESSION['auth_user']['phone'] ?? '',
        'address' => $_SESSION['auth_user']['address'] ?? '',
        'role_as' => $_SESSION['auth_user']['role_as'] ?? 0,
        'password' => '',
        'created_at' => date('Y-m-d H:i:s')
    ];
}

$success_message = "";
$error_message = "";

// Xử lý cập nhật thông tin
if(isset($_POST['update_profile_btn'])) {
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $phone = mysqli_real_escape_string($conn, $_POST['phone']);
    $address = mysqli_real_escape_string($conn, $_POST['address']);
    
    if(empty($name) || empty($phone)) {
        $error_message = "Vui lòng điền đầy đủ thông tin!";
    } else {
        $update_query = "UPDATE users SET name='$name', phone='$phone', address='$address' WHERE id='$user_id'";
        if(mysqli_query($conn, $update_query)) {
            $_SESSION['auth_user']['name'] = $name;
            $user_data['name'] = $name;
            $user_data['phone'] = $phone;
            $user_data['address'] = $address;
            $success_message = "Cập nhật thông tin thành công!";
        } else {
            $error_message = "Có lỗi xảy ra: " . mysqli_error($conn);
        }
    }
}

// Xử lý đổi mật khẩu
if(isset($_POST['change_password_btn'])) {
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];
    
    if(empty($current_password) || empty($new_password) || empty($confirm_password)) {
        $error_message = "Vui lòng điền đầy đủ thông tin!";
    } elseif($new_password !== $confirm_password) {
        $error_message = "Mật khẩu mới và xác nhận không khớp!";
    } elseif(strlen($new_password) < 6) {
        $error_message = "Mật khẩu mới phải có ít nhất 6 ký tự!";
    } else {
        // Kiểm tra mật khẩu hiện tại
        $password_ok = false;
        if(isset($user_data['password'])) {
            if($user_data['password'] === $current_password) {
                $password_ok = true;
            } elseif(password_verify($current_password, $user_data['password'])) {
                $password_ok = true;
            } elseif($user_data['password'] === md5($current_password)) {
                $password_ok = true;
            }
        }
        
        if($password_ok) {
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            $update_query = "UPDATE users SET password='$hashed_password' WHERE id='$user_id'";
            if(mysqli_query($conn, $update_query)) {
                $success_message = "Đổi mật khẩu thành công!";
            } else {
                $error_message = "Có lỗi xảy ra: " . mysqli_error($conn);
            }
        } else {
            $error_message = "Mật khẩu hiện tại không đúng!";
        }
    }
}
?>

<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <div class="card shadow border-0" style="border-radius:16px;">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="mb-0"><i class="fas fa-user-circle me-2"></i>Hồ sơ cá nhân</h4>
                </div>
                <div class="card-body">
                    <?php if($success_message): ?>
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <i class="fas fa-check-circle me-2"></i>
                            <?= $success_message ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>
                    
                    <?php if($error_message): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            <?= $error_message ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>
                    
                    <div class="row">
                        <!-- Thông tin cá nhân -->
                        <div class="col-md-6">
                            <div class="card border-0 shadow-sm mb-4">
                                <div class="card-header bg-gradient-primary text-white">
                                    <h5 class="mb-0"><i class="fas fa-info-circle me-2"></i>Thông tin cá nhân</h5>
                                </div>
                                <div class="card-body">
                                    <form method="POST">
                                        <div class="mb-3">
                                            <label for="name" class="form-label">Họ và tên</label>
                                            <input type="text" class="form-control" id="name" name="name" 
                                                   value="<?= htmlspecialchars($user_data['name'] ?? '') ?>" required>
                                        </div>
                                        <div class="mb-3">
                                            <label for="email" class="form-label">Email</label>
                                            <input type="email" class="form-control" id="email" 
                                                   value="<?= htmlspecialchars($user_data['email'] ?? '') ?>" disabled>
                                            <small class="text-muted">Email không thể thay đổi</small>
                                        </div>
                                        <div class="mb-3">
                                            <label for="phone" class="form-label">Số điện thoại</label>
                                            <input type="tel" class="form-control" id="phone" name="phone" 
                                                   value="<?= htmlspecialchars($user_data['phone'] ?? '') ?>" required>
                                        </div>
                                        <div class="mb-3">
                                            <label for="address" class="form-label">Địa chỉ</label>
                                            <textarea class="form-control" id="address" name="address" rows="3"><?= htmlspecialchars($user_data['address'] ?? '') ?></textarea>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">Vai trò</label>
                                            <input type="text" class="form-control" 
                                                   value="<?= isset($user_data['role_as']) && $user_data['role_as'] == 1 ? 'Admin' : 'Nhân viên' ?>" disabled>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">Ngày tham gia</label>
                                            <input type="text" class="form-control" 
                                                   value="<?= isset($user_data['created_at']) ? date('d/m/Y H:i', strtotime($user_data['created_at'])) : 'N/A' ?>" disabled>
                                        </div>
                                        <button type="submit" name="update_profile_btn" class="btn bg-gradient-primary">
                                            <i class="fas fa-save me-2"></i>Cập nhật thông tin
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Đổi mật khẩu -->
                        <div class="col-md-6">
                            <div class="card border-0 shadow-sm">
                                <div class="card-header bg-gradient-warning text-white">
                                    <h5 class="mb-0"><i class="fas fa-lock me-2"></i>Đổi mật khẩu</h5>
                                </div>
                                <div class="card-body">
                                    <form method="POST">
                                        <div class="mb-3">
                                            <label for="current_password" class="form-label">Mật khẩu hiện tại</label>
                                            <input type="password" class="form-control" id="current_password" name="current_password" required>
                                        </div>
                                        <div class="mb-3">
                                            <label for="new_password" class="form-label">Mật khẩu mới</label>
                                            <input type="password" class="form-control" id="new_password" name="new_password" 
                                                   placeholder="Tối thiểu 6 ký tự" required>
                                        </div>
                                        <div class="mb-3">
                                            <label for="confirm_password" class="form-label">Xác nhận mật khẩu mới</label>
                                            <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                                        </div>
                                        <button type="submit" name="change_password_btn" class="btn bg-gradient-warning text-white">
                                            <i class="fas fa-key me-2"></i>Đổi mật khẩu
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

