<?php

namespace Featurit\Client\Modules\Segmentation;

interface FeaturitUserContextProvider
{
    public function getUserContext(): FeaturitUserContext;
}