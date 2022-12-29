<?php

namespace Featurit\Client\Modules\Segmentation\Entities;

class FeatureFlagVersion
{
    public function __construct(
        private string $name,
        private int $featureFlagDistributionPercentage
    ) {}

    public function name(): string
    {
        return $this->name;
    }

    public function featureFlagDistributionPercentage(): int
    {
        return $this->featureFlagDistributionPercentage;
    }

    public function toArray(): array
    {
        return [
            'name' => $this->name(),
            'distribution_percentage' => $this->featureFlagDistributionPercentage(),
        ];
    }
}
