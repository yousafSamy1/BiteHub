@extends('admin.admin_dashboard')

@section('admin')
<div class="page-content">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="text-white fw-bold"><i class="fas fa-wallet me-2 text-primary"></i> Withdrawal Methods</h3>
        <button class="btn btn-primary rounded-pill px-4" data-bs-toggle="modal" data-bs-target="#addMethodModal">
            <i class="fas fa-plus me-1"></i> Add New Account
        </button>
    </div>

    <div class="row">
        @forelse($methods as $method)
        <div class="col-md-4 mb-4">
            <div class="dark-card card p-4 position-relative overflow-hidden">
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <div class="method-icon">
                        @if($method->Type === 'Bank') <i class="fas fa-university text-primary fs-3"></i>
                        @elseif($method->Type === 'VodafoneCash') <i class="fas fa-mobile-alt text-danger fs-3"></i>
                        @else <i class="fas fa-bolt text-info fs-3"></i> @endif
                    </div>
                    <form action="{{ route('withdraw.methods.delete', $method->id) }}" method="POST">
                        @csrf @method('DELETE')
                        <button type="submit" class="btn btn-link text-danger p-0" onclick="return confirm('Remove this account?')">
                            <i class="fas fa-trash-alt"></i>
                        </button>
                    </form>
                </div>
                <h5 class="text-white fw-bold mb-1">{{ $method->Type }}</h5>
                <p class="text-white-50 small mb-3">
                    @if($method->Type === 'Bank')
                        {{ $method->Details['bank_name'] }}<br>
                        <span class="text-white fw-bold">{{ $method->Details['account_number'] }}</span>
                    @else
                        <span class="text-white fw-bold">{{ $method->Details['phone'] ?? $method->Details['address'] }}</span>
                    @endif
                </p>
                @if($method->IsPrimary)
                    <span class="badge bg-success-soft text-success rounded-pill px-3 py-1 small" style="background: rgba(16, 185, 129, 0.1);">Primary</span>
                @endif
            </div>
        </div>
        @empty
        <div class="col-12 text-center py-5">
            <div class="text-white-50 mb-3"><i class="fas fa-folder-open fa-3x"></i></div>
            <h5 class="text-white">No withdrawal methods found</h5>
            <p class="text-white-50">Add a bank account or mobile wallet to start withdrawing your earnings.</p>
        </div>
        @endforelse
    </div>

    <!-- Recent Withdrawal Requests -->
    <div class="row mt-5">
        <div class="col-12">
            <h4 class="text-white fw-bold mb-4"><i class="fas fa-history me-2 text-info"></i> Recent Withdrawal Requests</h4>
            <div class="dark-card card overflow-hidden border-0 shadow-sm" style="background: rgba(30, 41, 59, 0.5); border-radius: 20px;">
                <div class="table-responsive">
                    <table class="table table-hover mb-0" style="color: #cbd5e1;">
                        <thead style="background: rgba(255,255,255,0.03);">
                            <tr>
                                <th class="border-0 py-3 ps-4">Date</th>
                                <th class="border-0 py-3">Requested</th>
                                <th class="border-0 py-3">Net to Receive</th>
                                <th class="border-0 py-3">Method</th>
                                <th class="border-0 py-3 text-center">Status</th>
                                <th class="border-0 py-3 pe-4">Notes</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($requests as $req)
                            <tr style="border-bottom: 1px solid rgba(255,255,255,0.05); transition: all 0.2s ease;">
                                <td class="py-3 ps-4 small font-monospace">{{ $req->created_at->format('M d, Y h:i A') }}</td>
                                <td class="py-3 fw-bold text-white">{{ number_format($req->Amount, 2) }} <small class="text-white-50">EGP</small></td>
                                <td class="py-3 fw-bold text-success">{{ number_format($req->NetAmount, 2) }} <small class="text-white-50">EGP</small></td>
                                <td class="py-3">
                                    <div class="d-flex align-items-center gap-2">
                                        @if($req->Method === 'Bank') <i class="fas fa-university text-primary small"></i>
                                        @elseif($req->Method === 'VodafoneCash') <i class="fas fa-mobile-alt text-danger small"></i>
                                        @else <i class="fas fa-bolt text-info small"></i> @endif
                                        <span class="small">{{ $req->Method }}</span>
                                    </div>
                                </td>
                                <td class="py-3 text-center">
                                    @if($req->Status === 'Pending')
                                        <span class="badge rounded-pill px-3 py-1" style="background: rgba(245, 158, 11, 0.1); color: #f59e0b; border: 1px solid rgba(245, 158, 11, 0.3);">Pending</span>
                                    @elseif($req->Status === 'Approved')
                                        <span class="badge rounded-pill px-3 py-1" style="background: rgba(16, 185, 129, 0.1); color: #10b981; border: 1px solid rgba(16, 185, 129, 0.3);">Approved</span>
                                    @else
                                        <span class="badge rounded-pill px-3 py-1" style="background: rgba(239, 68, 68, 0.1); color: #ef4444; border: 1px solid rgba(239, 68, 68, 0.3);">Rejected</span>
                                    @endif
                                </td>
                                <td class="py-3 pe-4 small text-white-50">{{ $req->AdminNotes ?: '-' }}</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="py-5 text-center text-white-50">
                                    <i class="fas fa-history fa-2x mb-3 d-block opacity-25"></i>
                                    No withdrawal requests found.
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add Method Modal -->
<div class="modal fade" id="addMethodModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="background: #1a1d21; border-radius: 24px;">
            <div class="modal-header border-0 p-4 pb-0">
                <h5 class="modal-title fw-bold text-white fs-4">Add Account</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('withdraw.methods.store') }}" method="POST" id="addMethodForm">
                @csrf
                <div class="modal-body p-4">
                    <div class="mb-4">
                        <label class="form-label text-white-50 small fw-bold text-uppercase">Method Type</label>
                        <select name="type" id="methodTypeSelect" class="form-select bg-dark text-white border-0 py-3" style="border-radius: 12px;" onchange="updateFormFields()">
                            <option value="Bank">Bank Account</option>
                            <option value="VodafoneCash">Vodafone Cash</option>
                            <option value="InstaPay">InstaPay</option>
                        </select>
                    </div>

                    <div id="bankFields">
                        <div class="mb-3">
                            <label class="form-label text-white-50 small">Bank Name</label>
                            <input type="text" name="details[bank_name]" class="form-control bg-dark text-white border-0 py-3" placeholder="e.g. CIB, NBE" style="border-radius: 12px;" required oninput="this.value = this.value.replace(/[^a-zA-Z\s\u0600-\u06FF]/g, '')">
                        </div>
                        <div class="mb-3">
                            <label class="form-label text-white-50 small">Account Number / IBAN</label>
                            <input type="text" name="details[account_number]" class="form-control bg-dark text-white border-0 py-3" placeholder="Enter your full account number" style="border-radius: 12px;" required inputmode="numeric" oninput="this.value = this.value.replace(/[^0-9]/g, '')">
                        </div>
                    </div>

                    <div id="phoneFields" class="d-none">
                        <div class="mb-3">
                            <label class="form-label text-white-50 small">Wallet Phone Number</label>
                            <input type="text" name="details[phone]" class="form-control bg-dark text-white border-0 py-3" placeholder="01xxxxxxxxx" style="border-radius: 12px;">
                        </div>
                    </div>

                    <div id="instapayFields" class="d-none">
                        <div class="mb-3">
                            <label class="form-label text-white-50 small">InstaPay Address or Phone</label>
                            <input type="text" name="details[address]" class="form-control bg-dark text-white border-0 py-3" placeholder="name@instapay" style="border-radius: 12px;">
                        </div>
                    </div>

                    <div class="form-check mt-3">
                        <input class="form-check-input" type="checkbox" name="is_primary" id="isPrimaryCheck">
                        <label class="form-check-label text-white-50 small" for="isPrimaryCheck">Set as primary method</label>
                    </div>
                </div>
                <div class="modal-footer border-0 p-4 pt-0">
                    <button type="submit" class="btn btn-primary w-100 py-3 fw-bold rounded-pill">Save Account</button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
.dark-card { background: #1e293b; border: 1px solid rgba(255,255,255,0.05); border-radius: 16px; }
.form-select { background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16'%3e%3cpath fill='none' stroke='%23ffffff' stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='m2 5 6 6 6-6'/%3e%3c/svg%3e"); }
</style>

<script>
function updateFormFields() {
    const type = document.getElementById('methodTypeSelect').value;
    document.getElementById('bankFields').classList.add('d-none');
    document.getElementById('phoneFields').classList.add('d-none');
    document.getElementById('instapayFields').classList.add('d-none');

    if (type === 'Bank') document.getElementById('bankFields').classList.remove('d-none');
    else if (type === 'VodafoneCash') document.getElementById('phoneFields').classList.remove('d-none');
    else if (type === 'InstaPay') document.getElementById('instapayFields').classList.remove('d-none');

    // Clear inputs of hidden fields
    document.querySelectorAll('#addMethodForm .d-none input').forEach(i => i.value = '');
}
</script>
@endsection
