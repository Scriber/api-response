<?php
namespace Scriber\Component\ApiResponse\Symfony\EventListener;

use Scriber\Component\ApiResponse\SerializableResponseObjectHttpCodeAwareInterface;
use Scriber\Component\ApiResponse\SerializableResponseObjectInterface;
use Scriber\Component\ApiResponse\SerializableResponseObjectResponseHeadersAwareInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Event\GetResponseForControllerResultEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Serializer\SerializerInterface;

class OnKernelViewSerializableResponseObjectSubscriber implements EventSubscriberInterface
{
    /**
     * @var SerializerInterface
     */
    private $serializer;

    /**
     * @param SerializerInterface $serializer
     */
    public function __construct(SerializerInterface $serializer)
    {
        $this->serializer = $serializer;
    }

    /**
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::VIEW => 'serializableResponseObject'
        ];
    }

    /**
     * @param GetResponseForControllerResultEvent $event
     */
    public function serializableResponseSubscriber(GetResponseForControllerResultEvent $event)
    {
        if ($event->hasResponse()) {
            return;
        }

        $result = $event->getControllerResult();
        if (!$result instanceof SerializableResponseObjectInterface) {
            return;
        }

        $statusCode = $result instanceof SerializableResponseObjectHttpCodeAwareInterface ? $result->responseHttpCode() : 200;
        $headers = $result instanceof SerializableResponseObjectResponseHeadersAwareInterface ? $result->responseHeaders() : [];

        $event->setResponse(
            JsonResponse::fromJsonString(
                $this->serializer->serialize(
                    $result,
                    'json'
                ),
                $statusCode,
                $headers
            )
        );
    }
}
