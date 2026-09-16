@extends('layouts.main')

@section('content')
    <div class="page-header">
        <h2>Edit "{{ $feed->title }}"</h2>
        <p class="page-header__meta">
            Changing the URL clears the stored articles and fetches the feed again.
        </p>
    </div>
    <hr>

    <div class="row">
        <div class="col-lg">
            <form class="panel" action="{{ route('feed.update', $feed) }}" method="post">
                @csrf
                @method('PATCH')

                <div class="mb-3">
                    <label for="title" class="form-label">Title</label>
                    <input id="title" name="title" type="text" value="{{ old('title', $feed->title) }}"
                           class="form-control" required autofocus>
                    @error('title')
                        <p class="form-error">{{ $message }}</p>
                    @enderror
                </div>

                <div class="mb-4">
                    <label for="url" class="form-label">Feed URL</label>
                    <input id="url" name="url" type="text" value="{{ old('url', $feed->url) }}"
                           class="form-control" required>
                    @error('url')
                        <p class="form-error">{{ $message }}</p>
                    @enderror
                </div>

                <div class="d-flex justify-content-end gap-2">
                    <a href="{{ route('feed.show', $feed) }}" class="btn btn-soft">Cancel</a>
                    <button id="save-btn" type="submit" class="btn btn-accent">
                        <i class="bi bi-check-lg"></i> Save
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection
