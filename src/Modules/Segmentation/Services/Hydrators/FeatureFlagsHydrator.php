<?php

namespace Featurit\Client\Modules\Segmentation\Services\Hydrators;

use Featurit\Client\Modules\Segmentation\Entities\FeatureFlag;
use Featurit\Client\Modules\Segmentation\Entities\FeatureFlagSegment;
use Featurit\Client\Modules\Segmentation\Entities\FeatureFlagVersion;
use Featurit\Client\Modules\Segmentation\Entities\NumberSegmentRule;
use Featurit\Client\Modules\Segmentation\Entities\StringSegmentRule;

class FeatureFlagsHydrator implements Hydrator
{
    public function hydrate(array $featureFlagsArray): array
    {
        $featureFlags = [];
        foreach ($featureFlagsArray as $featureFlagName => $featureFlagArray) {

            $featureFlagSegments = [];
            $featureFlagSegmentsArray = $featureFlagArray['segments'];
            foreach ($featureFlagSegmentsArray as $featureFlagSegmentArray) {

                $stringSegmentRules = [];
                $stringSegmentRulesArray = $featureFlagSegmentArray['string_rules'];
                foreach ($stringSegmentRulesArray as $stringSegmentRuleArray) {

                    $stringSegmentRule = new StringSegmentRule(
                        $stringSegmentRuleArray['attribute'],
                        $stringSegmentRuleArray['operator'],
                        $stringSegmentRuleArray['value']
                    );

                    $stringSegmentRules[] = $stringSegmentRule;
                }

                $numberSegmentRules = [];
                $numberSegmentRulesArray = $featureFlagSegmentArray['number_rules'];
                foreach ($numberSegmentRulesArray as $numberSegmentRuleArray) {

                    $numberSegmentRule = new NumberSegmentRule(
                        $numberSegmentRuleArray['attribute'],
                        $numberSegmentRuleArray['operator'],
                        $numberSegmentRuleArray['value']
                    );

                    $numberSegmentRules[] = $numberSegmentRule;
                }

                $featureFlagSegment = new FeatureFlagSegment(
                    $featureFlagSegmentArray['rollout_attribute'],
                    $featureFlagSegmentArray['rollout_percentage'],
                    $stringSegmentRules,
                    $numberSegmentRules
                );

                $featureFlagSegments[] = $featureFlagSegment;
            }

            $featureFlagVersions = [];
            $featureFlagVersionsArray = $featureFlagArray['versions'];
            foreach ($featureFlagVersionsArray as $featureFlagVersionArray) {

                $featureFlagVersion = new FeatureFlagVersion(
                    $featureFlagVersionArray['name'],
                    $featureFlagVersionArray['distribution_percentage'],
                );

                $featureFlagVersions[] = $featureFlagVersion;
            }

            $featureFlag = new FeatureFlag(
                $featureFlagName,
                $featureFlagArray['active'],
                $featureFlagArray['distribution_attribute'],
                $featureFlagSegments,
                $featureFlagVersions
            );

            $featureFlags[$featureFlag->name()] = $featureFlag;
        }

        return $featureFlags;
    }
}
