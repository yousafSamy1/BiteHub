<?php
$pageTitle = 'Track Order';
$currentPage = '';
require_once __DIR__ . '/config/db.php';
requireLogin();
$uid = $_SESSION['user_id'];
$orderId = intval($_GET['id'] ?? 0);

// Verify this order belongs to the current user
$stmt = $pdo->prepare("SELECT o.*, p.Method as PayMethod, u.FullName as CustomerName 
    FROM `Order` o 
    LEFT JOIN Payment p ON o.PaymentID=p.PaymentID 
    LEFT JOIN Customer cu ON o.CustomerID=cu.CustomerID 
    LEFT JOIN User u ON cu.UserID=u.UserID 
    WHERE o.OrderID=? AND cu.UserID=?");
$stmt->execute([$orderId, $uid]);
$order = $stmt->fetch();
if(!$order) { header('Location: dashboard/customer.php'); exit; }

// Get order items
$itemsStmt = $pdo->prepare("SELECT mi.ItemName, mi.ItemPrice, moi.Quantity, ko.KitchenName 
    FROM MenuOrderItem moi 
    JOIN MenuItem mi ON moi.MenuItemID=mi.MenuItemID 
    LEFT JOIN KitchenOwner ko ON mi.KitchenOwnerID=ko.KitchenOwnerID 
    WHERE moi.OrderID=?");
$itemsStmt->execute([$orderId]);
$orderItems = $itemsStmt->fetchAll();

// Update status via simulation (demo)
if(isset($_GET['set_status']) && in_array($_GET['set_status'], ['Confirmed','Preparing','Ready','Delivering','Delivered'])) {
    $pdo->prepare("UPDATE `Order` SET OrderStatus=? WHERE OrderID=?")->execute([$_GET['set_status'], $orderId]);
    header("Location: order-tracking.php?id=$orderId");
    exit;
}

$status = $order['OrderStatus'];
$steps = ['Confirmed','Preparing','Ready','Delivering','Delivered'];
$currentStep = array_search($status, $steps);
if($currentStep === false) $currentStep = 0;

$kitchenName = !empty($orderItems) ? $orderItems[0]['KitchenName'] : 'Kitchen';

$statusMessages = [
    'Confirmed' => 'Your order has been confirmed! 🎉',
    'Preparing' => 'Kitchen is preparing your meal... 👨‍🍳',
    'Ready' => 'Your food is ready for pickup! 📦',
    'Delivering' => 'Your order is on its way! 🏍️',
    'Delivered' => 'Order delivered! Enjoy your meal! 🎊'
];
$statusIcons = [
    'Confirmed' => 'fa-check-circle',
    'Preparing' => 'fa-fire',
    'Ready' => 'fa-box',
    'Delivering' => 'fa-motorcycle',
    'Delivered' => 'fa-home'
];

require_once __DIR__ . '/includes/header.php';
?>
<style>
/* ===== Order Tracking Styles ===== */
.tracking-container { max-width:800px; margin:0 auto; padding:20px; }
.tracking-hero { text-align:center; margin-bottom:32px; }
.tracking-hero h1 { font-size:1.8rem; margin-bottom:6px; }
.tracking-hero .order-id { color:var(--primary); font-weight:700; font-size:1.1rem; }

/* Step Progress Bar */
.step-progress { display:flex; justify-content:space-between; align-items:flex-start; position:relative; margin:40px 0 48px; padding:0 10px; }
.step-progress::before { content:''; position:absolute; top:28px; left:40px; right:40px; height:4px; background:var(--bg-dark); border-radius:2px; z-index:0; }
.step-progress::after { content:''; position:absolute; top:28px; left:40px; height:4px; background:linear-gradient(90deg,var(--primary),var(--accent)); border-radius:2px; z-index:1; transition:width 0.8s cubic-bezier(0.4,0,0.2,1); width:<?= ($currentStep / (count($steps)-1)) * (100 - (80/800*100)) ?>%; }
.step-item { display:flex; flex-direction:column; align-items:center; position:relative; z-index:2; flex:1; }
.step-circle { width:56px; height:56px; border-radius:50%; display:flex; align-items:center; justify-content:center; font-size:1.2rem; border:3px solid var(--border-color); background:var(--bg-card); color:var(--text-muted); transition:all 0.5s ease; }
.step-item.active .step-circle { border-color:var(--primary); background:linear-gradient(135deg,var(--primary),var(--accent)); color:#fff; box-shadow:0 0 20px rgba(255,107,53,0.4); animation:pulse-glow 2s infinite; }
.step-item.done .step-circle { border-color:var(--success); background:var(--success); color:#fff; }
.step-label { margin-top:10px; font-size:0.8rem; font-weight:600; color:var(--text-muted); text-align:center; transition:color 0.3s; }
.step-item.active .step-label, .step-item.done .step-label { color:var(--text-primary); }
@keyframes pulse-glow { 0%,100%{box-shadow:0 0 20px rgba(255,107,53,0.3)} 50%{box-shadow:0 0 35px rgba(255,107,53,0.5)} }

/* Animation Scene */
.animation-scene { position:relative; overflow:hidden; border-radius:var(--radius); height:260px; margin-bottom:32px; background:var(--bg-dark); }
.road { position:absolute; bottom:0; left:0; right:0; height:80px; background:#4a5568; }
.road::before { content:''; position:absolute; top:50%; left:0; right:0; height:4px; background:repeating-linear-gradient(90deg,#fff 0,#fff 30px,transparent 30px,transparent 60px); transform:translateY(-50%); animation:road-scroll 2s linear infinite; }
@keyframes road-scroll { 0%{transform:translateY(-50%) translateX(0)} 100%{transform:translateY(-50%) translateX(-60px)} }
.scene-message { position:absolute; top:30px; left:50%; transform:translateX(-50%); font-size:1.3rem; font-weight:700; text-align:center; width:90%;
    background:var(--bg-card); padding:20px; border-radius:var(--radius); border:1px solid var(--border-color); }
.character { position:absolute; bottom:80px; transition:left 1s cubic-bezier(0.4,0,0.2,1); }
.chef-char { left:<?= min($currentStep * 22, 80) ?>%; }
.chef-body { width:60px; text-align:center; }
.chef-emoji { font-size:3rem; display:block; animation:char-bounce 1s ease infinite; }
@keyframes char-bounce { 0%,100%{transform:translateY(0)} 50%{transform:translateY(-8px)} }

/* Delivered celebration */
.delivered .animation-scene { background:linear-gradient(135deg,rgba(102,187,106,0.05),rgba(255,167,38,0.05)); }
.confetti { position:absolute; width:8px; height:8px; border-radius:2px; animation:confetti-fall 3s linear infinite; }
@keyframes confetti-fall { 0%{transform:translateY(-10px) rotate(0deg);opacity:1} 100%{transform:translateY(280px) rotate(720deg);opacity:0} }

/* Simulation Tools */
.sim-tools { text-align:center; padding:20px; }
.sim-tools h3 { font-size:1rem; margin-bottom:12px; color:var(--text-secondary); }
.sim-btns { display:flex; justify-content:center; gap:8px; flex-wrap:wrap; }
.sim-btn { padding:8px 16px; border-radius:20px; border:2px solid var(--border-color); background:var(--bg-card); color:var(--text-muted); cursor:pointer; font-size:0.8rem; font-weight:600; transition:all 0.3s; text-decoration:none; }
.sim-btn:hover { border-color:var(--primary); color:var(--primary); }
.sim-btn.current { border-color:var(--primary); background:var(--primary); color:#fff; }

/* Order Details */
.order-detail-grid { display:grid; grid-template-columns:1fr 1fr; gap:20px; margin-bottom:24px; }
@media(max-width:768px) { .order-detail-grid{grid-template-columns:1fr} .step-progress{overflow-x:auto} }

/* Chat Box */
.tracking-chat { border-radius:var(--radius); overflow:hidden; }
.chat-messages-box { max-height:250px; overflow-y:auto; padding:16px; background:var(--bg-dark); border-radius:var(--radius); margin:12px 0; }
.chat-msg { display:flex; gap:10px; margin-bottom:12px; }
.chat-msg.sent { flex-direction:row-reverse; }
.chat-avatar { width:32px; height:32px; border-radius:50%; display:flex; align-items:center; justify-content:center; font-size:0.85rem; flex-shrink:0; }
.chat-bubble-msg { padding:10px 14px; border-radius:16px; font-size:0.9rem; max-width:75%; }
.chat-msg.received .chat-avatar { background:rgba(255,107,53,0.15); }
.chat-msg.received .chat-bubble-msg { background:var(--bg-card); border:1px solid var(--border-color); color:var(--text-secondary); border-radius:0 16px 16px 16px; }
.chat-msg.sent .chat-bubble-msg { background:linear-gradient(135deg,var(--primary),var(--accent)); color:#fff; border-radius:16px 0 16px 16px; }
</style>

<div class="tracking-container <?= $status==='Delivered'?'delivered':'' ?>">
    <!-- Hero -->
    <div class="tracking-hero">
        <h1>Track Your Order</h1>
        <span class="order-id">Order #<?= $orderId ?></span>
        <span style="color:var(--text-muted);margin-left:8px">· <?= date('M d, Y h:i A', strtotime($order['CreatedAt'])) ?></span>
    </div>

    <!-- Step Progress -->
    <div class="step-progress">
        <?php foreach($steps as $i => $step): ?>
        <div class="step-item <?= $i < $currentStep ? 'done' : ($i === $currentStep ? 'active' : '') ?>">
            <div class="step-circle">
                <?php if($i < $currentStep): ?>
                    <i class="fas fa-check"></i>
                <?php else: ?>
                    <i class="fas <?= $statusIcons[$step] ?>"></i>
                <?php endif; ?>
            </div>
            <span class="step-label"><?= $i+1 ?>. <?= $step === 'Delivering' ? 'Out for Delivery' : $step ?></span>
        </div>
        <?php endforeach; ?>
    </div>

    <!-- Animation Scene -->
    <div class="glass-card" style="padding:0;overflow:hidden;margin-bottom:24px">
        <div class="animation-scene">
            <div class="scene-message"><?= $statusMessages[$status] ?? 'Processing...' ?></div>
            <div class="character chef-char">
                <div class="chef-body">
                    <?php if($status === 'Confirmed'): ?>
                        <span class="chef-emoji">✅</span>
                    <?php elseif($status === 'Preparing'): ?>
                        <span class="chef-emoji">👨‍🍳</span>
                    <?php elseif($status === 'Ready'): ?>
                        <span class="chef-emoji">📦</span>
                    <?php elseif($status === 'Delivering'): ?>
                        <span class="chef-emoji">🏍️</span>
                    <?php else: ?>
                        <span class="chef-emoji">🎉</span>
                    <?php endif; ?>
                </div>
            </div>
            <div class="road"></div>
            <?php if($status === 'Delivered'): ?>
            <script>
            // Generate confetti
            (function(){
                const scene = document.querySelector('.animation-scene');
                const colors = ['#ff6b35','#ffa726','#66bb6a','#42a5f5','#ab47bc','#ef5350'];
                for(let i=0;i<30;i++){
                    const c = document.createElement('div');
                    c.className = 'confetti';
                    c.style.left = Math.random()*100+'%';
                    c.style.background = colors[Math.floor(Math.random()*colors.length)];
                    c.style.animationDelay = Math.random()*3+'s';
                    c.style.animationDuration = (2+Math.random()*2)+'s';
                    scene.appendChild(c);
                }
            })();
            </script>
            <?php endif; ?>
        </div>
    </div>

    <!-- Simulation Tools (Demo) -->
    <div class="glass-card sim-tools" style="margin-bottom:24px">
        <h3><i class="fas fa-sliders-h" style="color:var(--accent)"></i> Simulation Tools</h3>
        <p style="font-size:0.8rem;color:var(--text-muted);margin-bottom:12px">Click to simulate order progress (for demo purposes)</p>
        <div class="sim-btns">
            <?php foreach($steps as $i => $step): ?>
            <a href="?id=<?= $orderId ?>&set_status=<?= $step ?>" class="sim-btn <?= $step===$status?'current':'' ?>"><?= $i+1 ?>. <?= $step ?></a>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Order Details + Chat -->
    <div class="order-detail-grid">
        <!-- Order Info -->
        <div class="glass-card">
            <h3 style="margin-bottom:16px"><i class="fas fa-receipt" style="color:var(--primary)"></i> Order Details</h3>
            <?php foreach($orderItems as $oi): ?>
            <div style="display:flex;justify-content:space-between;align-items:center;padding:10px 0;border-bottom:1px solid var(--border-color)">
                <div>
                    <span style="font-weight:600"><?= htmlspecialchars($oi['ItemName']) ?></span>
                    <span style="color:var(--text-muted);font-size:0.85rem"> × <?= $oi['Quantity'] ?></span>
                </div>
                <span style="font-weight:600;color:var(--primary)"><?= number_format($oi['ItemPrice'] * $oi['Quantity'], 2) ?> EGP</span>
            </div>
            <?php endforeach; ?>
            <div style="display:flex;justify-content:space-between;padding:12px 0;font-weight:700;font-size:1.1rem;margin-top:8px">
                <span>Total</span>
                <span style="color:var(--primary)"><?= number_format($order['TotalPrice'], 2) ?> EGP</span>
            </div>
            <div style="margin-top:12px;padding-top:12px;border-top:1px solid var(--border-color)">
                <p style="font-size:0.85rem;color:var(--text-muted)"><i class="fas fa-credit-card"></i> <?= htmlspecialchars($order['PayMethod'] ?? 'Cash') ?></p>
                <?php if($order['DeliveryAddress']): ?>
                <p style="font-size:0.85rem;color:var(--text-muted);margin-top:6px"><i class="fas fa-map-marker-alt"></i> <?= htmlspecialchars($order['DeliveryAddress']) ?></p>
                <?php endif; ?>
                <?php if($order['SpecialRequests']): ?>
                <div style="margin-top:10px;padding:10px;background:rgba(255,107,53,0.06);border-radius:var(--radius);border-left:3px solid var(--primary)">
                    <p style="font-size:0.8rem;font-weight:600;color:var(--primary);margin-bottom:4px"><i class="fas fa-comment-dots"></i> Special Requests</p>
                    <p style="font-size:0.85rem;color:var(--text-secondary)"><?= htmlspecialchars($order['SpecialRequests']) ?></p>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Chat with Kitchen -->
        <div class="glass-card tracking-chat">
            <h3 style="margin-bottom:4px"><i class="fas fa-comments" style="color:var(--accent)"></i> Chat with <?= htmlspecialchars($kitchenName) ?></h3>
            <p style="font-size:0.8rem;color:var(--text-muted);margin-bottom:8px">Communicate directly about your order</p>
            <div class="chat-messages-box" id="chatBox">
                <div class="chat-msg received">
                    <div class="chat-avatar"><i class="fas fa-user-tie" style="color:var(--primary)"></i></div>
                    <div class="chat-bubble-msg">Hi! Your order #<?= $orderId ?> has been received. We'll start preparing it shortly! 🍳</div>
                </div>
                <?php if($status !== 'Confirmed'): ?>
                <div class="chat-msg received">
                    <div class="chat-avatar"><i class="fas fa-user-tie" style="color:var(--primary)"></i></div>
                    <div class="chat-bubble-msg">We're now preparing your food with care! 👨‍🍳✨</div>
                </div>
                <?php endif; ?>
                <?php if($order['SpecialRequests']): ?>
                <div class="chat-msg sent">
                    <div class="chat-avatar" style="background:rgba(66,165,245,0.15)"><i class="fas fa-user" style="color:var(--info)"></i></div>
                    <div class="chat-bubble-msg"><?= htmlspecialchars($order['SpecialRequests']) ?></div>
                </div>
                <div class="chat-msg received">
                    <div class="chat-avatar"><i class="fas fa-user-tie" style="color:var(--primary)"></i></div>
                    <div class="chat-bubble-msg">Got it! We'll make sure to follow your instructions. 👍</div>
                </div>
                <?php endif; ?>
                <?php if(in_array($status, ['Ready','Delivering','Delivered'])): ?>
                <div class="chat-msg received">
                    <div class="chat-avatar"><i class="fas fa-user-tie" style="color:var(--primary)"></i></div>
                    <div class="chat-bubble-msg">Your food is ready! <?= $status==='Delivering'?'Our delivery partner is on the way! 🏍️':($status==='Delivered'?'Hope you enjoyed your meal! ❤️':'Waiting for pickup...') ?></div>
                </div>
                <?php endif; ?>
            </div>
            <div style="display:flex;gap:8px">
                <input type="text" id="chatInput" class="form-control" placeholder="Message <?= htmlspecialchars($kitchenName) ?>..." style="font-size:0.9rem">
                <button class="btn btn-primary" style="padding:10px 16px" onclick="sendChatMsg()"><i class="fas fa-paper-plane"></i></button>
            </div>
        </div>
    </div>

    <!-- Actions -->
    <div style="display:flex;gap:12px;justify-content:center;margin-top:24px">
        <a href="dashboard/customer.php" class="btn btn-outline"><i class="fas fa-arrow-left"></i> Back to Dashboard</a>
        <a href="menu.php" class="btn btn-primary"><i class="fas fa-utensils"></i> Order Again</a>
    </div>
</div>

<script>
function sendChatMsg() {
    const input = document.getElementById('chatInput');
    const box = document.getElementById('chatBox');
    const msg = input.value.trim();
    if (!msg) return;
    
    // Add user message
    const userDiv = document.createElement('div');
    userDiv.className = 'chat-msg sent';
    userDiv.innerHTML = `
        <div class="chat-avatar" style="background:rgba(66,165,245,0.15)"><i class="fas fa-user" style="color:var(--info)"></i></div>
        <div class="chat-bubble-msg">${msg}</div>`;
    box.appendChild(userDiv);
    input.value = '';
    box.scrollTop = box.scrollHeight;

    // Simulate chef reply
    setTimeout(() => {
        const replies = [
            "Thanks for letting us know! We'll take care of it 👍",
            "Sure thing! Your request has been noted 😊",
            "Got it! We'll make it just the way you like it ✨",
            "No worries! We're on it! 🍳",
            "Absolutely! Thanks for the heads up 👨‍🍳"
        ];
        const reply = replies[Math.floor(Math.random()*replies.length)];
        const replyDiv = document.createElement('div');
        replyDiv.className = 'chat-msg received';
        replyDiv.innerHTML = `
            <div class="chat-avatar"><i class="fas fa-user-tie" style="color:var(--primary)"></i></div>
            <div class="chat-bubble-msg">${reply}</div>`;
        box.appendChild(replyDiv);
        box.scrollTop = box.scrollHeight;
    }, 1000 + Math.random() * 1500);
}

// Enter key to send
document.getElementById('chatInput').addEventListener('keypress', function(e) {
    if (e.key === 'Enter') sendChatMsg();
});
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
