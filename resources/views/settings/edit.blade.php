@extends('layouts.main')

@section('content')
    <div class="page-header pt-3">
        <h2>Settings</h2>
    </div>
    <hr>
    <div class="row">
        <form action="{{ route('settings.update') }}" method="post">
            @csrf
            @method('PATCH')

            <div class="mb-3">
                <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" role="switch" id="preview" name="preview" 
                        @if($settings->preview) checked @endif>
                    <label class="form-check-label" for="preview">Show preview for feed item</label>
                </div>
                @error('preview')
                    <p class="text-danger p-lg-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="mb-3">
                <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" role="switch" id="dark" name="dark"
                        @if($settings->dark) checked @endif>
                    <label class="form-check-label" for="dark">Dark colorscheme</label>
                </div>
                @error('dark')
                    <p class="text-danger p-lg-1">{{ $message }}</p>
                @enderror
            </div>
            
            <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                <button id="save-btn" type="submit" class="btn border-0 btn-primary">Save</button>
            </div>
        </form>
    </div>
@endsection
