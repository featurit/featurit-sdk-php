<?php

namespace Featurit\Client\Modules\Segmentation\Services\Hydrators;

interface Hydrator
{
    public function hydrate(array $array): array;
}
