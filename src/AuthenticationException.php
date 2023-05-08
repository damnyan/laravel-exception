<?php

namespace Dmn\Exceptions;

use Dmn\Exceptions\Exception;
use Illuminate\Http\Response;

class AuthenticationException extends Exception
{
    protected $code = 'unauthorized';

    protected $message = 'Unauthorized';

    protected $httpStatusCode = Response::HTTP_UNAUTHORIZED;
}
