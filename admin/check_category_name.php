<?php
session_start();
include("../config/dbcon.php");

header('Content-Type: application/json');

if (isset($_GET['name'])) {
    $category_name = mysqli_real_escape_string($conn, $_GET['name']);
    $category_id = isset($_GET['id']) ? mysqli_real_escape_string($conn, $_GET['id']) : 0;
    
    // Kiểm tra tên danh mục đã tồn tại chưa (không phân biệt hoa thường)
    if ($category_id > 0) {
        // Khi edit, loại trừ chính danh mục đang sửa
        $check_query = "SELECT * FROM categories WHERE LOWER(name) = LOWER('$category_name') AND id != '$category_id'";
    } else {
        // Khi thêm mới
        $check_query = "SELECT * FROM categories WHERE LOWER(name) = LOWER('$category_name')";
    }
    
    $result = mysqli_query($conn, $check_query);
    
    if (mysqli_num_rows($result) > 0) {
        echo json_encode([
            'exists' => true,
            'message' => 'Tên danh mục đã tồn tại'
        ]);
    } else {
        echo json_encode([
            'exists' => false,
            'message' => 'Tên danh mục có thể sử dụng'
        ]);
    }
} else {
    echo json_encode([
        'exists' => false,
        'message' => 'Thiếu tham số'
    ]);
}

mysqli_close($conn);
?>

