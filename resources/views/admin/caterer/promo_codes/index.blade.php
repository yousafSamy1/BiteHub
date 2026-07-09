@extends('admin.admin_dashboard')

@section('admin')
<div class="page-content">

<style>
    .promo-card { background: #1e293b; border-radius: 16px; border: 1px solid rgba(255,255,255,0.05); padding: 28px; color: #f8fafc; }
    .promo-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 28px; flex-wrap: wrap; gap: 12px; }
    .promo-title { color: #f8fafc; font-weight: 700; font-size: 1.4rem; margin: 0; }
    .promo-sub { color: #94a3b8; font-size: 0.9rem; margin: 4px 0 0; }

    .btn-promo-primary { background: linear-gradient(135deg, #6366f1, #4f46e5); color: #fff; border: none; border-radius: 10px; padding: 10px 20px; font-weight: 600; font-size: 0.9rem; cursor: pointer; transition: all 0.2s; }
    .btn-promo-primary:hover { filter: brightness(1.15); transform: translateY(-1px); box-shadow: 0 4px 12px rgba(99,102,241,0.4); }
    .btn-promo-secondary { background: rgba(255,255,255,0.07); color: #e2e8f0; border: 1px solid rgba(255,255,255,0.1); border-radius: 10px; padding: 10px 20px; font-weight: 600; font-size: 0.9rem; cursor: pointer; transition: all 0.2s; }
    .btn-promo-secondary:hover { background: rgba(255,255,255,0.12); }

    .create-form-panel { background: rgba(99,102,241,0.06); border: 1px solid rgba(99,102,241,0.2); border-radius: 14px; padding: 24px; margin-bottom: 28px; }
    .create-form-panel label { color: #94a3b8; font-weight: 600; font-size: 0.8rem; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 6px; display: block; }
    .promo-input { background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1); border-radius: 10px; color: #f8fafc; padding: 10px 14px; width: 100%; font-size: 0.95rem; transition: border-color 0.2s; }
    .promo-input:focus { outline: none; border-color: #6366f1; background: rgba(99,102,241,0.08); }
    .promo-input option { background: #1e293b; }

    .promo-table { width: 100%; border-collapse: separate; border-spacing: 0; }
    .promo-table th { color: #94a3b8; font-weight: 600; font-size: 0.78rem; text-transform: uppercase; letter-spacing: 0.06em; padding: 14px 16px; border-bottom: 1px solid rgba(255,255,255,0.05); }
    .promo-table td { color: #e2e8f0; font-size: 0.92rem; padding: 14px 16px; vertical-align: middle; border-bottom: 1px solid rgba(255,255,255,0.03); }
    .promo-table tbody tr { transition: background 0.2s; }
    .promo-table tbody tr:hover { background: rgba(255,255,255,0.02); }

    .badge-active   { background: rgba(16,185,129,0.15); color: #34d399; border: 1px solid rgba(16,185,129,0.25); padding: 5px 12px; border-radius: 8px; font-size: 0.78rem; font-weight: 700; }
    .badge-inactive { background: rgba(148,163,184,0.15); color: #94a3b8; border: 1px solid rgba(148,163,184,0.2); padding: 5px 12px; border-radius: 8px; font-size: 0.78rem; font-weight: 700; }
    .badge-type-pct { background: rgba(99,102,241,0.15); color: #818cf8; border: 1px solid rgba(99,102,241,0.2); padding: 4px 10px; border-radius: 7px; font-size: 0.78rem; font-weight: 700; }
    .badge-type-fix { background: rgba(245,158,11,0.15); color: #fbbf24; border: 1px solid rgba(245,158,11,0.2); padding: 4px 10px; border-radius: 7px; font-size: 0.78rem; font-weight: 700; }

    .code-chip { background: rgba(99,102,241,0.12); color: #a5b4fc; border: 1px solid rgba(99,102,241,0.25); padding: 5px 12px; border-radius: 8px; font-family: 'Courier New', monospace; font-weight: 700; font-size: 0.88rem; letter-spacing: 1px; }

    .action-btn { border: none; border-radius: 8px; padding: 6px 12px; font-size: 0.8rem; font-weight: 600; cursor: pointer; transition: all 0.15s; }
    .action-btn-toggle { background: rgba(245,158,11,0.15); color: #fbbf24; }
    .action-btn-toggle:hover { background: rgba(245,158,11,0.25); }
    .action-btn-edit { background: rgba(99,102,241,0.15); color: #818cf8; }
    .action-btn-edit:hover { background: rgba(99,102,241,0.25); }
    .action-btn-delete { background: rgba(239,68,68,0.15); color: #f87171; }
    .action-btn-delete:hover { background: rgba(239,68,68,0.25); }
    .action-btn-announce { background: rgba(16,185,129,0.15); color: #34d399; }
    .action-btn-announce:hover { background: rgba(16,185,129,0.25); }
    .action-btn-announced { background: rgba(100,116,139,0.15); color: #64748b; cursor: not-allowed; }

    .modal-promo .modal-content { background: #1e293b; color: #f8fafc; border: 1px solid rgba(255,255,255,0.08); border-radius: 16px; }
    .modal-promo .modal-header { border-bottom: 1px solid rgba(255,255,255,0.07); }
    .modal-promo .modal-footer { border-top: 1px solid rgba(255,255,255,0.07); }
    .stats-row { display: flex; gap: 16px; margin-bottom: 24px; flex-wrap: wrap; }
    .stat-mini { background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.06); border-radius: 12px; padding: 14px 20px; flex: 1; min-width: 120px; }
    .stat-mini-val { font-size: 1.6rem; font-weight: 800; color: #f8fafc; }
    .stat-mini-lbl { font-size: 0.75rem; font-weight: 600; color: #94a3b8; text-transform: uppercase; }
</style>

<div class="promo-card">
    <div class="promo-header">
        <div>
            <h2 class="promo-title"><i data-feather="tag" style="width:22px;color:#818cf8;vertical-align:middle;margin-right:8px;"></i> Promo Codes</h2>
            <p class="promo-sub">Create and manage discount codes for your customers.</p>
        </div>
        <button class="btn-promo-primary" id="toggleCreateForm" onclick="document.getElementById('createFormPanel').classList.toggle('d-none')">
            <i data-feather="plus" style="width:16px;vertical-align:middle;margin-right:4px;"></i> New Promo Code
        </button>
    </div>

    {{-- Stats --}}
    <div class="stats-row">
        <div class="stat-mini">
            <div class="stat-mini-val">{{ $promoCodes->total() }}</div>
            <div class="stat-mini-lbl">Total Codes</div>
        </div>
        <div class="stat-mini">
            <div class="stat-mini-val" style="color:#34d399;">{{ $promoCodes->where('IsActive', true)->count() }}</div>
            <div class="stat-mini-lbl">Active</div>
        </div>
        <div class="stat-mini">
            <div class="stat-mini-val" style="color:#fbbf24;">{{ $promoCodes->sum('UsedCount') }}</div>
            <div class="stat-mini-lbl">Total Uses</div>
        </div>
    </div>

    {{-- Create Form --}}
    <div id="createFormPanel" class="create-form-panel d-none">
        <h5 style="color:#818cf8; font-weight:700; margin-bottom:20px;"><i data-feather="plus-circle" style="width:17px;margin-right:6px;vertical-align:middle;"></i>Create New Promo Code</h5>
        <form method="POST" action="{{ route('caterer.promo_codes.store') }}">
            @csrf
            <div class="row g-3">
                <div class="col-md-3">
                    <label>Code</label>
                    <input type="text" name="Code" class="promo-input" placeholder="e.g. SAVE20" required style="text-transform:uppercase;">
                </div>
                <div class="col-md-2">
                    <label>Type</label>
                    <select name="Type" class="promo-input" required>
                        <option value="Percentage">Percentage %</option>
                        <option value="Fixed">Fixed EGP</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label>Value</label>
                    <input type="number" name="Value" class="promo-input" placeholder="20" min="0" step="0.01" required>
                </div>
                <div class="col-md-2">
                    <label>Min. Order (EGP)</label>
                    <input type="number" name="MinOrderAmount" class="promo-input" placeholder="0" min="0" step="0.01" value="0" required>
                </div>
                <div class="col-md-2">
                    <label>Max Uses</label>
                    <input type="number" name="MaxUses" class="promo-input" placeholder="Unlimited" min="1">
                </div>
                <div class="col-md-2">
                    <label>Expiry Date</label>
                    <input type="date" name="ExpiryDate" class="promo-input">
                </div>
                <div class="col-12 d-flex gap-2 mt-2">
                    <button type="submit" class="btn-promo-primary">
                        <i data-feather="save" style="width:15px;margin-right:5px;vertical-align:middle;"></i>Create Code
                    </button>
                    <button type="button" class="btn-promo-secondary" onclick="document.getElementById('createFormPanel').classList.add('d-none')">Cancel</button>
                </div>
            </div>
        </form>
    </div>

    {{-- Promo Codes Table --}}
    @if($promoCodes->count() > 0)
    <div class="table-responsive">
        <table class="promo-table">
            <thead>
                <tr>
                    <th>Code</th>
                    <th>Type</th>
                    <th>Value</th>
                    <th>Min. Order</th>
                    <th>Uses</th>
                    <th>Expiry</th>
                    <th>Status</th>
                    <th style="text-align:right;">Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($promoCodes as $promo)
                <tr>
                    <td><span class="code-chip">{{ $promo->Code }}</span></td>
                    <td>
                        @if($promo->Type === 'Percentage')
                            <span class="badge-type-pct">% Percentage</span>
                        @else
                            <span class="badge-type-fix">EGP Fixed</span>
                        @endif
                    </td>
                    <td class="fw-bold">
                        @if($promo->Type === 'Percentage')
                            {{ $promo->Value }}%
                        @else
                            {{ number_format($promo->Value, 2) }} EGP
                        @endif
                    </td>
                    <td>{{ number_format($promo->MinOrderAmount, 2) }} EGP</td>
                    <td>
                        <span style="color:#818cf8; font-weight:700;">{{ $promo->UsedCount }}</span>
                        @if($promo->MaxUses)
                            <span style="color:#64748b;"> / {{ $promo->MaxUses }}</span>
                        @else
                            <span style="color:#64748b;"> / ∞</span>
                        @endif
                    </td>
                    <td>
                        @if($promo->ExpiryDate)
                            @php $expired = \Carbon\Carbon::parse($promo->ExpiryDate)->isPast(); @endphp
                            <span style="color: {{ $expired ? '#f87171' : '#94a3b8' }};">
                                {{ \Carbon\Carbon::parse($promo->ExpiryDate)->format('d M Y') }}
                                @if($expired) <i data-feather="alert-circle" style="width:12px;margin-left:4px;"></i> @endif
                            </span>
                        @else
                            <span style="color:#64748b;">No Expiry</span>
                        @endif
                    </td>
                    <td>
                        @if($promo->IsActive)
                            <span class="badge-active">Active</span>
                        @else
                            <span class="badge-inactive">Inactive</span>
                        @endif
                    </td>
                    <td style="text-align:right;">
                        <div class="d-flex gap-2 justify-content-end">
                            {{-- Edit Button --}}
                            <button class="action-btn action-btn-edit" data-bs-toggle="modal" data-bs-target="#editModal{{ $promo->PromoCodeID }}">
                                <i data-feather="edit-2" style="width:13px;"></i> Edit
                            </button>
                            {{-- Toggle --}}
                            <form method="POST" action="{{ route('caterer.promo_codes.toggle', $promo->PromoCodeID) }}" style="display:inline;">
                                @csrf
                                <button type="submit" class="action-btn action-btn-toggle">
                                    @if($promo->IsActive)
                                        <i data-feather="eye-off" style="width:13px;"></i> Disable
                                    @else
                                        <i data-feather="eye" style="width:13px;"></i> Enable
                                    @endif
                                </button>
                            </form>
                            {{-- Announce --}}
                            @if($promo->email_sent_at)
                                <button class="action-btn action-btn-announced" title="Already sent on {{ $promo->email_sent_at->format('d M Y') }}" disabled>
                                    <i data-feather="send" style="width:13px;"></i> Sent
                                </button>
                            @else
                                <button class="action-btn action-btn-announce" onclick="announcePromo({{ $promo->PromoCodeID }}, this)" title="Send email to all customers">
                                    <i data-feather="send" style="width:13px;"></i> Announce
                                </button>
                            @endif
                            {{-- Delete --}}
                            <form method="POST" action="{{ route('caterer.promo_codes.delete', $promo->PromoCodeID) }}" style="display:inline;" onsubmit="return confirm('Delete this promo code?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="action-btn action-btn-delete">
                                    <i data-feather="trash-2" style="width:13px;"></i>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>

                {{-- Edit Modal --}}
                <div class="modal fade modal-promo" id="editModal{{ $promo->PromoCodeID }}" tabindex="-1">
                    <div class="modal-dialog modal-dialog-centered modal-lg">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title fw-bold"><i data-feather="edit" style="width:17px;margin-right:8px;color:#818cf8;vertical-align:middle;"></i>Edit: <span class="code-chip ms-2">{{ $promo->Code }}</span></h5>
                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                            </div>
                            <form method="POST" action="{{ route('caterer.promo_codes.update', $promo->PromoCodeID) }}">
                                @csrf
                                <div class="modal-body p-4">
                                    <div class="row g-3">
                                        <div class="col-md-4">
                                            <label class="create-form-panel" style="background:none;border:none;padding:0;">Code</label>
                                            <input type="text" name="Code" class="promo-input" value="{{ $promo->Code }}" required style="text-transform:uppercase;">
                                        </div>
                                        <div class="col-md-4">
                                            <label style="color:#94a3b8;font-weight:600;font-size:0.8rem;text-transform:uppercase;letter-spacing:0.05em;margin-bottom:6px;display:block;">Type</label>
                                            <select name="Type" class="promo-input" required>
                                                <option value="Percentage" {{ $promo->Type === 'Percentage' ? 'selected' : '' }}>Percentage %</option>
                                                <option value="Fixed" {{ $promo->Type === 'Fixed' ? 'selected' : '' }}>Fixed EGP</option>
                                            </select>
                                        </div>
                                        <div class="col-md-4">
                                            <label style="color:#94a3b8;font-weight:600;font-size:0.8rem;text-transform:uppercase;letter-spacing:0.05em;margin-bottom:6px;display:block;">Value</label>
                                            <input type="number" name="Value" class="promo-input" value="{{ $promo->Value }}" min="0" step="0.01" required>
                                        </div>
                                        <div class="col-md-4">
                                            <label style="color:#94a3b8;font-weight:600;font-size:0.8rem;text-transform:uppercase;letter-spacing:0.05em;margin-bottom:6px;display:block;">Min. Order (EGP)</label>
                                            <input type="number" name="MinOrderAmount" class="promo-input" value="{{ $promo->MinOrderAmount }}" min="0" step="0.01" required>
                                        </div>
                                        <div class="col-md-4">
                                            <label style="color:#94a3b8;font-weight:600;font-size:0.8rem;text-transform:uppercase;letter-spacing:0.05em;margin-bottom:6px;display:block;">Max Uses</label>
                                            <input type="number" name="MaxUses" class="promo-input" value="{{ $promo->MaxUses }}" min="1" placeholder="Unlimited">
                                        </div>
                                        <div class="col-md-4">
                                            <label style="color:#94a3b8;font-weight:600;font-size:0.8rem;text-transform:uppercase;letter-spacing:0.05em;margin-bottom:6px;display:block;">Expiry Date</label>
                                            <input type="date" name="ExpiryDate" class="promo-input" value="{{ $promo->ExpiryDate ? \Carbon\Carbon::parse($promo->ExpiryDate)->format('Y-m-d') : '' }}">
                                        </div>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn-promo-secondary" data-bs-dismiss="modal">Cancel</button>
                                    <button type="submit" class="btn-promo-primary"><i data-feather="save" style="width:15px;margin-right:5px;vertical-align:middle;"></i>Save Changes</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                @endforeach
            </tbody>
        </table>
    </div>

    {{-- Pagination --}}
    <div class="mt-4 d-flex justify-content-center">
        {{ $promoCodes->links() }}
    </div>

    @else
    <div class="text-center py-5" style="color:#64748b;">
        <i data-feather="tag" style="width:48px;height:48px;margin-bottom:16px;opacity:0.4;"></i>
        <p class="fw-bold fs-5" style="color:#94a3b8;">No promo codes yet</p>
        <p style="font-size:0.9rem;">Click "New Promo Code" to create your first discount code.</p>
    </div>
    @endif
</div>

</div>
@endsection

@push('custom-scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    function enforcePromoMax(form) {
        if (!form) return;
        const typeSelect = form.querySelector('select[name="Type"]');
        const valueInput = form.querySelector('input[name="Value"]');
        if (typeSelect && valueInput) {
            if (typeSelect.value === 'Percentage') {
                valueInput.max = "100";
                if (parseFloat(valueInput.value) > 100) valueInput.value = "100";
            } else {
                valueInput.removeAttribute('max');
            }
        }
    }

    document.querySelectorAll('select[name="Type"]').forEach(select => {
        select.addEventListener('change', function() { enforcePromoMax(this.closest('form')); });
        enforcePromoMax(select.closest('form'));
    });

    document.querySelectorAll('input[name="Value"]').forEach(input => {
        input.addEventListener('input', function() { enforcePromoMax(this.closest('form')); });
    });
});
</script>
<script>
function announcePromo(id, btn) {
    if (typeof Swal !== 'undefined') {
        Swal.fire({
            title: 'Are you sure?',
            text: 'Send an announcement email to ALL customers about this promo code?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Yes, announce it!'
        }).then((result) => {
            if (result.isConfirmed) {
                executeAnnounce(id, btn);
            }
        });
    } else {
        if (!confirm('Send an announcement email to ALL customers about this promo code?')) return;
        executeAnnounce(id, btn);
    }
}

function executeAnnounce(id, btn) {
    btn.disabled = true;
    btn.innerHTML = '<i data-feather="loader" style="width:13px;"></i> Sending...';
    if (typeof feather !== 'undefined') feather.replace();

    fetch(`/admin/caterer/promo-codes/${id}/announce`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json',
            'Content-Type': 'application/json'
        }
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            btn.className = 'action-btn action-btn-announced';
            btn.innerHTML = '<i data-feather="send" style="width:13px;"></i> Sent';
            btn.title = 'Announcement already sent';
            btn.disabled = true;
            if (typeof feather !== 'undefined') feather.replace();
            if (typeof toastr !== 'undefined') toastr.success(data.message);
            else alert('✅ ' + data.message);
        } else {
            btn.disabled = false;
            btn.className = 'action-btn action-btn-announced';
            btn.innerHTML = '<i data-feather="send" style="width:13px;"></i> Sent';
            btn.title = data.message;
            btn.disabled = true;
            if (typeof feather !== 'undefined') feather.replace();
            if (typeof toastr !== 'undefined') toastr.info(data.message);
            else alert('ℹ️ ' + data.message);
        }
    })
    .catch(() => {
        btn.disabled = false;
        btn.innerHTML = '<i data-feather="send" style="width:13px;"></i> Announce';
        if (typeof feather !== 'undefined') feather.replace();
        if (typeof toastr !== 'undefined') toastr.error('Network error. Please try again.');
        else alert('❌ Network error. Please try again.');
    });
}
</script>
@endpush
