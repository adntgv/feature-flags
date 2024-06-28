<?php

namespace App\Services\FeaturesGatekeeper\Implementations;

class Feature
{
    /**
     * @var string $name
     */
    public $name;

    /**
     * @var bool $enabled
     */
    public $enabled;

    /**
     * @var string $hash
     */
    public $hash;

    public $value;

    public function __construct(string $name, bool $enabled, string $hash, $value = null)
    {
        $this->name = $name;
        $this->enabled = $enabled;
        $this->hash = $hash;
        $this->value = $value;
    }
}