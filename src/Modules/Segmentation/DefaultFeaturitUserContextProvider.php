<?php

namespace Featurit\Client\Modules\Segmentation;

final class DefaultFeaturitUserContextProvider implements FeaturitUserContextProvider
{
    public function getUserContext(): FeaturitUserContext
    {
        return new DefaultFeaturitUserContext(null, null, null);
    }
}