<?php
/**
 * Security Helper Functions
 * CSRF Protection, XSS Prevention, File Upload Validation
 */

// ============================================
// CSRF PROTECTION
// ============================================

/**
 * Tạo hoặc lấy CSRF token từ session
 */
function csrf_token() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Tạo hidden input chứa CSRF token cho form
 */
function csrf_field() {
    return '<input type="hidden" name="csrf_token" value="' . csrf_token() . '">';
}

/**
 * Kiểm tra CSRF token có hợp lệ không
 * @param string $token - Token từ form POST
 * @return bool
 */
function csrf_verify($token = null) {
    if ($token === null) {
        $token = $_POST['csrf_token'] ?? '';
    }
    if (empty($token) || empty($_SESSION['csrf_token'])) {
        return false;
    }
    return hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Kiểm tra CSRF và redirect nếu không hợp lệ
 */
function csrf_check() {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (!csrf_verify()) {
            if (session_status() === PHP_SESSION_NONE) {
                session_start();
            }
            $_SESSION['message'] = 'Phiên làm việc đã hết hạn. Vui lòng thử lại.';
            header('HTTP/1.1 403 Forbidden');
            header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? '/'));
            exit();
        }
    }
}

// ============================================
// XSS PROTECTION
// ============================================

/**
 * Escape HTML output - shorthand cho htmlspecialchars
 * @param string $string
 * @return string
 */
function e($string) {
    if ($string === null) return '';
    return htmlspecialchars((string)$string, ENT_QUOTES, 'UTF-8');
}

/**
 * Escape output cho JavaScript context
 * @param string $string
 * @return string
 */
function ejs($string) {
    if ($string === null) return '';
    return addslashes(htmlspecialchars((string)$string, ENT_QUOTES, 'UTF-8'));
}

// ============================================
// FILE UPLOAD VALIDATION
// ============================================

/**
 * Validate file upload
 * @param array $file - $_FILES element
 * @param array $options - Tùy chọn validation
 * @return array ['valid' => bool, 'error' => string|null]
 */
function validate_upload($file, $options = []) {
    $defaults = [
        'max_size' => 5 * 1024 * 1024, // 5MB
        'allowed_types' => ['image/jpeg', 'image/png', 'image/gif', 'image/webp'],
        'allowed_extensions' => ['jpg', 'jpeg', 'png', 'gif', 'webp'],
    ];
    $options = array_merge($defaults, $options);

    // Kiểm tra lỗi upload
    if ($file['error'] !== UPLOAD_ERR_OK) {
        $errors = [
            UPLOAD_ERR_INI_SIZE => 'File quá lớn (vượt giới hạn server)',
            UPLOAD_ERR_FORM_SIZE => 'File quá lớn (vượt giới hạn form)',
            UPLOAD_ERR_PARTIAL => 'File chỉ được upload một phần',
            UPLOAD_ERR_NO_FILE => 'Không có file nào được upload',
            UPLOAD_ERR_NO_TMP_DIR => 'Thiếu thư mục tạm',
            UPLOAD_ERR_CANT_WRITE => 'Không thể ghi file',
        ];
        return ['valid' => false, 'error' => $errors[$file['error']] ?? 'Lỗi upload không xác định'];
    }

    // Kiểm tra kích thước
    if ($file['size'] > $options['max_size']) {
        $maxMB = round($options['max_size'] / (1024 * 1024), 1);
        return ['valid' => false, 'error' => "File quá lớn. Tối đa {$maxMB}MB"];
    }

    // Kiểm tra extension
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($ext, $options['allowed_extensions'])) {
        return ['valid' => false, 'error' => 'Định dạng file không được phép. Chấp nhận: ' . implode(', ', $options['allowed_extensions'])];
    }

    // Kiểm tra MIME type thực tế
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);

    if (!in_array($mime, $options['allowed_types'])) {
        return ['valid' => false, 'error' => 'Loại file không hợp lệ. Chỉ chấp nhận ảnh.'];
    }

    return ['valid' => true, 'error' => null];
}

/**
 * Tạo tên file an toàn cho upload
 * @param string $original_name - Tên file gốc
 * @return string - Tên file an toàn
 */
function safe_filename($original_name) {
    $ext = strtolower(pathinfo($original_name, PATHINFO_EXTENSION));
    return time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
}

// ============================================
// INPUT SANITIZATION
// ============================================

/**
 * Lấy giá trị từ POST đã được sanitize
 * @param string $key
 * @param string $default
 * @return string
 */
function post($key, $default = '') {
    return isset($_POST[$key]) ? trim($_POST[$key]) : $default;
}

/**
 * Lấy giá trị từ GET đã được sanitize
 * @param string $key
 * @param string $default
 * @return string
 */
function get($key, $default = '') {
    return isset($_GET[$key]) ? trim($_GET[$key]) : $default;
}

// ============================================
// RATE LIMITING (Session-based)
// ============================================

/**
 * Kiểm tra rate limit cho login
 * @param string $identifier - Email hoặc IP
 * @param int $max_attempts - Số lần tối đa
 * @param int $lockout_seconds - Thời gian khóa (giây)
 * @return bool - true nếu được phép, false nếu bị khóa
 */
function check_rate_limit($identifier, $max_attempts = 5, $lockout_seconds = 300) {
    $key = 'rate_limit_' . md5($identifier);
    
    if (!isset($_SESSION[$key])) {
        $_SESSION[$key] = ['attempts' => 0, 'last_attempt' => 0, 'locked_until' => 0];
    }
    
    $data = &$_SESSION[$key];
    
    // Kiểm tra nếu đang bị khóa
    if ($data['locked_until'] > time()) {
        return false;
    }
    
    // Reset nếu đã hết thời gian khóa
    if ($data['locked_until'] > 0 && $data['locked_until'] <= time()) {
        $data = ['attempts' => 0, 'last_attempt' => 0, 'locked_until' => 0];
    }
    
    return true;
}

/**
 * Ghi nhận một lần attempt
 */
function record_attempt($identifier, $max_attempts = 5, $lockout_seconds = 300) {
    $key = 'rate_limit_' . md5($identifier);
    
    if (!isset($_SESSION[$key])) {
        $_SESSION[$key] = ['attempts' => 0, 'last_attempt' => 0, 'locked_until' => 0];
    }
    
    $_SESSION[$key]['attempts']++;
    $_SESSION[$key]['last_attempt'] = time();
    
    if ($_SESSION[$key]['attempts'] >= $max_attempts) {
        $_SESSION[$key]['locked_until'] = time() + $lockout_seconds;
    }
}

/**
 * Reset rate limit (sau khi đăng nhập thành công)
 */
function reset_rate_limit($identifier) {
    $key = 'rate_limit_' . md5($identifier);
    unset($_SESSION[$key]);
}
?>
