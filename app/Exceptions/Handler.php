<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\Exceptions\HttpResponseException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * A list of exception types with their corresponding custom log levels.
     */
    protected $levels = [];

    /**
     * A list of the exception types that are not reported.
     */
    protected $dontReport = [];

    /**
     * A list of the inputs that are never flashed to the session on validation exceptions.
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

    /**
     * Render an exception into an HTTP response.
     * Maps HTTP status codes to our custom BiteHub error pages.
     */
    public function render($request, Throwable $e)
    {
        // Let Laravel handle API requests with JSON responses
        if ($request->expectsJson()) {
            return parent::render($request, $e);
        }

        // Handle HTTP exceptions (403, 404, 419, 429, 500, 503 …)
        if (!config('app.debug')) {
            if ($e instanceof HttpException || $e instanceof \Illuminate\Http\Exceptions\ThrottleRequestsException) {
                $status = $e instanceof \Illuminate\Http\Exceptions\ThrottleRequestsException
                    ? 429
                    : $e->getStatusCode();

                $knownCodes = [403, 404, 419, 429, 500, 503];
                $viewCode   = in_array($status, $knownCodes) ? $status : 500;

                if (view()->exists("errors.{$viewCode}")) {
                    return response()->view("errors.{$viewCode}", [], $status);
                }
            }

            // For unexpected server errors (non-HTTP), show 500
            if (view()->exists('errors.500')) {
                return response()->view('errors.500', [], 500);
            }
        }

        return parent::render($request, $e);
    }
}
