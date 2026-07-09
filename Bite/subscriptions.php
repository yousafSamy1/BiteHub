<?php
$pageTitle = 'Subscription Plans';
$currentPage = 'subs';
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/header.php';
?>
<div class="page-header"><h1>Meal <span class="highlight">Subscription Plans</span></h1><p>Save more with our weekly and monthly meal subscriptions</p></div>
<section class="section" style="padding-top:0">
<div class="container">
    <div class="grid grid-3 reveal">
        <div class="glass-card sub-card">
            <h3>Daily Plan</h3>
            <div class="sub-price">25 <small>EGP/day</small></div>
            <p style="color:var(--text-muted);font-size:0.9rem">Perfect for trying out</p>
            <ul class="sub-features">
                <li>1 meal per day</li>
                <li>Choose from any kitchen</li>
                <li>Free delivery</li>
                <li>Cancel anytime</li>
            </ul>
            <a href="<?= isLoggedIn()?'dashboard/customer.php':'login.php' ?>" class="btn btn-outline btn-block">Get Started</a>
        </div>
        <div class="glass-card sub-card featured">
            <h3>Weekly Plan</h3>
            <div class="sub-price">60 <small>EGP/week</small></div>
            <p style="color:var(--text-muted);font-size:0.9rem">Most popular choice</p>
            <ul class="sub-features">
                <li>7 meals per week</li>
                <li>Choose from any kitchen</li>
                <li>Free delivery</li>
                <li>10% loyalty bonus</li>
                <li>Priority support</li>
            </ul>
            <a href="<?= isLoggedIn()?'dashboard/customer.php':'login.php' ?>" class="btn btn-primary btn-block">Subscribe Now</a>
        </div>
        <div class="glass-card sub-card">
            <h3>Monthly Plan</h3>
            <div class="sub-price">200 <small>EGP/month</small></div>
            <p style="color:var(--text-muted);font-size:0.9rem">Best value!</p>
            <ul class="sub-features">
                <li>30 meals per month</li>
                <li>All kitchens access</li>
                <li>Free delivery</li>
                <li>20% loyalty bonus</li>
                <li>VIP support</li>
                <li>Exclusive deals</li>
            </ul>
            <a href="<?= isLoggedIn()?'dashboard/customer.php':'login.php' ?>" class="btn btn-outline btn-block">Get Started</a>
        </div>
    </div>
</div>
</section>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
