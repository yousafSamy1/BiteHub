<?php
$pageTitle = 'Login';
require_once __DIR__ . '/config/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = sanitize($_POST['email'] ?? '');
    $pass = $_POST['password'] ?? '';
    $stmt = $pdo->prepare("SELECT * FROM User WHERE Email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();
    if ($user && $user['Password'] === $pass) {
        $_SESSION['user_id'] = $user['UserID'];
        $_SESSION['full_name'] = $user['FullName'];
        $_SESSION['email'] = $user['Email'];
        $_SESSION['role'] = $user['Role'];
        $_SESSION['image'] = $user['Image'];
        $dashMap = ['Admin'=>'admin','Customer'=>'customer','KitchenOwner'=>'kitchen-owner','Caterer'=>'caterer','DeliveryAgent'=>'delivery'];
        redirect('dashboard/' . ($dashMap[$user['Role']] ?? 'customer') . '.php');
    } else {
        $error = "Invalid email or password.";
    }
}
require_once __DIR__ . '/includes/header.php';
?>

<div class="auth-page">
    <div class="glass-card auth-card animate-fadeInUp">
        <div class="text-center mb-3">
            <i class="fas fa-fire" style="font-size:2.5rem;color:var(--primary)"></i>
        </div>
        <h2>Welcome Back</h2>
        <p class="subtitle">Sign in to your BiteHub account</p>
        <?php if(isset($error)): ?>
        <div style="background:rgba(239,83,80,0.1);border:1px solid var(--danger);border-radius:var(--radius);padding:12px;margin-bottom:16px;color:var(--danger);font-size:0.9rem;text-align:center">
            <i class="fas fa-exclamation-circle"></i> <?= $error ?>
        </div>
        <?php endif; ?>
        <form method="POST" id="loginForm">
            <div class="form-group">
                <label class="form-label">Email Address</label>
                <input type="email" name="email" class="form-control" placeholder="you@example.com" required value="<?= htmlspecialchars($email ?? '') ?>">
            </div>
            <div class="form-group">
                <label class="form-label">Password</label>
                <input type="password" name="password" class="form-control" placeholder="Enter your password" required>
            </div>
            <button type="submit" class="btn btn-primary btn-block btn-lg"><i class="fas fa-sign-in-alt"></i> Sign In</button>
        </form>
        <div class="auth-footer">
            Don't have an account? <a href="register.php">Create one</a>
        </div>
        <div class="auth-divider">Demo Accounts</div>
        <div style="font-size:0.8rem;color:var(--text-muted);text-align:center;line-height:1.8">
            <strong>Admin:</strong> admin@mail.com<br>
            <strong>Customer:</strong> cust@mail.com<br>
            <strong>Kitchen:</strong> kitchen@mail.com<br>
            <strong>Caterer:</strong> cat@mail.com<br>
            <strong>Delivery:</strong> del@mail.com<br>
            <em>Password for all: 123</em>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
