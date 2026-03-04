<?php
// File chứa các hàm để ghi log hoạt động của nhân viên
// $conn đã được include từ functions.php trong index.php

/**
 * Ghi log hoạt động của nhân viên
 * @param int $admin_id ID của admin
 * @param string $admin_name Tên admin
 * @param string $action Loại hành động (CREATE, UPDATE, DELETE, VIEW)
 * @param string $table_name Tên bảng
 * @param int $record_id ID của record
 * @param array $old_values Giá trị cũ (optional)
 * @param array $new_values Giá trị mới (optional)
 * @param string $description Mô tả chi tiết (optional)
 */
function logAdminActivity($admin_id, $admin_name, $action, $table_name, $record_id = null, $old_values = null, $new_values = null, $description = null) {
    global $conn;
    
    // Kiểm tra xem admin_id có tồn tại trong bảng users không
    $valid_admin_id = null;
    if ($admin_id) {
        $check_user_query = "SELECT id FROM users WHERE id = ? LIMIT 1";
        $check_stmt = mysqli_prepare($conn, $check_user_query);
        mysqli_stmt_bind_param($check_stmt, "i", $admin_id);
        mysqli_stmt_execute($check_stmt);
        $check_result = mysqli_stmt_get_result($check_stmt);
        
        if ($check_result && mysqli_num_rows($check_result) > 0) {
            $valid_admin_id = $admin_id;
        }
        mysqli_stmt_close($check_stmt);
    }
    
    // Lấy thông tin IP và User Agent
    $ip_address = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';
    
    // Chuyển đổi array thành JSON nếu cần
    $old_values_json = $old_values ? json_encode($old_values, JSON_UNESCAPED_UNICODE) : null;
    $new_values_json = $new_values ? json_encode($new_values, JSON_UNESCAPED_UNICODE) : null;
    
    // Sử dụng prepared statement với admin_id có thể là NULL
    $query = "INSERT INTO audit_log (admin_id, admin_name, action, table_name, record_id, old_values, new_values, description, ip_address, user_agent) 
              VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    
    $stmt = mysqli_prepare($conn, $query);
    
    // Bind parameters - admin_id có thể là NULL
    // mysqli sẽ tự động xử lý NULL cho integer
    $admin_id_to_bind = $valid_admin_id;
    mysqli_stmt_bind_param($stmt, "isssisssss", 
        $admin_id_to_bind, 
        $admin_name, 
        $action, 
        $table_name, 
        $record_id, 
        $old_values_json, 
        $new_values_json, 
        $description, 
        $ip_address, 
        $user_agent
    );
    
    mysqli_stmt_execute($stmt);
    $log_id = mysqli_insert_id($conn);
    mysqli_stmt_close($stmt);
    
    // Tạo mối quan hệ với bảng được thao tác
    if($record_id && $table_name) {
        $relation_query = "INSERT INTO audit_log_relations (audit_log_id, related_table, related_id) VALUES (?, ?, ?)";
        $relation_stmt = mysqli_prepare($conn, $relation_query);
        mysqli_stmt_bind_param($relation_stmt, "isi", $log_id, $table_name, $record_id);
        mysqli_stmt_execute($relation_stmt);
        mysqli_stmt_close($relation_stmt);
    }
}

/**
 * Lấy tất cả log hoạt động với phân trang
 * @param int $limit Số record mỗi trang
 * @param int $offset Vị trí bắt đầu
 * @param string $search Từ khóa tìm kiếm
 * @param string $filter_admin Lọc theo admin
 * @param string $filter_action Lọc theo hành động
 * @param string $filter_table Lọc theo bảng
 */
function getAuditLogs($limit = 50, $offset = 0, $search = '', $filter_admin = '', $filter_action = '', $filter_table = '') {
    global $conn;
    
    $where_conditions = [];
    $params = [];
    $param_types = '';
    
    if (!empty($search)) {
        $where_conditions[] = "(al.admin_name LIKE ? OR al.table_name LIKE ? OR al.description LIKE ?)";
        $search_param = "%$search%";
        $params[] = $search_param;
        $params[] = $search_param;
        $params[] = $search_param;
        $param_types .= 'sss';
    }
    
    if (!empty($filter_admin)) {
        $where_conditions[] = "al.admin_name = ?";
        $params[] = $filter_admin;
        $param_types .= 's';
    }
    
    if (!empty($filter_action)) {
        $where_conditions[] = "al.action = ?";
        $params[] = $filter_action;
        $param_types .= 's';
    }
    
    if (!empty($filter_table)) {
        $where_conditions[] = "al.table_name = ?";
        $params[] = $filter_table;
        $param_types .= 's';
    }
    
    $where_clause = !empty($where_conditions) ? 'WHERE ' . implode(' AND ', $where_conditions) : '';
    
    // JOIN với bảng users để lấy role
    $query = "SELECT al.*, u.role_as, 
                     CASE WHEN u.role_as = 1 THEN 'Admin' WHEN u.role_as = 0 THEN 'Nhân viên' ELSE 'Không xác định' END as user_role
              FROM audit_log al 
              LEFT JOIN users u ON al.admin_id = u.id 
              $where_clause 
              ORDER BY al.created_at DESC 
              LIMIT ? OFFSET ?";
    $params[] = $limit;
    $params[] = $offset;
    $param_types .= 'ii';
    
    $stmt = mysqli_prepare($conn, $query);
    if (!empty($params)) {
        mysqli_stmt_bind_param($stmt, $param_types, ...$params);
    }
    mysqli_stmt_execute($stmt);
    
    return mysqli_stmt_get_result($stmt);
}

/**
 * Đếm tổng số log
 */
function countAuditLogs($search = '', $filter_admin = '', $filter_action = '', $filter_table = '') {
    global $conn;
    
    $where_conditions = [];
    $params = [];
    $param_types = '';
    
    if (!empty($search)) {
        $where_conditions[] = "(admin_name LIKE ? OR table_name LIKE ? OR description LIKE ?)";
        $search_param = "%$search%";
        $params[] = $search_param;
        $params[] = $search_param;
        $params[] = $search_param;
        $param_types .= 'sss';
    }
    
    if (!empty($filter_admin)) {
        $where_conditions[] = "admin_name = ?";
        $params[] = $filter_admin;
        $param_types .= 's';
    }
    
    if (!empty($filter_action)) {
        $where_conditions[] = "action = ?";
        $params[] = $filter_action;
        $param_types .= 's';
    }
    
    if (!empty($filter_table)) {
        $where_conditions[] = "table_name = ?";
        $params[] = $filter_table;
        $param_types .= 's';
    }
    
    $where_clause = !empty($where_conditions) ? 'WHERE ' . implode(' AND ', $where_conditions) : '';
    
    $query = "SELECT COUNT(*) as total FROM audit_log $where_clause";
    
    if (!empty($params)) {
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, $param_types, ...$params);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
    } else {
        $result = mysqli_query($conn, $query);
    }
    
    $row = mysqli_fetch_array($result);
    return $row['total'];
}

/**
 * Lấy thống kê hoạt động theo admin
 */
function getAdminActivityStats() {
    global $conn;
    
    $query = "SELECT admin_name, 
                     COUNT(*) as total_actions,
                     SUM(CASE WHEN action = 'CREATE' THEN 1 ELSE 0 END) as create_count,
                     SUM(CASE WHEN action = 'UPDATE' THEN 1 ELSE 0 END) as update_count,
                     SUM(CASE WHEN action = 'DELETE' THEN 1 ELSE 0 END) as delete_count,
                     MAX(created_at) as last_activity
              FROM audit_log 
              GROUP BY admin_name 
              ORDER BY total_actions DESC";
    
    return mysqli_query($conn, $query);
}

/**
 * Lấy thống kê hoạt động theo bảng
 */
function getTableActivityStats() {
    global $conn;
    
    $query = "SELECT table_name,
                     COUNT(*) as total_actions,
                     SUM(CASE WHEN action = 'CREATE' THEN 1 ELSE 0 END) as create_count,
                     SUM(CASE WHEN action = 'UPDATE' THEN 1 ELSE 0 END) as update_count,
                     SUM(CASE WHEN action = 'DELETE' THEN 1 ELSE 0 END) as delete_count
              FROM audit_log 
              GROUP BY table_name 
              ORDER BY total_actions DESC";
    
    return mysqli_query($conn, $query);
}

/**
 * Lấy danh sách admin đã thực hiện hoạt động
 */
function getActiveAdmins() {
    global $conn;
    
    $query = "SELECT DISTINCT admin_name FROM audit_log ORDER BY admin_name";
    return mysqli_query($conn, $query);
}

/**
 * Lấy danh sách bảng đã có hoạt động
 */
function getActiveTables() {
    global $conn;
    
    $query = "SELECT DISTINCT table_name FROM audit_log ORDER BY table_name";
    return mysqli_query($conn, $query);
}

/**
 * Xóa log cũ (giữ lại log trong X ngày)
 */
function cleanOldAuditLogs($days_to_keep = 90) {
    global $conn;
    
    $query = "DELETE FROM audit_log WHERE created_at < DATE_SUB(NOW(), INTERVAL ? DAY)";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "i", $days_to_keep);
    mysqli_stmt_execute($stmt);
    
    return mysqli_stmt_affected_rows($stmt);
}

/**
 * Lấy thông tin chi tiết của record được thao tác
 */
function getAuditRecordInfo($table_name, $record_id) {
    global $conn;
    
    $info = "Không xác định";
    
    switch($table_name) {
        case 'users':
            $query = "SELECT CONCAT('ID: ', id, ', Tên: ', name, ', Email: ', email) as info FROM users WHERE id = ?";
            break;
        case 'products':
            $query = "SELECT CONCAT('ID: ', id, ', Tên: ', name, ', Giá: ', selling_price) as info FROM products WHERE id = ?";
            break;
        case 'categories':
            $query = "SELECT CONCAT('ID: ', id, ', Tên: ', name) as info FROM categories WHERE id = ?";
            break;
        case 'orders':
            $query = "SELECT CONCAT('ID: ', id, ', Tổng tiền: ', total_amount, ', Trạng thái: ', status) as info FROM orders WHERE id = ?";
            break;
        case 'voucher':
            $query = "SELECT CONCAT('ID: ', id, ', Mã: ', code, ', Giá trị: ', value) as info FROM voucher WHERE id = ?";
            break;
        default:
            return "Bảng: $table_name, ID: $record_id";
    }
    
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "i", $record_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    if($row = mysqli_fetch_array($result)) {
        $info = $row['info'];
    }
    
    mysqli_stmt_close($stmt);
    return $info;
}

/**
 * Lấy audit log với thông tin chi tiết
 */
function getAuditLogsWithDetails($limit = 50, $offset = 0, $search = '', $filter_admin = '', $filter_action = '', $filter_table = '') {
    global $conn;
    
    $where_conditions = [];
    $params = [];
    $param_types = '';
    
    if (!empty($search)) {
        $where_conditions[] = "(al.admin_name LIKE ? OR al.table_name LIKE ? OR al.description LIKE ?)";
        $search_param = "%$search%";
        $params[] = $search_param;
        $params[] = $search_param;
        $params[] = $search_param;
        $param_types .= 'sss';
    }
    
    if (!empty($filter_admin)) {
        $where_conditions[] = "al.admin_name = ?";
        $params[] = $filter_admin;
        $param_types .= 's';
    }
    
    if (!empty($filter_action)) {
        $where_conditions[] = "al.action = ?";
        $params[] = $filter_action;
        $param_types .= 's';
    }
    
    if (!empty($filter_table)) {
        $where_conditions[] = "al.table_name = ?";
        $params[] = $filter_table;
        $param_types .= 's';
    }
    
    $where_clause = !empty($where_conditions) ? 'WHERE ' . implode(' AND ', $where_conditions) : '';
    
    $query = "SELECT al.*, u.name as admin_full_name, u.email as admin_email, u.role as admin_role 
              FROM audit_log al 
              LEFT JOIN users u ON al.admin_id = u.id 
              $where_clause 
              ORDER BY al.created_at DESC 
              LIMIT ? OFFSET ?";
    
    $params[] = $limit;
    $params[] = $offset;
    $param_types .= 'ii';
    
    $stmt = mysqli_prepare($conn, $query);
    if (!empty($params)) {
        mysqli_stmt_bind_param($stmt, $param_types, ...$params);
    }
    mysqli_stmt_execute($stmt);
    
    return mysqli_stmt_get_result($stmt);
}

/**
 * Lấy tất cả mối quan hệ của audit log
 */
function getAuditLogRelations($audit_log_id) {
    global $conn;
    
    $query = "SELECT * FROM audit_log_relations WHERE audit_log_id = ?";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "i", $audit_log_id);
    mysqli_stmt_execute($stmt);
    
    return mysqli_stmt_get_result($stmt);
}

/**
 * Format chi tiết thay đổi từ old_values và new_values (ngắn gọn)
 */
function formatAuditDetails($action, $old_values, $new_values, $description) {
    // Ưu tiên sử dụng description nếu có
    if ($description) {
        return htmlspecialchars($description);
    }
    
    $old_data = is_string($old_values) ? json_decode($old_values, true) : $old_values;
    $new_data = is_string($new_values) ? json_decode($new_values, true) : $new_values;
    
    // Lấy tên sản phẩm/danh mục
    $item_name = '';
    if ($new_data && isset($new_data['name'])) {
        $item_name = $new_data['name'];
    } elseif ($old_data && isset($old_data['name'])) {
        $item_name = $old_data['name'];
    }
    
    // Xử lý theo từng action
    if ($action === 'UPDATE' && $old_data && $new_data && is_array($old_data) && is_array($new_data)) {
        // Kiểm tra các trường thay đổi quan trọng
        $changed_fields = [];
        
        if (isset($new_data['qty']) && isset($old_data['qty']) && $old_data['qty'] != $new_data['qty']) {
            $changed_fields[] = 'qty';
        }
        if (isset($new_data['name']) && isset($old_data['name']) && $old_data['name'] != $new_data['name']) {
            $changed_fields[] = 'name';
        }
        if (isset($new_data['description']) && isset($old_data['description']) && $old_data['description'] != $new_data['description']) {
            $changed_fields[] = 'description';
        }
        if (isset($new_data['selling_price']) && isset($old_data['selling_price']) && $old_data['selling_price'] != $new_data['selling_price']) {
            $changed_fields[] = 'selling_price';
        }
        
        // Nếu chỉ thay đổi số lượng
        if (count($changed_fields) === 1 && $changed_fields[0] === 'qty' && $item_name) {
            return htmlspecialchars($item_name) . " - Số lượng: " . ($old_data['qty'] ?? 0) . " → " . ($new_data['qty'] ?? 0);
        }
        
        // Nếu chỉ thay đổi tên
        if (count($changed_fields) === 1 && $changed_fields[0] === 'name') {
            return "Tên: " . htmlspecialchars($old_data['name'] ?? '') . " → " . htmlspecialchars($new_data['name'] ?? '');
        }
        
        // Nếu chỉ thay đổi mô tả
        if (count($changed_fields) === 1 && $changed_fields[0] === 'description' && $item_name) {
            return htmlspecialchars($item_name) . " - Mô tả đã thay đổi";
        }
        
        // Nếu chỉ thay đổi giá
        if (count($changed_fields) === 1 && $changed_fields[0] === 'selling_price' && $item_name) {
            return htmlspecialchars($item_name) . " - Giá: " . number_format($old_data['selling_price'] ?? 0) . " → " . number_format($new_data['selling_price'] ?? 0);
        }
        
        // Nếu thay đổi nhiều thứ, chỉ hiển thị tên
        if ($item_name) {
            return htmlspecialchars($item_name);
        }
    }
    
    // Fallback cho các action khác
    if ($action === 'CREATE' && $new_data && isset($new_data['name'])) {
        return "Đã tạo: " . htmlspecialchars($new_data['name']);
    } elseif ($action === 'UPDATE' && $item_name) {
        return "Đã cập nhật: " . htmlspecialchars($item_name);
    } elseif ($action === 'DELETE' && $old_data && isset($old_data['name'])) {
        return "Đã xóa: " . htmlspecialchars($old_data['name']);
    }
    
    return "Không có thông tin";
}
?>
