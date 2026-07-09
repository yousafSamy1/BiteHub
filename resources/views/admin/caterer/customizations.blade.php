@extends('admin.admin_dashboard')

@section('admin')
<div class="page-content">
    <div class="d-flex justify-content-between align-items-center flex-wrap grid-margin mb-3">
        <div>
            <h4 class="mb-1">Customization Requests 🪄</h4>
            <p class="text-muted">Requests for items not yet ordered. Approve or reject them to allow customers to add them to their cart.</p>
        </div>
    </div>

    {{-- ── Approved & Awaiting Customer Payment ── --}}
    @if(isset($approvedPending) && $approvedPending->count() > 0)
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card border-0" style="border-left: 4px solid #4ade80 !important; border-radius: 10px;">
                <div class="card-body">
                    <h5 class="mb-3" style="color:#4ade80"><i class="fas fa-check-circle me-2"></i>Approved — Awaiting Customer Payment</h5>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Customer</th>
                                    <th>Dish</th>
                                    <th>Request</th>
                                    <th>Extra Charge</th>
                                    <th>Total Price</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($approvedPending as $req)
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <img src="{{ (!empty($req->sender->Image) && file_exists(public_path('upload/customer_images/'.$req->sender->Image))) ? url('upload/customer_images/'.$req->sender->Image) : url('upload/no_image.jpg') }}" class="wd-30 ht-30 rounded-circle me-2">
                                                <span>{{ $req->sender->name }}</span>
                                            </div>
                                        </td>
                                        <td>
                                            @if($req->menuItem)
                                                <strong>{{ $req->menuItem->ItemName }}</strong>
                                            @else
                                                <span class="badge bg-soft-info" style="background:rgba(96,165,250,0.1);color:#60a5fa"><i class="fas fa-magic me-1"></i> Special Request</span>
                                            @endif
                                        </td>
                                        <td>
                                            <div style="max-width:250px; white-space: normal; font-style: italic">
                                                "{{ $req->Message }}"
                                            </div>
                                            <small class="text-muted">{{ $req->Timestamp }}</small>
                                        </td>
                                        <td>
                                            @if($req->ExtraCharge > 0)
                                                <span class="text-warning fw-bold">+{{ number_format($req->ExtraCharge, 2) }} EGP</span>
                                            @else
                                                <span class="text-muted">—</span>
                                            @endif
                                        </td>
                                        <td>
                                            @php
                                                $basePrice = $req->menuItem ? $req->menuItem->ItemPrice : 0;
                                            @endphp
                                            <strong style="color:#4ade80">{{ number_format($basePrice + $req->ExtraCharge, 2) }} EGP</strong>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

    {{-- ── Pending Requests ── --}}

    <div class="row">
        <div class="col-md-12 grid-margin stretch-card">
            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Customer</th>
                                    <th>Dish</th>
                                    <th>Request</th>
                                    <th>Status</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($requests as $req)
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <img src="{{ (!empty($req->sender->Image) && file_exists(public_path('upload/customer_images/'.$req->sender->Image))) ? url('upload/customer_images/'.$req->sender->Image) : url('upload/no_image.jpg') }}" class="wd-30 ht-30 rounded-circle me-2">
                                                <span>{{ $req->sender->name }}</span>
                                            </div>
                                        </td>
                                        <td>
                                            @if($req->menuItem)
                                                <strong>{{ $req->menuItem->ItemName }}</strong>
                                            @else
                                                <span class="badge bg-soft-info" style="background:rgba(96,165,250,0.1);color:#60a5fa"><i class="fas fa-magic me-1"></i> Special Request</span>
                                            @endif
                                        </td>
                                        <td>
                                            <div style="max-width:300px; white-space: normal; font-style: italic">
                                                "{{ $req->Message }}"
                                            </div>
                                            <small class="text-muted">{{ $req->Timestamp }}</small>
                                        </td>
                                        <td>
                                            <span class="badge bg-warning">Pending</span>
                                        </td>
                                        <td>
                                            <div class="d-flex gap-2">
                                                <a href="{{ route('caterer.preorder.chat', [$req->MenuItemID ?? 0, $req->SenderID]) }}?session={{ $req->SessionID }}" class="btn btn-sm btn-outline-primary">
                                                    {{ Auth::user()->Role === 'Admin' ? 'View Chat' : 'Chat' }}
                                                </a>
                                                
                                                @if(Auth::user()->Role !== 'Admin')
                                                    <button type="button" class="btn btn-sm btn-success" data-bs-toggle="modal" data-bs-target="#approveModal{{ $req->LiveChatID }}">
                                                        Accept
                                                    </button>
                                                    <form action="{{ route('caterer.chat.reject', $req->LiveChatID) }}" method="POST">
                                                        @csrf
                                                        <button type="submit" class="btn btn-sm btn-outline-danger">Reject</button>
                                                    </form>
                                                @endif
                                            </div>

                                            @if(Auth::user()->Role !== 'Admin')
                                                <!-- Approve Modal -->
                                                <div class="modal fade" id="approveModal{{ $req->LiveChatID }}" tabindex="-1" aria-hidden="true">
                                                    <div class="modal-dialog">
                                                        <form action="{{ route('caterer.chat.approve', $req->LiveChatID) }}" method="POST">
                                                            @csrf
                                                            <div class="modal-content">
                                                                <div class="modal-header">
                                                                    <h5 class="modal-title">Approve Customization</h5>
                                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                                </div>
                                                                <div class="modal-body">
                                                                    <p>Accepting request for: <strong>{{ $req->menuItem ? $req->menuItem->ItemName : 'Special Custom Dish' }}</strong></p>
                                                                    <p class="text-muted italic">"{{ $req->Message }}"</p>
                                                                    
                                                                    <div class="mb-3">
                                                                        <label class="form-label">Extra Charge (EGP) - <small>Optional</small></label>
                                                                        <input type="number" step="0.5" name="extra_charge" class="form-control" placeholder="0.00">
                                                                    </div>
                                                                </div>
                                                                <div class="modal-footer">
                                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                                                    <button type="submit" class="btn btn-success">Approve & Notify</button>
                                                                </div>
                                                            </div>
                                                        </form>
                                                    </div>
                                                </div>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center py-5">
                                            <h5 class="text-muted">No pending customization requests. ✨</h5>
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
</div>
@endsection
