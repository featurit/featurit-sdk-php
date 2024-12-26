<?php

namespace Featurit\Client\Tests\Modules\Tracking;

use Featurit\Client\Modules\Tracking\Services\EventTrackingService;
use Featurit\Client\Modules\Tracking\Services\TrackingEventsSender;
use PHPUnit\Framework\TestCase;

class EventTrackingServiceTest extends TestCase
{
    public function test_it_tracks_an_event(): void
    {
        $mockTrackEventsSender = $this->createMock(TrackingEventsSender::class);
        $mockTrackEventsSender->expects($this->once())->method("sendTrackingEvents");
        $mockTrackEventsSender->expects($this->once())->method("sendPeople");

        $eventTrackingService = new EventTrackingService($mockTrackEventsSender, true);

        $eventTrackingService->track("event", [
            "hello" => "world"
        ]);
    }
}