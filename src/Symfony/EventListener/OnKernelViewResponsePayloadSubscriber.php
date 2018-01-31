<?php
namespace Scriber\Component\ApiResponse\Symfony\EventListener;

use Scriber\Component\ApiResponse\ResponseBuilderInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\GetResponseForControllerResultEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class OnKernelViewResponsePayloadSubscriber implements EventSubscriberInterface
{
    /**
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::VIEW => 'onResponsePayload'
        ];
    }

    /**
     * @param GetResponseForControllerResultEvent $event
     */
    public function onResponsePayload(GetResponseForControllerResultEvent $event)
    {
        if ($event->hasResponse()) {
            return;
        }

        $result = $event->getControllerResult();
        if (!$result instanceof ResponseBuilderInterface) {
            return;
        }

        $event->setResponse($result->getResponse());
    }
}
