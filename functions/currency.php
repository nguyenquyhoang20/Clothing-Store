<?php
/**
 * File định dạng tiền tệ
 * Chứa các hàm liên quan đến việc định dạng và xử lý tiền tệ
 */

/**
 * Hàm định dạng giá tiền theo định dạng Việt Nam Đồng
 * @param float $price - Giá tiền cần định dạng
 * @return string - Chuỗi đã định dạng (ví dụ: 10.000 VNĐ)
 */
function formatVND($price) {
    // Đảm bảo price là số
    $price = is_numeric($price) ? $price : 0;
    // Định dạng với dấu chấm phân cách hàng nghìn và 0 số thập phân
    return number_format($price, 0, ',', '.') . ' VNĐ';
}

/**
 * Hàm định dạng giá tiền theo định dạng Việt Nam Đồng (không có đơn vị)
 * @param float $price - Giá tiền cần định dạng
 * @return string - Chuỗi đã định dạng (ví dụ: 10.000)
 */
function formatNumber($price) {
    // Đảm bảo price là số
    $price = is_numeric($price) ? $price : 0;
    // Định dạng với dấu chấm phân cách hàng nghìn và 0 số thập phân
    return number_format($price, 0, ',', '.');
}

/**
 * Hàm chuyển đổi chuỗi giá trị thành số
 * Loại bỏ các ký tự không phải số và dấu chấm
 * @param string $priceString - Chuỗi giá tiền (ví dụ: "10.000 VNĐ")
 * @return float - Giá trị số
 */
function parseVND($priceString) {
    // Loại bỏ các ký tự không phải số, dấu chấm và dấu phẩy
    $cleaned = preg_replace('/[^0-9.,]/', '', $priceString);
    // Thay thế dấu chấm bằng rỗng để có số nguyên
    $cleaned = str_replace('.', '', $cleaned);
    // Thay thế dấu phẩy bằng dấu chấm (nếu có)
    $cleaned = str_replace(',', '.', $cleaned);
    return floatval($cleaned);
}

/**
 * Hàm xử lý giá trị từ form nhập liệu
 * Chuyển đổi giá trị có định dạng Việt Nam thành số nguyên để lưu vào database
 * @param string $priceInput - Giá trị từ form (ví dụ: "10.000")
 * @return int - Giá trị số nguyên (ví dụ: 10000)
 */
function processPriceInput($priceInput) {
    // Loại bỏ các ký tự không phải số
    $cleaned = preg_replace('/[^0-9]/', '', $priceInput);
    return intval($cleaned);
}
?>