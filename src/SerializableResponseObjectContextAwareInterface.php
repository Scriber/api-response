<?php
namespace Scriber\Component\ApiResponse;

interface SerializableResponseObjectContextAwareInterface
{
    /**
     * @return array
     */
    public function serializerContext(): array;
}
