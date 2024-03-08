@extends('layouts.main')

@section('content')
    <div class="page-header pt-3">
        <h2>Error</h2>
    </div>
    <hr>
    <div class="p-3 mb-2 bg-danger text-white">
        Feed with id "{{ $wrongId }}" not found!
    </div>
@endsection
