<?php

namespace Featurit\Client\Tests\Modules\Analytics\Services;

use DateTime;
use Featurit\Client\Modules\Analytics\AnalyticsBucket;
use Featurit\Client\Modules\Analytics\Exceptions\CantSendAnalyticsToServerException;
use Featurit\Client\Modules\Analytics\Services\AnalyticsSender;
use Http\Client\Common\HttpMethodsClientInterface;
use Laminas\Diactoros\Response;
use PHPUnit\Framework\TestCase;

class AnalyticsSenderTest extends TestCase
{
    public function test_it_can_send_a_simple_request(): void
    {
        $mockHttpMethodsClient = $this->createMock(HttpMethodsClientInterface::class);
        $mockHttpMethodsClient->method('post')->willReturn(new Response(
            'php://memory',
            200
        ));

        $now = new DateTime();
        $analyticsBucket = new AnalyticsBucket($now);
        $analyticsBucket->closeBucket($now);

        $analyticsSender = new AnalyticsSender($mockHttpMethodsClient);
        $analyticsSender->sendAnalyticsBucket($analyticsBucket);

        $this->assertTrue(true);
    }

    public function test_it_sends_the_right_exception_on_non_200_http_status_response(): void
    {
        $mockHttpMethodsClient = $this->createMock(HttpMethodsClientInterface::class);
        $mockHttpMethodsClient->method('post')->willReturn(new Response(
            'php://memory',
            500
        ));

        $now = new DateTime();
        $analyticsBucket = new AnalyticsBucket($now);
        $analyticsBucket->closeBucket($now);

        $this->expectException(CantSendAnalyticsToServerException::class);

        $analyticsSender = new AnalyticsSender($mockHttpMethodsClient);
        $analyticsSender->sendAnalyticsBucket($analyticsBucket);
    }
}