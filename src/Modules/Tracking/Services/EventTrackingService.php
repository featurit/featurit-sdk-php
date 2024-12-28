<?php

namespace Featurit\Client\Modules\Tracking\Services;

use Featurit\Client\Modules\Segmentation\FeaturitUserContext;
use Featurit\Client\Modules\Tracking\Exceptions\CantSendPeopleToServerException;
use Featurit\Client\Modules\Tracking\Exceptions\CantSendTrackingEventsToServerException;

class EventTrackingService
{
    private array $events = [];
    private array $globalProperties = [];

    private array $people = [];

    public function __construct(
        private TrackingEventsSender $trackingEventsSender,
        private bool $isEventTrackingEnabled = false
    )
    {
    }

    public function __destruct()
    {
        if (!$this->isEventTrackingEnabled) {
            return;
        }

        $this->flush();
    }

    public function track(string $eventName, array $properties = []): void
    {
        if (!$this->isEventTrackingEnabled) {
            return;
        }

        if (!isset($properties["time"])) {
            $properties["time"] = microtime(true);
        }

        $event["event"] = $eventName;
        $event["properties"] = array_merge($properties, $this->globalProperties);

        $this->events[] = $event;
    }

    public function register(string $propertyName, mixed $value): void
    {
        if (!$this->isEventTrackingEnabled) {
            return;
        }

        if ($propertyName == "") {
            return;
        }

        $this->globalProperties[$propertyName] = $value;
    }

    public function addPerson(FeaturitUserContext $featuritUserContext): void
    {
        if (!$this->isEventTrackingEnabled) {
            return;
        }

        if ($featuritUserContext->getUserId() != null) {
            $this->register("distinct_id", $featuritUserContext->getUserId());
            $this->people[$featuritUserContext->getUserId()] = array_merge(
                $featuritUserContext->toArray(),
                $this->globalProperties,
                ["time" => microtime(true)],
            );
        } else if ($featuritUserContext->getSessionId() != null) {
            $this->register("distinct_id", $featuritUserContext->getSessionId());
            $this->people[$featuritUserContext->getSessionId()] = array_merge(
                $featuritUserContext->toArray(),
                $this->globalProperties,
                ["time" => microtime(true)],
            );
        }
    }

    public function flush(): void
    {
        if (!$this->isEventTrackingEnabled) {
            return;
        }

        $remainingAttempts = 10;
        while ($remainingAttempts > 0) {
            try {
                // TODO: Improve batching strategy (can grow too big)
                if (count($this->events) > 0) {
                    $this->trackingEventsSender->sendTrackingEvents($this->events);
                    $this->events = [];
                }

                if (count($this->people) > 0) {
                    $this->trackingEventsSender->sendPeople($this->people);
                    $this->people = [];
                }

                return;
            } catch (CantSendTrackingEventsToServerException|CantSendPeopleToServerException $exception) {

            } finally {
                $remainingAttempts--;
            }
        }
    }
}