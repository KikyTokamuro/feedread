@extends('layouts.main')

@section('content')
    <div class="page-header">
        <h2>Import &amp; Export</h2>
        <p class="page-header__meta">
            OPML is the subscription format every other feed reader understands, so you can move your
            feeds in and out of FeedRead freely.
        </p>
    </div>
    <hr>

    <div class="row">
        <div class="col-lg-6 mb-4">
            <div class="panel h-100 d-flex flex-column">
                <h3 class="panel__title"><i class="bi bi-box-arrow-down"></i> Export</h3>
                <p class="text-secondary">
                    Download all {{ $feedCount }} feed(s) of this account as a single OPML file.
                </p>

                <div class="mt-auto">
                    @if($feedCount > 0)
                        <a id="export-opml-btn" href="{{ route('opml.export') }}" class="btn btn-accent">
                            <i class="bi bi-download"></i> Download OPML
                        </a>
                    @else
                        <button class="btn btn-soft" disabled>Nothing to export yet</button>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-lg-6 mb-4">
            <div class="panel h-100 d-flex flex-column">
                <h3 class="panel__title"><i class="bi bi-box-arrow-in-up"></i> Import</h3>
                <p class="text-secondary">
                    Upload an <code>.opml</code> or <code>.xml</code> file. Feeds you already have are
                    skipped, so importing twice is safe.
                </p>

                <form action="{{ route('opml.import') }}" method="post" enctype="multipart/form-data"
                      class="mt-auto">
                    @csrf

                    <div class="mb-3">
                        <input id="file" name="file" type="file" accept=".opml,.xml,text/xml,text/x-opml"
                               class="form-control" required>
                        @error('file')
                            <p class="form-error">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="d-flex justify-content-end">
                        <button id="import-opml-btn" type="submit" class="btn btn-accent">
                            <i class="bi bi-upload"></i> Import feeds
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
