@extends('admin.admin_dashboard')

@section('admin')
<div class="page-content">
    <div class="d-flex justify-content-between align-items-center flex-wrap grid-margin mb-3">
        <div>
            <h4 class="mb-1">Request: {{ $item ? $item->ItemName : 'Special Dish' }}</h4>
            <p class="text-muted">Chatting with <strong>{{ $customerUser->name }}</strong> about a customization request.</p>
        </div>
        <a href="{{ route('kitchen.customization.requests') }}" class="btn btn-outline-primary btn-sm">
            <i class="fas fa-arrow-left me-1"></i> Back to Requests
        </a>
    </div>

    <div class="row chat-wrapper">
        <div class="col-md-12">
            <div class="card border-0 shadow-sm" style="border-radius: 20px; overflow: hidden; display: flex; flex-direction: column; min-height: 500px; max-height: 70vh;">
                <div class="card-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between">
                    <div class="d-flex align-items-center">
                        <div class="wd-45 ht-45 bg-soft-primary rounded-circle d-flex align-items-center justify-content-center me-3 text-primary">
                            <i class="fas fa-magic"></i>
                        </div>
                        <div>
                            <h6 class="mb-0">{{ $customerUser->name }}</h6>
                            <small class="text-success"><i class="fas fa-circle font-size-10 me-1"></i> Online</small>
                        </div>
                    </div>
                </div>
                
                <div class="card-body chat-body" style="flex: 1; overflow-y: auto; background: #f8f9fa; padding: 30px;">
                    <div class="messages">
                        @foreach($messages as $msg)
                            @php 
                                $isMe = $msg->SenderID == Auth::id();
                            @endphp
                            <div class="d-flex {{ $isMe ? 'justify-content-end' : 'justify-content-start' }} mb-4">
                                <div class="message-container" style="max-width: 70%;">
                                    <div class="p-3 {{ $isMe ? 'bg-primary text-white rounded-start-lg rounded-top-lg' : 'bg-white text-dark shadow-sm rounded-end-lg rounded-top-lg' }}" 
                                         style="border-radius: 15px;">
                                        <p class="mb-0">{{ $msg->Message }}</p>
                                        @if($msg->Type === 'approved')
                                            <div class="mt-2 pt-2 border-top border-white-50 small">
                                                <i class="fas fa-check-circle me-1"></i> Approved 
                                                @if($msg->ExtraCharge > 0)
                                                    (+{{ number_format($msg->ExtraCharge, 2) }} EGP)
                                                @endif
                                            </div>
                                        @elseif($msg->Type === 'rejected')
                                            <div class="mt-2 pt-2 border-top border-danger-50 small text-danger">
                                                <i class="fas fa-times-circle me-1"></i> Rejected
                                            </div>
                                        @endif
                                    </div>
                                    <div class="small text-muted mt-1 {{ $isMe ? 'text-end' : '' }}">
                                        {{ $msg->Timestamp }}
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                @if(Auth::user()->Role !== 'Admin')
                <div class="card-footer bg-white p-3 border-top">
                    <form id="chatForm" action="{{ route('kitchen.preorder.chat.send', [$menuItemId ?? 0, $customerUser->UserID]) }}" method="POST">
                        @csrf
                        <input type="hidden" name="session_id" value="{{ $sessionId }}">
                        <div class="input-group">
                            <input type="text" name="message" class="form-control border-0 bg-light py-2" placeholder="Write your message..." required>
                            <button class="btn btn-primary px-4" type="submit">
                                <i class="fas fa-paper-plane"></i>
                            </button>
                        </div>
                    </form>
                    
                    <div class="mt-3 d-flex gap-2">
                        @php $lastReq = $messages->where('Type', 'request')->last(); @endphp
                        <button type="button" class="btn btn-sm btn-success" data-bs-toggle="modal" data-bs-target="#approveModal" {{ !$lastReq ? 'disabled' : '' }}>
                            <i class="fas fa-check me-1"></i> Final Approve
                        </button>
                        <form action="{{ route('kitchen.chat.reject', optional($lastReq)->LiveChatID ?? 0) }}" method="POST">
                            @csrf
                            <button type="submit" class="btn btn-sm btn-outline-danger" {{ !$lastReq ? 'disabled' : '' }}>
                                <i class="fas fa-times me-1"></i> Reject
                            </button>
                        </form>
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>

@if(Auth::user()->Role !== 'Admin')
<!-- Final Approve Modal -->
@php $lastRequest = $messages->where('Type', 'request')->last(); @endphp
@if($lastRequest)
<div class="modal fade" id="approveModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form action="{{ route('kitchen.chat.approve', $lastRequest->LiveChatID) }}" method="POST">
            @csrf
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Approve Customization</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Final approval for request: <strong>{{ $item ? $item->ItemName : 'Special Custom Dish' }}</strong></p>
                    <div class="mb-3">
                        <label class="form-label">Extra Charge (EGP)</label>
                        <input type="number" step="0.5" name="extra_charge" class="form-control" placeholder="0.00">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-success">Approve & Send to Cart</button>
                </div>
            </div>
        </form>
    </div>
</div>
@endif
@endif

@push('scripts')
<script>
    $(document).ready(function() {
        const chatBody = $('.chat-body');
        const messageContainer = $('.messages');
        const sessionId = '{{ $sessionId }}';
        const menuItemId = '{{ $menuItemId ?? 0 }}';
        const customerId = '{{ $customerUser->UserID }}';
        const currentUserId = '{{ Auth::id() }}';
        
        chatBody.scrollTop(chatBody[0].scrollHeight);

        // AJAX Send
        $('#chatForm').on('submit', function(e) {
            e.preventDefault();
            const form = $(this);
            const input = form.find('input[name="message"]');
            const message = input.val();
            if(!message) return;

            $.post(form.attr('action'), form.serialize(), function() {
                input.val('');
                fetchMessages(); // Refresh immediately
            });
        });

        function fetchMessages() {
            let url = `/chat/preorder/${menuItemId}/messages?session_id=${sessionId}`;
            $.get(url, function(data) {
                const currentCount = messageContainer.find('.message-container').length;
                if (data.length > currentCount || JSON.stringify(data).includes('approved') || JSON.stringify(data).includes('rejected')) {
                    messageContainer.empty();
                    data.forEach(m => {
                        const isMe = m.SenderID == currentUserId;
                        let extraHtml = '';
                        if(m.Type === 'approved') {
                            extraHtml = `<div class="mt-2 pt-2 border-top border-white-50 small"><i class="fas fa-check-circle me-1"></i> Approved ${m.ExtraCharge > 0 ? '(+' + m.ExtraCharge + ' EGP)' : ''}</div>`;
                        } else if(m.Type === 'rejected') {
                            extraHtml = `<div class="mt-2 pt-2 border-top border-danger-50 small text-danger"><i class="fas fa-times-circle me-1"></i> Rejected</div>`;
                        }

                        const msgHtml = `
                            <div class="d-flex ${isMe ? 'justify-content-end' : 'justify-content-start'} mb-4">
                                <div class="message-container" style="max-width: 70%;">
                                    <div class="p-3 ${isMe ? 'bg-primary text-white' : 'bg-white text-dark shadow-sm'}" style="border-radius: 15px;">
                                        <p class="mb-0">${m.Message}</p>
                                        ${extraHtml}
                                    </div>
                                    <div class="small text-muted mt-1 ${isMe ? 'text-end' : ''}">${m.Timestamp}</div>
                                </div>
                            </div>
                        `;
                        messageContainer.append(msgHtml);
                    });
                    chatBody.scrollTop(chatBody[0].scrollHeight);
                }
            });
        }

        setInterval(fetchMessages, 4000);
    });
</script>
@endpush

<style>
    .chat-body::-webkit-scrollbar { width: 5px; }
    .chat-body::-webkit-scrollbar-track { background: #f1f1f1; }
    .chat-body::-webkit-scrollbar-thumb { background: #ccc; border-radius: 10px; }
</style>
@endsection

