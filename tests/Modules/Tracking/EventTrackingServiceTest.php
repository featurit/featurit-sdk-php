<?php

namespace Featurit\Client\Tests\Modules\Tracking;

use Featurit\Client\Modules\Segmentation\DefaultFeaturitUserContext;
use Featurit\Client\Modules\Tracking\Services\EventTrackingService;
use Featurit\Client\Modules\Tracking\Services\TrackingEventsSender;
use PHPUnit\Framework\TestCase;

class EventTrackingServiceTest extends TestCase
{
    public function test_it_doesnt_send_events_if_not_necessary(): void
    {
        $mockTrackEventsSender = $this->createMock(TrackingEventsSender::class);
        $mockTrackEventsSender->expects($this->never())->method("sendTrackingEvents");

        $eventTrackingService = new EventTrackingService($mockTrackEventsSender, true);
        $eventTrackingService->addPeople(
            new DefaultFeaturitUserContext("aaa", "b23", "0.0.0.0")
        );
    }

    public function test_it_doesnt_send_people_if_not_necessary(): void
    {
        $mockTrackEventsSender = $this->createMock(TrackingEventsSender::class);
        $mockTrackEventsSender->expects($this->never())->method("sendPeople");

        $eventTrackingService = new EventTrackingService($mockTrackEventsSender, true);
        $eventTrackingService->track("event", [
            "hello" => "world"
        ]);
    }

    public function test_it_tracks_an_event(): void
    {
        $mockTrackEventsSender = $this->createMock(TrackingEventsSender::class);
        $mockTrackEventsSender->expects($this->once())->method("sendTrackingEvents");

        $eventTrackingService = new EventTrackingService($mockTrackEventsSender, true);

        $eventTrackingService->track("event", [
            "hello" => "world"
        ]);
    }

    public function test_it_tracks_a_person(): void
    {
        $mockTrackEventsSender = $this->createMock(TrackingEventsSender::class);
        $mockTrackEventsSender->expects($this->once())->method("sendPeople");

        $eventTrackingService = new EventTrackingService($mockTrackEventsSender, true);

        $eventTrackingService->addPeople(
            new DefaultFeaturitUserContext("123", "666", "192.168.0.9")
        );
    }
}