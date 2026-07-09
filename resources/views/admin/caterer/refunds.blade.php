@extends('admin.admin_dashboard')
@section('admin')

<div class="page-content">
    <nav class="page-breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('caterer.dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item active" aria-current="page">Processed Refunds</li>
        </ol>
    </nav>

    <div class="row">
        <div class="col-md-12 grid-margin stretch-card">
            <div class="card dark-card" style="background-color: #1e293b; border-radius: 16px; border: 1px solid rgba(255, 255, 255, 0.05);">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h6 class="card-title mb-0" style="font-size: 1.2rem; font-weight: 800; color: #f8fafc;">
                            <i data-feather="refresh-ccw" class="me-2 text-warning"></i> My Deducted Refunds
                        </h6>
                        <div class="d-flex gap-2">
                            <button onclick="exportToCSV()" class="btn btn-outline-info btn-sm d-flex align-items-center gap-2" style="border-radius: 10px; font-weight: 600;">
                                <i data-feather="download" style="width: 16px;"></i> Export CSV
                            </button>
                            <button onclick="window.print()" class="btn btn-primary btn-sm d-flex align-items-center gap-2" style="border-radius: 10px; font-weight: 600; background: #6366f1; border: none;">
                                <i data-feather="printer" style="width: 16px;"></i> Print Report
                            </button>
                        </div>
                    </div>
                    
                    <div class="alert alert-warning border-0 bg-warning bg-opacity-10 text-warning d-flex align-items-center gap-3 mb-4 no-print" style="border-radius: 12px;">
                        <i data-feather="info" style="width: 20px;"></i>
                        <p class="mb-0 fs-6 fw-semibold">The following amounts have been deducted from your wallet balance due to approved customer refund requests.</p>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-dark-custom">
                            <thead>
                                <tr>
                                    <th>Ref ID</th>
                                    <th>Customer</th>
                                    <th>Refunded Item</th>
                                    <th>Customer Reason</th>
                                    <th>Amount Deducted</th>
                                    <th>Deduction Date</th>
                                    <th>Admin Notes</th>
                                    <th class="no-print text-nowrap" style="width: 1%;">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($refunds as $refund)
                                <tr class="refund-row">
                                    <td class="fw-bold">#{{ $refund->RequestID }}</td>
                                    <td>
                                        <div class="fw-bold text-white">{{ $refund->customer->user->FullName }}</div>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center gap-2">
                                            <span class="badge border border-{{ $refund->RefundableType === 'Order' ? 'info' : 'primary' }} text-{{ $refund->RefundableType === 'Order' ? 'info' : 'primary' }} bg-transparent">
                                                {{ $refund->RefundableType }} #{{ $refund->RefundableID }}
                                            </span>
                                            <a href="{{ $refund->RefundableType === 'Order' ? route('caterer.orders') : '#' }}" class="text-custom-muted no-print" title="View Details">
                                                <i data-feather="external-link" style="width: 14px;"></i>
                                            </a>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="text-custom-muted small" style="max-width: 180px; white-space: normal; line-height: 1.4;">
                                            {{ $refund->Reason }}
                                        </div>
                                    </td>
                                    <td class="fw-bold text-danger">
                                        <div class="d-flex flex-column">
                                            <span>- {{ number_format($refund->Amount, 2) }} EGP</span>
                                            <small class="text-muted" style="font-size: 0.7rem;">Original: {{ number_format($refund->OriginalAmount, 2) }}</small>
                                        </div>
                                    </td>
                                    <td class="text-custom-muted">
                                        {{ \Carbon\Carbon::parse($refund->updated_at)->format('d M Y') }}
                                        <div class="small opacity-50">{{ \Carbon\Carbon::parse($refund->updated_at)->format('h:i A') }}</div>
                                    </td>
                                    <td>
                                        <div class="admin-note-box">
                                            {{ $refund->AdminNotes ?? 'Manual Adjustment' }}
                                        </div>
                                    </td>
                                    <td class="no-print">
                                        @php
                                            $orderId = $refund->RefundableType == 'Order' ? $refund->RefundableID : '';
                                            $details = "I would like to dispute Refund Request #{$refund->RequestID}.\n\n" .
                                                       "--- Refund Details ---\n" .
                                                       "• Item: {$refund->RefundableType} #{$refund->RefundableID}\n" .
                                                       "• Customer: {$refund->customer->user->FullName}\n" .
                                                       "• Refunded Amount: " . number_format($refund->Amount, 2) . " EGP\n" .
                                                       "• Original Amount: " . number_format($refund->OriginalAmount, 2) . " EGP\n" .
                                                       "• Customer Reason: {$refund->Reason}\n" .
                                                       "• Admin Notes: " . ($refund->AdminNotes ?? 'N/A') . "\n" .
                                                       "• Date: " . \Carbon\Carbon::parse($refund->updated_at)->format('d M Y, h:i A') . "\n\n" .
                                                       "My concern is: [Please type your reason here]";
                                        @endphp
                                        <a href="{{ route('caterer.support') }}?subject=Dispute Refund #{{ $refund->RequestID }}&message={{ urlencode($details) }}&order_id={{ $orderId }}" class="btn btn-sm btn-outline-warning d-inline-flex align-items-center gap-1" style="border-radius: 8px; font-size: 0.75rem;">
                                            <i data-feather="help-circle" style="width: 14px;"></i> Support
                                        </a>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="6" class="text-center py-5">
                                        <div class="text-custom-muted opacity-50 mb-3">
                                            <i data-feather="smile" style="width: 48px; height: 48px;"></i>
                                        </div>
                                        <h5 class="text-custom-muted">No refunds processed for your catering account yet.</h5>
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

@push('custom-scripts')
<script>
    document.addEventListener("DOMContentLoaded", function() {
        if (window.feather) { feather.replace(); }
    });

    function exportToCSV() {
        let csv = [];
        let rows = document.querySelectorAll("table tr");
        
        for (let i = 0; i < rows.length; i++) {
            let row = [], cols = rows[i].querySelectorAll("td, th");
            for (let j = 0; j < cols.length; j++) {
                // Clean data: remove extra spaces and newlines
                let data = cols[j].innerText.replace(/(\r\n|\n|\r)/gm, "").replace(/(\s\s+)/gm, ' ').trim();
                row.push('"' + data + '"');
            }
            csv.push(row.join(","));
        }

        // Download CSV file
        let csvFile = new Blob([csv.join("\n")], {type: "text/csv"});
        let downloadLink = document.createElement("a");
        downloadLink.download = "caterer-refunds-report.csv";
        downloadLink.href = window.URL.createObjectURL(csvFile);
        downloadLink.style.display = "none";
        document.body.appendChild(downloadLink);
        downloadLink.click();
    }
</script>

<style>
    .dark-card { color: #f8fafc; }
    .table-dark-custom th { 
        color: #94a3b8; font-weight: 600; font-size: 0.8rem; text-transform: uppercase; padding: 16px; 
        border-bottom: 1px solid rgba(255,255,255,0.05); 
    }
    .table-dark-custom td { 
        color: #e2e8f0; font-weight: 500; font-size: 0.95rem; padding: 16px; border-bottom: 1px solid rgba(255,255,255,0.02); 
    }
    .text-custom-muted { color: #94a3b8; }

    .refund-row:hover {
        background: rgba(255,255,255,0.02);
    }
    .admin-note-box {
        background: rgba(99, 102, 241, 0.05);
        border-left: 3px solid #6366f1;
        padding: 8px 12px;
        border-radius: 4px;
        font-size: 0.8rem;
        color: #e2e8f0;
        max-width: 220px;
        word-wrap: break-word;
        overflow-wrap: break-word;
        white-space: normal;
        line-height: 1.5;
        display: block;
    }

    .table-dark-custom td {
        vertical-align: middle;
    }

    .table-dark-custom td:last-child {
        white-space: nowrap;
        width: 120px;
        min-width: 120px;
        text-align: right;
    }

    /* Print Styles */
    @media print {
        body { background: white !important; color: black !important; }
        .sidebar, .navbar, .no-print, .btn, .page-breadcrumb { display: none !important; }
        .page-content { margin: 0 !important; padding: 0 !important; width: 100% !important; }
        .card { background: white !important; border: none !important; box-shadow: none !important; }
        .table-dark-custom th, .table-dark-custom td { color: black !important; border-bottom: 1px solid #ddd !important; }
        .card-title { color: black !important; font-size: 24px !important; margin-bottom: 20px !important; }
        .text-white, .text-danger { color: black !important; }
        .badge { border: 1px solid #000 !important; color: black !important; background: transparent !important; }
    }
</style>
@endpush

@endsection
