<?php 
$pageTitle = "Sửa sản phẩm - Admin NHÓM 10";
include("../functions/currency.php");

if(isset($_GET['id']))
{
    $id = $_GET['id'];
    $product = getByID("products", $id);
    
    if(mysqli_num_rows($product) > 0)
    {
        $data = mysqli_fetch_array($product);
?>

<div class="container-fluid py-4">   
    <div class="row">
        <div class="col-md-12">
            <?php if(isset($_GET['success'])): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <?= htmlspecialchars($_GET['success']); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>
            <?php if(isset($_GET['error'])): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <?= htmlspecialchars($_GET['error']); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>
            
            <div class="card shadow border-0" style="border-radius:16px;">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="mb-0">Sửa sản phẩm</h4>
                    <a href="index.php?page=products" class="btn bg-gradient-primary btn-sm">Quay lại</a>
                </div>
                <div class="card-body">
                    <form action="code.php" method="POST" enctype="multipart/form-data">
                        <div class="row">
                            <div class="col-md-12">
                                <label class="form-label"><b>Danh mục</b></label>
                                <select name="category_id" class="form-select mb-2" required>
                                    <option value="">Chọn danh mục</option>
                                    <?php 
                                        $categories = getAll("categories");
                                        if(mysqli_num_rows($categories) > 0)
                                        {
                                            foreach($categories as $item)
                                            {
                                    ?>
                                                <option value="<?= $item['id']; ?>" <?= $data['category_id'] == $item['id'] ? 'selected' : '' ?>><?= $item['name']?></option>
                                    <?php
                                            }
                                        }
                                    ?>                                  
                                </select>
                            </div>
                            
                            <input type="hidden" name="product_id" value="<?= $data['id']; ?>">
                            
                            <div class="col-md-6">
                                <label class="form-label"><b>Tên sản phẩm</b></label>
                                <input type="text" id="full-name" required name="name" value="<?= $data['name']; ?>" placeholder="Enter Product Name" class="form-control mb-2"> 
                            </div>                               
                            <div class="col-md-6">
                                <label class="form-label"><b>Slug</b></label>
                                <input type="text" id="slug-name" required name="slug" value="<?= $data['slug']; ?>" placeholder="Enter slug" class="form-control mb-2">
                            </div>
                            <div class="col-md-12">
                                <label class="form-label"><b>Mô tả ngắn</b></label>
                                <textarea required name="small_description" placeholder="Enter Small Description" class="form-control mb-2" rows="2"><?= $data['small_description']; ?></textarea>
                            </div>                               
                            <div class="col-md-12">
                                <label class="form-label"><b>Mô tả chi tiết</b></label>
                                <textarea required name="description" placeholder="Enter Description" class="form-control mb-2" rows="3"><?= $data['description']; ?></textarea>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label"><b>Giá gốc (VNĐ)</b></label>
                                <input type="text" required name="original_price" value="<?= formatNumber($data['original_price']); ?>" placeholder="Nhập giá gốc" class="form-control mb-2">
                                <small class="text-muted">Hiển thị: <?= formatVND($data['original_price']) ?></small>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label"><b>Giá bán (VNĐ)</b></label>
                                <input type="text" required name="selling_price" value="<?= formatNumber($data['selling_price']); ?>" placeholder="Nhập giá bán" class="form-control mb-2">
                                <small class="text-muted">Hiển thị: <?= formatVND($data['selling_price']) ?></small>
                            </div>
                            <div class="col-md-12">
                                <label class="form-label"><b>Ảnh sản phẩm</b></label>
                                <input type="file" name="image" class="form-control mb-2">
                                <label class="form-label">Ảnh hiện tại:</label>
                                <input type="hidden" name="old_image" value="<?= $data['image']?>">
                                <div>
                                    <img src="../images/<?= $data['image']?>" height="100px" width="100px" style="object-fit:cover;border-radius:8px;" alt="Product Image">
                                </div>
                            </div>
                            <div class="col-md-12">
                                <label class="form-label"><strong>Số lượng</strong></label>
                                <p class="text-muted small mb-2">Chỉnh sửa số lượng tồn kho cho từng size hoặc số lượng tổng</p>
                                <div id="size-quantity-container" class="p-3 rounded" style="background: white; border: 2px dashed #ddd;">
                                    <!-- Sẽ load bằng JavaScript -->
                                </div>
                                <!-- Trường số lượng tổng (ẩn mặc định, sẽ hiển thị khi sản phẩm không có size) -->
                                <div id="total-quantity-container" style="display: none;">
                                    <label class="form-label">Số lượng tổng</label>
                                    <input type="number" name="qty" value="<?= $data['qty'] ?? 0 ?>" min="0" placeholder="Nhập số lượng tổng" class="form-control mb-2" id="total-qty-input">
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="form-check mt-4">
                                    <input class="form-check-input" type="checkbox" name="status" id="status" <?= $data['status'] == '0' ? '' : 'checked' ?>>
                                    <label class="form-check-label" for="status"><b>Ẩn sản phẩm</b></label>
                                </div>
                            </div>
                            <div class="col-md-12 mt-3">
                                <button type="submit" class="btn bg-gradient-primary" name="update_product_btn">Cập nhật</button>
                                <a href="index.php?page=products" class="btn btn-secondary">Hủy</a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript" src="./assets/js/StringConvertToSlug.js"></script>
<script>
// Định dạng giá tiền khi nhập
document.addEventListener('DOMContentLoaded', function() {
    const priceInputs = document.querySelectorAll('input[name="original_price"], input[name="selling_price"]');
    
    priceInputs.forEach(function(input) {
        // Lưu giá trị gốc khi load trang
        let originalValue = input.value.replace(/[^\d]/g, '');
        
        // Định dạng khi mất focus
        input.addEventListener('blur', function() {
            let value = this.value.replace(/[^\d]/g, ''); // Chỉ giữ lại số
            if (value) {
                // Định dạng với dấu chấm phân cách hàng nghìn
                this.value = parseInt(value).toLocaleString('vi-VN');
                originalValue = value;
            }
        });
        
        // Loại bỏ định dạng khi focus để dễ chỉnh sửa
        input.addEventListener('focus', function() {
            this.value = originalValue || this.value.replace(/[^\d]/g, '');
        });
    });
    
    // Load size quantities cho sản phẩm
    loadProductSizes();
});

// Xác định loại size dựa trên tên danh mục
function determineSizeType(categoryName) {
    const name = categoryName.toLowerCase();
    
    // Keywords cho quần áo (S, M, L)
    const clothingKeywords = ['áo', 'ao', 'quần', 'quan', 'đầm', 'dam', 'váy', 'vay', 'shirt', 'pant', 'dress'];
    for (let keyword of clothingKeywords) {
        if (name.includes(keyword)) {
            return {type: 'clothing', sizes: ['S', 'M', 'L', 'XL']};
        }
    }
    
    // Keywords cho giày dép (sizes số)
    const shoeKeywords = ['giày', 'giay', 'dép', 'dep', 'sandal', 'shoe'];
    for (let keyword of shoeKeywords) {
        if (name.includes(keyword)) {
            return {type: 'shoes', sizes: ['36', '37', '38', '39', '40', '41', '42', '43', '44']};
        }
    }
    
    // Keywords cho sản phẩm không có size
    const noSizeKeywords = ['balo', 'túi', 'tui', 'cặp', 'cap', 'ví', 'vi', 'bag', 'wallet', 'phụ kiện', 'phu kien'];
    for (let keyword of noSizeKeywords) {
        if (name.includes(keyword)) {
            return {type: 'none', sizes: []};
        }
    }
    
    return {type: 'none', sizes: []};
}

// Load size quantities hiện tại của sản phẩm
function loadProductSizes() {
    <?php
    // Lấy tên category hiện tại
    $current_category_query = "SELECT name FROM categories WHERE id = '{$data['category_id']}'";
    $current_category_result = mysqli_query($conn, $current_category_query);
    $current_category = mysqli_fetch_array($current_category_result);
    $category_name = $current_category['name'];
    
    // Lấy số lượng từng size
    $sizes_query = "SELECT size, quantity FROM product_sizes WHERE product_id = '{$data['id']}'";
    $sizes_result = mysqli_query($conn, $sizes_query);
    $current_sizes = [];
    while($size_row = mysqli_fetch_array($sizes_result)) {
        $current_sizes[$size_row['size']] = $size_row['quantity'];
    }
    
    // Lấy số lượng tổng từ products
    $current_qty = $data['qty'] ?? 0;
    ?>
    
    const categoryName = "<?= $category_name ?>";
    const currentSizes = <?= json_encode($current_sizes) ?>;
    const currentQty = <?= $current_qty ?>;
    
    const sizeInfo = determineSizeType(categoryName);
    displaySizeInputs(sizeInfo, currentSizes, currentQty);
}

// Hiển thị input số lượng cho từng size
function displaySizeInputs(sizeInfo, currentSizes = {}, currentQty = 0) {
    const container = document.getElementById('size-quantity-container');
    const totalQtyContainer = document.getElementById('total-quantity-container');
    const totalQtyInput = document.getElementById('total-qty-input');
    
    if (sizeInfo.type === 'none' || sizeInfo.sizes.length === 0) {
        // Sản phẩm không có size - hiển thị trường số lượng tổng
        container.innerHTML = '<p class="text-center text-muted mb-0">Sản phẩm này không có phân loại size</p>';
        totalQtyContainer.style.display = 'block';
        if (totalQtyInput) {
            totalQtyInput.required = true;
            if (currentQty > 0) {
                totalQtyInput.value = currentQty;
            }
        }
        return;
    }
    
    // Sản phẩm có size - ẩn trường số lượng tổng và hiển thị số lượng theo size
    totalQtyContainer.style.display = 'none';
    if (totalQtyInput) {
        totalQtyInput.required = false;
        totalQtyInput.value = '0';
    }
    
    let html = '<div class="row">';
    
    sizeInfo.sizes.forEach((size, index) => {
        const quantity = currentSizes[size] || 0;
        html += `
            <div class="col-md-3 col-sm-4 col-6 mb-3">
                <label class="form-label"><strong>Size ${size}</strong></label>
                <input type="number" 
                       name="size_quantities[${size}]" 
                       value="${quantity}" 
                       min="0" 
                       placeholder="Số lượng" 
                       class="form-control"
                       id="size-${size}">
            </div>
        `;
    });
    
    html += '</div>';
    html += '<div class="mt-2 mb-0 p-2" style="background: #f8f9fa; border-left: 3px solid #6c757d; color: #495057; border-radius: 4px;"><i class="fas fa-info-circle"></i> Cập nhật số lượng tồn kho cho từng size. Để 0 nếu size đó hết hàng.</div>';
    
    container.innerHTML = html;
}

// Lắng nghe sự kiện thay đổi category
const categorySelect = document.querySelector('select[name="category_id"]');
categorySelect.addEventListener('change', function() {
    const selectedOption = this.options[this.selectedIndex];
    const categoryName = selectedOption.text;
    
    if (this.value) {
        const sizeInfo = determineSizeType(categoryName);
        const totalQtyInput = document.getElementById('total-qty-input');
        const currentQty = totalQtyInput ? parseInt(totalQtyInput.value) || 0 : 0;
        displaySizeInputs(sizeInfo, {}, currentQty);
    } else {
        document.getElementById('size-quantity-container').innerHTML = 
            '<p class="text-center text-muted">Vui lòng chọn danh mục trước</p>';
        document.getElementById('total-quantity-container').style.display = 'none';
        const totalQtyInput = document.getElementById('total-qty-input');
        if (totalQtyInput) {
            totalQtyInput.required = false;
        }
    }
});
</script>

<?php
    } else {
        echo '<div class="alert alert-danger">Không tìm thấy sản phẩm</div>';
    }
} else {
    echo '<div class="alert alert-danger">Thiếu ID sản phẩm</div>';
}
?>

