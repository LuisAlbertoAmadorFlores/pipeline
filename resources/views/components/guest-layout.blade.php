@extends('layouts.app')

@section('content')
    <div class="min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0">
        {{ $slot }}
    </div>
@endsection
