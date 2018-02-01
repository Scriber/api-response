<?php
namespace Scriber\Component\ApiResponse;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

abstract class JsonResponsePayload implements ResponsePayloadInterface, ResponseBuilderInterface
{
    /**
     * @param int $status
     * @param array $headers
     *
     * @return JsonResponse
     */
    public function getResponse(int $status = 200, array $headers = []): Response
    {
        return new JsonResponse(
            $this->getData(),
            $status,
            $headers
        );
    }
}
