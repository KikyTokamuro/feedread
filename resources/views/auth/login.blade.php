<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#1b1f2e">
    <title>Sign in · FeedRead</title>

    @vite(['resources/sass/app.scss', 'resources/js/app.js'])
</head>
<body>
<div class="auth-page">
    <form class="auth-card" action="{{ route('login') }}" method="post">
        @csrf

        <div class="auth-card__brand">
            <span class="brand-mark"><i class="bi bi-rss-fill"></i></span>
            <span>FeedRead</span>
        </div>

        @if($errors->any())
            <div class="alert alert-danger py-2" role="alert">
                <i class="bi bi-exclamation-triangle"></i>
                <span>{{ $errors->first() }}</span>
            </div>
        @endif

        <div class="mb-3">
            <label for="email" class="form-label">Email</label>
            <input id="email" name="email" type="email" value="{{ old('email') }}"
                   class="form-control" required autofocus autocomplete="username">
        </div>

        <div class="mb-3">
            <label for="password" class="form-label">Password</label>
            <input id="password" name="password" type="password"
                   class="form-control" required autocomplete="current-password">
        </div>

        <div class="form-check mb-4">
            <input id="remember" name="remember" type="checkbox" value="1" class="form-check-input">
            <label for="remember" class="form-check-label">Remember me</label>
        </div>

        <div class="d-grid">
            <button id="save-btn" type="submit" class="btn btn-accent">Sign in</button>
        </div>
    </form>
</div>
</body>
</html>
