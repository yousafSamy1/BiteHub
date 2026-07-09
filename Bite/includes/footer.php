<?php $base = getBaseUrl(); ?>
<?php if(isLoggedIn()): ?>
<div class="chat-fab" onclick="this.querySelector('.chat-popup').classList.toggle('show')" title="Chat Support">
    <i class="fas fa-comment-dots"></i>
    <div class="chat-popup glass-card">
        <h4 style="margin-bottom:12px"><i class="fas fa-headset" style="color:var(--primary)"></i> Support Chat</h4>
        <p style="font-size:0.85rem;color:var(--text-secondary);margin-bottom:12px">Need help? We're here for you!</p>
        <div style="max-height:200px;overflow-y:auto;margin-bottom:12px;padding:8px;background:var(--bg-dark);border-radius:var(--radius);font-size:0.85rem">
            <div style="padding:8px;margin-bottom:6px;background:rgba(255,107,53,0.08);border-radius:8px;color:var(--text-secondary)"><strong style="color:var(--primary)">BiteBot:</strong> Hi there! 👋 How can I help you today?</div>
        </div>
        <div style="display:flex;gap:6px">
            <input type="text" class="form-control" placeholder="Type a message..." style="font-size:0.85rem" onclick="event.stopPropagation()">
            <button class="btn btn-primary" style="padding:8px 12px" onclick="event.stopPropagation();showToast('Message sent!','success')"><i class="fas fa-paper-plane"></i></button>
        </div>
    </div>
</div>
<?php endif; ?>
<footer class="footer">
    <div class="container">
        <div class="footer-grid">
            <div class="footer-brand">
                <h3><i class="fas fa-fire"></i> <span>Bite</span>Hub</h3>
                <p>Connecting you with the best home kitchens and caterers in your area. Fresh, homemade food delivered to your door.</p>
                <div class="footer-social">
                    <a href="#"><i class="fab fa-facebook-f"></i></a>
                    <a href="#"><i class="fab fa-twitter"></i></a>
                    <a href="#"><i class="fab fa-instagram"></i></a>
                    <a href="#"><i class="fab fa-whatsapp"></i></a>
                </div>
            </div>
            <div class="footer-col">
                <h4>Quick Links</h4>
                <a href="<?= $base ?>/browse.php">Browse Kitchens</a>
                <a href="<?= $base ?>/menu.php">Full Menu</a>
                <a href="<?= $base ?>/top-kitchens.php">Top 10 Kitchens</a>
                <a href="<?= $base ?>/subscriptions.php">Meal Plans</a>
            </div>
            <div class="footer-col">
                <h4>For Partners</h4>
                <a href="<?= $base ?>/register.php">Register Kitchen</a>
                <a href="<?= $base ?>/register.php">Become a Caterer</a>
                <a href="<?= $base ?>/register.php">Join as Driver</a>
            </div>
            <div class="footer-col">
                <h4>Support</h4>
                <a href="#">Help Center</a>
                <a href="#">Contact Us</a>
                <a href="#">Privacy Policy</a>
                <a href="#">Terms of Service</a>
            </div>
        </div>
        <div class="footer-bottom">
            <p>&copy; <?= date('Y') ?> BiteHub. All rights reserved. Made with <span style="color:var(--danger)">♥</span></p>
        </div>
    </div>
</footer>
<script src="<?= $base ?>/js/app.js"></script>
</body>
</html>
