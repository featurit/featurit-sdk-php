<?php

namespace Featurit\Client\Modules\Segmentation\Entities;

class FeatureFlagSegment
{
    public function __construct(
        private string $rolloutAttribute,
        private int $rolloutPercentage,
        private array $stringSegmentRules,
        private array $numberSegmentRules
    ) {}

    public function rolloutAttribute(): string
    {
        return $this->rolloutAttribute;
    }

    public function rolloutPercentage(): int
    {
        return $this->rolloutPercentage;
    }

    public function stringSegmentRules(): array
    {
        return $this->stringSegmentRules;
    }

    public function numberSegmentRules(): array
    {
        return $this->numberSegmentRules;
    }

    public function toArray(): array
    {
        return [
            'rollout_attribute' => $this->rolloutAttribute(),
            'rollout_percentage' => $this->rolloutPercentage(),
            'string_rules' => array_map(function(StringSegmentRule $stringSegmentRule) {
                return $stringSegmentRule->toArray();
            }, $this->stringSegmentRules()),
            'number_rules' => array_map(function(NumberSegmentRule $numberSegmentRule) {
                return $numberSegmentRule->toArray();
            }, $this->numberSegmentRules()),
        ];
    }
}
