<?php

namespace Featurit\Client\Tests\Modules\Analytics;

use DateTime;
use Exception;
use Featurit\Client\Modules\Analytics\AnalyticsBucket;
use Featurit\Client\Modules\Segmentation\ConstantCollections\BaseAttributes;
use Featurit\Client\Modules\Segmentation\DefaultFeaturitUserContext;
use Featurit\Client\Modules\Segmentation\Entities\FeatureFlag;
use Featurit\Client\Modules\Segmentation\Entities\FeatureFlagVersion;
use PHPUnit\Framework\TestCase;

class AnalyticsBucketTest extends TestCase
{
    public function test_new_data_cant_be_added_after_closing(): void
    {
        $startDateTime = new DateTime();
        $bucket = new AnalyticsBucket($startDateTime);

        $featuritUserContext = new DefaultFeaturitUserContext(
            "1234",
            "1357",
            "192.168.1.1"
        );

        $featureFlag = new FeatureFlag(
            "Test",
            true,
            BaseAttributes::USER_ID,
            [],
            []
        );

        $insertionTime1 = new DateTime();
        $bucket->addFeatureFlagRequest($featureFlag, $featuritUserContext, $insertionTime1);

        $endDateTime = new DateTime();
        $bucket->closeBucket($endDateTime);

        $insertionTime2 = new DateTime();
        $bucket->addFeatureFlagRequest($featureFlag, $featuritUserContext, $insertionTime2);

        $result = $bucket->jsonSerialize();

        $expectedResult = [
            "start" => $startDateTime,
            "end" => $endDateTime,
            "reqs" => [
                [
                    "ctx" => [
                        BaseAttributes::USER_ID => $featuritUserContext->getUserId(),
                        BaseAttributes::SESSION_ID => $featuritUserContext->getSessionId(),
                        BaseAttributes::IP_ADDRESS => $featuritUserContext->getIpAddress(),
                    ],
                    "flag" => [
                        "featureName" => $featureFlag->name(),
                        "featureVersion" => $featureFlag->selectedFeatureFlagVersion()->name(),
                        "isActive" => $featureFlag->isActive(),
                    ],
                    "timestamp" => $insertionTime1,
                ],
            ],
        ];

        $this->assertEquals($expectedResult, $result);
    }

    public function test_it_cant_be_serialized_if_not_closed(): void
    {
        $startDateTime = new DateTime();
        $bucket = new AnalyticsBucket($startDateTime);

        $this->expectException(Exception::class);

        $bucket->jsonSerialize();
    }

    public function test_it_serializes_properly_when_empty(): void
    {
        $startDateTime = new DateTime();
        $bucket = new AnalyticsBucket($startDateTime);

        $endDateTime = new DateTime();
        $bucket->closeBucket($endDateTime);

        $result = $bucket->jsonSerialize();

        $expectedResult = [
            "start" => $startDateTime,
            "end" => $endDateTime,
            "reqs" => [],
        ];

        $this->assertEquals($expectedResult, $result);
    }

    public function test_it_stores_one_request_properly(): void
    {
        $startDateTime = new DateTime();
        $bucket = new AnalyticsBucket($startDateTime);

        $featureFlag = new FeatureFlag(
            "Test",
            true,
            BaseAttributes::USER_ID,
            [],
            []
        );

        $featuritUserContext = new DefaultFeaturitUserContext(
            "1234",
            "1357",
            "192.168.1.1"
        );

        $insertionDateTime = new DateTime();
        $bucket->addFeatureFlagRequest($featureFlag, $featuritUserContext, $insertionDateTime);

        $endDateTime = new DateTime();
        $bucket->closeBucket($endDateTime);

        $result = $bucket->jsonSerialize();
        $expectedReqs = [
            [
                "ctx" => [
                    BaseAttributes::USER_ID => $featuritUserContext->getUserId(),
                    BaseAttributes::SESSION_ID => $featuritUserContext->getSessionId(),
                    BaseAttributes::IP_ADDRESS => $featuritUserContext->getIpAddress(),
                ],
                "flag" => [
                    "featureName" => $featureFlag->name(),
                    "featureVersion" => $featureFlag->selectedFeatureFlagVersion()->name(),
                    "isActive" => $featureFlag->isActive(),
                ],
                "timestamp" => $insertionDateTime,
            ],
        ];

        $this->assertEquals($expectedReqs, $result["reqs"]);
    }

    public function test_it_stores_multiple_requests_properly(): void
    {
        $startDateTime = new DateTime();
        $bucket = new AnalyticsBucket($startDateTime);

        $featureFlag1 = new FeatureFlag(
            "Test",
            true,
            BaseAttributes::USER_ID,
            [],
            []
        );

        $featuritUserContext1 = new DefaultFeaturitUserContext(
            "1234",
            "1357",
            "192.168.1.1"
        );

        $insertionDateTime1 = new DateTime();

        $bucket->addFeatureFlagRequest($featureFlag1, $featuritUserContext1, $insertionDateTime1);

        $featuritUserContext2 = new DefaultFeaturitUserContext(
            "2468",
            "1357",
            "192.168.1.1"
        );

        $featureFlag2 = new FeatureFlag(
            "Test2",
            false,
            BaseAttributes::SESSION_ID,
            [],
            [],
            new FeatureFlagVersion("v1", 100)
        );

        $insertionDateTime2 = new DateTime();

        $bucket->addFeatureFlagRequest($featureFlag2, $featuritUserContext2, $insertionDateTime2);

        $endDateTime = new DateTime();
        $bucket->closeBucket($endDateTime);

        $result = $bucket->jsonSerialize();
        $expectedReqs = [
            [
                "ctx" => [
                    BaseAttributes::USER_ID => $featuritUserContext1->getUserId(),
                    BaseAttributes::SESSION_ID => $featuritUserContext1->getSessionId(),
                    BaseAttributes::IP_ADDRESS => $featuritUserContext1->getIpAddress(),
                ],
                "flag" => [
                    "featureName" => $featureFlag1->name(),
                    "featureVersion" => $featureFlag1->selectedFeatureFlagVersion()->name(),
                    "isActive" => $featureFlag1->isActive(),
                ],
                "timestamp" => $insertionDateTime1,
            ],
            [
                "ctx" => [
                    BaseAttributes::USER_ID => $featuritUserContext2->getUserId(),
                    BaseAttributes::SESSION_ID => $featuritUserContext2->getSessionId(),
                    BaseAttributes::IP_ADDRESS => $featuritUserContext2->getIpAddress(),
                ],
                "flag" => [
                    "featureName" => $featureFlag2->name(),
                    "featureVersion" => $featureFlag2->selectedFeatureFlagVersion()->name(),
                    "isActive" => $featureFlag2->isActive(),
                ],
                "timestamp" => $insertionDateTime2,
            ],
        ];

        $this->assertEquals($expectedReqs, $result["reqs"]);
    }
}