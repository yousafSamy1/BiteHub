<?php
$pageTitle = 'Customer Dashboard';
require_once __DIR__ . '/../config/db.php';
requireLogin();
$uid = $_SESSION['user_id'];
$cust = $pdo->prepare("SELECT * FROM Customer WHERE UserID=?"); $cust->execute([$uid]); $customer = $cust->fetch();
$custId = $customer['CustomerID'] ?? 0;
$orders = $pdo->prepare("SELECT o.*, p.Method FROM `Order` o LEFT JOIN Payment p ON o.PaymentID=p.PaymentID WHERE o.CustomerID=? ORDER BY o.CreatedAt DESC"); $orders->execute([$custId]); $orderList = $orders->fetchAll();
$subs = $pdo->prepare("SELECT * FROM Subscription WHERE CustomerID=? ORDER BY SubscriptionID DESC"); $subs->execute([$custId]); $subsList = $subs->fetchAll();
$totalOrders = count($orderList);
$totalSpent = array_sum(array_column($orderList, 'TotalPrice'));
$wallet = $customer['WalletBalance'] ?? 0;

// Loyalty Points
$loyaltyStmt = $pdo->prepare("SELECT * FROM LoyaltyTransaction WHERE CustomerID=? ORDER BY CreatedAt DESC");
$loyaltyStmt->execute([$custId]); $loyaltyList = $loyaltyStmt->fetchAll();
$loyaltyPts = array_sum(array_column($loyaltyList, 'Points'));

// Notifications
$notifs = $pdo->prepare("SELECT * FROM Notification WHERE UserID=? ORDER BY CreatedAt DESC LIMIT 10");
$notifs->execute([$uid]); $notifList = $notifs->fetchAll();
$unreadCount = count(array_filter($notifList, fn($n)=>!$n['IsRead']));

// Cancel order
if (isset($_GET['cancel']) && $custId) {
    $pdo->prepare("UPDATE `Order` SET OrderStatus='Cancelled' WHERE OrderID=? AND CustomerID=? AND OrderStatus='Pending'")->execute([intval($_GET['cancel']), $custId]);
    header("Location: customer.php"); exit;
}

$currentPage = '';
require_once __DIR__ . '/../includes/header.php';
?>
<div class="dashboard">
    <aside class="sidebar">
        <ul class="sidebar-menu">
            <li><a href="#" class="active" onclick="switchTab('dash','overview');setActive(this)"><i class="fas fa-home"></i> Overview</a></li>
            <li><a href="#" onclick="switchTab('dash','orders');setActive(this)"><i class="fas fa-shopping-bag"></i> My Orders</a></li>
            <li><a href="#" onclick="switchTab('dash','subs');setActive(this)"><i class="fas fa-sync"></i> Subscriptions</a></li>
            <li><a href="#" onclick="switchTab('dash','loyalty');setActive(this)"><i class="fas fa-star"></i> Loyalty Points</a></li>
            <li><a href="#" onclick="switchTab('dash','notifs');setActive(this)"><i class="fas fa-bell"></i> Notifications <?php if($unreadCount): ?><span style="background:var(--primary);color:#fff;font-size:0.7rem;padding:2px 6px;border-radius:10px;margin-left:4px"><?= $unreadCount ?></span><?php endif; ?></a></li>
            <li><a href="../menu.php"><i class="fas fa-utensils"></i> Browse Menu</a></li>
            <li><a href="../browse.php"><i class="fas fa-store"></i> Kitchens</a></li>
            <li><a href="../logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
        </ul>
    </aside>
    <main class="dash-content">
        <div class="dash-header">
            <div><h1>Welcome, <?= htmlspecialchars(explode(' ',$_SESSION['full_name'])[0]) ?>! 👋</h1><p style="color:var(--text-muted)">Here's your activity overview</p></div>
            <a href="../menu.php" class="btn btn-primary"><i class="fas fa-plus"></i> New Order</a>
        </div>
        <div class="grid grid-4 mb-3">
            <div class="glass-card stat-card"><div class="stat-icon orange"><i class="fas fa-shopping-bag"></i></div><div class="stat-value"><?= $totalOrders ?></div><div class="stat-label">Total Orders</div></div>
            <div class="glass-card stat-card"><div class="stat-icon green"><i class="fas fa-wallet"></i></div><div class="stat-value"><?= number_format($wallet,2) ?></div><div class="stat-label">Wallet (EGP)</div></div>
            <div class="glass-card stat-card"><div class="stat-icon blue"><i class="fas fa-coins"></i></div><div class="stat-value"><?= number_format($totalSpent,2) ?></div><div class="stat-label">Total Spent</div></div>
            <div class="glass-card stat-card"><div class="stat-icon purple"><i class="fas fa-star"></i></div><div class="stat-value"><?= $loyaltyPts ?></div><div class="stat-label">Loyalty Points</div></div>
        </div>
        <!-- Overview -->
        <div data-tab-group="dash" data-tab="overview" style="display:block">
            <div class="grid grid-2 mb-3">
                <div class="glass-card"><h3 class="mb-2"><i class="fas fa-clock" style="color:var(--primary)"></i> Recent Orders</h3>
                <div class="table-wrap"><table><thead><tr><th>Order #</th><th>Total</th><th>Payment</th><th>Status</th><th>Date</th></tr></thead><tbody>
                <?php foreach(array_slice($orderList,0,5) as $o): ?>
                <tr><td>#<?= $o['OrderID'] ?></td><td><?= number_format($o['TotalPrice'],2) ?> EGP</td><td><?= $o['Method'] ?? 'N/A' ?></td>
                <td><span class="badge-status badge-<?= strtolower($o['OrderStatus'] ?? 'pending') ?>"><?= $o['OrderStatus'] ?? 'Pending' ?></span></td>
                <td><?= date('M d, Y', strtotime($o['CreatedAt'])) ?></td></tr>
                <?php endforeach; ?>
                <?php if(empty($orderList)): ?><tr><td colspan="5" class="text-center text-muted">No orders yet. <a href="../menu.php">Browse menu</a></td></tr><?php endif; ?>
                </tbody></table></div></div>
                <!-- Notifications Mini -->
                <div class="glass-card"><h3 class="mb-2"><i class="fas fa-bell" style="color:var(--warning)"></i> Latest Notifications</h3>
                <?php foreach(array_slice($notifList,0,4) as $n): ?>
                <div style="display:flex;gap:12px;padding:12px 0;border-bottom:1px solid var(--border-color);opacity:<?= $n['IsRead']?'0.6':'1' ?>">
                    <div style="width:8px;height:8px;border-radius:50%;background:<?= $n['IsRead']?'transparent':'var(--primary)' ?>;flex-shrink:0;margin-top:6px"></div>
                    <div>
                        <p style="font-size:0.9rem;font-weight:500;margin-bottom:2px"><?= htmlspecialchars($n['Title']) ?></p>
                        <span style="color:var(--text-muted);font-size:0.75rem"><?= date('M d, H:i',strtotime($n['CreatedAt'])) ?></span>
                    </div>
                </div>
                <?php endforeach; ?>
                <?php if(empty($notifList)): ?><p class="text-muted text-center" style="padding:20px">No notifications</p><?php endif; ?>
                </div>
            </div>
        </div>
        <!-- All Orders -->
        <div data-tab-group="dash" data-tab="orders" style="display:none">
            <div class="glass-card"><h3 class="mb-2">All Orders (<?= $totalOrders ?>)</h3>
            <div class="table-wrap"><table><thead><tr><th>Order #</th><th>Total</th><th>Payment</th><th>Status</th><th>Notes</th><th>Date</th><th>Action</th></tr></thead><tbody>
            <?php foreach($orderList as $o): ?>
            <tr><td>#<?= $o['OrderID'] ?></td><td><?= number_format($o['TotalPrice'],2) ?> EGP</td><td><?= $o['Method'] ?? 'N/A' ?></td>
            <td><span class="badge-status badge-<?= strtolower($o['OrderStatus'] ?? 'pending') ?>"><?= $o['OrderStatus'] ?? 'Pending' ?></span></td>
            <td style="max-width:150px;font-size:0.8rem;color:var(--text-muted)"><?= htmlspecialchars(substr($o['SpecialRequests']??'—',0,40)) ?></td>
            <td><?= date('M d, Y H:i', strtotime($o['CreatedAt'])) ?></td>
            <td><?php if(in_array($o['OrderStatus']??'',['Confirmed','Preparing','Ready','Delivering'])): ?><a href="../order-tracking.php?id=<?= $o['OrderID'] ?>" class="btn btn-primary btn-sm"><i class="fas fa-map-marker-alt"></i> Track</a>
            <?php elseif(($o['OrderStatus']??'')==='Pending'): ?><a href="?cancel=<?= $o['OrderID'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Cancel this order?')"><i class="fas fa-times"></i></a>
            <?php elseif(($o['OrderStatus']??'')==='Delivered'): ?><a href="../order-tracking.php?id=<?= $o['OrderID'] ?>" class="btn btn-outline btn-sm"><i class="fas fa-eye"></i></a>
            <?php else: ?>—<?php endif; ?></td></tr>
            <?php endforeach; ?>
            </tbody></table></div></div>
        </div>
        <!-- Subscriptions -->
        <div data-tab-group="dash" data-tab="subs" style="display:none">
            <div class="glass-card"><h3 class="mb-2">My Subscriptions</h3>
            <div class="table-wrap"><table><thead><tr><th>Plan</th><th>Price</th><th>Start</th><th>End</th><th>Status</th></tr></thead><tbody>
            <?php foreach($subsList as $s): ?>
            <tr><td><?= $s['PlanTime'] ?></td><td><?= number_format($s['Price'],2) ?> EGP</td><td><?= $s['StartDate'] ?></td><td><?= $s['EndDate'] ?></td>
            <td><span class="badge-status badge-<?= strtolower($s['Status']) ?>"><?= $s['Status'] ?></span></td></tr>
            <?php endforeach; ?>
            <?php if(empty($subsList)): ?><tr><td colspan="5" class="text-center text-muted">No subscriptions. <a href="../subscriptions.php">View plans</a></td></tr><?php endif; ?>
            </tbody></table></div></div>
        </div>
        <!-- Loyalty Points -->
        <div data-tab-group="dash" data-tab="loyalty" style="display:none">
            <div class="grid grid-3 mb-3">
                <div class="glass-card text-center" style="padding:32px">
                    <div style="font-size:3rem;font-weight:800;background:linear-gradient(135deg,var(--primary),var(--accent));-webkit-background-clip:text;-webkit-text-fill-color:transparent"><?= $loyaltyPts ?></div>
                    <p style="color:var(--text-muted);font-size:0.9rem">Total Points</p>
                </div>
                <div class="glass-card text-center" style="padding:32px">
                    <div style="font-size:2rem;font-weight:700;color:var(--success)"><?= number_format(max(0,$loyaltyPts) * 0.5, 2) ?></div>
                    <p style="color:var(--text-muted);font-size:0.9rem">Value (EGP)</p>
                </div>
                <div class="glass-card text-center" style="padding:32px">
                    <div style="font-size:2rem;font-weight:700;color:var(--info)"><?= $totalOrders > 0 ? round($loyaltyPts / max(1,$totalOrders)) : 0 ?></div>
                    <p style="color:var(--text-muted);font-size:0.9rem">Avg Points/Order</p>
                </div>
            </div>
            <div class="glass-card"><h3 class="mb-2"><i class="fas fa-history" style="color:var(--accent)"></i> Points History</h3>
            <div class="table-wrap"><table><thead><tr><th>Date</th><th>Type</th><th>Points</th><th>Description</th></tr></thead><tbody>
            <?php foreach($loyaltyList as $lt): ?>
            <tr><td><?= date('M d, Y', strtotime($lt['CreatedAt'])) ?></td>
            <td><span class="badge-status badge-<?= $lt['Type']==='Earned'||$lt['Type']==='Bonus'||$lt['Type']==='Referral'?'active':'cancelled' ?>"><?= $lt['Type'] ?></span></td>
            <td style="font-weight:700;color:<?= $lt['Points']>=0?'var(--success)':'var(--danger)' ?>"><?= $lt['Points']>=0?'+':'' ?><?= $lt['Points'] ?></td>
            <td style="color:var(--text-secondary);font-size:0.85rem"><?= htmlspecialchars($lt['Description']) ?></td></tr>
            <?php endforeach; ?>
            <?php if(empty($loyaltyList)): ?><tr><td colspan="4" class="text-center text-muted">No points yet. Order to earn points!</td></tr><?php endif; ?>
            </tbody></table></div></div>
        </div>
        <!-- Notifications -->
        <div data-tab-group="dash" data-tab="notifs" style="display:none">
            <div class="glass-card"><h3 class="mb-2"><i class="fas fa-bell" style="color:var(--warning)"></i> All Notifications</h3>
            <?php foreach($notifList as $n): ?>
            <div style="display:flex;gap:16px;padding:16px;border-bottom:1px solid var(--border-color);align-items:flex-start;opacity:<?= $n['IsRead']?'0.6':'1' ?>">
                <div style="width:40px;height:40px;border-radius:10px;background:rgba(255,167,38,0.15);display:flex;align-items:center;justify-content:center;flex-shrink:0">
                    <i class="fas fa-<?= $n['Type']==='Order'?'shopping-bag':($n['Type']==='Promotion'?'tag':($n['Type']==='Chat'?'comment':'cog')) ?>" style="color:var(--warning)"></i>
                </div>
                <div style="flex:1">
                    <h4 style="font-size:0.95rem;margin-bottom:2px"><?= htmlspecialchars($n['Title']) ?></h4>
                    <p style="color:var(--text-secondary);font-size:0.85rem;margin-bottom:4px"><?= htmlspecialchars($n['Message']) ?></p>
                    <span style="color:var(--text-muted);font-size:0.75rem"><?= date('M d, Y H:i', strtotime($n['CreatedAt'])) ?></span>
                </div>
                <?php if(!$n['IsRead']): ?><span style="width:8px;height:8px;border-radius:50%;background:var(--primary);flex-shrink:0;margin-top:6px"></span><?php endif; ?>
            </div>
            <?php endforeach; ?>
            <?php if(empty($notifList)): ?><p class="text-center text-muted" style="padding:40px">No notifications yet</p><?php endif; ?>
            </div>
        </div>
    </main>
</div>
<script>
function setActive(el){document.querySelectorAll('.sidebar-menu a').forEach(a=>a.classList.remove('active'));el.classList.add('active');}
</script>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
