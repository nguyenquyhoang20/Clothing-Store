<?php 
// ============================================
// TRANG DASHBOARD ADMIN/NHÂN VIÊN - CHỈ CONTENT
// ============================================
$user_role = isset($_SESSION['auth_user']['role_as']) ? $_SESSION['auth_user']['role_as'] : 1;
$is_admin = ($user_role == 1);
$pageTitle = $is_admin ? "Dashboard - Admin NHÓM 10" : "Dashboard - Nhân viên NHÓM 10";
$orderSummary = getOrderStatusSummary();
$completedOrders = $orderSummary['4'] ?? 0;
$cancelledOrders = $orderSummary['5'] ?? 0;
$failedOrders = $orderSummary['6'] ?? 0;
$topProducts = getTopSellingProducts(5);
?>

<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <div class="card shadow border-0" style="border-radius:16px;">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="mb-0">Dashboard</h4>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                    <?php if($is_admin): ?>
                    <div class="col-xl-3 col-md-6">
                        <div class="stats-card p-4" style="background:#fff;border-radius:16px;box-shadow:0 5px 15px rgba(0,0,0,.08)">
                            <div class="d-flex align-items-center gap-3">
                                <div class="stats-icon users" style="width:48px;height:48px;border-radius:12px;background:linear-gradient(135deg,#0ea5e9,#0284c7);display:flex;align-items:center;justify-content:center;color:#fff"><i class="fas fa-users"></i></div>
                                <div>
                                    <div class="fw-bold fs-4"><?= totalValue('users') ?></div>
                                    <div class="text-muted small">Tổng người dùng</div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>
                    <div class="<?= $is_admin ? 'col-xl-3' : 'col-xl-4' ?> col-md-6">
                        <div class="stats-card p-4" style="background:#fff;border-radius:16px;box-shadow:0 5px 15px rgba(0,0,0,.08)">
                            <div class="d-flex align-items-center gap-3">
                                <div class="stats-icon products" style="width:48px;height:48px;border-radius:12px;background:linear-gradient(135deg,#ef4444,#f97316);display:flex;align-items:center;justify-content:center;color:#fff"><i class="fas fa-box"></i></div>
                                <div>
                                    <div class="fw-bold fs-4"><?= totalValue('products') ?></div>
                                    <div class="text-muted small">Tổng sản phẩm</div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="<?= $is_admin ? 'col-xl-3' : 'col-xl-4' ?> col-md-6">
                        <div class="stats-card p-4" style="background:#fff;border-radius:16px;box-shadow:0 5px 15px rgba(0,0,0,.08)">
                            <div class="d-flex align-items-center gap-3">
                                <div class="stats-icon categories" style="width:48px;height:48px;border-radius:12px;background:linear-gradient(135deg,#10b981,#059669);display:flex;align-items:center;justify-content:center;color:#fff"><i class="fas fa-tags"></i></div>
                                <div>
                                    <div class="fw-bold fs-4"><?= totalValue('categories') ?></div>
                                    <div class="text-muted small">Danh mục</div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="<?= $is_admin ? 'col-xl-3' : 'col-xl-4' ?> col-md-6">
                        <div class="stats-card p-4" style="background:#fff;border-radius:16px;box-shadow:0 5px 15px rgba(0,0,0,.08)">
                            <div class="d-flex align-items-center gap-3">
                                <div class="stats-icon orders" style="width:48px;height:48px;border-radius:12px;background:linear-gradient(135deg,#8b5cf6,#7c3aed);display:flex;align-items:center;justify-content:center;color:#fff"><i class="fas fa-shopping-cart"></i></div>
                                <div>
                                    <div class="fw-bold fs-4"><?= totalValue('orders') ?></div>
                                    <div class="text-muted small">Tổng đơn hàng</div>
                                </div>
                            </div>
                        </div>
                    </div>
                    </div>

                    <div class="row g-3 mt-1">
                        <div class="col-xl-4 col-md-6">
                            <div class="stats-card p-4 h-100" style="background:#fff;border-radius:16px;box-shadow:0 5px 15px rgba(0,0,0,.08)">
                                <div class="d-flex align-items-center gap-3">
                                    <div class="stats-icon completed" style="width:48px;height:48px;border-radius:12px;background:linear-gradient(135deg,#34d399,#059669);display:flex;align-items:center;justify-content:center;color:#fff">
                                        <i class="fas fa-check-circle"></i>
                                    </div>
                                    <div>
                                        <div class="fw-bold fs-4"><?= number_format($completedOrders); ?></div>
                                        <div class="text-muted small">Đơn hàng hoàn thành</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-4 col-md-6">
                            <div class="stats-card p-4 h-100" style="background:#fff;border-radius:16px;box-shadow:0 5px 15px rgba(0,0,0,.08)">
                                <div class="d-flex align-items-center gap-3">
                                    <div class="stats-icon cancelled" style="width:48px;height:48px;border-radius:12px;background:linear-gradient(135deg,#f87171,#ef4444);display:flex;align-items:center;justify-content:center;color:#fff">
                                        <i class="fas fa-times-circle"></i>
                                    </div>
                                    <div>
                                        <div class="fw-bold fs-4"><?= number_format($cancelledOrders); ?></div>
                                        <div class="text-muted small">Đơn hàng đã hủy</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-4 col-md-12">
                            <div class="stats-card p-4 h-100" style="background:#fff;border-radius:16px;box-shadow:0 5px 15px rgba(0,0,0,.08)">
                                <div class="d-flex align-items-center gap-3">
                                    <div class="stats-icon failed" style="width:48px;height:48px;border-radius:12px;background:linear-gradient(135deg,#fbbf24,#f59e0b);display:flex;align-items:center;justify-content:center;color:#fff">
                                        <i class="fas fa-exclamation-triangle"></i>
                                    </div>
                                    <div>
                                        <div class="fw-bold fs-4"><?= number_format($failedOrders); ?></div>
                                        <div class="text-muted small">Đơn thất bại</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row mt-4">
        <div class="col-12">
            <div class="card shadow border-0" style="border-radius:16px;">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="fas fa-fire me-2 text-warning"></i>Sản phẩm bán chạy</h5>
                    <span class="text-muted small">Dựa trên đơn hàng hoàn thành</span>
                </div>
                <div class="card-body">
                    <?php if($topProducts && mysqli_num_rows($topProducts) > 0): ?>
                        <div class="table-responsive">
                            <table class="table align-middle">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Sản phẩm</th>
                                        <th>Số lượng đã bán</th>
                                        <th>Doanh thu</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php $rank = 1; ?>
                                    <?php while($product = mysqli_fetch_assoc($topProducts)): ?>
                                        <tr>
                                            <td><span class="badge bg-gradient-primary"><?= $rank++; ?></span></td>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div class="me-3" style="width:60px;height:60px;border-radius:14px;overflow:hidden;background:#f8fafc;">
                                                        <img src="../images/<?= htmlspecialchars($product['image']); ?>" alt="" style="width:100%;height:100%;object-fit:cover;">
                                                    </div>
                                                    <div>
                                                        <div class="fw-semibold"><?= htmlspecialchars($product['name']); ?></div>
                                                        <div class="text-muted small">Mã sản phẩm: #<?= $product['id']; ?></div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td><strong><?= number_format($product['total_quantity']); ?></strong></td>
                                            <td><?= number_format($product['total_revenue'], 0, ',', '.'); ?> VNĐ</td>
                                        </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="text-center text-muted py-4">
                            <i class="fas fa-box-open fa-2x mb-2"></i>
                            <p>Chưa có dữ liệu bán chạy. Hãy hoàn tất đơn hàng để thống kê.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal: Add Category -->
<div class="modal fade" id="addCategoryModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content" style="border-radius:16px;">
      <div class="modal-header">
        <h5 class="modal-title">Thêm danh mục</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form action="code.php" method="POST" enctype="multipart/form-data">
        <div class="modal-body">
          <div class="row g-3">
            <div class="col-md-6">
              <label class="form-label">Tên danh mục</label>
              <input type="text" class="form-control" name="name" id="full-name" required placeholder="Nhập tên danh mục">
            </div>
            <div class="col-md-6">
              <label class="form-label">Slug</label>
              <input type="text" class="form-control" name="slug" id="slug-name" required placeholder="nhap-slug">
            </div>
            <div class="col-12">
              <label class="form-label">Mô tả</label>
              <input type="text" class="form-control" name="description" required placeholder="Nhập mô tả">
            </div>
            <div class="col-md-6">
              <label class="form-label">Ảnh</label>
              <input type="file" class="form-control" name="image" required>
            </div>
            <div class="col-md-6 d-flex align-items-end">
              <div class="form-check">
                <input class="form-check-input" type="checkbox" name="status" id="catStatus">
                <label class="form-check-label" for="catStatus">Hiển thị</label>
              </div>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
          <button type="submit" name="add_category_btn" class="btn bg-gradient-primary">Lưu danh mục</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Modal: Add Product -->
<div class="modal fade" id="addProductModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-xl modal-dialog-centered">
    <div class="modal-content" style="border-radius:16px;">
      <div class="modal-header">
        <h5 class="modal-title">Thêm sản phẩm</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form action="code.php" method="POST" enctype="multipart/form-data">
        <div class="modal-body">
          <div class="row g-3">
            <div class="col-md-6">
              <label class="form-label">Danh mục</label>
              <select name="category_id" class="form-select" required>
                <option value="">Chọn danh mục</option>
                <?php 
                $categories= getAll("categories");
                if($categories && mysqli_num_rows($categories)>0){
                    foreach($categories as $item){ ?>
                        <option value="<?= $item['id']; ?>"><?= $item['name']; ?></option>
                <?php }} ?>
              </select>
            </div>
            <div class="col-md-6">
              <label class="form-label">Tên sản phẩm</label>
              <input type="text" class="form-control" name="name" id="full-name-p" required placeholder="Nhập tên sản phẩm">
            </div>
            <div class="col-md-6">
              <label class="form-label">Slug</label>
              <input type="text" class="form-control" name="slug" id="slug-name-p" required placeholder="nhap-slug">
            </div>
            <div class="col-12">
              <label class="form-label">Mô tả ngắn</label>
              <textarea class="form-control" name="small_description" rows="2" required></textarea>
            </div>
            <div class="col-12">
              <label class="form-label">Mô tả chi tiết</label>
              <textarea class="form-control" name="description" rows="3" required></textarea>
            </div>
            <div class="col-md-6">
              <label class="form-label">Giá gốc</label>
              <input type="text" class="form-control" name="original_price" required>
            </div>
            <div class="col-md-6">
              <label class="form-label">Giá bán</label>
              <input type="text" class="form-control" name="selling_price" required>
            </div>
            <div class="col-md-6">
              <label class="form-label">Ảnh chính</label>
              <input type="file" class="form-control" name="image" required>
            </div>
            <div class="col-md-6">
              <label class="form-label">Ảnh phụ (nhiều ảnh)</label>
              <input type="file" class="form-control" name="product_images[]" multiple accept="image/*">
            </div>
            <div class="col-md-6">
              <label class="form-label">Số lượng</label>
              <input type="number" class="form-control" name="qty" required>
            </div>
            <div class="col-md-6 d-flex align-items-end">
              <div class="form-check">
                <input class="form-check-input" type="checkbox" name="status" id="prodStatus">
                <label class="form-check-label" for="prodStatus">Hiển thị</label>
              </div>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
          <button type="submit" name="add_product_btn" class="btn bg-gradient-primary">Thêm sản phẩm</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script type="text/javascript" src="./assets/js/StringConvertToSlug.js"></script>

