// ===== BiteHub App JavaScript =====

// Mobile Menu Toggle
document.addEventListener('DOMContentLoaded', function () {
    // Mobile menu toggle is handled by onclick="toggleMobileNav()" in the blade template
    // to allow for more complex logic (icon switching, etc.) without duplication.
    initScrollReveal();
    initCart();
    initTheme();
    initCustomizationIndicators();
    
    if (window.isAuthenticated) {
        fetchCustomizationCount();
    }
});

// Scroll Reveal
function initScrollReveal() {
    const reveals = document.querySelectorAll('.reveal');
    const observer = new IntersectionObserver((entries) => {
        entries.forEach((entry, i) => {
            if (entry.isIntersecting) {
                setTimeout(() => entry.target.classList.add('is-visible'), i * 60);
                observer.unobserve(entry.target);
            }
        });
    }, { threshold: 0.08, rootMargin: '0px 0px -60px 0px' });
    reveals.forEach(el => observer.observe(el));
}

// ===== Cart Management (localStorage) =====
function initCart() {
    updateCartBadge();
}

function getCart() {
    return JSON.parse(localStorage.getItem('bitehub_cart') || '[]');
}

function saveCart(cart) {
    localStorage.setItem('bitehub_cart', JSON.stringify(cart));
    updateCartBadge();
}

function addToCart(id, name, price, image, kitchenId = null, catererId = null, note = '', sessionId = null) {
    if (window.isAuthenticated === false) {
        window.biteConfirm('You need to log in or sign up to add items to your cart. Go to login page?', function(res) {
            if (res) window.location.href = window.loginUrl || '/login';
        });
        return;
    }

    let cart = getCart();
    // Items with different notes or different session IDs are treated as separate line items
    const existingIndex = cart.findIndex(item => item.id === id && (item.note || '') === note && (item.session_id || '') === (sessionId || ''));
    if (existingIndex !== -1) {
        cart[existingIndex].qty += 1;
        showCartToast(name, 'updated');
    } else {
        cart.push({ 
            id, 
            name, 
            price: parseFloat(price), 
            image, 
            qty: 1, 
            note: note,
            kitchen_id: kitchenId,
            caterer_id: catererId,
            session_id: sessionId
        });
        showCartToast(name, note ? 'customized' : 'added');
    }
    saveCart(cart);
}

function removeFromCart(index) {
    let cart = getCart();
    cart.splice(index, 1);
    saveCart(cart);
    if (typeof renderCart === 'function') renderCart();
}

function updateCartQty(index, delta) {
    let cart = getCart();
    if (cart[index]) {
        cart[index].qty += delta;
        if (cart[index].qty <= 0) {
            cart.splice(index, 1);
        }
    }
    saveCart(cart);
    if (typeof renderCart === 'function') renderCart();
}

function getCartTotal() {
    return getCart().reduce((sum, item) => sum + item.price * item.qty, 0);
}

function getCartCount() {
    return getCart().reduce((sum, item) => sum + item.qty, 0);
}

// ── Customization request counter (pending requests sent but not yet approved) ──
function getPendingCustomItemIds() {
    try { return JSON.parse(localStorage.getItem('bitehub_custom_items') || '[]'); }
    catch { return []; }
}
function addPendingCustomItem(itemId) {
    const ids = getPendingCustomItemIds();
    if (!ids.includes(String(itemId))) {
        ids.push(String(itemId));
        localStorage.setItem('bitehub_custom_items', JSON.stringify(ids));
    }
    injectCustomizationPillForItem(itemId);
}
function removePendingCustomItem(itemId) {
    const ids = getPendingCustomItemIds().filter(id => id !== String(itemId));
    localStorage.setItem('bitehub_custom_items', JSON.stringify(ids));
    // remove pill from card if visible
    document.querySelectorAll(`.custom-request-pill[data-item-id="${itemId}"]`).forEach(el => el.remove());
}

function getCustomizationCount() {
    return parseInt(localStorage.getItem('bitehub_custom_count') || '0', 10);
}
function addCustomizationCount(delta) {
    const next = Math.max(0, getCustomizationCount() + delta);
    localStorage.setItem('bitehub_custom_count', next);
    updateCartBadge();
}
function resetCustomizationCount() {
    localStorage.removeItem('bitehub_custom_count');
    localStorage.removeItem('bitehub_custom_items');
    updateCartBadge();
}

function fetchCustomizationCount() {
    if (!window.isAuthenticated) return;
    fetch('/api/cart/customization-count')
        .then(res => res.json())
        .then(data => {
            if (typeof data.count !== 'undefined') {
                localStorage.setItem('bitehub_custom_count', data.count);
                updateCartBadge();
            }
        })
        .catch(err => console.error('Error fetching customization count:', err));
}

function updateCartBadge() {
    const badge = document.querySelector('.nav-cart .badge');
    if (badge) {
        const count = getCartCount() + getCustomizationCount();
        badge.textContent = count;
        badge.style.display = count > 0 ? 'flex' : 'none';
        // Pulse animation when count increases
        badge.style.animation = 'none';
        requestAnimationFrame(() => { badge.style.animation = ''; });
    }
}

function clearCart() {
    localStorage.removeItem('bitehub_cart');
    updateCartBadge();
}

// ===== Customization Messenger (Floating Chat) =====
function openMessengerChat(itemId, itemName, itemPrice, kitchenId, sessionId = null, type = 'kitchen') {
    const box = document.getElementById('customMessenger');
    if (!box) {
        showToast('Please login to customize items', 'warning');
        return;
    }

    document.getElementById('msgDishName').innerText = itemName;
    document.getElementById('msgDishNameIntro').innerText = itemName;
    box.style.display = 'flex';
    box.dataset.itemId = itemId;
    box.dataset.itemName = itemName;
    box.dataset.itemPrice = itemPrice;
    box.dataset.kitchenId = type === 'kitchen' ? kitchenId : '';
    box.dataset.catererId = type === 'caterer' ? kitchenId : '';
    box.dataset.sessionId = sessionId || '';
    box.dataset.requestCounted = sessionId ? '1' : '';

    // Load history
    const msgs = document.getElementById('messengerMessages');
    msgs.innerHTML = '<div class="text-center p-3"><i class="fas fa-spinner fa-spin"></i> Loading...</div>';

    let url = `/chat/preorder/${itemId}/messages`;
    if (sessionId) {
        url += `?session_id=${sessionId}`;
    }
    // For direct requests, add kitchen/caterer info to message fetch
    if (itemId == 0) {
        url += (sessionId ? '&' : '?') + (box.dataset.kitchenId ? `kitchen_id=${box.dataset.kitchenId}` : `caterer_id=${box.dataset.catererId}`);
    }

    fetch(url)
        .then(res => res.json())
        .then(data => {
            msgs.innerHTML = '';
            if (!data || data.length === 0) {
                msgs.innerHTML = `<div class="messenger-intro"><p>Tell the owner how you'd like your <strong>${itemName}</strong>!</p><small>Admin monitors this chat to ensure quality.</small></div>`;
            } else {
                data.forEach(m => appendMessengerMessage(m, box.dataset.currentUserId, itemName));
            }
            const body = document.getElementById('messengerBody');
            body.scrollTop = body.scrollHeight;
        });

    // --- LIVE POLLING ---
    if (window.messengerPollInterval) clearInterval(window.messengerPollInterval);
    window.messengerPollInterval = setInterval(() => {
        if (box.style.display === 'flex') {
            const lastMsg = msgs.querySelector('.messenger-msg:last-child');
            // We'll just refresh all for now to keep it simple and ensure status changes (Approved/Rejected) show up
            fetch(url)
                .then(res => res.json())
                .then(newData => {
                    const currentCount = msgs.querySelectorAll('.messenger-msg').length;
                    if (newData.length > currentCount || JSON.stringify(newData).includes('Approved') || JSON.stringify(newData).includes('Rejected')) {
                        msgs.innerHTML = '';
                        newData.forEach(m => appendMessengerMessage(m, box.dataset.currentUserId, itemName));
                        body.scrollTop = body.scrollHeight;
                    }
                });
        } else {
            clearInterval(window.messengerPollInterval);
        }
    }, 4000);

    document.getElementById('messengerInput').focus();
}

function appendMessengerMessage(m, currentUserId, itemName) {
    const msgs = document.getElementById('messengerMessages');
    const side = m.SenderID == currentUserId ? 'sent' : 'received';
    const msgDiv = document.createElement('div');
    msgDiv.className = `messenger-msg ${side}`;
    
    let inner = `<div class="msg-bubble">`;
    if(m.Type === 'approved' && m.ExtraCharge > 0) {
        inner += `<div style="padding:10px;background:rgba(255,107,53,0.1);border-radius:8px;margin-bottom:8px;border:1px dashed var(--primary)">
                    <div style="font-weight:700;color:var(--primary)">Price Quote: ${m.ExtraCharge} EGP</div>
                    <div style="font-size:0.75rem;margin-bottom:8px">${m.Message}</div>
                    <button class="btn btn-primary btn-sm w-100" onclick="addCustomQuoteToCart(${m.LiveChatID}, ${m.ExtraCharge}, '${itemName}', '${document.getElementById('customMessenger').dataset.kitchenId || document.getElementById('customMessenger').dataset.catererId}')">Accept & Add to Cart</button>
                  </div>`;
    } else {
        let label = m.Type === 'approved' ? '<span class="badge bg-success" style="font-size:0.6rem">Approved</span> ' :
            (m.Type === 'rejected' ? '<span class="badge bg-danger" style="font-size:0.6rem">Rejected</span> ' : '');
        inner += label + m.Message;
    }
    inner += `</div>`;
    msgDiv.innerHTML = inner;
    msgs.appendChild(msgDiv);
}

function closeMessengerChat() {
    const box = document.getElementById('customMessenger');
    if (box) box.style.display = 'none';
}

function sendMessengerMessage() {
    const inp = document.getElementById('messengerInput');
    const box = document.getElementById('customMessenger');
    const msgs = document.getElementById('messengerMessages');

    if (!inp || !inp.value.trim()) return;

    const text = inp.value.trim();
    const itemId = box.dataset.itemId;
    const sessionId = box.dataset.sessionId;

    const payload = {
        menu_item_id: itemId,
        message: text,
        kitchen_id: box.dataset.kitchenId || null,
        caterer_id: box.dataset.catererId || null
    };
    if (sessionId) {
        payload.session_id = sessionId;
    }

    // AJAX Send
    fetch('/chat/preorder/send', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: JSON.stringify(payload)
    })
        .then(res => res.json())
        .then(data => {
            if (data.status === 'success') {
                if (data.session_id) {
                    box.dataset.sessionId = data.session_id;
                }

                const msgDiv = document.createElement('div');
                msgDiv.className = 'messenger-msg sent';
                msgDiv.innerHTML = `<div class="msg-bubble">${text}</div>`;
                msgs.appendChild(msgDiv);

                inp.value = '';
                const body = document.getElementById('messengerBody');
                body.scrollTop = body.scrollHeight;

                // Only count on the first message in a session (new request)
                if (!box.dataset.requestCounted) {
                    box.dataset.requestCounted = '1';
                    // Only count customization status if it's an actual menu item
                    if (box.dataset.itemId > 0) {
                      addCustomizationCount(1);
                      addPendingCustomItem(box.dataset.itemId);
                    }
                    showToast('✨ Customization request sent! The owner will reply with a quote.', 'info');
                } else {
                    showToast('Message sent!', 'success');
                }

            } else {
                showToast(data.message || 'Error sending request.', 'error');
            }
        })
        .catch(err => {
            console.error('Send Error:', err);
            showToast('Unable to connect to server. Please try again.', 'error');
        });
}

function addCustomQuoteToCart(chatId, price, name, kitchenId) {
    // Add virtual item to cart
    addToCart(0, name, price, '/upload/website_assets/custom_dish.png', kitchenId, null, `Custom Quote #${chatId} - Approved by Kitchen`);
    
    // Mark the chat locally or on server as handled
    fetch(`/cart/customization/${chatId}/used`, { method: 'POST', headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content } });
    
    closeMessengerChat();
}

// ===== Rich Cart Toast (slide-in with item name & icon) =====
function showCartToast(itemName, action) {
    const labels = {
        added:      { icon: 'fa-bag-shopping',  color: 'var(--success)',  text: 'Added to cart' },
        customized: { icon: 'fa-magic',          color: 'var(--info)',     text: 'Added to cart with note' },
        updated:    { icon: 'fa-circle-plus',    color: 'var(--primary)',  text: 'Quantity updated' },
    };
    const cfg = labels[action] || labels.added;
    const container = document.getElementById('toastContainer') || (() => {
        const c = document.createElement('div'); c.className = 'toast-container'; c.id = 'toastContainer';
        document.body.appendChild(c); return c;
    })();

    const toast = document.createElement('div');
    toast.className = 'toast success';
    toast.style.cssText = 'display:flex; align-items:center; gap:12px; min-width:260px;';
    toast.innerHTML = `
        <span style="width:36px;height:36px;border-radius:10px;background:rgba(255,255,255,0.08);display:flex;align-items:center;justify-content:center;flex-shrink:0">
            <i class="fas ${cfg.icon}" style="color:${cfg.color};font-size:1rem"></i>
        </span>
        <div style="flex:1;min-width:0">
            <div style="font-weight:700;font-size:0.82rem;color:var(--text-primary);white-space:nowrap;overflow:hidden;text-overflow:ellipsis">${itemName}</div>
            <div style="font-size:0.75rem;color:${cfg.color};margin-top:1px">${cfg.text}</div>
        </div>
        <button class="toast-close" onclick="this.parentElement.remove()"><i class="fas fa-times"></i></button>`;
    container.appendChild(toast);
    setTimeout(() => {
        toast.style.opacity = '0';
        toast.style.transform = 'translateX(20px)';
        toast.style.transition = 'all 0.3s ease';
        setTimeout(() => toast.remove(), 300);
    }, 3500);
}

// ===== Customization Indicator Pills on Menu Cards =====
function initCustomizationIndicators() {
    const ids = getPendingCustomItemIds();
    if (!ids.length) return;
    ids.forEach(id => injectCustomizationPillForItem(id));
}

function injectCustomizationPillForItem(itemId) {
    // Targets: any element whose onclick references openMessengerChat with this itemId
    const selector = `[onclick*="openMessengerChat(${itemId},"], [onclick*="openMessengerChat(${itemId} "]`;
    document.querySelectorAll(selector).forEach(btn => {
        const card = btn.closest('.menu-card, .card') || btn.parentElement;
        if (!card) return;
        // Only inject once per card
        if (card.querySelector(`.custom-request-pill[data-item-id="${itemId}"]`)) return;
        const pill = document.createElement('span');
        pill.className = 'custom-request-pill';
        pill.setAttribute('data-item-id', itemId);
        pill.innerHTML = '<i class="fas fa-magic" style="font-size:0.65rem"></i> Request Pending';
        // Insert it right after the button
        btn.insertAdjacentElement('afterend', pill);
    });
}

// ===== Toast Notifications =====
function showToast(message, type = 'success') {
    const container = document.getElementById('toastContainer') || (() => {
        const c = document.createElement('div');
        c.className = 'toast-container';
        c.id = 'toastContainer';
        document.body.appendChild(c);
        return c;
    })();

    const icons = { success: 'fa-check-circle', error: 'fa-times-circle', warning: 'fa-exclamation-triangle', info: 'fa-info-circle' };
    const colors = { success: 'var(--success)', error: 'var(--danger)', warning: 'var(--warning)', info: 'var(--info)' };

    const toast = document.createElement('div');
    toast.className = `toast ${type}`;
    toast.innerHTML = `
        <i class="fas ${icons[type] || 'fa-check-circle'}" style="color:${colors[type] || 'var(--success)'}"></i>
        <span class="toast-msg">${message}</span>
        <button class="toast-close" onclick="this.parentElement.remove()"><i class="fas fa-times"></i></button>`;
    container.appendChild(toast);
    setTimeout(() => {
        toast.style.opacity = '0';
        toast.style.transform = 'translateX(20px)';
        toast.style.transition = 'all 0.3s ease';
        setTimeout(() => toast.remove(), 300);
    }, 3500);
}

// ===== Tab Switching =====
function switchTab(tabGroup, tabName) {
    document.querySelectorAll(`[data-tab-group="${tabGroup}"]`).forEach(el => {
        el.style.display = el.dataset.tab === tabName ? 'block' : 'none';
    });
    document.querySelectorAll(`[data-tab-btn="${tabGroup}"]`).forEach(btn => {
        btn.classList.toggle('active', btn.dataset.tabTarget === tabName);
    });
}

// ===== Search Filter =====
function filterItems(inputId, containerSelector, itemSelector) {
    const query = document.getElementById(inputId).value.toLowerCase();
    document.querySelectorAll(`${containerSelector} ${itemSelector}`).forEach(item => {
        const text = item.textContent.toLowerCase();
        item.style.display = text.includes(query) ? '' : 'none';
    });
    
    // Hide "Most Ordered" section when searching
    const moSection = document.getElementById('mostOrderedSection');
    const moDivider = document.getElementById('mostOrderedDivider');
    if (moSection) moSection.style.display = query ? 'none' : '';
    if (moDivider) moDivider.style.display = query ? 'none' : '';
}

// ===== Category Filter (Client-Side) =====
function filterByCategory(catName, btnElement) {
    // Update active state
    const container = btnElement.closest('.cat-slider');
    if (container) {
        container.querySelectorAll('.cat-btn').forEach(b => {
            b.style.borderColor = 'var(--border-color)';
            b.style.background = 'var(--bg-card2)';
            b.style.color = 'var(--text-primary)';
            b.classList.remove('active');
        });
        btnElement.style.borderColor = 'var(--primary)';
        btnElement.style.background = 'rgba(255,107,53,0.15)';
        btnElement.style.color = 'var(--primary)';
        btnElement.classList.add('active');
    }

    // Filter items
    document.querySelectorAll('.menu-grid .menu-card').forEach(item => {
        if (!catName || catName === 'all') {
            item.style.display = '';
        } else {
            const catBadge = item.querySelector('.cat-badge');
            if (catBadge && catBadge.textContent.trim() === catName) {
                item.style.display = '';
            } else {
                item.style.display = 'none';
            }
        }
    });
}



// ===== Theme Toggle =====
function initTheme() {
    const saved = localStorage.getItem('bitehub_theme') || 'dark';
    document.documentElement.setAttribute('data-theme', saved);
}

function toggleTheme() {
    const current = document.documentElement.getAttribute('data-theme') || 'dark';
    const next = current === 'dark' ? 'light' : 'dark';
    document.documentElement.setAttribute('data-theme', next);
    localStorage.setItem('bitehub_theme', next);
}

// ===== Global Overrides =====
window.alert = function(msg) {
    if (typeof showToast === 'function') {
        showToast(msg, 'warning');
    } else {
        console.warn('Alert:', msg);
    }
};

window.biteConfirm = function(msg, callback) {
    const modalHtml = `
    <div id="biteConfirmModal" style="position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.8); z-index:100000; display:flex; align-items:center; justify-content:center; backdrop-filter:blur(8px)">
        <div style="background:var(--bg-card); width:100%; max-width:400px; padding:32px; border:1px solid var(--border-color); border-radius: 20px; text-align:center; box-shadow: var(--shadow);">
            <div style="font-size:3rem; margin-bottom:15px; color:var(--primary);"><i class="fas fa-question-circle"></i></div>
            <h3 style="font-size:1.4rem; margin-bottom:12px; color:var(--text-primary);">Confirm Action</h3>
            <p style="color:var(--text-secondary); font-size:0.9rem; margin-bottom:25px;">${msg}</p>
            <div style="display:flex; gap:12px">
                <button id="biteConfirmCancel" class="btn btn-outline" style="flex:1; border-radius:12px; padding:10px; border:1px solid var(--border-color); background:transparent; color:var(--text-primary); cursor:pointer; font-weight:600;">Cancel</button>
                <button id="biteConfirmOk" class="btn btn-primary" style="flex:1; border-radius:12px; padding:10px; background:var(--primary); border:none; color:#fff; font-weight:bold; cursor:pointer;">Yes, Proceed</button>
            </div>
        </div>
    </div>`;
    
    document.body.insertAdjacentHTML('beforeend', modalHtml);
    const modal = document.getElementById('biteConfirmModal');
    
    document.getElementById('biteConfirmCancel').onclick = function() {
        modal.remove();
        if(callback) callback(false);
    };
    document.getElementById('biteConfirmOk').onclick = function() {
        modal.remove();
        if(callback) callback(true);
    };
};

// ─── Active Chats Bubble Logic ──────────────────────────────────────────
let activeSessions = [];

async function refreshActiveChatsBubble() {
    try {
        const response = await fetch('/api/chat/active-sessions');
        if (!response.ok) return;
        
        activeSessions = await response.json();
        const bubble = document.getElementById('activeChatsBubble');
        const countSpan = bubble.querySelector('.active-count');
        const itemsContainer = document.getElementById('activeChatsItems');
        
        if (activeSessions.length > 0) {
            bubble.style.display = 'flex';
            countSpan.textContent = activeSessions.length;
            
            itemsContainer.innerHTML = activeSessions.map(s => `
                <div class="chat-item-row" onclick="reopenFromList('${s.session_id}', ${s.menu_item_id}, '${s.item_name.replace(/'/g, "\\'")}', ${s.item_price}, ${s.kitchen_id}, ${s.caterer_id}, '${s.owner_type}', ${s.order_id})">
                    <div class="item-icon" style="${s.unread ? 'background:rgba(139, 92, 246, 0.2); color:#8b5cf6;' : ''}">
                        <i class="fas fa-comment-dots"></i>
                    </div>
                    <div class="item-info">
                        <div class="item-name">${s.item_name} ${s.unread ? '<span style="color:var(--danger); font-size:1.2rem; line-height:0;">•</span>' : ''}</div>
                        <div class="item-last">${s.last_message}</div>
                    </div>
                </div>
            `).join('');
        } else {
            bubble.style.display = 'none';
        }
    } catch (e) {
        console.error('Error refreshing active chats:', e);
    }
}

function toggleActiveChatsList() {
    const list = document.getElementById('activeChatsList');
    list.classList.toggle('show');
}

function reopenFromList(sessionId, itemId, itemName, itemPrice, kitchenId, catererId, ownerType, orderId = null) {
    if (typeof window !== 'undefined' && window.event && window.event.stopPropagation) {
        window.event.stopPropagation();
    }
    const list = document.getElementById('activeChatsList');
    if (list) list.classList.remove('show');
    
    if (orderId) {
        window.location.href = `/order-tracking/${orderId}/chat`;
        return;
    }

    const targetId = kitchenId || catererId;
    openMessengerChat(itemId, itemName, itemPrice, targetId, sessionId, ownerType);
}

// Initial load and poller
document.addEventListener('DOMContentLoaded', () => {
    if (document.getElementById('activeChatsBubble')) {
        refreshActiveChatsBubble();
        setInterval(refreshActiveChatsBubble, 10000); // Check every 10s
    }
});
