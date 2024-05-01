<?php

namespace App\Exceptions;

use App\Http\Traits\Helpers\ApiResponseTrait;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\Exceptions\ThrottleRequestsException;
use Illuminate\Validation\UnauthorizedException;
use Illuminate\Validation\ValidationException;
use Throwable;
use Illuminate\Auth\AuthenticationException;

class Handler extends ExceptionHandler
{
    use ApiResponseTrait;
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

        if ($request->is('api/*')) {
            if($e instanceof ValidationException) {
                return $this->respondValidationErrors($e);
            }

            if ($e instanceof AuthenticationException) {
                return $this->apiResponse(
                    [
                        'success' => false,
                        'message' => 'Unauthenticated or Token Expired, Please Login'
                    ],
                    401
                );
            }

            if ($e instanceof UnauthorizedException) {
                return $this->apiResponse(
                    [
                        'success' => false,
                        'message' => $e->getMessage(),
                    ],
                    401
                );
            }

            if ($e instanceof ThrottleRequestsException) {
                return $this->apiResponse(
                    [
                        'success' => false,
                        'message' => 'Too Many Requests,Please Slow Down'
                    ],
                    429
                );
            }

        }
        return parent::render($request, $e);
    }

}
