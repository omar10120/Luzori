<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\HttpException;
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

    /**
     * Render an exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Throwable  $e
     * @return \Symfony\Component\HttpFoundation\Response
     *
     * @throws \Throwable
     */
    public function render($request, Throwable $e)
    {
        // Handle 401 Unauthorized errors - redirect to login
        if ($e instanceof HttpException && $e->getStatusCode() === 401) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Unauthorized. Please login again.',
                    'redirect' => $this->getLoginRoute($request)
                ], 401);
            }
            
            return redirect($this->getLoginRoute($request));
        }

        return parent::render($request, $e);
    }

    /**
     * Get the login route based on the current URL
     *
     * @param  \Illuminate\Http\Request  $request
     * @return string
     */
    protected function getLoginRoute(Request $request): string
    {
        $currentUrl = $request->url();
        
        if (str_contains($currentUrl, '/admin')) {
            return route('admin.login');
        } elseif (str_contains($currentUrl, '/center_user') || str_contains($currentUrl, '/center-user')) {
            return route('center_user.login');
        }
        
        // Default to center_user login if route name exists
        if (route('center_user.login', [], false)) {
            return route('center_user.login');
        }
        
        return '/login';
    }
}
