<?php

namespace Featurit\Client\Modules\Segmentation;

use Featurit\Client\Modules\Segmentation\ConstantCollections\BaseAttributes;

final class DefaultFeaturitUserContext implements FeaturitUserContext
{
    public function __construct(
        private ?string $userId,
        private ?string $sessionId,
        private ?string $ipAddress,
        private ?array $customAttributes = []
    ) {}

    public function getUserId(): ?string
    {
        return $this->userId;
    }

    public function getSessionId(): ?string
    {
        return $this->sessionId;
    }

    public function getIpAddress(): ?string
    {
        return $this->ipAddress;
    }

    public function getCustomAttributes(): array
    {
        return $this->customAttributes;
    }

    public function hasCustomAttribute(string $name): bool
    {
        return array_key_exists($name, $this->customAttributes);
    }

    public function getCustomAttribute(string $name): ?string
    {
        return $this->customAttributes[$name] ?? null;
    }

    public function getAttribute(string $name): ?string
    {
        return $this->toArray()[$name] ?? null;
    }

    public function toArray(): array
    {
        return [
            BaseAttributes::USER_ID => $this->userId,
            BaseAttributes::SESSION_ID => $this->sessionId,
            BaseAttributes::IP_ADDRESS => $this->ipAddress,
            ...$this->customAttributes,
        ];
    }
}