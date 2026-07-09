<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Goodbye — BiteHub</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700;900&family=Inter:wght@300;400;600&display=swap"
        rel="stylesheet">

    <style>
        *,
        *::before,
        *::after {
            margin: 0;
            padding: 0;
            box-sizing: border-box
        }

        html,
        body {
            width: 100%;
            height: 100%;
            overflow: hidden;
            background: #080810;
            font-family: 'Inter', sans-serif
        }

        /* ─────────────────────────── CANVAS ──────────────────────────────────── */
        #bgCanvas {
            position: fixed;
            inset: 0;
            z-index: 0;
            opacity: .7
        }

        /* ─────────────────────── CURTAIN PANELS ──────────────────────────────── */
        .curtain-left,
        .curtain-right {
            position: fixed;
            top: 0;
            bottom: 0;
            width: 52%;
            z-index: 30;
            will-change: transform;
        }

        .curtain-left {
            left: 0;
            transform: translateX(-103%);
            animation: swingLeft 1.1s cubic-bezier(.77, 0, .175, 1) 0.2s forwards
        }

        .curtain-right {
            right: 0;
            transform: translateX(103%);
            animation: swingRight 1.1s cubic-bezier(.77, 0, .175, 1) 0.2s forwards
        }

        .curtain-inner {
            position: absolute;
            inset: 0;
            background:
                linear-gradient(to right,
                    #1a0500 0%,
                    #280b00 30%,
                    #3d1500 60%,
                    #280b00 80%,
                    #1a0500 100%);
        }

        /* Fabric folds via repeating gradient */
        .curtain-inner::before {
            content: '';
            position: absolute;
            inset: 0;
            background: repeating-linear-gradient(to right,
                    rgba(255, 255, 255, .000) 0px,
                    rgba(255, 130, 30, .04) 12px,
                    rgba(255, 255, 255, .000) 24px,
                    rgba(200, 80, 10, .025) 36px,
                    rgba(255, 255, 255, .000) 48px);
        }

        /* Gold trim at the meeting edge */
        .curtain-left .curtain-inner::after {
            content: '';
            position: absolute;
            top: 0;
            right: 0;
            bottom: 0;
            width: 4px;
            background: linear-gradient(to bottom, transparent, rgba(255, 180, 60, .6) 30%, rgba(255, 140, 30, .9) 50%, rgba(255, 180, 60, .6) 70%, transparent);
        }

        .curtain-right .curtain-inner::after {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            bottom: 0;
            width: 4px;
            background: linear-gradient(to bottom, transparent, rgba(255, 180, 60, .6) 30%, rgba(255, 140, 30, .9) 50%, rgba(255, 180, 60, .6) 70%, transparent);
        }

        /* Pelmet (top decorative rod) */
        .curtain-inner .pelmet {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 14px;
            background: linear-gradient(to right, #2a0a00, #c06010 50%, #2a0a00);
            box-shadow: 0 4px 20px rgba(255, 120, 0, .3);
        }

        @keyframes swingLeft {
            to {
                transform: translateX(0)
            }
        }

        @keyframes swingRight {
            to {
                transform: translateX(0)
            }
        }

        /* ─────────────────────── MAIN STAGE ──────────────────────────────────── */
        .stage {
            position: fixed;
            inset: 0;
            z-index: 50;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            text-align: center;
            padding: 32px;
            opacity: 0;
            animation: appear .9s ease 1.5s forwards;
        }

        /* ── Subtle radial light behind content ── */
        .stage::before {
            content: '';
            position: absolute;
            inset: 0;
            z-index: -1;
            background: radial-gradient(ellipse 55% 55% at 50% 50%, rgba(255, 100, 30, .09) 0%, transparent 70%);
            pointer-events: none;
        }

        /* ── Logo ── */
        .brand {
            font-family: 'Playfair Display', serif;
            font-size: 2.2rem;
            font-weight: 900;
            letter-spacing: -.5px;
            color: #fff;
            margin-bottom: 36px;
            line-height: 1;
        }

        .brand em {
            color: #ff6b35;
            font-style: normal
        }

        .brand .tagline-brand {
            display: block;
            font-family: 'Inter', sans-serif;
            font-size: .7rem;
            font-weight: 300;
            letter-spacing: 4px;
            text-transform: uppercase;
            color: rgba(255, 255, 255, .35);
            margin-top: 4px;
        }

        /* ── Dish ── */
        .dish-scene {
            position: relative;
            width: 160px;
            height: 160px;
            margin: 0 auto 32px;
            animation: levitate 4s ease-in-out 1.8s infinite;
        }

        .dish-plate {
            position: absolute;
            bottom: 0;
            left: 50%;
            transform: translateX(-50%);
            width: 140px;
            height: 28px;
            background: linear-gradient(to bottom, #d4d4d4, #888);
            border-radius: 50%;
            box-shadow: 0 8px 40px rgba(0, 0, 0, .6), inset 0 2px 4px rgba(255, 255, 255, .3);
        }

        .dish-dome {
            position: absolute;
            bottom: 18px;
            left: 50%;
            transform: translateX(-50%);
            width: 120px;
            height: 80px;
            background: linear-gradient(135deg, rgba(230, 230, 230, .15), rgba(200, 200, 200, .05));
            border-radius: 60px 60px 0 0;
            border: 1px solid rgba(255, 255, 255, .08);
            backdrop-filter: blur(6px);
            box-shadow: inset 0 1px 2px rgba(255, 255, 255, .12);
        }

        .food-emoji {
            position: absolute;
            bottom: 22px;
            left: 50%;
            transform: translateX(-50%);
            font-size: 2.8rem;
            z-index: 1;
            filter: drop-shadow(0 4px 12px rgba(255, 100, 0, .4));
            animation: wobble 3s ease-in-out 1.8s infinite;
        }

        /* Steam particles rendered on canvas overlay */
        #steamCanvas {
            position: absolute;
            bottom: 70px;
            left: 50%;
            transform: translateX(-50%);
            width: 120px;
            height: 90px;
            opacity: .7
        }

        /* ── Headline ── */
        h1 {
            font-family: 'Playfair Display', serif;
            font-size: 2.4rem;
            font-weight: 900;
            color: #fff;
            margin-bottom: 8px;
            line-height: 1.15;
        }

        h1 span {
            background: linear-gradient(90deg, #ff6b35, #ffa726);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .sub {
            font-size: .95rem;
            font-weight: 300;
            color: rgba(255, 255, 255, .42);
            margin-bottom: 38px;
            max-width: 300px;
            line-height: 1.6;
        }

        /* ── Countdown ring ── */
        .ring-wrap {
            position: relative;
            width: 72px;
            height: 72px;
            margin: 0 auto 28px
        }

        .ring-wrap svg {
            transform: rotate(-90deg)
        }

        .rg-bg {
            fill: none;
            stroke: rgba(255, 255, 255, .07);
            stroke-width: 5
        }

        .rg-fill {
            fill: none;
            stroke: url(#grad);
            stroke-width: 5;
            stroke-linecap: round;
            stroke-dasharray: 195;
            stroke-dashoffset: 195;
            animation: ring 5s linear 1.5s forwards;
        }

        .ring-num {
            position: absolute;
            inset: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.45rem;
            font-weight: 700;
            color: #fff;
        }

        /* ── Divider dots ── */
        .dots {
            display: flex;
            gap: 8px;
            justify-content: center;
            margin-bottom: 18px
        }

        .dot {
            width: 6px;
            height: 6px;
            border-radius: 50%;
            background: rgba(255, 107, 53, .4);
            animation: dotPulse 1.6s ease-in-out infinite;
        }

        .dot:nth-child(2) {
            animation-delay: .2s;
            background: rgba(255, 167, 38, .5)
        }

        .dot:nth-child(3) {
            animation-delay: .4s;
            background: rgba(255, 107, 53, .4)
        }

        .note {
            font-size: .75rem;
            font-weight: 300;
            letter-spacing: 1.5px;
            text-transform: uppercase;
            color: rgba(255, 255, 255, .2)
        }

        /* ─────────────────────── KEYFRAMES ───────────────────────────────────── */
        @keyframes appear {
            from {
                opacity: 0;
                transform: translateY(12px)
            }

            to {
                opacity: 1;
                transform: none
            }
        }

        @keyframes levitate {

            0%,
            100% {
                transform: translateY(0)
            }

            50% {
                transform: translateY(-12px)
            }
        }

        @keyframes wobble {

            0%,
            100% {
                transform: translateX(-50%) rotate(-3deg)
            }

            50% {
                transform: translateX(-50%) rotate(3deg)
            }
        }

        @keyframes ring {
            to {
                stroke-dashoffset: 0
            }
        }

        @keyframes dotPulse {

            0%,
            100% {
                opacity: .4;
                transform: scale(1)
            }

            50% {
                opacity: 1;
                transform: scale(1.4)
            }
        }
    </style>
</head>

<body>

    {{-- Ambient background canvas --}}
    <canvas id="bgCanvas"></canvas>

    {{-- Curtains --}}
    <div class="curtain-left">
        <div class="curtain-inner">
            <div class="pelmet"></div>
        </div>
    </div>
    <div class="curtain-right">
        <div class="curtain-inner">
            <div class="pelmet"></div>
        </div>
    </div>

    {{-- Main content --}}
    <div class="stage">

        <div class="brand">
            <em>Bite</em>Hub
            <span class="tagline-brand">Home Kitchen Platform</span>
        </div>

        <div class="dish-scene">
            <canvas id="steamCanvas" width="120" height="90"></canvas>
            <div class="food-emoji">🍲</div>
            <div class="dish-dome"></div>
            <div class="dish-plate"></div>
        </div>

        <h1>Until Next <span>Bite!</span></h1>
        <p class="sub">You've been logged out. Come back soon — your favourite kitchen is waiting.</p>

        <div class="dots">
            <div class="dot"></div>
            <div class="dot"></div>
            <div class="dot"></div>
        </div>

        <div class="note">Redirecting to home</div>

    </div>

    <script>
        /* ── Ambient background: slow moving orbs / bokeh ─────────────────────── */
        (function () {
            const c = document.getElementById('bgCanvas');
            const ctx = c.getContext('2d');
            let W, H, orbs = [];
            function resize() { W = c.width = window.innerWidth; H = c.height = window.innerHeight; }
            resize(); window.addEventListener('resize', resize);

            const COLS = ['rgba(255,107,53,', 'rgba(255,160,40,', 'rgba(180,40,10,'];
            for (let i = 0; i < 18; i++) {
                orbs.push({
                    x: Math.random() * 1e4, y: Math.random() * 1e4,
                    r: 60 + Math.random() * 120,
                    dx: (Math.random() - .5) * .3, dy: (Math.random() - .5) * .3,
                    c: COLS[i % COLS.length],
                    a: .04 + Math.random() * .07,
                    phase: Math.random() * Math.PI * 2
                });
            }
            let t = 0;
            function draw() {
                ctx.clearRect(0, 0, W, H);
                t += .008;
                orbs.forEach(o => {
                    o.x += o.dx; o.y += o.dy;
                    if (o.x < -o.r || o.x > W + o.r) o.dx *= -1;
                    if (o.y < -o.r || o.y > H + o.r) o.dy *= -1;
                    const pulse = o.a + Math.sin(t + o.phase) * .02;
                    const g = ctx.createRadialGradient(o.x, o.y, 0, o.x, o.y, o.r);
                    g.addColorStop(0, o.c + pulse + ')');
                    g.addColorStop(1, o.c + '0)');
                    ctx.fillStyle = g; ctx.beginPath(); ctx.arc(o.x, o.y, o.r, 0, Math.PI * 2); ctx.fill();
                });
                requestAnimationFrame(draw);
            }
            draw();
        })();

        /* ── Steam particles ───────────────────────────────────────────────────── */
        (function () {
            const c = document.getElementById('steamCanvas');
            if (!c) return;
            const ctx = c.getContext('2d');
            const W = c.width, H = c.height;
            let particles = [];
            function mkP() {
                return {
                    x: W / 2 + (Math.random() - 0.5) * 20, y: H, vy: -(0.4 + Math.random() * .6),
                    vx: (Math.random() - .5) * .3, r: 2 + Math.random() * 3,
                    a: 0.35 + Math.random() * .2, life: 1
                };
            }
            for (let i = 0; i < 12; i++) { const p = mkP(); p.y = Math.random() * H; particles.push(p); }
            function draw() {
                ctx.clearRect(0, 0, W, H);
                if (Math.random() < .3) particles.push(mkP());
                particles = particles.filter(p => p.life > 0);
                particles.forEach(p => {
                    p.y += p.vy; p.x += p.vx; p.life -= .007; p.r += .03;
                    ctx.beginPath(); ctx.arc(p.x, p.y, p.r, 0, Math.PI * 2);
                    ctx.fillStyle = `rgba(255,255,255,${p.a * p.life})`;
                    ctx.fill();
                });
                requestAnimationFrame(draw);
            }
            draw();
        })();

        /* ── Redirect ──────────────────────────────────────────────── */
        setTimeout(() => {
            window.location.href = '{{ route("frontend.home") }}';
        }, 3000);
    </script>
</body>

</html>