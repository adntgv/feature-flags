<?php

namespace App\Services\FeaturesGatekeeper;

use App\Services\FeaturesGatekeeper\Implementations\Feature;

interface FeaturesConfigurator {
    public function enabled(string $feature): bool;
    public function load();

    /**
     * @return Feature[]
     */
    public function getFeatures(): array;

    public function setFeatureValue(string $target, $value);

    public function getFeatureValue(string $target);
}