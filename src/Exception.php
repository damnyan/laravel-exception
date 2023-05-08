<?php

namespace Dmn\Exceptions;

use Exception as BaseException;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Config;
use Throwable;

class Exception extends BaseException
{
    protected $code = 'unexpected_error';

    protected $message = 'Unexpected error.';

    protected $description = null;

    protected $httpStatusCode = 400;

    /**
     * Construct
     *
     * @param string $message
     * @param int $code
     * @param Throwable $previous
     */
    public function __construct(
        $message = '',
        $code = 0,
        Throwable $previous = null
    ) {
        $message = $message == '' ? ($this->message ?? '') : $message;
        parent::__construct($message, $code, $previous);
    }

    /**
     * Get error description
     *
     * @return string
     */
    public function getDescription(): string
    {
        return $this->description ?? $this->getMessage();
    }

    /**
     * Set description
     *
     * @param string $description
     * @return void
     */
    public function setDescription(string $description): void
    {
        $this->description = $description;
    }

    /**
     * Set HTTP status code
     *
     * @param integer $httpStatusCode
     * @return void
     */
    public function setHttpStatusCode(int $httpStatusCode = 400): void
    {
        $this->httpStatusCode = $httpStatusCode;
    }

    /**
     * Set code
     *
     * @param string|int $code
     * @return void
     */
    public function setCode(string|int $code = 'unexpected_error'): void
    {
        $this->code = $code;
    }

    /**
     * Get meta
     *
     * @return array
     */
    public function getMeta(): array
    {
        return [];
    }

    /**
     * Render an exception to HTTP Response
     *
     * @return void
     */
    public function render()
    {
        $response = [
            'status_code' => $this->httpStatusCode,
            'error' => $this->code,
            'message' => $this->getMessage(),
            'description' => $this->getDescription(),
        ];

        $this->mergeMeta($response);
        $this->mergeErrorResponse($response);

        return new JsonResponse($response, $this->httpStatusCode);
    }

    /**
     * Merge error response for validation exception
     *
     * @param array $response
     * @return void
     */
    private function mergeErrorResponse(array &$response): void
    {
        $previous = $this->getPrevious();

        if (!is_null($previous)) {
            if (method_exists($previous, 'errors')) {
                $response['errors'] = $previous->errors();
                $this->setReferences($response);
            }
        }
    }

    /**
     * Set reference if any
     *
     * @param array $response
     *
     * @return void
     */
    private function setReferences(array &$response): void
    {
        $group = Config::get('validation.default_group');
        $references = (array) Config::get('validation.references.' . $group);

        foreach ($references as $reference => $data) {
            $pattern = '/^' . $reference . '(\.\S+)?$/i';
            $matches = preg_grep($pattern, array_keys($response['errors']));
            if (count($matches) > 0) {
                $route = route('reference.' . $group . '.' . $reference);
                $response['meta']['references'][$reference] = $route;
            }
        }
    }

    /**
     * Merge meta to response
     *
     * @param array $response
     *
     * @return void
     */
    private function mergeMeta(array &$response): void
    {
        if (count($this->getMeta()) < 1) {
            return;
        }

        $response['meta'] = $this->getMeta();
    }
}
