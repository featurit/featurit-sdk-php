<?php

namespace Featurit\Client\Modules\Segmentation\Services;

use Featurit\Client\Modules\Segmentation\ConstantCollections\AttributeTypes;
use Featurit\Client\Modules\Segmentation\Entities\FeatureFlag;
use Featurit\Client\Modules\Segmentation\Entities\FeatureFlagSegment;
use Featurit\Client\Modules\Segmentation\Entities\NumberSegmentRule;
use Featurit\Client\Modules\Segmentation\Entities\StringSegmentRule;
use Featurit\Client\Modules\Segmentation\FeaturitUserContext;
use Featurit\Client\Modules\Segmentation\Services\AttributeEvaluators\NumberAttributeEvaluator;
use Featurit\Client\Modules\Segmentation\Services\AttributeEvaluators\StringAttributeEvaluator;
use Featurit\Client\Modules\Segmentation\Services\BucketDistributors\BucketDistributor;
use Featurit\Client\Modules\Segmentation\Services\BucketDistributors\MurmurBucketDistributor;

class FeatureSegmentationService
{
    private array $attributeEvaluators;
    private BucketDistributor $bucketDistributor;
    private FeatureFlagVersionSelector $featureFlagVersionSelector;

    public function __construct(
        ?array $attributeEvaluators = [],
        BucketDistributor $bucketDistributor = null,
        FeatureFlagVersionSelector $featureFlagVersionSelector = null
    )
    {
        $this->attributeEvaluators = [
            AttributeTypes::STRING => new StringAttributeEvaluator(),
            AttributeTypes::NUMBER => new NumberAttributeEvaluator(),
        ];

        foreach ($attributeEvaluators as $attributeType => $attributeEvaluator)
        {
            $this->attributeEvaluators[$attributeType] = $attributeEvaluator;
        }

        $this->bucketDistributor = $bucketDistributor ?? new MurmurBucketDistributor();
        $this->featureFlagVersionSelector = $featureFlagVersionSelector ?? new FeatureFlagVersionSelector();
    }

    /**
     * @param array $featureFlags
     * @param FeaturitUserContext $featuritUserContext
     * @return array
     */
    public function execute(array $featureFlags, FeaturitUserContext $featuritUserContext): array
    {
        /**
         * - For every Feature Flag
         * - If Feature Flag isActive is TRUE
         * - For every Feature Flag Segment in a Feature Flag
         * - If the Rollout Bucket Calculator is bigger than the Rollout Percentage, the Feature Flag Segment is FALSE
         * - For every Segment Rule in the Feature Flag Segment
         * - Evaluate the Segment Rule against the Featurit User Context
         * - If ALL the Segment Rules in a Feature Flag Segment are TRUE, the Feature Flag Segment is TRUE
         * - If ANY of the Feature Flag Segments in a Feature Flag is TRUE, the Feature Flag is TRUE
         */

        $segmentedFeatureFlags = [];

        foreach ($featureFlags as $featureFlag) {

            if (! $featureFlag->isActive() || count($featureFlag->featureFlagSegments()) == 0) {
                $isSegmentedFeatureFlagActive = $featureFlag->isActive();
            } else {
                $isSegmentedFeatureFlagActive = $this->evaluateFeatureFlagSegments(
                    $featureFlag->name(),
                    $featureFlag->featureFlagSegments(),
                    $featuritUserContext
                );
            }

            $selectedFeatureFlagVersion = $this->featureFlagVersionSelector->select(
                $featureFlag,
                $featuritUserContext
            );

            $segmentedFeatureFlags[$featureFlag->name()] = new FeatureFlag(
                $featureFlag->name(),
                $isSegmentedFeatureFlagActive,
                $featureFlag->distributionAttribute(),
                $featureFlag->featureFlagSegments(),
                $featureFlag->featureFlagVersions(),
                $selectedFeatureFlagVersion
            );
        }

        return $segmentedFeatureFlags;
    }

    /**
     * If ANY of the Feature Flag Segments in a Feature Flag is TRUE, the Feature Flag is TRUE
     *
     * @param string $featureFlagName
     * @param array $featureFlagSegments
     * @param FeaturitUserContext $featuritUserContext
     * @return bool
     */
    private function evaluateFeatureFlagSegments(
        string $featureFlagName,
        array $featureFlagSegments,
        FeaturitUserContext $featuritUserContext
    ): bool
    {
        foreach ($featureFlagSegments as $featureFlagSegment) {
            if ($this->evaluateFeatureFlagSegment($featureFlagName, $featureFlagSegment, $featuritUserContext)) {
                return true;
            }
        }

        return false;
    }

    /**
     * If ALL the Segment Rules in a Feature Flag Segment are TRUE, the Feature Flag Segment is TRUE
     *
     * @param string $featureFlagName
     * @param FeatureFlagSegment $featureFlagSegment
     * @param FeaturitUserContext $featuritUserContext
     * @return bool
     */
    private function evaluateFeatureFlagSegment(
        string $featureFlagName,
        FeatureFlagSegment $featureFlagSegment,
        FeaturitUserContext $featuritUserContext
    ): bool
    {
        $rolloutPercentage = $featureFlagSegment->rolloutPercentage();
        $rolloutAttributeName = $featureFlagSegment->rolloutAttribute();
        $rolloutAttributeValue = $featuritUserContext->getAttribute($rolloutAttributeName);

        if ($this->bucketDistributor->distribute($featureFlagName, $rolloutAttributeValue) > $rolloutPercentage) {
            return false;
        }

        foreach ($featureFlagSegment->stringSegmentRules() as $stringSegmentRule) {
            if (! $this->evaluateSegmentRule(AttributeTypes::STRING, $stringSegmentRule, $featuritUserContext)) {
                return false;
            }
        }

        foreach ($featureFlagSegment->numberSegmentRules() as $numberSegmentRule) {
            if (! $this->evaluateSegmentRule(AttributeTypes::NUMBER, $numberSegmentRule, $featuritUserContext)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Evaluate the Segment Rule against the Featurit User Context
     *
     * @param string $attributeType
     * @param StringSegmentRule|NumberSegmentRule $segmentRule
     * @param FeaturitUserContext $featuritUserContext
     * @return bool
     */
    private function evaluateSegmentRule(
        string $attributeType,
        StringSegmentRule|NumberSegmentRule $segmentRule,
        FeaturitUserContext $featuritUserContext
    ): bool
    {
        $attributeName = $segmentRule->attribute();
        $operator = $segmentRule->operator();
        $segmentRuleAttributeValue = $segmentRule->value();
        $featuritUserContextAttributeValue = $featuritUserContext->getAttribute($attributeName);

        return $this->attributeEvaluators[$attributeType]->evaluate(
            $featuritUserContextAttributeValue,
            $operator,
            $segmentRuleAttributeValue
        );
    }
}
