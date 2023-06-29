<?php

namespace Featurit\Client\Modules\Analytics;

use DateTime;
use DateTimeInterface;
use Exception;
use Featurit\Client\Modules\Segmentation\Entities\FeatureFlag;
use JsonSerializable;

class AnalyticsBucket implements JsonSerializable
{
    private DateTimeInterface $startDateTime;
    private ?DateTimeInterface $endDateTime = null;

    /**
     * [
     *   "$hour" => [
     *     "$featureName" => [
     *       "$featureVersion" => [
     *         "$isActive" => $count,
     *       ],
     *     ],
     *   ],
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
        DateTime $currentTime = null
    ): void
    {
        if ($this->isClosed()) {
            return;
        }

        if (is_null($currentTime)) {
            $currentTime = new DateTime();
        }

        // Save the Feature Flag Request.
        $hourKey = $this->generateHourKey($currentTime);
        $flagNameKey = $this->generateFeatureFlagNameKey($featureFlag);
        $flagVersionKey = $this->generateFeatureFlagVersionKey($featureFlag);
        $flagIsActiveKey = $this->generateFeatureFlagIsActiveKey($featureFlag);

        if (!isset($this->requests["$hourKey"])) {
            $this->requests["$hourKey"] = [];
        }

        if (!isset($this->requests["$hourKey"]["$flagNameKey"])) {
            $this->requests["$hourKey"]["$flagNameKey"] = [];
        }

        if (!isset($this->requests["$hourKey"]["$flagNameKey"]["$flagVersionKey"])) {
            $this->requests["$hourKey"]["$flagNameKey"]["$flagVersionKey"] = [];
        }

        if (!isset($this->requests["$hourKey"]["$flagNameKey"]["$flagVersionKey"]["$flagIsActiveKey"])) {
            $this->requests["$hourKey"]["$flagNameKey"]["$flagVersionKey"]["$flagIsActiveKey"] = 1;
        } else {
            $this->requests["$hourKey"]["$flagNameKey"]["$flagVersionKey"]["$flagIsActiveKey"]++;
        }
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

    public function generateHourKey(DateTime $currentTime): string
    {
        return $currentTime->format("Y-m-d H:00:00");
    }

    public function generateFeatureFlagNameKey(FeatureFlag $featureFlag): string
    {
        return $featureFlag->name();
    }

    public function generateFeatureFlagVersionKey(FeatureFlag $featureFlag): string
    {
        return $featureFlag->selectedFeatureFlagVersion()->name();
    }

    public function generateFeatureFlagIsActiveKey(FeatureFlag $featureFlag): string
    {
        return $featureFlag->isActive() ? "t" : "f";
    }
}