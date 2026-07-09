<?php
$pageTitle = 'Browse Kitchens';
$currentPage = 'browse';
require_once __DIR__ . '/config/db.php';
$kitchens = $pdo->query("SELECT ko.*, u.FullName, u.Image FROM KitchenOwner ko JOIN User u ON ko.UserID=u.UserID WHERE ko.Status='Active'")->fetchAll();
$caterers = $pdo->query("SELECT c.*, u.FullName, u.Image FROM Caterer c JOIN User u ON c.UserID=u.UserID WHERE c.IsActive=1")->fetchAll();
require_once __DIR__ . '/includes/header.php';
?>
<div class="page-header"><h1>Browse <span class="highlight">Kitchens & Caterers</span></h1><p>Discover amazing home kitchens and professional caterers near you</p></div>
<section class="section" style="padding-top:0">
<div class="container">
    <div class="filter-bar reveal">
        <input type="text" id="searchKitchen" class="form-control" placeholder="🔍 Search kitchens..." oninput="filterItems('searchKitchen','.kitchen-grid','.card')">
        <select class="form-control" style="max-width:200px"><option>All Areas</option><option>Cairo</option><option>Giza</option><option>Alexandria</option></select>
        <select class="form-control" style="max-width:200px"><option>All Ratings</option><option>4+ Stars</option><option>3+ Stars</option></select>
    </div>
    <h2 class="mb-2 reveal" style="font-size:1.4rem"><i class="fas fa-store" style="color:var(--primary)"></i> Home Kitchens</h2>
    <div class="grid grid-3 kitchen-grid mb-3">
        <?php foreach($kitchens as $k): $rating = number_format(4 + (crc32($k['KitchenName']) % 10) / 10, 1); $reviews = 50 + (crc32($k['KitchenName']) % 150); ?>
        <a href="kitchen.php?id=<?= $k['KitchenOwnerID'] ?>" class="card kitchen-card reveal">
            <div class="card-img" style="background:linear-gradient(135deg,#1a1a2e,#2a2a4e);display:flex;align-items:center;justify-content:center">
                <div style="text-align:center"><img src="<?= htmlspecialchars($k['Image']) ?>" style="width:70px;height:70px;border-radius:50%;border:3px solid var(--primary)" alt=""><h3 style="color:#fff;margin-top:8px"><?= htmlspecialchars($k['KitchenName']) ?></h3></div>
                <?php if($k['VerifyStatus']==='Verified'): ?><span class="kitchen-badge"><i class="fas fa-check-circle"></i> Verified</span><?php endif; ?>
            </div>
            <div class="card-body">
                <div class="kitchen-rating"><i class="fas fa-star"></i> <?= $rating ?> <span style="color:var(--text-muted);font-weight:400">(<?= $reviews ?> reviews)</span></div>
                <p class="card-text"><?= htmlspecialchars(substr($k['Description'] ?? 'Delicious homemade food', 0, 80)) ?>...</p>
                <div class="kitchen-meta"><span><i class="fas fa-clock"></i> 25-35 min</span><span class="badge-status badge-<?= strtolower($k['Status']) ?>"><?= $k['Status'] ?></span></div>
            </div>
        </a>
        <?php endforeach; ?>
    </div>
    <h2 class="mb-2 reveal" style="font-size:1.4rem"><i class="fas fa-concierge-bell" style="color:var(--accent)"></i> Catering Providers</h2>
    <div class="grid grid-3">
        <?php foreach($caterers as $c): ?>
        <div class="card reveal">
            <div class="card-img" style="background:linear-gradient(135deg,rgba(171,71,188,0.2),rgba(255,167,38,0.1));display:flex;align-items:center;justify-content:center">
                <div style="text-align:center"><img src="<?= htmlspecialchars($c['Image']) ?>" style="width:70px;height:70px;border-radius:50%;border:3px solid var(--accent)" alt=""><h3 style="color:#fff;margin-top:8px"><?= htmlspecialchars($c['FullName']) ?></h3></div>
            </div>
            <div class="card-body">
                <div class="kitchen-rating"><i class="fas fa-star"></i> 4.7 <span style="color:var(--text-muted);font-weight:400">(85 reviews)</span></div>
                <p class="card-text">Professional catering services for events, weddings, and corporate gatherings.</p>
                <div class="kitchen-meta"><span><i class="fas fa-users"></i> 50-500 guests</span><span class="badge-status badge-active">Available</span></div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</div>
</section>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
