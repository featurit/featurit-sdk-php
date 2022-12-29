<?php

namespace Featurit\Client\Modules\Segmentation\Entities;

use Featurit\Client\Modules\Segmentation\Entities\Attribute;

class FeatureFlag
{
    public function __construct(
        private string $name,
        private bool $isActive,
        private string $distributionAttribute,
        private array $featureFlagSegments,
        private array $featureFlagVersions,
        private ?FeatureFlagVersion $selectedFeatureFlagVersion = null
    ) {
        if (is_null($this->selectedFeatureFlagVersion)) {
            $this->selectedFeatureFlagVersion = new FeatureFlagVersion(
                'default',
                100
            );
        }
    }

    public function name(): string
    {
        return $this->name;
    }

    public function isActive(): bool
    {
        return $this->isActive;
    }

    public function distributionAttribute(): string
    {
        return $this->distributionAttribute;
    }

    public function featureFlagSegments(): array
    {
        return $this->featureFlagSegments;
    }

    public function featureFlagVersions(): array
    {
        return $this->featureFlagVersions;
    }

    public function selectedFeatureFlagVersion(): FeatureFlagVersion
    {
        return $this->selectedFeatureFlagVersion;
    }

    public function toArray(): array
    {
        return [
            'name' => $this->name(),
            'active' => $this->isActive(),
            'distribution_attribute' => $this->distributionAttribute(),
            'segments' => array_map(function(FeatureFlagSegment $featureFlagSegment) {
                return $featureFlagSegment->toArray();
            }, $this->featureFlagSegments()),
            'versions' => array_map(function(FeatureFlagVersion $featureFlagVersion) {
                return $featureFlagVersion->toArray();
            }, $this->featureFlagVersions()),
        ];
    }
}
