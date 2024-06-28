<?php

namespace App\Services\FeaturesGatekeeper\Implementations\Storage;

use App\Helper\Json;
use App\Services\FeaturesGatekeeper\Implementations\Feature;
use Phalcon\DiInterface;

class FeaturesCookieStorage implements FeaturesStorage
{
    private $di;

    const COOKIE_NAME = 'features';
    const COOKIE_LIFETIME = 60 * 30; // 30 minutes

    public function __construct(DiInterface $di)
    {
        $this->di = $di;
    }

    /**
     * @return Feature[]
     */
    public function getFeatures(): array
    {
        // get features from cookie
        $cookies = $this->di->get('cookies');
        $featuresCookie = $cookies->get(self::COOKIE_NAME);
        $featuresJSON = $featuresCookie->getValue() ?? '[]';
        return Json::decodeAsArray($featuresJSON);
    }

    /**
     * @param Feature[] $features
     */
    public function setFeatures(array $features): bool
    {
        $expire = time() + self::COOKIE_LIFETIME;
        $featuresJSON = json_encode($features);
        $this->di->get('cookies')->set(self::COOKIE_NAME, $featuresJSON, $expire);
        return true;
    }
}