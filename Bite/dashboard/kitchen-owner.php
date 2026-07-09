<?php
$pageTitle = 'Kitchen Dashboard';
require_once __DIR__ . '/../config/db.php';
requireLogin();
$uid = $_SESSION['user_id'];
$ko = $pdo->prepare("SELECT * FROM KitchenOwner WHERE UserID=?"); $ko->execute([$uid]); $kitchen = $ko->fetch();
$koId = $kitchen['KitchenOwnerID'] ?? 0;

// Stats
$orders = $pdo->prepare("SELECT o.*, u.FullName as CustomerName, p.Method as PayMethod FROM `Order` o JOIN MenuOrderItem moi ON o.OrderID=moi.OrderID JOIN MenuItem mi ON moi.MenuItemID=mi.MenuItemID LEFT JOIN Customer cu ON o.CustomerID=cu.CustomerID LEFT JOIN User u ON cu.UserID=u.UserID LEFT JOIN Payment p ON o.PaymentID=p.PaymentID WHERE mi.KitchenOwnerID=? GROUP BY o.OrderID ORDER BY o.CreatedAt DESC");
$orders->execute([$koId]); $orderList = $orders->fetchAll();
$totalRevenue = array_sum(array_column($orderList, 'TotalPrice'));
$menuItems = $pdo->prepare("SELECT mi.*, c.Name as CatName FROM MenuItem mi LEFT JOIN Category c ON mi.CategoryID=c.CategoryID WHERE mi.KitchenOwnerID=? ORDER BY mi.MenuItemID DESC"); $menuItems->execute([$koId]); $menuList = $menuItems->fetchAll();
$categories = $pdo->query("SELECT * FROM Category ORDER BY Name")->fetchAll();

// Reviews
$reviews = $pdo->prepare("SELECT r.*, u.FullName, u.Image FROM Review r JOIN Customer cu ON r.CustomerID=cu.CustomerID JOIN User u ON cu.UserID=u.UserID WHERE r.KitchenOwnerID=? ORDER BY r.CreatedAt DESC");
$reviews->execute([$koId]); $reviewList = $reviews->fetchAll();
$avgRating = count($reviewList) > 0 ? round(array_sum(array_column($reviewList, 'Rating')) / count($reviewList), 1) : 0;

// Actions
if (isset($_GET['del_item'])) {
    $pdo->prepare("DELETE FROM MenuItem WHERE MenuItemID=? AND KitchenOwnerID=?")->execute([intval($_GET['del_item']), $koId]);
    header("Location: kitchen-owner.php"); exit;
}
if (isset($_GET['accept'])) {
    $pdo->prepare("UPDATE `Order` SET OrderStatus='Confirmed' WHERE OrderID=? AND OrderStatus='Pending'")->execute([intval($_GET['accept'])]);
    header("Location: kitchen-owner.php"); exit;
}
if (isset($_GET['reject'])) {
    $pdo->prepare("UPDATE `Order` SET OrderStatus='Cancelled' WHERE OrderID=? AND OrderStatus='Pending'")->execute([intval($_GET['reject'])]);
    header("Location: kitchen-owner.php"); exit;
}
if (isset($_GET['prepare'])) {
    $pdo->prepare("UPDATE `Order` SET OrderStatus='Preparing' WHERE OrderID=? AND OrderStatus='Confirmed'")->execute([intval($_GET['prepare'])]);
    header("Location: kitchen-owner.php"); exit;
}
if (isset($_GET['ready'])) {
    $pdo->prepare("UPDATE `Order` SET OrderStatus='Ready' WHERE OrderID=? AND OrderStatus='Preparing'")->execute([intval($_GET['ready'])]);
    header("Location: kitchen-owner.php"); exit;
}
if ($_SERVER['REQUEST_METHOD']==='POST' && isset($_POST['add_item'])) {
    $pdo->prepare("INSERT INTO MenuItem (ItemName,ItemPrice,CategoryID,KitchenOwnerID,Description) VALUES (?,?,?,?,?)")
        ->execute([sanitize($_POST['name']),floatval($_POST['price']),intval($_POST['category']),$koId,sanitize($_POST['desc']??'')]);
    header("Location: kitchen-owner.php"); exit;
}

$currentPage = '';
require_once __DIR__ . '/../includes/header.php';
?>
<div class="dashboard">
    <aside class="sidebar">
        <ul class="sidebar-menu">
            <li><a href="#" class="active" onclick="switchTab('ko','overview');setActive(this)"><i class="fas fa-home"></i> Overview</a></li>
            <li><a href="#" onclick="switchTab('ko','menu');setActive(this)"><i class="fas fa-utensils"></i> Menu Items</a></li>
            <li><a href="#" onclick="switchTab('ko','orders');setActive(this)"><i class="fas fa-shopping-bag"></i> Orders</a></li>
            <li><a href="#" onclick="switchTab('ko','reviews');setActive(this)"><i class="fas fa-star"></i> Reviews</a></li>
            <li><a href="../logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
        </ul>
    </aside>
    <main class="dash-content">
        <div class="dash-header">
            <div><h1><?= htmlspecialchars($kitchen['KitchenName'] ?? 'My Kitchen') ?> 🍳</h1>
            <p style="color:var(--text-muted)">
                Status: <span class="badge-status badge-<?= strtolower($kitchen['VerifyStatus']??'pending') ?>"><?= $kitchen['VerifyStatus']??'Pending' ?></span>
                <?php if($avgRating > 0): ?> · ⭐ <?= $avgRating ?>/5 (<?= count($reviewList) ?> reviews)<?php endif; ?>
            </p></div>
        </div>
        <div class="grid grid-4 mb-3">
            <div class="glass-card stat-card"><div class="stat-icon orange"><i class="fas fa-shopping-bag"></i></div><div class="stat-value"><?= count($orderList) ?></div><div class="stat-label">Total Orders</div></div>
            <div class="glass-card stat-card"><div class="stat-icon green"><i class="fas fa-dollar-sign"></i></div><div class="stat-value"><?= number_format($totalRevenue,2) ?></div><div class="stat-label">Revenue (EGP)</div></div>
            <div class="glass-card stat-card"><div class="stat-icon blue"><i class="fas fa-utensils"></i></div><div class="stat-value"><?= count($menuList) ?></div><div class="stat-label">Menu Items</div></div>
            <div class="glass-card stat-card"><div class="stat-icon purple"><i class="fas fa-star"></i></div><div class="stat-value"><?= $avgRating ?></div><div class="stat-label">Avg Rating</div></div>
        </div>
        <!-- Overview -->
        <div data-tab-group="ko" data-tab="overview" style="display:block">
            <div class="grid grid-2 mb-3">
                <div class="glass-card"><h3 class="mb-2"><i class="fas fa-clock" style="color:var(--warning)"></i> Pending Orders</h3>
                <?php $pending = array_filter($orderList, fn($o)=>in_array($o['OrderStatus'],['Pending','Confirmed','Preparing']));
                foreach(array_slice($pending,0,5) as $o): ?>
                <div style="display:flex;justify-content:space-between;align-items:center;padding:12px;border-bottom:1px solid var(--border-color)">
                    <div>
                        <span style="font-weight:600">#<?= $o['OrderID'] ?></span> - <?= htmlspecialchars($o['CustomerName']??'Customer') ?>
                        <div style="font-size:0.8rem;color:var(--text-muted)"><?= number_format($o['TotalPrice'],2) ?> EGP · <?= $o['PayMethod']??'—' ?></div>
                    </div>
                    <div style="display:flex;gap:6px">
                        <?php if($o['OrderStatus']==='Pending'): ?>
                        <a href="?accept=<?= $o['OrderID'] ?>" class="btn btn-success btn-sm"><i class="fas fa-check"></i></a>
                        <a href="?reject=<?= $o['OrderID'] ?>" class="btn btn-danger btn-sm"><i class="fas fa-times"></i></a>
                        <?php elseif($o['OrderStatus']==='Confirmed'): ?>
                        <a href="?prepare=<?= $o['OrderID'] ?>" class="btn btn-primary btn-sm"><i class="fas fa-fire"></i> Start</a>
                        <?php elseif($o['OrderStatus']==='Preparing'): ?>
                        <a href="?ready=<?= $o['OrderID'] ?>" class="btn btn-success btn-sm"><i class="fas fa-check-double"></i> Ready</a>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endforeach; ?>
                <?php if(empty($pending)): ?><p class="text-center text-muted" style="padding:20px">No pending orders 🎉</p><?php endif; ?>
                </div>
                <!-- Recent Reviews -->
                <div class="glass-card"><h3 class="mb-2"><i class="fas fa-star" style="color:var(--accent)"></i> Latest Reviews</h3>
                <?php foreach(array_slice($reviewList,0,3) as $r): ?>
                <div style="display:flex;gap:12px;padding:12px;border-bottom:1px solid var(--border-color)">
                    <img src="<?= htmlspecialchars($r['Image']??'') ?>" style="width:36px;height:36px;border-radius:50%">
                    <div style="flex:1">
                        <div style="display:flex;justify-content:space-between;align-items:center">
                            <span style="font-weight:600;font-size:0.9rem"><?= htmlspecialchars($r['FullName']) ?></span>
                            <span style="color:var(--accent);font-size:0.85rem"><?= str_repeat('⭐',$r['Rating']) ?></span>
                        </div>
                        <p style="color:var(--text-secondary);font-size:0.85rem;margin-top:4px"><?= htmlspecialchars($r['Comment']??'') ?></p>
                    </div>
                </div>
                <?php endforeach; ?>
                <?php if(empty($reviewList)): ?><p class="text-center text-muted" style="padding:20px">No reviews yet</p><?php endif; ?>
                </div>
            </div>
        </div>
        <!-- Menu -->
        <div data-tab-group="ko" data-tab="menu" style="display:none">
            <div class="grid grid-2 mb-3">
                <div class="glass-card"><h3 class="mb-2">My Menu (<?= count($menuList) ?> items)</h3><div class="table-wrap"><table><thead><tr><th>Item</th><th>Category</th><th>Price</th><th>Status</th><th>Action</th></tr></thead><tbody>
                <?php foreach($menuList as $m): ?>
                <tr><td><?= htmlspecialchars($m['ItemName']) ?></td><td><span class="badge-status badge-active"><?= $m['CatName']??'—' ?></span></td>
                <td><?= number_format($m['ItemPrice'],2) ?> EGP</td><td><span class="badge-status badge-<?= strtolower($m['Status']) ?>"><?= $m['Status'] ?></span></td>
                <td><a href="?del_item=<?= $m['MenuItemID'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Delete?')"><i class="fas fa-trash"></i></a></td></tr>
                <?php endforeach; ?>
                </tbody></table></div></div>
                <div class="glass-card"><h3 class="mb-2"><i class="fas fa-plus-circle" style="color:var(--primary)"></i> Add Item</h3>
                <form method="POST">
                    <div class="form-group"><label class="form-label">Name</label><input name="name" class="form-control" required></div>
                    <div class="form-group"><label class="form-label">Price (EGP)</label><input name="price" type="number" step="0.01" class="form-control" required></div>
                    <div class="form-group"><label class="form-label">Category</label>
                    <select name="category" class="form-control" required>
                        <?php foreach($categories as $c): ?><option value="<?= $c['CategoryID'] ?>"><?= htmlspecialchars($c['Name']) ?></option><?php endforeach; ?>
                    </select></div>
                    <div class="form-group"><label class="form-label">Description</label><textarea name="desc" class="form-control" rows="3"></textarea></div>
                    <button type="submit" name="add_item" class="btn btn-primary btn-block"><i class="fas fa-plus"></i> Add Item</button>
                </form></div>
            </div>
        </div>
        <!-- All Orders -->
        <div data-tab-group="ko" data-tab="orders" style="display:none">
            <div class="glass-card"><h3 class="mb-2">All Orders</h3><div class="table-wrap"><table><thead><tr><th>#</th><th>Customer</th><th>Total</th><th>Payment</th><th>Status</th><th>Date</th><th>Action</th></tr></thead><tbody>
            <?php foreach($orderList as $o): ?>
            <tr><td>#<?= $o['OrderID'] ?></td><td><?= htmlspecialchars($o['CustomerName']??'N/A') ?></td><td><?= number_format($o['TotalPrice'],2) ?></td><td><?= $o['PayMethod']??'—' ?></td>
            <td><span class="badge-status badge-<?= strtolower($o['OrderStatus']??'pending') ?>"><?= $o['OrderStatus']??'Pending' ?></span></td>
            <td><?= date('M d H:i',strtotime($o['CreatedAt'])) ?></td>
            <td>
                <?php if($o['OrderStatus']==='Pending'): ?>
                <a href="?accept=<?= $o['OrderID'] ?>" class="btn btn-success btn-sm"><i class="fas fa-check"></i></a>
                <a href="?reject=<?= $o['OrderID'] ?>" class="btn btn-danger btn-sm"><i class="fas fa-times"></i></a>
                <?php elseif($o['OrderStatus']==='Confirmed'): ?>
                <a href="?prepare=<?= $o['OrderID'] ?>" class="btn btn-primary btn-sm"><i class="fas fa-fire"></i></a>
                <?php elseif($o['OrderStatus']==='Preparing'): ?>
                <a href="?ready=<?= $o['OrderID'] ?>" class="btn btn-success btn-sm"><i class="fas fa-check-double"></i></a>
                <?php else: ?>—<?php endif; ?>
            </td></tr>
            <?php endforeach; ?></tbody></table></div></div>
        </div>
        <!-- Reviews -->
        <div data-tab-group="ko" data-tab="reviews" style="display:none">
            <div class="glass-card mb-3">
                <h3 class="mb-2"><i class="fas fa-star" style="color:var(--accent)"></i> Customer Reviews (<?= count($reviewList) ?>)</h3>
                <div style="display:flex;gap:24px;align-items:center;padding:16px;margin-bottom:16px;background:rgba(255,167,38,0.08);border-radius:var(--radius)">
                    <div style="text-align:center"><div style="font-size:3rem;font-weight:800;color:var(--accent)"><?= $avgRating ?></div><div style="color:var(--text-muted);font-size:0.85rem">out of 5</div></div>
                    <div style="flex:1">
                        <?php for($s=5;$s>=1;$s--):
                            $cnt = count(array_filter($reviewList, fn($r)=>$r['Rating']==$s));
                            $pct = count($reviewList) > 0 ? round(($cnt/count($reviewList))*100) : 0;
                        ?>
                        <div style="display:flex;align-items:center;gap:8px;margin-bottom:4px">
                            <span style="font-size:0.8rem;width:15px;text-align:right"><?= $s ?></span>
                            <div style="flex:1;height:6px;background:var(--bg-dark);border-radius:3px;overflow:hidden"><div style="height:100%;width:<?= $pct ?>%;background:var(--accent);border-radius:3px"></div></div>
                            <span style="font-size:0.75rem;color:var(--text-muted);width:25px"><?= $cnt ?></span>
                        </div>
                        <?php endfor; ?>
                    </div>
                </div>
                <?php foreach($reviewList as $r): ?>
                <div style="display:flex;gap:16px;padding:16px;border-bottom:1px solid var(--border-color)">
                    <img src="<?= htmlspecialchars($r['Image']??'') ?>" style="width:42px;height:42px;border-radius:50%;flex-shrink:0">
                    <div style="flex:1">
                        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:4px">
                            <span style="font-weight:600"><?= htmlspecialchars($r['FullName']) ?></span>
                            <span style="color:var(--accent)"><?= str_repeat('⭐',$r['Rating']) ?></span>
                        </div>
                        <p style="color:var(--text-secondary);font-size:0.9rem;margin-bottom:4px"><?= htmlspecialchars($r['Comment']??'') ?></p>
                        <span style="color:var(--text-muted);font-size:0.75rem"><?= date('M d, Y',strtotime($r['CreatedAt'])) ?></span>
                    </div>
                </div>
                <?php endforeach; ?>
                <?php if(empty($reviewList)): ?><p class="text-center text-muted" style="padding:40px">No reviews yet</p><?php endif; ?>
            </div>
        </div>
    </main>
</div>
<script>
function setActive(el){document.querySelectorAll('.sidebar-menu a').forEach(a=>a.classList.remove('active'));el.classList.add('active');}
</script>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
