@extends('admin.admin_dashboard')

@section('admin')
    <div class="page-content">
        <div class="d-flex justify-content-between align-items-center flex-wrap grid-margin mb-3">
            <div>
                <h4 class="mb-1"><i data-feather="message-circle" class="me-2"></i>Plan Customization Chat</h4>
                <p class="text-muted">Chatting with <strong>{{ $customerUser->FullName }}</strong> about their subscription
                    plan.</p>
            </div>
            <a href="{{ route('kitchen.subscriptions.requests') }}" class="btn btn-outline-primary btn-sm">
                <i class="fas fa-arrow-left me-1"></i> Back to Requests
            </a>
        </div>

        <div class="row">
            <div class="col-md-12">
                <div class="card border-0 shadow-sm"
                    style="border-radius: 20px; overflow: hidden; display: flex; flex-direction: column; min-height: 500px; max-height: 70vh;">
                    <div class="card-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between">
                        <div class="d-flex align-items-center">
                            <div class="wd-45 ht-45 bg-soft-primary rounded-circle d-flex align-items-center justify-content-center me-3 text-primary"
                                style="width:45px; height:45px;">
                                <i class="fas fa-user"></i>
                            </div>
                            <div>
                                <h6 class="mb-0">{{ $customerUser->FullName }}</h6>
                                <small class="text-muted">Plan: {{ \Carbon\Carbon::parse($sub->StartDate)->format('d M') }}
                                    → {{ \Carbon\Carbon::parse($sub->EndDate)->format('d M Y') }} • {{ $sub->MealsPerDay }}
                                    meal(s)/day</small>
                            </div>
                        </div>
                        <span
                            class="badge bg-{{ $sub->Status === 'PendingApproval' ? 'warning' : 'success' }}">{{ $sub->Status }}</span>
                    </div>

                    <div class="card-body chat-body" style="flex: 1; overflow-y: auto; background: #f8f9fa; padding: 30px;"
                        id="chatBody">
                        <div class="messages">
                            @forelse($messages as $msg)
                                @php $isMe = $msg->SenderID == Auth::id(); @endphp
                                <div class="d-flex {{ $isMe ? 'justify-content-end' : 'justify-content-start' }} mb-4">
                                    <div class="message-container" style="max-width: 70%;">
                                        @if(!$isMe)
                                            <small class="text-muted d-block mb-1">{{ $customerUser->FullName }}</small>
                                        @endif
                                        <div class="p-3 {{ $isMe ? 'bg-primary text-white' : 'bg-white text-dark shadow-sm' }}"
                                            style="border-radius: 15px;">
                                            <p class="mb-0">{{ $msg->Message }}</p>
                                        </div>
                                        <div class="small text-muted mt-1 {{ $isMe ? 'text-end' : '' }}">
                                            {{ \Carbon\Carbon::parse($msg->Timestamp)->format('d M Y, H:i') }}
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <div class="text-center py-5 text-muted">
                                    <i class="fas fa-comments fa-3x mb-3 d-block opacity-25"></i>
                                    No messages yet. Start the conversation with the customer.
                                </div>
                            @endforelse
                        </div>
                    </div>

                    <div class="card-footer bg-white p-3 border-top">
                        @if(session('message'))
                            <div
                                class="alert alert-{{ session('alert-type') === 'success' ? 'success' : 'danger' }} alert-dismissible fade show py-2">
                                {{ session('message') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        @endif
                        <form action="{{ route('kitchen.subscriptions.chat.send', $sub->SubscriptionID) }}" method="POST">
                            @csrf
                            <div class="input-group">
                                <input type="text" name="message" class="form-control border-0 bg-light py-2"
                                    placeholder="Reply to customer..." required autofocus style="color:#111827;">
                                <button class="btn btn-primary px-4" type="submit">
                                    <i class="fas fa-paper-plane me-1"></i> Send
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <style>
        .chat-body::-webkit-scrollbar {
            width: 5px;
        }

        .chat-body::-webkit-scrollbar-track {
            background: #f1f1f1;
        }

        .chat-body::-webkit-scrollbar-thumb {
            background: #ccc;
            border-radius: 10px;
        }
    </style>
    <script>
        // Auto-scroll to bottom
        window.addEventListener('load', function () {
            var body = document.getElementById('chatBody');
            if (body) body.scrollTop = body.scrollHeight;
        });
    </script>
@endsection