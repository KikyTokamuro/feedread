@extends('layouts.main')

@section('content')
    <div class="page-header">
        <h2>Feed not found</h2>
    </div>
    <hr>

    <div class="empty-state empty-state--error">
        <i class="bi bi-search empty-state__icon"></i>
        <p class="mb-0">There is no feed with the id "{{ $wrongId }}" in this account.</p>
        <div class="mt-3">
            <a href="{{ route('main') }}" class="btn btn-soft">Back to the feed list</a>
        </div>
    </div>
@endsection
