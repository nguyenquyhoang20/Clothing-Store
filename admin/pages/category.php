<?php 
// ============================================
// TRANG DANH MỤC - CHỈ CONTENT
// ============================================
$pageTitle = "Danh mục - Admin NHÓM 10";

// Lấy tham số phân trang
$page = isset($_GET['page_num']) ? (int)$_GET['page_num'] : 1;
$limit = 10; // Số danh mục mỗi trang
$offset = ($page - 1) * $limit;

// Query đếm tổng số danh mục
$count_query = "SELECT COUNT(*) as total FROM categories";
$count_result = mysqli_query($conn, $count_query);
$count_row = mysqli_fetch_assoc($count_result);
$total_categories = $count_row['total'];
$total_pages = ceil($total_categories / $limit);

// Query lấy danh mục với phân trang
$category_query = "SELECT * FROM categories ORDER BY id DESC LIMIT $limit OFFSET $offset";
$category = mysqli_query($conn, $category_query);
?>

<div class="container-fluid py-4">
    <div class="row">
        <div class="col-md-12">
            <?php 
            // Hiển thị thông báo từ session
            if (function_exists('getSessionMessage')) {
                $flash = getSessionMessage();
                if ($flash): ?>
                    <div class="alert alert-<?= $flash['type']; ?> alert-dismissible fade show" role="alert">
                        <?= htmlspecialchars($flash['message']); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif;
            }
            ?>
            
            <div class="card shadow border-0" style="border-radius:16px;">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="mb-0">Categories (<?= $total_categories ?>)</h4>
                    <a href="index.php?page=add-category" class="btn bg-gradient-primary btn-sm">Thêm danh mục</a>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Name</th>
                                    <th>Image</th>
                                    <th>Status</th>
                                    <th>Edit</th>
                                    <th>Delete</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                    if(mysqli_num_rows($category) > 0)
                                    {
                                        while($item = mysqli_fetch_array($category))
                                        {
                                        ?>
                                            <tr id="category-row-<?= $item['id'];?>">
                                                <td><?= $item['id'];?> </td>
                                                <td><?= $item['name'];?></td>
                                                <td>
                                                    <img src="../images/<?= $item['image']; ?>" width="50" height="50" style="object-fit:cover;border-radius:8px;" alt="<?= $item['name'];?>">
                                                </td>
                                                <td>
                                                    <?= $item['status'] == '0' ? "Visible":"Hidden"?>
                                                </td> 
                                                <td>
                                                    <a href="index.php?page=edit-category&id=<?= $item['id'];?>" class="btn bg-gradient-primary btn-sm">Edit</a>                                 
                                                </td>
                                                <td>
                                                    <button type="button" onclick="deleteCategory(<?= $item['id']; ?>, '<?= htmlspecialchars($item['name']); ?>')" class="btn bg-gradient-danger btn-sm">Delete</button>
                                                </td>                      
                                            </tr>
                                        <?php
                                        }
                                    }
                                    else
                                    {
                                        echo "<tr><td colspan='6' class='text-center'>No Record Found</td></tr>";
                                    }
                                ?>
                            </tbody>
                        </table>
                    </div>

                    <!-- Phân trang -->
                    <?php if ($total_pages > 1): ?>
                    <div class="d-flex justify-content-center mt-4">
                        <nav aria-label="Page navigation">
                            <ul class="pagination">
                                <!-- Nút Previous -->
                                <?php if ($page > 1): ?>
                                <li class="page-item">
                                    <a class="page-link" href="index.php?page=category&page_num=<?= $page - 1; ?>" aria-label="Previous">
                                        <span aria-hidden="true">&laquo;</span>
                                    </a>
                                </li>
                                <?php else: ?>
                                <li class="page-item disabled">
                                    <span class="page-link">&laquo;</span>
                                </li>
                                <?php endif; ?>

                                <!-- Các số trang -->
                                <?php
                                $start_page = max(1, $page - 2);
                                $end_page = min($total_pages, $page + 2);
                                
                                if ($start_page > 1): ?>
                                    <li class="page-item">
                                        <a class="page-link" href="index.php?page=category&page_num=1">1</a>
                                    </li>
                                    <?php if ($start_page > 2): ?>
                                    <li class="page-item disabled"><span class="page-link">...</span></li>
                                    <?php endif; ?>
                                <?php endif; ?>

                                <?php for ($i = $start_page; $i <= $end_page; $i++): ?>
                                <li class="page-item <?= $i == $page ? 'active' : ''; ?>">
                                    <a class="page-link" href="index.php?page=category&page_num=<?= $i; ?>"><?= $i; ?></a>
                                </li>
                                <?php endfor; ?>

                                <?php if ($end_page < $total_pages): ?>
                                    <?php if ($end_page < $total_pages - 1): ?>
                                    <li class="page-item disabled"><span class="page-link">...</span></li>
                                    <?php endif; ?>
                                    <li class="page-item">
                                        <a class="page-link" href="index.php?page=category&page_num=<?= $total_pages; ?>"><?= $total_pages; ?></a>
                                    </li>
                                <?php endif; ?>

                                <!-- Nút Next -->
                                <?php if ($page < $total_pages): ?>
                                <li class="page-item">
                                    <a class="page-link" href="index.php?page=category&page_num=<?= $page + 1; ?>" aria-label="Next">
                                        <span aria-hidden="true">&raquo;</span>
                                    </a>
                                </li>
                                <?php else: ?>
                                <li class="page-item disabled">
                                    <span class="page-link">&raquo;</span>
                                </li>
                                <?php endif; ?>
                            </ul>
                        </nav>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Thông báo động -->
<div id="alert-container" style="position: fixed; top: 20px; right: 20px; z-index: 9999; max-width: 400px;"></div>

<script>
// Hàm hiển thị thông báo
function showAlert(message, type = 'success') {
    const alertContainer = document.getElementById('alert-container');
    const alertId = 'alert-' + Date.now();
    
    const alertHTML = `
        <div id="${alertId}" class="alert alert-${type} alert-dismissible fade show" role="alert" style="box-shadow: 0 4px 12px rgba(0,0,0,0.15);">
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    `;
    
    alertContainer.insertAdjacentHTML('beforeend', alertHTML);
    
    // Tự động ẩn sau 5 giây
    setTimeout(() => {
        const alertElement = document.getElementById(alertId);
        if (alertElement) {
            alertElement.classList.remove('show');
            setTimeout(() => alertElement.remove(), 150);
        }
    }, 5000);
}

// Hàm xóa danh mục
function deleteCategory(categoryId, categoryName) {
    // Xác nhận trước khi xóa
    if (!confirm(`Bạn có chắc chắn muốn xóa danh mục "${categoryName}"?`)) {
        return;
    }
    
    // Tạo FormData
    const formData = new FormData();
    formData.append('category_id', categoryId);
    formData.append('delete_category_btn', '1');
    formData.append('ajax_request', '1'); // Đánh dấu là AJAX request
    
    // Gửi request
    fetch('code.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Hiển thị thông báo thành công
            showAlert(data.message, 'success');
            
            // Xóa dòng khỏi bảng với hiệu ứng
            const row = document.getElementById('category-row-' + categoryId);
            if (row) {
                row.style.transition = 'opacity 0.5s';
                row.style.opacity = '0';
                setTimeout(() => row.remove(), 500);
            }
        } else {
            // Hiển thị thông báo lỗi
            showAlert(data.message, 'danger');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showAlert('Có lỗi xảy ra. Vui lòng thử lại!', 'danger');
    });
}
</script>

