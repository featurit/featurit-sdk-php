<?php

namespace Featurit\Client\Modules\Segmentation;

interface FeaturitUserContext
{
    public function getUserId(): ?string;
    public function getSessionId(): ?string;
    public function getIpAddress(): ?string;
    public function getCustomAttributes(): array;
    public function hasCustomAttribute(string $name): bool;
    public function getCustomAttribute(string $name): ?string;
    public function getAttribute(string $name): ?string;

    public function toArray(): array;
}