<?php
session_start();
include(__DIR__ . "/../config/dbcon.php");
include(__DIR__ . "/../functions/currency.php");
require_once(__DIR__ . "/../functions/userfunctions.php");
require_once(__DIR__ . "/functions/audit_functions.php");

if (!function_exists('getFlashSaleById')) {
    function getFlashSaleById($id)
    {
        global $conn;
        $id = intval($id);
        $query = "SELECT fs.*, 
                        p.name AS product_name,
                        p.slug AS product_slug,
                        p.selling_price AS product_price,
                        p.image AS product_image
                  FROM flash_sales fs
                  JOIN products p ON fs.product_id = p.id
                  WHERE fs.id = '$id'
                  LIMIT 1";
        return mysqli_query($conn, $query);
    }
}

// Helper functions cho session notifications
function setSessionMessage($message, $type = 'success') {
    $_SESSION['flash_message'] = $message;
    $_SESSION['flash_type'] = $type;
}

function getSessionMessage() {
    if (isset($_SESSION['flash_message'])) {
        $message = $_SESSION['flash_message'];
        $type = $_SESSION['flash_type'] ?? 'success';
        unset($_SESSION['flash_message']);
        unset($_SESSION['flash_type']);
        return ['message' => $message, 'type' => $type];
    }
    return null;
}

// Function redirect đơn giản (không dùng URL parameters nữa)
function redirect($url, $message = '', $type = 'success') {
    if ($message) {
        setSessionMessage($message, $type);
    }
    header("Location: $url");
    exit();
}

// Function logAdminActivity được định nghĩa trong functions/audit_functions.php

// Kiểm tra đăng nhập (cho phép cả admin và nhân viên)
if(!isset($_SESSION['auth']) || !isset($_SESSION['auth_user']['role_as']) || ($_SESSION['auth_user']['role_as'] != 1 && $_SESSION['auth_user']['role_as'] != 0)) {
    header("Location: login.php");
    exit();
}

// Lấy thông tin admin hiện tại
$admin_id = $_SESSION['auth_user']['id'] ?? 1;
$admin_name = $_SESSION['auth_user']['name'] ?? 'Admin';

if(isset($_POST['add_category_btn']))
{

    $name= mysqli_real_escape_string($conn, $_POST['name']);
    $slug=$_POST['slug'] . "-" . rand(10,99);
    $description=$_POST['description'];
    $status=isset($_POST['status']) ? '1':'0';
    $image= $_FILES['image']['name'];

    // Kiểm tra trùng tên danh mục (không phân biệt hoa thường)
    $check_name_query = "SELECT * FROM categories WHERE LOWER(name) = LOWER('$name')";
    $check_name_result = mysqli_query($conn, $check_name_query);
    
    if(mysqli_num_rows($check_name_result) > 0) {
        $existing = mysqli_fetch_array($check_name_result);
        redirect("index.php?page=add-category", "Tên danh mục đã tồn tại!", "danger");
        exit();
    }

    $path="../images"; 
    $image_ext=pathinfo($image, PATHINFO_EXTENSION);
    $filename= time().'.'.$image_ext;

    $cate_query = "INSERT INTO categories (name,slug,description,status,image) 
    VALUES ('$name', '$slug','$description','$status', '$filename')";

    $cate_query_run=mysqli_query($conn, $cate_query);

    if($cate_query_run)
    {
        $category_id = mysqli_insert_id($conn);
        move_uploaded_file($_FILES['image']['tmp_name'], $path.'/'.$filename);
        
        // Ghi log hoạt động
        logAdminActivity($admin_id, $admin_name, 'CREATE', 'categories', $category_id, 
            null, 
            ['name' => $name, 'slug' => $slug, 'description' => $description, 'status' => $status, 'image' => $filename], 
            "Thêm danh mục mới: $name");
        
        redirect("index.php?page=category", "Thêm danh mục thành công!");
    }else
    {
        redirect("index.php?page=add-category", "Có lỗi xảy ra khi thêm danh mục!", "danger");
    }
}else if(isset($_POST['update_category_btn']))
{

    $category_id= mysqli_real_escape_string($conn, $_POST['category_id']);
    $name= mysqli_real_escape_string($conn, $_POST['name']);
    $slug=$_POST['slug'];
    $description=$_POST['description'];
    $status=isset($_POST['status']) ? '1':'0';

    // Kiểm tra trùng tên danh mục với danh mục khác (loại trừ chính nó)
    $check_name_query = "SELECT * FROM categories WHERE LOWER(name) = LOWER('$name') AND id != '$category_id'";
    $check_name_result = mysqli_query($conn, $check_name_query);
    
    if(mysqli_num_rows($check_name_result) > 0) {
        $existing = mysqli_fetch_array($check_name_result);
        redirect("index.php?page=edit-category&id=$category_id", "Tên danh mục đã tồn tại!", "danger");
        exit();
    }

    $new_image= $_FILES['image']['name'];
    $old_image= $_POST['old_image'];

    if($new_image != "")
    {
        //$update_filename= $new_image;
        $image_ext=pathinfo($new_image, PATHINFO_EXTENSION);
        $update_filename= time().'.'.$image_ext;
    
    }
    else
    {
        $update_filename=$old_image;
    }
    $path="../images"; 
    $update_query= "UPDATE categories SET name='$name', slug='$slug', description='$description', status='$status', image='$update_filename' WHERE id='$category_id'";
    $update_query_run= mysqli_query($conn,$update_query);

    if($update_query_run)
    {
        if($_FILES['image']['name'] != "")
        {
            move_uploaded_file($_FILES['image']['tmp_name'],$path.'/'. $update_filename);
            if(file_exists("../images/".$old_image))
            {
                unlink("../images/".$old_image);
            }
        }
        
        // Ghi log hoạt động
        logAdminActivity($admin_id, $admin_name, 'UPDATE', 'categories', $category_id, 
            ['old_image' => $old_image], 
            ['name' => $name, 'slug' => $slug, 'description' => $description, 'status' => $status, 'image' => $update_filename], 
            "Cập nhật danh mục: $name");
        
        redirect("index.php?page=category", "Cập nhật danh mục thành công!");
    }
    else
    {
        redirect("index.php?page=edit-category&id=$category_id", "Có lỗi xảy ra khi cập nhật danh mục!", "danger");
    }
} 
else if (isset($_POST['delete_category_btn'])) {
    $category_id = mysqli_real_escape_string($conn, $_POST['category_id']);
    $is_ajax = isset($_POST['ajax_request']);

    $check_products_query = "SELECT * FROM products WHERE category_id='$category_id'";
    $check_products_query_run = mysqli_query($conn, $check_products_query);

    if (mysqli_num_rows($check_products_query_run) > 0) {
        if ($is_ajax) {
            header('Content-Type: application/json');
            echo json_encode([
                'success' => false,
                'message' => 'Danh mục còn chứa sản phẩm, không thể xóa.'
            ]);
            exit();
        } else {
            redirect("index.php?page=category", "Danh mục còn chứa sản phẩm, không thể xóa!", "danger");
        }
    } else {
        $category_query = "SELECT * FROM categories WHERE id='$category_id'";
        $category_query_run = mysqli_query($conn, $category_query);
        $category_data = mysqli_fetch_array($category_query_run);
        $image = $category_data['image'];

        $delete_query = "DELETE FROM categories WHERE id='$category_id'";
        $delete_query_run = mysqli_query($conn, $delete_query);

        if ($delete_query_run) {
            if (file_exists("../images/" . $image)) {
                unlink("../images/" . $image);
            }
            
            // Ghi log hoạt động
            logAdminActivity($admin_id, $admin_name, 'DELETE', 'categories', $category_id, 
                ['name' => $category_data['name'], 'slug' => $category_data['slug'], 'image' => $image], 
                null, "Xóa danh mục: " . $category_data['name']);
            
            if ($is_ajax) {
                header('Content-Type: application/json');
                echo json_encode([
                    'success' => true,
                    'message' => 'Xóa danh mục "' . $category_data['name'] . '" thành công!'
                ]);
                exit();
            } else {
                header("Location: index.php?page=category");
                exit();
            }
        } else {
            if ($is_ajax) {
                header('Content-Type: application/json');
                echo json_encode([
                    'success' => false,
                    'message' => 'Có lỗi xảy ra khi xóa danh mục'
                ]);
                exit();
            } else {
                header("Location: index.php?page=category");
                exit();
            }
        }
    }
}
else if(isset($_POST['add_product_btn']))
{
    $category_id= mysqli_real_escape_string($conn, $_POST['category_id']);

    $name= mysqli_real_escape_string($conn, $_POST['name']);
    $slug= mysqli_real_escape_string($conn, $_POST['slug'])  . "-" . rand(10,99);
    $small_description= mysqli_real_escape_string($conn, $_POST['small_description']);
    $description= mysqli_real_escape_string($conn, $_POST['description']);
    $original_price= processPriceInput($_POST['original_price']);
    $selling_price= processPriceInput($_POST['selling_price']);
    $status= isset($_POST['status']) ? '1':'0';
    $qty= mysqli_real_escape_string($conn, $_POST['qty']);
    $size= ''; // Size sẽ do khách hàng chọn khi mua hàng
    $image= $_FILES['image']['name'];
    $product_images = $_FILES['product_images'];

    $path="../images"; 
    $image_ext=pathinfo($image, PATHINFO_EXTENSION);
    $filename= time().'.'.$image_ext;

    if($name != "" && $slug != "" && $description !="")
    {
        $product_query= "INSERT INTO products (category_id,name,slug,small_description,description,original_price,selling_price,image,qty,size,status) VALUES 
        ('$category_id','$name','$slug','$small_description','$description','$original_price','$selling_price','$filename','$qty','$size','$status')";

        $product_query_run=mysqli_query($conn,$product_query);

        if($product_query_run)
        {
            $product_id = mysqli_insert_id($conn);
            
            // Xử lý ảnh chính
            move_uploaded_file($_FILES['image']['tmp_name'], $path.'/'.$filename);
            
            // Xử lý nhiều ảnh phụ
            if(isset($product_images['name']) && !empty($product_images['name'][0])) {
                $image_count = count($product_images['name']);
                
                for($i = 0; $i < $image_count; $i++) {
                    if($product_images['error'][$i] == 0) {
                        $image_name = $product_images['name'][$i];
                        $image_ext = pathinfo($image_name, PATHINFO_EXTENSION);
                        $new_filename = time() . '_' . $i . '.' . $image_ext;
                        
                        // Upload ảnh
                        move_uploaded_file($product_images['tmp_name'][$i], $path.'/'.$new_filename);
                        
                        // Lưu vào database
                        $is_main = ($i == 0) ? 1 : 0; // Ảnh đầu tiên là ảnh chính
                        $image_query = "INSERT INTO product_images (product_id, image_url, alt_text, is_main) VALUES ('$product_id', '$new_filename', '$name', '$is_main')";
                        mysqli_query($conn, $image_query);
                    }
                }
            }
            
            // Lưu số lượng từng size vào bảng product_sizes
            if(isset($_POST['size_quantities']) && is_array($_POST['size_quantities'])) {
                foreach($_POST['size_quantities'] as $size => $quantity) {
                    $size = mysqli_real_escape_string($conn, $size);
                    $quantity = intval($quantity);
                    
                    // Chỉ lưu những size có số lượng > 0
                    if($quantity >= 0) {
                        $size_query = "INSERT INTO product_sizes (product_id, size, quantity) VALUES ('$product_id', '$size', '$quantity')";
                        mysqli_query($conn, $size_query);
                    }
                }
            }
            
            // Ghi log hoạt động
            logAdminActivity($admin_id, $admin_name, 'CREATE', 'products', $product_id, null, 
                ['name' => $name, 'slug' => $slug, 'category_id' => $category_id, 'selling_price' => $selling_price, 'qty' => $qty], 
                "Tạo sản phẩm mới: $name");
            
            redirect("index.php?page=products", "Thêm sản phẩm thành công!");
        }else
        {
            redirect("index.php?page=add-product", "Có lỗi xảy ra khi thêm sản phẩm!", "danger");
        }
    }else
    {
        redirect("index.php?page=add-product", "Vui lòng điền đầy đủ thông tin!", "danger");
    }
}
else if(isset($_POST['update_product_btn']))
{
    $product_id= mysqli_real_escape_string($conn, $_POST['product_id']);
    $category_id= mysqli_real_escape_string($conn, $_POST['category_id']);

    $name= mysqli_real_escape_string($conn, $_POST['name']);
    $slug= mysqli_real_escape_string($conn, $_POST['slug'])  . "-" . rand(10,99);
    $small_description= mysqli_real_escape_string($conn, $_POST['small_description']);
    $description= mysqli_real_escape_string($conn, $_POST['description']);
    $original_price= processPriceInput($_POST['original_price']);
    $selling_price= processPriceInput($_POST['selling_price']);
    $status= isset($_POST['status']) ? '1':'0';
    // Nếu sản phẩm có size, qty sẽ là 0 (số lượng được quản lý theo size)
    // Nếu sản phẩm không có size, qty sẽ lấy từ form
    if(isset($_POST['size_quantities']) && is_array($_POST['size_quantities']) && count($_POST['size_quantities']) > 0) {
        $qty = 0; // Sản phẩm có size, số lượng được quản lý trong product_sizes
    } else {
        $qty = mysqli_real_escape_string($conn, $_POST['qty'] ?? 0);
    }
    $size= ''; // Size sẽ do khách hàng chọn khi mua hàng

    $path="../images"; 

    $new_image= $_FILES['image']['name'];
    $old_image= mysqli_real_escape_string($conn, $_POST['old_image']);

    if($new_image != "")
    {
        //$update_filename= $new_image;
        $image_ext=pathinfo($new_image, PATHINFO_EXTENSION);
        $update_filename= time().'.'.$image_ext;
    
    }
    else
    {
        $update_filename=$old_image;
    }

    $update_product_query= "UPDATE products SET name='$name', slug='$slug', small_description='$small_description', description='$description',
    original_price='$original_price', selling_price='$selling_price', status='$status', qty='$qty', size='$size', image='$update_filename' WHERE id='$product_id' ";
    $update_product_query_run= mysqli_query($conn,$update_product_query);

    if($update_product_query_run)
    {
        if($_FILES['image']['name'] != "")
        {
            move_uploaded_file($_FILES['image']['tmp_name'],$path.'/'. $update_filename);
            if(file_exists("../images/".$old_image))
            {
                unlink("../images/".$old_image);
            }
        }
        // Cập nhật số lượng từng size
        if(isset($_POST['size_quantities']) && is_array($_POST['size_quantities'])) {
            // Xóa tất cả size cũ của sản phẩm
            $delete_sizes_query = "DELETE FROM product_sizes WHERE product_id = '$product_id'";
            mysqli_query($conn, $delete_sizes_query);
            
            // Thêm lại với số lượng mới
            foreach($_POST['size_quantities'] as $size => $quantity) {
                $size = mysqli_real_escape_string($conn, $size);
                $quantity = intval($quantity);
                
                if($quantity >= 0) {
                    $size_query = "INSERT INTO product_sizes (product_id, size, quantity) VALUES ('$product_id', '$size', '$quantity')";
                    mysqli_query($conn, $size_query);
                }
            }
        }
        
        // Ghi log hoạt động
        logAdminActivity($admin_id, $admin_name, 'UPDATE', 'products', $product_id, 
            ['old_image' => $old_image], 
            ['name' => $name, 'slug' => $slug, 'category_id' => $category_id, 'selling_price' => $selling_price, 'qty' => $qty, 'image' => $update_filename], 
            "Cập nhật sản phẩm: $name");
        
        redirect("index.php?page=products", "Cập nhật sản phẩm thành công!");
    }else
    {
        redirect("index.php?page=edit-product&id=$product_id", "Có lỗi xảy ra khi cập nhật sản phẩm!", "danger");
    }
} 
else if (isset($_POST['delete_product_btn'])) {
    $product_id = mysqli_real_escape_string($conn, $_POST['product_id']);
    $is_ajax = isset($_POST['ajax_request']);

    // Lấy thông tin sản phẩm trước
    $product_query = "SELECT * FROM products WHERE id='$product_id' AND deleted_at IS NULL";
    $product_query_run = mysqli_query($conn, $product_query);
    
    if (mysqli_num_rows($product_query_run) == 0) {
        if ($is_ajax) {
            header('Content-Type: application/json');
            echo json_encode([
                'success' => false,
                'message' => 'Sản phẩm không tồn tại hoặc đã bị xóa'
            ]);
            exit();
        } else {
            redirect("index.php?page=products", "Sản phẩm không tồn tại hoặc đã bị xóa!", "danger");
        }
        exit();
    }
    
    $product_data = mysqli_fetch_array($product_query_run);

    // SOFT DELETE - Chỉ đánh dấu là đã xóa, KHÔNG xóa thật
    // Giữ nguyên dữ liệu để thống kê, báo cáo
    $soft_delete_query = "UPDATE products SET deleted_at = NOW() WHERE id='$product_id'";
    $soft_delete_run = mysqli_query($conn, $soft_delete_query);

    if ($soft_delete_run) {
        // Ghi log hoạt động
        logAdminActivity($admin_id, $admin_name, 'SOFT_DELETE', 'products', $product_id, 
            ['name' => $product_data['name'], 'slug' => $product_data['slug']], 
            ['deleted_at' => date('Y-m-d H:i:s')], 
            "Xóa sản phẩm (soft delete): " . $product_data['name']);
        
        if ($is_ajax) {
            header('Content-Type: application/json');
            echo json_encode([
                'success' => true,
                'message' => 'Đã xóa sản phẩm "' . $product_data['name'] . '" (dữ liệu vẫn được giữ để thống kê)'
            ]);
            exit();
        } else {
            redirect("index.php?page=products", "Xóa sản phẩm thành công!");
        }
    } else {
        if ($is_ajax) {
            header('Content-Type: application/json');
            echo json_encode([
                'success' => false,
                'message' => 'Có lỗi xảy ra khi xóa sản phẩm'
            ]);
            exit();
        } else {
            redirect("index.php?page=products", "Có lỗi xảy ra khi xóa sản phẩm!", "danger");
        }
    }
}
else if (isset($_POST['update_order_status'])){
    $order_id = mysqli_real_escape_string($conn, $_POST['order_id']);
    $new_status = mysqli_real_escape_string($conn, $_POST['order_status']);
    
    // Lấy thông tin đơn hàng trước khi cập nhật
    $order_query = "SELECT * FROM orders WHERE id = '$order_id'";
    $order_result = mysqli_query($conn, $order_query);
    $order_data = mysqli_fetch_array($order_result);
    $old_status = $order_data['status'];
    
    // Nếu chuyển sang trạng thái "Đã hủy" (5) hoặc "Thất bại" (6), hoàn trả sản phẩm về kho
    if (($new_status == 5 || $new_status == 6) && ($old_status == 2 || $old_status == 3)) {
        // Lấy tất cả sản phẩm trong đơn hàng (bao gồm cả size)
        $get_order_details = "SELECT product_id, quantity, product_size FROM order_detail WHERE order_id = '$order_id'";
        $order_details_result = mysqli_query($conn, $get_order_details);
        
        while ($detail = mysqli_fetch_array($order_details_result)) {
            $product_id = $detail['product_id'];
            $quantity = $detail['quantity'];
            $size = $detail['product_size'];
            
            // Hoàn trả số lượng vào kho theo size
            if (!empty($size)) {
                // Nếu có size, hoàn trả số lượng của size đó
                increaseSizeQuantity($product_id, $size, $quantity);
            } else {
                // Nếu không có size, hoàn trả số lượng tổng
                $restore_qty_query = "UPDATE products SET qty = qty + '$quantity' WHERE id = '$product_id'";
                mysqli_query($conn, $restore_qty_query);
            }
        }
    }
    
    // Cập nhật trạng thái orders
    $update_order_query = "UPDATE `orders` SET `status` = '$new_status' WHERE `id` = '$order_id'"; 
    mysqli_query($conn, $update_order_query);

    // Cập nhật trạng thái order_detail
    $update_detail_query = "UPDATE `order_detail` SET `status` = '$new_status' WHERE `order_id` = '$order_id'"; 
    mysqli_query($conn, $update_detail_query);

    // Tạo thông báo thành công
    $status_names = ['2' => 'Đang chuẩn bị', '3' => 'Đang giao', '4' => 'Hoàn thành', '5' => 'Đã hủy', '6' => 'Thất bại'];
    $new_status_text = $status_names[$new_status] ?? 'Không xác định';
    
    if ($new_status == 5 || $new_status == 6) {
        $_SESSION['message'] = "Cập nhật trạng thái đơn hàng #$order_id thành '$new_status_text'. Sản phẩm đã được hoàn về kho!";
    } else {
        $_SESSION['message'] = "Cập nhật trạng thái đơn hàng #$order_id thành '$new_status_text' thành công!";
    }
    
    // Kiểm tra nếu có tham số return_to thì quay lại trang đó
    if(isset($_SERVER['HTTP_REFERER']) && strpos($_SERVER['HTTP_REFERER'], 'page=orders') !== false) {
        header("Location: index.php?page=orders");
    } else {
        header("Location: index.php?page=order-detail&id=$order_id");
    }
    exit();
}
else if (isset($_GET['order'])){
    $order_id   = $_GET['id'];
    $type       = $_GET['order'];
    
    // Lấy thông tin đơn hàng trước khi cập nhật
    $order_query = "SELECT * FROM orders WHERE id = '$order_id'";
    $order_result = mysqli_query($conn, $order_query);
    $order_data = mysqli_fetch_array($order_result);
    
    $query =    "UPDATE `orders` SET `status` = '$type', `processed_by` = '$admin_id'
                WHERE `id` = '$order_id'"; 
    mysqli_query($conn, $query);

    $query =    "UPDATE `order_detail` SET `status` = '$type'
                WHERE `order_id` = '$order_id'"; 
    mysqli_query($conn, $query);

    // Ghi log hoạt động
    $status_names = ['2' => 'Đang chuẩn bị', '3' => 'Đang giao', '4' => 'Hoàn thành', '5' => 'Đã hủy'];
    $old_status = $status_names[$order_data['status']] ?? 'Không xác định';
    $new_status = $status_names[$type] ?? 'Không xác định';
    
    logAdminActivity($admin_id, $admin_name, 'UPDATE', 'orders', $order_id, 
        ['status' => $order_data['status']], 
        ['status' => $type], 
        "Cập nhật trạng thái đơn hàng #$order_id từ '$old_status' thành '$new_status'");

    $_SESSION['message'] = "Cập nhật trạng thái thành công!";
    redirect("index.php?page=order-detail&id_order=$order_id");
}
else if(isset($_POST['add_voucher_btn']))
{
    $code = strtoupper(trim($_POST['code'] ?? ''));
    $code = mysqli_real_escape_string($conn, $code);
    $type = $_POST['type'] === 'percentage' ? 'percentage' : 'fixed';
    $value = isset($_POST['value']) ? floatval($_POST['value']) : 0;
    if ($type === 'percentage' && $value > 100) {
        $value = 100;
    }

    $min_order = isset($_POST['min_order']) && $_POST['min_order'] !== '' ? floatval($_POST['min_order']) : null;
    $max_order = isset($_POST['max_order']) && $_POST['max_order'] !== '' ? floatval($_POST['max_order']) : null;

    $start_date_input = trim($_POST['start_date'] ?? '');
    $end_date_input = trim($_POST['end_date'] ?? '');

    $start_date = $start_date_input !== '' ? date('Y-m-d H:i:s', strtotime($start_date_input)) : null;
    $end_date = $end_date_input !== '' ? date('Y-m-d H:i:s', strtotime($end_date_input)) : null;

    $status = isset($_POST['status']) ? 1 : 0;

    $min_order_sql = $min_order !== null ? "'" . $min_order . "'" : "NULL";
    $max_order_sql = $max_order !== null ? "'" . $max_order . "'" : "NULL";
    $start_date_sql = $start_date !== null ? "'" . mysqli_real_escape_string($conn, $start_date) . "'" : "NULL";
    $end_date_sql = $end_date !== null ? "'" . mysqli_real_escape_string($conn, $end_date) . "'" : "NULL";

    $voucher_query = "INSERT INTO voucher (code, type, value, min_order, max_order, start_date, end_date, status, created_by, created_at) 
    VALUES ('$code', '$type', '$value', $min_order_sql, $max_order_sql, $start_date_sql, $end_date_sql, '$status', '$admin_id', NOW())";

    $voucher_query_run = mysqli_query($conn, $voucher_query);

    if($voucher_query_run)
    {
        $voucher_id = mysqli_insert_id($conn);
        logAdminActivity(
            $admin_id,
            $admin_name,
            'CREATE',
            'voucher',
            $voucher_id,
            null,
            [
                'code' => $code,
                'type' => $type,
                'value' => $value,
                'status' => $status
            ],
            "Tạo voucher mới: $code"
        );
        
        redirect("index.php?page=voucher", "Thêm voucher thành công!");
    }else
    {
        redirect("index.php?page=add-voucher", "Có lỗi xảy ra khi thêm voucher!", "danger");
    }
}
else if(isset($_POST['update_voucher_btn']))
{
    $voucher_id = intval($_POST['voucher_id']);
    $code = strtoupper(trim($_POST['code'] ?? ''));
    $code = mysqli_real_escape_string($conn, $code);
    $type = $_POST['type'] === 'percentage' ? 'percentage' : 'fixed';
    $value = isset($_POST['value']) ? floatval($_POST['value']) : 0;
    if ($type === 'percentage' && $value > 100) {
        $value = 100;
    }

    $min_order = isset($_POST['min_order']) && $_POST['min_order'] !== '' ? floatval($_POST['min_order']) : null;
    $max_order = isset($_POST['max_order']) && $_POST['max_order'] !== '' ? floatval($_POST['max_order']) : null;

    $start_date_input = trim($_POST['start_date'] ?? '');
    $end_date_input = trim($_POST['end_date'] ?? '');

    $start_date = $start_date_input !== '' ? date('Y-m-d H:i:s', strtotime($start_date_input)) : null;
    $end_date = $end_date_input !== '' ? date('Y-m-d H:i:s', strtotime($end_date_input)) : null;

    $status = isset($_POST['status']) ? 1 : 0;

    $min_order_sql = $min_order !== null ? "'" . $min_order . "'" : "NULL";
    $max_order_sql = $max_order !== null ? "'" . $max_order . "'" : "NULL";
    $start_date_sql = $start_date !== null ? "'" . mysqli_real_escape_string($conn, $start_date) . "'" : "NULL";
    $end_date_sql = $end_date !== null ? "'" . mysqli_real_escape_string($conn, $end_date) . "'" : "NULL";

    $update_query = "UPDATE voucher 
                    SET code='$code',
                        type='$type',
                        value='$value',
                        min_order=$min_order_sql,
                        max_order=$max_order_sql,
                        start_date=$start_date_sql,
                        end_date=$end_date_sql,
                        status='$status',
                        updated_by='$admin_id'
                    WHERE id='$voucher_id'";
    $update_query_run = mysqli_query($conn, $update_query);

    if($update_query_run)
    {
        logAdminActivity(
            $admin_id,
            $admin_name,
            'UPDATE',
            'voucher',
            $voucher_id,
            null,
            [
                'code' => $code,
                'type' => $type,
                'value' => $value,
                'status' => $status
            ],
            "Cập nhật voucher: $code"
        );
        
        redirect("index.php?page=voucher", "Cập nhật voucher thành công!");
    }
    else
    {
        redirect("index.php?page=edit-voucher&id=$voucher_id", "Có lỗi xảy ra khi cập nhật voucher!", "danger");
    }
}
else if (isset($_POST['delete_voucher_btn'])) {
    $voucher_id = intval($_POST['voucher_id']);

    // Check if voucher is being used in any orders
    $check_orders_query = "SELECT * FROM orders WHERE voucher_id='$voucher_id'";
    $check_orders_query_run = mysqli_query($conn, $check_orders_query);

    if ($check_orders_query_run && mysqli_num_rows($check_orders_query_run) > 0) {
        redirect("index.php?page=voucher", "Voucher đang được sử dụng trong đơn hàng, không thể xóa!", "danger");
    } else {
        $voucher_query = "SELECT * FROM voucher WHERE id='$voucher_id' LIMIT 1";
        $voucher_result = mysqli_query($conn, $voucher_query);
        $voucher_data = $voucher_result && mysqli_num_rows($voucher_result) > 0 ? mysqli_fetch_array($voucher_result) : null;

        $delete_query = "DELETE FROM voucher WHERE id='$voucher_id'";
        $delete_query_run = mysqli_query($conn, $delete_query);

        if ($delete_query_run) {
            logAdminActivity(
                $admin_id,
                $admin_name,
                'DELETE',
                'voucher',
                $voucher_id,
                $voucher_data ? [
                    'code' => $voucher_data['code'],
                    'type' => $voucher_data['type'],
                    'value' => $voucher_data['value']
                ] : null,
                null,
                $voucher_data ? "Xóa voucher: " . $voucher_data['code'] : "Xóa voucher ID $voucher_id"
            );
            
            redirect("index.php?page=voucher", "Xóa voucher thành công!");
        } else {
            redirect("index.php?page=voucher", "Có lỗi xảy ra khi xóa voucher!", "danger");
        }
    }
}
else if(isset($_POST['add_flash_sale_btn']))
{
    $product_id = isset($_POST['product_id']) ? intval($_POST['product_id']) : 0;
    $discount_type = ($_POST['discount_type'] ?? 'fixed') === 'percentage' ? 'percentage' : 'fixed';
    $discount_value = isset($_POST['discount_value']) ? floatval($_POST['discount_value']) : 0;
    if ($discount_type === 'percentage' && $discount_value > 100) {
        $discount_value = 100;
    }

    $start_time_input = trim($_POST['start_time'] ?? '');
    $end_time_input = trim($_POST['end_time'] ?? '');

    $max_quantity = isset($_POST['max_quantity']) && $_POST['max_quantity'] !== '' ? intval($_POST['max_quantity']) : null;
    $status = isset($_POST['status']) ? 1 : 0;

    if ($product_id <= 0 || $start_time_input === '' || $end_time_input === '') {
        redirect("index.php?page=add-flash-sale", "Vui lòng điền đầy đủ thông tin!", "danger");
    }

    $start_time = date('Y-m-d H:i:s', strtotime($start_time_input));
    $end_time = date('Y-m-d H:i:s', strtotime($end_time_input));

    if ($end_time <= $start_time) {
        redirect("index.php?page=add-flash-sale", "Thời gian kết thúc phải sau thời gian bắt đầu!", "danger");
    }

    $product_check = mysqli_query($conn, "SELECT id, name FROM products WHERE id = '$product_id' LIMIT 1");
    if (!$product_check || mysqli_num_rows($product_check) === 0) {
        redirect("index.php?page=add-flash-sale", "Sản phẩm không tồn tại!", "danger");
    }
    $product_row = mysqli_fetch_assoc($product_check);

    $discount_value_sql = mysqli_real_escape_string($conn, $discount_value);
    $start_time_sql = mysqli_real_escape_string($conn, $start_time);
    $end_time_sql = mysqli_real_escape_string($conn, $end_time);
    $max_quantity_sql = $max_quantity !== null ? "'" . mysqli_real_escape_string($conn, $max_quantity) . "'" : "NULL";

    $insert_query = "INSERT INTO flash_sales (product_id, discount_type, discount_value, start_time, end_time, status, max_quantity, created_by, created_at)
                     VALUES ('$product_id', '$discount_type', '$discount_value_sql', '$start_time_sql', '$end_time_sql', '$status', $max_quantity_sql, '$admin_id', NOW())";
    $insert_run = mysqli_query($conn, $insert_query);

    if ($insert_run) {
        $flash_sale_id = mysqli_insert_id($conn);
        logAdminActivity(
            $admin_id,
            $admin_name,
            'CREATE',
            'flash_sales',
            $flash_sale_id,
            null,
            [
                'product_id' => $product_id,
                'discount_type' => $discount_type,
                'discount_value' => $discount_value,
                'start_time' => $start_time,
                'end_time' => $end_time,
                'status' => $status
            ],
            "Tạo flash sale cho sản phẩm {$product_row['name']}"
        );

        redirect("index.php?page=flash-sale", "Thêm flash sale thành công!");
    } else {
        redirect("index.php?page=add-flash-sale", "Có lỗi xảy ra khi thêm flash sale!", "danger");
    }
}
else if(isset($_POST['update_flash_sale_btn']))
{
    $flash_sale_id = isset($_POST['flash_sale_id']) ? intval($_POST['flash_sale_id']) : 0;
    $product_id = isset($_POST['product_id']) ? intval($_POST['product_id']) : 0;
    $discount_type = ($_POST['discount_type'] ?? 'fixed') === 'percentage' ? 'percentage' : 'fixed';
    $discount_value = isset($_POST['discount_value']) ? floatval($_POST['discount_value']) : 0;
    if ($discount_type === 'percentage' && $discount_value > 100) {
        $discount_value = 100;
    }

    $start_time_input = trim($_POST['start_time'] ?? '');
    $end_time_input = trim($_POST['end_time'] ?? '');
    $max_quantity = isset($_POST['max_quantity']) && $_POST['max_quantity'] !== '' ? intval($_POST['max_quantity']) : null;
    $status = isset($_POST['status']) ? 1 : 0;

    if ($flash_sale_id <= 0 || $product_id <= 0 || $start_time_input === '' || $end_time_input === '') {
        redirect("index.php?page=edit-flash-sale&id=$flash_sale_id", "Vui lòng điền đầy đủ thông tin!", "danger");
    }

    $start_time = date('Y-m-d H:i:s', strtotime($start_time_input));
    $end_time = date('Y-m-d H:i:s', strtotime($end_time_input));

    if ($end_time <= $start_time) {
        redirect("index.php?page=edit-flash-sale&id=$flash_sale_id", "Thời gian kết thúc phải sau thời gian bắt đầu!", "danger");
    }

    $product_check = mysqli_query($conn, "SELECT id, name FROM products WHERE id = '$product_id' LIMIT 1");
    if (!$product_check || mysqli_num_rows($product_check) === 0) {
        redirect("index.php?page=edit-flash-sale&id=$flash_sale_id", "Sản phẩm không tồn tại!", "danger");
    }
    $product_row = mysqli_fetch_assoc($product_check);

    $discount_value_sql = mysqli_real_escape_string($conn, $discount_value);
    $start_time_sql = mysqli_real_escape_string($conn, $start_time);
    $end_time_sql = mysqli_real_escape_string($conn, $end_time);
    $max_quantity_sql = $max_quantity !== null ? "'" . mysqli_real_escape_string($conn, $max_quantity) . "'" : "NULL";

    $update_query = "UPDATE flash_sales 
                     SET product_id = '$product_id',
                         discount_type = '$discount_type',
                         discount_value = '$discount_value_sql',
                         start_time = '$start_time_sql',
                         end_time = '$end_time_sql',
                         status = '$status',
                         max_quantity = $max_quantity_sql,
                         updated_by = '$admin_id'
                     WHERE id = '$flash_sale_id'";
    $update_run = mysqli_query($conn, $update_query);

    if ($update_run) {
        logAdminActivity(
            $admin_id,
            $admin_name,
            'UPDATE',
            'flash_sales',
            $flash_sale_id,
            null,
            [
                'product_id' => $product_id,
                'discount_type' => $discount_type,
                'discount_value' => $discount_value,
                'start_time' => $start_time,
                'end_time' => $end_time,
                'status' => $status
            ],
            "Cập nhật flash sale cho sản phẩm {$product_row['name']}"
        );

        redirect("index.php?page=flash-sale", "Cập nhật flash sale thành công!");
    } else {
        redirect("index.php?page=edit-flash-sale&id=$flash_sale_id", "Có lỗi xảy ra khi cập nhật flash sale!", "danger");
    }
}
else if(isset($_POST['delete_flash_sale_btn']))
{
    $flash_sale_id = isset($_POST['flash_sale_id']) ? intval($_POST['flash_sale_id']) : 0;
    if ($flash_sale_id <= 0) {
        redirect("index.php?page=flash-sale", "ID flash sale không hợp lệ!", "danger");
    }

    $flash_sale = getFlashSaleById($flash_sale_id);
    $flash_sale_data = ($flash_sale && mysqli_num_rows($flash_sale) > 0) ? mysqli_fetch_assoc($flash_sale) : null;

    $delete_query = "DELETE FROM flash_sales WHERE id = '$flash_sale_id'";
    $delete_run = mysqli_query($conn, $delete_query);

    if ($delete_run) {
        logAdminActivity(
            $admin_id,
            $admin_name,
            'DELETE',
            'flash_sales',
            $flash_sale_id,
            $flash_sale_data ? [
                'product_id' => $flash_sale_data['product_id'],
                'discount_type' => $flash_sale_data['discount_type'],
                'discount_value' => $flash_sale_data['discount_value']
            ] : null,
            null,
            $flash_sale_data ? "Xóa flash sale sản phẩm {$flash_sale_data['product_name']}" : "Xóa flash sale ID $flash_sale_id"
        );

        redirect("index.php?page=flash-sale", "Xóa flash sale thành công!");
    } else {
        redirect("index.php?page=flash-sale", "Có lỗi xảy ra khi xóa flash sale!", "danger");
    }
}
// Xử lý duyệt đăng ký nhân viên
else if(isset($_GET['approve_registration']))
{
    $registration_id = intval($_GET['approve_registration']);
    $admin_id = $_SESSION['auth_user']['id'];
    $admin_name = $_SESSION['auth_user']['name'];
    
    // Cập nhật is_active = 1 (approved) trong bảng users
    $update_user = "UPDATE users SET is_active=1 WHERE id='$registration_id' AND role_as=0 AND is_active=0";
    if(mysqli_query($conn, $update_user)) {
        logAdminActivity($admin_id, $admin_name, 'APPROVE', 'users', $registration_id, null, null, "Duyệt đăng ký nhân viên");
        redirect("index.php?page=employee-registrations");
    } else {
        redirect("index.php?page=employee-registrations");
    }
}
// Xử lý từ chối đăng ký nhân viên
else if(isset($_GET['reject_registration']))
{
    $registration_id = intval($_GET['reject_registration']);
    $admin_id = $_SESSION['auth_user']['id'];
    $admin_name = $_SESSION['auth_user']['name'];
    
    // Cập nhật is_active = 2 (rejected) trong bảng users
    $update_user = "UPDATE users SET is_active=2 WHERE id='$registration_id' AND role_as=0 AND is_active=0";
    if(mysqli_query($conn, $update_user)) {
        logAdminActivity($admin_id, $admin_name, 'REJECT', 'users', $registration_id, null, null, "Từ chối đăng ký nhân viên");
        redirect("index.php?page=employee-registrations");
    } else {
        redirect("index.php?page=employee-registrations");
    }
}
// Xử lý xóa nhân viên
else if(isset($_GET['delete_user']))
{
    $user_id = intval($_GET['delete_user']);
    $admin_id = $_SESSION['auth_user']['id'];
    $admin_name = $_SESSION['auth_user']['name'];
    
    // Kiểm tra user có tồn tại và là nhân viên không
    $check_user = "SELECT * FROM users WHERE id='$user_id' AND role_as=0";
    $user_result = mysqli_query($conn, $check_user);
    
    if(mysqli_num_rows($user_result) > 0) {
        $user_data = mysqli_fetch_assoc($user_result);
        
        // Kiểm tra xem user có đơn hàng không
        $check_orders = "SELECT COUNT(*) as order_count FROM orders WHERE user_id='$user_id'";
        $order_result = mysqli_query($conn, $check_orders);
        $order_data = mysqli_fetch_assoc($order_result);
        
        if($order_data['order_count'] > 0) {
            // Nếu có đơn hàng, chỉ đánh dấu là không hoạt động (soft delete)
            $update_query = "UPDATE users SET is_active=0 WHERE id='$user_id'";
            if(mysqli_query($conn, $update_query)) {
                logAdminActivity($admin_id, $admin_name, 'DELETE', 'users', $user_id, $user_data, null, "Vô hiệu hóa nhân viên: {$user_data['email']} (có {$order_data['order_count']} đơn hàng)");
                redirect("index.php?page=user");
            } else {
                redirect("index.php?page=user");
            }
        } else {
            // Nếu không có đơn hàng, xóa hoàn toàn
            $delete_query = "DELETE FROM users WHERE id='$user_id' AND role_as=0";
            if(mysqli_query($conn, $delete_query)) {
                logAdminActivity($admin_id, $admin_name, 'DELETE', 'users', $user_id, $user_data, null, "Xóa nhân viên: {$user_data['email']}");
                redirect("index.php?page=user");
            } else {
                redirect("index.php?page=user");
            }
        }
    } else {
        redirect("index.php?page=user");
    }
}
?>