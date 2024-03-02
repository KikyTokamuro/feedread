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

    @section('buttons')
        <a href="{{ route('feed.edit', $feed->id) }}" class="btn btn-outline border text-decoration-none text-warning">
            <i class="bi bi-pencil-fill bi-lg"></i> Edit
        </a>
        <a href="#" class="btn btn-outline border text-decoration-none text-danger" data-bs-toggle="modal"
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
        <div class="list-group m-lg-2">
            <ul>
                @foreach($data['items'] as $item)
                    <li><small>{{ $item->get_date('j M Y, g:i a') }}</small> - <a
                            href="{{ $item->get_permalink() }}"
                            target="_blank">{{ $item->get_title() }}</a>
                    </li>
                @endforeach
            </ul>
        </div>
    @else
        <div class="p-3 mb-2 bg-danger text-white">Problem when retrieving data from:
            <a href="{{ $feed->url }}" class="link-light">{{ $feed->url }}</a>
        </div>
    @endif
@endsection
