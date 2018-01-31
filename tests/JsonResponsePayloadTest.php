<?php
namespace Scriber\Component\ApiResponse\Tests;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Scriber\Component\ApiResponse\JsonResponsePayload;
use Scriber\Component\ApiResponse\ResponseBuilderInterface;
use Scriber\Component\ApiResponse\ResponsePayloadInterface;
use Symfony\Component\HttpFoundation\JsonResponse;

class JsonResponsePayloadTest extends TestCase
{
    /**
     * @param string $interface
     *
     * @dataProvider implementsInterfaceProvider
     */
    public function testImplementsInterface(string $interface)
    {
        static::assertInstanceOf($interface, $this->getMockForAbstractClass(JsonResponsePayload::class));
    }

    /**
     * @return array
     */
    public function implementsInterfaceProvider(): array
    {
        return [
            [ResponseBuilderInterface::class],
            [ResponsePayloadInterface::class],
        ];
    }

    public function testGetResponse()
    {
        /** @var MockObject|JsonResponsePayload $payload */
        $payload = $this->getMockForAbstractClass(JsonResponsePayload::class);

        $data = ['test' => 'test'];
        $expected = json_encode($data);

        $payload
            ->expects(static::once())
            ->method('getData')
            ->willReturn($data);

        $result = $payload->getResponse();

        static::assertInstanceOf(JsonResponse::class, $result);
        static::assertEquals($expected, $result->getContent());
    }
}
