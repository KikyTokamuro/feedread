@extends('layouts.main')

@section('content')
    <div class="page-header">
        <h2>New account</h2>
        <p class="page-header__meta">Accounts are created by hand; there is no public sign up.</p>
    </div>
    <hr>

    <div class="row">
        <div class="col-lg">
            <form class="panel" action="{{ route('users.store') }}" method="post">
                @csrf

                <div class="mb-3">
                    <label for="name" class="form-label">Name</label>
                    <input id="name" name="name" type="text" value="{{ old('name') }}"
                           class="form-control" required autofocus>
                    @error('name')
                        <p class="form-error">{{ $message }}</p>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="email" class="form-label">Email</label>
                    <input id="email" name="email" type="email" value="{{ old('email') }}"
                           class="form-control" required>
                    @error('email')
                        <p class="form-error">{{ $message }}</p>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="password" class="form-label">Password</label>
                    <input id="password" name="password" type="password" class="form-control"
                           autocomplete="new-password">
                    <div class="form-text">Leave empty to generate one and show it once.</div>
                    @error('password')
                        <p class="form-error">{{ $message }}</p>
                    @enderror
                </div>

                <div class="mb-4">
                    <label for="password_confirmation" class="form-label">Repeat password</label>
                    <input id="password_confirmation" name="password_confirmation" type="password"
                           class="form-control" autocomplete="new-password">
                </div>

                <div class="form-check form-switch mb-4">
                    <input id="is_admin" name="is_admin" type="checkbox" role="switch" value="1"
                           class="form-check-input" @checked(old('is_admin'))>
                    <label for="is_admin" class="form-check-label">
                        <span class="d-block fw-semibold">Administrator</span>
                        <span class="form-text d-block mt-0">Can create and delete other accounts.</span>
                    </label>
                </div>

                <div class="d-flex justify-content-end gap-2">
                    <a href="{{ route('users.index') }}" class="btn btn-soft">Cancel</a>
                    <button id="save-btn" type="submit" class="btn btn-accent">
                        <i class="bi bi-person-plus"></i> Create account
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection
