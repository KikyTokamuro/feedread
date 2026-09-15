@extends('layouts.main')

@section('content')
    <div class="page-header">
        <h2>Settings</h2>
        <p class="page-header__meta">These settings are stored on your account.</p>
    </div>
    <hr>

    <div class="row">
        <div class="col-lg">
            <form class="panel" action="{{ route('settings.update') }}" method="post">
                @csrf
                @method('PATCH')

                <div class="form-check form-switch mb-4">
                    <input id="dark" name="dark" type="checkbox" role="switch" value="1"
                           class="form-check-input" @checked($user->dark)>
                    <label for="dark" class="form-check-label">
                        <span class="d-block fw-semibold">Dark colour scheme</span>
                        <span class="form-text d-block mt-0">
                            Switches the whole interface, including the sidebar, to the dark palette.
                        </span>
                    </label>
                    @error('dark')
                        <p class="form-error">{{ $message }}</p>
                    @enderror
                </div>

                <div class="d-flex justify-content-end">
                    <button id="save-btn" type="submit" class="btn btn-accent">
                        <i class="bi bi-check-lg"></i> Save
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection
