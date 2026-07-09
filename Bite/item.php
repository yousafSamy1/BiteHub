<?php
require_once __DIR__ . '/config/db.php';
$id = intval($_GET['id'] ?? 0);
$stmt = $pdo->prepare("SELECT mi.*, c.Name as CatName, ko.KitchenName, ko.KitchenOwnerID FROM MenuItem mi LEFT JOIN Category c ON mi.CategoryID=c.CategoryID LEFT JOIN KitchenOwner ko ON mi.KitchenOwnerID=ko.KitchenOwnerID WHERE mi.MenuItemID=?");
$stmt->execute([$id]); $item = $stmt->fetch();
if (!$item) { header('Location: menu.php'); exit; }
$related = $pdo->prepare("SELECT * FROM MenuItem WHERE CategoryID=? AND MenuItemID!=? AND Status='Available' LIMIT 4");
$related->execute([$item['CategoryID'],$id]); $relatedItems = $related->fetchAll();
$pageTitle = $item['ItemName'];
require_once __DIR__ . '/includes/header.php';
?>
<div class="page-header"><h1><?= htmlspecialchars($item['ItemName']) ?></h1>
<div class="breadcrumb"><a href="menu.php">Menu</a> <span>/</span> <a href="menu.php?cat=<?= $item['CategoryID'] ?>"><?= htmlspecialchars($item['CatName']??'') ?></a> <span>/</span> <?= htmlspecialchars($item['ItemName']) ?></div></div>
<section class="section" style="padding-top:0">
<div class="container">
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:32px;align-items:start" class="reveal">
        <div class="glass-card" style="display:flex;align-items:center;justify-content:center;min-height:300px;font-size:5rem">🍽️</div>
        <div class="glass-card">
            <span style="color:var(--primary);font-size:0.85rem;font-weight:600"><?= htmlspecialchars($item['CatName']??'') ?></span>
            <h2 style="margin:8px 0"><?= htmlspecialchars($item['ItemName']) ?></h2>
            <div class="kitchen-rating mb-2"><i class="fas fa-star"></i> 4.7 <span style="color:var(--text-muted);font-weight:400">(92 reviews)</span></div>
            <p style="color:var(--text-secondary);margin-bottom:20px"><?= htmlspecialchars($item['Description'] ?? 'A delicious dish prepared with fresh ingredients.') ?></p>
            <div class="flex-between mb-3">
                <span class="menu-price" style="font-size:2rem"><?= number_format($item['ItemPrice'],2) ?> <small>EGP</small></span>
                <span class="badge-status badge-<?= strtolower($item['Status']) ?>"><?= $item['Status'] ?></span>
            </div>
            <p style="color:var(--text-muted);font-size:0.9rem;margin-bottom:16px"><i class="fas fa-store"></i> From <a href="kitchen.php?id=<?= $item['KitchenOwnerID'] ?>"><?= htmlspecialchars($item['KitchenName']??'Kitchen') ?></a></p>
            <p style="color:var(--text-muted);font-size:0.9rem;margin-bottom:20px"><i class="fas fa-clock"></i> Preparation: 25-35 min</p>
            <button class="btn btn-primary btn-lg btn-block" onclick="addToCart(<?= $item['MenuItemID'] ?>,'<?= addslashes($item['ItemName']) ?>',<?= $item['ItemPrice'] ?>,'')"><i class="fas fa-cart-plus"></i> Add to Cart</button>
        </div>
    </div>
    <?php if($relatedItems): ?>
    <h2 class="mt-3 mb-2 reveal" style="font-size:1.3rem">You Might Also Like</h2>
    <div class="grid grid-4 reveal">
        <?php foreach($relatedItems as $r): ?>
        <a href="item.php?id=<?= $r['MenuItemID'] ?>" class="card menu-card">
            <div class="card-img" style="background:linear-gradient(135deg,rgba(255,107,53,0.15),rgba(255,167,38,0.08));display:flex;align-items:center;justify-content:center;font-size:2.5rem">🍽️</div>
            <div class="card-body"><h3 class="card-title"><?= htmlspecialchars($r['ItemName']) ?></h3><span class="menu-price"><?= number_format($r['ItemPrice'],2) ?> <small>EGP</small></span></div>
        </a>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
</div>
</section>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
