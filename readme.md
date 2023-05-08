# Installation
`composer require dmn/laravel-exception`

# Usage
Extend `Dmn\Exceptions\Handler` to your `app/Exceptions/Handler.php`
```php
<?php

namespace App\Exceptions;

use Dmn\Exceptions\Handler as ExceptionHandler;
use Throwable;

class Handler extends ExceptionHandler
{
....
```

To add more custom exceptions, add new exception by running `php artisan make:exception` and extend `Dmn\Exceptions\Exception` instead of `\Exception`.
New exceptions need to have `$code`, `$message` and `$httpStatusCode`. You may add `$description`.
```php
<?php

namespace App\Exceptions;

use Dmn\Exceptions\Exception;
use Illuminate\Http\Response;

class NewCustomException extends Exception
{
    protected $code = 'sample_error_code';

    protected $message = 'Sample error message';

    protected $httpStatusCode = Response::HTTP_BAD_REQUEST;

    protected $description = 'Sample more detailed error description.';
}

```

The above exception will render:
```php
{
    status_code: 400,
    code: "sample_error_code",
    message: "Sample error message.",
    description: "Sample more details error description.",
}
```

To override other exceptions from other packages or from laravel itself, you can add it inside `customException()`.

```php
protected function customException(): void
{
    parent::customException();

    // your overrides here
    // example
    $this->renderable(function (\Vendor\Package\Exception $e) {
        throw new App\Exceptions\NewCustomException();
    });
}
```

# Other usage option (not recommended)
You may use `Dmn\Exceptions\Handler` directly from your `bootstrap/app.php`
```php
$app->singleton(
    Illuminate\Contracts\Debug\ExceptionHandler::class,
    // App\Exceptions\Handler::class <-- change this
    Dmn\Exceptions\Handler
);
```


