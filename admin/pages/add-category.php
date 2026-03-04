<?php 
$pageTitle = "Thêm danh mục - Admin NHÓM 10";
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
                    <h4>Thêm danh mục</h4>
                </div>
                <div class="card-body">
                    <form action="code.php" method="POST" enctype="multipart/form-data">
                        <div class="row">
                            <div class="col-md-12">
                                <label class="form-label"><b>Tên danh mục</b></label>
                                <input type="text" id="full-name" required name="name" placeholder="Nhập tên danh mục" class="form-control mb-2"> 
                                <div id="name-check-message" style="font-size: 13px; margin-top: 5px; margin-bottom: 10px;"></div>
                            </div>                               
                            <div class="col-md-12">
                                <label class="form-label"><b>Slug</b></label>
                                <input type="text" id="slug-name" required name="slug" placeholder="Nhập slug" class="form-control mb-3">
                            </div>
                            <input type="hidden" name="description" value="">
                            <div class="col-md-12">
                                <label class="form-label"><b>Ảnh</b></label>
                                <input type="file" required name="image" class="form-control mb-3">
                            </div>
                            <div class="col-md-6">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="status" id="status">
                                    <label class="form-check-label" for="status"><b>Hiển thị</b></label>
                                </div>
                            </div>
                            <div class="col-md-12 mt-3">
                                <button type="submit" class="btn bg-gradient-primary" name="add_category_btn">Lưu danh mục</button>
                                <a href="index.php?page=category" class="btn btn-secondary">Quay lại</a>
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
// Kiểm tra tên danh mục đã tồn tại chưa
document.getElementById('full-name').addEventListener('blur', function() {
    const categoryName = this.value.trim();
    const messageDiv = document.getElementById('name-check-message');
    const submitBtn = document.querySelector('button[name="add_category_btn"]');
    
    if (categoryName === '') {
        messageDiv.innerHTML = '';
        submitBtn.disabled = false;
        return;
    }
    
    // Gọi AJAX kiểm tra
    fetch('check_category_name.php?name=' + encodeURIComponent(categoryName))
        .then(response => response.json())
        .then(data => {
            if (data.exists) {
                messageDiv.innerHTML = '<span style="color: #dc3545;"><i class="fas fa-exclamation-triangle"></i> Tên danh mục "' + categoryName + '" đã tồn tại! Vui lòng đặt tên khác như: "' + categoryName + ' Mùa đông", "' + categoryName + ' Nam", v.v.</span>';
                submitBtn.disabled = true;
            } else {
                messageDiv.innerHTML = '<span style="color: #28a745;"><i class="fas fa-check-circle"></i> Tên danh mục có thể sử dụng!</span>';
                submitBtn.disabled = false;
            }
        })
        .catch(error => {
            console.error('Error:', error);
            messageDiv.innerHTML = '';
            submitBtn.disabled = false;
        });
});
</script>

