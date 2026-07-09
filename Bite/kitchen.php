<?php
require_once __DIR__ . '/config/db.php';
$id = intval($_GET['id'] ?? 1);
$k = $pdo->prepare("SELECT ko.*, u.FullName, u.Image, u.Email FROM KitchenOwner ko JOIN User u ON ko.UserID=u.UserID WHERE ko.KitchenOwnerID=?");
$k->execute([$id]);
$kitchen = $k->fetch();
if (!$kitchen) { header('Location: browse.php'); exit; }
$items = $pdo->prepare("SELECT mi.*, c.Name as CatName FROM MenuItem mi LEFT JOIN Category c ON mi.CategoryID=c.CategoryID WHERE mi.KitchenOwnerID=? AND mi.Status='Available'");
$items->execute([$id]);
$menuItems = $items->fetchAll();
$pageTitle = $kitchen['KitchenName'];
$currentPage = 'browse';
require_once __DIR__ . '/includes/header.php';
$rating = number_format(4 + (crc32($kitchen['KitchenName']) % 10) / 10, 1);
?>
<div class="page-header" style="padding-bottom:0">
    <div class="container" style="display:flex;align-items:center;gap:24px;justify-content:center;flex-wrap:wrap;position:relative">
        <img src="<?= htmlspecialchars($kitchen['Image']) ?>" style="width:100px;height:100px;border-radius:50%;border:4px solid var(--primary)" alt="">
        <div style="text-align:left">
            <h1 style="font-size:2rem;margin-bottom:4px"><?= htmlspecialchars($kitchen['KitchenName']) ?></h1>
            <div style="display:flex;gap:16px;align-items:center;flex-wrap:wrap">
                <span class="kitchen-rating"><i class="fas fa-star"></i> <?= $rating ?></span>
                <?php if($kitchen['VerifyStatus']==='Verified'): ?><span class="badge-status badge-verified"><i class="fas fa-check-circle"></i> Verified</span><?php endif; ?>
                <span style="color:var(--text-muted)"><i class="fas fa-map-marker-alt"></i> Cairo</span>
                <span style="color:var(--text-muted)"><i class="fas fa-clock"></i> 25-35 min</span>
            </div>
        </div>
    </div>
</div>
<section class="section" style="padding-top:24px">
<div class="container">
    <div class="glass-card mb-3 reveal">
        <h3 class="mb-1">About</h3>
        <p style="color:var(--text-secondary)"><?= htmlspecialchars($kitchen['Description'] ?? 'Delicious homemade food made with love.') ?></p>
    </div>
    <h2 class="mb-2 reveal" style="font-size:1.4rem"><i class="fas fa-utensils" style="color:var(--primary)"></i> Menu (<?= count($menuItems) ?> items)</h2>
    <div class="grid grid-3">
        <?php foreach($menuItems as $item): ?>
        <div class="card menu-card reveal">
            <div class="card-img" style="background:linear-gradient(135deg,rgba(255,107,53,0.2),rgba(255,167,38,0.1));display:flex;align-items:center;justify-content:center;font-size:3rem">🍽️</div>
            <div class="card-body">
                <span style="color:var(--primary);font-size:0.75rem;font-weight:600"><?= htmlspecialchars($item['CatName'] ?? '') ?></span>
                <h3 class="card-title"><?= htmlspecialchars($item['ItemName']) ?></h3>
                <p class="card-text"><?= htmlspecialchars(substr($item['Description'] ?? '', 0, 70)) ?></p>
                <div class="flex-between mt-2">
                    <span class="menu-price"><?= number_format($item['ItemPrice'], 2) ?> <small>EGP</small></span>
                    <button class="btn btn-primary btn-sm" onclick="addToCart(<?= $item['MenuItemID'] ?>,'<?= addslashes($item['ItemName']) ?>',<?= $item['ItemPrice'] ?>,'')"><i class="fas fa-cart-plus"></i> Add</button>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php if(empty($menuItems)): ?>
    <div class="glass-card text-center" style="padding:40px"><i class="fas fa-utensils" style="font-size:2rem;color:var(--text-muted);margin-bottom:12px;display:block"></i><p style="color:var(--text-muted)">No menu items available yet.</p></div>
    <?php endif; ?>
</div>
</section>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
