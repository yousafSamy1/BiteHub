<?php
$pageTitle = 'Delivery Dashboard';
require_once __DIR__ . '/../config/db.php';
requireLogin();
$uid = $_SESSION['user_id'];
$da = $pdo->prepare("SELECT * FROM DeliveryAgent WHERE UserID=?"); $da->execute([$uid]); $agent = $da->fetch();
$daId = $agent['DeliveryAgentID'] ?? 0;

// Toggle status
if (isset($_GET['toggle_status'])) {
    $newStatus = $agent['Status'] === 'Available' ? 'Offline' : 'Available';
    $pdo->prepare("UPDATE DeliveryAgent SET Status=? WHERE DeliveryAgentID=?")->execute([$newStatus, $daId]);
    header("Location: delivery.php"); exit;
}

// Update order delivery status
if (isset($_GET['deliver'])) {
    $pdo->prepare("UPDATE `Order` SET OrderStatus='Delivering' WHERE OrderID=? AND DeliveryAgentID=? AND OrderStatus='Ready'")->execute([intval($_GET['deliver']), $daId]);
    header("Location: delivery.php"); exit;
}
if (isset($_GET['complete'])) {
    $pdo->prepare("UPDATE `Order` SET OrderStatus='Delivered' WHERE OrderID=? AND DeliveryAgentID=? AND OrderStatus='Delivering'")->execute([intval($_GET['complete']), $daId]);
    header("Location: delivery.php"); exit;
}

// Get assignments
$deliveries = $pdo->prepare("SELECT o.*, u.FullName as CustomerName, cu.CustomerID, p.Method as PayMethod FROM `Order` o LEFT JOIN Customer cu ON o.CustomerID=cu.CustomerID LEFT JOIN User u ON cu.UserID=u.UserID LEFT JOIN Payment p ON o.PaymentID=p.PaymentID WHERE o.DeliveryAgentID=? ORDER BY o.CreatedAt DESC");
$deliveries->execute([$daId]); $deliveryList = $deliveries->fetchAll();

$completed = count(array_filter($deliveryList, fn($d)=>$d['OrderStatus']==='Delivered'));
$active = count(array_filter($deliveryList, fn($d)=>in_array($d['OrderStatus'],['Ready','Delivering'])));
$totalEarnings = $completed * 15; // 15 EGP per delivery
// Tips from special requests 
$totalTips = 0;

// Notifications
$notifs = $pdo->prepare("SELECT * FROM Notification WHERE UserID=? ORDER BY CreatedAt DESC LIMIT 8");
$notifs->execute([$uid]); $notifList = $notifs->fetchAll();

$currentPage = '';
require_once __DIR__ . '/../includes/header.php';
?>
<div class="dashboard">
    <aside class="sidebar">
        <ul class="sidebar-menu">
            <li><a href="#" class="active" onclick="switchTab('del','overview');setActive(this)"><i class="fas fa-home"></i> Overview</a></li>
            <li><a href="#" onclick="switchTab('del','deliveries');setActive(this)"><i class="fas fa-truck"></i> All Deliveries</a></li>
            <li><a href="#" onclick="switchTab('del','earnings');setActive(this)"><i class="fas fa-wallet"></i> Earnings</a></li>
            <li><a href="#" onclick="switchTab('del','notifs');setActive(this)"><i class="fas fa-bell"></i> Notifications</a></li>
            <li><a href="../logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
        </ul>
    </aside>
    <main class="dash-content">
        <div class="dash-header">
            <div><h1>Delivery Dashboard 🚲</h1>
            <p style="color:var(--text-muted)"><?= htmlspecialchars($agent['VehicleType']??'') ?> · <?= htmlspecialchars($agent['PlateNumber']??'') ?></p></div>
            <a href="?toggle_status=1" class="btn <?= $agent['Status']==='Available'?'btn-success':'btn-danger' ?>" style="min-width:160px">
                <i class="fas fa-<?= $agent['Status']==='Available'?'check-circle':'times-circle' ?>"></i>
                <?= $agent['Status']==='Available'?'Online – Available':'Offline' ?>
            </a>
        </div>
        <div class="grid grid-4 mb-3">
            <div class="glass-card stat-card"><div class="stat-icon orange"><i class="fas fa-truck"></i></div><div class="stat-value"><?= count($deliveryList) ?></div><div class="stat-label">Total Deliveries</div></div>
            <div class="glass-card stat-card"><div class="stat-icon green"><i class="fas fa-check-circle"></i></div><div class="stat-value"><?= $completed ?></div><div class="stat-label">Completed</div></div>
            <div class="glass-card stat-card"><div class="stat-icon blue"><i class="fas fa-bolt"></i></div><div class="stat-value"><?= $active ?></div><div class="stat-label">Active Now</div></div>
            <div class="glass-card stat-card"><div class="stat-icon purple"><i class="fas fa-wallet"></i></div><div class="stat-value"><?= number_format($totalEarnings,0) ?></div><div class="stat-label">Earnings (EGP)</div></div>
        </div>
        <!-- Overview -->
        <div data-tab-group="del" data-tab="overview" style="display:block">
            <div class="glass-card mb-3">
                <h3 class="mb-2"><i class="fas fa-bolt" style="color:var(--warning)"></i> Active Deliveries</h3>
                <?php $activeDeliveries = array_filter($deliveryList, fn($d)=>in_array($d['OrderStatus'],['Ready','Delivering','Confirmed','Preparing']));
                foreach($activeDeliveries as $d): ?>
                <div style="display:flex;justify-content:space-between;align-items:center;padding:16px;border-bottom:1px solid var(--border-color)">
                    <div>
                        <span style="font-weight:700;font-size:1.1rem">#<?= $d['OrderID'] ?></span>
                        <span style="color:var(--text-secondary);margin-left:8px"><?= htmlspecialchars($d['CustomerName']??'Customer') ?></span>
                        <div style="font-size:0.8rem;color:var(--text-muted);margin-top:4px">
                            <i class="fas fa-money-bill"></i> <?= number_format($d['TotalPrice'],2) ?> EGP · <?= $d['PayMethod']??'—' ?>
                            <?php if($d['SpecialRequests']): ?><br><i class="fas fa-sticky-note"></i> <?= htmlspecialchars(substr($d['SpecialRequests'],0,50)) ?><?php endif; ?>
                        </div>
                    </div>
                    <div style="display:flex;align-items:center;gap:10px">
                        <span class="badge-status badge-<?= strtolower($d['OrderStatus']) ?>"><?= $d['OrderStatus'] ?></span>
                        <?php if($d['OrderStatus']==='Ready'): ?>
                        <a href="?deliver=<?= $d['OrderID'] ?>" class="btn btn-primary btn-sm"><i class="fas fa-motorcycle"></i> Pickup</a>
                        <?php elseif($d['OrderStatus']==='Delivering'): ?>
                        <a href="?complete=<?= $d['OrderID'] ?>" class="btn btn-success btn-sm"><i class="fas fa-check"></i> Delivered</a>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endforeach; ?>
                <?php if(empty($activeDeliveries)): ?>
                <div style="text-align:center;padding:40px">
                    <i class="fas fa-couch" style="font-size:3rem;color:var(--text-muted);margin-bottom:12px;display:block"></i>
                    <p style="color:var(--text-muted)">No active deliveries. <?= $agent['Status']==='Available'?'Waiting for new orders...':'Go online to receive orders!' ?></p>
                </div>
                <?php endif; ?>
            </div>
        </div>
        <!-- All Deliveries -->
        <div data-tab-group="del" data-tab="deliveries" style="display:none">
            <div class="glass-card"><h3 class="mb-2">Delivery History</h3>
            <div class="table-wrap"><table><thead><tr><th>Order #</th><th>Customer</th><th>Total</th><th>Payment</th><th>Status</th><th>Date</th><th>Action</th></tr></thead><tbody>
            <?php foreach($deliveryList as $d): ?>
            <tr><td>#<?= $d['OrderID'] ?></td><td><?= htmlspecialchars($d['CustomerName']??'N/A') ?></td><td><?= number_format($d['TotalPrice'],2) ?></td><td><?= $d['PayMethod']??'—' ?></td>
            <td><span class="badge-status badge-<?= strtolower($d['OrderStatus']) ?>"><?= $d['OrderStatus'] ?></span></td>
            <td><?= date('M d H:i',strtotime($d['CreatedAt'])) ?></td>
            <td>
            <?php if($d['OrderStatus']==='Ready'): ?><a href="?deliver=<?= $d['OrderID'] ?>" class="btn btn-primary btn-sm"><i class="fas fa-truck"></i></a>
            <?php elseif($d['OrderStatus']==='Delivering'): ?><a href="?complete=<?= $d['OrderID'] ?>" class="btn btn-success btn-sm"><i class="fas fa-check"></i></a>
            <?php else: ?>—<?php endif; ?>
            </td></tr>
            <?php endforeach; ?></tbody></table></div></div>
        </div>
        <!-- Earnings -->
        <div data-tab-group="del" data-tab="earnings" style="display:none">
            <div class="grid grid-3 mb-3">
                <div class="glass-card text-center" style="padding:32px">
                    <div style="font-size:3rem;font-weight:800;background:linear-gradient(135deg,var(--primary),var(--accent));-webkit-background-clip:text;-webkit-text-fill-color:transparent"><?= number_format($totalEarnings,0) ?></div>
                    <p style="color:var(--text-muted)">Total Earnings (EGP)</p>
                </div>
                <div class="glass-card text-center" style="padding:32px">
                    <div style="font-size:2.5rem;font-weight:700;color:var(--success)"><?= $completed ?></div>
                    <p style="color:var(--text-muted)">Deliveries Completed</p>
                </div>
                <div class="glass-card text-center" style="padding:32px">
                    <div style="font-size:2.5rem;font-weight:700;color:var(--info)">15</div>
                    <p style="color:var(--text-muted)">EGP per Delivery</p>
                </div>
            </div>
            <div class="glass-card"><h3 class="mb-2"><i class="fas fa-receipt" style="color:var(--accent)"></i> Completed Deliveries</h3>
            <div class="table-wrap"><table><thead><tr><th>Order #</th><th>Customer</th><th>Order Total</th><th>Your Earning</th><th>Date</th></tr></thead><tbody>
            <?php foreach(array_filter($deliveryList, fn($d)=>$d['OrderStatus']==='Delivered') as $d): ?>
            <tr><td>#<?= $d['OrderID'] ?></td><td><?= htmlspecialchars($d['CustomerName']??'N/A') ?></td><td><?= number_format($d['TotalPrice'],2) ?></td>
            <td style="font-weight:700;color:var(--success)">+15.00 EGP</td><td><?= date('M d, Y',strtotime($d['CreatedAt'])) ?></td></tr>
            <?php endforeach; ?></tbody></table></div></div>
        </div>
        <!-- Notifications -->
        <div data-tab-group="del" data-tab="notifs" style="display:none">
            <div class="glass-card"><h3 class="mb-2"><i class="fas fa-bell" style="color:var(--warning)"></i> Notifications</h3>
            <?php foreach($notifList as $n): ?>
            <div style="display:flex;gap:16px;padding:16px;border-bottom:1px solid var(--border-color);opacity:<?= $n['IsRead']?'0.6':'1' ?>">
                <div style="width:40px;height:40px;border-radius:10px;background:rgba(255,167,38,0.15);display:flex;align-items:center;justify-content:center;flex-shrink:0">
                    <i class="fas fa-<?= $n['Type']==='Order'?'truck':'bell' ?>" style="color:var(--warning)"></i>
                </div>
                <div>
                    <h4 style="font-size:0.95rem"><?= htmlspecialchars($n['Title']) ?></h4>
                    <p style="color:var(--text-secondary);font-size:0.85rem"><?= htmlspecialchars($n['Message']) ?></p>
                    <span style="color:var(--text-muted);font-size:0.75rem"><?= date('M d, H:i',strtotime($n['CreatedAt'])) ?></span>
                </div>
            </div>
            <?php endforeach; ?>
            <?php if(empty($notifList)): ?><p class="text-center text-muted" style="padding:40px">No notifications</p><?php endif; ?>
            </div>
        </div>
    </main>
</div>
<script>
function setActive(el){document.querySelectorAll('.sidebar-menu a').forEach(a=>a.classList.remove('active'));el.classList.add('active');}
</script>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
