// ===== BiteHub App JavaScript =====

// Mobile Menu Toggle
document.addEventListener('DOMContentLoaded', function () {
    const toggle = document.querySelector('.mobile-toggle');
    const navLinks = document.querySelector('.nav-links');
    if (toggle && navLinks) {
        toggle.addEventListener('click', () => navLinks.classList.toggle('open'));
    }
    initScrollReveal();
    initCart();
    initTheme();
});

// Scroll Reveal
function initScrollReveal() {
    const reveals = document.querySelectorAll('.reveal');
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('visible');
                observer.unobserve(entry.target);
            }
        });
    }, { threshold: 0.1 });
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

function addToCart(id, name, price, image) {
    let cart = getCart();
    const existing = cart.find(item => item.id === id);
    if (existing) {
        existing.qty += 1;
    } else {
        cart.push({ id, name, price: parseFloat(price), image, qty: 1 });
    }
    saveCart(cart);
    showToast('Added to cart!', 'success');
}

function removeFromCart(id) {
    let cart = getCart().filter(item => item.id !== id);
    saveCart(cart);
    if (typeof renderCart === 'function') renderCart();
}

function updateCartQty(id, delta) {
    let cart = getCart();
    const item = cart.find(i => i.id === id);
    if (item) {
        item.qty += delta;
        if (item.qty <= 0) cart = cart.filter(i => i.id !== id);
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

function updateCartBadge() {
    const badge = document.querySelector('.nav-cart .badge');
    if (badge) {
        const count = getCartCount();
        badge.textContent = count;
        badge.style.display = count > 0 ? 'flex' : 'none';
    }
}

function clearCart() {
    localStorage.removeItem('bitehub_cart');
    updateCartBadge();
}

// ===== Toast Notifications =====
function showToast(message, type = 'success') {
    const existing = document.querySelector('.toast');
    if (existing) existing.remove();
    const toast = document.createElement('div');
    toast.className = `toast ${type}`;
    toast.innerHTML = `<span>${type === 'success' ? '✓' : '✕'}</span> ${message}`;
    document.body.appendChild(toast);
    setTimeout(() => toast.classList.add('show'), 10);
    setTimeout(() => { toast.classList.remove('show'); setTimeout(() => toast.remove(), 300); }, 3000);
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
}

// ===== Chat Toggle =====
function toggleChat() {
    const chatBox = document.querySelector('.chat-box');
    const fab = document.querySelector('.chat-fab');
    if (chatBox) {
        chatBox.classList.toggle('open');
        if (fab) fab.style.display = chatBox.classList.contains('open') ? 'none' : 'flex';
    }
}

// ===== Particles =====
function createParticles(container, count = 20) {
    const el = document.querySelector(container);
    if (!el) return;
    for (let i = 0; i < count; i++) {
        const p = document.createElement('div');
        p.className = 'particle';
        p.style.left = Math.random() * 100 + '%';
        p.style.top = Math.random() * 100 + '%';
        p.style.animationDelay = Math.random() * 6 + 's';
        p.style.animationDuration = (4 + Math.random() * 4) + 's';
        el.appendChild(p);
    }
}

// ===== Form Validation =====
function validateForm(formId) {
    const form = document.getElementById(formId);
    if (!form) return false;
    let valid = true;
    form.querySelectorAll('[required]').forEach(input => {
        if (!input.value.trim()) {
            input.style.borderColor = 'var(--danger)';
            valid = false;
        } else {
            input.style.borderColor = 'var(--border-color)';
        }
    });
    return valid;
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
