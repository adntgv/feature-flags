<?php

namespace App\Services\FeaturesGatekeeper\Implementations\Storage;

use App\Services\FeaturesGatekeeper\Implementations\Feature;
use Phalcon\DiInterface;

class FeaturesCombinedStorage implements FeaturesStorage
{
    private $jwtStorage;
    private $cookieStorage;

    public function __construct(DiInterface $di, FeaturesStorage $jwtStorage = null, FeaturesStorage $cookieStorage = null)
    {
        if (!$jwtStorage) {
            $jwtStorage = new FeaturesJWTStorage($di);
        }
        if (!$cookieStorage) {
            $cookieStorage = new FeaturesCookieStorage($di);
        }

        $this->jwtStorage = $jwtStorage;
        $this->cookieStorage = $cookieStorage;
    }

    public function getFeatures(): array
    {
        return array_merge($this->jwtStorage->getFeatures(), $this->cookieStorage->getFeatures());
    }

    /**
     * @param Feature[] $features
     */
    public function setFeatures(array $features): bool
    {
        if (!$this->jwtStorage->setFeatures($features)) {
            $this->cookieStorage->setFeatures($features);
        }

        return true;
    }
}