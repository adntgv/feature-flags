<?php

namespace App\Services\FeaturesGatekeeper\Implementations\Rules;

use Phalcon\Di;

abstract class AbstractRule implements FeatureApplicationRule
{

    protected $di;

    protected $feature;

    protected $properties;

    public function __construct(Di $di, string $feature, $properties)
    {
        $this->di = $di;
        $this->feature = $feature;
        $this->properties = $properties;
    }

    public function satisfied(): bool
    {
        return true;
    }

    public function value()
    {
        return null;
    }
}