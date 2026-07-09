@extends('errors.layout')

@section('error_code', '419')
@section('error_tagline', 'Your session cookie has expired!')
@section('error_title', 'Page Expired')
@section('error_desc', 'Your session has timed out or the CSRF token has expired. This usually happens when you leave a page open for too long. Please go back and try again.')

@section('extra_actions')
    <a href="{{ url()->previous() }}" class="btn-outline-err">
        <i class="fas fa-rotate-right"></i> Try Again
    </a>
@endsection
