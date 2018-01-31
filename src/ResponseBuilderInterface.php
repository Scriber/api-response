<?php
namespace Scriber\Component\ApiResponse;

use Symfony\Component\HttpFoundation\Response;

interface ResponseBuilderInterface
{
    /**
     * @return Response
     */
    public function getResponse(): Response;
}
