<!-- Withdrawal Modal -->
<div class="modal fade" id="withdrawModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="background: #1a1d21; border-radius: 24px; overflow: hidden;">
            <!-- Header -->
            <div class="modal-header border-0 p-4 pb-0">
                <h5 class="modal-title fw-bold text-white fs-4"><i class="fas fa-hand-holding-usd me-2 text-primary"></i> Withdraw Funds</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body p-4">
                <!-- Step 1: Amount -->
                <div id="wd-step-1">
                    <p class="text-white-50 mb-4">Enter the amount you wish to withdraw to your preferred account.</p>
                    <div class="amount-input-group mb-4">
                        <label class="form-label text-white-50 small fw-bold text-uppercase">Withdrawal Amount</label>
                        <div class="input-group input-group-lg">
                            <span class="input-group-text bg-dark border-0 text-primary fw-bold" style="border-radius: 12px 0 0 12px;">EGP</span>
                            <input type="number" id="wd-amount" class="form-control bg-dark text-white border-0 py-3" placeholder="Min 50" min="50" style="border-radius: 0 12px 12px 0;">
                        </div>
                        <div id="wd-amount-error" class="text-danger small mt-2 d-none">Please enter a valid amount (Min 50).</div>
                        <div class="mt-2 d-flex justify-content-between small">
                            <span class="text-white-50">Available: <span class="text-white fw-bold">{{ number_format(Auth::user()->Wallet_balance, 2) }} EGP</span></span>
                            <span class="text-warning-soft text-warning px-2 py-0 rounded" style="background: rgba(245, 158, 11, 0.1);">1% Fee Applied</span>
                        </div>
                    </div>
                    <button type="button" class="btn btn-primary w-100 py-3 fw-bold rounded-pill" onclick="wdNextStep(2)">
                        Choose Method <i class="fas fa-arrow-right ms-2"></i>
                    </button>
                </div>

                <!-- Step 2: Method Selection -->
                <div id="wd-step-2" class="d-none">
                    <p class="text-white-50 mb-3">Select your payout method</p>
                    <div class="d-flex flex-column gap-3 mb-4">
                        <div class="wd-method-card" onclick="wdSelectMethod('Bank')">
                            <i class="fas fa-university text-primary fs-4"></i>
                            <div class="flex-grow-1">
                                <div class="fw-bold text-white">Bank Account</div>
                                <div class="small text-white-50">Transfer to any local bank</div>
                            </div>
                            <i class="fas fa-chevron-right text-white-50"></i>
                        </div>
                        <div class="wd-method-card" onclick="wdSelectMethod('VodafoneCash')">
                            <i class="fas fa-mobile-alt text-danger fs-4"></i>
                            <div class="flex-grow-1">
                                <div class="fw-bold text-white">Vodafone Cash</div>
                                <div class="small text-white-50">Instant mobile wallet payout</div>
                            </div>
                            <i class="fas fa-chevron-right text-white-50"></i>
                        </div>
                        <div class="wd-method-card" onclick="wdSelectMethod('InstaPay')">
                            <i class="fas fa-bolt text-info fs-4"></i>
                            <div class="flex-grow-1">
                                <div class="fw-bold text-white">InstaPay</div>
                                <div class="small text-white-50">Real-time payment address</div>
                            </div>
                            <i class="fas fa-chevron-right text-white-50"></i>
                        </div>
                    </div>
                    <button type="button" class="btn btn-link text-white-50 w-100 py-0" onclick="wdNextStep(1)">Back</button>
                </div>

                <!-- Step 3: Account Selection -->
                <div id="wd-step-3" class="d-none">
                    <p class="text-white-50 mb-3">Select a saved <span id="wd-selected-method-label"></span> account</p>
                    <div id="wd-methods-list" class="d-flex flex-column gap-2 mb-4">
                        <!-- Populated by AJAX -->
                    </div>
                    <div class="mb-4">
                        <a href="{{ route('withdraw.methods.index') }}" target="_blank" class="btn btn-outline-light btn-sm w-100 rounded-pill py-2 border-dashed">
                            <i class="fas fa-plus-circle me-1"></i> Add New Account
                        </a>
                    </div>
                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-dark flex-grow-1 py-2 fw-bold rounded-pill" onclick="wdNextStep(2)">Back</button>
                        <button type="button" id="wd-final-next" class="btn btn-primary flex-grow-1 py-2 fw-bold rounded-pill d-none" onclick="wdNextStep(4)">Confirm Details</button>
                    </div>
                </div>

                <!-- Step 4: Confirmation -->
                <div id="wd-step-4" class="d-none">
                    <div class="text-center mb-4">
                        <div class="display-5 fw-bold text-white mb-1"><span id="wd-final-total"></span> <small class="fs-6 text-white-50">EGP</small></div>
                        <div class="text-white-50 small">Withdrawal Request Total</div>
                    </div>
                    <div class="bg-dark p-3 rounded-4 mb-4">
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-white-50 small">Net Payout (99%)</span>
                            <span class="text-success small fw-bold"><span id="wd-final-net"></span> EGP</span>
                        </div>
                        <div class="d-flex justify-content-between mb-2 border-top border-secondary pt-2 mt-2">
                            <span class="text-white-50 small">Processing Fee (1%)</span>
                            <span class="text-warning small fw-bold"><span id="wd-final-fee"></span> EGP</span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-white-50 small">Payout Method</span>
                            <span id="wd-confirm-method" class="text-white small fw-bold"></span>
                        </div>
                        <div class="d-flex justify-content-between">
                            <span class="text-white-50 small">Account Details</span>
                            <span id="wd-confirm-details" class="text-white small fw-bold text-end"></span>
                        </div>
                    </div>
                    <form action="{{ route('withdraw.store') }}" method="POST">
                        @csrf
                        <input type="hidden" name="amount" id="wd-form-amount">
                        <input type="hidden" name="method_id" id="wd-form-method-id">
                        <button type="submit" class="btn btn-success w-100 py-3 fw-bold rounded-pill">
                            Submit Request <i class="fas fa-check-circle ms-1"></i>
                        </button>
                    </form>
                    <button type="button" class="btn btn-link text-white-50 w-100 mt-2 py-0" onclick="wdNextStep(3)">Back</button>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.wd-method-card {
    display: flex;
    align-items: center;
    gap: 15px;
    padding: 16px;
    background: rgba(255, 255, 255, 0.05);
    border: 1px solid rgba(255, 255, 255, 0.1);
    border-radius: 16px;
    cursor: pointer;
    transition: all 0.2s ease;
}
.wd-method-card:hover {
    background: rgba(255, 255, 255, 0.08);
    border-color: var(--primary);
    transform: translateY(-2px);
}
.wd-account-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 12px 16px;
    background: rgba(255, 255, 255, 0.03);
    border: 1px solid rgba(255, 255, 255, 0.05);
    border-radius: 12px;
    cursor: pointer;
}
.wd-account-item.active {
    border-color: #10b981;
    background: rgba(16, 185, 129, 0.05);
}
.border-dashed { border-style: dashed !important; }
</style>

<script>
let wdData = {
    amount: 0,
    type: '',
    methodId: null,
    methodDetails: ''
};

function wdNextStep(step) {
    if (step === 2) {
        const amt = document.getElementById('wd-amount').value;
        const max = {{ Auth::user()->Wallet_balance }};
        if (amt < 50 || amt > max) {
            document.getElementById('wd-amount-error').classList.remove('d-none');
            document.getElementById('wd-amount-error').textContent = amt > max ? 'Amount exceeds balance.' : 'Min 50 EGP.';
            return;
        }
        wdData.amount = amt;
        document.getElementById('wd-amount-error').classList.add('d-none');
    }

    if (step === 4) {
        const total = parseFloat(wdData.amount);
        const fee = total * 0.01;
        const net = total - fee;

        document.getElementById('wd-final-total').textContent = total.toFixed(2);
        document.getElementById('wd-final-fee').textContent = fee.toFixed(2);
        document.getElementById('wd-final-net').textContent = net.toFixed(2);

        document.getElementById('wd-form-amount').value = total;
        document.getElementById('wd-form-method-id').value = wdData.methodId;
        document.getElementById('wd-confirm-method').textContent = wdData.type;
        document.getElementById('wd-confirm-details').textContent = wdData.methodDetails;
    }

    document.querySelectorAll('[id^="wd-step-"]').forEach(el => el.classList.add('d-none'));
    document.getElementById(`wd-step-${step}`).classList.remove('d-none');
}

function wdSelectMethod(type) {
    wdData.type = type;
    document.getElementById('wd-selected-method-label').textContent = type;
    wdLoadMethods(type);
    wdNextStep(3);
}

function wdLoadMethods(type) {
    const list = document.getElementById('wd-methods-list');
    list.innerHTML = '<div class="text-center p-3 text-white-50"><i class="fas fa-spinner fa-spin"></i> Loading accounts...</div>';
    
    fetch('{{ route("api.withdraw.methods") }}')
        .then(res => res.json())
        .then(data => {
            const filtered = data.filter(m => m.Type === type);
            list.innerHTML = '';
            if (filtered.length === 0) {
                list.innerHTML = '<div class="text-center p-3 text-white-50 bg-dark rounded-4 mb-2 small">No saved ' + type + ' accounts found.</div>';
                document.getElementById('wd-final-next').classList.add('d-none');
            } else {
                filtered.forEach(m => {
                    let desc = '';
                    if (type === 'Bank') desc = m.Details.bank_name + ' - ' + m.Details.account_number;
                    else desc = m.Details.phone || m.Details.address;

                    const div = document.createElement('div');
                    div.className = 'wd-account-item';
                    div.innerHTML = `<div><div class="text-white small">${desc}</div></div><i class="fas fa-check-circle text-success d-none"></i>`;
                    div.onclick = () => {
                        document.querySelectorAll('.wd-account-item').forEach(i => i.classList.remove('active'));
                        div.classList.add('active');
                        wdData.methodId = m.id;
                        wdData.methodDetails = desc;
                        document.getElementById('wd-final-next').classList.remove('d-none');
                    };
                    list.appendChild(div);
                });
            }
        });
}
</script>
