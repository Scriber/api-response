<?php
namespace Scriber\Component\ApiResponse;

interface ResponsePayloadInterface
{
    /**
     * @return array
     */
    public function getPayload(): array;

    /**
     * @return array
     */
    public function getData(): array;
}
