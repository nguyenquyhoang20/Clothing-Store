<?php 
$pageTitle = "Thanh toán - NHÓM 10 Fashion Shop";

// Kiểm tra xem có order_id không
if (!isset($_GET['order_id']) || empty($_GET['order_id'])) {
    $_SESSION['message'] = "Không tìm thấy đơn hàng";
    header("Location: index.php");
    exit();
}

// Lấy thông tin đơn hàng
global $pdo;
$stmt = $pdo->prepare("SELECT * FROM orders WHERE id = ?");
$stmt->execute([$_GET['order_id']]);

if ($stmt->rowCount() == 0) {
    $_SESSION['message'] = "Đơn hàng không tồn tại";
    header("Location: index.php");
    exit();
}

$order = $stmt->fetch(PDO::FETCH_ASSOC);
$order_id = $order['id'];
$total_amount = $order['total_amount'];
?>

<style>
    @keyframes fadeInUp {
        from {
            opacity: 0;
            transform: translateY(30px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
    
    @keyframes pulse {
        0%, 100% {
            transform: scale(1);
        }
        50% {
            transform: scale(1.05);
        }
    }
    
    @keyframes slideIn {
        from {
            opacity: 0;
            transform: translateX(-20px);
        }
        to {
            opacity: 1;
            transform: translateX(0);
        }
    }
    
    .payment-container {
        max-width: 750px;
        margin: 40px auto;
        padding: 20px;
        animation: fadeInUp 0.6s ease;
    }
    
    .payment-card {
        background: linear-gradient(135deg, #1e3c72 0%, #2a5298 50%, #7e22ce 100%);
        padding: 0;
        border-radius: 25px;
        box-shadow: 0 20px 60px rgba(30, 60, 114, 0.4);
        overflow: hidden;
        position: relative;
    }
    
    .payment-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 200px;
        background: radial-gradient(circle at 50% 0%, rgba(255,255,255,0.1) 0%, transparent 70%);
        pointer-events: none;
    }
    
    .payment-header {
        text-align: center;
        padding: 40px 40px 30px;
        position: relative;
    }
    
    .success-badge {
        background: linear-gradient(135deg, #27ae60 0%, #229954 100%);
        color: white;
        padding: 12px 25px;
        border-radius: 30px;
        display: inline-flex;
        align-items: center;
        gap: 10px;
        margin-bottom: 20px;
        font-weight: bold;
        font-size: 16px;
        box-shadow: 0 4px 15px rgba(39, 174, 96, 0.3);
        animation: pulse 2s infinite;
    }
    
    .payment-header h2 {
        font-size: 32px;
        margin-bottom: 15px;
        color: white;
        font-weight: 700;
        text-shadow: 0 2px 10px rgba(0,0,0,0.1);
    }
    
    .payment-header p {
        font-size: 20px;
        color: white;
        background: rgba(255,255,255,0.2);
        padding: 10px 25px;
        border-radius: 20px;
        display: inline-block;
        backdrop-filter: blur(10px);
    }
    
    .payment-header p strong {
        font-weight: 700;
        font-size: 24px;
    }
    
    .bank-info-box {
        background: white;
        padding: 35px;
        margin: 0 30px 25px;
        border-radius: 20px;
        box-shadow: 0 10px 30px rgba(0,0,0,0.1);
    }
    
    .info-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 18px 0;
        border-bottom: 2px dashed #f0f0f0;
        animation: slideIn 0.5s ease;
    }
    
    .info-row:last-child {
        border-bottom: none;
    }
    
    .info-label {
        font-weight: 600;
        color: #555;
        font-size: 17px;
        display: flex;
        align-items: center;
        gap: 8px;
    }
    
    .info-value {
        font-size: 17px;
        color: #2c3e50;
        font-weight: 700;
        display: flex;
        align-items: center;
        gap: 12px;
        flex-wrap: wrap;
        justify-content: flex-end;
    }
    
    .info-value.highlight {
        color: #e74c3c;
        font-size: 28px;
        font-weight: 800;
    }
    
    .btn-copy {
        background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
        color: white;
        border: none;
        padding: 10px 18px;
        border-radius: 10px;
        cursor: pointer;
        font-size: 14px;
        font-weight: 600;
        transition: all 0.3s;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        box-shadow: 0 4px 15px rgba(30, 60, 114, 0.3);
    }
    
    .btn-copy:hover {
        transform: translateY(-3px);
        box-shadow: 0 6px 20px rgba(42, 82, 152, 0.5);
    }
    
    .btn-copy:active {
        transform: translateY(-1px);
    }
    
    .qr-container {
        text-align: center;
        background: white;
        padding: 35px;
        margin: 0 30px 25px;
        border-radius: 20px;
        box-shadow: 0 10px 30px rgba(0,0,0,0.1);
    }
    
    .qr-container h3 {
        color: #2c3e50;
        margin-bottom: 25px;
        font-size: 22px;
        font-weight: 700;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 10px;
    }
    
    .qr-code {
        background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
        padding: 25px;
        border-radius: 15px;
        display: inline-block;
        box-shadow: 0 8px 25px rgba(0,0,0,0.1);
        transition: transform 0.3s;
    }
    
    .qr-code:hover {
        transform: scale(1.02);
    }
    
    .qr-code img {
        max-width: 280px;
        width: 100%;
        display: block;
        border-radius: 10px;
    }
    
    .qr-container p {
        color: #7f8c8d;
        margin-top: 20px;
        font-size: 15px;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
    }
    
    .notice-box {
        background: rgba(255,255,255,0.15);
        padding: 25px;
        margin: 0 30px;
        border-radius: 15px;
        border-left: 5px solid #ffd700;
        backdrop-filter: blur(10px);
    }
    
    .notice-box h4 {
        color: white;
        margin-bottom: 18px;
        display: flex;
        align-items: center;
        gap: 10px;
        font-size: 18px;
        font-weight: 700;
    }
    
    .notice-box ul {
        margin: 0;
        padding-left: 25px;
    }
    
    .notice-box li {
        margin: 12px 0;
        color: white;
        line-height: 1.6;
        font-size: 15px;
    }
    
    .action-buttons {
        display: flex;
        gap: 15px;
        padding: 30px 30px 40px;
    }
    
    .btn-action {
        flex: 1;
        padding: 18px;
        border: 3px solid white;
        background: rgba(255,255,255,0.1);
        color: white;
        border-radius: 15px;
        font-size: 17px;
        font-weight: 700;
        cursor: pointer;
        transition: all 0.3s;
        text-decoration: none;
        text-align: center;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        backdrop-filter: blur(10px);
    }
    
    .btn-action:hover {
        background: white;
        color: #1e3c72;
        transform: translateY(-3px);
        box-shadow: 0 10px 25px rgba(0,0,0,0.2);
    }
    
    .btn-action i {
        font-size: 22px;
    }
    
    /* Responsive */
    @media (max-width: 768px) {
        .payment-container {
            padding: 15px;
            margin: 20px auto;
        }
        
        .bank-info-box, .qr-container, .notice-box {
            margin-left: 20px;
            margin-right: 20px;
            padding: 25px;
        }
        
        .action-buttons {
            flex-direction: column;
            padding: 25px 20px 30px;
        }
        
        .info-row {
            flex-direction: column;
            align-items: flex-start;
            gap: 10px;
        }
        
        .info-value {
            justify-content: flex-start;
        }
    }
</style>

<div class="bg-main">
    <div class="container">
        <div class="payment-container">
            <div class="payment-card">
                <div class="payment-header">
                    <div class="success-badge">
                        <i class='bx bxs-check-circle' style="font-size: 22px;"></i>
                        Đặt hàng thành công
                    </div>
                    <h2>
                        <i class='bx bxs-bank' style="font-size: 36px;"></i> 
                        Thông tin thanh toán
                    </h2>
                    <p>Mã đơn hàng: <strong>#<?= e($order_id) ?></strong></p>
                </div>
                
                <div class="bank-info-box">
                    <div class="info-row">
                        <span class="info-label">
                            <i class='bx bxs-bank' style="color: #1e3c72; font-size: 22px;"></i> 
                            Ngân hàng:
                        </span>
                        <span class="info-value">MB Bank (Ngân hàng Quân Đội)</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">
                            <i class='bx bxs-credit-card' style="color: #2a5298; font-size: 22px;"></i>
                            Số tài khoản:
                        </span>
                        <span class="info-value">
                            <strong style="color: #2c3e50;">0000955063080</strong>
                            <button type="button" class="btn-copy" onclick="copyToClipboard('0000955063080', this)">
                                <i class='bx bx-copy'></i> Copy
                            </button>
                        </span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">
                            <i class='bx bxs-user' style="color: #6366f1; font-size: 22px;"></i>
                            Chủ tài khoản:
                        </span>
                        <span class="info-value">Nguyen Tran Tuan Phat</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">
                            <i class='bx bxs-wallet' style="color: #e67e22; font-size: 22px;"></i>
                            Số tiền:
                        </span>
                        <span class="info-value highlight"><?= formatVND($total_amount) ?></span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">
                            <i class='bx bxs-message-square-edit' style="color: #e74c3c; font-size: 22px;"></i>
                            Nội dung chuyển khoản:
                        </span>
                        <span class="info-value">
                            <strong style="color: #e74c3c; font-size: 20px;">DH<?= e($order_id) ?></strong>
                            <button type="button" class="btn-copy" onclick="copyToClipboard('DH<?= ejs($order_id) ?>', this)">
                                <i class='bx bx-copy'></i> Copy
                            </button>
                        </span>
                    </div>
                </div>
                
                <div class="qr-container">
                    <h3>
                        <i class='bx bx-qr-scan' style="font-size: 28px; color: #1e3c72;"></i> 
                        Quét mã QR để chuyển khoản
                    </h3>
                    <div class="qr-code">
                        <img src="https://img.vietqr.io/image/MB-0000955063080-compact2.png?amount=<?= intval($total_amount) ?>&addInfo=DH<?= urlencode($order_id) ?>&accountName=NGUYEN%20TRAN%20TUAN%20PHAT" alt="QR Code">
                    </div>
                    <p>
                        <i class='bx bx-mobile' style="font-size: 18px;"></i> 
                        Mở app ngân hàng và quét mã QR để thanh toán
                    </p>
                </div>
                
                <div class="notice-box">
                    <h4>
                        <i class='bx bx-info-circle' style="font-size: 24px;"></i> 
                        Lưu ý quan trọng
                    </h4>
                    <ul>
                        <li>✅ Vui lòng chuyển khoản <strong>ĐÚNG SỐ TIỀN</strong>: <strong><?= formatVND($total_amount) ?></strong></li>
                        <li>✅ Vui lòng ghi <strong>ĐÚNG NỘI DUNG</strong>: <strong style="font-size: 18px;">DH<?= e($order_id) ?></strong></li>
                        <li>⏱️ Đơn hàng sẽ được xử lý sau khi chúng tôi xác nhận thanh toán (5-10 phút)</li>
                        <li>📞 Nếu có vấn đề, vui lòng liên hệ hotline: <strong>0906248107</strong></li>
                    </ul>
                </div>
                
                <div class="action-buttons">
                    <a href="index.php?page=cart-status" class="btn-action">
                        <i class='bx bxs-package'></i> Xem đơn hàng
                    </a>
                    <a href="index.php?page=home" class="btn-action">
                        <i class='bx bxs-home'></i> Về trang chủ
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    // Copy text to clipboard with animation
    function copyToClipboard(text, button) {
        navigator.clipboard.writeText(text).then(function() {
            const originalText = button.innerHTML;
            button.innerHTML = '<i class="bx bxs-check-circle"></i> Đã copy';
            button.style.background = 'linear-gradient(135deg, #27ae60 0%, #229954 100%)';
            button.style.transform = 'scale(1.05)';
            
            // Hiệu ứng rung nhẹ
            button.style.animation = 'pulse 0.3s ease';
            
            setTimeout(function() {
                button.innerHTML = originalText;
                button.style.background = 'linear-gradient(135deg, #1e3c72 0%, #2a5298 100%)';
                button.style.transform = 'scale(1)';
                button.style.animation = '';
            }, 2000);
        }).catch(function(err) {
            alert('Không thể copy: ' + err);
        });
    }
    
    // Animation khi trang load
    document.addEventListener('DOMContentLoaded', function() {
        const infoRows = document.querySelectorAll('.info-row');
        infoRows.forEach((row, index) => {
            row.style.animationDelay = (index * 0.1) + 's';
        });
    });
</script>

