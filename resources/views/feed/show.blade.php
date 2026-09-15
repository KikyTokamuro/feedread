@extends('layouts.main')

@section('buttons')
    <a id="edit-feed-btn" href="{{ route('feed.edit', $feed) }}" class="btn btn-soft" title="Edit this feed">
        <i class="bi bi-pencil"></i> Edit
    </a>
    <form action="{{ route('feed.refresh', $feed) }}" method="post">
        @csrf
        <button id="refresh-feed-btn" type="submit" class="btn btn-soft" title="Fetch new articles now">
            <i class="bi bi-arrow-clockwise"></i> Refresh
        </button>
    </form>
    @if($unreadCount > 0)
        <form action="{{ route('feed.read-all', $feed) }}" method="post">
            @csrf
            <button id="read-all-btn" type="submit" class="btn btn-soft" title="Mark every article as read">
                <i class="bi bi-check2-all"></i> Mark all read
            </button>
        </form>
    @endif
    <a id="delete-feed-btn" href="#" class="btn btn-outline-danger" data-bs-toggle="modal"
       data-bs-target="#deleteFeedModal" title="Delete this feed">
        <i class="bi bi-trash3"></i> Delete
    </a>
@endsection

@section('content')
    <div class="modal" id="deleteFeedModal" tabindex="-1" aria-labelledby="deleteFeedModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="deleteFeedModalLabel">Deleting feed</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    Permanently delete "{{ $feed->title }}" feed?
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-soft" data-bs-dismiss="modal">Cancel</button>
                    <form id="delete_form" action="{{ route('feed.delete', $feed) }}" method="post">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger">Delete</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="page-header">
        <h2>
            {{ $feed->title }}
            @if($unreadCount > 0)
                <span class="badge badge-soft">{{ $unreadCount }} unread</span>
            @endif
        </h2>
        <p class="page-header__meta">
            <a href="{{ $feed->url }}" target="_blank" rel="noopener nofollow">{{ $feed->url }}</a>
            <span class="page-header__meta-item">
                <i class="bi bi-clock-history"></i>
                @if($feed->last_refreshed_at)
                    Updated {{ $feed->last_refreshed_at->diffForHumans() }}
                @else
                    Never refreshed
                @endif
            </span>
            <span class="page-header__meta-item">
                <i class="bi bi-collection"></i> {{ $items->total() }} article(s)
            </span>
        </p>
    </div>
    <hr>

    @if($feed->last_error)
        <div class="empty-state empty-state--error mb-3" role="alert">
            <div>
                <i class="bi bi-exclamation-triangle"></i>
                Last refresh failed: {{ $feed->last_error }}
            </div>
        </div>
    @endif

    @forelse($items as $item)
        <article class="article-card {{ $item->isUnread() ? 'is-unread' : '' }}">
            <div class="article-card__head">
                @if($item->isUnread())
                    <span class="unread-dot" title="Unread" aria-label="Unread"></span>
                @endif

                <h5 class="article-card__title">
                    <a href="{{ route('item.open', $item) }}" target="_blank"
                       rel="noopener nofollow">{{ $item->displayTitle() }}</a>
                </h5>

                <div class="article-card__actions">
                    <form action="{{ route($item->isUnread() ? 'item.read' : 'item.unread', $item) }}"
                          method="post">
                        @csrf
                        <button type="submit" class="btn btn-icon"
                                title="{{ $item->isUnread() ? 'Mark as read' : 'Mark as unread' }}">
                            <i class="bi {{ $item->isUnread() ? 'bi-check2' : 'bi-arrow-counterclockwise' }}"></i>
                        </button>
                    </form>
                </div>
            </div>

            <p class="article-card__excerpt">{{ $item->excerpt() }}</p>

            <p class="article-meta">
                @if($item->published_at)
                    <span><i class="bi bi-calendar3"></i> {{ $item->published_at->format('j M Y, g:i a') }}</span>
                @endif
            </p>
        </article>
    @empty
        <div class="empty-state {{ $feed->last_error ? 'empty-state--error' : '' }}">
            @if($feed->last_error)
                <i class="bi bi-plug empty-state__icon"></i>
                <p class="mb-0">
                    Problem when retrieving data from
                    <a href="{{ $feed->url }}">{{ $feed->url }}</a>
                </p>
            @else
                <i class="bi bi-inbox empty-state__icon"></i>
                <p class="mb-0">
                    No articles stored yet. Use <strong>Refresh</strong> to fetch them, or wait for the
                    scheduled refresh to run.
                </p>
            @endif
        </div>
    @endforelse

    @if($items->hasPages())
        <div class="pt-2 pb-4">
            {{ $items->links() }}
        </div>
    @endif
@endsection
