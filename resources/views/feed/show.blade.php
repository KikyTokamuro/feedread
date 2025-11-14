@extends('layouts.main')

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
                    <form id="delete_form" action="{{ route('feed.delete', $feed->id) }}" method="post"
                          class="py-2 p-1 d-inline">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger">Delete</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="modal modal-xl" id="previewFeedItemModal" tabindex="-1" aria-labelledby="previewFeedItemModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="previewFeedItemModalLabel"></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div id="preview-loading-spinner" class="d-flex justify-content-center align-items-center position-absolute top-0 start-0 w-100 h-100 bg-light" style="z-index: 10;">
                        <div class="spinner-border text-primary" role="status">
                        </div>
                    </div>
                    <iframe id="preview-feed-item-iframe"></iframe>
                </div>
            </div>
        </div>
    </div>

    @section('buttons')
        <a id="edit-feed-btn" href="{{ route('feed.edit', $feed->id) }}" class="btn btn-outline text-decoration-none">
            <i class="bi bi-pencil-fill bi-lg"></i> Edit
        </a>
        <a id="delete-feed-btn" href="#" class="btn btn-outline text-decoration-none" data-bs-toggle="modal"
           data-bs-target="#deleteFeedModal">
            <i class="bi bi-trash3-fill bi-lg"></i> Delete
        </a>
    @endsection

    <div class="page-header pt-3">
        <h2>{{ $feed->title }}</h2>
    </div>
    @if(isset($data['description']))
        <p class="lead">{{ $data['description'] }}</p>
    @endif()
    <hr>
    @if(isset($data['items']))
        @foreach($data['items'] as $item)
            <div class="card mb-3">
                <div class="row g-0">
                    <div class="col-md">
                        <div class="card-body">
                        <div class="card-preview-title">
                            <a class="preview-feed-item-btn btn btn-outline text-decoration-none" href="#" data-bs-toggle="modal" data-bs-target="#previewFeedItemModal" data-iframe-link="{{ route('iframe.proxy') }}?q={{ urlencode($item['link']) }}">
                                <i class="bi bi-binoculars bi-lg"></i>
                            </a>
                            <h5 class="card-title"><a href="{{ $item['link'] }}" target="_blank">{{ $item['title'] }}</a></h5>
                        </div>
                            <p class="card-text">{{ $item['content'] }}</p>
                            <p class="card-text"><small class="text-muted">{{ $item['date'] }}</small></p>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    @else
        <div class="p-3 mb-2 bg-danger text-white">Problem when retrieving data from:
            <a href="{{ $feed->url }}" class="link-light">{{ $feed->url }}</a>
        </div>
    @endif
@endsection
