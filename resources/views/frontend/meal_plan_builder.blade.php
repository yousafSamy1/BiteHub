@extends('frontend.layouts.app')
@section('title', 'Build Your Premium Meal Plan')
@section('nav-subs', 'active')

@section('content')
    <!-- Cinematic Adaptive Background -->
    <div class="builder-hero-wrapper">
        <div class="hero-image-layer"
            style="background-image: url('{{ asset('upload/website_assets/meal_plan_bg.png') }}');"></div>
        <div class="hero-overlay-layer"></div>
        <div class="particles-container" id="particles"></div>
    </div>

    <div class="builder-content-container">
        <div class="container" style="max-width: 1350px; position:relative; z-index: 10;">

            <!-- Header Section -->
            <header class="builder-header text-center mb-5">
                <h1 class="highlight mb-2"
                    style="font-size: clamp(2rem, 5vw, 3.5rem); letter-spacing: -1.5px; font-weight: 800;">Subscription
                    <span style="font-weight: 300;">Builder</span>
                </h1>
                <p class="text-secondary mx-auto opacity-75" style="max-width: 600px; font-size: 1rem;">Design your culinary
                    journey. Pick your favorites and set your schedule.</p>
            </header>

            <!-- Horizontal Premium Indicator -->
            <div class="wizard-nav-container mb-5">
                <div class="nav-glass-panel">
                    <div class="wizard-steps-row">
                        <div class="step-item active" data-step="1">
                            <div class="step-icon"><i class="fas fa-store"></i></div>
                            <span class="step-label">Kitchen</span>
                        </div>
                        <div class="step-connector">
                            <div class="fill"></div>
                        </div>
                        <div class="step-item" data-step="2">
                            <div class="step-icon"><i class="fas fa-utensils"></i></div>
                            <span class="step-label">Menu</span>
                        </div>
                        <div class="step-connector">
                            <div class="fill"></div>
                        </div>
                        <div class="step-item" data-step="3">
                            <div class="step-icon"><i class="fas fa-clock"></i></div>
                            <span class="step-label">Schedule</span>
                        </div>
                        <div class="step-connector">
                            <div class="fill"></div>
                        </div>
                        <div class="step-item" data-step="4">
                            <div class="step-icon"><i class="fas fa-check-circle"></i></div>
                            <span class="step-label">Review</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Main Content Card -->
            <div class="builder-glass-case">
                <div id="step1" class="wizard-step active">
                    <div class="step-meta mb-5 text-center">
                        <h2 class="h3 display-title">1. Choose Your Foundation</h2>
                        <p class="opacity-50 small">Select a kitchen that resonates with your taste.</p>
                    </div>
                    <div class="grid grid-4 gap-4" id="kitchenGrid">
                        @foreach($kitchens as $kitchen)
                            @php
                                $rating = $kitchen->AverageRating;
                                $kitchenProfileImg = $kitchen->ProfileImg;
                            @endphp
                            <div class="premium-kitchen-card"
                                onclick="selectKitchen(this, {{ $kitchen->KitchenOwnerID }}, '{{ addslashes($kitchen->KitchenName) }}')">
                                <div class="card-inner">
                                    <div class="card-img-box">
                                        <img src="{{ $kitchenProfileImg }}" alt="{{ $kitchen->KitchenName }}">
                                        <div class="img-tint"></div>
                                        @if($kitchen->VerifyStatus === 'Verified')
                                            <div class="ver-badge"><i class="fas fa-check"></i> Verified</div>
                                        @endif
                                    </div>
                                    <div class="card-body-box">
                                        <div class="k-badge">{{ $kitchen->Location ?? 'Cairo' }}</div>
                                        <h3 class="k-name">{{ $kitchen->KitchenName }}</h3>
                                        <div class="k-stats">
                                            <span><i class="fas fa-star text-warning me-1"></i> {{ $rating }}</span>
                                            <span><i class="fas fa-clock text-primary me-1"></i>
                                                {{ $kitchen->DeliveryTime ?? '30-45 min' }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                <div id="step2" class="wizard-step d-none">
                    <div class="step-meta mb-5 text-center">
                        <h2 class="h3 display-title">2. Curate Your Flavors</h2>
                        <p class="opacity-50 small">Select multiple dishes to cycle through your plan.</p>
                    </div>

                    <!-- New Filter Tools -->
                    <div class="menu-filters-bar">
                        <div class="search-box-wrapper">
                            <i class="fas fa-search"></i>
                            <input type="text" id="menuSearch" class="menu-search-input" placeholder="Search dishes by name..." oninput="handleFilterChange()">
                        </div>
                        <div class="category-scroll-wrapper" id="categoryList">
                            <!-- Categories will be injected here -->
                        </div>
                    </div>

                    <div class="grid grid-4 gap-4" id="itemGrid">
                        <!-- Dynamically Loaded -->
                    </div>
                    <!-- Float Counter moved to footer -->
                </div>

                <div id="step3" class="wizard-step d-none">
                    <div class="step-meta mb-5 text-center">
                        <h2 class="h3 display-title">3. Master Your Schedule</h2>
                        <p class="opacity-50 small">Define when and how often you receive your meals.</p>
                    </div>
                    <div class="row g-5 justify-content-center">
                        <div class="col-md-5">
                            <div class="ctrl-group mb-5">
                                <div class="row g-3">
                                    <div class="col-6">
                                        <label class="ctrl-label">Start Date</label>
                                        <input type="date" id="startDate" class="lux-input" min="{{ date('Y-m-d') }}"
                                            value="{{ date('Y-m-d') }}">
                                    </div>
                                    <div class="col-6">
                                        <label class="ctrl-label">End Date</label>
                                        <input type="date" id="endDate" class="lux-input" min="{{ date('Y-m-d') }}"
                                            value="{{ date('Y-m-d', strtotime('+6 days')) }}">
                                    </div>
                                </div>
                                <input type="hidden" id="duration" value="7">
                            </div>
                            <div class="ctrl-group mb-4">
                                <label class="ctrl-label">Frequency</label>
                                <div class="segmented-ctrl" id="mealsPerDayControl" onchange="generateTimePickers()">
                                    <span class="sc-opt active" data-val="1">1 Meal</span>
                                    <span class="sc-opt" data-val="2">2 Meals</span>
                                    <span class="sc-opt" data-val="3">3 Meals</span>
                                    <span class="sc-opt" data-val="4">4 Meals</span>
                                    <span class="sc-opt" data-val="5">5 Meals</span>
                                    <input type="hidden" id="mealsPerDay" value="1">
                                </div>
                            </div>
                        </div>
                        <div class="col-md-5">
                            <div class="time-panel">
                                <label class="ctrl-label mb-3">Delivery Timings</label>
                                <div id="timePickers" class="d-flex flex-column gap-3"></div>
                            </div>
                        </div>
                    </div>
                </div>

                <div id="step4" class="wizard-step d-none">
                    <div class="passport-case">
                        <div class="passport-top text-center">
                            <div class="p-icon"><i class="fas fa-file-invoice"></i></div>
                            <h2 class="h4 fw-bold mb-1">Final Summary</h2>
                            <p class="opacity-50 small">Review before sending to kitchen</p>
                        </div>
                        <div class="passport-mid" id="finalSummary"></div>
                        <div class="px-4 pb-3">
                            <label class="ctrl-label mb-2"><i class="fas fa-comment-dots text-primary me-2"></i>
                                Customizations Request (Optional)</label>
                            <textarea id="customMessage"
                                class="lux-input form-control bg-transparent text-white border-secondary" rows="3"
                                placeholder="Any special requests or allergies?..."></textarea>
                        </div>
                        <div class="passport-bot border-top border-secondary pt-3">
                            <p class="m-0"><i class="fas fa-shield-alt me-2"></i> This request requires kitchen review.</p>
                        </div>
                    </div>
                </div>

                <!-- Footer Actions -->
                <div class="builder-footer mt-5 pt-4" style="display: flex; flex-direction: row; align-items: center; justify-content: space-between; width: 100%;">
                    <div style="flex: 1; display: flex; align-items: center;">
                        <button id="prevBtn" class="btn btn-lux-outline d-none" onclick="goBack()">
                            <i class="fas fa-arrow-left me-2"></i> Back
                        </button>
                    </div>
                    
                    <div style="flex: 1; display: flex; justify-content: center; align-items: center;">
                        <div id="selectedItemsCounter" class="d-none">
                            <div class="fab-inner" style="box-shadow: 0 5px 15px rgba(255, 107, 53, 0.2); white-space: nowrap; width: auto; display: inline-flex; justify-content: center; align-items: center;">
                                <i class="fas fa-shopping-basket me-2"></i>
                                <span id="counterText">0 Selected</span>
                            </div>
                        </div>
                    </div>

                    <div style="flex: 1; display: flex; justify-content: flex-end; align-items: center; gap: 15px;">
                        <button id="nextBtn" class="btn btn-lux-primary" onclick="goNext()">
                            Continue <i class="fas fa-arrow-right ms-2"></i>
                        </button>
                        <button id="submitBtn" class="btn btn-lux-primary d-none" onclick="submitPlanRequest()">
                            Finalize Plan <i class="fas fa-paper-plane ms-2"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>
        <div id="toastContainer" class="toast-container"></div>
    </div>

    <style>
        /* ─── Premium Design Tokens ────────────────── */
        :root {
            --lux-bg: #05050a;
            --lux-card: rgba(255, 255, 255, 0.03);
            --lux-border: rgba(255, 255, 255, 0.08);
            --lux-primary: #ff6b35;
            --lux-accent: #a78bfa;
            --lux-text: #ffffff;
            --lux-text-dim: rgba(255, 255, 255, 0.5);
            --lux-shadow: 0 40px 100px rgba(0, 0, 0, 0.6);
            --lux-glow: 0 0 30px rgba(255, 107, 53, 0.3);
        }

        [data-theme="light"] {
            --lux-bg: #f8f9fc;
            --lux-card: #ffffff;
            --lux-border: rgba(0, 0, 0, 0.08);
            --lux-text: #1a1a2e;
            --lux-text-dim: #64748b;
            --lux-shadow: 0 20px 50px rgba(0, 0, 0, 0.05);
            --lux-glow: 0 10px 25px rgba(255, 107, 53, 0.15);
        }

        /* ─── Core Layout ───────────────────────────── */
        .builder-hero-wrapper {
            position: fixed;
            inset: 0;
            z-index: -1;
            background: var(--lux-bg);
            overflow: hidden;
            pointer-events: none;
        }

        .hero-image-layer {
            position: absolute;
            inset: 0;
            background-size: cover;
            background-position: center;
            filter: blur(60px) scale(1.1);
            opacity: 0.2;
            transform: translateZ(0);
        }

        .hero-overlay-layer {
            position: absolute;
            inset: 0;
            background: radial-gradient(circle at 10% 10%, rgba(255, 107, 53, 0.08), transparent);
        }

        .builder-content-container {
            padding: 80px 0 100px;
        }

        /* ─── Horizontal Nav ───────────────────────── */
        .wizard-nav-container {
            max-width: 900px;
            margin: 0 auto;
        }

        .nav-glass-panel {
            background: var(--lux-card);
            border: 1px solid var(--lux-border);
            border-radius: 50px;
            padding: 15px 35px;
            box-shadow: var(--lux-shadow);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
        }

        .wizard-steps-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .step-item {
            display: flex;
            align-items: center;
            gap: 12px;
            transition: all 0.4s ease;
            opacity: 0.4;
        }

        .step-item.active {
            opacity: 1;
            transform: scale(1.05);
        }

        .step-item.completed {
            opacity: 0.8;
        }

        .step-icon {
            width: 40px;
            height: 40px;
            background: rgba(255, 255, 255, 0.05);
            border: 1.5px solid var(--lux-border);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--lux-text);
            font-size: 0.9rem;
            transition: 0.3s;
        }

        .step-item.active .step-icon {
            background: var(--lux-primary);
            border-color: var(--lux-primary);
            box-shadow: var(--lux-glow);
        }

        .step-item.completed .step-icon {
            background: rgba(255, 107, 53, 0.1);
            border-color: var(--lux-primary);
            color: var(--lux-primary);
        }

        .step-label {
            font-size: 0.75rem;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: var(--lux-text);
        }

        .step-connector {
            flex: 1;
            height: 2px;
            background: var(--lux-border);
            margin: 0 15px;
            border-radius: 2px;
            overflow: hidden;
        }

        .step-connector .fill {
            width: 0;
            height: 100%;
            background: var(--lux-primary);
            transition: 0.6s ease;
        }

        .step-item.completed+.step-connector .fill {
            width: 100%;
        }

        /* ─── Main Glass Case ──────────────────────── */
        .builder-glass-case {
            background: var(--lux-card);
            border: 1.5px solid var(--lux-border);
            border-radius: 40px;
            padding: 50px;
            box-shadow: var(--lux-shadow);
            backdrop-filter: blur(30px);
            -webkit-backdrop-filter: blur(30px);
            min-height: 500px;
            display: flex;
            flex-direction: column;
        }

        .display-title {
            font-weight: 850;
            letter-spacing: -1px;
            text-shadow: 0 10px 20px rgba(0, 0, 0, 0.2);
            color: var(--lux-text);
        }

        /* ─── Components ───────────────────────────── */
        .premium-kitchen-card {
            cursor: pointer;
            transition: 0.4s;
        }

        .card-inner {
            background: rgba(255, 255, 255, 0.02);
            border: 1px solid var(--lux-border);
            border-radius: 24px;
            overflow: hidden;
            height: 100%;
            display: flex;
            flex-direction: column;
            transition: 0.4s cubic-bezier(0.17, 0.67, 0.83, 0.67);
        }

        .card-img-box {
            position: relative;
            height: 160px;
        }

        .card-img-box img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: 0.6s;
        }

        .img-tint {
            position: absolute;
            inset: 0;
            background: linear-gradient(0deg, var(--lux-bg), transparent);
            opacity: 0.3;
        }

        .card-body-box {
            padding: 20px;
        }

        .k-badge {
            font-size: 0.6rem;
            font-weight: 900;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: var(--lux-primary);
            margin-bottom: 5px;
        }

        .k-name {
            font-size: 1.15rem;
            font-weight: 800;
            color: var(--lux-text);
            margin-bottom: 5px;
        }

        .k-stats {
            font-size: 0.75rem;
            font-weight: 700;
            color: var(--lux-text-dim);
            display: flex;
            gap: 12px;
        }

        .premium-kitchen-card:hover .card-inner {
            transform: translateY(-8px);
            border-color: var(--lux-primary);
            background: rgba(255, 107, 53, 0.03);
            box-shadow: var(--lux-glow);
        }

        .premium-kitchen-card:hover img {
            transform: scale(1.1);
        }

        .premium-kitchen-card.selected .card-inner {
            border-color: var(--lux-primary);
            background: rgba(255, 107, 53, 0.08);
            border-width: 2px;
        }

        /* Step 2 Tiles */
        .premium-item-tile {
            background: rgba(255, 255, 255, 0.02);
            border: 1px solid var(--lux-border);
            border-radius: 20px;
            cursor: pointer;
            transition: 0.3s;
            position: relative;
            overflow: hidden;
            display: flex;
            flex-direction: column;
        }

        .premium-item-tile:hover {
            border-color: var(--lux-primary);
            transform: translateY(-5px);
        }

        .premium-item-tile.selected {
            border-color: var(--lux-primary);
            background: rgba(255, 107, 53, 0.08);
            box-shadow: var(--lux-glow);
        }

        .premium-item-tile img {
            width: 100%;
            height: 160px;
            object-fit: cover;
        }

        .ver-badge {
            position: absolute;
            top: 12px;
            left: 12px;
            background: rgba(25, 135, 84, 0.9);
            color: white;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 0.65rem;
            font-weight: 800;
            z-index: 2;
        }

        .tile-body {
            padding: 15px;
            flex: 1;
            display: flex;
            flex-direction: column;
        }

        .tile-cat {
            font-size: 0.6rem;
            font-weight: 900;
            text-transform: uppercase;
            color: var(--lux-primary);
            margin-bottom: 4px;
        }

        .tile-desc {
            font-size: 0.7rem;
            color: var(--lux-text-dim);
            line-height: 1.4;
            margin-bottom: 12px;
            flex: 1;
        }

        .check-mark {
            position: absolute;
            top: 12px;
            right: 12px;
            width: 28px;
            height: 28px;
            background: var(--lux-primary);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 0.8rem;
            transform: scale(0);
            transition: 0.3s cubic-bezier(0.18, 0.89, 0.32, 1.28);
            z-index: 3;
        }

        .selected .check-mark {
            transform: scale(1);
        }

        /* Step 3 Controls */
        .ctrl-label {
            display: block;
            font-size: 0.75rem;
            font-weight: 900;
            text-transform: uppercase;
            letter-spacing: 2px;
            color: var(--lux-text-dim);
            margin-bottom: 12px;
        }

        .segmented-ctrl {
            display: flex;
            background: rgba(0, 0, 0, 0.2);
            border: 1px solid var(--lux-border);
            border-radius: 16px;
            padding: 5px;
            gap: 4px;
        }

        .sc-opt {
            flex: 1;
            text-align: center;
            padding: 12px;
            font-weight: 800;
            font-size: 0.85rem;
            border-radius: 12px;
            cursor: pointer;
            color: var(--lux-text-dim);
            transition: 0.3s;
        }

        .sc-opt:hover {
            color: var(--lux-text);
        }

        .sc-opt.active {
            background: var(--lux-primary);
            color: white;
            box-shadow: var(--lux-glow);
        }

        .lux-input {
            width: 100%;
            background: rgba(0, 0, 0, 0.2);
            border: 1.5px solid var(--lux-border);
            border-radius: 14px;
            padding: 16px;
            color: var(--lux-text);
            font-weight: 700;
            transition: 0.3s;
        }

        .lux-input:focus {
            border-color: var(--lux-primary);
            outline: none;
            box-shadow: var(--lux-glow);
        }

        /* Step 4 Passport */
        .passport-case {
            max-width: 550px;
            margin: 0 auto;
            background: rgba(255, 255, 255, 0.02);
            border: 1px solid var(--lux-border);
            border-radius: 35px;
            overflow: hidden;
        }

        .passport-top {
            padding: 35px;
            background: rgba(255, 255, 255, 0.03);
            border-bottom: 2px dashed var(--lux-border);
        }

        .p-icon {
            font-size: 2.5rem;
            color: var(--lux-primary);
            margin-bottom: 15px;
        }

        .passport-mid {
            padding: 35px;
        }

        .passport-bot {
            padding: 15px;
            text-align: center;
            background: rgba(255, 107, 53, 0.05);
            color: var(--lux-primary);
            font-size: 0.75rem;
            font-weight: 700;
        }

        .p-row {
            display: flex;
            gap: 20px;
            padding: 12px 0;
            border-bottom: 1px solid var(--lux-border);
        }

        .p-row:last-child {
            border: none;
        }

        .p-row i {
            width: 35px;
            height: 35px;
            background: rgba(255, 107, 53, 0.1);
            color: var(--lux-primary);
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .p-lbl {
            font-size: 0.7rem;
            font-weight: 800;
            text-transform: uppercase;
            color: var(--lux-text-dim);
            display: block;
        }

        .p-val {
            font-weight: 800;
            color: var(--lux-text);
        }

        /* Buttons */
        .btn-lux-primary {
            background: var(--lux-primary);
            color: white;
            border: none;
            padding: 16px 40px;
            border-radius: 50px;
            font-weight: 900;
            text-transform: uppercase;
            letter-spacing: 1.5px;
            transition: 0.3s;
            box-shadow: var(--lux-glow);
        }

        .btn-lux-primary:hover {
            transform: translateY(-3px);
            box-shadow: 0 15px 30px rgba(255, 107, 53, 0.4);
        }

        .btn-lux-outline {
            background: transparent;
            color: var(--lux-text);
            border: 1.5px solid var(--lux-border);
            padding: 14px 35px;
            border-radius: 50px;
            font-weight: 700;
            transition: 0.3s;
        }

        .btn-lux-outline:hover {
            background: var(--lux-border);
            color: var(--lux-primary);
        }

        .selection-fab {
            position: fixed;
            bottom: 40px;
            right: 40px;
            z-index: 1000;
        }

        .fab-inner {
            background: var(--lux-primary);
            color: white;
            padding: 14px 30px;
            border-radius: 50px;
            font-weight: 900;
            box-shadow: var(--lux-glow);
            animation: fabPop 0.4s ease;
        }

        @keyframes fabPop {
            from {
                transform: scale(0);
            }

            to {
                transform: scale(1);
            }
        }

        .wizard-step {
            animation: stepIn 0.5s ease-out;
        }

        @keyframes stepIn {
            from {
                opacity: 0;
                transform: translateY(15px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .d-none {
            display: none !important;
        }

        /* Light Theme Input override */
        [data-theme="light"] select,
        [data-theme="light"] input {
            background: #f1f5f9;
            color: #1e293b;
            border-color: #e2e8f0;
        }

        .lux-input::-webkit-calendar-picker-indicator {
            filter: invert(1);
            cursor: pointer;
        }

        [data-theme="light"] .lux-input::-webkit-calendar-picker-indicator {
            filter: none;
        }

        /* Toast Styles */
        .toast-container {
            position: fixed;
            top: 100px;
            right: 40px;
            z-index: 10000;
            display: flex;
            flex-direction: column;
            gap: 12px;
            pointer-events: none;
        }

        .toast-item {
            background: var(--lux-card);
            border: 1px solid var(--lux-border);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            padding: 16px 24px;
            border-radius: 16px;
            min-width: 280px;
            display: flex;
            align-items: center;
            gap: 15px;
            transform: translateX(120%);
            transition: 0.5s cubic-bezier(0.68, -0.55, 0.265, 1.55);
            box-shadow: var(--lux-shadow);
        }

        .toast-item.show {
            transform: translateX(0);
        }

        .toast-indicator {
            width: 4px;
            height: 30px;
            border-radius: 4px;
        }

        .toast-content {
            font-weight: 700;
            font-size: 0.9rem;
            color: var(--lux-text);
        }

        .warning {
            --warning: #f59e0b;
        }

        .success {
            --success: #10b981;
        }

        .error {
            --error: #ef4444;
        }

        /* ─── Menu Filters ─────────────────────────── */
        .menu-filters-bar {
            display: flex;
            flex-direction: column;
            gap: 20px;
            margin-bottom: 30px;
            padding: 25px;
            background: rgba(255, 255, 255, 0.02);
            border: 1px solid var(--lux-border);
            border-radius: 25px;
            backdrop-filter: blur(10px);
        }

        .search-box-wrapper {
            position: relative;
            width: 100%;
        }

        .search-box-wrapper i {
            position: absolute;
            left: 20px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--lux-text-dim);
            font-size: 1.1rem;
        }

        .menu-search-input {
            width: 100%;
            background: rgba(0, 0, 0, 0.2);
            border: 1px solid var(--lux-border);
            border-radius: 15px;
            padding: 15px 15px 15px 55px;
            color: var(--lux-text);
            font-weight: 600;
            transition: 0.3s;
        }

        .menu-search-input:focus {
            border-color: var(--lux-primary);
            box-shadow: 0 0 15px rgba(255, 107, 53, 0.1);
            outline: none;
        }

        .category-scroll-wrapper {
            display: flex;
            gap: 12px;
            overflow-x: auto;
            padding-bottom: 10px;
            scrollbar-width: thin;
            scrollbar-color: var(--lux-primary) transparent;
        }

        .category-pill {
            padding: 10px 24px;
            background: rgba(255, 255, 255, 0.03);
            border: 1px solid var(--lux-border);
            border-radius: 50px;
            color: var(--lux-text-dim);
            font-size: 0.85rem;
            font-weight: 700;
            white-space: nowrap;
            cursor: pointer;
            transition: 0.3s;
        }

        .category-pill:hover,
        .category-pill.active {
            background: var(--lux-primary);
            color: white;
            border-color: var(--lux-primary);
            box-shadow: var(--lux-glow);
        }

        /* Items Animation */
        .premium-item-tile {
            animation: tileAppear 0.4s ease forwards;
        }

        @keyframes tileAppear {
            from {
                opacity: 0;
                transform: translateY(10px) scale(0.95);
            }
            to {
                opacity: 1;
                transform: translateY(0) scale(1);
            }
        }
        /* ─── Responsive Excellence ────────────────── */
        @media (max-width: 991px) {
            .wizard-nav-container { padding: 0 15px; }
            .nav-glass-panel { padding: 12px 20px; border-radius: 25px; }
            .step-label { display: none; }
            .step-item { gap: 0; }
            .builder-glass-case { padding: 30px 20px; border-radius: 30px; }
            .grid-4 { grid-template-columns: repeat(2, 1fr) !important; }
        }

        @media (max-width: 768px) {
            .builder-header h1 { font-size: 2.2rem !important; }
            .wizard-steps-row { justify-content: center; gap: 10px; }
            .step-connector { display: none; }
            .builder-footer { flex-direction: column !important; gap: 20px; text-align: center; }
            .builder-footer > div { justify-content: center !important; width: 100%; }
            #nextBtn, #submitBtn, #prevBtn { width: 100%; }
            .segmented-ctrl { overflow-x: auto; -webkit-overflow-scrolling: touch; }
            .sc-opt { white-space: nowrap; min-width: 80px; }
        }

        @media (max-width: 576px) {
            .grid-4 { grid-template-columns: 1fr !important; }
            .premium-kitchen-card img, .premium-item-tile img { height: 180px; }
            .builder-glass-case { padding: 25px 15px; }
            .passport-case { border-radius: 25px; }
            .passport-top, .passport-mid { padding: 20px; }
        }
    </style>

    @push('scripts')
        <script>
            let currentStep = 1;
            let selectedKitchenId = null;
            let selectedKitchenName = "";
            let selectedItems = [];
            let itemPrices = {}; // Store item prices by ID
            let allKitchenItems = []; // Global store for filtering
            let activeCategory = 'All';

            function updateWizardState() {
                document.querySelectorAll('.wizard-step').forEach(s => s.classList.add('d-none'));
                document.getElementById(`step${currentStep}`).classList.remove('d-none');

                document.querySelectorAll('.step-item').forEach((it, idx) => {
                    const num = idx + 1;
                    it.classList.remove('active', 'completed');
                    if (num === currentStep) it.classList.add('active');
                    if (num < currentStep) it.classList.add('completed');
                });

                document.getElementById('prevBtn').classList.toggle('d-none', currentStep === 1);
                document.getElementById('nextBtn').classList.toggle('d-none', currentStep === 4);
                document.getElementById('submitBtn').classList.toggle('d-none', currentStep !== 4);
                document.getElementById('selectedItemsCounter').classList.toggle('d-none', currentStep !== 2);

                if (currentStep === 4) populateSummary();
                window.scrollTo({ top: 0, behavior: 'smooth' });
            }

            function selectKitchen(el, id, name) {
                selectedKitchenId = id; selectedKitchenName = name;
                document.querySelectorAll('.premium-kitchen-card').forEach(c => c.classList.remove('selected'));
                el.classList.add('selected');
                setTimeout(goNext, 300);
            }

            async function loadKitchenItems(id) {
                const grid = document.getElementById('itemGrid');
                grid.innerHTML = '<div class="col-span-full py-5 text-center"><div class="spinner-border text-primary"></div></div>';
                try {
                    const r = await fetch(`/api/kitchen/${id}/menu`);
                    allKitchenItems = await r.json();
                    
                    // Reset filters
                    activeCategory = 'All';
                    document.getElementById('menuSearch').value = '';
                    
                    // Generate Categories
                    const categories = ['All', ...new Set(allKitchenItems.map(i => i.category))];
                    const catList = document.getElementById('categoryList');
                    catList.innerHTML = '';
                    categories.forEach(cat => {
                        const pill = document.createElement('div');
                        pill.className = `category-pill ${cat === 'All' ? 'active' : ''}`;
                        pill.innerText = cat;
                        pill.onclick = () => {
                            document.querySelectorAll('.category-pill').forEach(p => p.classList.remove('active'));
                            pill.classList.add('active');
                            activeCategory = cat;
                            handleFilterChange();
                        };
                        catList.appendChild(pill);
                    });

                    applyFilters();
                } catch (e) { grid.innerHTML = '<div class="col-span-full text-center py-5">Error loading menu</div>'; }
            }

            function handleFilterChange() {
                applyFilters();
            }

            function applyFilters() {
                const searchTerm = document.getElementById('menuSearch').value.toLowerCase();
                const filtered = allKitchenItems.filter(item => {
                    const matchesSearch = item.name.toLowerCase().includes(searchTerm);
                    const matchesCat = activeCategory === 'All' || item.category === activeCategory;
                    return matchesSearch && matchesCat;
                });
                renderItems(filtered);
            }

            function renderItems(items) {
                const grid = document.getElementById('itemGrid');
                grid.innerHTML = '';

                if (items.length === 0) {
                    grid.innerHTML = '<div class="col-span-full py-5 text-center opacity-50"><i class="fas fa-search-minus mb-3" style="font-size: 2rem;"></i><br>No matching dishes found...</div>';
                    return;
                }

                items.forEach((item) => {
                    itemPrices[item.id] = item.price; 
                    const el = document.createElement('div');
                    el.className = "premium-item-tile";
                    el.onclick = (e) => toggleItem(e, item.id, item.name);
                    if (selectedItems.find(x => x.id === item.id)) el.classList.add('selected');
                    el.innerHTML = `
                        <div class="check-mark"><i class="fas fa-check"></i></div>
                        <img src="${item.image}">
                        <div class="tile-body">
                            <span class="tile-cat">${item.category}</span>
                            <div class="fw-bold mb-1" style="color:var(--lux-text); font-size:0.95rem">${item.name}</div>
                            <p class="tile-desc">${item.description.substring(0, 50)}...</p>
                            <div style="color:var(--lux-primary);font-weight:900;font-size:1rem">${item.price} EGP</div>
                        </div>
                    `;
                    grid.appendChild(el);
                });
            }

            function toggleItem(event, id, name) {
                const i = selectedItems.findIndex(x => x.id === id);
                const card = event.currentTarget.closest('.premium-item-tile');
                if (i > -1) { selectedItems.splice(i, 1); card.classList.remove('selected'); }
                else { selectedItems.push({ id, name }); card.classList.add('selected'); }
                document.getElementById('counterText').innerText = `${selectedItems.length} Selected`;
            }

            function generateTimePickers() {
                const n = document.getElementById('mealsPerDay').value;
                const c = document.getElementById('timePickers'); c.innerHTML = '';
                for (let i = 1; i <= n; i++) {
                    const d = document.createElement('div');
                    d.innerHTML = `<div class="p-row"><i class="fas fa-clock"></i> <div class="flex-grow-1"><span class="p-lbl">Meal #${i} Time</span><input type="time" class="slot-input lux-input p-0 border-0 bg-transparent" required></div></div>`;
                    c.appendChild(d);
                }
            }

            function populateSummary() {
                const s = document.getElementById('finalSummary');
                const t = Array.from(document.querySelectorAll('.slot-input')).map(x => x.value || '--:--');

                // Calculate dynamic duration
                const startD = new Date(document.getElementById('startDate').value);
                const endD = new Date(document.getElementById('endDate').value);
                let computedDuration = 1;
                if (!isNaN(startD) && !isNaN(endD) && startD <= endD) {
                    computedDuration = Math.round((endD - startD) / 86400000) + 1;
                }
                document.getElementById('duration').value = computedDuration;
                const duration = computedDuration;

                const mealsPerDay = parseInt(document.getElementById('mealsPerDay').value) || 0;
                const totalItemsPrice = selectedItems.reduce((acc, item) => {
                    const price = parseFloat(itemPrices[item.id]) || 0;
                    return acc + price;
                }, 0);

                const avgPrice = selectedItems.length > 0 ? (totalItemsPrice / selectedItems.length) : 0;
                const totalPrice = Math.round(avgPrice * mealsPerDay * duration);

                s.innerHTML = `
                            <div class="p-row"><i class="fas fa-store"></i> <div><span class="p-lbl">Kitchen</span><span class="p-val">${selectedKitchenName}</span></div></div>
                            <div class="p-row"><i class="fas fa-utensils"></i> <div><span class="p-lbl">Items Selected</span><span class="p-val">${selectedItems.length} Dishes</span></div></div>
                            <div class="p-row"><i class="fas fa-calendar-day"></i> <div><span class="p-lbl">Duration</span><span class="p-val">${duration} Days</span></div></div>
                            <div class="p-row"><i class="fas fa-bell"></i> <div><span class="p-lbl">Frequency</span><span class="p-val">${t.length} Times Daily: ${t.join(' • ')}</span></div></div>
                            <div class="p-row" style="background: rgba(255,107,53,0.1); margin-top: 15px; border-radius: 15px; border: none;">
                                <i class="fas fa-coins"></i> 
                                <div>
                                    <span class="p-lbl">Estimated Total</span>
                                    <span class="p-val" style="color: var(--lux-primary); font-size: 1.4rem;">${totalPrice} EGP</span>
                                </div>
                            </div>
                        `;
            }

            function goNext() {
                if (currentStep === 1 && !selectedKitchenId) return showToast('Pick a Kitchen', 'warning');
                if (currentStep === 2 && selectedItems.length === 0) return showToast('Pick Menu Items', 'warning');
                if (currentStep === 3) {
                    if (Array.from(document.querySelectorAll('.slot-input')).some(x => !x.value)) return showToast('Set Timings', 'warning');
                    const st = new Date(document.getElementById('startDate').value);
                    const ed = new Date(document.getElementById('endDate').value);
                    if (ed < st) return showToast('End Date must be after Start Date', 'warning');
                }
                currentStep++;
                if (currentStep === 2) loadKitchenItems(selectedKitchenId);
                if (currentStep === 3) generateTimePickers();
                updateWizardState();
            }

            function goBack() { currentStep--; updateWizardState(); }

            document.querySelectorAll('.sc-opt').forEach(b => {
                b.onclick = function () {
                    const p = this.closest('.segmented-ctrl');
                    p.querySelectorAll('.sc-opt').forEach(x => x.classList.remove('active'));
                    this.classList.add('active');
                    p.querySelector('input').value = this.dataset.val;
                    if (p.id === 'mealsPerDayControl') generateTimePickers();
                }
            });

            async function submitPlanRequest() {
                const b = document.getElementById('submitBtn');
                b.disabled = true;
                b.innerHTML = '<i class="fas fa-circle-notch fa-spin me-2"></i> Finalizing...';

                const duration = parseInt(document.getElementById('duration').value) || 0;
                const mealsPerDay = parseInt(document.getElementById('mealsPerDay').value) || 0;

                // Calculate estimated price (consistent with populateSummary)
                const totalItemsPrice = selectedItems.reduce((acc, item) => {
                    const price = parseFloat(itemPrices[item.id]) || 0;
                    return acc + price;
                }, 0);

                const avgPrice = selectedItems.length > 0 ? (totalItemsPrice / selectedItems.length) : 0;
                const subtotal = Math.round(avgPrice * mealsPerDay * duration);

                window.biteConfirm(`Are you sure you want to finalize this plan? Estimated total is ${subtotal} EGP.`, async function(res) {
                    if (!res) {
                        b.disabled = false;
                        b.innerHTML = 'Finalize Plan <i class="fas fa-paper-plane ms-2"></i>';
                        return;
                    }

                    const payload = {
                        kitchen_id: selectedKitchenId,
                        menu_items: selectedItems.map(x => x.id),
                        duration: duration,
                        meals_per_day: mealsPerDay,
                        start_date: document.getElementById('startDate').value,
                        times: Array.from(document.querySelectorAll('.slot-input')).map(x => x.value),
                        price: subtotal,
                        custom_message: document.getElementById('customMessage') ? document.getElementById('customMessage').value : ''
                    };
                    try {
                        const r = await fetch('{{ route("frontend.meal_plan_builder.store") }}', {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                            body: JSON.stringify(payload)
                        });
                        const d = await r.json();
                        if (d.success) {
                            showToast('Plan Finalized Successfully!', 'success');
                            setTimeout(() => window.location.href = '{{ route("frontend.subscriptions") }}', 1500);
                        }
                        else {
                            showToast(d.message || 'Error finalizing plan', 'error');
                            b.disabled = false;
                            b.innerHTML = 'Finalize Plan <i class="fas fa-paper-plane ms-2"></i>';
                        }
                    } catch (e) {
                        showToast('Network Error. Please try again.', 'error');
                        b.disabled = false;
                        b.innerHTML = 'Finalize Plan <i class="fas fa-paper-plane ms-2"></i>';
                    }
                });
            }

            function showToast(m, t) {
                const c = document.getElementById('toastContainer');
                const e = document.createElement('div');
                e.className = `toast-item ${t}`;
                e.innerHTML = `<div class="toast-indicator" style="background:var(--${t})"></div><div class="toast-content">${m}</div>`;
                c.appendChild(e);
                setTimeout(() => e.classList.add('show'), 100);
                setTimeout(() => { e.classList.remove('show'); setTimeout(() => e.remove(), 400); }, 3000);
            }
        </script>
    @endpush
@endsection