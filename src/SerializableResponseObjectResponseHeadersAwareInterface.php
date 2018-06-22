<?php
namespace Scriber\Component\ApiResponse;

interface SerializableResponseObjectResponseHeadersAwareInterface
{
    /**
     * @return array
     */
    public function responseHeaders(): array;
}
