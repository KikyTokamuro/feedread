@extends('layouts.main')

@section('content')
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
    @endif
@endsection
