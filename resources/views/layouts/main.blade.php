<!doctype html>
<html lang="en" @if($settings->dark) data-bs-theme="dark" @endif>
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <link rel="shortcut icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
    <title>FeedRead</title>
    
    @vite(['resources/sass/app.scss', 'resources/js/app.js'])
</head>
<body>
<div class="container-fluid">
    <div class="row flex-nowrap">
        @if(count($feeds) > 0)
            <div class="col-auto px-0">
                <div id="sidebar" class="collapse collapse-horizontal show h-100">
                    <div id="sidebar-nav" class="list-group rounded-0 text-sm-start min-vh-100">
                        <div class="d-flex pt-1 align-items-center justify-content-center border-bottom border-light">
                            <a href="/" class="text-decoration-none fs-4 text-light"><i class="text-warning bi bi-rss-fill"></i>FeedRead</a>
                        </div>
                        @foreach($feeds as $feed)
                            <a href="{{ route('feed.show', $feed->id) }}"
                               class="list-group-item border-0 d-inline-block text-truncate"
                               data-bs-parent="#sidebar">
                                @php($favicon = $feed->favicon())
                                @if($favicon != null)
                                    <img src="{{ $favicon }}" alt="{{ $feed->title }}">
                                @else
                                    <i class="bi bi-rss"></i>
                                @endif
                                <span id="feed-title">{{ $feed->title }}</span>
                            </a>
                        @endforeach

                    </div>
                </div>
            </div>
        @endif
        <main class="col ps-md-2 pt-2 min-vh-100">
            <div class="btn-toolbar" role="toolbar">
                @if(count($feeds) > 0)
                    <div class="btn-group me-2" role="group">
                        <a id="collapse-sidebar-btn" href="#" data-bs-target="#sidebar" data-bs-toggle="collapse"
                           class="btn btn-outline text-decoration-none"><i
                                class="bi bi-list bi-lg py-2 p-1"></i> Feed</a>
                    </div>
                @endif

                <div class="btn-group me-2" role="group">
                    @if(!request()->is('feeds/add'))
                        <a id="add-feed-btn" href="{{ route('feed.add') }}"
                           class="btn btn-outline text-decoration-none">
                            <i class="bi bi-plus-square bi-lg"></i> Add
                        </a>
                    @endif
                    @yield('buttons')
                </div>

                @if(!request()->is('settings'))
                    <div class="btn-group me-2" role="group">
                        <a id="settings-btn" href="{{ route('settings.show') }}"
                           class="btn btn-outline text-decoration-none">
                            <i class="bi bi-gear bi-lg"></i> Settings
                        </a>
                    </div>
                @endif
            </div>

            @yield('content')
        </main>
    </div>
</div>

</body>
</html>
