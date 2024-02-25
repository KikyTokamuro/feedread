@extends('layouts.main')

@section('content')
    <div class="page-header pt-3">
        <h2>Add new feed</h2>
    </div>
    <hr>
    <div class="row">
        <form action="{{ route('feed.store') }}" method="post">
            @csrf
            <div class="mb-3">
                <label for="title" class="form-label">Feed Title</label>
                <input
                    value="{{ old('title') }}"
                    type="text" name="title" class="form-control" id="title" placeholder="Title">
                @error('title')
                <p class="text-danger p-lg-1">{{ $message }}</p>
                @enderror
            </div>
            <div class="mb-3">
                <label for="url" class="form-label">Feed URL</label>
                <input
                    value="{{ old('url') }}"
                    type="text" name="url" class="form-control" id="url" placeholder="Url">
                @error('url')
                <p class="text-danger p-lg-1">{{ $message }}</p>
                @enderror
            </div>
            <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                <button type="submit" class="btn btn-primary float-right">Add</button>
            </div>
        </form>
    </div>
@endsection
