@extends('frontend.layouts.app')
@section('title', 'Subscribe to a Meal Plan')

@section('content')
<style>
/* Layout */
.sub-wrap { display:grid; grid-template-columns:1fr 370px; gap:28px; align-items:start; width: 100%; max-width: 100%; }
@media(max-width:1000px){ .sub-wrap { grid-template-columns:1fr !important; gap:20px; } .sub-wrap > div:last-child { position:static !important; width:100% !important; } }
@media(max-width:576px){ .kitchen-card { flex-direction:column; text-align:center; padding:15px; } .plan-card { padding:15px; } }

/* Headers & Glass */
.page-header { padding: calc(var(--nav-h) + 20px) 0 20px !important; }
.section { padding: 30px 0 !important; }
.glass-card { margin-bottom: 20px !important; border-radius: 20px; }

/* Selection Cards */
.kitchen-card {
    border: 2px solid var(--border-color); border-radius: 18px; padding: 22px;
    cursor: pointer; transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1); background: var(--bg-card);
    display: flex; align-items: center; gap: 18px; margin-bottom: 16px; position: relative;
    overflow: hidden;
}
.kitchen-card::before { content: ''; position: absolute; top: 0; left: 0; width: 4px; height: 100%; background: var(--primary); opacity: 0; transition: 0.3s; }
.kitchen-card:hover { border-color: var(--primary); transform: translateY(-3px); box-shadow: 0 10px 20px rgba(0,0,0,0.1); }
.kitchen-card.active { border-color: var(--primary); background: rgba(255,107,53,0.04); }
.kitchen-card.active::before { opacity: 1; }

.plan-card {
    border: 2px solid var(--border-color); border-radius: 16px; padding: 20px;
    cursor: pointer; transition: all 0.3s ease; margin-bottom: 12px;
    display: flex; flex-direction: column; gap: 6px; background: var(--bg-card2);
}
.plan-card:hover { border-color: var(--primary); transform: scale(1.02); }
.plan-card.active { border-color: var(--primary); background: rgba(255,107,53,0.06); box-shadow: inset 0 0 0 1px var(--primary); }
.plan-card input[type=radio] { display: none; }

/* Item Rows */
.item-row {
    display:flex; align-items:center; gap:14px; padding:15px 20px;
    border-bottom:1px solid var(--border-color); cursor:pointer;
    transition: all 0.2s;
}
.item-row:hover { background:rgba(255,107,53,0.05); }
.item-row:last-child { border-bottom: none; }
.item-row input[type=checkbox] { width:20px;height:20px;cursor:pointer;accent-color:var(--primary); }

/* Premium Payment Cards (Mirroring Cart) */
.pay-card {
    display:flex; align-items:center; gap:14px;
    padding:16px 20px; border-radius:16px;
    border:2px solid var(--border-color);
    cursor:pointer; transition: 0.25s ease;
    margin-bottom:12px; user-select:none;
    background: var(--bg-card);
}
.pay-card:hover { border-color: rgba(255,107,53,0.4); background: rgba(255,255,255,0.02); }
.pay-card.active { border-color: var(--primary); background: rgba(255,107,53,0.06); }
.pay-card-icon { 
    width:44px; height:44px; border-radius:12px; 
    display:flex; align-items:center; justify-content:center; 
    font-size:1.2rem; flex-shrink:0; 
}

.summary-sep { border:none; border-top:1px solid var(--border-color); margin:16px 0; }
</style>

<div class="page-header">
    <h1>Subscribe to a <span class="highlight">Kitchen Plan</span></h1>
    <p>Choose your favorite kitchen and subscribe to their exclusive meal plans</p>
</div>

<section class="section" style="padding-top:0">
<div class="container">

@if(session('message'))
<div class="info-box {{ session('alert-type') === 'success' ? 'info-success' : 'info-error' }} reveal" style="margin-bottom:24px">
    <i class="fas fa-{{ session('alert-type') === 'success' ? 'check-circle' : 'exclamation-circle' }}"></i>
    <span>{{ session('message') }}</span>
</div>
@endif

<form method="POST" action="{{ route('frontend.subscribe.store') }}" id="subForm">
@csrf
<input type="hidden" name="payment_method" id="subPayMethod" value="Wallet">

<div class="sub-wrap">

    {{-- ── LEFT Column: Configuration ── --}}
    <div>
        {{-- Step 1: Kitchen Selection --}}
        <div class="glass-card" style="padding:30px; margin-bottom:28px">
            <h3 style="margin:0 0 22px; font-size:1.2rem; display:flex; align-items:center; gap:12px">
                <span style="width:36px; height:36px; background:linear-gradient(135deg,var(--primary),var(--accent)); border-radius:12px; display:flex; align-items:center; justify-content:center; font-size:1rem; color:#fff">1</span>
                Choose a Kitchen
            </h3>
            
            <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 18px;">
                @foreach($kitchens as $k)
                <div class="kitchen-card" id="kitchen-{{ $k->KitchenOwnerID }}" 
                     data-name="{{ $k->KitchenName }}"
                     onclick="selectKitchen({{ $k->KitchenOwnerID }})">
                    <div style="width:54px; height:54px; border-radius:14px; background:rgba(255,107,53,0.1); border:1px solid rgba(255,107,53,0.2); display:flex; align-items:center; justify-content:center; font-size:1.6rem">👨‍🍳</div>
                    <div style="flex:1">
                        <div style="font-weight:800; font-size:1.1rem; color:var(--text-primary)">{{ $k->KitchenName }}</div>
                        <p style="font-size:0.8rem; color:var(--text-muted); margin-top:2px">{{ $k->plans->count() }} specialized plans</p>
                    </div>
                </div>
                @endforeach
            </div>
        </div>

        {{-- Step 2: Plans & Items --}}
        @foreach($kitchens as $k)
        <div class="glass-card kitchen-plans-container" id="plans-for-{{ $k->KitchenOwnerID }}" style="padding:30px; margin-bottom:28px; display:none">
            <h3 style="margin:0 0 22px; font-size:1.2rem; display:flex; align-items:center; gap:12px">
                <span style="width:36px; height:36px; background:linear-gradient(135deg,#60a5fa,#a78bfa); border-radius:12px; display:flex; align-items:center; justify-content:center; font-size:1rem; color:#fff">2</span>
                Available Plans for {{ $k->KitchenName }}
            </h3>
            
            <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 18px;">
                @foreach($k->plans as $plan)
                <label class="plan-card" id="plan-{{ $plan->KitchenPlanID }}">
                    <input type="radio" name="plan_id" value="{{ $plan->KitchenPlanID }}" 
                           onchange="selectPlan(this, '{{ addslashes($plan->Title) }}', {{ $plan->Price }}, '{{ $plan->PlanTime }}')">
                    <div style="display:flex; justify-content:space-between; align-items:start">
                        <div style="font-weight:800; font-size:1.1rem">{{ $plan->Title }}</div>
                        <div style="background:rgba(255,107,53,0.15); color:var(--primary); padding:4px 12px; border-radius:20px; font-size:0.75rem; font-weight:800; text-transform:uppercase">{{ $plan->PlanTime }}</div>
                    </div>
                    <div style="font-size:0.85rem; color:var(--text-muted); margin:12px 0; line-height:1.5">{{ $plan->Description ?? 'Bespoke meal variety delivered.' }}</div>
                    <div style="margin-top:auto; padding-top:14px; border-top:1px solid var(--border-color); display:flex; justify-content:space-between; align-items:center">
                        <span style="font-size:0.9rem; color:var(--text-muted)">Plan Price</span>
                        <span style="font-weight:900; color:var(--primary); font-size:1.2rem">{{ number_format($plan->Price) }} <small style="font-size:0.7rem; font-weight:400; color:var(--text-muted)">EGP</small></span>
                    </div>
                </label>
                @endforeach
            </div>

        </div>
        @endforeach

        {{-- Step 4: Finalize Date --}}
        <div class="glass-card" style="padding:30px; display:none" id="datePickerStep">
            <h3 style="margin:0 0 20px; font-size:1.2rem; display:flex; align-items:center; gap:12px">
                <span style="width:36px; height:36px; background:linear-gradient(135deg,#4ade80,#22d3ee); border-radius:12px; display:flex; align-items:center; justify-content:center; font-size:1rem; color:#fff">3</span>
                Select Start Date
            </h3>
            <div style="max-width:320px; position:relative">
                <i class="fas fa-calendar-alt" style="position:absolute; left:16px; top:50%; transform:translateY(-50%); color:var(--primary)"></i>
                <input type="date" name="start_date" required class="form-control" style="padding-left:45px; height:50px; border-radius:12px"
                    min="{{ date('Y-m-d', strtotime('+1 day')) }}" value="{{ date('Y-m-d', strtotime('+1 day')) }}">
            </div>
        </div>
    </div>

    {{-- ── RIGHT Column: Order Summary (Mirroring Cart) ── --}}
    <div style="position:sticky; top:90px">
        <div class="glass-card" style="padding:28px">
            <h3 style="margin-bottom:24px; font-size:1.1rem; display:flex; align-items:center; gap:12px">
                <span style="width:36px; height:36px; background:linear-gradient(135deg,var(--primary),var(--accent)); border-radius:10px; display:flex; align-items:center; justify-content:center; font-size:0.9rem"><i class="fas fa-receipt" style="color:#fff"></i></span>
                Order Summary
            </h3>

            <!-- Details -->
            <div style="display:flex; justify-content:space-between; padding:10px 0; color:var(--text-secondary); font-size:0.95rem">
                <span>Selected Kitchen</span>
                <span id="sumKitchen" style="font-weight:700; color:var(--text-primary)">None</span>
            </div>
            <div style="display:flex; justify-content:space-between; padding:10px 0; color:var(--text-secondary); font-size:0.95rem">
                <span>Subscription Plan</span>
                <span id="sumPlan" style="font-weight:700; color:var(--text-primary)">None</span>
            </div>

            <hr class="summary-sep">

            <div style="display:flex; justify-content:space-between; padding:4px 0; font-size:1.2rem; font-weight:900; letter-spacing:-0.5px">
                <span>Total Due</span>
                <span id="sumTotal" style="color:var(--primary)">0.00 EGP</span>
            </div>

            <div style="margin:24px 0 16px">
                <label class="form-label" style="font-weight:700; font-size:0.9rem; color:var(--text-primary); margin-bottom:12px; display:block">
                    <i class="fas fa-credit-card" style="color:var(--primary)"></i> Payment Method
                </label>
                
                <!-- Wallet Option -->
                <div class="pay-card active" id="pc-Wallet" onclick="selectSubPay('Wallet')">
                    <div class="pay-card-icon" style="background:rgba(255,107,53,0.1)">
                        <i class="fas fa-wallet" style="color:var(--primary)"></i>
                    </div>
                    <div style="flex:1">
                        <div style="font-weight:700; font-size:0.95rem; color:var(--text-primary)">BiteHub Wallet</div>
                        <div style="font-size:0.75rem; color:var(--text-muted)">Balance: {{ number_format($walletBalance ?? 0, 2) }} EGP</div>
                    </div>
                </div>

                <!-- Stripe Option -->
                <div class="pay-card" id="pc-Card" onclick="selectSubPay('Card')">
                    <div class="pay-card-icon" style="background:rgba(99,91,255,0.1)">
                        <i class="fab fa-stripe-s" style="color:#635bff"></i>
                    </div>
                    <div style="flex:1">
                        <div style="font-weight:700; font-size:0.95rem; color:var(--text-primary)">Credit / Debit Card</div>
                        <div style="font-size:0.75rem; color:var(--text-muted)">Secured by Stripe</div>
                    </div>
                </div>
            </div>

            <!-- Contextual Messages -->
            <div id="payAlert" class="info-box info-error" style="display:none; padding:12px; font-size:0.85rem; margin-bottom:16px">
                <i class="fas fa-exclamation-circle"></i>
                <span id="payAlertTxt"></span>
            </div>

            <div id="stripeInfo" class="info-box info-blue" style="display:none; padding:12px; font-size:0.85rem; margin-bottom:16px; background:rgba(99,91,255,0.08); color:#635bff; border-color:rgba(99,91,255,0.2)">
                <i class="fab fa-stripe"></i>
                <span>You'll be redirected to Stripe for secure payment.</span>
            </div>

            <!-- Action Buttons -->
            <button type="submit" id="subBtn" class="btn btn-primary w-100 py-3 mt-2 fw-bold shadow-lg" disabled>
                <i class="fas fa-check-circle me-2"></i> Confirm Subscription
            </button>
            
            <button type="button" id="stripeBtn" onclick="payWithStripe()" class="btn w-100 py-3 mt-2 fw-bold shadow-lg" style="background:#635bff; color:#fff; display:none; border:none">
                <i class="fab fa-stripe me-2"></i> Pay with Stripe
            </button>
            
            <p style="text-align:center; font-size:0.75rem; color:var(--text-muted); margin-top:16px">
                By subscribing, you agree to our <a href="#" style="color:var(--primary)">Terms of Service</a>.
            </p>
        </div>
    </div>
</div>
</form>

</div>
</section>

@push('scripts')
<script>
const WALLET = {{ $walletBalance ?? 0 }};
let subPayMethod = 'Wallet';
let currentPrice = 0;
let selectedKitchenName = 'None';
let selectedPlanName = 'None';

function selectKitchen(id) {
    const card = document.getElementById('kitchen-' + id);
    if(!card) return;

    // Visual Toggle
    document.querySelectorAll('.kitchen-card').forEach(c => c.classList.remove('active'));
    card.classList.add('active');
    
    // Dynamic Step 2
    document.querySelectorAll('.kitchen-plans-container').forEach(c => c.style.display = 'none');
    const plansDiv = document.getElementById('plans-for-' + id);
    if(plansDiv) plansDiv.style.display = 'block';
    
    // Summary Update
    selectedKitchenName = card.getAttribute('data-name');
    document.getElementById('sumKitchen').textContent = selectedKitchenName;
    
    // Reset Plan Selection if kitchen changed
    selectedPlanName = 'None';
    currentPrice = 0;
    document.getElementById('sumPlan').textContent = 'None';
    document.getElementById('sumTotal').textContent = '0.00 EGP';
    document.getElementById('datePickerStep').style.display = 'none';
    
    refreshPayAlert(0);
}

function selectPlan(radio, title, price, time) {
    document.querySelectorAll('.plan-card').forEach(c => c.classList.remove('active'));
    const card = radio.closest('.plan-card');
    if(card) card.classList.add('active');
    
    currentPrice = price;
    selectedPlanName = title + ' (' + time + ')';
    
    document.getElementById('sumPlan').textContent = selectedPlanName;
    document.getElementById('sumTotal').textContent = price.toLocaleString(undefined, {minimumFractionDigits:2}) + ' EGP';
    document.getElementById('datePickerStep').style.display = 'block';
    
    refreshPayAlert(price);
}

function selectSubPay(val) {
    subPayMethod = val;
    document.querySelectorAll('.pay-card').forEach(c => c.classList.remove('active'));
    document.getElementById('pc-' + val).classList.add('active');
    refreshPayAlert(currentPrice);
}

function refreshPayAlert(total) {
    const errorBox = document.getElementById('payAlert');
    const errorTxt = document.getElementById('payAlertTxt');
    const walletBtn = document.getElementById('subBtn');
    const stripeBtn = document.getElementById('stripeBtn');
    const stripeBox = document.getElementById('stripeInfo');
    
    // Hide everything by default
    errorBox.style.display = 'none';
    stripeBox.style.display = 'none';
    walletBtn.style.display = 'none';
    stripeBtn.style.display = 'none';

    if (total <= 0) {
        walletBtn.style.display = 'block';
        walletBtn.disabled = true;
        return;
    }

    if (subPayMethod === 'Wallet') {
        walletBtn.style.display = 'block';
        if (WALLET >= total) {
            walletBtn.disabled = false;
        } else {
            errorBox.style.display = 'flex';
            errorTxt.textContent = `Insufficient balance (${WALLET.toFixed(2)} EGP). Need ${total.toFixed(2)} EGP.`;
            walletBtn.disabled = true;
        }
    } else {
        stripeBox.style.display = 'flex';
        stripeBtn.style.display = 'block';
    }
}

function payWithStripe() {
    const form = document.getElementById('subForm');
    form.action = "{{ route('frontend.stripe.subscribe') }}";
    form.submit();
}

function updateSummary() {
    // Current total is just plan price (no item extras yet in logic)
}

// Deep Linking / Auto Selection
document.addEventListener('DOMContentLoaded', function() {
    const preKitchen = "{{ $kitchenId }}";
    const prePlan    = "{{ $planId }}";
    
    if (preKitchen) {
        selectKitchen(preKitchen);
        if (prePlan) {
            const planRadio = document.querySelector(`input[name="plan_id"][value="${prePlan}"]`);
            if (planRadio) {
                planRadio.checked = true;
                // Dispatch click to trigger both visual and data logic
                planRadio.click();
            }
        }
    }
});
</script>
@endpush
@endsection
