<?php

namespace App\Exceptions;

use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * The list of the inputs that are never flashed to the session on validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     */
    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
            //
        });
    }

    public function render($request, Throwable $e)
    {
        // A feed that does not exist (or belongs to somebody else) gets the app
        // styled error page instead of the generic one. Route model binding runs
        // before the auth middleware, so a visitor without a session is sent to
        // the sign in page rather than shown an empty shell.
        if ($e instanceof ModelNotFoundException && str_starts_with($request->getRequestUri(), '/feeds/')) {
            if (! $request->user()) {
                return redirect()->route('login');
            }

            return response()->view('feed.not_found', ['wrongId' => $e->getIds()[0] ?? null], 404);
        }

        return parent::render($request, $e);
    }
}
