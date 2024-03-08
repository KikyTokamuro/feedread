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

        // Feed not found
        if ($e instanceof ModelNotFoundException) {
            if (str_starts_with($request->getRequestUri(), '/feeds/')) {
                $wrongId = $e->getIds()[0];

                return response()->view('feed.not_found', compact('wrongId'));
            }
        }

        return parent::render($request, $e);
    }
}
