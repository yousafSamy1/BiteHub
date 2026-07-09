<!-- Top Up Modal -->
<div class="modal fade" id="topupModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content border-0 shadow-lg" style="background: #1a1d21; border-radius: 24px;">
            <!-- Header -->
            <div class="modal-header border-0 p-4 pb-0">
                <h5 class="modal-title fw-bold text-white fs-4"><i class="fas fa-wallet me-2 text-success"></i> Top Up Wallet</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body p-4">
                <p class="text-white-50 small mb-4">Enter the amount you wish to add to your balance using PayMob.</p>
                
                <form action="{{ route('wallet.paymob.topup') }}" method="POST">
                    @csrf
                    <div class="mb-4">
                        <label class="form-label text-white-50 small fw-bold text-uppercase">Recharge Amount</label>
                        <div class="input-group input-group-lg">
                            <span class="input-group-text bg-dark border-0 text-success fw-bold" style="border-radius: 12px 0 0 12px;">EGP</span>
                            <input type="number" name="amount" class="form-control bg-dark text-white border-0 py-3" placeholder="Min 50" min="50" required style="border-radius: 0 12px 12px 0;">
                        </div>
                    </div>

                    <button type="submit" class="btn btn-success w-100 py-3 fw-bold rounded-pill shadow-sm">
                        Proceed to Payment <i class="fas fa-arrow-right ms-2"></i>
                    </button>
                    
                    <div class="mt-4 text-center">
                        <img src="https://paymob.com/images/paymob-logo.svg" alt="PayMob" style="height: 20px; opacity: 0.6; filter: grayscale(1) brightness(2);">
                        <p class="text-white-50 small mt-2 fw-light" style="font-size: 0.7rem;">Secure multi-payment gateway</p>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
