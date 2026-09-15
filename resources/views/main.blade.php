@extends('layouts.main')

@section('content')
    <div class="hero">
        <span class="hero__logo"><i class="bi bi-rss-fill" aria-hidden="true"></i></span>
        <h1 class="hero__title">FeedRead</h1>
        <p class="hero__tagline">A small Atom, RSS and JSON feed reader.</p>
        <span class="badge badge-muted">v{{ Config::get('app.version') }}</span>

        @if($feeds->isEmpty())
            <p class="mt-4 mb-2">This account does not have any feeds yet.</p>
            <div class="hero__links mx-auto justify-content-center">
                <a href="{{ route('feed.add') }}" class="btn btn-accent">
                    <i class="bi bi-plus-lg"></i> Add a feed
                </a>
                <a href="{{ route('opml.index') }}" class="btn btn-soft">
                    <i class="bi bi-box-arrow-in-up"></i> Import OPML
                </a>
            </div>
        @else
            <div class="hero__links mx-auto justify-content-center">
                <a href="{{ route('feed.show', $feeds->first()) }}" class="btn btn-accent">
                    <i class="bi bi-journal-text"></i> Start reading
                </a>
            </div>
        @endif

        <p class="hero__foot mb-0">
            <a href="https://github.com/KikyTokamuro/feedread" target="_blank" rel="noopener">
                <i class="bi bi-github"></i> Source
            </a>
        </p>
    </div>
@endsection
