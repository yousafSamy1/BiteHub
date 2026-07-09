@extends('frontend.layouts.app')
@section('title', 'Chat for Order #' . $chatData['orderId'])

@section('content')
<style>
.chat-layout {
    display: flex;
    justify-content: center;
    height: calc(100vh - var(--nav-h) - 80px);
    min-height: 600px;
    margin-top: calc(var(--nav-h) + 20px);
    margin-bottom: 40px;
}
@media (max-width: 900px) {
    .chat-layout { height: auto; flex-direction: column; }
}

/* Main Chat Area */
.chat-main {
    width: 100%;
    max-width: 800px;
    background: var(--bg-card);
    border: 1px solid var(--border-color);
    border-radius: var(--radius-lg);
    display: flex;
    flex-direction: column;
    overflow: hidden;
    position: relative;
    box-shadow: 0 10px 30px rgba(0,0,0,0.2);
}
.chat-main::before {
    content: ''; position: absolute; inset: 0;
    background: radial-gradient(circle at center, rgba(255,107,53,0.03) 0%, transparent 60%);
    pointer-events: none; z-index: 0;
}

.chat-header {
    padding: 20px 24px;
    border-bottom: 1px solid var(--border-color);
    display: flex; align-items: center; justify-content: space-between;
    background: var(--bg-card2); z-index: 1;
}
.chef-info { display: flex; align-items: center; gap: 16px; }
.chef-avatar {
    width: 48px; height: 48px; border-radius: 50%;
    object-fit: cover; border: 2px solid var(--primary);
    box-shadow: 0 4px 12px rgba(255,107,53,0.3);
}

.chat-messages {
    flex: 1; overflow-y: auto; padding: 24px;
    display: flex; flex-direction: column; gap: 16px; z-index: 1;
}
.msg { max-width: 75%; display: flex; flex-direction: column; animation: fadeInUp 0.3s ease forwards; }
.msg.me { align-self: flex-end; align-items: flex-end; }
.msg.them { align-self: flex-start; align-items: flex-start; }
.msg.system { align-self: center; align-items: center; max-width: 90%; margin: 10px 0; }

.msg-bubble {
    padding: 14px 20px; border-radius: 20px;
    font-size: 0.95rem; line-height: 1.5;
    position: relative; box-shadow: 0 4px 12px rgba(0,0,0,0.1);
}
.msg.me .msg-bubble {
    background: linear-gradient(135deg, var(--primary), var(--accent));
    color: #fff;
    border-bottom-right-radius: 4px;
}
.msg.them .msg-bubble {
    background: var(--bg-card2);
    color: var(--text-primary);
    border: 1px solid var(--border-color);
    border-bottom-left-radius: 4px;
}
.msg.system .msg-bubble {
    background: rgba(255,255,255,0.05);
    color: var(--text-muted);
    border: 1px dashed var(--border-color);
    border-radius: 12px;
    font-size: 0.85rem;
    text-align: center;
    box-shadow: none;
}
.msg-time { font-size: 0.7rem; color: var(--text-muted); margin-top: 6px; }
.msg.me .msg-time { color: rgba(255,255,255,0.7); }

.chat-input-area {
    padding: 20px 24px; border-top: 1px solid var(--border-color);
    background: var(--bg-card2); z-index: 1;
}
.input-wrapper {
    display: flex; align-items: center; gap: 12px;
    background: var(--bg-card); border: 1px solid var(--border-color);
    border-radius: 30px; padding: 6px 6px 6px 20px;
    transition: var(--transition-fast);
}
.input-wrapper:focus-within { border-color: var(--primary); box-shadow: 0 0 0 3px rgba(255,107,53,0.15); }
.chat-input {
    flex: 1; border: none; background: transparent; color: var(--text-primary);
    font-size: 0.95rem; outline: none; padding: 10px 0; font-family: inherit;
}
.send-btn {
    width: 44px; height: 44px; border-radius: 50%;
    background: linear-gradient(135deg, var(--primary), var(--accent));
    color: #fff; border: none; display: flex; align-items: center; justify-content: center;
    cursor: pointer; box-shadow: 0 4px 12px rgba(255,107,53,0.4);
    transition: var(--transition-fast);
}
.send-btn:hover { transform: scale(1.05); }

/* Custom scrollbar for chat */
.chat-messages::-webkit-scrollbar { width: 6px; }
.chat-messages::-webkit-scrollbar-track { background: transparent; }
.chat-messages::-webkit-scrollbar-thumb { background: rgba(255,255,255,0.1); border-radius: 10px; }
</style>

<div class="container">
    <div class="chat-layout reveal">
        <!-- Main Chat -->
        <div class="chat-main">
            <!-- Header -->
            <div class="chat-header">
                <div class="chef-info">
                    @if($chatData['image'])
                    <img src="{{ $chatData['image'] }}" class="chef-avatar">
                    @else
                    <div class="chef-avatar" style="background:var(--bg-card);display:flex;align-items:center;justify-content:center;font-size:1.5rem">👨‍🍳</div>
                    @endif
                    <div>
                        <h2 style="margin: 0; font-size: 1.2rem; font-weight: 700;">{{ $chatData['title'] }}</h2>
                        <div style="font-size: 0.8rem; color: var(--text-muted); display: flex; align-items: center; gap: 6px; margin-top: 4px;">
                            Regarding Order #{{ $chatData['orderId'] }}
                        </div>
                    </div>
                </div>
                <a href="{{ route('frontend.tracking', $chatData['orderId']) }}" class="btn btn-outline btn-sm" style="border-radius: 20px; padding: 6px 16px;">
                    <i class="fas fa-arrow-left"></i> Back to Tracking
                </a>
            </div>

            <!-- Messages Box -->
            <div class="chat-messages" id="msgBox">
                <div style="text-align: center; margin-bottom: 20px; opacity: 0.6;">
                    <span style="background: var(--bg-card2); padding: 4px 12px; border-radius: 20px; font-size: 0.75rem; color: var(--text-muted); border: 1px solid var(--border-color);">
                        Chat started for Order #{{ $chatData['orderId'] }}
                    </span>
                </div>
                
                @foreach($messages as $msg)
                    @php 
                        $isMine = ($msg->SenderID == auth()->id());
                        $isSystem = in_array($msg->Type, ['approved', 'rejected']); 
                    @endphp
                    
                    @if($isSystem)
                        <div class="msg system" data-id="{{ $msg->LiveChatID }}">
                            <div class="msg-bubble">
                                @if($msg->Type == 'approved')
                                    <i class="fas fa-check-circle" style="color:var(--success);margin-right:6px"></i> 
                                    Request was approved. 
                                    @if($msg->ExtraCharge > 0)
                                    <span style="color:var(--accent);font-weight:bold">(Extra Charge: {{ number_format($msg->ExtraCharge, 2) }} EGP)</span>
                                    @endif
                                @else
                                    <i class="fas fa-times-circle" style="color:var(--danger);margin-right:6px"></i> 
                                    Request was rejected.
                                @endif
                                <div class="msg-time">{{ \Carbon\Carbon::parse($msg->Timestamp)->format('h:i A') }}</div>
                            </div>
                        </div>
                    @else
                        <div class="msg {{ $isMine ? 'me' : 'them' }}" data-id="{{ $msg->LiveChatID }}">
                            <div class="msg-bubble">
                                {{ $msg->Message }}
                                <div class="msg-time">{{ \Carbon\Carbon::parse($msg->Timestamp)->format('h:i A') }}</div>
                            </div>
                        </div>
                    @endif
                @endforeach
            </div>

            <!-- Input Area -->
            <form class="chat-input-area" id="chatForm">
                @csrf
                <div class="input-wrapper">
                    <input type="text" id="chatInput" class="chat-input" placeholder="Message the kitchen about your order..." autocomplete="off">
                    <button type="submit" class="send-btn" id="sendBtn">
                        <i class="fas fa-paper-plane" style="margin-left: -2px;"></i>
                    </button>
                </div>
                <div class="text-center mt-2">
                    <small style="color:var(--text-muted); font-size:0.75rem; opacity:0.8">
                        <i class="fas fa-info-circle"></i> To end this customization chat, type <strong>"Bitehub"</strong>. The Admin also monitors this chat.
                    </small>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
const orderId = {{ $chatData['orderId'] }};
const receiverId = {{ $chatData['receiverId'] }};
const msgBox = document.getElementById('msgBox');
const form = document.getElementById('chatForm');
const input = document.getElementById('chatInput');
let lastMsgId = {{ $messages->last()->LiveChatID ?? 0 }};

function scrollToBottom() {
    msgBox.scrollTop = msgBox.scrollHeight;
}
scrollToBottom();

function appendMessage(msg, isMine, time, id, type) {
    const div = document.createElement('div');
    
    if (type === 'approved' || type === 'rejected') {
        div.className = 'msg system';
        div.dataset.id = id;
        const icon = type === 'approved' ? '<i class="fas fa-check-circle" style="color:var(--success);margin-right:6px"></i>' : '<i class="fas fa-times-circle" style="color:var(--danger);margin-right:6px"></i>';
        let txt = type === 'approved' ? 'Request was approved.' : 'Request was rejected.';
        
        // Add charge info if approved and charge > 0
        const chargeVal = parseFloat(arguments[5] || 0); // Using 6th arg as charge
        if (type === 'approved' && chargeVal > 0) {
            txt += ` <span style="color:var(--accent);font-weight:bold">(Extra Charge: ${chargeVal.toFixed(2)} EGP)</span>`;
        }

        div.innerHTML = `
            <div class="msg-bubble">
                ${icon} ${txt}
                <div class="msg-time">${time}</div>
            </div>
        `;
    } else {
        div.className = `msg ${isMine ? 'me' : 'them'}`;
        div.dataset.id = id;
        div.innerHTML = `
            <div class="msg-bubble">
                ${msg}
                <div class="msg-time">${time}</div>
            </div>
        `;
    }
    
    msgBox.appendChild(div);
    scrollToBottom();
}

form.addEventListener('submit', function(e) {
    e.preventDefault();
    const text = input.value.trim();
    if(!text) return;
    
    // Optimistic UI
    appendMessage(text, true, 'Just now', 999999, 'message');
    input.value = '';
    
    fetch('{{ route("frontend.chat.send", $chatData["orderId"]) }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({ 
            message: text,
            receiver_id: receiverId
        })
    })
    .then(res => res.json())
    .then(data => {
        if(data.id) lastMsgId = Math.max(lastMsgId, data.id);
    })
    .catch(err => console.error(err));
});

// Poll for new messages every 3 seconds
setInterval(() => {
    fetch(`{{ route("frontend.chat.messages", $chatData["orderId"]) }}?after=${lastMsgId}`)
    .then(res => res.json())
    .then(msgs => {
        if(msgs && msgs.length > 0) {
            msgs.forEach(m => {
                if(!document.querySelector(`.msg[data-id="${m.id}"]`)) {
                    appendMessage(m.message, m.isMine, m.time, m.id, m.type, m.charge);
                    lastMsgId = Math.max(lastMsgId, m.id);
                }
            });
        }
    });
}, 3000);
</script>
@endpush
@endsection
