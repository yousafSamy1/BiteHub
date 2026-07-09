@extends('frontend.layouts.app')
@section('title', 'Pay Subscription')

@section('content')
<style>
/* Layout */
.pay-wrap { display:grid; grid-template-columns:1fr 400px; gap:28px; align-items:start; width: 100%; max-width: 1000px; margin: 0 auto; }
@media(max-width:900px){ .pay-wrap { grid-template-columns:1fr !important; gap:20px; } .pay-wrap > div:last-child { position:static !important; width:100% !important; } }

/* Headers & Glass */
.page-header { padding: calc(var(--nav-h) + 20px) 0 20px !important; }
.section { padding: 30px 0 !important; }
.glass-card { margin-bottom: 20px !important; border-radius: 20px; background: var(--bg-card); border:1px solid var(--border-color); }

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
.summary-sep { border:none; border-top:1px dashed var(--border-color); margin:16px 0; }
</style>

<div class="page-header text-center">
    <h1>Complete <span class="highlight">Payment</span></h1>
    <p>Pay your subscription plan securely.</p>
</div>

<section class="section" style="padding-top:0">
<div class="container">

@if(session('message'))
<div class="info-box {{ session('alert-type') === 'success' ? 'info-success' : 'info-error' }} reveal" style="margin-bottom:24px; max-width: 1000px; margin-left: auto; margin-right: auto;">
    <i class="fas fa-{{ session('alert-type') === 'success' ? 'check-circle' : 'exclamation-circle' }}"></i>
    <span>{{ session('message') }}</span>
</div>
@endif

<form method="POST" action="{{ route('frontend.subscription.payment.process', $subscription->SubscriptionID) }}" id="payForm">
@csrf
<input type="hidden" name="payment_method" id="subPayMethod" value="Wallet">
<input type="hidden" name="method" id="actualMethod" value="Wallet">
<input type="hidden" name="amount" value="{{ $amount }}">

<div class="pay-wrap">
    {{-- ── LEFT Column: Order Details ── --}}
    <div>
        <div class="glass-card" style="padding:30px; margin-bottom:28px">
            <h3 style="margin:0 0 22px; font-size:1.2rem; display:flex; align-items:center; gap:12px">
                <span style="width:36px; height:36px; background:linear-gradient(135deg,var(--primary),var(--accent)); border-radius:12px; display:flex; align-items:center; justify-content:center; font-size:1rem; color:#fff"><i class="fas fa-receipt"></i></span>
                Subscription Invoice
            </h3>
            
            <div style="background: var(--bg-card2); border-radius: 16px; padding: 20px; border: 1px solid var(--border-color);">
                <div style="display:flex; justify-content:space-between; margin-bottom: 12px;">
                    <span style="color:var(--text-muted)">Plan Kitchen</span>
                    <span style="font-weight:700">{{ $subscription->kitchen->KitchenName ?? 'N/A' }}</span>
                </div>
                <div style="display:flex; justify-content:space-between; margin-bottom: 12px;">
                    <span style="color:var(--text-muted)">Current Status</span>
                    <span style="font-weight:700">{{ $subscription->Status }}</span>
                </div>
                <div style="display:flex; justify-content:space-between; margin-bottom: 12px;">
                    <span style="color:var(--text-muted)">Plan Cost</span>
                    <span style="font-weight:700">{{ number_format($subscription->Price, 2) }} EGP</span>
                </div>
                <div style="display:flex; justify-content:space-between; margin-bottom: 12px;">
                    <span style="color:var(--text-muted)">Delivery Charge</span>
                    <span style="font-weight:700">{{ number_format($subscription->DeliveryCharge, 2) }} EGP</span>
                </div>
                
                <hr class="summary-sep">
                
                <div style="display:flex; justify-content:space-between; margin-bottom: 12px;">
                    <span style="color:var(--text-muted)">Already Paid</span>
                    <span style="font-weight:700; color:var(--success)">- {{ number_format($subscription->PaidAmount, 2) }} EGP</span>
                </div>
                <div style="display:flex; justify-content:space-between; font-size:1.2rem; font-weight:900;">
                    <span>Amount Due to Pay</span>
                    <span style="color:var(--primary)">{{ number_format($amount, 2) }} EGP</span>
                </div>
            </div>
        </div>
    </div>

    {{-- ── RIGHT Column: Payment Methods ── --}}
    <div style="position:sticky; top:90px">
        <div class="glass-card" style="padding:28px">
            <h3 style="margin-bottom:24px; font-size:1.1rem; display:flex; align-items:center; gap:12px">
                <span style="width:36px; height:36px; background:linear-gradient(135deg,var(--primary),var(--accent)); border-radius:10px; display:flex; align-items:center; justify-content:center; font-size:0.9rem"><i class="fas fa-credit-card" style="color:#fff"></i></span>
                Payment Options
            </h3>

            <div style="margin:10px 0 16px">
                
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

                <!-- PayMob Card Option -->
                <div class="pay-card" id="pc-Card" onclick="selectSubPay('Card')">
                    <div class="pay-card-icon" style="background:rgba(96,165,250,0.1)">
                        <i class="fas fa-credit-card" style="color:var(--info)"></i>
                    </div>
                    <div style="flex:1">
                        <div style="font-weight:700; font-size:0.95rem; color:var(--text-primary)">Credit / Debit Card</div>
                        <div style="font-size:0.75rem; color:var(--text-muted)">Secured via PayMob</div>
                    </div>
                </div>
                
                <!-- Cash Option -->
                <div class="pay-card" id="pc-Cash" onclick="selectSubPay('Cash')">
                    <div class="pay-card-icon" style="background:rgba(74,222,128,0.1)">
                        <i class="fas fa-money-bill-wave" style="color:var(--success)"></i>
                    </div>
                    <div style="flex:1">
                        <div style="font-weight:700; font-size:0.95rem; color:var(--text-primary)">Cash on Delivery</div>
                        <div style="font-size:0.75rem; color:var(--text-muted)">Pay on first dropoff</div>
                    </div>
                </div>
            </div>

            <!-- Contextual Messages -->
            <div id="payAlert" class="info-box info-error" style="display:none; padding:12px; font-size:0.85rem; margin-bottom:16px">
                <i class="fas fa-exclamation-circle"></i>
                <span id="payAlertTxt"></span>
            </div>
            
            <div id="stripeInfo" class="info-box info-blue" style="display:none; padding:12px; font-size:0.85rem; margin-bottom:16px; background:rgba(96,165,250,0.08); color:var(--info); border-color:rgba(96,165,250,0.2)">
                <i class="fas fa-lock"></i>
                <span style="font-weight:700">You'll be redirected to PayMob for secure payment.</span>
            </div>

            <!-- Action Buttons -->
            <button type="submit" id="subBtn" class="btn btn-primary w-100 py-3 mt-2 fw-bold shadow-lg">
                <i class="fas fa-check-circle me-2"></i> Confirm Payment of {{ number_format($amount, 2) }} EGP
            </button>
            
            <button type="button" id="paymobBtn" onclick="checkoutPaymob()" class="btn btn-primary w-100 py-3 mt-2 fw-bold shadow-lg" style="display:none; border:none">
                <i class="fas fa-lock me-2"></i> Pay with Card (PayMob)
            </button>
            
        </div>
    </div>
</div>
</form>

</div>
</section>

@push('scripts')
<script>
const WALLET = {{ $walletBalance ?? 0 }};
const TOTAL = {{ $amount ?? 0 }};
let subPayMethod = 'Wallet';

function selectSubPay(val) {
    subPayMethod = val;
    document.getElementById('actualMethod').value = val;
    
    document.querySelectorAll('.pay-card').forEach(c => c.classList.remove('active'));
    document.getElementById('pc-' + val).classList.add('active');
    refreshPayAlert();
}

function refreshPayAlert() {
    const errorBox = document.getElementById('payAlert');
    const errorTxt = document.getElementById('payAlertTxt');
    const payBtn = document.getElementById('subBtn');
    const stripeBtn = document.getElementById('stripeBtn');
    const stripeBox = document.getElementById('stripeInfo');
    
    errorBox.style.display = 'none';
    stripeBox.style.display = 'none';
    payBtn.style.display = 'none';
    document.getElementById('paymobBtn').style.display = 'none';

    if (TOTAL <= 0) {
        payBtn.style.display = 'block';
        payBtn.disabled = true;
        return;
    }

    if (subPayMethod === 'Wallet') {
        payBtn.style.display = 'block';
        payBtn.disabled = false;
        if (WALLET < TOTAL) {
            errorBox.style.display = 'flex';
            errorTxt.textContent = `Insufficient balance (${WALLET.toFixed(2)} EGP). Need ${TOTAL.toFixed(2)} EGP.`;
            payBtn.disabled = true;
        }
    } else if (subPayMethod === 'Card') {
        stripeBox.style.display = 'flex';
        document.getElementById('paymobBtn').style.display = 'block';
    } else {
        payBtn.style.display = 'block';
        payBtn.disabled = false;
    }
}

function checkoutPaymob() {
    const form = document.getElementById('payForm');
    form.action = "{{ route('frontend.paymob.subscription_checkout', $subscription->SubscriptionID) }}";
    form.submit();
}

document.addEventListener('DOMContentLoaded', refreshPayAlert);
</script>
@endpush
@endsection
