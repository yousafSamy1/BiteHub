@php
    $isLogged = auth()->check();
    $user = auth()->user();
    $name = $user?->FullName ?? $user?->name ?? 'Guest';

    // Build correct avatar URL — same pattern as admin panel
    if ($user?->Image && file_exists(public_path('upload/admin_images/'.$user->Image))) {
        $img = url('upload/admin_images/'.$user->Image);
    } else {
        $img = 'https://ui-avatars.com/api/?name='.urlencode($name).'&background=ff6b35&color=fff&bold=true';
    }

    $role = $user?->Role ?? $user?->role ?? null;
@endphp
@php
    $isAr = isset($_COOKIE['googtrans']) && str_contains($_COOKIE['googtrans'], '/en/ar');
@endphp
<!DOCTYPE html>
<html lang="{{ $isAr ? 'ar' : 'en' }}" dir="{{ $isAr ? 'rtl' : 'ltr' }}" data-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="BiteHub — Discover amazing home kitchens and caterers. Order fresh, authentic homemade meals delivered to your door.">
    <meta name="theme-color" content="#ff6b35">
    <title>@yield('title', 'BiteHub') · Home Food Platform</title>
    <script>
        window.isAuthenticated = @json($isLogged);
        window.loginUrl = "{{ route('login') }}";
        (function() {
            var t = localStorage.getItem('bitehub_theme') || 'dark';
            document.documentElement.setAttribute('data-theme', t);
        })();
    </script>
    <link rel="stylesheet" href="{{ asset('frontend/css/style.css') }}?v={{ filemtime(public_path('frontend/css/style.css')) }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" crossorigin="anonymous">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <style>
        .active-chats-bubble {
            position: fixed; bottom: 30px; left: 30px; width: 60px; height: 60px;
            background: linear-gradient(135deg, #8b5cf6, #6366f1);
            color: #fff; border-radius: 50%; display: flex; align-items: center; justify-content: center;
            font-size: 1.5rem; cursor: pointer; z-index: 9000; box-shadow: 0 8px 24px rgba(139, 92, 246, 0.4);
            transition: all 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        }
        .active-chats-bubble:hover { transform: scale(1.1); box-shadow: 0 12px 32px rgba(139, 92, 246, 0.6); }
        .active-chats-bubble .active-count {
            position: absolute; top: -5px; right: -5px; background: var(--danger);
            color: #fff; font-size: 0.75rem; font-weight: 800; width: 22px; height: 22px;
            border-radius: 50%; display: flex; align-items: center; justify-content: center;
            border: 2px solid var(--bg-dark);
        }
        .active-chats-list {
            position: absolute; bottom: 80px; left: 0; width: 300px; background: var(--bg-card);
            border: 1px solid var(--border-color); border-radius: 20px; overflow: hidden;
            box-shadow: 0 15px 40px rgba(0,0,0,0.5); display: none; flex-direction: column;
            animation: slideInUp 0.3s ease;
        }
        .active-chats-list.show { display: flex; }
        .active-chats-list .list-header {
            padding: 15px 20px; background: rgba(255,255,255,0.03); border-bottom: 1px solid var(--border-color);
            font-weight: 700; font-size: 0.9rem; color: var(--text-primary);
        }
        .active-chats-list .list-items { max-height: 350px; overflow-y: auto; }
        .chat-item-row {
            padding: 12px 20px; border-bottom: 1px solid var(--border-color); cursor: pointer;
            transition: background 0.2s; display: flex; align-items: center; gap: 12px;
        }
        .chat-item-row:hover { background: rgba(255,107,53,0.05); }
        .chat-item-row .item-icon {
            width: 40px; height: 40px; border-radius: 10px; background: var(--bg-dark);
            display: flex; align-items: center; justify-content: center; font-size: 1.1rem;
        }
        .chat-item-row .item-info { flex: 1; min-width: 0; }
        .chat-item-row .item-name { font-weight: 600; font-size: 0.85rem; color: var(--text-primary); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .chat-item-row .item-last { font-size: 0.75rem; color: var(--text-muted); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        
        @keyframes slideInUp { from { opacity: 0; transform: translateY(20px); } to { opacity: 1; transform: translateY(0); } }
    </style>
    @stack('styles')
</head>
<body>

<!-- ═══════════════ NAVBAR ═══════════════ -->
<nav class="navbar" id="mainNav">
    <div class="container">
        <div class="nav-backdrop" id="navBackdrop" onclick="toggleMobileNav()"></div>
        <a href="{{ route('frontend.home') }}" class="nav-logo">
            <i class="fas fa-fire"></i>
            <span>Bite</span>Hub
        </a>

        <ul class="nav-links" id="navLinks">
            <li class="mobile-only" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px; padding: 0 12px;">
                <div class="nav-logo" style="font-size: 1.4rem;">
                    <i class="fas fa-fire"></i>
                    <span>Bite</span>Hub
                </div>
                <button onclick="toggleMobileNav()" style="background: var(--bg-card2); border: 1px solid var(--border-color); color: #fff; width: 40px; height: 40px; border-radius: 10px; display: flex; align-items: center; justify-content: center; cursor: pointer;">
                    <i class="fas fa-times"></i>
                </button>
            </li>
            <li><a href="{{ route('frontend.home') }}"          class="@yield('nav-home')">Home</a></li>
            <li><a href="{{ route('frontend.browse') }}"        class="@yield('nav-browse')">Kitchens</a></li>
            <li><a href="{{ route('frontend.menu') }}"          class="@yield('nav-menu')">Menu</a></li>
            <li><a href="{{ route('frontend.caterers') }}"      class="@yield('nav-caterers')">Caterers</a></li>
            <li><a href="{{ route('frontend.top') }}"           class="@yield('nav-top')">Top 10</a></li>
            <li><a href="{{ route('frontend.subscriptions') }}" class="@yield('nav-subs')">Plans</a></li>
            
            @guest
                <li class="mobile-only"><a href="{{ route('login') }}" class="btn-primary" style="background:var(--primary); color:#fff; margin-top:10px">Log In</a></li>
                <li class="mobile-only"><a href="{{ route('register') }}" class="btn-outline" style="border:1px solid var(--primary); color:var(--primary)">Sign Up</a></li>
            @endguest
            @auth
                @php
                    $role = Auth::user()->Role;
                    $dashUrl = match($role) {
                        'Customer' => route('dashboard.customer'),
                        'Kitchen'  => route('kitchen.dashboard'),
                        'Caterer'  => route('caterer.dashboard'),
                        'Delivery' => route('agent.dashboard'),
                        default    => '#'
                    };
                @endphp
                <li class="mobile-only" style="border-top:1px solid var(--border-color); margin-top:10px; padding-top:10px">
                    <a href="{{ $dashUrl }}" style="background:rgba(255,255,255,0.05)"><i class="fas fa-gauge-high"></i> Dashboard</a>
                </li>
                <li class="mobile-only">
                    <a href="{{ route('frontend.profile') }}" style="background:rgba(255,255,255,0.05)"><i class="fas fa-user-pen"></i> My Profile</a>
                </li>
                <li class="mobile-only">
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" style="width:100%; background:rgba(248,113,113,0.1); border:none; color:var(--danger); padding:14px; border-radius:12px; font-weight:700; margin-top:10px">
                            <i class="fas fa-arrow-right-from-bracket"></i> Sign Out
                        </button>
                    </form>
                </li>
            @endauth
        </ul>

        <div class="nav-actions">
            <!-- Language toggle -->
            <button class="theme-toggle" onclick="toggleLanguage()" title="Toggle Language" style="color:var(--text-primary)">
                <i class="fas fa-globe"></i>
                <span style="font-size:0.65rem;font-weight:700;margin-left:2px;font-family:sans-serif">{{ $isAr ? 'EN' : 'AR' }}</span>
            </button>

            <!-- Theme toggle -->
            <button class="theme-toggle" onclick="toggleTheme()" title="Toggle Theme" id="themeBtn">
                <i class="fas fa-sun"></i>
                <i class="fas fa-moon"></i>
            </button>

            <!-- Cart -->
            <a href="{{ route('frontend.cart') }}" class="nav-cart" id="navCartBtn">
                <i class="fas fa-shopping-bag"></i>
                <span class="badge" id="cartBadge" style="display:none">0</span>
            </a>

            @auth
            <!-- Notifications -->
            <div class="nav-dropdown">
                <div class="nav-cart" id="navNotifyBtn" style="cursor:pointer; position:relative">
                    <i class="fas fa-bell"></i>
                    @php $unreadCount = $headerNotifications->where('IsRead', false)->count(); @endphp
                    @if($unreadCount > 0)
                        <span class="badge" style="display:flex">{{ $unreadCount }}</span>
                    @endif
                </div>
                <div class="nav-dropdown-menu notify-dropdown">
                    <div class="notify-header">
                        <span>Notifications</span>
                        <a href="{{ route('notifications.clear') }}">Clear all</a>
                    </div>
                    <div class="notify-body">
                        @forelse($headerNotifications ?? [] as $notification)
                            <a href="{{ route('notifications.read', $notification->NotificationID) }}" 
                               class="notify-item {{ $notification->IsRead ? 'read' : 'unread' }}">
                                <div class="notify-icon icon-{{ strtolower($notification->Type) }}">
                                    @php
                                        $icon = match($notification->Type) {
                                            'Order' => 'shopping-cart',
                                            'Promotion' => 'gift',
                                            'Chat' => 'comment-dots',
                                            default => 'bell',
                                        };
                                    @endphp
                                    <i class="fas fa-{{ $icon }}"></i>
                                </div>
                                <div class="notify-content">
                                    <div class="notify-title">{{ $notification->Title }}</div>
                                    <div class="notify-msg">{{ $notification->Message }}</div>
                                    <div class="notify-time">{{ \Carbon\Carbon::parse($notification->CreatedAt)->diffForHumans() }}</div>
                                </div>
                            </a>
                        @empty
                            <div class="notify-empty">
                                <i class="fas fa-bell-slash"></i>
                                <span>No notifications</span>
                            </div>
                        @endforelse
                    </div>
                    <a href="{{ route('notifications.index') }}" class="notify-footer">
                        View all
                    </a>
                </div>
            </div>

            <!-- User dropdown -->
            <div class="nav-dropdown">
                <div class="nav-user" id="navUserBtn">
                    <img src="{{ htmlspecialchars($img) }}" alt="{{ htmlspecialchars($name) }}">
                    <span>{{ explode(' ', $name)[0] }}</span>
                    <i class="fas fa-chevron-down" style="color:var(--text-muted);font-size:0.8rem;transition:var(--transition-fast)"></i>
                </div>
                <div class="nav-dropdown-menu">
                    @php
                        $dashUrl = match($role) {
                            'Admin'         => route('admin.dashboard'),
                            'Customer'      => route('dashboard.customer'),
                            'KitchenOwner'  => route('kitchen.dashboard'),
                            'Caterer'       => route('caterer.dashboard'),
                            'DeliveryAgent' => route('agent.dashboard'),
                            default         => route('frontend.home'),
                        };
                    @endphp
                    <a href="{{ $dashUrl }}"><i class="fas fa-gauge-high"></i> Dashboard</a>
                    <a href="{{ route('frontend.profile') }}"><i class="fas fa-user-pen"></i> My Profile</a>
                    <a href="{{ route('frontend.cart') }}"><i class="fas fa-shopping-bag"></i> My Cart</a>
                    <div style="height:1px;background:var(--border-color);margin:6px 0"></div>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" style="background:none;border:none;color:var(--danger);cursor:pointer;padding:10px 14px;width:100%;text-align:left;font-size:0.9rem;font-family:inherit;display:flex;align-items:center;gap:10px;border-radius:10px;transition:var(--transition-fast)" onmouseover="this.style.background='rgba(248,113,113,0.1)'" onmouseout="this.style.background='none'">
                            <i class="fas fa-arrow-right-from-bracket" style="width:16px;text-align:center"></i> Sign Out
                        </button>
                    </form>
                </div>
            </div>
            @else
            <a href="{{ route('login') }}" class="btn btn-outline btn-sm">Log In</a>
            <a href="{{ route('register') }}" class="btn btn-primary btn-sm">Sign Up</a>
            @endauth

            <!-- Mobile hamburger -->
            <button class="mobile-toggle" id="mobileToggle" onclick="toggleMobileNav()">
                <i class="fas fa-bars" id="menuIcon"></i>
            </button>
        </div>
    </div>
</nav>

@yield('content')

<!-- ═══════════════ CHAT FAB ═══════════════ -->
@auth
@php
    $role = auth()->user()->Role ?? null;
    $supportUrl = match($role) {
        'Admin'         => route('admin.reports'),
        'Customer'      => route('customer.support'),
        'KitchenOwner'  => route('kitchen.support'),
        'Caterer'       => route('caterer.support'),
        default         => null,
    };
@endphp
@if($supportUrl)
<a href="{{ $supportUrl }}" class="report-fab" title="Report an Issue">
    <i class="fas fa-exclamation-triangle"></i>
</a>
@endif

<div class="chat-fab" id="chatFab" onclick="toggleChat()" title="Support Chat">
    <i class="fas fa-comment-dots" id="chatIcon"></i>
    <span id="chatNotificationDot" style="display:none;position:absolute;top:-8px;right:-8px;background:var(--danger);color:#fff;font-size:0.65rem;padding:2px 6px;border-radius:20px;font-weight:700;box-shadow:0 2px 8px rgba(0,0,0,0.3);z-index:2;line-height:1">0</span>
    <div class="chat-popup" id="chatPopup">
        <div style="display:flex;align-items:center;gap:10px;margin-bottom:16px">
            <div style="width:38px;height:38px;border-radius:50%;background:linear-gradient(135deg,var(--primary),var(--accent));display:flex;align-items:center;justify-content:center;color:#fff;font-size:1rem;flex-shrink:0"><i class="fas fa-headset"></i></div>
            <div>
                <div style="font-weight:700;font-size:0.95rem">BiteHub Support</div>
                <div style="font-size:0.75rem;color:var(--success);display:flex;align-items:center;gap:5px"><span style="width:7px;height:7px;background:var(--success);border-radius:50%;display:inline-block;animation:bounceDot 1s infinite"></span>Online now</div>
            </div>
        </div>
        <div style="max-height:200px;overflow-y:auto;margin-bottom:14px;padding:10px;background:var(--bg-dark);border-radius:12px;font-size:0.85rem" id="chatMessages">
            <div style="padding:10px 12px;margin-bottom:8px;background:rgba(255,107,53,0.07);border-radius:10px;border-left:3px solid var(--primary)">
                <strong style="color:var(--primary);font-size:0.8rem">BiteBot 🤖</strong>
                <p style="color:var(--text-secondary);margin-top:4px">Hi {{ explode(' ', $name)[0] }}! 👋 How can I help you today?</p>
            </div>
        </div>
        <div style="display:flex;gap:8px" onclick="event.stopPropagation()">
            <input type="text" class="form-control" placeholder="Type a message..." style="font-size:0.85rem;padding:10px 14px" id="chatInput" onkeypress="if(event.key==='Enter')sendChat()">
            <button class="btn btn-primary" style="padding:10px 14px;border-radius:12px" onclick="sendChat()"><i class="fas fa-paper-plane"></i></button>
        </div>
    </div>
</div>

<div id="customMessenger" class="messenger-box" style="display:none" data-current-user-id="{{ Auth::id() }}">
    <div class="messenger-header">
        <div class="d-flex align-items-center gap-2">
            <div class="messenger-icon"><i class="fas fa-magic"></i></div>
            <div>
                <div class="messenger-title" id="msgDishName">Customize Dish</div>
                <div class="messenger-status"><span class="status-dot"></span> Kitchen & Admin Online</div>
            </div>
        </div>
        <button class="messenger-close" onclick="closeMessengerChat()"><i class="fas fa-times"></i></button>
    </div>
    <div class="messenger-body" id="messengerBody">
        <div class="messenger-intro">
            <p>Tell the kitchen how you'd like your <strong><span id="msgDishNameIntro">dish</span></strong>!</p>
            <small>Admin monitors this chat to ensure quality.</small>
        </div>
        <div id="messengerMessages"></div>
    </div>
    <div class="messenger-footer">
        <input type="text" id="messengerInput" placeholder="Type a request..." onkeypress="if(event.key==='Enter')sendMessengerMessage()">
        <button class="messenger-send" onclick="sendMessengerMessage()"><i class="fas fa-paper-plane"></i></button>
    </div>
</div>

<!-- Active Chats Floating Bubble (Left Side) -->
<div id="activeChatsBubble" class="active-chats-bubble" style="display:none" onclick="toggleActiveChatsList()">
    <i class="fas fa-comments"></i>
    <span class="active-count">0</span>
    <div id="activeChatsList" class="active-chats-list">
        <div class="list-header">Active Requests</div>
        <div id="activeChatsItems" class="list-items">
            <!-- Populated via JS -->
        </div>
    </div>
</div>
@endauth

<!-- ═══════════════ FOOTER ═══════════════ -->
<footer class="footer">
    <div class="container">
        <div class="footer-grid">
            <div class="footer-brand">
                <h3><i class="fas fa-fire" style="color:var(--primary);filter:drop-shadow(0 0 8px var(--primary))"></i> <span>Bite</span>Hub</h3>
                <p>Connecting you with the best home kitchens and caterers in your area. Fresh, authentic homemade food delivered to your door — every time.</p>
                <div class="footer-social">
                    <a href="#" aria-label="Facebook"><i class="fab fa-facebook-f"></i></a>
                    <a href="#" aria-label="Twitter"><i class="fab fa-x-twitter"></i></a>
                    <a href="#" aria-label="Instagram"><i class="fab fa-instagram"></i></a>
                    <a href="#" aria-label="WhatsApp"><i class="fab fa-whatsapp"></i></a>
                    <a href="#" aria-label="TikTok"><i class="fab fa-tiktok"></i></a>
                </div>
            </div>
            <div class="footer-col">
                <h4>Explore</h4>
                <a href="{{ route('frontend.browse') }}">Browse Kitchens</a>
                <a href="{{ route('frontend.menu') }}">Full Menu</a>
                <a href="{{ route('frontend.top') }}">Top 10 Kitchens</a>
                <a href="{{ route('frontend.subscriptions') }}">Meal Plans</a>
            </div>
            <div class="footer-col">
                <h4>Join Us</h4>
                <a href="{{ route('register', ['role' => 'KitchenOwner']) }}">Register Kitchen</a>
                <a href="{{ route('register', ['role' => 'Caterer']) }}">Become a Caterer</a>
                <a href="{{ route('frontend.catering') }}">Corporate Catering</a>
                <a href="{{ route('login') }}">Partner Login</a>
            </div>
            <div class="footer-col">
                <h4>Support</h4>
                <a href="{{ route('customer.support') }}">Help Center</a>
                <a href="{{ route('customer.support') }}">Contact Us</a>
                <a href="{{ route('customer.support') }}">Privacy Policy</a>
                <a href="{{ route('customer.support') }}">Terms of Service</a>
            </div>
        </div>
        <div class="footer-bottom">
            <p>&copy; {{ date('Y') }} BiteHub. All rights reserved. Made with <span style="color:var(--primary)">♥</span> for food lovers.</p>
        </div>
    </div>
</footer>

<!-- ═══════════════ GOOGLE TRANSLATE ═══════════════ -->
<style>
/* Hide the Google Translate UI elements and prevent padding jump */
.goog-te-banner-frame,
.VIpgJd-ZVi9od-ORHb-OEVmcd,
#goog-gt-tt,
iframe.goog-te-banner-frame { 
    display: none !important; 
}
body { top: 0px !important; }
#google_translate_element { display: none !important; }
font { background: transparent !important; box-shadow: none !important; }
</style>
<div id="google_translate_element"></div>
<script>
function googleTranslateElementInit() {
    new google.translate.TranslateElement({pageLanguage: 'en', includedLanguages: 'ar,en', autoDisplay: false}, 'google_translate_element');
}
function toggleLanguage() {
    let c = document.cookie;
    let isAr = c.includes('googtrans=/en/ar');
    
    // Clear all possible combinations of the cookie
    ['', location.hostname, '.' + location.hostname].forEach(function(domain) {
        let d = domain ? '; domain=' + domain : '';
        document.cookie = 'googtrans=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/' + d;
    });

    if (!isAr) {
        document.cookie = "googtrans=/en/ar; path=/";
    } else {
        document.cookie = "googtrans=/en/en; path=/";
    }
    location.reload();
}
</script>
<script src="https://translate.google.com/translate_a/element.js?cb=googleTranslateElementInit"></script>

<!-- ═══════════════ TOAST CONTAINER ═══════════════ -->
<div class="toast-container" id="toastContainer"></div>

<!-- ═══════════════ SCRIPTS ═══════════════ -->
<script src="{{ asset('frontend/js/app.js') }}?v={{ filemtime(public_path('frontend/js/app.js')) }}"></script>
<script src="https://unpkg.com/@lottiefiles/lottie-player@latest/dist/lottie-player.js"></script>
@stack('scripts')

<script>
// ─── Flash Messages to Toasts ──────────────────────────────
document.addEventListener('DOMContentLoaded', function() {
    @if(session('message'))
        const msg = "{{ session('message') }}";
        const type = "{{ session('alert-type') ?? 'info' }}";
        if (typeof showToast === 'function') {
            showToast(msg, type);
        }
    @endif
});

// ─── Navbar scroll effect ──────────────────────────────────
window.addEventListener('scroll', function() {
    const nav = document.getElementById('mainNav');
    nav && nav.classList.toggle('scrolled', window.scrollY > 20);
});

// ─── Dropdown Click Toggle ─────────────────────────────────
document.addEventListener('click', function(e) {
    const dropdownBtn = e.target.closest('#navUserBtn, #navNotifyBtn');
    const allDropdowns = document.querySelectorAll('.nav-dropdown');
    
    if (dropdownBtn) {
        e.preventDefault();
        const parent = dropdownBtn.parentElement;
        const isActive = parent.classList.contains('active');
        
        // Close all others
        allDropdowns.forEach(d => d.classList.remove('active'));
        
        // Toggle current
        if (!isActive) {
            parent.classList.add('active');
        }
    } else if (!e.target.closest('.nav-dropdown-menu')) {
        // Click outside closes all
        allDropdowns.forEach(d => d.classList.remove('active'));
    }
});

// ─── Mobile nav ────────────────────────────────────────────
function toggleMobileNav() {
    const links = document.getElementById('navLinks');
    const icon  = document.getElementById('menuIcon');
    const nav   = document.getElementById('mainNav');
    const open  = links.classList.toggle('open');
    const backdrop = document.getElementById('navBackdrop');
    icon.className = open ? 'fas fa-times' : 'fas fa-bars';
    if(nav) { nav.classList.toggle('mobile-open', open); }
    if(backdrop) { backdrop.classList.toggle('active', open); }
}

// ─── Chat ──────────────────────────────────────────────────
// ─── Chat ──────────────────────────────────────────────────
async function toggleChat() {
    const popup = document.getElementById('chatPopup');
    if (!popup) return;
    
    const isShowing = popup.classList.toggle('show');
    if (isShowing) {
        // Load history when opening
        loadChatHistory();
        
        // Hide notification dot when opened
        const dot = document.getElementById('chatNotificationDot');
        if (dot) dot.style.display = 'none';
    }
}

async function loadChatHistory() {
    const msgs = document.getElementById('chatMessages');
    if (!window.isAuthenticated) return;

    try {
        const response = await fetch("{{ route('bitebot.history') }}");
        const data = await response.json();
        
        if (data.messages && data.messages.length > 0) {
            msgs.innerHTML = '';
            data.messages.forEach(m => {
                appendChatMessage(m.sender, m.message, m.time);
            });
        }
        msgs.scrollTop = msgs.scrollHeight;
    } catch (err) {
        console.error('Failed to load chat history:', err);
    }
}

async function sendChat() {
    const inp = document.getElementById('chatInput');
    const msgs = document.getElementById('chatMessages');
    if (!inp || !inp.value.trim()) return;
    
    const text = inp.value.trim();
    inp.value = '';

    if (!window.isAuthenticated) {
        appendChatMessage('User', text, 'Just now');
        setTimeout(() => {
            appendChatMessage('Bot', "Please log in to chat with our team and get personalized answers! 😊", 'Just now');
        }, 600);
        return;
    }

    // Append user message immediately
    appendChatMessage('User', text, 'Sending...');

    try {
        const response = await fetch("{{ route('bitebot.send') }}", {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify({ message: text })
        });
        const data = await response.json();
        
        // Refresh messages from history returned
        msgs.innerHTML = '';
        data.history.forEach(m => {
            appendChatMessage(m.sender, m.message, m.time);
        });
        msgs.scrollTop = msgs.scrollHeight;
    } catch (err) {
        console.error('Failed to send message:', err);
        appendChatMessage('Bot', "Sorry, I'm having trouble connecting to the server. Please try again later.", 'Just now');
    }
}

function appendChatMessage(sender, message, time) {
    const msgs = document.getElementById('chatMessages');
    let html = '';
    
    if (sender === 'User') {
        html = `<div style="padding:8px 12px;margin-bottom:8px;background:rgba(255,107,53,0.12);border-radius:10px;text-align:right">
                    <p style="color:var(--text-primary);font-size:0.85rem">${message}</p>
                    <small style="font-size:0.65rem;opacity:0.6">${time}</small>
                </div>`;
    } else {
        const icon = sender === 'Admin' ? '👮' : '🤖';
        const color = sender === 'Admin' ? '#727cf5' : 'var(--primary)';
        html = `<div style="padding:10px 12px;margin-bottom:8px;background:rgba(255,107,53,0.07);border-radius:10px;border-left:3px solid ${color}">
                    <strong style="color:${color};font-size:0.8rem">${sender} ${icon}</strong>
                    <p style="color:var(--text-secondary);margin-top:4px;font-size:0.85rem">${message}</p>
                    <small style="font-size:0.65rem;opacity:0.6">${time}</small>
                </div>`;
    }
    msgs.innerHTML += html;
    msgs.scrollTop = msgs.scrollHeight;
}

// ─── Unread notification dot ────────────────────────────────
async function checkSupportUnread() {
    if (!window.isAuthenticated) return;
    try {
        const res = await fetch("{{ route('bitebot.unread') }}");
        const data = await res.json();
        const dot = document.getElementById('chatNotificationDot');
        if (data.unread > 0 && dot) {
            dot.style.display = 'block';
            dot.innerText = data.unread;
        }
    } catch (err) {}
}
setInterval(checkSupportUnread, 30000); // Check every 30s
document.addEventListener('DOMContentLoaded', checkSupportUnread);

// ─── Reveal on scroll ──────────────────────────────────────
(function() {
    const els = document.querySelectorAll('.reveal');
    if (!els.length) return;
    const observer = new IntersectionObserver((entries) => {
        entries.forEach((e, i) => {
            if (e.isIntersecting) {
                setTimeout(() => e.target.classList.add('is-visible'), i * 80);
                observer.unobserve(e.target);
            }
        });
    }, { threshold: 0.08, rootMargin: '0px 0px -60px 0px' });
    els.forEach(el => observer.observe(el));
})();

// ─── Ripple effect on buttons ──────────────────────────────
document.addEventListener('click', function(e) {
    const btn = e.target.closest('.btn');
    if (!btn) return;
    const r = document.createElement('span');
    r.className = 'ripple';
    const rect = btn.getBoundingClientRect();
    const size = Math.max(rect.width, rect.height);
    r.style.cssText = `width:${size}px;height:${size}px;left:${e.clientX-rect.left-size/2}px;top:${e.clientY-rect.top-size/2}px`;
    btn.appendChild(r);
    setTimeout(() => r.remove(), 600);
});
</script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-C6RzsynM9kWDrMNeT87bh95OGNyZPhcTNXj1NW7RuBCsyN/o0jlpcV8Qyq46cDfL" crossorigin="anonymous"></script>
    @auth
    <!-- Notification Polling -->
    <script>
        function pollNotifications() {
            fetch('{{ route("notifications.latest") }}')
                .then(response => response.json())
                .then(data => {
                    // Update Count Badge
                    const badge = document.querySelector('.nav-cart .badge');
                    if (data.unread_count > 0) {
                        if (badge) {
                            badge.innerText = data.unread_count;
                            badge.style.display = 'flex';
                        } else {
                            // Create badge if not exists
                            const bell = document.querySelector('#navNotifyBtn');
                            if (bell) {
                                const newBadge = document.createElement('span');
                                newBadge.className = 'badge';
                                newBadge.style.display = 'flex';
                                newBadge.innerText = data.unread_count;
                                bell.appendChild(newBadge);
                            }
                        }
                    } else if (badge) {
                        badge.style.display = 'none';
                    }

                    // Update List
                    const notifyBody = document.querySelector('.notify-body');
                    if (notifyBody && data.notifications.length > 0) {
                        let html = '';
                        data.notifications.forEach(n => {
                            html += `
                                <a href="${n.url}" class="notify-item unread">
                                    <div class="notify-icon icon-${n.type}">
                                        <i class="fas fa-${n.icon}"></i>
                                    </div>
                                    <div class="notify-content">
                                        <div class="notify-title">${n.title}</div>
                                        <div class="notify-msg">${n.message}</div>
                                        <div class="notify-time">${n.time}</div>
                                    </div>
                                </a>
                            `;
                        });
                        notifyBody.innerHTML = html;
                    }
                })
                .catch(err => console.error('Notification poll failed', err));
        }
        // Poll every 10 seconds
        setInterval(pollNotifications, 10000);
    </script>
    @endauth
</body>
</html>
