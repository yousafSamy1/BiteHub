<!DOCTYPE html>
<html lang="en" data-theme="dark" id="htmlRoot">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('error_code', 'Error') · BiteHub</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" crossorigin="anonymous">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800;900&display=swap" rel="stylesheet">
    {{-- Read theme BEFORE paint to avoid flash --}}
    <script>
        (function(){
            var t = localStorage.getItem('bitehub_theme') || 'dark';
            document.getElementById('htmlRoot').setAttribute('data-theme', t);
        })();
    </script>
    <style>
        *,*::before,*::after{box-sizing:border-box;margin:0;padding:0}

        /* ══ DARK MODE ══ */
        :root,
        [data-theme="dark"]{
            --bg:            #0a0910;
            --bg-circle:     rgba(255,107,53,.20);
            --bg-circle-b:   rgba(255,107,53,.06);
            --border-circle: rgba(255,107,53,.18);
            --grid-line:     rgba(255,107,53,.04);
            --radial-l:      rgba(255,107,53,.16);
            --radial-r:      rgba(255,167,38,.10);
            --text:          #FFFFFF;
            --text-muted:    rgba(255,255,255,.60);
            --text-faint:    rgba(255,255,255,.38);
            --tagline:       rgba(255,220,190,.80);
            --divider:       rgba(255,107,53,.35);
            --outline-bg:    rgba(255,255,255,.06);
            --outline-bd:    rgba(255,255,255,.14);
            --outline-hover: rgba(255,107,53,.12);
            --outline-hbd:   rgba(255,107,53,.55);
            --status-bar:    rgba(255,255,255,.38);
        }

        /* ══ LIGHT MODE ══ */
        [data-theme="light"]{
            --bg:            #FEF3E8;
            --bg-circle:     rgba(255,107,53,.18);
            --bg-circle-b:   rgba(255,107,53,.05);
            --border-circle: rgba(255,107,53,.28);
            --grid-line:     rgba(180,83,9,.06);
            --radial-l:      rgba(255,107,53,.12);
            --radial-r:      rgba(255,167,38,.10);
            --text:          #1A0A00;
            --text-muted:    #78350F;
            --text-faint:    #92400E;
            --tagline:       #92400E;
            --divider:       rgba(180,83,9,.40);
            --outline-bg:    rgba(180,83,9,.07);
            --outline-bd:    rgba(180,83,9,.28);
            --outline-hover: rgba(255,107,53,.14);
            --outline-hbd:   rgba(180,83,9,.60);
            --status-bar:    #92400E;
        }

        html,body{height:100%;font-family:'Inter',sans-serif;background:var(--bg);color:var(--text);overflow:hidden;transition:background .35s,color .35s;}

        /* ── background ── */
        body::before{
            content:'';position:fixed;inset:0;
            background:radial-gradient(ellipse 70% 70% at 15% 50%,var(--radial-l) 0%,transparent 65%),
                        radial-gradient(ellipse 50% 60% at 85% 50%,var(--radial-r) 0%,transparent 60%);
            pointer-events:none;transition:background .35s;
        }
        /* subtle grid */
        body::after{
            content:'';position:fixed;inset:0;
            background-image:linear-gradient(var(--grid-line) 1px,transparent 1px),
                             linear-gradient(90deg,var(--grid-line) 1px,transparent 1px);
            background-size:50px 50px;
            pointer-events:none;
        }

        /* ── layout ── */
        .page{
            position:relative;z-index:10;
            display:flex;align-items:center;justify-content:center;
            height:100vh;padding:20px;gap:0;
        }

        /* ── left: illustration panel ── */
        .illo-panel{
            flex:1;display:flex;align-items:center;justify-content:center;
            position:relative;max-width:520px;
        }

        /* big warm circle behind chef */
        .illo-circle{
            position:absolute;
            width:360px;height:360px;border-radius:50%;
            background:radial-gradient(circle,var(--bg-circle) 0%,var(--bg-circle-b) 70%,transparent 100%);
            border:1px solid var(--border-circle);
            transition:background .35s,border-color .35s;
        }

        /* chef SVG wrapper */
        .chef-wrap{position:relative;z-index:2;width:320px;}
        .chef-wrap svg{width:100%;height:auto;filter:drop-shadow(0 20px 40px rgba(255,107,53,.25));}

        /* floating utensils */
        .utensil{
            position:absolute;font-size:1.8rem;
            animation:floatItem 4s ease-in-out infinite;
        }
        .utensil:nth-child(1){top:8%;left:12%;animation-delay:0s;}
        .utensil:nth-child(2){top:15%;right:10%;animation-delay:.8s;}
        .utensil:nth-child(3){bottom:18%;left:8%;animation-delay:1.5s;}
        .utensil:nth-child(4){bottom:25%;right:5%;animation-delay:.4s;}
        @keyframes floatItem{0%,100%{transform:translateY(0) rotate(0deg);}50%{transform:translateY(-12px) rotate(8deg);}}

        /* steam lines */
        .steam{
            position:absolute;bottom:52%;left:50%;
            display:flex;gap:8px;transform:translateX(-50%);
        }
        .steam-line{
            width:3px;border-radius:3px;
            background:linear-gradient(to top,rgba(255,107,53,.6),transparent);
            animation:steamRise 2s ease-in-out infinite;
        }
        .steam-line:nth-child(1){height:30px;animation-delay:0s;}
        .steam-line:nth-child(2){height:22px;animation-delay:.4s;}
        .steam-line:nth-child(3){height:28px;animation-delay:.8s;}
        @keyframes steamRise{0%,100%{opacity:.8;transform:translateY(0) scaleX(1);}50%{opacity:.3;transform:translateY(-14px) scaleX(1.4);}}

        /* ── divider ── */
        .divider{
            width:1px;height:280px;
            background:linear-gradient(to bottom,transparent,var(--divider),transparent);
            flex-shrink:0;margin:0 40px;transition:background .35s;
        }

        /* ── right: error content ── */
        .content-panel{
            flex:1;max-width:460px;
            display:flex;flex-direction:column;align-items:flex-start;gap:20px;
        }

        /* logo */
        .error-logo{
            display:inline-flex;align-items:center;gap:8px;
            text-decoration:none;color:var(--text);
            font-size:1.3rem;font-weight:900;letter-spacing:-0.5px;
            margin-bottom:8px;transition:color .35s;
        }
        .error-logo i{color:#FF6B35;filter:drop-shadow(0 0 8px #FF6B35);}
        .error-logo span{color:#FF6B35;}

        /* 404 display */
        .error-number{
            display:flex;align-items:center;gap:6px;
            line-height:1;
        }
        .digit{
            font-size:clamp(5.5rem,12vw,9rem);
            font-weight:900;letter-spacing:-4px;
            background:linear-gradient(135deg,#FF6B35 0%,#FFA726 100%);
            -webkit-background-clip:text;-webkit-text-fill-color:transparent;
            background-clip:text;
            filter:drop-shadow(0 4px 24px rgba(255,107,53,.5));
            animation:digitPulse 3s ease-in-out infinite;
        }
        @keyframes digitPulse{0%,100%{filter:drop-shadow(0 4px 24px rgba(255,107,53,.5));}50%{filter:drop-shadow(0 4px 40px rgba(255,107,53,.85));}}

        /* food plate replacing "0" */
        .food-zero{
            width:clamp(80px,12vw,130px);
            height:clamp(80px,12vw,130px);
            position:relative;
            animation:foodSpin 8s linear infinite;
        }
        @keyframes foodSpin{0%{transform:rotate(-4deg);}50%{transform:rotate(4deg);}100%{transform:rotate(-4deg);}}
        .food-zero svg{width:100%;height:100%;}

        /* tag line */
        .error-tagline{
            font-size:clamp(1rem,2.5vw,1.3rem);
            color:var(--tagline);
            font-style:italic;
            font-weight:500;
            transition:color .35s;
        }

        /* title */
        .error-title{
            font-size:clamp(1.5rem,3.5vw,2.2rem);
            font-weight:800;letter-spacing:-.5px;
            line-height:1.25;color:var(--text);transition:color .35s;
        }

        /* desc */
        .error-desc{
            font-size:.95rem;
            color:var(--text-muted);
            line-height:1.75;
            max-width:380px;transition:color .35s;
        }

        /* buttons */
        .error-actions{display:flex;gap:12px;flex-wrap:wrap;margin-top:8px;}
        .btn-primary-err{
            display:inline-flex;align-items:center;gap:9px;
            padding:13px 28px;border-radius:50px;
            background:linear-gradient(135deg,#FF6B35,#FFA726);
            color:#fff;font-weight:700;font-size:.95rem;
            text-decoration:none;border:none;cursor:pointer;
            font-family:'Inter',sans-serif;
            box-shadow:0 8px 28px rgba(255,107,53,.45);
            transition:all .3s;
        }
        .btn-primary-err:hover{transform:translateY(-3px) scale(1.04);box-shadow:0 14px 40px rgba(255,107,53,.6);color:#fff;}
        .btn-outline-err{
            display:inline-flex;align-items:center;gap:9px;
            padding:13px 28px;border-radius:50px;
            background:var(--outline-bg);
            color:var(--text);font-weight:600;font-size:.95rem;
            text-decoration:none;border:1px solid var(--outline-bd);
            cursor:pointer;font-family:'Inter',sans-serif;
            transition:all .3s;backdrop-filter:blur(8px);
        }
        .btn-outline-err:hover{border-color:var(--outline-hbd);background:var(--outline-hover);transform:translateY(-3px);color:var(--text);}

        /* ── Report Bug button special look ── */
        .btn-report-err{
            display:inline-flex;align-items:center;gap:9px;
            padding:13px 28px;border-radius:50px;
            background:rgba(239,68,68,.12);
            color:#f87171;font-weight:600;font-size:.95rem;
            border:1px solid rgba(239,68,68,.35);
            cursor:pointer;font-family:'Inter',sans-serif;
            transition:all .3s;backdrop-filter:blur(8px);
        }
        .btn-report-err:hover{background:rgba(239,68,68,.22);border-color:rgba(239,68,68,.7);transform:translateY(-3px);color:#fca5a5;}
        .btn-report-err:disabled{opacity:.5;cursor:not-allowed;transform:none;}

        /* ── Toast Notification ── */
        .err-toast{
            position:fixed;bottom:30px;left:50%;transform:translateX(-50%) translateY(80px);
            background:#1e1b4b;border:1px solid rgba(139,92,246,.5);
            color:#fff;padding:14px 28px;border-radius:50px;
            font-size:.9rem;font-weight:600;font-family:'Inter',sans-serif;
            display:flex;align-items:center;gap:10px;
            box-shadow:0 8px 32px rgba(0,0,0,.4);
            transition:transform .4s cubic-bezier(.34,1.56,.64,1),opacity .3s;
            opacity:0;z-index:9999;white-space:nowrap;
        }
        .err-toast.show{transform:translateX(-50%) translateY(0);opacity:1;}
        .err-toast.success{background:#052e16;border-color:rgba(34,197,94,.5);}
        .err-toast.error-t{background:#2d0a0a;border-color:rgba(239,68,68,.5);}

        /* bottom bar */
        .status-bar{
            display:flex;align-items:center;gap:16px;
            font-size:.72rem;color:var(--status-bar);
            margin-top:10px;transition:color .35s;
        }
        .sdot{width:6px;height:6px;border-radius:50%;background:#f87171;
              box-shadow:0 0 8px #f87171;animation:sdotPulse 1.5s infinite;}
        @keyframes sdotPulse{0%,100%{opacity:1;}50%{opacity:.3;}}

        /* ── light mode: recolour SVG elements ── */
        [data-theme="light"] .pot-body { fill: #D97706 !important; }
        [data-theme="light"] .pot-rim  { fill: #B45309 !important; }
        [data-theme="light"] .body-suit{ fill: #1C1917 !important; }
        [data-theme="light"] .shoe-fill{ fill: #1C1917 !important; }
        /* lighten chef hat & apron sparkles in light mode */
        [data-theme="light"] .chef-wrap svg { filter: drop-shadow(0 16px 32px rgba(180,83,9,.30)); }
        /* digit shadow in light mode is more subtle */
        [data-theme="light"] .digit { filter: drop-shadow(0 4px 18px rgba(180,83,9,.45)); }
        [data-theme="light"] .sdot  { background:#DC2626; box-shadow:0 0 8px #DC2626; }

        /* theme toggle button */
        .theme-btn{
            position:fixed;top:22px;right:22px;z-index:100;
            width:40px;height:40px;border-radius:50%;
            background:var(--outline-bg);border:1px solid var(--outline-bd);
            color:var(--text);cursor:pointer;font-size:1rem;
            display:flex;align-items:center;justify-content:center;
            transition:all .3s;backdrop-filter:blur(8px);
        }
        .theme-btn:hover{background:var(--outline-hover);border-color:var(--outline-hbd);transform:scale(1.1);}

        /* ── responsive ── */
        @media(max-width:768px){
            html,body{overflow:auto;}
            .page{flex-direction:column;height:auto;min-height:100vh;padding:30px 20px 50px;}
            .illo-panel{max-width:280px;}
            .illo-circle{width:240px;height:240px;}
            .chef-wrap{width:220px;}
            .divider{width:200px;height:1px;margin:0;}
            .content-panel{align-items:center;text-align:center;}
            .error-desc{text-align:center;}
        }
    </style>
</head>
<body>
<div class="page">

    {{-- ── LEFT: Chef Illustration ── --}}
    <div class="illo-panel">
        <div class="illo-circle"></div>

        {{-- floating food items --}}
        <span class="utensil">🍕</span>
        <span class="utensil">🥗</span>
        <span class="utensil">🍜</span>
        <span class="utensil">🧁</span>

        <div class="chef-wrap">
            {{-- SVG Chef --}}
            <svg viewBox="0 0 300 420" fill="none" xmlns="http://www.w3.org/2000/svg">
                <!-- Chef Hat -->
                <ellipse cx="150" cy="95" rx="48" ry="12" fill="#f8fafc" opacity=".9"/>
                <rect x="116" y="50" width="68" height="50" rx="10" fill="#f8fafc" opacity=".9"/>
                <ellipse cx="150" cy="52" rx="36" ry="18" fill="white"/>
                <ellipse cx="134" cy="48" rx="18" ry="22" fill="white"/>
                <ellipse cx="166" cy="48" rx="18" ry="22" fill="white"/>
                <!-- Head -->
                <ellipse cx="150" cy="130" rx="36" ry="38" fill="#FDDCB5"/>
                <!-- Face features -->
                <ellipse cx="140" cy="127" rx="4" ry="5" fill="#1a1a2e"/>
                <ellipse cx="160" cy="127" rx="4" ry="5" fill="#1a1a2e"/>
                <path d="M140 145 Q150 154 160 145" stroke="#d97706" stroke-width="2.5" stroke-linecap="round" fill="none"/>
                <!-- Left eyebrow -->
                <path d="M134 119 Q140 115 146 119" stroke="#92400e" stroke-width="2" stroke-linecap="round" fill="none"/>
                <!-- Right eyebrow -->
                <path d="M154 119 Q160 115 166 119" stroke="#92400e" stroke-width="2" stroke-linecap="round" fill="none"/>
                <!-- Blush -->
                <ellipse cx="132" cy="137" rx="8" ry="5" fill="rgba(255,107,53,.25)"/>
                <ellipse cx="168" cy="137" rx="8" ry="5" fill="rgba(255,107,53,.25)"/>
                <!-- Neck -->
                <rect x="138" y="164" width="24" height="20" rx="6" fill="#FDDCB5"/>
                <!-- Body / Apron -->
                <path class="body-suit" d="M90 200 Q80 260 85 320 Q150 335 215 320 Q220 260 210 200 Q180 185 150 183 Q120 185 90 200Z" fill="#1e1b4b"/>
                <!-- Apron overlay -->
                <path d="M118 195 Q110 250 112 315 Q150 325 188 315 Q190 250 182 195 Q165 188 150 187 Q135 188 118 195Z" fill="#FF6B35" opacity=".85"/>
                <!-- Apron pocket -->
                <rect x="132" y="270" width="36" height="28" rx="8" fill="rgba(0,0,0,.2)"/>
                <!-- Left Arm - raised up with ladle -->
                <path d="M95 205 Q60 195 45 165 Q38 148 50 140 Q62 132 68 148 Q72 162 88 185" fill="#FDDCB5" stroke="#FDDCB5" stroke-width="2"/>
                <!-- Ladle handle -->
                <line x1="50" y1="138" x2="25" y2="105" stroke="#b45309" stroke-width="5" stroke-linecap="round"/>
                <!-- Ladle head -->
                <ellipse cx="20" cy="98" rx="14" ry="10" fill="#d97706" opacity=".9"/>
                <!-- Right Arm - holding toward pot -->
                <path d="M205 205 Q240 215 252 240 Q258 255 248 262 Q238 268 228 252 Q218 238 208 218" fill="#FDDCB5" stroke="#FDDCB5" stroke-width="2"/>
                <!-- Legs -->
                <rect class="body-suit" x="120" y="315" width="28" height="70" rx="14" fill="#1e1b4b"/>
                <rect class="body-suit" x="152" y="315" width="28" height="70" rx="14" fill="#1e1b4b"/>
                <!-- Shoes -->
                <ellipse class="shoe-fill" cx="134" cy="384" rx="20" ry="10" fill="#111"/>
                <ellipse class="shoe-fill" cx="166" cy="384" rx="20" ry="10" fill="#111"/>
                <!-- Pot -->
                <ellipse class="pot-rim" cx="220" cy="340" rx="50" ry="14" fill="#374151"/>
                <rect class="pot-body" x="170" y="290" width="100" height="55" rx="12" fill="#4b5563"/>
                <rect class="pot-rim" x="165" y="285" width="110" height="16" rx="8" fill="#374151"/>
                <!-- Pot handles -->
                <rect class="pot-rim" x="155" y="293" width="16" height="10" rx="5" fill="#374151"/>
                <rect class="pot-rim" x="269" y="293" width="16" height="10" rx="5" fill="#374151"/>
                <!-- Soup/liquid -->
                <ellipse cx="220" cy="285" rx="46" ry="10" fill="#FF6B35" opacity=".7"/>
                <!-- Bubbles on soup -->
                <circle cx="205" cy="282" r="4" fill="#FFA726" opacity=".8"/>
                <circle cx="225" cy="278" r="3" fill="#FFA726" opacity=".6"/>
                <circle cx="240" cy="281" r="5" fill="#FFA726" opacity=".7"/>
                <!-- Flame under pot -->
                <path d="M195 345 Q200 360 210 355 Q205 370 220 365 Q215 378 230 372 Q225 385 240 378 Q248 360 240 345" fill="#FF6B35" opacity=".8"/>
                <path d="M205 348 Q208 358 215 355 Q212 365 222 362 Q220 372 228 368 Q230 355 225 348" fill="#FFA726" opacity=".9"/>
                <!-- Sparkles -->
                <text x="75" y="210" font-size="18" opacity=".7">✦</text>
                <text x="248" y="195" font-size="14" opacity=".5">✦</text>
                <text x="100" y="250" font-size="10" opacity=".4">✦</text>
            </svg>

            {{-- Steam --}}
            <div class="steam">
                <div class="steam-line"></div>
                <div class="steam-line"></div>
                <div class="steam-line"></div>
            </div>
        </div>
    </div>

    {{-- Divider --}}
    <div class="divider"></div>

    {{-- ── RIGHT: Error Content ── --}}
    <div class="content-panel">

        <a href="{{ url('/') }}" class="error-logo">
            <i class="fas fa-fire"></i><span>Bite</span>Hub
        </a>

        {{-- Hidden error code for JS --}}
        <input type="hidden" id="errorCodeValue" value="@yield('error_code','404')">

        {{-- Dynamic Error Number --}}
        @php
            $code = $__env->yieldContent('error_code', '404');
            $digits = str_split((string)$code);
            $iconMap = [
                '404' => ['type'=>'plate',   'color'=>'#FF6B35'],
                '403' => ['type'=>'lock',    'color'=>'#f59e0b'],
                '500' => ['type'=>'fire',    'color'=>'#ef4444'],
                '503' => ['type'=>'fire',    'color'=>'#ef4444'],
                '419' => ['type'=>'clock',   'color'=>'#8b5cf6'],
                '429' => ['type'=>'warning', 'color'=>'#f59e0b'],
            ];
            $icon = $iconMap[$code] ?? ['type'=>'plate','color'=>'#FF6B35'];
        @endphp

        <div class="error-number">
            {{-- First digit --}}
            @if(isset($digits[0]))
                <span class="digit">{{ $digits[0] }}</span>
            @endif

            {{-- Middle icon --}}
            <div class="food-zero">
                @if($icon['type'] === 'plate')
                <svg viewBox="0 0 130 130" xmlns="http://www.w3.org/2000/svg">
                    <circle cx="65" cy="65" r="60" fill="#374151" opacity=".9"/>
                    <circle cx="65" cy="65" r="55" fill="#4b5563"/>
                    <circle cx="65" cy="65" r="50" fill="#f3f4f6"/>
                    <ellipse cx="65" cy="72" rx="36" ry="28" fill="white"/>
                    <ellipse cx="58" cy="70" rx="14" ry="10" fill="white"/>
                    <circle cx="65" cy="65" r="18" fill="#FFA726"/>
                    <circle cx="65" cy="65" r="14" fill="#FF6B35"/>
                    <circle cx="59" cy="60" r="4" fill="#FFC107" opacity=".7"/>
                    <circle cx="65" cy="65" r="60" fill="none" stroke="rgba(255,107,53,.4)" stroke-width="3"/>
                </svg>
                @elseif($icon['type'] === 'lock')
                <svg viewBox="0 0 130 130" xmlns="http://www.w3.org/2000/svg">
                    <circle cx="65" cy="65" r="60" fill="#374151" opacity=".9"/>
                    <circle cx="65" cy="65" r="55" fill="#1e1b4b"/>
                    <rect x="38" y="60" width="54" height="42" rx="10" fill="#f59e0b" opacity=".9"/>
                    <path d="M48 60 V48 A17 17 0 0 1 82 48 V60" stroke="#fbbf24" stroke-width="8" fill="none" stroke-linecap="round"/>
                    <circle cx="65" cy="82" r="8" fill="#1e1b4b"/>
                    <rect x="62" y="82" width="6" height="10" rx="3" fill="#1e1b4b"/>
                    <circle cx="65" cy="65" r="60" fill="none" stroke="rgba(245,158,11,.4)" stroke-width="3"/>
                </svg>
                @elseif($icon['type'] === 'fire')
                <svg viewBox="0 0 130 130" xmlns="http://www.w3.org/2000/svg">
                    <circle cx="65" cy="65" r="60" fill="#374151" opacity=".9"/>
                    <circle cx="65" cy="65" r="55" fill="#1c1917"/>
                    <path d="M65 25 C65 25 85 45 80 62 C90 52 88 38 88 38 C102 55 98 80 85 90 C88 80 80 74 80 74 C82 90 72 100 65 103 C58 100 48 90 50 74 C50 74 42 80 45 90 C32 80 28 55 42 38 C42 38 40 52 50 62 C45 45 65 25 65 25Z" fill="#ef4444" opacity=".9"/>
                    <path d="M65 50 C65 50 75 62 72 72 C78 66 77 57 77 57 C84 66 82 80 75 86 C77 79 72 76 72 76 C73 83 68 88 65 90 C62 88 57 83 58 76 C58 76 53 79 55 86 C48 80 46 66 53 57 C53 57 52 66 58 72 C55 62 65 50 65 50Z" fill="#fbbf24" opacity=".85"/>
                    <circle cx="65" cy="65" r="60" fill="none" stroke="rgba(239,68,68,.4)" stroke-width="3"/>
                </svg>
                @elseif($icon['type'] === 'clock')
                <svg viewBox="0 0 130 130" xmlns="http://www.w3.org/2000/svg">
                    <circle cx="65" cy="65" r="60" fill="#374151" opacity=".9"/>
                    <circle cx="65" cy="65" r="55" fill="#1e1b4b"/>
                    <circle cx="65" cy="65" r="42" fill="none" stroke="#8b5cf6" stroke-width="5" opacity=".8"/>
                    <line x1="65" y1="65" x2="65" y2="35" stroke="#a78bfa" stroke-width="5" stroke-linecap="round"/>
                    <line x1="65" y1="65" x2="88" y2="72" stroke="#c4b5fd" stroke-width="4" stroke-linecap="round"/>
                    <circle cx="65" cy="65" r="5" fill="#8b5cf6"/>
                    <circle cx="65" cy="65" r="60" fill="none" stroke="rgba(139,92,246,.4)" stroke-width="3"/>
                </svg>
                @elseif($icon['type'] === 'warning')
                <svg viewBox="0 0 130 130" xmlns="http://www.w3.org/2000/svg">
                    <circle cx="65" cy="65" r="60" fill="#374151" opacity=".9"/>
                    <circle cx="65" cy="65" r="55" fill="#1c1917"/>
                    <path d="M65 28 L105 100 H25 Z" fill="#f59e0b" opacity=".9"/>
                    <path d="M65 40 L97 95 H33 Z" fill="#fbbf24" opacity=".5"/>
                    <rect x="61" y="56" width="8" height="26" rx="4" fill="#1c1917"/>
                    <circle cx="65" cy="90" r="5" fill="#1c1917"/>
                    <circle cx="65" cy="65" r="60" fill="none" stroke="rgba(245,158,11,.4)" stroke-width="3"/>
                </svg>
                @endif
            </div>

            {{-- Remaining digits --}}
            @foreach(array_slice($digits, 1) as $d)
                <span class="digit">{{ $d }}</span>
            @endforeach
        </div>

        <p class="error-tagline">"@yield('error_tagline', 'Looks like this page is still cooking!')"</p>

        <h1 class="error-title">@yield('error_title', 'Page Not Found')</h1>

        <p class="error-desc">@yield('error_desc', 'The page you\'re looking for doesn\'t exist or has been moved to another URL. Let\'s get you back on track!')</p>

        <div class="error-actions">
            <a href="{{ url('/') }}" class="btn-primary-err">
                <i class="fas fa-house"></i> Back to Home
            </a>
            <button onclick="history.back()" class="btn-outline-err">
                <i class="fas fa-arrow-left"></i> Go Back
            </button>
            <button onclick="reportError()" id="reportBtn" class="btn-report-err">
                <i class="fas fa-bug"></i> Report Problem
            </button>
            @yield('extra_actions')
        </div>

        <div class="status-bar">
            <span class="sdot"></span>
            <span>Error <strong style="color:#FF6B35">@yield('error_code','404')</strong></span>
            <span>·</span>
            <span>BiteHub Platform</span>
            <span>·</span>
            <span>{{ now()->format('H:i') }}</span>
        </div>
    </div>

</div>

{{-- Toast notification --}}
<div class="err-toast" id="errToast"></div>

{{-- Theme toggle button --}}
<button class="theme-btn" id="themeBtn" onclick="toggleErrTheme()" title="Toggle Theme">
    <i class="fas fa-sun" id="errThemeIcon"></i>
</button>

<script>
// ── Sync with BiteHub's theme system ──────────────────────
function applyErrTheme(t) {
    var html = document.getElementById('htmlRoot');
    html.setAttribute('data-theme', t);
    document.getElementById('errThemeIcon').className =
        t === 'dark' ? 'fas fa-sun' : 'fas fa-moon';
}

function toggleErrTheme() {
    var current = document.getElementById('htmlRoot').getAttribute('data-theme');
    var next    = current === 'dark' ? 'light' : 'dark';
    localStorage.setItem('bitehub_theme', next);
    applyErrTheme(next);
}

// Apply on load (already set inline to avoid flash, just sync icon)
(function(){
    var t = localStorage.getItem('bitehub_theme') || 'dark';
    applyErrTheme(t);
})();

// ── Toast helper ──────────────────────────────────────────
function showToast(msg, type) {
    var el = document.getElementById('errToast');
    el.innerHTML = (type === 'success'
        ? '<i class="fas fa-check-circle" style="color:#4ade80"></i>'
        : '<i class="fas fa-times-circle" style="color:#f87171"></i>')
        + ' ' + msg;
    el.className = 'err-toast ' + (type === 'success' ? 'success' : 'error-t');
    void el.offsetWidth;
    el.classList.add('show');
    setTimeout(function(){ el.classList.remove('show'); }, 3500);
}

// ── Report Error ──────────────────────────────────────────
function reportError() {
    var btn = document.getElementById('reportBtn');
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Sending...';

    // Get CSRF token from meta tag
    var csrf = document.querySelector('meta[name="csrf-token"]');
    var token = csrf ? csrf.getAttribute('content') : '';

    var errorCode = document.getElementById('errorCodeValue') ?
        document.getElementById('errorCodeValue').value : 'Unknown';

    console.log("Sending error report to /report-error", {url: window.location.href, error_code: errorCode});

    fetch('/report-error', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': token,
            'Accept': 'application/json'
        },
        body: JSON.stringify({
            url: window.location.href,
            error_code: errorCode
        })
    })
    .then(function(res){ 
        console.log("Response status:", res.status);
        return res.json(); 
    })
    .then(function(data){
        console.log("Response data:", data);
        showToast('Report sent! Our team will look into this.', 'success');
        btn.innerHTML = '<i class="fas fa-check"></i> Reported!';
        btn.style.color = '#4ade80';
        btn.style.borderColor = 'rgba(74,222,128,.5)';
    })
    .catch(function(err){
        console.error("Error sending report:", err);
        showToast('Failed to send report. Please try again.', 'error');
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-bug"></i> Report Problem';
    });
}
</script>
</body>
</html>
