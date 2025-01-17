<?php

namespace Featurit\Client\Tests\Modules\Tracking;

use Featurit\Client\Modules\Tracking\Exceptions\CantSendPeopleToServerException;
use Featurit\Client\Modules\Tracking\Exceptions\CantSendTrackingEventsToServerException;
use Featurit\Client\Modules\Tracking\Services\TrackingEventsSender;
use Http\Client\Common\HttpMethodsClientInterface;
use Laminas\Diactoros\Response;
use PHPUnit\Framework\TestCase;

class TrackingEventsSenderTest extends TestCase
{
    /**
     * @throws CantSendTrackingEventsToServerException
     * @throws CantSendPeopleToServerException
     */
    public function test_it_can_send_a_simple_request(): void
    {
        $mockHttpMethodsClient = $this->createMock(HttpMethodsClientInterface::class);
        $mockHttpMethodsClient->method('post')->willReturn(new Response(
            'php://memory',
            200
        ));

        $trackingEventsSender = new TrackingEventsSender($mockHttpMethodsClient);
        $trackingEventsSender->sendTrackingEvents();
        $trackingEventsSender->sendPeople();

        $this->assertTrue(true);
    }

    public function test_it_sends_the_right_exception_on_non_200_http_status_response(): void
    {
        $mockHttpMethodsClient = $this->createMock(HttpMethodsClientInterface::class);
        $mockHttpMethodsClient->method('post')->willReturn(new Response(
            'php://memory',
            500
        ));

        $this->expectException(CantSendTrackingEventsToServerException::class);

        $trackingEventsSender = new TrackingEventsSender($mockHttpMethodsClient);
        $trackingEventsSender->sendTrackingEvents();

        $this->expectException(CantSendPeopleToServerException::class);

        $trackingEventsSender = new TrackingEventsSender($mockHttpMethodsClient);
        $trackingEventsSender->sendPeople();
    }
}