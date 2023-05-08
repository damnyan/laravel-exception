<?php

namespace Dmn\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * Register the exception handling callbacks for the application.
     *
     * @return void
     */
    public function register()
    {
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
