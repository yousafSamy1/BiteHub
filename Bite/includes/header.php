<?php
$base = getBaseUrl();
$isLogged = isLoggedIn();
$role = getUserRole();
$name = getUserName();
$img = getUserImage();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="BiteHub - Connect with home kitchens and caterers. Order fresh homemade food delivered to your door.">
    <title><?= $pageTitle ?? 'BiteHub' ?> | Home Food Platform</title>
    <script>document.documentElement.setAttribute('data-theme', localStorage.getItem('bitehub_theme') || 'dark');</script>
    <link rel="stylesheet" href="<?= $base ?>/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>
<nav class="navbar">
    <div class="container">
        <a href="<?= $base ?>/index.php" class="nav-logo">
            <i class="fas fa-fire"></i> <span>Bite</span>Hub
        </a>
        <ul class="nav-links">
            <li><a href="<?= $base ?>/index.php" class="<?= ($currentPage ?? '') === 'home' ? 'active' : '' ?>">Home</a></li>
            <li><a href="<?= $base ?>/browse.php" class="<?= ($currentPage ?? '') === 'browse' ? 'active' : '' ?>">Kitchens</a></li>
            <li><a href="<?= $base ?>/menu.php" class="<?= ($currentPage ?? '') === 'menu' ? 'active' : '' ?>">Menu</a></li>
            <li><a href="<?= $base ?>/top-kitchens.php" class="<?= ($currentPage ?? '') === 'top' ? 'active' : '' ?>">Top 10</a></li>
            <li><a href="<?= $base ?>/subscriptions.php" class="<?= ($currentPage ?? '') === 'subs' ? 'active' : '' ?>">Plans</a></li>
        </ul>
        <div class="nav-actions">
            <button class="theme-toggle" onclick="toggleTheme()" title="Toggle Dark/Light Mode">
                <i class="fas fa-sun"></i>
                <i class="fas fa-moon"></i>
            </button>
            <a href="<?= $base ?>/cart.php" class="nav-cart">
                <i class="fas fa-shopping-cart"></i>
                <span class="badge" style="display:none">0</span>
            </a>
            <?php if ($isLogged): ?>
            <div class="nav-dropdown">
                <div class="nav-user">
                    <img src="<?= htmlspecialchars($img) ?>" alt="<?= htmlspecialchars($name) ?>">
                    <span><?= htmlspecialchars(explode(' ', $name)[0]) ?></span>
                    <i class="fas fa-chevron-down" style="color:var(--text-muted);font-size:0.7rem"></i>
                </div>
                <div class="nav-dropdown-menu">
                    <?php
                    $dashMap = ['Admin'=>'admin','Customer'=>'customer','KitchenOwner'=>'kitchen-owner','Caterer'=>'caterer','DeliveryAgent'=>'delivery'];
                    $dashFile = $dashMap[$role] ?? 'customer';
                    ?>
                    <a href="<?= $base ?>/dashboard/<?= $dashFile ?>.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
                    <a href="<?= $base ?>/logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
                </div>
            </div>
            <?php else: ?>
            <a href="<?= $base ?>/login.php" class="btn btn-outline btn-sm">Login</a>
            <a href="<?= $base ?>/register.php" class="btn btn-primary btn-sm">Sign Up</a>
            <?php endif; ?>
            <button class="mobile-toggle"><i class="fas fa-bars"></i></button>
        </div>
    </div>
</nav>
