<?php

namespace App\Services\FeaturesGatekeeper\Implementations\Storage;

use App\Services\FeaturesGatekeeper\Implementations\Feature;

interface FeaturesStorage
{

    /**
     * @return Feature[]
     */
    public function getFeatures(): array;
    public function setFeatures(array $features): bool;
}