<?php
namespace Scriber\Component\ApiResponse\Tests\Symfony\EventListener;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Scriber\Component\ApiResponse\SerializableResponseObjectHttpCodeAwareInterface;
use Scriber\Component\ApiResponse\SerializableResponseObjectInterface;
use Scriber\Component\ApiResponse\SerializableResponseObjectResponseHeadersAwareInterface;
use Scriber\Component\ApiResponse\Symfony\EventListener\OnKernelViewSerializableResponseObjectSubscriber;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\GetResponseForControllerResultEvent;
use Symfony\Component\Serializer\SerializerInterface;

class OnKernelViewSerializableResponseObjectSubscriberTest extends TestCase
{
    /**
     * @var MockObject|SerializerInterface
     */
    private $serializer;

    protected function setUp()
    {
        $this->serializer = $this->createMock(SerializerInterface::class);
    }

    protected function tearDown()
    {
        $this->serializer = null;
    }

    public function testImplementsEventSubscriberInterface()
    {
        $subscriber = new OnKernelViewSerializableResponseObjectSubscriber($this->serializer);

        static::assertInstanceOf(EventSubscriberInterface::class, $subscriber);
    }

    public function testSubscriberEvents()
    {
        $events = OnKernelViewSerializableResponseObjectSubscriber::getSubscribedEvents();

        $expected = [
            'kernel.view' => 'serializableResponseObject'
        ];

        static::assertEquals($expected, $events);
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

        $subscriber = new OnKernelViewSerializableResponseObjectSubscriber($this->serializer);
        $subscriber->serializableResponseSubscriber($event);
    }

    public function testOnResponsePayloadEventResultNotSerializableResponseObject()
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

        $subscriber = new OnKernelViewSerializableResponseObjectSubscriber($this->serializer);
        $subscriber->serializableResponseSubscriber($event);
    }

    public function testOnResponsePayloadSetResponse()
    {
        $event = $this->createMock(GetResponseForControllerResultEvent::class);
        $controllerResult = $this->createMock(SerializableResponseObjectInterface::class);

        $serializedContent = json_encode(['test' => 'data']);

        $event
            ->expects(static::once())
            ->method('hasResponse')
            ->willReturn(false);

        $event
            ->expects(static::once())
            ->method('getControllerResult')
            ->willReturn($controllerResult);

        $this->serializer
            ->expects(static::once())
            ->method('serialize')
            ->with($controllerResult, 'json')
            ->willReturn($serializedContent);

        $event
            ->expects(static::once())
            ->method('setResponse')
            ->with(static::callback(function (JsonResponse $response) use ($serializedContent) {
                return $response instanceof JsonResponse &&
                    $response->getStatusCode() === 200 &&
                    $response->getContent() === $serializedContent;
            }));

        $subscriber = new OnKernelViewSerializableResponseObjectSubscriber($this->serializer);
        $subscriber->serializableResponseSubscriber($event);
    }

    public function testOnResponsePayloadSetResponseStatusCode()
    {
        $statusCode = 400;

        $event = $this->createMock(GetResponseForControllerResultEvent::class);
        $controllerResult = new class($statusCode) implements
            SerializableResponseObjectInterface,
            SerializableResponseObjectHttpCodeAwareInterface {
            private $statusCode;

            public function __construct(int $statusCode)
            {
                $this->statusCode = $statusCode;
            }

            public function responseHttpCode(): int
            {
                return $this->statusCode;
            }
        };

        $event
            ->expects(static::once())
            ->method('hasResponse')
            ->willReturn(false);

        $event
            ->expects(static::once())
            ->method('getControllerResult')
            ->willReturn($controllerResult);

        $this->serializer
            ->expects(static::once())
            ->method('serialize')
            ->willReturn('{}');

        $event
            ->expects(static::once())
            ->method('setResponse')
            ->with(static::callback(function (JsonResponse $response) use ($statusCode) {
                return $response instanceof JsonResponse &&
                    $response->getStatusCode() === $statusCode;
            }));

        $subscriber = new OnKernelViewSerializableResponseObjectSubscriber($this->serializer);
        $subscriber->serializableResponseSubscriber($event);
    }

    public function testOnResponsePayloadSetResponseHeaders()
    {
        $testHeader = 'test';
        $testHeaderValue = 'value';

        $event = $this->createMock(GetResponseForControllerResultEvent::class);
        $controllerResult = new class([$testHeader => $testHeaderValue]) implements
            SerializableResponseObjectInterface,
            SerializableResponseObjectResponseHeadersAwareInterface {
            private $headers;

            public function __construct(array $headers)
            {
                $this->headers = $headers;
            }

            public function responseHeaders(): array
            {
                return $this->headers;
            }
        };

        $event
            ->expects(static::once())
            ->method('hasResponse')
            ->willReturn(false);

        $event
            ->expects(static::once())
            ->method('getControllerResult')
            ->willReturn($controllerResult);

        $this->serializer
            ->expects(static::once())
            ->method('serialize')
            ->willReturn('{}');

        $event
            ->expects(static::once())
            ->method('setResponse')
            ->with(static::callback(function (JsonResponse $response) use ($testHeader, $testHeaderValue) {
                return $response instanceof JsonResponse &&
                    $response->headers->has($testHeader) &&
                    $response->headers->get($testHeader) === $testHeaderValue;
            }));

        $subscriber = new OnKernelViewSerializableResponseObjectSubscriber($this->serializer);
        $subscriber->serializableResponseSubscriber($event);
    }
}
