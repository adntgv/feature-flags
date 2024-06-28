<?php

namespace App\Services\FeaturesGatekeeper\Implementations\Storage;

use App\Services\FeaturesGatekeeper\Implementations\Feature;
use Phalcon\DiInterface;

class FeaturesJWTStorage implements FeaturesStorage
{
    private $di;

    public function __construct(DiInterface $di)
    {
        $this->di = $di;
    }

    /**
     * @return Feature[]
     */
    public function getFeatures(): array
    {
        $jwt = $this->di->get('jwt');
        $token = $jwt->getAuthToken();
        return $token ? $token->getFeatures() : [];
    }
    /**
     * @param Feature[] $features
     */
    public function setFeatures(array $features): bool
    {
        $jwt = $this->di->get('jwt');
        if ($token = $jwt->getAuthToken()) {
            $token->setFeatures($features);
            $token->save();
            return true;
        }

        return false;
    }
}