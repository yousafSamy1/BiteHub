@extends('frontend.layouts.app')
@section('title', $item->ItemName)

@section('content')
<div class="page-header" style="padding-bottom:28px">
    <div style="display:flex;align-items:center;gap:8px;justify-content:center;color:var(--text-muted);font-size:0.85rem;margin-bottom:12px">
        <a href="{{ route('frontend.menu') }}" style="color:var(--primary)">Menu</a>
        <i class="fas fa-chevron-right" style="font-size:0.7rem"></i>
        @if($item->CatName)
        <a href="{{ route('frontend.menu', ['cat' => $item->CategoryID]) }}" style="color:var(--primary)">{{ $item->CatName }}</a>
        <i class="fas fa-chevron-right" style="font-size:0.7rem"></i>
        @endif
        <span>{{ $item->ItemName }}</span>
    </div>
    <h1 style="font-size:2rem">{{ $item->ItemName }}</h1>
</div>

<section class="section" style="padding-top:0">
<div class="container">
    @php
        $itemImg = null;
        if($item->images->count() > 0) {
            $itemImg = asset('upload/item_images/'.$item->images->first()->Image);
        } else {
            $itemImg = url('upload/website_assets/grills.png');
        }
    @endphp
    <style>@media(max-width:991px){ .item-main-grid { grid-template-columns: 1fr !important; gap: 24px !important; } .glass-card { padding: 24px !important; } }</style>
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:32px;align-items:start" class="reveal item-main-grid">
        <!-- Image -->
        <div class="glass-card" style="display:flex;align-items:center;justify-content:center;min-height:320px;padding:32px;font-size:7rem">
            @if($item->images->count() > 0)
                <img src="{{ asset('upload/item_images/'.$item->images->first()->Image) }}" alt="{{ $item->ItemName }}" style="width:100%;max-height:400px;object-fit:cover;border-radius:14px">
            @else
                🍽️
            @endif
        </div>
        <!-- Details -->
        <div class="glass-card" style="padding:32px">
            @if($item->CatName)
            <span style="display:inline-flex;align-items:center;gap:6px;background:rgba(255,107,53,0.1);border:1px solid rgba(255,107,53,0.25);color:var(--primary);padding:4px 14px;border-radius:20px;font-size:0.78rem;font-weight:700;margin-bottom:16px">{{ $item->CatName }}</span>
            @endif
            <h2 style="margin:0 0 12px;letter-spacing:-0.5px">{{ $item->ItemName }}</h2>
            <div class="kitchen-rating mb-2">
                <i class="fas fa-star"></i> 4.7
                <span style="color:var(--text-muted);font-weight:400">(92 reviews)</span>
            </div>
            <p style="color:var(--text-secondary);margin-bottom:24px;line-height:1.75">{{ $item->Description ?? 'A delicious dish prepared with fresh, quality ingredients and authentic flavors.' }}</p>
            <div class="flex-between mb-3">
                @if($item->DiscountPrice)
                    <div>
                        <span style="text-decoration:line-through;color:var(--text-muted);font-size:1rem;display:block;line-height:1.2">{{ number_format($item->ItemPrice, 2) }} EGP</span>
                        <span class="menu-price text-success" style="font-size:2.2rem">{{ number_format($item->DiscountPrice, 2) }}<small> EGP</small></span>
                        <span style="display:inline-block;background:rgba(40,199,111,0.12);border:1px solid rgba(40,199,111,0.3);color:#28c76f;padding:3px 10px;border-radius:20px;font-size:0.78rem;font-weight:700;margin-left:8px">
                            {{ round((1 - $item->DiscountPrice / $item->ItemPrice) * 100) }}% OFF
                        </span>
                    </div>
                @else
                    <span class="menu-price" style="font-size:2.2rem">{{ number_format($item->ItemPrice, 2) }}<small> EGP</small></span>
                @endif
                <span class="badge-status badge-{{ strtolower($item->Status) }}">{{ $item->Status }}</span>
            </div>
            </div>
            @php
                $vendor = $item->kitchenOwner ?? $item->caterer;
                $vStatus = $vendor ? $vendor->current_status : 'Open';
            @endphp
            <div style="display:flex;gap:12px;flex-direction:column">
                @if($vStatus === 'Closed')
                    <button class="btn btn-outline btn-lg" style="width:100%;opacity:0.6;cursor:not-allowed;filter:grayscale(1)" disabled>
                        <i class="fas fa-times"></i> Kitchen Currently Closed
                    </button>
                @else
                    <button class="btn btn-primary btn-lg" style="width:100%" onclick="addToCart({{ $item->MenuItemID }},'{{ addslashes($item->ItemName) }}',{{ $item->DiscountPrice ?: $item->ItemPrice }},'{{ $itemImg }}',{{ $item->KitchenOwnerID ?? 'null' }},{{ $item->CatererID ?? 'null' }})">
                        <i class="fas fa-cart-plus"></i> Add to Cart
                    </button>
                    @if($item->KitchenOwnerID)
                    <button class="btn btn-outline-primary btn-lg" style="width:100%; border:2px solid var(--primary)" onclick="openSubscriptionModal()">
                        <i class="fas fa-calendar-check"></i> Request Subscription Plan
                    </button>
                    @endif
                @endif
            </div>
        </div>
    </div>

    @if(count($relatedItems))
    <div class="section-header reveal" style="text-align:left;margin:56px 0 28px">
        <h2 style="font-size:1.5rem">You Might Also Like</h2>
    </div>
    <div class="grid grid-4 reveal">
        @foreach($relatedItems as $r)
        <a href="{{ route('frontend.item', $r->MenuItemID) }}" class="card menu-card">
            <div class="card-img" style="background:linear-gradient(135deg,rgba(255,107,53,0.1),rgba(255,167,38,0.05));display:flex;align-items:center;justify-content:center;font-size:3rem;overflow:hidden">
                @if($r->images->count() > 0)
                    <img src="{{ asset('upload/item_images/'.$r->images->first()->Image) }}" alt="{{ $r->ItemName }}" style="width:100%;height:100%;object-fit:cover">
                @else 🍽️ @endif
            </div>
            <div class="card-body">
                <h3 class="card-title">{{ $r->ItemName }}</h3>
                @if($r->DiscountPrice)
                    <span style="text-decoration:line-through;color:var(--text-muted);font-size:0.8rem;display:block">{{ number_format($r->ItemPrice, 2) }} EGP</span>
                    <span class="menu-price text-success">{{ number_format($r->DiscountPrice, 2) }}<small> EGP</small></span>
                @else
                    <span class="menu-price">{{ number_format($r->ItemPrice, 2) }}<small> EGP</small></span>
                @endif
            </div>
        </a>
        @endforeach
    </div>
    @endif

</div>
</section>

<!-- Subscription Request Modal -->
<div class="modal fade" id="subModal" tabindex="-1" aria-hidden="true" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.7); z-index:9999; backdrop-filter:blur(8px); align-items:center; justify-content:center">
    <div style="background:var(--bg-card); width:95%; max-width:500px; border-radius:24px; padding:32px; border:1px solid var(--border-color); box-shadow:0 20px 40px rgba(0,0,0,0.4)">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:24px">
            <h3 style="margin:0; font-size:1.3rem">Request <span class="highlight">Subscription</span></h3>
            <button onclick="closeSubscriptionModal()" style="background:none; border:none; color:var(--text-muted); font-size:1.2rem; cursor:pointer"><i class="fas fa-times"></i></button>
        </div>
        
        <form id="subRequestForm" onsubmit="submitSubscriptionRequest(event)">
            @csrf
            <input type="hidden" name="menu_item_id" value="{{ $item->MenuItemID }}">
            
            <div class="mb-4">
                <label class="form-label" style="display:block; margin-bottom:8px; font-weight:700">Subscription Duration</label>
                <select name="duration" class="form-control" style="width:100%; height:50px; border-radius:12px; padding:0 16px" required>
                    <option value="7">1 Week (7 Days)</option>
                    <option value="14">2 Weeks (14 Days)</option>
                    <option value="30" selected>1 Month (30 Days)</option>
                </select>
            </div>
            
            <div class="mb-4">
                <label class="form-label" style="display:block; margin-bottom:8px; font-weight:700">Meals Per Day</label>
                <div class="meal-options-grid" style="display:grid; grid-template-columns:1fr 1fr 1fr; gap:12px">
                    <style>@media(max-width:480px){ .meal-options-grid { grid-template-columns: 1fr !important; } }</style>
                    <label class="pay-card" style="margin-bottom:0; padding:12px; justify-content:center; cursor:pointer">
                        <input type="radio" name="meals_per_day" value="1" checked style="display:none" onchange="updateRadioStates(this)">
                        <span style="font-weight:700">1 Meal</span>
                    </label>
                    <label class="pay-card" style="margin-bottom:0; padding:12px; justify-content:center; cursor:pointer">
                        <input type="radio" name="meals_per_day" value="2" style="display:none" onchange="updateRadioStates(this)">
                        <span style="font-weight:700">2 Meals</span>
                    </label>
                    <label class="pay-card" style="margin-bottom:0; padding:12px; justify-content:center; cursor:pointer">
                        <input type="radio" name="meals_per_day" value="3" style="display:none" onchange="updateRadioStates(this)">
                        <span style="font-weight:700">3 Meals</span>
                    </label>
                </div>
            </div>
            
            <div class="mb-4">
                <label class="form-label" style="display:block; margin-bottom:8px; font-weight:700">Start Date</label>
                <input type="date" name="start_date" class="form-control" style="width:100%; height:50px; border-radius:12px; padding:0 16px" 
                       min="{{ date('Y-m-d', strtotime('+1 day')) }}" value="{{ date('Y-m-d', strtotime('+1 day')) }}" required>
            </div>

            <p style="font-size:0.85rem; color:var(--text-muted); margin-bottom:24px; line-height:1.5">
                <i class="fas fa-info-circle"></i> Your request will be sent to <strong>{{ $item->KitchenName }}</strong>. They will review it and provide a price quote.
            </p>
            
            <button type="submit" class="btn btn-primary w-100 py-3 fw-bold" id="subSubmitBtn">
                Send Request
            </button>
        </form>
    </div>
</div>

@push('scripts')
<script>
function openSubscriptionModal() {
    @auth
        document.getElementById('subModal').style.display = 'flex';
        updateRadioStates(document.querySelector('input[name="meals_per_day"]:checked'));
    @else
        window.location.href = "{{ route('login') }}";
    @endauth
}

function closeSubscriptionModal() {
    document.getElementById('subModal').style.display = 'none';
}

function updateRadioStates(radio) {
    radio.closest('div').querySelectorAll('.pay-card').forEach(c => c.classList.remove('active'));
    radio.parentElement.classList.add('active');
}

function submitSubscriptionRequest(e) {
    e.preventDefault();
    const btn = document.getElementById('subSubmitBtn');
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Sending...';
    
    const formData = new FormData(e.target);
    
    fetch("{{ route('frontend.subscribe.request') }}", {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json'
        },
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if(data.success) {
            Swal.fire({
                title: 'Request Sent!',
                text: 'Your subscription request has been sent to the kitchen.',
                icon: 'success',
                confirmButtonColor: '#ff6b35'
            }).then(() => {
                closeSubscriptionModal();
                window.location.href = "{{ route('dashboard.customer') }}";
            });
        } else {
            Swal.fire('Error', data.message || 'Something went wrong.', 'error');
            btn.disabled = false;
            btn.textContent = 'Send Request';
        }
    })
    .catch(err => {
        console.error(err);
        Swal.fire('Error', 'Connection failed.', 'error');
        btn.disabled = false;
        btn.textContent = 'Send Request';
    });
}
</script>
@endpush
@endsection
