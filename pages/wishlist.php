<?php
// ============================================
// TRANG YÊU THÍCH (WISHLIST) - Session-based
// ============================================
$pageTitle = "Sản phẩm yêu thích - NHÓM 10 Fashion Shop";

// Handle add/remove wishlist via GET
if (isset($_GET['add_wishlist'])) {
    addToWishlist(intval($_GET['add_wishlist']));
    $_SESSION['message'] = "Đã thêm vào danh sách yêu thích!";
    $redirect = $_GET['redirect'] ?? 'wishlist';
    header("Location: index.php?page=" . urlencode($redirect));
    exit();
}

if (isset($_GET['remove_wishlist'])) {
    removeFromWishlist(intval($_GET['remove_wishlist']));
    $_SESSION['message'] = "Đã xóa khỏi danh sách yêu thích!";
    header("Location: index.php?page=wishlist");
    exit();
}

$wishlist_items = getWishlistItems();
?>

<style>
.wishlist-empty { text-align: center; padding: 60px 20px; }
.wishlist-empty i { font-size: 80px; color: #ddd; display: block; margin-bottom: 15px; }
.wishlist-empty h3 { color: #666; }
.wishlist-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(250px, 1fr)); gap: 20px; }
.wishlist-card { background: white; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 15px rgba(0,0,0,0.08); transition: transform 0.3s, box-shadow 0.3s; }
.wishlist-card:hover { transform: translateY(-5px); box-shadow: 0 8px 25px rgba(0,0,0,0.15); }
.wishlist-card-img { position: relative; height: 240px; overflow: hidden; }
.wishlist-card-img img { width: 100%; height: 100%; object-fit: cover; transition: transform 0.3s; }
.wishlist-card:hover .wishlist-card-img img { transform: scale(1.05); }
.wishlist-remove { position: absolute; top: 10px; right: 10px; background: rgba(231,76,60,0.9); color: white; border: none; width: 36px; height: 36px; border-radius: 50%; cursor: pointer; font-size: 18px; display: flex; align-items: center; justify-content: center; transition: all 0.3s; }
.wishlist-remove:hover { background: #c0392b; transform: scale(1.1); }
.wishlist-card-body { padding: 15px; }
.wishlist-card-body h4 { margin: 0 0 8px; font-size: 16px; color: #333; }
.wishlist-card-body .category { font-size: 13px; color: #999; margin-bottom: 8px; }
.wishlist-card-body .price { font-size: 18px; font-weight: 700; color: #e74c3c; }
.wishlist-card-body .price del { font-size: 14px; color: #999; font-weight: 400; margin-right: 8px; }
.wishlist-card-actions { padding: 0 15px 15px; display: flex; gap: 8px; }
.wishlist-card-actions a { flex: 1; text-align: center; padding: 10px; border-radius: 8px; text-decoration: none; font-weight: 600; font-size: 14px; transition: all 0.3s; }
.btn-view { background: #f0f0f0; color: #333; }
.btn-view:hover { background: #e0e0e0; }
.btn-add-cart { background: linear-gradient(135deg, #667eea, #764ba2); color: white; }
.btn-add-cart:hover { transform: translateY(-2px); box-shadow: 0 4px 12px rgba(102,126,234,0.4); }
</style>

<div class="bg-main">
    <div class="container">
        <div class="box">
            <div class="breadcumb">
                <a href="index.php?page=home">Trang chủ</a>
                <span><i class='bx bxs-chevrons-right'></i></span>
                <a href="#">Yêu thích (<?= count($wishlist_items) ?>)</a>
            </div>
        </div>
        
        <div class="box">
            <?php if (empty($wishlist_items)): ?>
                <div class="wishlist-empty">
                    <i class='bx bx-heart'></i>
                    <h3>Danh sách yêu thích trống</h3>
                    <p>Hãy thêm sản phẩm bạn yêu thích để xem lại sau!</p>
                    <a href="index.php?page=products" class="btn-flat btn-hover" style="display: inline-block; margin-top: 10px; text-decoration: none; color: white; padding: 12px 24px; border-radius: 8px;">
                        Xem sản phẩm
                    </a>
                </div>
            <?php else: ?>
                <div class="wishlist-grid">
                    <?php foreach ($wishlist_items as $item): 
                        $pricing = calculateProductPricing($item);
                        $has_flash = $pricing['flash_sale'] !== null && $pricing['final_price'] < $pricing['base_price'];
                    ?>
                    <div class="wishlist-card">
                        <div class="wishlist-card-img">
                            <img src="./images/<?= e($item['image']) ?>" alt="<?= e($item['name']) ?>" loading="lazy">
                            <a href="index.php?page=wishlist&remove_wishlist=<?= $item['id'] ?>" class="wishlist-remove" title="Xóa khỏi yêu thích">
                                <i class='bx bx-x'></i>
                            </a>
                        </div>
                        <div class="wishlist-card-body">
                            <div class="category"><?= e($item['category_name'] ?? '') ?></div>
                            <h4><?= e($item['name']) ?></h4>
                            <div class="price">
                                <?php if ($has_flash): ?>
                                    <del><?= formatVND($item['selling_price']) ?></del>
                                    <?= formatVND($pricing['final_price']) ?>
                                <?php else: ?>
                                    <?= formatVND($item['selling_price']) ?>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="wishlist-card-actions">
                            <a href="index.php?page=product-detail&slug=<?= e($item['slug']) ?>" class="btn-view">
                                <i class='bx bx-show'></i> Xem
                            </a>
                            <a href="index.php?page=product-detail&slug=<?= e($item['slug']) ?>" class="btn-add-cart">
                                <i class='bx bx-cart-add'></i> Mua
                            </a>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
