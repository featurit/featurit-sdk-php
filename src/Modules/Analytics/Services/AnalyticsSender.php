<?php

namespace Featurit\Client\Modules\Analytics\Services;

use Featurit\Client\Modules\Analytics\AnalyticsBucket;
use Featurit\Client\Modules\Analytics\Exceptions\CantSendAnalyticsToServerException;
use Http\Client\Common\HttpMethodsClientInterface;

class AnalyticsSender
{
    public function __construct(
        private HttpMethodsClientInterface $httpMethodsClient
    )
    {
    }

    /**
     * @throws CantSendAnalyticsToServerException
     */
    public function sendAnalyticsBucket(AnalyticsBucket $analyticsBucket): void
    {
        try {
            $featureFlagsApiResponse = $this->httpMethodsClient->post(
                '/analytics',
                [],
                json_encode($analyticsBucket->jsonSerialize())
            );

            if ($featureFlagsApiResponse->getStatusCode() != 200) {
                throw new CantSendAnalyticsToServerException("Error sending Analytics to the API");
            }
        } catch (\Http\Client\Exception $exception) {
            throw new CantSendAnalyticsToServerException($exception->getMessage(), $exception->getCode(), $exception);
        }
    }
}