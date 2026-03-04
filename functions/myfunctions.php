<?php
include(__DIR__ . "/../config/dbcon.php");
include_once(__DIR__ . "/security.php");

/**
 * Kiểm tra bảng có tồn tại không
 */
function tableExists($table)
{
    global $pdo;
    // Whitelist các bảng được phép
    $allowed_tables = ['products', 'categories', 'orders', 'order_detail', 'users', 
                       'voucher', 'product_images', 'product_sizes', 'roles', 
                       'permissions', 'role_permissions', 'user_permissions', 
                       'audit_log', 'audit_log_relations', 'flash_sales', 'blog'];
    if (!in_array($table, $allowed_tables)) {
        return false;
    }
    $stmt = $pdo->prepare("SHOW TABLES LIKE ?");
    $stmt->execute([$table]);
    return $stmt->rowCount() > 0;
}

/**
 * Lấy tất cả records từ một bảng
 */
function getAll($table)
{
    global $pdo;
    $allowed_tables = ['products', 'categories', 'orders', 'order_detail', 'users', 
                       'voucher', 'product_images', 'product_sizes', 'roles',
                       'permissions', 'role_permissions', 'audit_log'];
    if (!in_array($table, $allowed_tables)) {
        return [];
    }
    if (!tableExists($table)) {
        return [];
    }
    
    if ($table === 'products') {
        $stmt = $pdo->query("SELECT * FROM products WHERE deleted_at IS NULL ORDER BY id DESC");
    } else {
        $stmt = $pdo->query("SELECT * FROM `$table` ORDER BY id DESC");
    }
    
    return $stmt->fetchAll();
}

/**
 * Lấy record theo ID
 */
function getByID($table, $id)
{
    global $conn, $pdo;
    $allowed_tables = ['products', 'categories', 'orders', 'order_detail', 'users', 
                       'voucher', 'product_images', 'product_sizes', 'roles'];
    if (!in_array($table, $allowed_tables)) {
        return mysqli_query($conn, "SELECT 1 WHERE 0");
    }
    
    if ($table === 'products') {
        $query = "SELECT * FROM products WHERE id=? AND deleted_at IS NULL";
    } else {
        $query = "SELECT * FROM `$table` WHERE id=?";
    }
    
    // Vẫn trả về mysqli result để giữ tương thích ngược
    $id = intval($id);
    if ($table === 'products') {
        $safe_query = "SELECT * FROM products WHERE id='$id' AND deleted_at IS NULL";
    } else {
        $safe_query = "SELECT * FROM `$table` WHERE id='$id'";
    }
    return mysqli_query($conn, $safe_query);
}

/**
 * Đếm tổng số records trong bảng
 */
function totalValue($table){
    global $pdo;
    $allowed_tables = ['products', 'categories', 'orders', 'order_detail', 'users', 
                       'voucher', 'roles', 'permissions'];
    if (!in_array($table, $allowed_tables)) {
        return 0;
    }
    if (!tableExists($table)) {
        return 0;
    }
    
    if ($table === 'products') {
        $stmt = $pdo->query("SELECT COUNT(*) as `number` FROM products WHERE deleted_at IS NULL");
    } else {
        $stmt = $pdo->query("SELECT COUNT(*) as `number` FROM `$table`");
    }
    
    $row = $stmt->fetch();
    return $row['number'] ?? 0;
}

/**
 * Lấy tất cả users kèm tổng đơn mua
 */
function getAllUsers($page = 0){
    global $pdo;
    $stmt = $pdo->query("SELECT `users`.*, COUNT(`order_detail`.`id`) AS `total_buy` FROM `users`
            LEFT JOIN `order_detail` ON `users`.`id` = `order_detail`.`user_id`
            GROUP BY `users`.`id`
            ORDER BY `users`.`created_at` DESC");
    return $stmt->fetchAll();
}

/**
 * Lấy tất cả đơn hàng theo loại trạng thái
 */
function getAllOrder($type = -1){
    global $pdo;
    
    if ($type == -1) {
        $stmt = $pdo->query("SELECT `orders`.*,
                COUNT(`order_detail`.`id`) as `quantity`,
                COALESCE(`orders`.`customer_name`, `users`.`name`) as `name`,
                COALESCE(`orders`.`customer_email`, `users`.`email`) as `email`,
                COALESCE(`orders`.`customer_phone`, `users`.`phone`) as `phone`,
                COALESCE(`orders`.`customer_address`, `users`.`address`) as `address`
                FROM `orders`
                LEFT JOIN `users` ON `orders`.`user_id` = `users`.`id`
                LEFT JOIN `order_detail` ON `order_detail`.`order_id` = `orders`.`id`
                WHERE `orders`.`status` IN(1,2,3,4,5)
                GROUP BY `orders`.`id`
                ORDER BY `orders`.`id` DESC");
    } else {
        $type = intval($type);
        $stmt = $pdo->prepare("SELECT `orders`.*,
                COUNT(`order_detail`.`id`) as `quantity`,
                COALESCE(`orders`.`customer_name`, `users`.`name`) as `name`,
                COALESCE(`orders`.`customer_email`, `users`.`email`) as `email`,
                COALESCE(`orders`.`customer_phone`, `users`.`phone`) as `phone`,
                COALESCE(`orders`.`customer_address`, `users`.`address`) as `address`
                FROM `orders`
                LEFT JOIN `users` ON `orders`.`user_id` = `users`.`id`
                LEFT JOIN `order_detail` ON `order_detail`.`order_id` = `orders`.`id`
                WHERE `orders`.`status` = ?
                GROUP BY `orders`.`id`
                ORDER BY `orders`.`id` DESC");
        $stmt->execute([$type]);
    }
    
    return $stmt->fetchAll();
}

/**
 * Lấy chi tiết đơn hàng
 */
function getOrderDetail($order_id){
    global $pdo;
    $stmt = $pdo->prepare("SELECT 
                COALESCE(orders.customer_name, users.name) as name,
                COALESCE(orders.customer_email, users.email) as email,
                COALESCE(orders.customer_phone, users.phone) as phone,
                COALESCE(orders.customer_address, users.address) as address,
                products.name as name_product, 
                products.selling_price,
                products.image,
                order_detail.*,
                orders.customer_note
                FROM order_detail 
                LEFT JOIN orders ON order_detail.order_id = orders.id
                LEFT JOIN users ON order_detail.user_id = users.id
                JOIN products ON products.id = order_detail.product_id
                WHERE order_detail.order_id = ?");
    $stmt->execute([intval($order_id)]);
    return $stmt->fetchAll();
}

/**
 * Tính tổng doanh thu từ đơn hàng hoàn thành
 */
function totalPriceGet(){
    global $pdo;
    $stmt = $pdo->query("SELECT selling_price * quantity as price FROM `order_detail` WHERE `status` = 4");
    $total_price = 0;
    while ($row = $stmt->fetch()) {
        $total_price += $row['price'];
    }
    return $total_price;
}

/**
 * Redirect với session message
 */
function redirect($url, $message)
{
    $_SESSION['message']= $message;
    header("Location:" . $url);
    exit();
}

// ============================================
// HELPER: Kiểm tra tồn kho sản phẩm
// ============================================

/**
 * Kiểm tra sản phẩm có size hay không và còn hàng không
 * @param array $product - Dữ liệu sản phẩm
 * @param string|null $cat_name - Tên danh mục (lowercase), null = tự lấy
 * @return array ['has_size' => bool, 'is_in_stock' => bool, 'cat_name' => string]
 */
function checkProductStock($product, $cat_name = null) {
    if ($cat_name === null) {
        $cat_info = getByID("categories", $product['category_id']);
        $cat_info = mysqli_fetch_array($cat_info);
        $cat_name = mb_strtolower($cat_info['name'] ?? '', 'UTF-8');
    }
    
    $has_size = false;
    $clothing_keywords = ['áo', 'ao', 'quần', 'quan', 'đầm', 'dam', 'váy', 'vay', 'shirt', 'pant'];
    $shoe_keywords = ['giày', 'giay', 'dép', 'dep', 'sandal', 'shoe'];
    
    foreach ($clothing_keywords as $keyword) {
        if (mb_strpos($cat_name, $keyword) !== false) {
            $has_size = true;
            break;
        }
    }
    if (!$has_size) {
        foreach ($shoe_keywords as $keyword) {
            if (mb_strpos($cat_name, $keyword) !== false) {
                $has_size = true;
                break;
            }
        }
    }
    
    $is_in_stock = false;
    if ($has_size) {
        $available_sizes = getAvailableSizes($product['id']);
        $is_in_stock = !empty($available_sizes);
    } else {
        $is_in_stock = ($product['qty'] > 0);
    }
    
    return [
        'has_size' => $has_size,
        'is_in_stock' => $is_in_stock,
        'cat_name' => $cat_name
    ];
}

/**
 * Lấy sản phẩm kèm thông tin danh mục (tránh N+1 query)
 */
function getProductsWithCategory($limit, $offset = 0, $category_slug = '', $search = '', $sort = 'newest', $min_price = 0, $max_price = 0) {
    global $pdo;
    
    $where = ["p.deleted_at IS NULL", "p.status = 0"];
    $params = [];
    
    if (!empty($category_slug)) {
        $where[] = "c.slug = ?";
        $params[] = $category_slug;
    }
    
    if (!empty($search)) {
        $where[] = "p.name LIKE ?";
        $params[] = "%$search%";
    }
    
    if ($min_price > 0) {
        $where[] = "p.selling_price >= ?";
        $params[] = intval($min_price);
    }
    
    if ($max_price > 0) {
        $where[] = "p.selling_price <= ?";
        $params[] = intval($max_price);
    }
    
    $where_clause = implode(' AND ', $where);
    
    // Sắp xếp
    switch ($sort) {
        case 'price_asc':
            $order = "p.selling_price ASC";
            break;
        case 'price_desc':
            $order = "p.selling_price DESC";
            break;
        case 'name_asc':
            $order = "p.name ASC";
            break;
        case 'oldest':
            $order = "p.id ASC";
            break;
        case 'newest':
        default:
            $order = "p.id DESC";
            break;
    }
    
    $sql = "SELECT p.*, p.image as display_image, c.name as category_name, c.slug as category_slug
            FROM products p
            LEFT JOIN categories c ON p.category_id = c.id
            WHERE $where_clause
            ORDER BY $order
            LIMIT ? OFFSET ?";
    
    $params[] = intval($limit);
    $params[] = intval($offset);
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll();
}

/**
 * Đếm tổng sản phẩm (cho phân trang)
 */
function countProducts($category_slug = '', $search = '', $min_price = 0, $max_price = 0) {
    global $pdo;
    
    $where = ["p.deleted_at IS NULL", "p.status = 0"];
    $params = [];
    
    if (!empty($category_slug)) {
        $where[] = "c.slug = ?";
        $params[] = $category_slug;
    }
    
    if (!empty($search)) {
        $where[] = "p.name LIKE ?";
        $params[] = "%$search%";
    }
    
    if ($min_price > 0) {
        $where[] = "p.selling_price >= ?";
        $params[] = intval($min_price);
    }
    
    if ($max_price > 0) {
        $where[] = "p.selling_price <= ?";
        $params[] = intval($max_price);
    }
    
    $where_clause = implode(' AND ', $where);
    
    $sql = "SELECT COUNT(*) as total FROM products p
            LEFT JOIN categories c ON p.category_id = c.id
            WHERE $where_clause";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $row = $stmt->fetch();
    return $row['total'] ?? 0;
}

/**
 * Tra cứu đơn hàng theo mã đơn và SĐT
 */
function trackOrder($order_id, $phone) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT o.*, 
                           COUNT(od.id) as total_items
                           FROM orders o
                           LEFT JOIN order_detail od ON o.id = od.order_id
                           WHERE o.id = ? AND o.customer_phone = ?
                           GROUP BY o.id
                           LIMIT 1");
    $stmt->execute([intval($order_id), $phone]);
    return $stmt->fetch();
}

/**
 * Lấy chi tiết đơn hàng cho tra cứu
 */
function getTrackOrderItems($order_id) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT od.*, p.name as product_name, p.image, p.slug
                           FROM order_detail od
                           JOIN products p ON od.product_id = p.id
                           WHERE od.order_id = ?");
    $stmt->execute([intval($order_id)]);
    return $stmt->fetchAll();
}
?>