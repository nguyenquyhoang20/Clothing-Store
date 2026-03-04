<?php
session_start();
include("../config/dbcon.php");
include("../functions/userfunctions.php");

header('Content-Type: application/json');

if (isset($_GET['product_id'])) {
    $product_id = mysqli_real_escape_string($conn, $_GET['product_id']);
    
    // Lấy size còn hàng
    $available_sizes = getAvailableSizes($product_id);
    
    if (!empty($available_sizes)) {
        echo json_encode([
            'success' => true,
            'sizes' => $available_sizes
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Sản phẩm hiện đang hết hàng',
            'sizes' => []
        ]);
    }
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Thiếu product_id',
        'sizes' => []
    ]);
}
?>

