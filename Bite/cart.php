<?php
$pageTitle = 'Cart';
$currentPage = '';
require_once __DIR__ . '/config/db.php';

// Handle order placement
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['place_order']) && isLoggedIn()) {
    $custStmt = $pdo->prepare("SELECT CustomerID FROM Customer WHERE UserID=?");
    $custStmt->execute([$_SESSION['user_id']]);
    $cust = $custStmt->fetch();
    if($cust) {
        $payMethod = sanitize($_POST['payment'] ?? 'Cash');
        $payStmt = $pdo->prepare("INSERT INTO Payment (Method) VALUES (?)");
        $payStmt->execute([$payMethod]);
        $payId = $pdo->lastInsertId();
        $total = floatval($_POST['total'] ?? 0);
        $specialReqs = sanitize($_POST['special_requests'] ?? '');
        $address = sanitize($_POST['address'] ?? '');
        $orderStmt = $pdo->prepare("INSERT INTO `Order` (CustomerID,TotalPrice,PaymentID,OrderStatus,SpecialRequests,DeliveryAddress) VALUES (?,?,?,'Confirmed',?,?)");
        $orderStmt->execute([$cust['CustomerID'], $total, $payId, $specialReqs, $address]);
        $orderId = $pdo->lastInsertId();
        $cartItems = json_decode($_POST['cart_items'] ?? '[]', true);
        if(is_array($cartItems)) {
            foreach($cartItems as $ci) {
                $pdo->prepare("INSERT INTO MenuOrderItem (MenuItemID,OrderID,Quantity) VALUES (?,?,?)")
                    ->execute([intval($ci['id']), $orderId, intval($ci['qty'])]);
            }
        }
        // Redirect to order tracking
        header("Location: order-tracking.php?id=$orderId");
        exit;
    }
}
require_once __DIR__ . '/includes/header.php';
?>
<div class="page-header"><h1>Your <span class="highlight">Cart</span></h1><p>Review your items and customize your order</p></div>
<section class="section" style="padding-top:0">
<div class="container">
<div style="display:grid;grid-template-columns:2fr 1fr;gap:24px" id="cartPage">
    <!-- Cart Items -->
    <div>
        <div class="glass-card" id="cartItemsCard">
            <h3 class="mb-2"><i class="fas fa-shopping-cart" style="color:var(--primary)"></i> Cart Items</h3>
            <div id="cartItems"><p class="text-center text-muted" style="padding:40px">Your cart is empty. <a href="menu.php" style="color:var(--primary)">Browse menu</a></p></div>
        </div>
        <!-- Special Requests / Chef Chat -->
        <div class="glass-card" id="customizeSection" style="margin-top:20px;display:none">
            <h3 class="mb-2"><i class="fas fa-comment-dots" style="color:var(--accent)"></i> Customize Your Order</h3>
            <p style="color:var(--text-secondary);font-size:0.9rem;margin-bottom:16px">Tell the chef exactly how you want your food prepared. Any allergies, preferences, or special instructions!</p>
            <div style="background:var(--bg-dark);border-radius:var(--radius);padding:16px;margin-bottom:16px">
                <div style="display:flex;gap:12px;margin-bottom:12px">
                    <div style="width:40px;height:40px;border-radius:50%;background:linear-gradient(135deg,var(--primary),var(--accent));display:flex;align-items:center;justify-content:center;flex-shrink:0"><i class="fas fa-user-tie" style="color:#fff"></i></div>
                    <div style="background:rgba(255,107,53,0.08);padding:12px 16px;border-radius:0 16px 16px 16px;flex:1">
                        <p style="font-size:0.9rem;color:var(--text-secondary)"><strong style="color:var(--primary)">Chef:</strong> Hello! 👋 Let me know if you have any special requests. I can adjust spice levels, remove ingredients, or add extras!</p>
                    </div>
                </div>
            </div>
            <div id="quickRequests" style="display:flex;flex-wrap:wrap;gap:8px;margin-bottom:16px">
                <button type="button" class="btn btn-outline btn-sm" onclick="addQuickRequest(this)" data-text="Less spicy 🌶️">Less spicy 🌶️</button>
                <button type="button" class="btn btn-outline btn-sm" onclick="addQuickRequest(this)" data-text="Extra spicy 🔥">Extra spicy 🔥</button>
                <button type="button" class="btn btn-outline btn-sm" onclick="addQuickRequest(this)" data-text="No onions 🧅">No onions 🧅</button>
                <button type="button" class="btn btn-outline btn-sm" onclick="addQuickRequest(this)" data-text="No garlic">No garlic</button>
                <button type="button" class="btn btn-outline btn-sm" onclick="addQuickRequest(this)" data-text="Gluten free 🌾">Gluten free 🌾</button>
                <button type="button" class="btn btn-outline btn-sm" onclick="addQuickRequest(this)" data-text="Extra sauce 🍯">Extra sauce 🍯</button>
                <button type="button" class="btn btn-outline btn-sm" onclick="addQuickRequest(this)" data-text="Well done 🥩">Well done 🥩</button>
                <button type="button" class="btn btn-outline btn-sm" onclick="addQuickRequest(this)" data-text="Less salt">Less salt</button>
            </div>
            <textarea id="specialRequests" class="form-control" rows="3" placeholder="Type your special requests here... (e.g., less salt, extra rice, no tomatoes)" style="margin-bottom:12px"></textarea>
        </div>
    </div>
    <!-- Order Summary -->
    <div>
        <div class="glass-card cart-summary" id="cartSummary" style="position:sticky;top:90px">
            <h3 class="mb-2"><i class="fas fa-receipt" style="color:var(--primary)"></i> Order Summary</h3>
            <div id="summaryContent"></div>
        </div>
    </div>
</div>
</div>
</section>
<script>
function addQuickRequest(btn) {
    const ta = document.getElementById('specialRequests');
    const text = btn.getAttribute('data-text');
    if (ta.value.includes(text)) return;
    ta.value = ta.value ? ta.value + ', ' + text : text;
    btn.classList.add('active');
    btn.style.background = 'rgba(255,107,53,0.15)';
    btn.style.borderColor = 'var(--primary)';
    btn.style.color = 'var(--primary)';
}

function renderCart() {
    const cart = getCart();
    const container = document.getElementById('cartItems');
    const summary = document.getElementById('summaryContent');
    const customize = document.getElementById('customizeSection');
    if (!cart || !cart.length) {
        container.innerHTML = '<p class="text-center text-muted" style="padding:40px">Your cart is empty. <a href="menu.php" style="color:var(--primary)">Browse menu</a></p>';
        summary.innerHTML = '<p style="color:var(--text-muted);text-align:center;padding:20px">Add items to get started</p>';
        if(customize) customize.style.display = 'none';
        return;
    }
    if(customize) customize.style.display = 'block';
    let html = '';
    cart.forEach((item, idx) => {
        html += `<div class="cart-item" style="display:flex;align-items:center;gap:16px;padding:16px;border-bottom:1px solid var(--border-color);animation:fadeInUp 0.3s ease ${idx*0.05}s both">
            <div style="width:60px;height:60px;border-radius:14px;background:linear-gradient(135deg,rgba(255,107,53,0.2),rgba(255,167,38,0.1));display:flex;align-items:center;justify-content:center;font-size:1.5rem;flex-shrink:0">🍽️</div>
            <div style="flex:1">
                <h4 style="font-size:0.95rem;margin-bottom:2px">${item.name}</h4>
                <p style="color:var(--text-muted);font-size:0.85rem">${item.price.toFixed(2)} EGP each</p>
            </div>
            <div class="cart-qty" style="display:flex;align-items:center;gap:8px">
                <button onclick="updateCartQty(${item.id},-1)" style="width:32px;height:32px;border-radius:8px;border:1px solid var(--border-color);background:var(--bg-card);color:var(--text-primary);cursor:pointer;font-size:1rem;transition:var(--transition)">−</button>
                <span style="font-weight:600;min-width:24px;text-align:center">${item.qty}</span>
                <button onclick="updateCartQty(${item.id},1)" style="width:32px;height:32px;border-radius:8px;border:1px solid var(--border-color);background:var(--bg-card);color:var(--text-primary);cursor:pointer;font-size:1rem;transition:var(--transition)">+</button>
            </div>
            <span style="font-weight:700;min-width:80px;text-align:right;color:var(--primary)">${(item.price*item.qty).toFixed(2)} EGP</span>
            <button onclick="removeFromCart(${item.id})" style="color:var(--danger);cursor:pointer;font-size:1.1rem;background:none;border:none;padding:8px;transition:var(--transition)"><i class="fas fa-trash"></i></button>
        </div>`;
    });
    container.innerHTML = html;

    const subtotal = getCartTotal();
    const delivery = 15;
    const total = subtotal + delivery;
    const isLoggedIn = <?= isLoggedIn() ? 'true' : 'false' ?>;
    const cartJson = JSON.stringify(cart).replace(/'/g, '&#39;').replace(/"/g, '&quot;');
    
    summary.innerHTML = `
        <div style="padding:4px 0">
            <div style="display:flex;justify-content:space-between;padding:10px 0;color:var(--text-secondary)"><span>Subtotal (${cart.reduce((s,i)=>s+i.qty,0)} items)</span><span>${subtotal.toFixed(2)} EGP</span></div>
            <div style="display:flex;justify-content:space-between;padding:10px 0;color:var(--text-secondary)"><span>Delivery Fee</span><span>${delivery.toFixed(2)} EGP</span></div>
            <div style="display:flex;justify-content:space-between;padding:12px 0;font-size:1.15rem;font-weight:700;color:var(--text-primary);border-top:2px solid var(--border-color);margin-top:8px"><span>Total</span><span style="color:var(--primary)">${total.toFixed(2)} EGP</span></div>
        </div>
        <form method="POST" style="margin-top:16px" onsubmit="return prepareCheckout(this)">
            <input type="hidden" name="total" value="${total}">
            <input type="hidden" name="cart_items" id="cartDataField" value="">
            <input type="hidden" name="special_requests" id="specialReqField" value="">
            <div style="margin-bottom:14px">
                <label style="display:block;font-size:0.85rem;font-weight:600;margin-bottom:6px;color:var(--text-primary)"><i class="fas fa-map-marker-alt" style="color:var(--primary)"></i> Delivery Address</label>
                <input name="address" class="form-control" placeholder="Enter your delivery address" required>
            </div>
            <div style="margin-bottom:14px">
                <label style="display:block;font-size:0.85rem;font-weight:600;margin-bottom:6px;color:var(--text-primary)"><i class="fas fa-credit-card" style="color:var(--primary)"></i> Payment Method</label>
                <select name="payment" class="form-control">
                    <option value="Cash">💵 Cash on Delivery</option>
                    <option value="Card">💳 Credit/Debit Card</option>
                    <option value="Wallet">👛 Wallet</option>
                    <option value="Online">🌐 Online Payment</option>
                </select>
            </div>
            <button type="submit" name="place_order" class="btn btn-primary btn-block btn-lg" style="margin-top:8px;font-size:1rem"
                ${!isLoggedIn ? 'onclick="alert(\'Please login first\');return false;"' : ''}>
                <i class="fas fa-check-circle"></i> Place Order
            </button>
        </form>
        ${!isLoggedIn ? '<p style="text-align:center;margin-top:12px;font-size:0.85rem;color:var(--text-muted)"><a href="login.php" style="color:var(--primary)">Login</a> to place your order</p>' : ''}
    `;
}

function prepareCheckout(form) {
    const cart = getCart();
    if (!cart.length) { alert('Your cart is empty!'); return false; }
    document.getElementById('cartDataField').value = JSON.stringify(cart);
    const sr = document.getElementById('specialRequests');
    document.getElementById('specialReqField').value = sr ? sr.value : '';
    return true;
}

document.addEventListener('DOMContentLoaded', renderCart);
</script>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
