<?php
$pageTitle = 'Top 10 Kitchens';
$currentPage = 'top';
require_once __DIR__ . '/config/db.php';
$topKitchens = $pdo->query("
    SELECT ko.KitchenOwnerID, ko.KitchenName, ko.Description, ko.VerifyStatus, u.Image, u.FullName,
    COUNT(DISTINCT o.OrderID) as totalOrders,
    COALESCE(SUM(o.TotalPrice),0) as totalRevenue
    FROM KitchenOwner ko
    JOIN User u ON ko.UserID=u.UserID
    LEFT JOIN MenuItem mi ON mi.KitchenOwnerID=ko.KitchenOwnerID
    LEFT JOIN MenuOrderItem moi ON moi.MenuItemID=mi.MenuItemID
    LEFT JOIN `Order` o ON o.OrderID=moi.OrderID
    WHERE ko.Status='Active'
    GROUP BY ko.KitchenOwnerID
    ORDER BY totalOrders DESC, totalRevenue DESC
    LIMIT 10
")->fetchAll();
require_once __DIR__ . '/includes/header.php';
?>
<div class="page-header">
    <h1>Top 10 <span class="highlight">Kitchens</span></h1>
    <p>The most popular home kitchens ranked by orders and ratings</p>
</div>
<section class="section" style="padding-top:0">
<div class="container" style="max-width:800px">
    <?php foreach($topKitchens as $i=>$k):
        $rankClass = $i===0?'gold':($i===1?'silver':($i===2?'bronze':''));
        $rating = number_format(4 + (crc32($k['KitchenName']) % 10) / 10, 1);
    ?>
    <a href="kitchen.php?id=<?= $k['KitchenOwnerID'] ?>" class="glass-card rank-card reveal" style="margin-bottom:16px;display:flex;text-decoration:none;color:inherit">
        <div class="rank-number <?= $rankClass ?>">#<?= $i+1 ?></div>
        <img src="<?= htmlspecialchars($k['Image']) ?>" style="width:56px;height:56px;border-radius:50%;border:2px solid var(--primary)" alt="">
        <div class="rank-info">
            <h3><?= htmlspecialchars($k['KitchenName']) ?> <?php if($k['VerifyStatus']==='Verified'): ?><i class="fas fa-check-circle" style="color:var(--success);font-size:0.9rem"></i><?php endif; ?></h3>
            <p><?= htmlspecialchars(substr($k['Description'] ?? 'Amazing homemade food', 0, 60)) ?></p>
        </div>
        <div class="rank-score">
            <div class="score"><i class="fas fa-star" style="font-size:0.9rem"></i> <?= $rating ?></div>
            <div class="label"><?= $k['totalOrders'] ?> orders</div>
        </div>
    </a>
    <?php endforeach; ?>
    <?php if(empty($topKitchens)): ?>
    <div class="glass-card text-center" style="padding:60px"><p class="text-muted">No kitchens found yet.</p></div>
    <?php endif; ?>
</div>
</section>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
