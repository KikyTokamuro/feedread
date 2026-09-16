@extends('layouts.main')

@section('content')
    <div class="page-header">
        <h2>Add a feed</h2>
        <p class="page-header__meta">The address of the feed itself, not of the website.</p>
    </div>
    <hr>

    <div class="row">
        <div class="col-lg">
            <form class="panel" action="{{ route('feed.store') }}" method="post">
                @csrf

                <div class="mb-3">
                    <label for="title" class="form-label">Title</label>
                    <input id="title" name="title" type="text" value="{{ old('title') }}"
                           class="form-control" placeholder="The Verge" required autofocus>
                    @error('title')
                        <p class="form-error">{{ $message }}</p>
                    @enderror
                </div>

                <div class="mb-4">
                    <label for="url" class="form-label">Feed URL</label>
                    <input id="url" name="url" type="text" value="{{ old('url') }}"
                           class="form-control" placeholder="https://example.com/feed.xml" required>
                    <div class="form-text">
                        Atom, RSS or JSON feed. It has to be publicly reachable.
                    </div>
                    @error('url')
                        <p class="form-error">{{ $message }}</p>
                    @enderror
                </div>

                <div class="d-flex justify-content-end gap-2">
                    <a href="{{ route('main') }}" class="btn btn-soft">Cancel</a>
                    <button id="add-btn" type="submit" class="btn btn-accent">
                        <i class="bi bi-plus-lg"></i> Add feed
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection
