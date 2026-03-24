<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Session\TokenMismatchException;
use Throwable;
use Illuminate\Http\Exceptions\PostTooLargeException;
use Illuminate\Validation\ValidationException;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that are not reported.
     *
     * @var array<int, class-string<Throwable>>
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed for validation exceptions.
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
     *
     * @return void
     */
    public function register()
    {
        $this->reportable(function (Throwable $e) {
            //
        });

        // Gracefully handle oversized POST uploads (PHP limits like upload_max_filesize/post_max_size)
        $this->renderable(function (PostTooLargeException $e, $request) {
            $max = ini_get('upload_max_filesize') ?: 'unknown';

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => "Uploaded file is too large. Max allowed: {$max}",
                ], 413);
            }

            return redirect()->back()->withInput()->with('error', "Uploaded file is too large. Max allowed: {$max}");
        });

        // Gracefully handle CSRF/session expiration cases.
        $this->renderable(function (TokenMismatchException $e, $request) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Your session has expired. Please refresh the page and try again.',
                ], 419);
            }

            $request->session()->regenerateToken();

            return redirect()->route('login')
                ->with('warning', 'Your session expired for security reasons. Please sign in again to continue.');
        });

        // Return user-friendly validation details for all JSON form submissions.
        $this->renderable(function (ValidationException $e, $request) {
            if (!$request->expectsJson()) {
                return null;
            }

            $errors = $e->errors();
            $errorList = [];

            foreach ($errors as $field => $messages) {
                $label = ucwords(str_replace('_', ' ', $field));
                $firstMessage = $messages[0] ?? 'This field is invalid.';
                $errorList[] = "{$label}: {$firstMessage}";
            }

            $summary = count($errorList) > 0
                ? 'Please correct the highlighted fields and submit again.'
                : 'Some submitted data is invalid. Please review the form and try again.';

            return response()->json([
                'success' => false,
                'message' => $summary,
                'errors' => $errors,
                'error_list' => $errorList,
            ], 422);
        });
    }
}
