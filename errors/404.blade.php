@extends('errors.layout')

@section('error_code', '404')
@section('error_tagline', 'Looks like this page is still cooking!')
@section('error_title', 'Page Not Found')
@section('error_desc', 'The page you\'re looking for doesn\'t exist or has been moved. Maybe it was eaten by the chef? Let\'s get you back on track!')

@section('extra_actions')
    <a href="{{ url('/menu') }}" class="btn-outline-err">
        <i class="fas fa-utensils"></i> Explore Menu
    </a>
@endsection
