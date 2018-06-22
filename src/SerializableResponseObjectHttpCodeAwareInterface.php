<?php
namespace Scriber\Component\ApiResponse;

interface SerializableResponseObjectHttpCodeAwareInterface
{
    /**
     * @return int
     */
    public function responseHttpCode(): int;
}
