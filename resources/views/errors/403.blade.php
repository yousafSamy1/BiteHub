@extends('errors.layout')

@section('error_code', '403')
@section('error_tagline', 'This kitchen is staff-only!')
@section('error_title', 'Access Denied')
@section('error_desc', 'You don\'t have permission to enter this area. Please log in with the correct account or contact support.')

@section('extra_actions')
    <a href="{{ route('login') }}" class="btn-outline-err">
        <i class="fas fa-right-to-bracket"></i> Log In
    </a>
@endsection
