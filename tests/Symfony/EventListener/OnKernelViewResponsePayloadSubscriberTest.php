<?php
namespace Scriber\Component\ApiResponse\Tests\Symfony\EventListener;

use PHPUnit\Framework\TestCase;
use Scriber\Component\ApiResponse\ResponseBuilderInterface;
use Scriber\Component\ApiResponse\Symfony\EventListener\OnKernelViewResponsePayloadSubscriber;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\GetResponseForControllerResultEvent;

class OnKernelViewResponsePayloadSubscriberTest extends TestCase
{
    public function testImplementsEventSubscriberInterface()
    {
        $subscriber = new OnKernelViewResponsePayloadSubscriber();

        static::assertInstanceOf(EventSubscriberInterface::class, $subscriber);
    }

    public function testSubscriberEvents()
    {
        $events = OnKernelViewResponsePayloadSubscriber::getSubscribedEvents();

        $expected = [
            'kernel.view' => 'onResponsePayload'
        ];

        $method = $expected['kernel.view'];
        $methodExists = method_exists(OnKernelViewResponsePayloadSubscriber::class, $method);

        static::assertEquals($expected, $events);
        static::assertTrue($methodExists, sprintf('Method %s not found', $method));
    }

    public function testOnResponsePayloadEventHasResponse()
    {
        $event = $this->createMock(GetResponseForControllerResultEvent::class);
        $event
            ->expects(static::once())
            ->method('hasResponse')
            ->willReturn(true);

        $event
            ->expects(static::never())
            ->method('getControllerResult');

        $subscriber = new OnKernelViewResponsePayloadSubscriber();
        $subscriber->onResponsePayload($event);
    }

    public function testOnResponsePayloadEventResultNotResponseBuilder()
    {
        $event = $this->createMock(GetResponseForControllerResultEvent::class);
        $event
            ->expects(static::once())
            ->method('hasResponse')
            ->willReturn(false);

        $event
            ->expects(static::once())
            ->method('getControllerResult')
            ->willReturn('');

        $event
            ->expects(static::never())
            ->method('setResponse');

        $subscriber = new OnKernelViewResponsePayloadSubscriber();
        $subscriber->onResponsePayload($event);
    }

    public function testOnResponsePayloadSetResponse()
    {
        $event = $this->createMock(GetResponseForControllerResultEvent::class);
        $controllerResult = $this->createMock(ResponseBuilderInterface::class);
        $response = $this->createMock(Response::class);

        $controllerResult
            ->expects(static::once())
            ->method('getResponse')
            ->willReturn($response);

        $event
            ->expects(static::once())
            ->method('hasResponse')
            ->willReturn(false);

        $event
            ->expects(static::once())
            ->method('getControllerResult')
            ->willReturn($controllerResult);

        $event
            ->expects(static::once())
            ->method('setResponse')
            ->with($response);

        $subscriber = new OnKernelViewResponsePayloadSubscriber();
        $subscriber->onResponsePayload($event);
    }
}
