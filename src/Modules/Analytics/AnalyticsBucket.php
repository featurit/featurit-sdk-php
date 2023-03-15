<?php

namespace Featurit\Client\Modules\Analytics;

use DateTime;
use DateTimeInterface;
use Exception;
use Featurit\Client\Modules\Segmentation\ConstantCollections\BaseAttributes;
use Featurit\Client\Modules\Segmentation\Entities\FeatureFlag;
use Featurit\Client\Modules\Segmentation\FeaturitUserContext;
use JsonSerializable;

class AnalyticsBucket implements JsonSerializable
{
    private DateTimeInterface $startDateTime;
    private ?DateTimeInterface $endDateTime = null;

    /**
     * [
     *   [
     *     "timestamp",
     *     "ctx" => ["userId", "sessionId", "ipAddress", "custom"],
     *     "flag" => ["featureName", "featureVersion", "isActive"]
     *   ]
     * ]
     * @var array
     */
    private array $requests = [];

    public function __construct(
        DateTimeInterface $startDateTime,
        ?DateTimeInterface $endDateTime = null
    )
    {
        $this->startDateTime = clone $startDateTime;

        if (! is_null($endDateTime)) {
            $this->endDateTime = clone $endDateTime;
        }
    }

    public function startDateTime(): DateTimeInterface
    {
        return $this->startDateTime;
    }

    public function addFeatureFlagRequest(
        FeatureFlag $featureFlag,
        FeaturitUserContext $featuritUserContext,
        ?DateTimeInterface $insertionDateTime = null
    ): void
    {
        if ($this->isClosed()) {
            return;
        }

        // Save the User Context.
        $request["ctx"] = [
            BaseAttributes::USER_ID => $featuritUserContext->getUserId(),
            BaseAttributes::SESSION_ID => $featuritUserContext->getSessionId(),
            BaseAttributes::IP_ADDRESS => $featuritUserContext->getIpAddress(),
        ];

        if (count($featuritUserContext->getCustomAttributes()) > 0) {
            $request["ctx"]["custom"] = $featuritUserContext->getCustomAttributes();
        }

        // Save the Feature Flag.
        $request["flag"] = [
            "featureName" => $featureFlag->name(),
            "featureVersion" => $featureFlag->selectedFeatureFlagVersion()->name(),
            "isActive" => $featureFlag->isActive(),
        ];

        // Save the request timestamp.
        if (is_null($insertionDateTime)) {
            $insertionDateTime = new DateTime();
        }

        $request["timestamp"] = $insertionDateTime;

        $this->requests[] = $request;
    }

    public function openBucket(): void
    {
        if (!$this->isClosed()) {
            return;
        }

        $this->endDateTime = null;
    }

    public function closeBucket(?DateTimeInterface $endDateTime = null): void
    {
        if ($this->isClosed()) {
            return;
        }

        if (! is_null($endDateTime)) {
            $this->endDateTime = $endDateTime;
            return;
        }

        $this->endDateTime = new DateTime();
    }

    private function isClosed(): bool
    {
        return ! is_null($this->endDateTime);
    }

    /**
     * @throws Exception
     */
    public function jsonSerialize(): array
    {
        if (! $this->isClosed()) {
            throw new Exception("Can't serialize an open bucket.");
        }

        return [
            "start" => $this->startDateTime,
            "end" => $this->endDateTime,
            "reqs" => $this->requests,
        ];
    }
}