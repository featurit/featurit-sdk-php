<?php

namespace Featurit\Client\Modules\Segmentation;

final class DefaultFeaturitUserContextProvider implements FeaturitUserContextProvider
{
    public function __construct(private ?FeaturitUserContext $featuritUserContext = null)
    {
        if (is_null($featuritUserContext)) {
            $this->featuritUserContext = new DefaultFeaturitUserContext(null, null, null);
        }
    }

    public function getUserContext(): FeaturitUserContext
    {
        return $this->featuritUserContext;
    }
}