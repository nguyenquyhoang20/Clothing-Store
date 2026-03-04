<?php 
$pageTitle = "Sửa danh mục - Admin NHÓM 10";

if(isset($_GET['id']))
{
    $id = $_GET['id'];
    $category = getByID("categories", $id);

    if(mysqli_num_rows($category) > 0)
    {
        $data = mysqli_fetch_array($category);
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
                    <h4 class="mb-0">Sửa danh mục</h4>
                    <a href="index.php?page=category" class="btn bg-gradient-primary btn-sm">Quay lại</a>
                </div>
                <div class="card-body">
                    <form action="code.php" method="POST" enctype="multipart/form-data">
                        <div class="row">
                            <input type="hidden" name="category_id" value="<?= $data['id']?>">
                            
                            <div class="col-md-12">
                                <label class="form-label"><b>Tên danh mục</b></label>
                                <input type="text" id="full-name" required value="<?= $data['name']?>" name="name" placeholder="Enter Category Name" class="form-control mb-3"> 
                            </div>                               
                            <div class="col-md-12">
                                <label class="form-label"><b>Slug</b></label>
                                <input type="text" id="slug-name" required value="<?= $data['slug']?>" name="slug" placeholder="Enter slug" class="form-control mb-3">
                            </div>
                            <input type="hidden" name="description" value="">
                            <div class="col-md-12">
                                <label class="form-label"><b>Ảnh</b></label>
                                <input type="file" name="image" class="form-control mb-2">
                                <label class="form-label">Ảnh hiện tại:</label>
                                <input type="hidden" name="old_image" value="<?= $data['image']?>">
                                <div>
                                    <img src="../images/<?= $data['image']?>" height="100px" width="100px" style="object-fit:cover;border-radius:8px;" alt="Category Image">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-check mt-3">
                                    <input class="form-check-input" type="checkbox" <?= $data['status'] ? "checked" : "" ?> name="status" id="status">
                                    <label class="form-check-label" for="status"><b>Ẩn danh mục</b></label>
                                </div>
                            </div>
                            <div class="col-md-12 mt-3">
                                <button type="submit" class="btn bg-gradient-primary" name="update_category_btn">Cập nhật</button>
                                <a href="index.php?page=category" class="btn btn-secondary">Hủy</a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript" src="./assets/js/StringConvertToSlug.js"></script>

<?php
    } else {
        echo '<div class="alert alert-danger">Không tìm thấy danh mục</div>';
    }
} else {
    echo '<div class="alert alert-danger">Thiếu ID danh mục</div>';
}
?>

