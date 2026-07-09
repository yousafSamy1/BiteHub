<?php
$pageTitle = 'Caterer Dashboard';
require_once __DIR__ . '/../config/db.php';
requireLogin();
$uid = $_SESSION['user_id'];
$cat = $pdo->prepare("SELECT * FROM Caterer WHERE UserID=?"); $cat->execute([$uid]); $caterer = $cat->fetch();
$catId = $caterer['CatererID'] ?? 0;

// Packages (menu items)
$pkgs = $pdo->prepare("SELECT mi.*, c.Name as CatName FROM MenuItem mi LEFT JOIN Category c ON mi.CategoryID=c.CategoryID WHERE mi.CatererID=? ORDER BY mi.MenuItemID DESC");
$pkgs->execute([$catId]); $pkgList = $pkgs->fetchAll();

// Catering Requests
$reqs = $pdo->prepare("SELECT cr.*, u.FullName, u.Email FROM CateringRequest cr JOIN Customer cu ON cr.CustomerID=cu.CustomerID JOIN User u ON cu.UserID=u.UserID WHERE cr.CatererID=? ORDER BY cr.CreatedAt DESC");
$reqs->execute([$catId]); $reqList = $reqs->fetchAll();

$pendingReqs = count(array_filter($reqList, fn($r)=>$r['Status']==='Pending'));
$totalEarnings = array_sum(array_column(array_filter($reqList, fn($r)=>$r['Status']==='Accepted'||$r['Status']==='Completed'), 'Budget'));
$categories = $pdo->query("SELECT * FROM Category ORDER BY Name")->fetchAll();

// Actions
if (isset($_GET['accept_req'])) { $pdo->prepare("UPDATE CateringRequest SET Status='Accepted' WHERE RequestID=? AND CatererID=?")->execute([intval($_GET['accept_req']),$catId]); header("Location: caterer.php"); exit; }
if (isset($_GET['reject_req'])) { $pdo->prepare("UPDATE CateringRequest SET Status='Rejected' WHERE RequestID=? AND CatererID=?")->execute([intval($_GET['reject_req']),$catId]); header("Location: caterer.php"); exit; }
if (isset($_GET['complete_req'])) { $pdo->prepare("UPDATE CateringRequest SET Status='Completed' WHERE RequestID=? AND CatererID=?")->execute([intval($_GET['complete_req']),$catId]); header("Location: caterer.php"); exit; }
if (isset($_GET['del_pkg'])) { $pdo->prepare("DELETE FROM MenuItem WHERE MenuItemID=? AND CatererID=?")->execute([intval($_GET['del_pkg']),$catId]); header("Location: caterer.php"); exit; }
if ($_SERVER['REQUEST_METHOD']==='POST' && isset($_POST['add_pkg'])) {
    $pdo->prepare("INSERT INTO MenuItem (ItemName,ItemPrice,CategoryID,CatererID,Description) VALUES (?,?,?,?,?)")
        ->execute([sanitize($_POST['name']),floatval($_POST['price']),intval($_POST['category']),$catId,sanitize($_POST['desc']??'')]);
    header("Location: caterer.php"); exit;
}

$currentPage = '';
require_once __DIR__ . '/../includes/header.php';
?>
<div class="dashboard">
    <aside class="sidebar">
        <ul class="sidebar-menu">
            <li><a href="#" class="active" onclick="switchTab('cat','overview');setActive(this)"><i class="fas fa-home"></i> Overview</a></li>
            <li><a href="#" onclick="switchTab('cat','packages');setActive(this)"><i class="fas fa-box"></i> Packages</a></li>
            <li><a href="#" onclick="switchTab('cat','requests');setActive(this)"><i class="fas fa-clipboard-list"></i> Requests <?php if($pendingReqs): ?><span style="background:var(--primary);color:#fff;font-size:0.7rem;padding:2px 6px;border-radius:10px;margin-left:4px"><?= $pendingReqs ?></span><?php endif; ?></a></li>
            <li><a href="../logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
        </ul>
    </aside>
    <main class="dash-content">
        <div class="dash-header">
            <div><h1><?= htmlspecialchars($caterer['BusinessName'] ?? 'Catering Business') ?> 🍽️</h1>
            <p style="color:var(--text-muted)"><?= htmlspecialchars(substr($caterer['Description']??'',0,80)) ?></p></div>
        </div>
        <div class="grid grid-4 mb-3">
            <div class="glass-card stat-card"><div class="stat-icon orange"><i class="fas fa-box"></i></div><div class="stat-value"><?= count($pkgList) ?></div><div class="stat-label">Packages</div></div>
            <div class="glass-card stat-card"><div class="stat-icon blue"><i class="fas fa-clipboard-list"></i></div><div class="stat-value"><?= count($reqList) ?></div><div class="stat-label">Total Requests</div></div>
            <div class="glass-card stat-card"><div class="stat-icon green"><i class="fas fa-dollar-sign"></i></div><div class="stat-value"><?= number_format($totalEarnings,0) ?></div><div class="stat-label">Earnings (EGP)</div></div>
            <div class="glass-card stat-card"><div class="stat-icon purple"><i class="fas fa-hourglass-half"></i></div><div class="stat-value"><?= $pendingReqs ?></div><div class="stat-label">Pending</div></div>
        </div>
        <!-- Overview -->
        <div data-tab-group="cat" data-tab="overview" style="display:block">
            <div class="grid grid-2 mb-3">
                <div class="glass-card"><h3 class="mb-2"><i class="fas fa-clock" style="color:var(--warning)"></i> Recent Requests</h3>
                <?php foreach(array_slice($reqList,0,4) as $r): ?>
                <div style="display:flex;justify-content:space-between;align-items:center;padding:14px;border-bottom:1px solid var(--border-color)">
                    <div>
                        <div style="font-weight:600"><?= htmlspecialchars($r['EventType']) ?></div>
                        <div style="font-size:0.8rem;color:var(--text-muted)"><?= htmlspecialchars($r['FullName']) ?> · <?= $r['GuestCount'] ?> guests · <?= number_format($r['Budget'],0) ?> EGP</div>
                        <div style="font-size:0.75rem;color:var(--text-muted)"><?= $r['EventDate'] ?></div>
                    </div>
                    <span class="badge-status badge-<?= strtolower($r['Status']) ?>"><?= $r['Status'] ?></span>
                </div>
                <?php endforeach; ?>
                <?php if(empty($reqList)): ?><p class="text-center text-muted" style="padding:30px">No requests yet</p><?php endif; ?>
                </div>
                <div class="glass-card"><h3 class="mb-2"><i class="fas fa-box" style="color:var(--primary)"></i> My Packages</h3>
                <?php foreach(array_slice($pkgList,0,4) as $p): ?>
                <div style="display:flex;justify-content:space-between;align-items:center;padding:14px;border-bottom:1px solid var(--border-color)">
                    <div>
                        <div style="font-weight:600"><?= htmlspecialchars($p['ItemName']) ?></div>
                        <div style="font-size:0.8rem;color:var(--text-muted)"><?= htmlspecialchars(substr($p['Description']??'',0,60)) ?></div>
                    </div>
                    <span style="font-weight:700;color:var(--primary)"><?= number_format($p['ItemPrice'],0) ?> EGP</span>
                </div>
                <?php endforeach; ?>
                <?php if(empty($pkgList)): ?><p class="text-center text-muted" style="padding:30px">No packages yet</p><?php endif; ?>
                </div>
            </div>
        </div>
        <!-- Packages -->
        <div data-tab-group="cat" data-tab="packages" style="display:none">
            <div class="grid grid-2 mb-3">
                <div class="glass-card"><h3 class="mb-2">All Packages</h3><div class="table-wrap"><table><thead><tr><th>Package</th><th>Category</th><th>Price</th><th>Status</th><th>Action</th></tr></thead><tbody>
                <?php foreach($pkgList as $p): ?>
                <tr><td><?= htmlspecialchars($p['ItemName']) ?></td><td><?= $p['CatName']??'—' ?></td><td><?= number_format($p['ItemPrice'],2) ?> EGP</td>
                <td><span class="badge-status badge-<?= strtolower($p['Status']) ?>"><?= $p['Status'] ?></span></td>
                <td><a href="?del_pkg=<?= $p['MenuItemID'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Delete?')"><i class="fas fa-trash"></i></a></td></tr>
                <?php endforeach; ?></tbody></table></div></div>
                <div class="glass-card"><h3 class="mb-2"><i class="fas fa-plus-circle" style="color:var(--primary)"></i> Add Package</h3>
                <form method="POST">
                    <div class="form-group"><label class="form-label">Package Name</label><input name="name" class="form-control" required placeholder="e.g. Wedding Gold Package"></div>
                    <div class="form-group"><label class="form-label">Price (EGP)</label><input name="price" type="number" step="0.01" class="form-control" required></div>
                    <div class="form-group"><label class="form-label">Category</label>
                    <select name="category" class="form-control" required><?php foreach($categories as $c): ?><option value="<?= $c['CategoryID'] ?>"><?= htmlspecialchars($c['Name']) ?></option><?php endforeach; ?></select></div>
                    <div class="form-group"><label class="form-label">Description</label><textarea name="desc" class="form-control" rows="3" placeholder="Describe what's included..."></textarea></div>
                    <button type="submit" name="add_pkg" class="btn btn-primary btn-block"><i class="fas fa-plus"></i> Add Package</button>
                </form></div>
            </div>
        </div>
        <!-- Catering Requests -->
        <div data-tab-group="cat" data-tab="requests" style="display:none">
            <div class="glass-card"><h3 class="mb-2"><i class="fas fa-clipboard-list" style="color:var(--info)"></i> Catering Requests</h3>
            <?php foreach($reqList as $r): ?>
            <div style="padding:20px;border-bottom:1px solid var(--border-color)">
                <div style="display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:12px">
                    <div>
                        <h4 style="font-size:1.1rem"><?= htmlspecialchars($r['EventType']) ?> <span class="badge-status badge-<?= strtolower($r['Status']) ?>"><?= $r['Status'] ?></span></h4>
                        <p style="color:var(--text-muted);font-size:0.85rem;margin-top:4px">By <?= htmlspecialchars($r['FullName']) ?> (<?= htmlspecialchars($r['Email']) ?>)</p>
                    </div>
                    <div style="text-align:right">
                        <div style="font-weight:700;color:var(--primary);font-size:1.2rem"><?= number_format($r['Budget'],0) ?> EGP</div>
                        <div style="font-size:0.8rem;color:var(--text-muted)"><?= $r['GuestCount'] ?> guests</div>
                    </div>
                </div>
                <div style="display:flex;gap:20px;margin-bottom:12px">
                    <div style="display:flex;align-items:center;gap:6px;color:var(--text-secondary);font-size:0.9rem"><i class="fas fa-calendar" style="color:var(--primary)"></i> <?= $r['EventDate'] ?></div>
                    <div style="display:flex;align-items:center;gap:6px;color:var(--text-secondary);font-size:0.9rem"><i class="fas fa-clock" style="color:var(--info)"></i> <?= date('M d, Y',strtotime($r['CreatedAt'])) ?></div>
                </div>
                <?php if($r['Details']): ?><p style="color:var(--text-secondary);font-size:0.9rem;padding:12px;background:var(--bg-dark);border-radius:var(--radius);margin-bottom:12px"><?= htmlspecialchars($r['Details']) ?></p><?php endif; ?>
                <?php if($r['Status']==='Pending'): ?>
                <div style="display:flex;gap:8px">
                    <a href="?accept_req=<?= $r['RequestID'] ?>" class="btn btn-success btn-sm"><i class="fas fa-check"></i> Accept</a>
                    <a href="?reject_req=<?= $r['RequestID'] ?>" class="btn btn-danger btn-sm"><i class="fas fa-times"></i> Reject</a>
                </div>
                <?php elseif($r['Status']==='Accepted'): ?>
                <a href="?complete_req=<?= $r['RequestID'] ?>" class="btn btn-success btn-sm"><i class="fas fa-check-double"></i> Mark Completed</a>
                <?php endif; ?>
            </div>
            <?php endforeach; ?>
            <?php if(empty($reqList)): ?><p class="text-center text-muted" style="padding:40px">No catering requests yet</p><?php endif; ?>
            </div>
        </div>
    </main>
</div>
<script>
function setActive(el){document.querySelectorAll('.sidebar-menu a').forEach(a=>a.classList.remove('active'));el.classList.add('active');}
</script>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
