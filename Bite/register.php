<?php
$pageTitle = 'Register';
require_once __DIR__ . '/config/db.php';
$success = false;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = sanitize($_POST['name'] ?? '');
    $email = sanitize($_POST['email'] ?? '');
    $pass = $_POST['password'] ?? '';
    $role = sanitize($_POST['role'] ?? 'Customer');
    $phone = sanitize($_POST['phone'] ?? '');
    $address = sanitize($_POST['address'] ?? '');
    try {
        $img = 'https://ui-avatars.com/api/?name=' . urlencode($name) . '&background=ff6b35&color=fff';
        $stmt = $pdo->prepare("INSERT INTO User (FullName,Email,Password,Role,Image) VALUES (?,?,?,?,?)");
        $stmt->execute([$name, $email, $pass, $role, $img]);
        $uid = $pdo->lastInsertId();
        switch($role) {
            case 'Customer': $pdo->prepare("INSERT INTO Customer (UserID) VALUES (?)")->execute([$uid]); break;
            case 'KitchenOwner':
                $kname = sanitize($_POST['kitchen_name'] ?? $name."'s Kitchen");
                $pdo->prepare("INSERT INTO KitchenOwner (UserID,KitchenName) VALUES (?,?)")->execute([$uid,$kname]); break;
            case 'Caterer': $pdo->prepare("INSERT INTO Caterer (UserID) VALUES (?)")->execute([$uid]); break;
            case 'DeliveryAgent':
                $vtype = sanitize($_POST['vehicle_type'] ?? 'Bike');
                $pdo->prepare("INSERT INTO DeliveryAgent (UserID,VehicleType) VALUES (?,?)")->execute([$uid,$vtype]); break;
        }
        if ($phone) $pdo->prepare("INSERT INTO UserPhone VALUES (?,?)")->execute([$uid,$phone]);
        if ($address) $pdo->prepare("INSERT INTO UserAddress VALUES (?,?)")->execute([$uid,$address]);
        $success = true;
    } catch(PDOException $e) {
        $error = strpos($e->getMessage(), 'Duplicate') !== false ? "Email already registered." : "Registration failed. Please try again.";
    }
}
require_once __DIR__ . '/includes/header.php';
?>
<div class="auth-page">
    <div class="glass-card auth-card animate-fadeInUp" style="max-width:520px">
        <div class="text-center mb-3"><i class="fas fa-fire" style="font-size:2.5rem;color:var(--primary)"></i></div>
        <h2>Create Account</h2>
        <p class="subtitle">Join BiteHub today</p>
        <?php if($success): ?>
        <div style="background:rgba(102,187,106,0.1);border:1px solid var(--success);border-radius:var(--radius);padding:20px;text-align:center;color:var(--success)">
            <i class="fas fa-check-circle" style="font-size:2rem;margin-bottom:8px;display:block"></i>
            Registration successful! <a href="login.php" style="color:var(--primary);font-weight:600">Login now</a>
        </div>
        <?php else: ?>
        <?php if(isset($error)): ?>
        <div style="background:rgba(239,83,80,0.1);border:1px solid var(--danger);border-radius:var(--radius);padding:12px;margin-bottom:16px;color:var(--danger);font-size:0.9rem;text-align:center">
            <i class="fas fa-exclamation-circle"></i> <?= $error ?>
        </div>
        <?php endif; ?>
        <form method="POST">
            <div class="form-group"><label class="form-label">Full Name</label><input name="name" class="form-control" placeholder="Enter your full name" required></div>
            <div class="form-group"><label class="form-label">Email Address</label><input type="email" name="email" class="form-control" placeholder="you@example.com" required></div>
            <div class="form-group"><label class="form-label">Password</label><input type="password" name="password" class="form-control" placeholder="Create a password" required></div>
            <div class="form-group"><label class="form-label">Phone Number</label><input name="phone" class="form-control" placeholder="+20-xxx-xxx-xxxx"></div>
            <div class="form-group"><label class="form-label">Address</label><input name="address" class="form-control" placeholder="Your address"></div>
            <div class="form-group">
                <label class="form-label">I want to join as</label>
                <div class="role-selector">
                    <label class="role-option active" onclick="selectRole(this,'Customer')"><input type="radio" name="role" value="Customer" checked><div class="role-icon">🛒</div><div class="role-name">Customer</div></label>
                    <label class="role-option" onclick="selectRole(this,'KitchenOwner')"><input type="radio" name="role" value="KitchenOwner"><div class="role-icon">👨‍🍳</div><div class="role-name">Kitchen Owner</div></label>
                    <label class="role-option" onclick="selectRole(this,'Caterer')"><input type="radio" name="role" value="Caterer"><div class="role-icon">🎪</div><div class="role-name">Caterer</div></label>
                    <label class="role-option" onclick="selectRole(this,'DeliveryAgent')"><input type="radio" name="role" value="DeliveryAgent"><div class="role-icon">🚲</div><div class="role-name">Delivery Agent</div></label>
                </div>
            </div>
            <div id="kitchen-fields" style="display:none"><div class="form-group"><label class="form-label">Kitchen Name</label><input name="kitchen_name" class="form-control" placeholder="Your kitchen name"></div></div>
            <div id="delivery-fields" style="display:none"><div class="form-group"><label class="form-label">Vehicle Type</label><select name="vehicle_type" class="form-control"><option>Bike</option><option>Car</option><option>Motorcycle</option></select></div></div>
            <button type="submit" class="btn btn-primary btn-block btn-lg"><i class="fas fa-user-plus"></i> Create Account</button>
        </form>
        <div class="auth-footer">Already have an account? <a href="login.php">Sign in</a></div>
        <?php endif; ?>
    </div>
</div>
<script>
function selectRole(el, role) {
    document.querySelectorAll('.role-option').forEach(o => o.classList.remove('active'));
    el.classList.add('active');
    document.getElementById('kitchen-fields').style.display = role === 'KitchenOwner' ? 'block' : 'none';
    document.getElementById('delivery-fields').style.display = role === 'DeliveryAgent' ? 'block' : 'none';
}
</script>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
