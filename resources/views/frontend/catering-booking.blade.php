@extends('frontend.layouts.app')
@section('title', 'Book Catering Service')

@section('content')
<div class="page-header">
    <h1>Book Our <span class="highlight">Catering Service</span></h1>
    <p>Plan your perfect event with BiteHub's verified caterers. Submit your request and they'll contact you!</p>
</div>

<section class="section" style="padding-top:0">
<div class="container" style="max-width:820px">

    @if(session('message'))
    <div class="info-box info-success reveal" style="margin-bottom:24px">
        <i class="fas fa-check-circle" style="font-size:1.2rem"></i>
        <span>{{ session('message') }}</span>
    </div>
    @endif

    <!-- Benefits strip -->
    <div class="grid grid-3 reveal" style="margin-bottom:36px">
        @foreach([
            ['icon'=>'🎪','title'=>'Any Event','sub'=>'Weddings, corporate, parties'],
            ['icon'=>'👨‍🍳','title'=>'Expert Chefs','sub'=>'Verified professional caterers'],
            ['icon'=>'✅','title'=>'Easy Process','sub'=>'Submit once, we handle the rest'],
        ] as $b)
        <div style="display:flex;align-items:center;gap:14px;background:var(--bg-glass);border:1px solid var(--border-color);border-radius:var(--radius-lg);padding:18px 20px;backdrop-filter:blur(20px)">
            <span style="font-size:1.8rem">{{ $b['icon'] }}</span>
            <div>
                <div style="font-weight:700;font-size:0.95rem">{{ $b['title'] }}</div>
                <div style="color:var(--text-muted);font-size:0.82rem">{{ $b['sub'] }}</div>
            </div>
        </div>
        @endforeach
    </div>

    <!-- Form -->
    <div class="glass-card reveal" style="padding:40px;border-color:rgba(171,71,188,0.2)">
        <div style="display:flex;align-items:center;gap:14px;margin-bottom:28px">
            <div style="width:48px;height:48px;border-radius:16px;background:linear-gradient(135deg,#ab47bc,#7b1fa2);display:flex;align-items:center;justify-content:center;font-size:1.3rem;flex-shrink:0">📅</div>
            <div>
                <h3 style="margin:0 0 4px">Book Your Catering</h3>
                <p style="color:var(--text-muted);font-size:0.88rem;margin:0">All fields marked with * are required</p>
            </div>
        </div>

        <form method="POST" action="{{ route('frontend.catering.store') }}">
            @csrf
            <div class="form-group">
                <label class="form-label">Select Caterer *</label>
                <select name="caterer_id" required class="form-control">
                    <option value="">— Choose a Caterer —</option>
                    @foreach($caterers as $cat)
                    <option value="{{ $cat->CatererID }}">{{ $cat->BusinessName }} ({{ $cat->FullName }})</option>
                    @endforeach
                </select>
            </div>

            <div class="grid grid-2" style="gap:20px">
                <div class="form-group">
                    <label class="form-label">Event Type *</label>
                    <input type="text" name="event_type" required class="form-control" placeholder="e.g. Wedding, Corporate event...">
                </div>
                <div class="form-group">
                    <label class="form-label">Event Date *</label>
                    <input type="date" name="event_date" required class="form-control" min="{{ date('Y-m-d', strtotime('+1 day')) }}">
                </div>
            </div>

            <div class="grid grid-2" style="gap:20px">
                <div class="form-group">
                    <label class="form-label">Number of Guests *</label>
                    <input type="number" name="guest_count" required class="form-control" min="10" placeholder="Minimum 10 guests">
                </div>
                <div class="form-group">
                    <label class="form-label">Estimated Budget (EGP)</label>
                    <input type="number" name="budget" class="form-control" placeholder="e.g. 5000">
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">Additional Details</label>
                <textarea name="details" class="form-control" rows="4" placeholder="Tell us about food preferences, allergies, location, setup requirements, etc."></textarea>
            </div>

            <button type="submit" class="btn btn-primary btn-block btn-lg" style="margin-top:8px">
                <i class="fas fa-paper-plane"></i> Submit Catering Request
            </button>
        </form>
    </div>

</div>
</section>
@endsection
