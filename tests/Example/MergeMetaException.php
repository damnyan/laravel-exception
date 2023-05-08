<?php

namespace Tests\Example;

use Dmn\Exceptions\Exception;

class MergeMetaException extends Exception
{
    /**
     * @inheritDoc
     */
    public function getMeta(): array
    {
        return [
            'test' => 'test meta',
        ];
    }
}
