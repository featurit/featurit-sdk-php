<?php

namespace Featurit\Client\Tests\Modules\Analytics;

use DateTime;
use Exception;
use Featurit\Client\Modules\Analytics\AnalyticsBucket;
use Featurit\Client\Modules\Segmentation\ConstantCollections\BaseAttributes;
use Featurit\Client\Modules\Segmentation\Entities\FeatureFlag;
use Featurit\Client\Modules\Segmentation\Entities\FeatureFlagVersion;
use PHPUnit\Framework\TestCase;

class AnalyticsBucketTest extends TestCase
{
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

    public function test_it_cant_be_serialized_if_not_closed(): void
    {
        $startDateTime = new DateTime();
        $bucket = new AnalyticsBucket($startDateTime);

        $this->expectException(Exception::class);

        $bucket->jsonSerialize();
    }

    public function test_it_stores_one_flag_properly(): void
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

        $bucket->addFeatureFlagRequest($featureFlag, $startDateTime);

        $endDateTime = new DateTime();
        $bucket->closeBucket($endDateTime);

        $hour = $bucket->generateHourKey($startDateTime);
        $flagNameKey = $bucket->generateFeatureFlagNameKey($featureFlag);
        $flagVersionKey = $bucket->generateFeatureFlagVersionKey($featureFlag);
        $flagIsActiveKey = $bucket->generateFeatureFlagIsActiveKey($featureFlag);

        $result = $bucket->jsonSerialize();
        $expectedReqs = [
            "$hour" => [
                "$flagNameKey" => [
                    "$flagVersionKey" => [
                        "$flagIsActiveKey" => 1,
                    ],
                ],
            ],
        ];

        $this->assertEquals($expectedReqs, $result["reqs"]);
    }

    public function test_it_stores_multiple_different_flags_properly(): void
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

        $bucket->addFeatureFlagRequest($featureFlag1, $startDateTime);

        $featureFlag2 = new FeatureFlag(
            "Test2",
            false,
            BaseAttributes::SESSION_ID,
            [],
            [],
            new FeatureFlagVersion("v1", 100)
        );

        $bucket->addFeatureFlagRequest($featureFlag2, $startDateTime);

        $endDateTime = new DateTime();
        $bucket->closeBucket($endDateTime);

        $hour = $bucket->generateHourKey($startDateTime);
        $flagNameKey1 = $bucket->generateFeatureFlagNameKey($featureFlag1);
        $flagVersionKey1 = $bucket->generateFeatureFlagVersionKey($featureFlag1);
        $flagIsActiveKey1 = $bucket->generateFeatureFlagIsActiveKey($featureFlag1);

        $flagNameKey2 = $bucket->generateFeatureFlagNameKey($featureFlag2);
        $flagVersionKey2 = $bucket->generateFeatureFlagVersionKey($featureFlag2);
        $flagIsActiveKey2 = $bucket->generateFeatureFlagIsActiveKey($featureFlag2);

        $result = $bucket->jsonSerialize();
        $expectedReqs = [
            "$hour" => [
                "$flagNameKey1" => [
                    "$flagVersionKey1" => [
                        "$flagIsActiveKey1" => 1,
                    ],
                ],
                "$flagNameKey2" => [
                    "$flagVersionKey2" => [
                        "$flagIsActiveKey2" => 1,
                    ],
                ],
            ],
        ];

        $this->assertEquals($expectedReqs, $result["reqs"]);
    }

    public function test_it_stores_multiple_equal_flags_properly(): void
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

        $bucket->addFeatureFlagRequest($featureFlag, $startDateTime);

        $bucket->addFeatureFlagRequest($featureFlag, $startDateTime);

        $endDateTime = new DateTime();
        $bucket->closeBucket($endDateTime);

        $hour = $bucket->generateHourKey($startDateTime);
        $flagNameKey = $bucket->generateFeatureFlagNameKey($featureFlag);
        $flagVersionKey = $bucket->generateFeatureFlagVersionKey($featureFlag);
        $flagIsActiveKey = $bucket->generateFeatureFlagIsActiveKey($featureFlag);

        $result = $bucket->jsonSerialize();
        $expectedReqs = [
            "$hour" => [
                "$flagNameKey" => [
                    "$flagVersionKey" => [
                        "$flagIsActiveKey" => 2,
                    ],
                ],
            ],
        ];

        $this->assertEquals($expectedReqs, $result["reqs"]);
    }

    public function test_it_stores_multiple_equal_flags_with_different_active_values_properly(): void
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

        $bucket->addFeatureFlagRequest($featureFlag, $startDateTime);

        $featureFlag = new FeatureFlag(
            "Test",
            false,
            BaseAttributes::USER_ID,
            [],
            []
        );

        $bucket->addFeatureFlagRequest($featureFlag);

        $endDateTime = new DateTime();
        $bucket->closeBucket($endDateTime);

        $hour = $bucket->generateHourKey($startDateTime);
        $flagNameKey = $bucket->generateFeatureFlagNameKey($featureFlag);
        $flagVersionKey = $bucket->generateFeatureFlagVersionKey($featureFlag);

        $result = $bucket->jsonSerialize();
        $expectedReqs = [
            "$hour" => [
                "$flagNameKey" => [
                    "$flagVersionKey" => [
                        "t" => 1,
                        "f" => 1,
                    ],
                ],
            ],
        ];

        $this->assertEquals($expectedReqs, $result["reqs"]);
    }

    public function test_it_stores_multiple_equal_flags_with_different_insertion_hour_in_different_keys(): void
    {
        $startDateTime = new DateTime("2023-06-06 11:54:48");
        $bucket = new AnalyticsBucket($startDateTime);

        $featureFlag = new FeatureFlag(
            "Test",
            true,
            BaseAttributes::USER_ID,
            [],
            []
        );

        $bucket->addFeatureFlagRequest($featureFlag, $startDateTime);

        $endDateTime = new DateTime("2023-06-06 12:00:01");
        $bucket->addFeatureFlagRequest($featureFlag, $endDateTime);

        $bucket->closeBucket($endDateTime);

        $hour1 = $bucket->generateHourKey($startDateTime);
        $hour2 = $bucket->generateHourKey($endDateTime);
        $flagNameKey = $bucket->generateFeatureFlagNameKey($featureFlag);
        $flagVersionKey = $bucket->generateFeatureFlagVersionKey($featureFlag);

        $result = $bucket->jsonSerialize();
        $expectedReqs = [
            "$hour1" => [
                "$flagNameKey" => [
                    "$flagVersionKey" => [
                        "t" => 1,
                    ],
                ],
            ],
            "$hour2" => [
                "$flagNameKey" => [
                    "$flagVersionKey" => [
                        "t" => 1,
                    ],
                ],
            ],
        ];

        $this->assertEquals($expectedReqs, $result["reqs"]);
    }

    public function test_new_data_cant_be_added_after_closing(): void
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

        $bucket->addFeatureFlagRequest($featureFlag, $startDateTime);

        $endDateTime = new DateTime();
        $bucket->closeBucket($endDateTime);

        $bucket->addFeatureFlagRequest($featureFlag, $startDateTime);

        $hour = $bucket->generateHourKey($startDateTime);
        $flagNameKey = $bucket->generateFeatureFlagNameKey($featureFlag);
        $flagVersionKey = $bucket->generateFeatureFlagVersionKey($featureFlag);
        $flagIsActiveKey = $bucket->generateFeatureFlagIsActiveKey($featureFlag);

        $result = $bucket->jsonSerialize();
        $expectedResult = [
            "start" => $startDateTime,
            "end" => $endDateTime,
            "reqs" => [
                "$hour" => [
                    "$flagNameKey" => [
                        "$flagVersionKey" => [
                            "$flagIsActiveKey" => 1,
                        ],
                    ],
                ],
            ],
        ];

        $this->assertEquals($expectedResult, $result);
    }
}