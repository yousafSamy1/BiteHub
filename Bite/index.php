<?php
$pageTitle = 'Home';
$currentPage = 'home';
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/header.php';

// Fetch featured kitchens
$kitchens = $pdo->query("SELECT ko.*, u.FullName, u.Image FROM KitchenOwner ko JOIN User u ON ko.UserID=u.UserID WHERE ko.VerifyStatus='Verified' AND ko.Status='Active'")->fetchAll();

// Fetch categories
$categories = $pdo->query("SELECT * FROM Category WHERE Status='Active'")->fetchAll();

// Fetch popular items
$popular = $pdo->query("SELECT mi.*, c.Name as CatName, ko.KitchenName FROM MenuItem mi LEFT JOIN Category c ON mi.CategoryID=c.CategoryID LEFT JOIN KitchenOwner ko ON mi.KitchenOwnerID=ko.KitchenOwnerID WHERE mi.Status='Available' ORDER BY mi.MenuItemID LIMIT 8")->fetchAll();
?>

<!-- Hero -->
<section class="hero">
    <div class="hero-bg"><div class="particles" id="hero-particles"></div></div>
    <div class="container">
        <div class="hero-content animate-fadeInUp">
            <div class="hero-badge"><i class="fas fa-fire"></i> #1 Home Food Platform</div>
            <h1>Homemade Food,<br><span class="highlight">Delivered Fresh</span></h1>
            <p>Discover amazing home kitchens and caterers near you. Order authentic homemade meals delivered straight to your doorstep.</p>
            <div class="hero-actions">
                <a href="browse.php" class="btn btn-primary btn-lg"><i class="fas fa-utensils"></i> Explore Kitchens</a>
                <a href="register.php" class="btn btn-outline btn-lg"><i class="fas fa-store"></i> Join as Kitchen</a>
            </div>
            <div class="hero-stats">
                <div class="hero-stat"><h3>500+</h3><p>Home Kitchens</p></div>
                <div class="hero-stat"><h3>10K+</h3><p>Happy Customers</p></div>
                <div class="hero-stat"><h3>50K+</h3><p>Orders Delivered</p></div>
            </div>
        </div>
    </div>
</section>

<!-- How It Works -->
<section class="section">
    <div class="container">
        <div class="section-header reveal">
            <p class="subtitle">Simple Process</p>
            <h2>How It Works</h2>
            <p>Get delicious homemade food in just 3 easy steps</p>
        </div>
        <div class="grid grid-3">
            <div class="glass-card step-card reveal">
                <div class="step-number">1</div>
                <h3>Browse Kitchens</h3>
                <p>Explore verified home kitchens and caterers in your area with real reviews and ratings.</p>
            </div>
            <div class="glass-card step-card reveal">
                <div class="step-number">2</div>
                <h3>Place Your Order</h3>
                <p>Choose your favorite meals, customize them, and place your order with secure payment.</p>
            </div>
            <div class="glass-card step-card reveal">
                <div class="step-number">3</div>
                <h3>Enjoy at Home</h3>
                <p>Track your delivery in real-time and enjoy fresh homemade food at your doorstep.</p>
            </div>
        </div>
    </div>
</section>

<!-- Categories -->
<section class="section" style="background:var(--bg-card)">
    <div class="container">
        <div class="section-header reveal">
            <p class="subtitle">Categories</p>
            <h2>Explore by Category</h2>
        </div>
        <div class="grid grid-4 reveal">
            <?php
            $catIcons = ['🍽️','🍰','🥗','🥤','🍳'];
            foreach($categories as $i=>$cat): ?>
            <a href="menu.php?cat=<?= $cat['CategoryID'] ?>" class="glass-card cat-card">
                <div class="cat-icon"><?= $catIcons[$i] ?? '🍴' ?></div>
                <h3><?= htmlspecialchars($cat['Name']) ?></h3>
                <p><?= htmlspecialchars(substr($cat['Description'] ?? '', 0, 50)) ?></p>
            </a>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- Featured Kitchens -->
<section class="section">
    <div class="container">
        <div class="section-header reveal">
            <p class="subtitle">Top Rated</p>
            <h2>Featured Kitchens</h2>
            <p>Verified home kitchens with the best reviews</p>
        </div>
        <div class="grid grid-3 reveal">
            <?php foreach($kitchens as $k): ?>
            <a href="kitchen.php?id=<?= $k['KitchenOwnerID'] ?>" class="card kitchen-card">
                <div class="card-img" style="background:linear-gradient(135deg,#1a1a2e,#2a2a4e);display:flex;align-items:center;justify-content:center;height:200px">
                    <div style="text-align:center">
                        <img src="<?= htmlspecialchars($k['Image']) ?>" style="width:80px;height:80px;border-radius:50%;border:3px solid var(--primary);margin-bottom:8px" alt="">
                        <h3 style="color:#fff"><?= htmlspecialchars($k['KitchenName']) ?></h3>
                    </div>
                    <span class="kitchen-badge"><i class="fas fa-check-circle"></i> Verified</span>
                </div>
                <div class="card-body">
                    <div class="kitchen-rating"><i class="fas fa-star"></i> 4.<?= rand(5,9) ?> <span style="color:var(--text-muted);font-weight:400">(<?= rand(50,200) ?> reviews)</span></div>
                    <p class="card-text"><?= htmlspecialchars(substr($k['Description'] ?? 'Delicious homemade food', 0, 80)) ?>...</p>
                    <div class="kitchen-meta">
                        <span><i class="fas fa-clock"></i> 25-35 min</span>
                        <span><i class="fas fa-map-marker-alt"></i> Cairo</span>
                    </div>
                </div>
            </a>
            <?php endforeach; ?>
        </div>
        <div class="text-center mt-3 reveal">
            <a href="browse.php" class="btn btn-outline">View All Kitchens <i class="fas fa-arrow-right"></i></a>
        </div>
    </div>
</section>

<!-- Popular Items -->
<section class="section" style="background:var(--bg-card)">
    <div class="container">
        <div class="section-header reveal">
            <p class="subtitle">Most Ordered</p>
            <h2>Popular Dishes</h2>
        </div>
        <div class="grid grid-4 reveal">
            <?php foreach(array_slice($popular, 0, 8) as $item): ?>
            <div class="card menu-card">
                <div class="card-img" style="background:linear-gradient(135deg,rgba(255,107,53,0.2),rgba(255,167,38,0.1));display:flex;align-items:center;justify-content:center;font-size:3rem">🍽️</div>
                <div class="card-body">
                    <span style="color:var(--primary);font-size:0.75rem;font-weight:600"><?= htmlspecialchars($item['CatName'] ?? 'Food') ?></span>
                    <h3 class="card-title"><?= htmlspecialchars($item['ItemName']) ?></h3>
                    <p class="card-text"><?= htmlspecialchars(substr($item['Description'] ?? '', 0, 60)) ?>...</p>
                    <div class="flex-between">
                        <span class="menu-price"><?= number_format($item['ItemPrice'], 2) ?> <small>EGP</small></span>
                        <button class="btn btn-primary btn-sm" onclick="addToCart(<?= $item['MenuItemID'] ?>,'<?= addslashes($item['ItemName']) ?>',<?= $item['ItemPrice'] ?>,'')"><i class="fas fa-plus"></i></button>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- CTA -->
<section class="section">
    <div class="container">
        <div class="glass-card text-center reveal" style="padding:60px 40px">
            <h2>Ready to Start Your Kitchen?</h2>
            <p style="color:var(--text-secondary);max-width:500px;margin:16px auto 32px">Join hundreds of home cooks and caterers. Reach thousands of customers and grow your food business.</p>
            <div style="display:flex;gap:16px;justify-content:center;flex-wrap:wrap">
                <a href="register.php" class="btn btn-primary btn-lg"><i class="fas fa-store"></i> Register Kitchen</a>
                <a href="register.php" class="btn btn-outline btn-lg"><i class="fas fa-truck"></i> Become a Driver</a>
            </div>
        </div>
    </div>
</section>

<script>createParticles('#hero-particles', 25);</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
