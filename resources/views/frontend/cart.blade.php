@extends('frontend.layouts.app')
@section('title', 'My Cart')

@section('content')
<style>
.cart-wrap { display:grid; grid-template-columns:1fr 370px; gap:28px; align-items:start; width: 100%; max-width: 100%; }
@media(max-width:1000px){ .cart-wrap { grid-template-columns:1fr !important; gap:20px; } }
@media(max-width:480px){ .cart-item { flex-direction:column; align-items:flex-start; } .item-img { width:48px; height:48px; } }

/* Layout adjustments */
.page-header { padding: calc(var(--nav-h) + 20px) 0 20px !important; }
.section { padding: 30px 0 !important; }
.glass-card { margin-bottom: 20px !important; }

/* Custom Modal Styles (since global Bootstrap CSS is removed) */
.modal { display:none !important; position:fixed !important; top:0; left:0; width:100%; height:100% !important; z-index:3000 !important; background:rgba(0,0,0,0.7) !important; align-items:center; justify-content:center; padding:20px; backdrop-filter: blur(4px); }
.modal.show { display:flex !important; }
.modal-dialog { background:var(--bg-card) !important; border-radius:var(--radius-lg) !important; width:100%; max-width:500px; border: 1px solid var(--border-color); overflow:hidden; animation: zoomIn 0.3s ease; }
.modal-content { border:none !important; background:transparent !important; box-shadow:none !important; }
@keyframes zoomIn { from { opacity:0; transform:scale(0.9); } to { opacity:1; transform:scale(1); } }
.btn-close { filter: invert(1); opacity: 0.5; }

.cart-item {
    display:flex; align-items:center; gap:16px; padding:16px 0;
    border-bottom:1px solid var(--border-color);
    animation: fadeInUp 0.3s ease;
}
.cart-item:last-child { border-bottom:none; }
.item-img {
    width:58px; height:58px; border-radius:14px;
    background:linear-gradient(135deg,rgba(255,107,53,0.12),rgba(255,167,38,0.06));
    display:flex; align-items:center; justify-content:center;
    font-size:1.5rem; flex-shrink:0;
    border:1px solid rgba(255,107,53,0.15);
}
.qty-btn {
    width:32px; height:32px; border-radius:10px;
    border:1px solid var(--border-color);
    background:var(--bg-card2);
    color:var(--text-primary);
    cursor:pointer; font-size:1.1rem; font-weight:700;
    line-height:1; transition:var(--transition-fast);
    display:flex; align-items:center; justify-content:center;
}
.qty-btn:hover { border-color:var(--primary); color:var(--primary); background:rgba(255,107,53,0.08); }
.del-btn {
    background:none; border:none; cursor:pointer;
    color:var(--text-muted); padding:7px; border-radius:10px;
    font-size:0.9rem; transition:var(--transition-fast);
}
.del-btn:hover { color:var(--danger); background:rgba(248,113,113,0.08); }

.tag-btn {
    padding:6px 14px; border-radius:20px;
    border:1px solid var(--border-color);
    background:transparent; color:var(--text-secondary);
    cursor:pointer; font-size:0.82rem; transition:var(--transition-fast);
    font-family:var(--font-body);
}
.tag-btn:hover, .tag-btn.on {
    border-color:var(--primary);
    background:rgba(255,107,53,0.1);
    color:var(--primary);
}

.pay-card {
    display:flex; align-items:center; gap:14px;
    padding:14px 18px; border-radius:14px;
    border:2px solid var(--border-color);
    cursor:pointer; transition:var(--transition-fast);
    margin-bottom:10px; user-select:none;
}
.pay-card:hover { border-color:rgba(255,107,53,0.4); }
.pay-card.active { border-color:var(--primary); background:rgba(255,107,53,0.05); }
.pay-card input { display:none; }
.pay-card-icon { width:40px; height:40px; border-radius:12px; display:flex; align-items:center; justify-content:center; font-size:1.1rem; flex-shrink:0; }

.cart-empty { text-align:center; padding:60px 20px; color:var(--text-muted); }
.cart-empty .empty-icon { font-size:4rem; display:block; margin-bottom:16px; opacity:0.3; }
.summary-sep { border:none; border-top:1px solid var(--border-color); margin:14px 0; }
</style>

<div class="page-header">
    <h1>Your <span class="highlight">Cart</span></h1>
    <p>Review your items, add special requests, and checkout</p>
</div>

<section class="section" style="padding-top:0">
<div class="container">

<form id="mainForm" method="POST" action="{{ route('frontend.checkout') }}">
@csrf
<input type="hidden" name="total"            id="fTotal">
<input type="hidden" name="cart_items"       id="fItems">
<input type="hidden" name="payment"          id="fPayment" value="Cash">
<input type="hidden" name="address"          id="fAddress">
<input type="hidden" name="points_used"      id="fPointsUsed" value="0">
<input type="hidden" name="is_deposit"       id="fIsDeposit" value="0">

<div class="cart-wrap">

    <!-- ── LEFT ── -->
    <div>
        <!-- Pending & Approved Customizations (Pre-orders) -->
        @if((isset($pendingRequests) && count($pendingRequests) > 0) || (isset($approvedRequests) && count($approvedRequests) > 0))
        <div class="glass-card" style="padding:24px;margin-bottom:24px; border:1px solid rgba(255,107,53,0.3)">
            <h3 style="margin:0 0 18px 0;font-size:1.1rem;display:flex;align-items:center;gap:10px">
                <span style="width:34px;height:34px;background:linear-gradient(135deg,#ff9800,#f44336);border-radius:10px;display:flex;align-items:center;justify-content:center;font-size:0.9rem"><i class="fas fa-magic" style="color:#fff"></i></span>
                Customization Requests
            </h3>

            @foreach($approvedRequests as $req)
            <div class="cart-item" id="customization-card-{{ $req->LiveChatID }}" style="background:rgba(74,222,128,0.05); padding:12px; border-radius:12px; margin-bottom:10px; border:1px dashed var(--success)">
                @php
                    $itemName = $req->menuItem->ItemName ?? 'Custom Request';
                    $itemPrice = $req->menuItem->ItemPrice ?? 0;
                    $itemId = $req->menuItem->MenuItemID ?? 0;
                    $kitchenId = $req->menuItem->KitchenOwnerID ?? 0;
                    $catererId = $req->menuItem->CatererID ?? 0;
                    if (!$kitchenId && !$catererId) {
                        $k = \App\Models\KitchenOwner::where('UserID', $req->ReceiverID)->first();
                        $kitchenId = $k->KitchenOwnerID ?? 0;
                        if (!$kitchenId) {
                            $c = \App\Models\Caterer::where('UserID', $req->ReceiverID)->first();
                            $catererId = $c->CatererID ?? 0;
                        }
                    }
                @endphp
                <div class="item-img" style="background:var(--success); color:#fff"><i class="fas fa-check"></i></div>
                <div style="flex:1">
                    <div style="font-weight:700; color:var(--success)">APPROVED: {{ $itemName }}</div>
                    <div style="font-size:0.75rem; color:var(--text-muted); margin-top:4px">
                        Base: {{ number_format($itemPrice, 2) }} 
                        @if($req->ExtraCharge > 0)
                            + Extra: {{ number_format($req->ExtraCharge, 2) }} 
                        @endif
                        = <strong style="color:var(--primary)">{{ number_format($itemPrice + $req->ExtraCharge, 2) }} EGP</strong>
                    </div>
                    <div style="font-size:0.8rem; color:var(--text-muted); font-style: italic">"{{ $req->Message }}"</div>
                </div>
                <div class="d-flex flex-column gap-2">
                    <button type="button" class="btn btn-success btn-sm"
                        onclick="addApprovedToCart({{ $itemId }}, '{{ addslashes($itemName) }}', {{ $itemPrice }}, {{ $req->ExtraCharge ?? 0 }}, '', '{{ addslashes($req->Message) }}', {{ $req->LiveChatID }}, '{{ $req->SessionID }}')">
                        Add to Cart
                    </button>
                    <button type="button" class="btn btn-outline-primary btn-sm" onclick="openMessengerChat({{ $itemId }}, '{{ addslashes($itemName) }}', {{ $itemPrice }}, {{ $kitchenId ?: $catererId ?: 0 }}, '{{ $req->SessionID }}', '{{ $kitchenId ? 'kitchen' : 'caterer' }}')">
                        <i class="fas fa-comment"></i> Chat
                    </button>
                    <button type="button" class="btn btn-outline-danger btn-sm" onclick="deleteCustomizationRequest({{ $req->LiveChatID }}, {{ $itemId }})">
                        <i class="fas fa-trash"></i> Remove
                    </button>
                </div>
            </div>
            @endforeach

            @foreach($pendingRequests as $req)
            @php
                $itemName = $req->menuItem->ItemName ?? 'Custom Request';
                $itemPrice = $req->menuItem->ItemPrice ?? 0;
                $itemId = $req->menuItem->MenuItemID ?? 0;
                $kitchenId = $req->menuItem->KitchenOwnerID ?? 0;
                $catererId = $req->menuItem->CatererID ?? 0;
                if (!$kitchenId && !$catererId) {
                    $k = \App\Models\KitchenOwner::where('UserID', $req->ReceiverID)->first();
                    $kitchenId = $k->KitchenOwnerID ?? 0;
                    if (!$kitchenId) {
                        $c = \App\Models\Caterer::where('UserID', $req->ReceiverID)->first();
                        $catererId = $c->CatererID ?? 0;
                    }
                }
            @endphp
            <div class="cart-item" id="pending-card-{{ $req->LiveChatID }}" data-menu-item-id="{{ $itemId }}" style="opacity:0.7; padding:12px">
                <div class="item-img" style="background:var(--warning); color:#fff"><i class="fas fa-clock"></i></div>
                <div style="flex:1">
                    <div style="font-weight:600">PENDING: {{ $itemName }}</div>
                    <div style="font-size:0.8rem; color:var(--text-muted)">{{ $req->Message }}</div>
                    <div style="font-size:0.75rem; color:var(--warning); margin-top:4px"><i class="fas fa-spinner fa-spin"></i> Waiting for Kitchen Approval...</div>
                </div>
                <div class="d-flex flex-column gap-2">
                    <button type="button" class="btn btn-outline-primary btn-sm" onclick="openMessengerChat({{ $itemId }}, '{{ addslashes($itemName) }}', {{ $itemPrice }}, {{ $kitchenId ?: $catererId ?: 0 }}, '{{ $req->SessionID }}', '{{ $kitchenId ? 'kitchen' : 'caterer' }}')">
                        <i class="fas fa-comment"></i> Open Chat
                    </button>
                    <button type="button" class="btn btn-outline-danger btn-sm" onclick="deleteCustomizationRequest({{ $req->LiveChatID }}, {{ $itemId }})">
                        <i class="fas fa-trash"></i> Remove
                    </button>
                </div>
            </div>
            @endforeach
        </div>
        @endif

        <!-- Cart items -->
        <div class="glass-card" style="padding:24px;margin-bottom:24px">
            <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:18px">
                <h3 style="margin:0;font-size:1.1rem;display:flex;align-items:center;gap:10px">
                    <span style="width:34px;height:34px;background:linear-gradient(135deg,var(--primary),var(--accent));border-radius:10px;display:flex;align-items:center;justify-content:center;font-size:0.9rem"><i class="fas fa-bag-shopping" style="color:#fff"></i></span>
                    Cart Items
                </h3>
                <button type="button" onclick="clearCartNow()" class="btn btn-sm" style="background:none;border:1px solid var(--border-color);color:var(--text-muted);border-radius:10px;padding:7px 14px;font-size:0.8rem">
                    <i class="fas fa-trash"></i> Clear
                </button>
            </div>
            <div id="cartItems">
                <div class="cart-empty">
                    <span class="empty-icon">🛒</span>
                    <h3 style="font-size:1.1rem;margin-bottom:8px">Your cart is empty</h3>
                    <p style="font-size:0.9rem;margin-bottom:20px">Add some delicious dishes to your cart</p>
                    <a href="{{ route('frontend.menu') }}" class="btn btn-primary btn-sm">Browse Menu</a>
                </div>
            </div>
        </div>


    </div>

    <!-- ── RIGHT ── -->
    <div style="position:sticky;top:90px">
    <div class="glass-card" style="padding:26px">
        <h3 style="margin-bottom:20px;font-size:1.05rem;display:flex;align-items:center;gap:10px">
            <span style="width:34px;height:34px;background:linear-gradient(135deg,var(--primary),var(--accent));border-radius:10px;display:flex;align-items:center;justify-content:center;font-size:0.9rem"><i class="fas fa-receipt" style="color:#fff"></i></span>
            Order Summary
        </h3>

        <!-- Line totals -->
        <div id="totalLines">
            <p style="text-align:center;color:var(--text-muted);padding:20px 0;font-size:0.9rem">Add items to see your total</p>
        </div>

        <hr class="summary-sep">

        <!-- Loyalty -->
        <div class="info-box info-orange" id="loyaltyHint" style="display:none">
            <i class="fas fa-star"></i>
            <span id="loyaltyTxt"></span>
        </div>

        <!-- Address -->
        <div id="checkoutFields" style="margin-bottom:16px; display:none">
            <label class="form-label"><i class="fas fa-location-dot" style="color:var(--primary)"></i> Delivery Address</label>
            @guest
                <input id="addrInput" class="form-control" placeholder="Enter your delivery address" style="margin-top:6px">
            @else
                @if(isset($addresses) && count($addresses) > 0)
                    <select id="addrInput" class="form-control" style="margin-top:6px">
                        @foreach($addresses as $addr)
                            <option value="{{ $addr->Address }}" {{ $addr->IsPrimary ? 'selected' : '' }}>
                                {{ $addr->Address }}
                            </option>
                        @endforeach
                    </select>
                    <div style="margin-top:6px; font-size: 0.8rem; text-align: right;">
                        <a href="javascript:void(0)" data-bs-toggle="modal" data-bs-target="#addressModal" style="color:var(--primary); text-decoration:none;"><i class="fas fa-plus"></i> Add new address</a>
                    </div>
                @else
                    <input id="addrInput" class="form-control" placeholder="Enter your delivery address" style="margin-top:6px" readonly>
                    <div style="margin-top:6px; font-size: 0.8rem; text-align: right;">
                        <a href="javascript:void(0)" data-bs-toggle="modal" data-bs-target="#addressModal" style="color:var(--primary); text-decoration:none;"><i class="fas fa-map-marker-alt"></i> Add Delivery Address</a>
                    </div>
                @endif
            @endguest
        </div>

        <!-- Payment Methods -->
        <div style="margin-bottom:16px" id="paymentFields">
            <label class="form-label"><i class="fas fa-credit-card" style="color:var(--primary)"></i> Payment Method</label>
            <div style="margin-top:10px">
                <div class="pay-card active" id="pc-Cash" onclick="selectPay('Cash')">
                    <div class="pay-card-icon" style="background:rgba(74,222,128,0.1)"><i class="fas fa-money-bill-wave" style="color:var(--success)"></i></div>
                    <div style="flex:1"><div style="font-weight:600;font-size:0.9rem">Cash on Delivery</div><div style="font-size:0.75rem;color:var(--text-muted)">Pay when you receive</div></div>
                </div>
                <div class="pay-card" id="pc-Card" onclick="selectPay('Card')">
                    <div class="pay-card-icon" style="background:rgba(96,165,250,0.1)"><i class="fas fa-credit-card" style="color:var(--info)"></i></div>
                    <div style="flex:1"><div style="font-weight:600;font-size:0.9rem">Credit / Debit Card</div><div style="font-size:0.75rem;color:var(--text-muted)">Secured by PayMob</div></div>
                </div>
                <div class="pay-card" id="pc-Wallet" onclick="selectPay('Wallet')">
                    <div class="pay-card-icon" style="background:rgba(255,107,53,0.1)"><i class="fas fa-wallet" style="color:var(--primary)"></i></div>
                    <div style="flex:1"><div style="font-weight:600;font-size:0.9rem">BiteHub Wallet</div><div style="font-size:0.75rem;color:var(--text-muted)">Balance: {{ number_format($walletBalance ?? 0, 2) }} EGP</div></div>
                </div>

                @auth
                @if(($loyaltyPoints ?? 0) >= 10)
                <!-- BitePoints Option — Rate: 10 pts = 1 EGP -->
                <div style="border:1px dashed rgba(255,193,7,0.5); border-radius:14px; padding:14px 18px; margin-top:4px; background:rgba(255,193,7,0.03);">
                    <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:10px;">
                        <div style="display:flex; align-items:center; gap:10px;">
                            <div style="width:40px;height:40px;border-radius:12px;background:rgba(255,193,7,0.15);display:flex;align-items:center;justify-content:center;">
                                <i class="fas fa-star" style="color:#f59e0b;"></i>
                            </div>
                            <div>
                                <div style="font-weight:600;font-size:0.9rem;">Redeem BitePoints</div>
                                <div style="font-size:0.75rem;color:var(--text-muted);">Balance: <strong style="color:#f59e0b;">{{ $loyaltyPoints ?? 0 }} pts</strong> = <strong style="color:#f59e0b;">{{ number_format(($loyaltyPoints ?? 0) / 100, 2) }} EGP</strong> &nbsp;<span style="opacity:0.5">(100 pts = 1 EGP)</span></div>
                            </div>
                        </div>
                        <label style="cursor:pointer;display:flex;align-items:center;gap:8px;">
                            <input type="checkbox" id="usePointsToggle" onchange="togglePoints()" style="width:18px;height:18px;cursor:pointer;accent-color:#f59e0b;">
                            <span style="font-size:0.8rem;color:var(--text-muted);">Apply</span>
                        </label>
                    </div>
                    <div id="pointsSliderBox" style="display:none;">
                        <div style="display:flex;justify-content:space-between;font-size:0.8rem;color:var(--text-muted);margin-bottom:6px;">
                            <span>Points to use: <strong id="sliderPtsLabel" style="color:#f59e0b;">0</strong></span>
                            <span>Discount: <strong id="sliderEgpLabel" style="color:#4ade80;">0.00 EGP</strong></span>
                        </div>
                        <input type="range" id="pointsSlider" min="0" max="0" step="10" value="0"
                            oninput="onSliderChange()"
                            style="width:100%;accent-color:#f59e0b;cursor:pointer;">
                        <div style="display:flex;justify-content:space-between;font-size:0.72rem;color:var(--text-muted);margin-top:4px;">
                            <span>0 pts</span>
                            <span id="sliderMaxLabel">max: 0 pts</span>
                        </div>
                    </div>
                </div>
                @endif

                <!-- Promo Code Option -->
                <div id="promoCodeBox" style="border:1px dashed rgba(255,107,53,0.3); border-radius:14px; padding:14px 18px; margin-top:12px; background:rgba(255,107,53,0.02);">
                    <div style="display:flex; align-items:center; gap:10px; margin-bottom:10px;">
                        <div style="width:40px;height:40px;border-radius:12px;background:rgba(255,107,53,0.1);display:flex;align-items:center;justify-content:center;">
                            <i class="fas fa-ticket-alt" style="color:var(--primary);"></i>
                        </div>
                        <div style="flex:1">
                            <div style="font-weight:600;font-size:0.9rem;">Promo Code</div>
                            <div style="font-size:0.75rem;color:var(--text-muted);" id="promoSubtitle">Have a coupon code? Apply it below.</div>
                        </div>
                    </div>
                    <div style="display:flex; gap:8px;">
                        <input type="text" id="promoCodeInput" class="form-control" placeholder="ENTER CODE" style="text-transform:uppercase; font-weight:700; letter-spacing:1px; flex:1;">
                        <button type="button" id="promoBtn" onclick="applyOrRemovePromo()" class="btn btn-primary btn-sm" style="padding:0 16px; border-radius:10px; font-weight:600; white-space:nowrap;">Apply</button>
                    </div>
                    <div id="promoMessage" style="display:none; font-size:0.8rem; margin-top:8px; align-items:center; gap:6px;"></div>
                </div>
                @endauth
            </div>
        </div>

        <!-- Pay Alert -->
        <div class="info-box" id="payAlert" style="display:none"></div>

        <!-- Place Order -->
        <button type="button" id="placeBtn" onclick="submitOrder()"
            class="btn btn-primary btn-block btn-lg" style="display:none;margin-top:4px">
            <i class="fas fa-check-circle"></i> Place Order
        </button>

        @guest
        <p style="text-align:center;font-size:0.82rem;color:var(--text-muted);margin-top:12px">
            <a href="{{ route('login') }}" style="color:var(--primary);font-weight:600">Login</a> to place an order
        </p>
        @endguest
    </div>
    </div>

</div>
</form>

</div>
</section>

<!-- Add Address Modal -->
@auth
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin=""/>
<link rel="stylesheet" href="https://unpkg.com/leaflet-control-geocoder/dist/Control.Geocoder.css" />
<style>
#cartMap { height: 300px; width: 100%; border-radius: 8px; border: 1px solid var(--border-color); z-index: 1055;}
</style>
<div class="modal fade" id="addressModal" tabindex="-1" aria-labelledby="addressModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content" style="border-radius: 12px; border: none; box-shadow: 0 10px 30px rgba(0,0,0,0.1);">
      <div class="modal-header" style="border-bottom: 1px solid var(--border-color);">
        <h5 class="modal-title" id="addressModalLabel"><i class="fas fa-map-marked-alt" style="color:var(--primary)"></i> Add New Address</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form action="{{ route('frontend.addresses.store') }}" method="POST">
          @csrf
          <div class="modal-body">
              <div class="mb-3">
                  <label class="form-label">Find your location <span style="color:var(--primary)">*</span></label>
                  <div id="cartMap"></div>
                  <input type="hidden" name="latitude" id="cartLatInput" required>
                  <input type="hidden" name="longitude" id="cartLngInput" required>
                  <small style="color:var(--text-muted);"><i class="fas fa-search"></i> Use the search icon or click the map.</small>
              </div>

              <div class="mb-3">
                  <label class="form-label">Full Address Description</label>
                  <input type="text" id="cartAddressInput" name="address" class="form-control" placeholder="e.g. 123 Main St, Apartment 4B" required>
              </div>
          </div>
          <div class="modal-footer" style="border-top: 1px solid var(--border-color);">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
            <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Save Address</button>
          </div>
      </form>
    </div>
  </div>
</div>
@endauth

<script>
    // Note: The rest of the cart logic is unchanged.
    document.addEventListener('DOMContentLoaded', () => {
        const toggleCheckout = document.getElementById('toggleCheckout');
        const checkoutFields = document.getElementById('checkoutFields');

        if(toggleCheckout) {
            toggleCheckout.addEventListener('click', () => {
                if(checkoutFields.style.display === 'none') {
                    checkoutFields.style.display = 'block';
                    toggleCheckout.textContent = 'Hide Checkout Details';
                    toggleCheckout.classList.replace('btn-primary','btn-secondary');
                } else {
                    checkoutFields.style.display = 'none';
                    toggleCheckout.textContent = 'Proceed to Checkout';
                    toggleCheckout.classList.replace('btn-secondary','btn-primary');
                }
            });
        }

        const form = document.getElementById('checkoutForm');
        if(form) {
            form.addEventListener('submit', (e) => {
                const totalInput = document.getElementById('checkoutTotal');
                totalInput.value = window.currentTotal || 0;

                const ci = document.getElementById('checkoutCartItems');
                ci.value = JSON.stringify(window.cartData || []);

                const method = document.getElementById('paymentMethodInputs').querySelector('.btn-check:checked');
                if(!method) {
                    e.preventDefault();
                    showToast("Please select a payment method.", "warning");
                    return;
                }
                document.getElementById('checkoutPaymentMethod').value = method.value;

                // Sync Address
                const realAddr = document.getElementById('addrInput');
                document.getElementById('finalAddressInput').value = realAddr ? realAddr.value : '';

                if (method.value === 'Card') {
                    form.action = "{{ route('frontend.stripe.checkout') }}";
                } else {
                    form.action = "{{ route('frontend.checkout') }}";
                }
            });
        }
    });
</script>

@auth
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
<script src="https://unpkg.com/leaflet-control-geocoder/dist/Control.Geocoder.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    var mapModal = document.getElementById('addressModal');
    if(!mapModal) return;

    var map = null;
    var marker = null;

    mapModal.addEventListener('shown.bs.modal', function () {
        if(map !== null) {
            map.invalidateSize();
            return;
        }

        var defaultLat = 30.0444;
        var defaultLng = 31.2357;

        map = L.map('cartMap').setView([defaultLat, defaultLng], 12);

        L.tileLayer('https://{s}.basemaps.cartocdn.com/rastertiles/voyager/{z}/{x}/{y}{r}.png', {
            attribution: '&copy; OpenStreetMap contributors &copy; CARTO'
        }).addTo(map);

        var geocoder = L.Control.geocoder({
            defaultMarkGeocode: false,
            placeholder: 'Search for your address...',
        }).on('markgeocode', function(e) {
            var bbox = e.geocode.bbox;
            var center = e.geocode.center;
            map.fitBounds(bbox);
            if (marker) map.removeLayer(marker);
            marker = L.marker(center).addTo(map);
            document.getElementById('cartLatInput').value = center.lat;
            document.getElementById('cartLngInput').value = center.lng;
            var addressInput = document.getElementById('cartAddressInput');
            if(!addressInput.value) addressInput.value = e.geocode.name;
        }).addTo(map);

        if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(function(position) {
                defaultLat = position.coords.latitude;
                defaultLng = position.coords.longitude;
                map.flyTo([defaultLat, defaultLng], 14);
            });
        }

        function getCartAddressFromCoords(lat, lng) {
            var addrInp = document.getElementById('cartAddressInput');
            addrInp.value = 'جاري تحميل تفاصيل العنوان...';
            fetch(`https://nominatim.openstreetmap.org/reverse?format=jsonv2&lat=${lat}&lon=${lng}&accept-language=ar`)
                .then(response => response.json())
                .then(data => {
                    if(data && data.display_name) {
                        addrInp.value = data.display_name;
                    } else {
                        addrInp.value = '';
                    }
                })
                .catch(error => {
                    addrInp.value = '';
                });
        }

        map.on('click', function(e) {
            if(marker) map.removeLayer(marker);
            marker = L.marker(e.latlng).addTo(map);
            document.getElementById('cartLatInput').value = e.latlng.lat;
            document.getElementById('cartLngInput').value = e.latlng.lng;
            getCartAddressFromCoords(e.latlng.lat, e.latlng.lng);
        });
    });
});
</script>
@endauth

@push('scripts')
<script>
const WALLET    = {{ $walletBalance ?? 0 }};
const LOYALTY   = {{ $loyaltyPoints ?? 0 }};
const IS_AUTH   = {{ auth()->check() ? 'true' : 'false' }};
const PAYMOB_URL= '{{ route("frontend.paymob.checkout") }}';
let   payMethod = 'Cash';
let   appliedPromo = null;
const initialPromoCode = @json(session('applied_promo_code'));

function addApprovedToCart(id, name, basePrice, extra, img, note, chatId, sessionId) {
    const total = parseFloat(basePrice) + parseFloat(extra);
    if (isNaN(total)) {
        showToast('Error calculating price', 'error');
        return;
    }
    addToCart(id, name, total, img, null, null, note, sessionId);
    showToast('Approved item added to cart!', 'success');
    renderCart();

    // Hide the card immediately and mark as used server-side
    if (chatId) {
        const card = document.getElementById('customization-card-' + chatId);
        if (card) {
            card.style.transition = 'opacity 0.3s ease';
            card.style.opacity = '0';
            setTimeout(() => {
                card.remove();
                // Hide the whole customizations section if no cards remain
                const section = document.querySelector('.glass-card[style*="rgba(255,107,53,0.3)"]');
                if (section && section.querySelectorAll('[id^="customization-card-"]').length === 0
                    && section.querySelectorAll('.cart-item[style*="opacity:0.7"]').length === 0) {
                    section.style.display = 'none';
                }
            }, 300);
        }

        // Tell the server this customization is consumed
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || document.querySelector('input[name="_token"]')?.value || '{{ csrf_token() }}';
        fetch('{{ route("frontend.cart.customization.used", ["id" => "__ID__"]) }}'.replace('__ID__', chatId), {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            }
        }).catch(err => console.error(err));

    // The approved request is now consumed — remove it from the customization counter
        if (typeof addCustomizationCount === 'function') addCustomizationCount(-1);
        // Also remove the item-level pill indicator
        if (typeof removePendingCustomItem === 'function') removePendingCustomItem(id);
    }
}



function deleteCustomizationRequest(id, menuItemId) {
    window.biteConfirm('Remove this customization request? This will cancel your conversation with the kitchen.', function(res) {
        if (!res) return;
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
            || document.querySelector('input[name="_token"]')?.value
            || '{{ csrf_token() }}';
        fetch('/cart/customization/' + id, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            }
        })
        .then(r => r.json())
        .then(data => {
            if (data.status === 'ok') {
                const card = document.getElementById('pending-card-' + id) || document.getElementById('customization-card-' + id);
                if (card) {
                    card.style.transition = 'opacity 0.3s ease, max-height 0.3s ease';
                    card.style.opacity = '0';
                    setTimeout(() => {
                        card.remove();
                        // Hide the whole customizations section if nothing remains
                        const section = document.querySelector('.glass-card[style*="rgba(255,107,53,0.3)"]');
                        if (section) {
                            const remaining = section.querySelectorAll('.cart-item');
                            let anyVisible = false;
                            remaining.forEach(el => { if (window.getComputedStyle(el).display !== 'none') anyVisible = true; });
                            if (!anyVisible) section.style.display = 'none';
                        }
                    }, 300);
                }
                // Decrement the customization counter in the badge
                if (typeof addCustomizationCount === 'function') addCustomizationCount(-1);
                // Remove the item-level pill indicator
                if (typeof removePendingCustomItem === 'function' && menuItemId) removePendingCustomItem(menuItemId);
                showToast('Request removed.', 'success');
            } else {
                showToast('Failed to remove request.', 'error');
            }
        })
        .catch(() => showToast('Network error.', 'error'));
    });
}

function clearCartNow() {
    window.biteConfirm('Clear all items from cart?', function(res) {
        if (!res) return;
        clearCart();
        renderCart();
    });
}

function renderCart() {
    const cart   = getCart();
    const box    = document.getElementById('cartItems');
    const custom = document.getElementById('customSection');
    const btn    = document.getElementById('placeBtn');

    if (!cart.length) {
        box.innerHTML = `
            <div class="cart-empty">
                <span class="empty-icon">🛒</span>
                <h3 style="font-size:1.1rem;margin-bottom:8px">Your cart is empty</h3>
                <p style="font-size:0.9rem;margin-bottom:20px">Add some delicious dishes</p>
                <a href="{{ route('frontend.menu') }}" class="btn btn-primary btn-sm">Browse Menu</a>
            </div>`;
        if (custom) custom.style.display = 'none';
        if (btn) btn.style.display = 'none';
        document.getElementById('totalLines').innerHTML = '<p style="text-align:center;color:var(--text-muted);padding:20px 0;font-size:0.9rem">Add items to see your total</p>';
        document.getElementById('loyaltyHint').style.display = 'none';
        document.getElementById('checkoutFields').style.display = 'none';
        return;
    }

    // ─── Calculate Unique Vendors ───────────────────────────────────────────
    // We need to know how many actual kitchens/caterers are in the cart
    // to calculate the delivery fee (15 EGP per vendor)
    const vendors = new Set();
    cart.forEach(item => {
        // Subscriptions have a kitchen_id, Items have a kitchen_id or caterer_id
        if (item.is_subscription && item.kitchen_id) vendors.add('k_' + item.kitchen_id);
        else if (item.kitchen_id) vendors.add('k_' + item.kitchen_id);
        else if (item.caterer_id) vendors.add('c_' + item.caterer_id);
    });
    window.vendorCount = Math.max(1, vendors.size);

    if (custom) {
        custom.style.display = 'block';
    }
    
    // Hide approved Customizations if they already exist in Cart
    const customCards = document.querySelectorAll('[id^="customization-card-"]');
    customCards.forEach(card => {
        let noteNode = card.querySelector('em');
        if (noteNode) {
            let noteStr = noteNode.textContent.replace(/^"|"$/g, '').trim();
            if (cart.some(item => (item.note || '').trim() === noteStr)) {
                card.style.display = 'none';
            }
        }
    });

    const approvalSection = document.querySelector('.glass-card[style*="rgba(255,107,53,0.3)"]');
    if (approvalSection) {
        let anyVisible = false;
        approvalSection.querySelectorAll('.cart-item').forEach(ci => {
            if (window.getComputedStyle(ci).display !== 'none') anyVisible = true;
        });
        if (!anyVisible) approvalSection.style.display = 'none';
    }

    if (btn) btn.style.display = 'flex';
    document.getElementById('checkoutFields').style.display = 'block';

    const foodEmojis = ['🍕','🍜','🥗','🍗','🥘','🍱','🥙','🍛'];
    box.innerHTML = cart.map((item, i) => `
        <div class="cart-item">
            <div class="item-img" style="background:none; border:none; padding:0; overflow:hidden;"><img src="${item.img ? item.img : '/upload/website_assets/grills.png'}" style="width:100%; height:100%; object-fit:cover;" alt="Item"></div>
            <div style="flex:1;min-width:0">
                <div style="font-weight:600;font-size:0.95rem;white-space:nowrap;overflow:hidden;text-overflow:ellipsis">${item.name}</div>
                <div style="color:var(--text-muted);font-size:0.8rem;margin-top:2px">${item.is_subscription ? 'Full Plan Price' : item.price.toFixed(2) + ' EGP each'}</div>
                ${item.details ? `<div style="font-size:0.75rem; color:var(--primary); font-weight:600;">${item.details}</div>` : ''}
                ${item.note ? `<div style="margin-top:4px;padding:4px 8px;background:rgba(255,107,53,0.06);border-left:2px solid var(--primary);border-radius:4px;font-size:0.75rem;color:var(--text-secondary);display:flex;align-items:center;gap:6px;"><i class="fas fa-comment-dots" style="color:var(--primary)"></i> <span><b>Note:</b> ${item.note}</span></div>` : ''}
            </div>
            <div style="display:flex;align-items:center;gap:8px">
                ${!item.is_subscription ? `
                <button type="button" class="qty-btn" onclick="updateCartQty(${i},-1)">−</button>
                <span style="font-weight:700;min-width:22px;text-align:center">${item.qty}</span>
                <button type="button" class="qty-btn" onclick="updateCartQty(${i},1)">+</button>
                ` : '<span class="badge bg-soft-primary" style="color:var(--primary); background:rgba(255,107,53,0.1); padding:5px 10px; border-radius:8px;">Plan</span>'}
            </div>
            <div style="font-weight:700;color:var(--primary);min-width:85px;text-align:right;font-size:0.95rem">${(item.price*item.qty).toFixed(2)} EGP</div>
            <button type="button" class="del-btn" onclick="removeFromCart(${i})"><i class="fas fa-trash"></i></button>
        </div>`).join('');

    renderTotals(cart);
}

function renderTotals(cart) {
    const sub   = getCartTotal();
    const vendorCount = window.vendorCount || 1;
    const del   = vendorCount * 15;
    const total = sub + del;
    
    // Deposit Logic
    const hasSubscription = cart.some(item => item.is_subscription);
    const isDepositOn = document.getElementById('isDepositCheckbox')?.checked || false;
    let depositAmount = 0;
    let finalPayable = total;

    if (hasSubscription && isDepositOn) {
        // Calculate 20% deposit for subscriptions, keep delivery and other items at full price
        const subTotalOnly = cart.filter(item => item.is_subscription).reduce((acc, item) => acc + item.price, 0);
        depositAmount = subTotalOnly * 0.2;
        finalPayable = (total - subTotalOnly) + depositAmount;
    }

    let summaryHtml = `
        <div style="display:flex;justify-content:space-between;padding:8px 0;color:var(--text-secondary);font-size:0.9rem">
            <span>Subtotal (${cart.reduce((s,i)=>s+i.qty,0)} items)</span><span>${sub.toFixed(2)} EGP</span>
        </div>
        <div style="display:flex;justify-content:space-between;padding:8px 0;color:var(--text-secondary);font-size:0.9rem">
            <span>Delivery (${vendorCount} vendor${vendorCount > 1 ? 's' : ''})</span><span>${del.toFixed(2)} EGP</span>
        </div>
    `;

    if (hasSubscription) {
        summaryHtml += `
            <div style="background:rgba(255,107,53,0.05); border:1px dashed var(--primary); padding:12px; border-radius:12px; margin:10px 0;">
                <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:5px;">
                    <span style="font-weight:700; font-size:0.85rem; color:var(--primary);">Meal Plan Deposit Option</span>
                    <label style="display:flex; align-items:center; gap:8px; cursor:pointer;">
                        <input type="checkbox" id="isDepositCheckbox" ${isDepositOn ? 'checked' : ''} onchange="renderTotals(getCart())" style="width:18px;height:18px; accent-color:var(--primary);">
                        <span style="font-size:0.8rem; font-weight:600;">Pay 20% Now</span>
                    </label>
                </div>
                <p style="font-size:0.75rem; color:var(--text-muted); margin:0;">Pay only 20% now to confirm your plan. The remaining balance must be paid at least 24h before plan ends.</p>
            </div>
        `;
    }

    if (isDepositOn && hasSubscription) {
        summaryHtml += `
            <div style="display:flex;justify-content:space-between;padding:8px 0;color:var(--success);font-size:0.9rem; font-weight:600;">
                <span>Deposit Applied</span><span>- ${(sub - (sub - cart.filter(it=>it.is_subscription).reduce((a,b)=>a+b.price,0)) - depositAmount).toFixed(2)} EGP</span>
            </div>
        `;
    }

    // Promo Code Logic
    let promoDiscount = 0;
    if (appliedPromo) {
        if (sub < appliedPromo.min_order_amount) {
            setTimeout(() => {
                removePromoCodeSilent();
                showToast(`Promo code removed: subtotal is below minimum order amount of ${appliedPromo.min_order_amount} EGP.`, 'warning');
            }, 0);
        } else {
            if (appliedPromo.type === 'Percentage') {
                promoDiscount = sub * (appliedPromo.value / 100);
            } else if (appliedPromo.type === 'Fixed') {
                promoDiscount = appliedPromo.value;
            }
            promoDiscount = parseFloat(Math.min(sub, promoDiscount).toFixed(2));
            finalPayable = Math.max(0, finalPayable - promoDiscount);

            summaryHtml += `
                <div style="display:flex;justify-content:space-between;padding:8px 0;color:var(--primary);font-size:0.9rem;font-weight:600;">
                    <span><i class="fas fa-ticket-alt"></i> Promo Discount (${appliedPromo.code})</span><span>- ${promoDiscount.toFixed(2)} EGP</span>
                </div>
            `;
        }
    }

    updateSliderMax();

    const REDEEM_RATE = 100; // 100 points = 1 EGP
    const hint = document.getElementById('loyaltyHint');
    if (hint) {
        hint.style.display = 'flex';
    }
    const usedPts   = parseInt(document.getElementById('fPointsUsed').value) || 0;
    const ptsDiscount  = parseFloat((usedPts / REDEEM_RATE).toFixed(2)); 
    const payable   = parseFloat(Math.max(0, finalPayable - ptsDiscount).toFixed(2));
    const earnPts   = Math.floor(payable);

    if (usedPts > 0) {
        summaryHtml += `
            <div style="display:flex;justify-content:space-between;padding:8px 0;color:#f59e0b;font-size:0.9rem;">
                <span><i class="fas fa-star"></i> BitePoints Discount</span><span>- ${ptsDiscount.toFixed(2)} EGP</span>
            </div>
        `;
    }

    summaryHtml += `
        <hr style="border:none;border-top:1px solid var(--border-color);margin:4px 0">
        <div style="display:flex;justify-content:space-between;padding:10px 0;font-size:1.15rem;font-weight:800;letter-spacing:-0.3px">
            <span>Total Payable</span><span style="color:var(--primary)">${payable.toFixed(2)} EGP</span>
        </div>
    `;

    document.getElementById('totalLines').innerHTML = summaryHtml;

    const loyaltyTxtNode = document.getElementById('loyaltyTxt');
    if (loyaltyTxtNode) {
        loyaltyTxtNode.textContent =
            usedPts > 0
                ? `Redeeming ${usedPts} pts = -${ptsDiscount.toFixed(2)} EGP · You'll earn ${earnPts} pts on delivery`
                : `You'll earn ${earnPts} pts on delivery · Your balance: ${LOYALTY} pts`;
    }
    refreshPayAlert(payable);
}

function selectPay(val) {
    payMethod = val;
    document.getElementById('fPayment').value = val;
    ['Cash','Card','Wallet'].forEach(v =>
        document.getElementById('pc-'+v).classList.toggle('active', v === val)
    );
    renderTotals(getCart());
    const btn = document.getElementById('placeBtn');
    if (!btn) return;
    if (val === 'Card')   btn.innerHTML = '<i class="fas fa-lock"></i> Pay with Card';
    else if (val === 'Wallet') btn.innerHTML = '<i class="fas fa-wallet"></i> Pay with Wallet';
    else btn.innerHTML = '<i class="fas fa-check-circle"></i> Place Order';
}

function togglePoints() {
    const on  = document.getElementById('usePointsToggle').checked;
    const box = document.getElementById('pointsSliderBox');
    box.style.display = on ? 'block' : 'none';
    if (!on) {
        document.getElementById('pointsSlider').value = 0;
        document.getElementById('fPointsUsed').value  = 0;
        document.getElementById('sliderPtsLabel').textContent = '0';
        document.getElementById('sliderEgpLabel').textContent = '0.00 EGP';
    }
    updateSliderMax();
    renderTotals(getCart());
}

function updateSliderMax() {
    const REDEEM_RATE = 100; // 100 pts = 1 EGP
    const sub         = getCartTotal();
    const del         = (window.vendorCount || 1) * 15;
    const total       = sub + del;

    let promoDiscount = 0;
    if (appliedPromo && sub >= appliedPromo.min_order_amount) {
        if (appliedPromo.type === 'Percentage') {
            promoDiscount = sub * (appliedPromo.value / 100);
        } else if (appliedPromo.type === 'Fixed') {
            promoDiscount = appliedPromo.value;
        }
        promoDiscount = parseFloat(Math.min(sub, promoDiscount).toFixed(2));
    }

    const remainingTotal = Math.max(0, total - promoDiscount);
    // Max discount = 50% of remaining order total (in EGP), converted to points
    const maxEgpDiscount = Math.floor(remainingTotal * 0.5 * 100) / 100;
    const maxPtsByPct    = Math.floor(maxEgpDiscount * REDEEM_RATE); // pts needed for 50% discount
    const maxPtsByBal    = LOYALTY;                                   // actual balance
    // Round down to nearest 10 so the step=10 slider lands cleanly
    const maxPts = Math.max(0, Math.floor(Math.min(maxPtsByPct, maxPtsByBal) / 10) * 10);

    const slider = document.getElementById('pointsSlider');
    if (!slider) return;
    slider.max  = maxPts;
    slider.step = 10;
    slider.value = Math.min(parseInt(slider.value) || 0, maxPts);

    const label = document.getElementById('sliderMaxLabel');
    if (label) label.textContent = `max: ${maxPts} pts = ${(maxPts / REDEEM_RATE).toFixed(2)} EGP off`;
}

function onSliderChange() {
    const REDEEM_RATE = 100;
    const pts         = parseInt(document.getElementById('pointsSlider').value) || 0;
    const egpVal      = parseFloat((pts / REDEEM_RATE).toFixed(2));
    document.getElementById('fPointsUsed').value          = pts;
    document.getElementById('sliderPtsLabel').textContent  = pts;
    document.getElementById('sliderEgpLabel').textContent  = egpVal.toFixed(2) + ' EGP';
    renderTotals(getCart());
}

function refreshPayAlert(total) {
    const alertBox = document.getElementById('payAlert');
    if (!alertBox) return;
    alertBox.className = 'info-box';
    alertBox.style.display = 'none';
    if (payMethod === 'Card') {
        alertBox.classList.add('info-blue');
        alertBox.style.display = 'flex';
        alertBox.innerHTML = '<i class="fas fa-lock"></i>&nbsp; You\'ll be redirected to PayMob\'s secure payment page.';
    } else if (payMethod === 'Wallet') {
        alertBox.style.display = 'flex';
        if (WALLET >= total) {
            alertBox.classList.add('info-success');
            alertBox.innerHTML = `<i class="fas fa-check-circle"></i>&nbsp; Balance OK — remaining: ${(WALLET - total).toFixed(2)} EGP`;
            document.getElementById('placeBtn').disabled = false;
        } else {
            alertBox.classList.add('info-error');
            alertBox.innerHTML = `<i class="fas fa-exclamation-circle"></i>&nbsp; Insufficient balance (${WALLET.toFixed(2)} EGP). Need ${total.toFixed(2)} EGP.`;
            document.getElementById('placeBtn').disabled = true;
        }
    } else {
        document.getElementById('placeBtn').disabled = false;
    }
}

function applyOrRemovePromo() {
    if (appliedPromo) {
        removePromoCode();
    } else {
        applyPromo();
    }
}

function applyPromo() {
    const code = document.getElementById('promoCodeInput').value.trim();
    if (!code) {
        showToast('Please enter a promo code.', 'warning');
        return;
    }

    const subtotal = getCartTotal();
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
        || document.querySelector('input[name="_token"]')?.value
        || '{{ csrf_token() }}';

    const btn = document.getElementById('promoBtn');
    btn.disabled = true;
    btn.textContent = 'Applying...';

    fetch('{{ route("frontend.cart.apply_promo") }}', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': csrfToken,
            'Accept': 'application/json',
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({ code: code, subtotal: subtotal, cart_items: getCart() })
    })
    .then(r => r.json())
    .then(data => {
        btn.disabled = false;
        if (data.success) {
            appliedPromo = {
                code: data.code,
                type: data.type,
                value: parseFloat(data.value),
                min_order_amount: parseFloat(data.min_order_amount)
            };
            showToast(data.message, 'success');
            updatePromoUI(true);
            renderTotals(getCart());
        } else {
            showToast(data.message, 'error');
            updatePromoUI(false, data.message);
        }
    })
    .catch(err => {
        btn.disabled = false;
        showToast('Network error while applying promo code.', 'error');
    });
}

function applyPromoSilent(code) {
    const subtotal = getCartTotal();
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
        || document.querySelector('input[name="_token"]')?.value
        || '{{ csrf_token() }}';

    fetch('{{ route("frontend.cart.apply_promo") }}', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': csrfToken,
            'Accept': 'application/json',
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({ code: code, subtotal: subtotal, cart_items: getCart() })
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            appliedPromo = {
                code: data.code,
                type: data.type,
                value: parseFloat(data.value),
                min_order_amount: parseFloat(data.min_order_amount)
            };
            updatePromoUI(true);
            renderTotals(getCart());
        } else {
            removePromoCodeSilent();
        }
    })
    .catch(err => {
        console.error('Error applying initial promo code:', err);
    });
}

function removePromoCode() {
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
        || document.querySelector('input[name="_token"]')?.value
        || '{{ csrf_token() }}';

    const btn = document.getElementById('promoBtn');
    if (btn) {
        btn.disabled = true;
        btn.textContent = 'Removing...';
    }

    fetch('{{ route("frontend.cart.remove_promo") }}', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': csrfToken,
            'Accept': 'application/json',
            'Content-Type': 'application/json'
        }
    })
    .then(r => r.json())
    .then(data => {
        if (btn) btn.disabled = false;
        if (data.success) {
            appliedPromo = null;
            document.getElementById('promoCodeInput').value = '';
            showToast('Promo code removed.', 'success');
            updatePromoUI(false);
            renderTotals(getCart());
        }
    })
    .catch(err => {
        if (btn) btn.disabled = false;
        showToast('Network error while removing promo code.', 'error');
    });
}

function removePromoCodeSilent() {
    appliedPromo = null;
    const inp = document.getElementById('promoCodeInput');
    if (inp) inp.value = '';
    updatePromoUI(false);

    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
        || document.querySelector('input[name="_token"]')?.value
        || '{{ csrf_token() }}';

    fetch('{{ route("frontend.cart.remove_promo") }}', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': csrfToken,
            'Accept': 'application/json',
            'Content-Type': 'application/json'
        }
    })
    .then(() => {
        renderTotals(getCart());
    });
}

function updatePromoUI(isApplied, errorMessage = '') {
    const btn = document.getElementById('promoBtn');
    const msg = document.getElementById('promoMessage');
    const input = document.getElementById('promoCodeInput');
    const subtitle = document.getElementById('promoSubtitle');

    if (!btn || !msg) return;

    if (isApplied && appliedPromo) {
        btn.textContent = 'Remove';
        btn.className = 'btn btn-outline-danger btn-sm';
        input.disabled = true;
        
        let valueStr = appliedPromo.type === 'Percentage' ? `${appliedPromo.value}%` : `${appliedPromo.value} EGP`;
        subtitle.innerHTML = `Applied: <strong style="color:var(--primary);">${appliedPromo.code}</strong> (${valueStr} off)`;

        msg.style.display = 'flex';
        msg.style.color = 'var(--success)';
        msg.innerHTML = `<i class="fas fa-check-circle"></i> Promo code applied successfully!`;
    } else {
        btn.textContent = 'Apply';
        btn.className = 'btn btn-primary btn-sm';
        input.disabled = false;
        subtitle.textContent = 'Have a coupon code? Apply it below.';

        if (errorMessage) {
            msg.style.display = 'flex';
            msg.style.color = 'var(--danger)';
            msg.innerHTML = `<i class="fas fa-exclamation-circle"></i> ${errorMessage}`;
        } else {
            msg.style.display = 'none';
            msg.innerHTML = '';
        }
    }
}

function submitOrder() {
    if (!IS_AUTH) { showToast('Please login to place an order.', 'warning'); return; }
    const cart = getCart();
    if (!cart.length) { showToast('Your cart is empty!', 'warning'); return; }
    const addrElem = document.getElementById('addrInput');
    const addr = addrElem ? addrElem.value.trim() : '';
    if (!addr) { if(addrElem) addrElem.focus(); showToast('Please enter your delivery address.', 'warning'); return; }

    const vendorCount = window.vendorCount || 1;
    const sub         = getCartTotal();
    const del         = vendorCount * 15;
    const total       = sub + del;

    // Deposit Logic (must match renderTotals)
    const hasSubscription = cart.some(item => item.is_subscription);
    const isDepositOn     = document.getElementById('isDepositCheckbox')?.checked || false;
    let finalTotal     = total;

    if (hasSubscription && isDepositOn) {
        const subTotalOnly = cart.filter(item => item.is_subscription).reduce((acc, item) => acc + item.price, 0);
        finalTotal = (total - subTotalOnly) + (subTotalOnly * 0.2);
    }

    // Subtract promo discount
    let promoDiscount = 0;
    if (appliedPromo && sub >= appliedPromo.min_order_amount) {
        if (appliedPromo.type === 'Percentage') {
            promoDiscount = sub * (appliedPromo.value / 100);
        } else if (appliedPromo.type === 'Fixed') {
            promoDiscount = appliedPromo.value;
        }
        promoDiscount = parseFloat(Math.min(sub, promoDiscount).toFixed(2));
    }
    finalTotal = Math.max(0, finalTotal - promoDiscount);

    const usedPts     = parseInt(document.getElementById('fPointsUsed').value) || 0;
    const discount    = parseFloat((usedPts / 100).toFixed(2));
    finalTotal  = parseFloat(Math.max(0, finalTotal - discount).toFixed(2));

    if (payMethod === 'Wallet' && WALLET < finalTotal) {
        showToast(`Insufficient wallet balance. You need ${finalTotal.toFixed(2)} EGP but have ${WALLET.toFixed(2)} EGP.`, 'warning');
        return;
    }

    document.getElementById('fTotal').value      = finalTotal;  // cash amount after points discount
    document.getElementById('fItems').value      = JSON.stringify(cart);
    document.getElementById('fPayment').value    = payMethod;
    document.getElementById('fAddress').value    = addr;
    document.getElementById('fPointsUsed').value = usedPts;
    document.getElementById('fIsDeposit').value  = document.getElementById('isDepositCheckbox')?.checked ? 1 : 0;

    const form = document.getElementById('mainForm');
    if (payMethod === 'Card') { form.action = PAYMOB_URL; }
    else { form.action = '{{ route("frontend.checkout") }}'; clearCart(); }
    form.submit();
}

document.addEventListener('DOMContentLoaded', () => {
    renderCart();
    if (initialPromoCode && IS_AUTH) {
        const input = document.getElementById('promoCodeInput');
        if (input) input.value = initialPromoCode;
        applyPromoSilent(initialPromoCode);
    }
});
</script>
@endpush
@endsection
