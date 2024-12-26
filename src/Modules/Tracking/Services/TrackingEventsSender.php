<?php

namespace Featurit\Client\Modules\Tracking\Services;

use Featurit\Client\Modules\Tracking\Exceptions\CantSendPeopleToServerException;
use Featurit\Client\Modules\Tracking\Exceptions\CantSendTrackingEventsToServerException;
use Http\Client\Common\HttpMethodsClientInterface;

class TrackingEventsSender
{
    public function __construct(
        private HttpMethodsClientInterface $httpMethodsClient
    )
    {
    }

    /**
     * @param array $events
     * @return void
     * @throws CantSendTrackingEventsToServerException
     */
    public function sendTrackingEvents(array $events = []): void
    {
        try {
            $trackingEventsApiResponse = $this->httpMethodsClient->post(
                '/track',
                [],
                json_encode(["events" => $events])
            );

            if ($trackingEventsApiResponse->getStatusCode() != 200) {
                throw new CantSendTrackingEventsToServerException("Error sending Tracking Events to the API");
            }
        } catch (\Http\Client\Exception $exception) {
            throw new CantSendTrackingEventsToServerException($exception->getMessage(), $exception->getCode(), $exception);
        }
    }

    /**
     * @param array $people
     * @return void
     * @throws CantSendPeopleToServerException
     */
    public function sendPeople(array $people = []): void
    {
        try {
            $peopleApiResponse = $this->httpMethodsClient->post(
                '/people',
                [],
                json_encode(["people" => $people])
            );

            if ($peopleApiResponse->getStatusCode() != 200) {
                throw new CantSendPeopleToServerException("Error sending People to the API");
            }
        } catch (\Http\Client\Exception $exception) {
            throw new CantSendPeopleToServerException($exception->getMessage(), $exception->getCode(), $exception);
        }
    }
}