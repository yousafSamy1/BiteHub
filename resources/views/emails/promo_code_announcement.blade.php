<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>New Promo Code from BiteHub</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { background-color: #0f172a; font-family: 'Segoe UI', Arial, sans-serif; color: #f1f5f9; }
        .wrapper { max-width: 600px; margin: 0 auto; padding: 32px 16px; }
        .card { background: linear-gradient(145deg, #1e293b, #1a2540); border-radius: 24px; overflow: hidden; border: 1px solid rgba(255,255,255,0.06); }
        .hero { background: linear-gradient(135deg, #ff6b35 0%, #f7c59f 100%); padding: 44px 40px 36px; text-align: center; position: relative; }
        .hero-icon { font-size: 3rem; display: block; margin-bottom: 10px; }
        .hero h1 { font-size: 1.65rem; color: #fff; font-weight: 800; margin-bottom: 4px; letter-spacing: -0.5px; }
        .hero p { color: rgba(255,255,255,0.85); font-size: 0.95rem; }
        .body { padding: 36px 40px; }
        .code-box { background: rgba(99,102,241,0.12); border: 2px dashed rgba(99,102,241,0.5); border-radius: 16px; padding: 24px; text-align: center; margin: 24px 0; }
        .code-label { color: #94a3b8; font-size: 0.78rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.1em; margin-bottom: 8px; }
        .code-text { font-family: 'Courier New', monospace; font-size: 2.2rem; font-weight: 900; color: #a5b4fc; letter-spacing: 4px; }
        .details-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin: 24px 0; }
        .detail-item { background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.06); border-radius: 12px; padding: 14px 16px; }
        .detail-label { color: #64748b; font-size: 0.72rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.06em; margin-bottom: 4px; }
        .detail-value { color: #e2e8f0; font-size: 1rem; font-weight: 700; }
        .cta { text-align: center; margin: 32px 0 16px; }
        .cta a { background: linear-gradient(135deg, #ff6b35, #f59e0b); color: #fff !important; text-decoration: none; padding: 14px 36px; border-radius: 12px; font-weight: 800; font-size: 1rem; display: inline-block; }
        .footer { padding: 24px 40px; border-top: 1px solid rgba(255,255,255,0.05); text-align: center; color: #475569; font-size: 0.8rem; }
        .badge { display: inline-block; padding: 4px 12px; border-radius: 20px; font-size: 0.75rem; font-weight: 700; }
        .badge-pct { background: rgba(99,102,241,0.2); color: #818cf8; }
        .badge-fix { background: rgba(245,158,11,0.2); color: #fbbf24; }
    </style>
</head>
<body>
<div class="wrapper">
    <div class="card">
        <div class="hero">
            <span class="hero-icon">🏷️</span>
            <h1>You've Got a Promo Code!</h1>
            <p>A special discount is waiting just for you on BiteHub.</p>
        </div>
        <div class="body">
            <p style="color:#94a3b8; margin-bottom:20px;">Hey there, food lover! We're excited to share an exclusive promo code with you. Use it on your next order to enjoy a great discount.</p>

            <div class="code-box">
                <div class="code-label">Your Promo Code</div>
                <div class="code-text">{{ $promo->Code }}</div>
                @if($promo->Type === 'Percentage')
                    <div style="margin-top:8px;"><span class="badge badge-pct">{{ $promo->Value }}% OFF</span></div>
                @else
                    <div style="margin-top:8px;"><span class="badge badge-fix">{{ number_format($promo->Value, 2) }} EGP OFF</span></div>
                @endif
            </div>

            <div class="details-grid">
                <div class="detail-item">
                    <div class="detail-label">Discount</div>
                    <div class="detail-value">
                        @if($promo->Type === 'Percentage')
                            {{ $promo->Value }}%
                        @else
                            {{ number_format($promo->Value, 2) }} EGP
                        @endif
                    </div>
                </div>
                <div class="detail-item">
                    <div class="detail-label">Min. Order</div>
                    <div class="detail-value">{{ number_format($promo->MinOrderAmount, 2) }} EGP</div>
                </div>
                <div class="detail-item">
                    <div class="detail-label">Valid Until</div>
                    <div class="detail-value">
                        @if($promo->ExpiryDate)
                            {{ \Carbon\Carbon::parse($promo->ExpiryDate)->format('d M Y') }}
                        @else
                            No Expiry
                        @endif
                    </div>
                </div>
                <div class="detail-item">
                    <div class="detail-label">Max Uses</div>
                    <div class="detail-value">
                        @if($promo->MaxUses) {{ $promo->MaxUses }} uses @else Unlimited @endif
                    </div>
                </div>
            </div>

            <div class="cta">
                <a href="{{ url('/') }}">Order Now & Use Code</a>
            </div>

            <p style="color:#64748b; font-size:0.82rem; text-align:center;">Simply enter the code <strong style="color:#a5b4fc;">{{ $promo->Code }}</strong> at checkout to apply your discount.</p>
        </div>
        <div class="footer">
            <p>© {{ date('Y') }} BiteHub. All rights reserved.</p>
            <p style="margin-top:6px;">You're receiving this because you have an account with us.</p>
        </div>
    </div>
</div>
</body>
</html>
