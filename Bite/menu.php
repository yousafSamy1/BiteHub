<?php
$pageTitle = 'Menu';
$currentPage = 'menu';
require_once __DIR__ . '/config/db.php';
$catFilter = intval($_GET['cat'] ?? 0);
$sql = "SELECT mi.*, c.Name as CatName, ko.KitchenName FROM MenuItem mi LEFT JOIN Category c ON mi.CategoryID=c.CategoryID LEFT JOIN KitchenOwner ko ON mi.KitchenOwnerID=ko.KitchenOwnerID WHERE mi.Status='Available'";
if ($catFilter > 0) $sql .= " AND mi.CategoryID=$catFilter";
$sql .= " ORDER BY mi.CategoryID, mi.ItemName";
$items = $pdo->query($sql)->fetchAll();
$categories = $pdo->query("SELECT * FROM Category WHERE Status='Active'")->fetchAll();
require_once __DIR__ . '/includes/header.php';
?>
<div class="page-header"><h1>Full <span class="highlight">Menu</span></h1><p>Browse all dishes from our verified kitchens</p></div>
<section class="section" style="padding-top:0">
<div class="container">
    <div class="filter-bar reveal">
        <input type="text" id="menuSearch" class="form-control" placeholder="🔍 Search dishes..." oninput="filterItems('menuSearch','.menu-grid','.menu-card')">
        <select class="form-control" style="max-width:200px" onchange="window.location='menu.php?cat='+this.value">
            <option value="0">All Categories</option>
            <?php foreach($categories as $cat): ?>
            <option value="<?= $cat['CategoryID'] ?>" <?= $catFilter==$cat['CategoryID']?'selected':'' ?>><?= htmlspecialchars($cat['Name']) ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="grid grid-4 menu-grid">
        <?php foreach($items as $item): ?>
        <div class="card menu-card reveal">
            <div class="card-img" style="background:linear-gradient(135deg,rgba(255,107,53,0.15),rgba(255,167,38,0.08));display:flex;align-items:center;justify-content:center;font-size:3rem">🍽️</div>
            <div class="card-body">
                <span style="color:var(--primary);font-size:0.75rem;font-weight:600"><?= htmlspecialchars($item['CatName'] ?? '') ?></span>
                <h3 class="card-title"><?= htmlspecialchars($item['ItemName']) ?></h3>
                <p class="card-text"><?= htmlspecialchars(substr($item['Description'] ?? '', 0, 60)) ?></p>
                <p style="color:var(--text-muted);font-size:0.8rem"><i class="fas fa-store"></i> <?= htmlspecialchars($item['KitchenName'] ?? 'Kitchen') ?></p>
                <div class="flex-between mt-2">
                    <span class="menu-price"><?= number_format($item['ItemPrice'], 2) ?> <small>EGP</small></span>
                    <button class="btn btn-primary btn-sm" onclick="addToCart(<?= $item['MenuItemID'] ?>,'<?= addslashes($item['ItemName']) ?>',<?= $item['ItemPrice'] ?>,'')"><i class="fas fa-plus"></i></button>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</div>
</section>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
