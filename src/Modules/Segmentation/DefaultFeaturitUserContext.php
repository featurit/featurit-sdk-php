<?php

namespace Featurit\Client\Modules\Segmentation;

final class DefaultFeaturitUserContext implements FeaturitUserContext
{
    private string $userId;
    private string $sessionId;
    private string $ipAddress;
    private array $customAttributes;

    public function __construct(
        ?string $userId,
        ?string $sessionId,
        ?string $ipAddress,
        array $customAttributes = []
    ) {
        $this->userId = $userId ?? "";
        $this->sessionId = $sessionId ?? "";
        $this->ipAddress = $ipAddress ?? "";
        $this->customAttributes = $customAttributes;
    }

    public function getUserId(): ?string
    {
        return $this->userId ?? null;
    }

    public function getSessionId(): ?string
    {
        return $this->sessionId ?? null;
    }

    public function getIpAddress(): ?string
    {
        return $this->ipAddress ?? null;
    }

    public function getCustomAttributes(): array
    {
        return $this->customAttributes;
    }

    public function getCustomAttribute(string $name): ?string
    {
        return $this->customAttributes[$name];
    }

    public function hasCustomAttribute(string $name): bool
    {
        return array_key_exists($name, $this->customAttributes);
    }

    public function toArray(): array
    {
        $featuritUserContextArray = $this->customAttributes;

        $featuritUserContextArray['userId'] = $this->userId;
        $featuritUserContextArray['sessionId'] = $this->sessionId;
        $featuritUserContextArray['ipAddress'] = $this->ipAddress;

        return $featuritUserContextArray;
    }
}