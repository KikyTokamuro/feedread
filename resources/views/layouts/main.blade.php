@php($boundFeed = request()->route('feed'))
@php($currentFeedId = $boundFeed instanceof \App\Models\Feed ? $boundFeed->id : null)
@php($unreadTotal = (int) $feeds->sum('unread_count'))
<!doctype html>
<html lang="en" @if(auth()->user()?->dark) data-bs-theme="dark" @endif>
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="theme-color" content="#1b1f2e">
    <link rel="shortcut icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
    <title>FeedRead</title>

    @vite(['resources/sass/app.scss', 'resources/js/app.js'])
</head>
<body>
<a class="skip-link" href="#content">Skip to content</a>

<div class="app-shell">
    @if($feeds->isNotEmpty())
        <div id="sidebar" class="app-sidebar collapse collapse-horizontal show">
            <a href="{{ route('main') }}" class="app-sidebar__brand">
                <span class="brand-mark"><i class="bi bi-rss-fill"></i></span>
                <span>FeedRead</span>
            </a>

            <nav id="sidebar-nav" class="app-sidebar__nav" aria-label="Feeds">
                <p class="app-sidebar__section">
                    <span>Your feeds</span>
                    @if($unreadTotal > 0)
                        <span>{{ $unreadTotal }} unread</span>
                    @endif
                </p>

                @foreach($feeds as $feed)
                    <a href="{{ route('feed.show', $feed) }}"
                       class="feed-link {{ $currentFeedId === $feed->id ? 'is-active' : '' }}"
                       @if($currentFeedId === $feed->id) aria-current="page" @endif
                       title="{{ $feed->title }}{{ $feed->unread_count > 0 ? ' — ' . $feed->unread_count . ' unread' : '' }}">
                        @if($feed->favicon_url)
                            <img class="feed-link__icon" src="{{ $feed->favicon_url }}" alt=""
                                 loading="lazy" onerror="this.style.display='none'">
                        @else
                            <i class="bi bi-rss feed-link__icon" aria-hidden="true"></i>
                        @endif
                        <span class="feed-link__title">{{ $feed->title }}</span>
                        @if($feed->unread_count > 0)
                            <span class="feed-link__count">{{ $feed->unread_count }}</span>
                        @endif
                    </a>
                @endforeach
            </nav>

            <div class="app-sidebar__footer">
                <a href="{{ route('feed.add') }}" class="app-sidebar__action">
                    <i class="bi bi-plus-lg"></i> Add
                </a>
                <a href="{{ route('opml.index') }}" class="app-sidebar__action" title="Import or export OPML">
                    <i class="bi bi-arrow-down-up"></i> Import
                </a>
            </div>
        </div>
    @endif

    <main class="app-main">
        <div class="app-toolbar" role="toolbar">
            <div class="app-toolbar__inner">
                <div class="app-toolbar__group">
                    @if($feeds->isNotEmpty())
                        <a id="collapse-sidebar-btn" href="#" data-bs-target="#sidebar" data-bs-toggle="collapse"
                           class="btn btn-soft" title="Show or hide the feed list">
                            <i class="bi bi-list"></i> Feeds
                        </a>
                    @endif

                    @yield('buttons')
                </div>

                <div class="app-toolbar__group">
                    {{-- Guarded so an error page can still be rendered for a
                         visitor who is not signed in. --}}
                    @auth
                        <div class="btn-group">
                            <button id="account-btn" type="button"
                                    class="btn btn-soft dropdown-toggle"
                                    data-bs-toggle="dropdown" aria-expanded="false"
                                    title="{{ auth()->user()->email }}">
                                <i class="bi bi-person-circle"></i> {{ auth()->user()->name }}
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li>
                                    <a class="dropdown-item {{ request()->routeIs('settings.*') ? 'active' : '' }}"
                                       href="{{ route('settings.show') }}"
                                       @if(request()->routeIs('settings.*')) aria-current="page" @endif>
                                        <i class="bi bi-gear"></i> Settings
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item" href="{{ route('opml.index') }}">
                                        <i class="bi bi-arrow-down-up"></i> Import / Export
                                    </a>
                                </li>
                                @if(auth()->user()->is_admin)
                                    <li>
                                        <a class="dropdown-item" href="{{ route('users.index') }}">
                                            <i class="bi bi-people"></i> Accounts
                                        </a>
                                    </li>
                                @endif
                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    <form action="{{ route('logout') }}" method="post">
                                        @csrf
                                        <button type="submit" class="dropdown-item">
                                            <i class="bi bi-box-arrow-right"></i> Sign out
                                        </button>
                                    </form>
                                </li>
                            </ul>
                        </div>
                    @endauth
                </div>
            </div>
        </div>

        @if(session('status') || session('error') || ($errors->any() && !$errors->has('file')))
            <div class="app-alerts">
                @if(session('status'))
                    <div class="alert alert-success" role="alert">
                        <i class="bi bi-check-circle"></i>
                        <span>{{ session('status') }}</span>
                    </div>
                @endif
                @if(session('error'))
                    <div class="alert alert-danger" role="alert">
                        <i class="bi bi-exclamation-triangle"></i>
                        <span>{{ session('error') }}</span>
                    </div>
                @endif
                @if($errors->any() && !$errors->has('file'))
                    <div class="alert alert-danger" role="alert">
                        <i class="bi bi-exclamation-triangle"></i>
                        <ul class="mb-0">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif
            </div>
        @endif

        <div id="content" class="app-content" tabindex="-1">
            @yield('content')
        </div>
    </main>
</div>

</body>
</html>
