@extends('errors.layout')

@section('error_code', '429')
@section('error_tagline', 'Slow down, you are ordering too fast!')
@section('error_title', 'Too Many Requests')
@section('error_desc', 'You have sent too many requests in a short period of time. Please wait a moment before trying again. Our kitchen needs a breather!')

@section('extra_actions')
    <a href="{{ url('/') }}" class="btn-outline-err">
        <i class="fas fa-clock"></i> Wait & Retry
    </a>
@endsection
