<?php 
$pageTitle = "Thêm sản phẩm - Admin NHÓM 10";
include("../functions/currency.php");
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
                <div class="card-header">
                    <h4>Thêm sản phẩm</h4>
                </div>
                <div class="card-body">
                    <form action="code.php" method="POST" enctype="multipart/form-data">
                        <div class="row">
                            <div class="col-md-12">
                                <label class="form-label">Danh mục</label>
                                <select name="category_id" class="form-select mb-2" required>
                                    <option value="">Chọn danh mục</option>
                                    <?php 
                                        include("../config/dbcon.php");
                                        $categories_query = "SELECT * FROM categories ORDER BY name ASC";
                                        $categories_result = mysqli_query($conn, $categories_query);
                                        
                                        if($categories_result && mysqli_num_rows($categories_result) > 0)
                                        {
                                            while($category = mysqli_fetch_array($categories_result))
                                            {
                                                echo '<option value="' . $category['id'] . '">' . $category['name'] . '</option>';
                                            }
                                        }
                                    ?>                                  
                                </select>
                            </div>
                            <div class="col-md-6">  
                                <label class="form-label">Tên sản phẩm</label>
                                <input type="text" id="full-name" required name="name" placeholder="Nhập tên sản phẩm" class="form-control mb-2 "> 
                            </div>                               
                            <div class="col-md-6">
                                <label class="form-label">Slug</label>
                                <input type="text" id="slug-name" required name="slug" placeholder="Nhập slug" class="form-control mb-2">
                            </div>
                            <div class="col-md-12">
                                <label class="form-label">Mô tả ngắn</label>
                                <textarea required name="small_description" placeholder="Nhập mô tả ngắn" class="form-control mb-2" rows="2"></textarea>
                            </div>                               
                            <div class="col-md-12">
                                <label class="form-label">Mô tả chi tiết</label>
                                <textarea required name="description" placeholder="Nhập mô tả chi tiết" class="form-control mb-2" rows="3"></textarea>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Giá gốc (VNĐ)</label>
                                <input type="text" required name="original_price" placeholder="Nhập giá gốc" class="form-control mb-2">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Giá bán (VNĐ)</label>
                                <input type="text" required name="selling_price" placeholder="Nhập giá bán" class="form-control mb-2">
                            </div>
                            <div class="col-md-12">
                                <label class="form-label">Ảnh chính</label>
                                <input type="file" name="image" class="form-control mb-2" required>
                            </div>
                            <div class="col-md-12">
                                <label class="form-label">Ảnh phụ (nhiều ảnh)</label>
                                <input type="file" name="product_images[]" class="form-control mb-2" multiple accept="image/*">
                            </div>
                            <div class="col-md-12">
                                <label class="form-label"><strong>Số lượng</strong></label>
                                <p class="text-muted small mb-2">Chọn danh mục để hiển thị số lượng theo size hoặc số lượng tổng</p>
                                <div id="size-quantity-container" class="p-3 rounded" style="background: white; border: 2px dashed #ddd;">
                                    <p class="text-center text-muted">Vui lòng chọn danh mục trước</p>
                                </div>
                                <!-- Trường số lượng tổng (ẩn mặc định, sẽ hiển thị khi sản phẩm không có size) -->
                                <div id="total-quantity-container" style="display: none;">
                                    <label class="form-label">Số lượng tổng</label>
                                    <input type="number" name="qty" value="0" min="0" placeholder="Nhập số lượng tổng" class="form-control mb-2" id="total-qty-input">
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="form-check mt-4">
                                    <input class="form-check-input" type="checkbox" name="status" id="status">
                                    <label class="form-check-label" for="status">Hiển thị</label>
                                </div>
                            </div>
                            <div class="col-md-12 mt-3">
                                <button type="submit" class="btn bg-gradient-primary" name="add_product_btn">Thêm sản phẩm</button>
                                <a href="index.php?page=products" class="btn btn-secondary">Quay lại</a>
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

// Hiển thị input số lượng cho từng size
function displaySizeInputs(sizeInfo) {
    const container = document.getElementById('size-quantity-container');
    const totalQtyContainer = document.getElementById('total-quantity-container');
    const totalQtyInput = document.getElementById('total-qty-input');
    
    if (sizeInfo.type === 'none' || sizeInfo.sizes.length === 0) {
        // Sản phẩm không có size - hiển thị trường số lượng tổng
        container.innerHTML = '<p class="text-center text-muted mb-0">Sản phẩm này không có phân loại size</p>';
        totalQtyContainer.style.display = 'block';
        if (totalQtyInput) {
            totalQtyInput.required = true;
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
        html += `
            <div class="col-md-3 col-sm-4 col-6 mb-3">
                <label class="form-label"><strong>Size ${size}</strong></label>
                <input type="number" 
                       name="size_quantities[${size}]" 
                       value="0" 
                       min="0" 
                       placeholder="Số lượng" 
                       class="form-control"
                       id="size-${size}">
            </div>
        `;
    });
    
    html += '</div>';
    html += '<div class="mt-2 mb-0 p-2" style="background: #f8f9fa; border-left: 3px solid #6c757d; color: #495057; border-radius: 4px;"><i class="fas fa-info-circle"></i> Nhập số lượng tồn kho cho từng size. Để 0 nếu size đó hết hàng.</div>';
    
    container.innerHTML = html;
}

// Lắng nghe sự kiện thay đổi category
document.addEventListener('DOMContentLoaded', function() {
    const categorySelect = document.querySelector('select[name="category_id"]');
    
    categorySelect.addEventListener('change', function() {
        const selectedOption = this.options[this.selectedIndex];
        const categoryName = selectedOption.text;
        
        if (this.value) {
            const sizeInfo = determineSizeType(categoryName);
            displaySizeInputs(sizeInfo);
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
});
</script>

