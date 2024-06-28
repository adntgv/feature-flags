<?php

namespace App\Services\FeaturesGatekeeper\Implementations\Rules;

interface FeatureApplicationRule
{
    public function satisfied(): bool;

    /**
     * @return mixed
     */
    public function value();
}