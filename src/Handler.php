<?php

namespace Dmn\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * A list of exception types with their corresponding custom log levels.
     *
     * @var array<class-string<\Throwable>, \Psr\Log\LogLevel::*>
     */
    protected $levels = [
        //
    ];

    /**
     * A list of the exception types that are not reported.
     *
     * @var array<int, class-string<\Throwable>>
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed to the session on validation exceptions.
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

        $this->customException();
    }

    /**
     * Custom Exception
     *
     * @return void
     */
    protected function customException(): void
    {
        $this->renderable(function (\Symfony\Component\HttpKernel\Exception\NotFoundHttpException $e) {
            if ($e->getPrevious() instanceof \Illuminate\Database\Eloquent\ModelNotFoundException) {
                throw new \Dmn\Exceptions\ModelNotFoundException($e->getPrevious());
            }
            throw new \Dmn\Exceptions\NotFoundHttpException($e->getMessage(), $e->getCode());
        });

        $this->renderable(function (\Illuminate\Validation\ValidationException $e) {
            throw new \Dmn\Exceptions\ValidationException(
                $e->getMessage(),
                $e->getCode(),
                $e
            );
        });

        $this->renderable(function (\Illuminate\Auth\AuthenticationException $e) {
            throw new \Dmn\Exceptions\ForbiddenException();
        });


        $this->renderable(function (\Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException $e) {
            $exception = new \Dmn\Exceptions\MethodNotAllowedHttpException('', $e->getCode());
            $exception->setDescription($e->getMessage());
            throw $exception;
        });

        $this->renderable(function (\Illuminate\Http\Exceptions\ThrottleRequestsException $e) {
            throw new \Dmn\Exceptions\ThrottleRequestsException(
                $e->getMessage(),
                $e->getPrevious(),
                $e->getHeaders(),
                $e->getCode()
            );
        });
    }
}
