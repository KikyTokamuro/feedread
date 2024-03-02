<!doctype html>
<html lang="en">
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
                <div id="sidebar" class="collapse collapse-horizontal show border-end h-100">
                    <div id="sidebar-nav" class="list-group border-0 rounded-0 text-sm-start min-vh-100">
                        @foreach($feeds as $feed)
                            <a href="{{ route('feed.show', $feed->id) }}"
                               class="list-group-item border-end-0 d-inline-block text-truncate"
                               data-bs-parent="#sidebar">
                                @php($favicon = $feed->favicon())
                                @if($favicon != null)
                                    <img src="{{ $favicon }}" alt="{{ $feed->title }}"
                                         style="width: 15px; height: 15px;">
                                @else
                                    <i class="bi bi-rss"></i>
                                @endif
                                <span>{{ $feed->title }}</span>
                            </a>
                        @endforeach

                    </div>
                </div>
            </div>
        @endif
        <main class="col ps-md-2 pt-2">
            <div class="btn-toolbar" role="toolbar">
                @if(count($feeds) > 0)
                    <div class="btn-group me-2" role="group">
                        <a href="#" data-bs-target="#sidebar" data-bs-toggle="collapse"
                           class="btn btn-outline border text-decoration-none text-primary"><i
                                class="bi bi-list bi-lg py-2 p-1"></i> Feed</a>
                    </div>
                @endif

                @if(!request()->is('feeds/add'))
                    <div class="btn-group me-2" role="group">
                        <a href="{{ route('feed.add') }}"
                           class="btn btn-outline border text-decoration-none text-success">
                            <i class="bi bi-plus-square-fill bi-lg"></i> Add
                        </a>
                        @yield('buttons')
                    </div>
                @endif
            </div>

            @yield('content')
        </main>
    </div>
</div>

</body>
</html>
