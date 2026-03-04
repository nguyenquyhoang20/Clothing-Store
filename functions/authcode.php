<?php
session_start();
include(__DIR__ . "/../config/dbcon.php");
include(__DIR__ . "/myfunctions.php");

if(isset($_POST['register-btn']) || isset($_POST['register_btn']))
{
    $name = trim($_POST['name'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $password = $_POST['password'] ?? '';
    $cpassword = $_POST['cpassword'] ?? '';

    // Validate email
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['message'] = "Email không hợp lệ!";
        redirect("../index.php?page=register", "Email không hợp lệ!");
    }

    // Check email already exists
    $stmt = $pdo->prepare("SELECT email FROM users WHERE email = ?");
    $stmt->execute([$email]);
    if ($stmt->rowCount() > 0) {
        redirect("../index.php?page=register", "Email đã được sử dụng!");
    }

    // Check password match
    if ($password !== $cpassword) {
        redirect("../index.php?page=register", "Mật khẩu không khớp!");
    }

    if (strlen($password) <= 6) {
        redirect("../index.php?page=register", "Mật khẩu phải nhiều hơn 6 ký tự!");
    }

    // Hash password
    $pass_hash = password_hash($password, PASSWORD_DEFAULT);
    
    // Insert user with prepared statement
    $stmt = $pdo->prepare("INSERT INTO `users` (`name`,`email`,`phone`,`address`,`password`,`role_as`,`is_active`) 
                          VALUES (?, ?, ?, ?, ?, 0, 0)");
    $result = $stmt->execute([$name, $email, $phone, $address, $pass_hash]);
    
    if ($result) {
        redirect("../index.php?page=login", "Đăng ký thành công! Vui lòng đăng nhập.");
    } else {
        redirect("../index.php?page=register", "Có lỗi xảy ra khi đăng ký!");
    }
}
else if(isset($_POST['login_btn']))
{
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    // Rate limiting
    if (!check_rate_limit($email)) {
        redirect("../index.php?page=login", "Bạn đã đăng nhập sai quá nhiều lần. Vui lòng thử lại sau 5 phút.");
    }

    // Find user by email using prepared statement
    $stmt = $pdo->prepare("SELECT * FROM `users` WHERE `email` = ?");
    $stmt->execute([$email]);
    $userdata = $stmt->fetch();

    if ($userdata) {
        $loginOk = false;

        // Verify password (support both hashed and legacy plaintext)
        if (password_verify($password, $userdata['password'])) {
            $loginOk = true;
        } else if ($userdata['password'] === $password) {
            // Legacy plaintext - auto-hash on successful login
            $loginOk = true;
            $new_hash = password_hash($password, PASSWORD_DEFAULT);
            $update = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
            $update->execute([$new_hash, $userdata['id']]);
        }

        if ($loginOk) {
            // Reset rate limit on success
            reset_rate_limit($email);

            $_SESSION['auth'] = true;
            $_SESSION['auth_user'] = [
                'id'      => $userdata['id'],
                'name'    => $userdata['name'],
                'email'   => $userdata['email'],
                'role_as' => $userdata['role_as']
            ];
            $_SESSION['role_as'] = $userdata['role_as'];

            if ($userdata['role_as'] == 1) {
                redirect("../admin/index.php", "Đăng nhập thành công!");
            } else {
                redirect("../index.php?page=home", "Đăng nhập thành công!");
            }
        } else {
            record_attempt($email);
            redirect("../index.php?page=login", "Mật khẩu không đúng!");
        }
    } else {
        record_attempt($email);
        redirect("../index.php?page=login", "Email không tồn tại!");
    }
}
else if(isset($_POST['update_user_btn']))
{
    if (!isset($_SESSION['auth_user']['id'])) {
        redirect("../index.php?page=login", "Vui lòng đăng nhập!");
    }

    $id = intval($_SESSION['auth_user']['id']);
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $password = $_POST['password'] ?? '';
    $cpassword = $_POST['cpassword'] ?? '';

    if (empty($password)) {
        // Update without password
        $stmt = $pdo->prepare("UPDATE `users` SET `name`=?, `email`=?, `phone`=?, `address`=? WHERE `id`=?");
        $result = $stmt->execute([$name, $email, $phone, $address, $id]);
    } else {
        if ($password !== $cpassword) {
            redirect("../admin/index.php?page=profile", "Mật khẩu không khớp!");
        }
        $p_hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("UPDATE `users` SET `name`=?, `email`=?, `phone`=?, `address`=?, `password`=? WHERE `id`=?");
        $result = $stmt->execute([$name, $email, $phone, $address, $p_hash, $id]);
    }

    if ($result) {
        // Update session data
        $_SESSION['auth_user']['name'] = $name;
        $_SESSION['auth_user']['email'] = $email;
        redirect("../admin/index.php?page=profile", "Cập nhật thông tin thành công!");
    } else {
        redirect("../admin/index.php?page=profile", "Có lỗi xảy ra!");
    }
}
else if(isset($_GET['logout']))
{
    session_destroy();
    header("Location: ../index.php?page=home");
    exit();
}
?>