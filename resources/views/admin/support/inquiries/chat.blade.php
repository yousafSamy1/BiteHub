@extends('admin.admin_dashboard')
@section('admin')
<div class="page-content">
    <nav class="page-breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('admin.inquiries') }}">Support Inquiries</a></li>
            <li class="breadcrumb-item active" aria-current="page">Chat with {{ $inquiry->user->FullName }}</li>
        </ol>
    </nav>

    <div class="row">
        <div class="col-md-10 mx-auto">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h6 class="card-title mb-0">Conversation History</h6>
                    <span class="badge {{ $inquiry->Status == 'Escalated' ? 'bg-danger' : 'bg-success' }}">
                        {{ $inquiry->Status }}
                    </span>
                </div>
                <div class="card-body" id="adminChatContainer" style="height: 480px; overflow-y: auto; background: #f4f5f7; padding: 25px;">
                    @foreach($inquiry->messages as $msg)
                        <div class="mb-3 d-flex @if($msg->SenderType == 'User') justify-content-start @else justify-content-end @endif">
                            <div style="max-width: 75%; padding: 14px 18px; border-radius: 18px; box-shadow: 0 2px 4px rgba(0,0,0,0.05);
                                @if($msg->SenderType == 'User') background: #ffffff; color: #333; border-bottom-left-radius: 2px; border: 1px solid #dee2e6;
                                @elseif($msg->SenderType == 'Bot') background: #fff9db; color: #856404; border: 1px solid #ffeeba; border-bottom-left-radius: 2px;
                                @else background: #727cf5; color: #fff; border-bottom-right-radius: 2px; @endif">
                                <div style="display:flex; align-items:center; gap:8px; margin-bottom:4px;">
                                    <strong style="font-size: 0.85rem; @if($msg->SenderType == 'Admin') color: #fff; @endif">
                                        @if($msg->SenderType == 'User') <i class="fas fa-user-circle"></i> {{ $inquiry->user->FullName }} 
                                        @elseif($msg->SenderType == 'Bot') <i class="fas fa-robot"></i> BiteBot 🤖
                                        @else <i class="fas fa-shield-halved"></i> Support Team @endif
                                    </strong>
                                </div>
                                <p class="mb-1" style="font-size: 0.95rem; line-height: 1.5; color: inherit;">{{ $msg->Message }}</p>
                                <div class="text-end" style="margin-top:4px">
                                    <small class="opacity-75" style="font-size: 0.7rem; font-weight: 600;">{{ $msg->created_at->format('M d, H:i') }}</small>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
                <div class="card-footer">
                    <form action="{{ route('admin.inquiry.reply', $inquiry->InquiryID) }}" method="POST">
                        @csrf
                        <div class="input-group">
                            <input type="text" name="message" class="form-control" placeholder="Type your reply here..." required autocomplete="off">
                            <button class="btn btn-primary" type="submit">
                                <i data-feather="send" class="icon-sm"></i> Send Reply
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener("DOMContentLoaded", function() {
        const container = document.getElementById('adminChatContainer');
        container.scrollTop = container.scrollHeight;
    });
</script>
@endsection
