<?php
session_start();

include(__DIR__ . "/../config/dbcon.php");
include(__DIR__ . "/../functions/userfunctions.php");
include(__DIR__ . "/../functions/myfunctions.php");
include(__DIR__ . "/../functions/currency.php");

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode([
        'success' => false,
        'message' => 'Phương thức không được hỗ trợ'
    ]);
    exit();
}

$action = isset($_POST['action']) ? $_POST['action'] : 'apply';
$cart_items = getCartItems();
$cart_totals = calculateCartTotals($cart_items);

if ($action === 'remove') {
    unset($_SESSION['checkout_voucher']);

    echo json_encode([
        'success' => true,
        'message' => 'Đã gỡ mã giảm giá',
        'voucher' => null,
        'discount' => 0,
        'formatted' => [
            'base_total' => formatVND($cart_totals['base_total']),
            'flash_discount' => formatVND($cart_totals['flash_discount']),
            'voucher_discount' => formatVND(0),
            'final_total' => formatVND($cart_totals['effective_total']),
        ]
    ]);
    exit();
}

$voucher_code = isset($_POST['voucher_code']) ? trim($_POST['voucher_code']) : '';

if (empty($voucher_code)) {
    echo json_encode([
        'success' => false,
        'message' => 'Vui lòng nhập mã voucher',
    ]);
    exit();
}

if (empty($cart_items)) {
    echo json_encode([
        'success' => false,
        'message' => 'Giỏ hàng trống',
    ]);
    exit();
}

$voucher_data = getVoucherDataByCode($voucher_code);

if (!$voucher_data) {
    unset($_SESSION['checkout_voucher']);
    echo json_encode([
        'success' => false,
        'message' => 'Mã voucher không hợp lệ hoặc đã hết hạn',
    ]);
    exit();
}

// Kiểm tra số lượng sử dụng
if (isset($voucher_data['voucher_quantity']) && $voucher_data['voucher_quantity'] !== null && $voucher_data['voucher_quantity'] <= 0) {
    unset($_SESSION['checkout_voucher']);
    echo json_encode([
        'success' => false,
        'message' => 'Mã voucher đã được sử dụng hết lượt',
    ]);
    exit();
}

$order_total = $cart_totals['effective_total'];
$discount_amount = applyVoucherDiscount($order_total, $voucher_data);

if ($discount_amount <= 0) {
    unset($_SESSION['checkout_voucher']);
    $min_order = $voucher_data['voucher_min_order'] ?? 0;
    $message = 'Đơn hàng chưa đáp ứng điều kiện để áp dụng voucher';
    if ($min_order > 0) {
        $message = 'Đơn hàng cần tối thiểu ' . formatVND($min_order) . ' để sử dụng voucher này';
    }
    echo json_encode([
        'success' => false,
        'message' => $message,
    ]);
    exit();
}

$final_total = max($order_total - $discount_amount, 0);

$_SESSION['checkout_voucher'] = [
    'code' => $voucher_data['code'],
    'voucher_id' => $voucher_data['id'],
    'discount_amount' => $discount_amount,
    'applied_at' => date('Y-m-d H:i:s'),
];

echo json_encode([
    'success' => true,
    'message' => 'Áp dụng voucher thành công',
    'voucher' => [
        'code' => $voucher_data['code'],
        'type' => $voucher_data['voucher_type'],
        'value' => $voucher_data['voucher_value'],
    ],
    'discount' => $discount_amount,
    'formatted' => [
        'base_total' => formatVND($cart_totals['base_total']),
        'flash_discount' => formatVND($cart_totals['flash_discount']),
        'voucher_discount' => formatVND($discount_amount),
        'final_total' => formatVND($final_total),
    ]
]);
exit();

