<?php
$pageTitle = 'Admin Dashboard';
require_once __DIR__ . '/../config/db.php';
requireLogin();
$uid = $_SESSION['user_id'];
// Stats
$totalUsers = $pdo->query("SELECT COUNT(*) FROM User")->fetchColumn();
$totalOrders = $pdo->query("SELECT COUNT(*) FROM `Order`")->fetchColumn();
$totalRevenue = $pdo->query("SELECT COALESCE(SUM(TotalPrice),0) FROM `Order`")->fetchColumn();
$totalKitchens = $pdo->query("SELECT COUNT(*) FROM KitchenOwner")->fetchColumn();
$pendingKitchens = $pdo->query("SELECT COUNT(*) FROM KitchenOwner WHERE VerifyStatus='Pending'")->fetchColumn();
$activeOrders = $pdo->query("SELECT COUNT(*) FROM `Order` WHERE OrderStatus NOT IN ('Delivered','Cancelled')")->fetchColumn();
// Users
$users = $pdo->query("SELECT * FROM User ORDER BY CreatedAt DESC")->fetchAll();
// Kitchens
$kitchens = $pdo->query("SELECT ko.*, u.FullName, u.Email FROM KitchenOwner ko JOIN User u ON ko.UserID=u.UserID ORDER BY ko.KitchenOwnerID DESC")->fetchAll();
// Orders
$orders = $pdo->query("SELECT o.*, u.FullName as CustomerName, p.Method as PayMethod FROM `Order` o LEFT JOIN Customer cu ON o.CustomerID=cu.CustomerID LEFT JOIN User u ON cu.UserID=u.UserID LEFT JOIN Payment p ON o.PaymentID=p.PaymentID ORDER BY o.CreatedAt DESC")->fetchAll();
// Categories
$categories = $pdo->query("SELECT * FROM Category")->fetchAll();
// Ads
$ads = $pdo->query("SELECT a.*, ko.KitchenName, u.FullName as CatererName FROM Advertising a LEFT JOIN KitchenOwner ko ON a.KitchenOwnerID=ko.KitchenOwnerID LEFT JOIN Caterer c ON a.CatererID=c.CatererID LEFT JOIN User u ON c.UserID=u.UserID ORDER BY a.AdvertisingID DESC")->fetchAll();
// Notifications
$notifications = $pdo->query("SELECT * FROM Notification WHERE UserID=1 ORDER BY CreatedAt DESC LIMIT 10")->fetchAll();
// Order stats by status
$orderStats = $pdo->query("SELECT OrderStatus, COUNT(*) as cnt FROM `Order` GROUP BY OrderStatus")->fetchAll();
// Revenue by day (last 7 days)
$dailyRevenue = $pdo->query("SELECT DATE(CreatedAt) as day, COALESCE(SUM(TotalPrice),0) as rev FROM `Order` WHERE CreatedAt >= DATE_SUB(NOW(), INTERVAL 7 DAY) GROUP BY DATE(CreatedAt) ORDER BY day")->fetchAll();

// Admin actions
if (isset($_GET['verify'])) { $pdo->prepare("UPDATE KitchenOwner SET VerifyStatus='Verified',Status='Active' WHERE KitchenOwnerID=?")->execute([intval($_GET['verify'])]); header("Location: admin.php"); exit; }
if (isset($_GET['suspend'])) { $pdo->prepare("UPDATE KitchenOwner SET Status='Suspended' WHERE KitchenOwnerID=?")->execute([intval($_GET['suspend'])]); header("Location: admin.php"); exit; }
if (isset($_GET['activate'])) { $pdo->prepare("UPDATE KitchenOwner SET Status='Active' WHERE KitchenOwnerID=?")->execute([intval($_GET['activate'])]); header("Location: admin.php"); exit; }
if (isset($_GET['del_user'])) { $pdo->prepare("DELETE FROM User WHERE UserID=? AND UserID!=?")->execute([intval($_GET['del_user']),$uid]); header("Location: admin.php"); exit; }
if ($_SERVER['REQUEST_METHOD']==='POST' && isset($_POST['add_cat'])) {
    $pdo->prepare("INSERT INTO Category (Name,Description) VALUES (?,?)")->execute([sanitize($_POST['cat_name']),sanitize($_POST['cat_desc']??'')]);
    header("Location: admin.php"); exit;
}
$currentPage = '';
require_once __DIR__ . '/../includes/header.php';
?>
<div class="dashboard">
    <aside class="sidebar">
        <ul class="sidebar-menu">
            <li><a href="#" class="active" onclick="switchTab('adm','overview');setActive(this)"><i class="fas fa-home"></i> Overview</a></li>
            <li><a href="#" onclick="switchTab('adm','users');setActive(this)"><i class="fas fa-users"></i> Users</a></li>
            <li><a href="#" onclick="switchTab('adm','kitchens');setActive(this)"><i class="fas fa-store"></i> Kitchens</a></li>
            <li><a href="#" onclick="switchTab('adm','orders');setActive(this)"><i class="fas fa-shopping-bag"></i> Orders</a></li>
            <li><a href="#" onclick="switchTab('adm','categories');setActive(this)"><i class="fas fa-tags"></i> Categories</a></li>
            <li><a href="#" onclick="switchTab('adm','analytics');setActive(this)"><i class="fas fa-chart-bar"></i> Analytics</a></li>
            <li><a href="#" onclick="switchTab('adm','ads');setActive(this)"><i class="fas fa-bullhorn"></i> Promotions</a></li>
            <li><a href="#" onclick="switchTab('adm','notifs');setActive(this)"><i class="fas fa-bell"></i> Notifications</a></li>
            <li><a href="../logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
        </ul>
    </aside>
    <main class="dash-content">
        <div class="dash-header"><div><h1>Admin Dashboard 🛡️</h1><p style="color:var(--text-muted)">System overview and management</p></div></div>
        <div class="grid grid-4 mb-3">
            <div class="glass-card stat-card"><div class="stat-icon orange"><i class="fas fa-users"></i></div><div class="stat-value"><?= $totalUsers ?></div><div class="stat-label">Total Users</div></div>
            <div class="glass-card stat-card"><div class="stat-icon green"><i class="fas fa-dollar-sign"></i></div><div class="stat-value"><?= number_format($totalRevenue,2) ?></div><div class="stat-label">Revenue (EGP)</div></div>
            <div class="glass-card stat-card"><div class="stat-icon blue"><i class="fas fa-shopping-bag"></i></div><div class="stat-value"><?= $totalOrders ?></div><div class="stat-label">Total Orders</div></div>
            <div class="glass-card stat-card"><div class="stat-icon purple"><i class="fas fa-store"></i></div><div class="stat-value"><?= $totalKitchens ?></div><div class="stat-label">Kitchens</div></div>
        </div>
        <!-- Overview -->
        <div data-tab-group="adm" data-tab="overview" style="display:block">
            <div class="grid grid-2 mb-3">
                <!-- Quick Stats -->
                <div class="glass-card">
                    <h3 class="mb-2"><i class="fas fa-bolt" style="color:var(--primary)"></i> Quick Stats</h3>
                    <div class="grid grid-2" style="gap:12px">
                        <div style="padding:16px;border-radius:var(--radius);background:rgba(255,107,53,0.08);text-align:center">
                            <div style="font-size:2rem;font-weight:700;color:var(--primary)"><?= $pendingKitchens ?></div>
                            <div style="font-size:0.85rem;color:var(--text-muted)">Pending Verification</div>
                        </div>
                        <div style="padding:16px;border-radius:var(--radius);background:rgba(79,195,247,0.08);text-align:center">
                            <div style="font-size:2rem;font-weight:700;color:var(--info)"><?= $activeOrders ?></div>
                            <div style="font-size:0.85rem;color:var(--text-muted)">Active Orders</div>
                        </div>
                    </div>
                </div>
                <!-- Recent Orders -->
                <div class="glass-card"><h3 class="mb-2">Recent Orders</h3><div class="table-wrap"><table><thead><tr><th>#</th><th>Customer</th><th>Total</th><th>Status</th></tr></thead><tbody>
                <?php foreach(array_slice($orders,0,5) as $o): ?>
                <tr><td>#<?= $o['OrderID'] ?></td><td><?= htmlspecialchars($o['CustomerName']??'N/A') ?></td><td><?= number_format($o['TotalPrice'],2) ?></td>
                <td><span class="badge-status badge-<?= strtolower($o['OrderStatus']??'pending') ?>"><?= $o['OrderStatus']??'Pending' ?></span></td></tr>
                <?php endforeach; ?></tbody></table></div></div>
            </div>
            <div class="glass-card mb-3"><h3 class="mb-2">Kitchen Verification</h3><div class="table-wrap"><table><thead><tr><th>Kitchen</th><th>Owner</th><th>Status</th><th>Action</th></tr></thead><tbody>
                <?php foreach($kitchens as $k): ?>
                <tr><td><?= htmlspecialchars($k['KitchenName']??'') ?></td><td><?= htmlspecialchars($k['FullName']) ?></td>
                <td><span class="badge-status badge-<?= strtolower($k['VerifyStatus']) ?>"><?= $k['VerifyStatus'] ?></span></td>
                <td><?php if($k['VerifyStatus']==='Pending'): ?><a href="?verify=<?= $k['KitchenOwnerID'] ?>" class="btn btn-success btn-sm"><i class="fas fa-check"></i></a><?php endif; ?>
                <?php if($k['Status']!=='Suspended'): ?><a href="?suspend=<?= $k['KitchenOwnerID'] ?>" class="btn btn-danger btn-sm"><i class="fas fa-ban"></i></a>
                <?php else: ?><a href="?activate=<?= $k['KitchenOwnerID'] ?>" class="btn btn-success btn-sm"><i class="fas fa-play"></i></a><?php endif; ?></td></tr>
                <?php endforeach; ?></tbody></table></div></div>
        </div>
        <!-- Users -->
        <div data-tab-group="adm" data-tab="users" style="display:none">
            <div class="glass-card"><h3 class="mb-2">All Users (<?= $totalUsers ?>)</h3><div class="table-wrap"><table><thead><tr><th>ID</th><th>Name</th><th>Email</th><th>Role</th><th>Wallet</th><th>Created</th><th>Action</th></tr></thead><tbody>
            <?php foreach($users as $u): ?>
            <tr><td><?= $u['UserID'] ?></td><td><div style="display:flex;align-items:center;gap:8px"><img src="<?= htmlspecialchars($u['Image']??'') ?>" style="width:30px;height:30px;border-radius:50%"> <?= htmlspecialchars($u['FullName']) ?></div></td>
            <td><?= htmlspecialchars($u['Email']) ?></td><td><span class="badge-status badge-active"><?= $u['Role'] ?></span></td>
            <td><?= number_format($u['Wallet_balance']??0,2) ?> EGP</td>
            <td><?= date('M d, Y',strtotime($u['CreatedAt'])) ?></td>
            <td><?php if($u['UserID']!==$uid): ?><a href="?del_user=<?= $u['UserID'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Delete user?')"><i class="fas fa-trash"></i></a><?php endif; ?></td></tr>
            <?php endforeach; ?></tbody></table></div></div>
        </div>
        <!-- Kitchens -->
        <div data-tab-group="adm" data-tab="kitchens" style="display:none">
            <div class="glass-card"><h3 class="mb-2">All Kitchens</h3><div class="table-wrap"><table><thead><tr><th>Kitchen</th><th>Owner</th><th>Status</th><th>Verified</th><th>Actions</th></tr></thead><tbody>
            <?php foreach($kitchens as $k): ?>
            <tr><td><?= htmlspecialchars($k['KitchenName']??'') ?></td><td><?= htmlspecialchars($k['FullName']) ?></td>
            <td><span class="badge-status badge-<?= strtolower($k['Status']) ?>"><?= $k['Status'] ?></span></td>
            <td><span class="badge-status badge-<?= strtolower($k['VerifyStatus']) ?>"><?= $k['VerifyStatus'] ?></span></td>
            <td><?php if($k['VerifyStatus']==='Pending'): ?><a href="?verify=<?= $k['KitchenOwnerID'] ?>" class="btn btn-success btn-sm"><i class="fas fa-check"></i> Verify</a><?php endif; ?>
            <?php if($k['Status']!=='Suspended'): ?><a href="?suspend=<?= $k['KitchenOwnerID'] ?>" class="btn btn-danger btn-sm"><i class="fas fa-ban"></i></a>
            <?php else: ?><a href="?activate=<?= $k['KitchenOwnerID'] ?>" class="btn btn-success btn-sm"><i class="fas fa-play"></i> Activate</a><?php endif; ?></td></tr>
            <?php endforeach; ?></tbody></table></div></div>
        </div>
        <!-- Orders -->
        <div data-tab-group="adm" data-tab="orders" style="display:none">
            <div class="glass-card"><h3 class="mb-2">All Orders (<?= $totalOrders ?>)</h3><div class="table-wrap"><table><thead><tr><th>#</th><th>Customer</th><th>Total</th><th>Payment</th><th>Status</th><th>Date</th></tr></thead><tbody>
            <?php foreach($orders as $o): ?>
            <tr><td>#<?= $o['OrderID'] ?></td><td><?= htmlspecialchars($o['CustomerName']??'N/A') ?></td><td><?= number_format($o['TotalPrice'],2) ?> EGP</td>
            <td><?= $o['PayMethod'] ?? '—' ?></td><td><span class="badge-status badge-<?= strtolower($o['OrderStatus']??'pending') ?>"><?= $o['OrderStatus']??'Pending' ?></span></td>
            <td><?= date('M d, Y H:i',strtotime($o['CreatedAt'])) ?></td></tr>
            <?php endforeach; ?></tbody></table></div></div>
        </div>
        <!-- Categories -->
        <div data-tab-group="adm" data-tab="categories" style="display:none">
            <div class="grid grid-2">
                <div class="glass-card"><h3 class="mb-2">Categories</h3><div class="table-wrap"><table><thead><tr><th>ID</th><th>Name</th><th>Description</th><th>Status</th></tr></thead><tbody>
                <?php foreach($categories as $c): ?>
                <tr><td><?= $c['CategoryID'] ?></td><td><?= htmlspecialchars($c['Name']) ?></td><td><?= htmlspecialchars(substr($c['Description']??'',0,50)) ?></td><td><span class="badge-status badge-<?= strtolower($c['Status']) ?>"><?= $c['Status'] ?></span></td></tr>
                <?php endforeach; ?></tbody></table></div></div>
                <div class="glass-card"><h3 class="mb-2"><i class="fas fa-plus-circle" style="color:var(--primary)"></i> Add Category</h3>
                <form method="POST">
                    <div class="form-group"><label class="form-label">Name</label><input name="cat_name" class="form-control" required></div>
                    <div class="form-group"><label class="form-label">Description</label><textarea name="cat_desc" class="form-control"></textarea></div>
                    <button type="submit" name="add_cat" class="btn btn-primary btn-block"><i class="fas fa-plus"></i> Add</button>
                </form></div>
            </div>
        </div>
        <!-- Analytics -->
        <div data-tab-group="adm" data-tab="analytics" style="display:none">
            <div class="grid grid-2 mb-3">
                <div class="glass-card">
                    <h3 class="mb-2"><i class="fas fa-chart-pie" style="color:var(--primary)"></i> Orders by Status</h3>
                    <div style="display:flex;flex-direction:column;gap:12px;margin-top:16px">
                        <?php
                        $statusColors = ['Pending'=>'#ffa726','Confirmed'=>'#4fc3f7','Preparing'=>'#ffb74d','Ready'=>'#81c784','Delivering'=>'#42a5f5','Delivered'=>'#66bb6a','Cancelled'=>'#ef5350'];
                        foreach($orderStats as $os):
                            $pct = $totalOrders > 0 ? round(($os['cnt']/$totalOrders)*100) : 0;
                            $color = $statusColors[$os['OrderStatus']] ?? '#888';
                        ?>
                        <div>
                            <div style="display:flex;justify-content:space-between;margin-bottom:4px">
                                <span style="font-size:0.9rem;font-weight:500"><?= $os['OrderStatus'] ?></span>
                                <span style="font-size:0.85rem;color:var(--text-muted)"><?= $os['cnt'] ?> (<?= $pct ?>%)</span>
                            </div>
                            <div style="height:8px;background:var(--bg-dark);border-radius:4px;overflow:hidden">
                                <div style="height:100%;width:<?= $pct ?>%;background:<?= $color ?>;border-radius:4px;transition:width 0.5s ease"></div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <div class="glass-card">
                    <h3 class="mb-2"><i class="fas fa-chart-line" style="color:var(--accent)"></i> Revenue (Last 7 Days)</h3>
                    <div style="display:flex;align-items:flex-end;gap:8px;height:200px;margin-top:16px;padding:0 8px">
                        <?php
                        $maxRev = max(array_column($dailyRevenue, 'rev') ?: [1]);
                        foreach($dailyRevenue as $d):
                            $h = $maxRev > 0 ? max(10, ($d['rev']/$maxRev)*180) : 10;
                        ?>
                        <div style="flex:1;display:flex;flex-direction:column;align-items:center;gap:4px">
                            <span style="font-size:0.7rem;color:var(--text-muted)"><?= number_format($d['rev'],0) ?></span>
                            <div style="width:100%;height:<?= $h ?>px;background:linear-gradient(180deg,var(--primary),var(--accent));border-radius:6px 6px 0 0;transition:height 0.5s ease"></div>
                            <span style="font-size:0.7rem;color:var(--text-muted)"><?= date('D',strtotime($d['day'])) ?></span>
                        </div>
                        <?php endforeach; ?>
                        <?php if(empty($dailyRevenue)): ?><p class="text-muted text-center" style="width:100%">No revenue data yet</p><?php endif; ?>
                    </div>
                </div>
            </div>
            <div class="glass-card">
                <h3 class="mb-2"><i class="fas fa-info-circle" style="color:var(--info)"></i> Platform Summary</h3>
                <div class="grid grid-4" style="gap:16px;margin-top:16px">
                    <?php
                    $totalCustomers = $pdo->query("SELECT COUNT(*) FROM Customer")->fetchColumn();
                    $totalCaterers = $pdo->query("SELECT COUNT(*) FROM Caterer")->fetchColumn();
                    $totalDelivery = $pdo->query("SELECT COUNT(*) FROM DeliveryAgent")->fetchColumn();
                    $totalSubs = $pdo->query("SELECT COUNT(*) FROM Subscription WHERE Status='Active'")->fetchColumn();
                    ?>
                    <div style="text-align:center;padding:16px;border-radius:var(--radius);background:rgba(79,195,247,0.08)"><div style="font-size:1.8rem;font-weight:700;color:var(--info)"><?= $totalCustomers ?></div><div style="font-size:0.85rem;color:var(--text-muted)">Customers</div></div>
                    <div style="text-align:center;padding:16px;border-radius:var(--radius);background:rgba(171,71,188,0.08)"><div style="font-size:1.8rem;font-weight:700;color:#ab47bc"><?= $totalCaterers ?></div><div style="font-size:0.85rem;color:var(--text-muted)">Caterers</div></div>
                    <div style="text-align:center;padding:16px;border-radius:var(--radius);background:rgba(239,83,80,0.08)"><div style="font-size:1.8rem;font-weight:700;color:var(--danger)"><?= $totalDelivery ?></div><div style="font-size:0.85rem;color:var(--text-muted)">Delivery Agents</div></div>
                    <div style="text-align:center;padding:16px;border-radius:var(--radius);background:rgba(102,187,106,0.08)"><div style="font-size:1.8rem;font-weight:700;color:var(--success)"><?= $totalSubs ?></div><div style="font-size:0.85rem;color:var(--text-muted)">Active Subs</div></div>
                </div>
            </div>
        </div>
        <!-- Promotions/Ads -->
        <div data-tab-group="adm" data-tab="ads" style="display:none">
            <div class="glass-card"><h3 class="mb-2"><i class="fas fa-bullhorn" style="color:var(--accent)"></i> Advertisements & Promotions</h3>
            <div class="table-wrap"><table><thead><tr><th>Title</th><th>Provider</th><th>Period</th><th>Status</th></tr></thead><tbody>
            <?php foreach($ads as $a): ?>
            <tr><td><?= htmlspecialchars($a['Title']??'Ad #'.$a['AdvertisingID']) ?></td>
            <td><?= htmlspecialchars($a['KitchenName']??$a['CatererName']??'N/A') ?></td>
            <td><?= $a['StartDate'] ?> → <?= $a['EndDate'] ?></td>
            <td><span class="badge-status badge-<?= strtolower($a['Status']) ?>"><?= $a['Status'] ?></span></td></tr>
            <?php endforeach; ?>
            <?php if(empty($ads)): ?><tr><td colspan="4" class="text-center text-muted">No ads yet</td></tr><?php endif; ?>
            </tbody></table></div></div>
        </div>
        <!-- Notifications -->
        <div data-tab-group="adm" data-tab="notifs" style="display:none">
            <div class="glass-card"><h3 class="mb-2"><i class="fas fa-bell" style="color:var(--warning)"></i> System Notifications</h3>
            <?php foreach($notifications as $n): ?>
            <div style="display:flex;gap:16px;padding:16px;border-bottom:1px solid var(--border-color);align-items:flex-start;opacity:<?= $n['IsRead']?'0.6':'1' ?>">
                <div style="width:40px;height:40px;border-radius:10px;background:rgba(255,167,38,0.15);display:flex;align-items:center;justify-content:center;flex-shrink:0">
                    <i class="fas fa-<?= $n['Type']==='Order'?'shopping-bag':($n['Type']==='Promotion'?'tag':'cog') ?>" style="color:var(--warning)"></i>
                </div>
                <div>
                    <h4 style="font-size:0.95rem;margin-bottom:2px"><?= htmlspecialchars($n['Title']) ?></h4>
                    <p style="color:var(--text-secondary);font-size:0.85rem;margin-bottom:4px"><?= htmlspecialchars($n['Message']) ?></p>
                    <span style="color:var(--text-muted);font-size:0.75rem"><?= date('M d, H:i', strtotime($n['CreatedAt'])) ?></span>
                </div>
                <?php if(!$n['IsRead']): ?><span style="width:8px;height:8px;border-radius:50%;background:var(--primary);flex-shrink:0;margin-top:6px"></span><?php endif; ?>
            </div>
            <?php endforeach; ?>
            <?php if(empty($notifications)): ?><p class="text-center text-muted" style="padding:40px">No notifications</p><?php endif; ?>
            </div>
        </div>
    </main>
</div>
<script>
function setActive(el){document.querySelectorAll('.sidebar-menu a').forEach(a=>a.classList.remove('active'));el.classList.add('active');}
</script>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
