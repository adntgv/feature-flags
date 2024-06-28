<?php

namespace App\Services;

use App\Services\FeaturesGatekeeper\FeaturesConfigurator;
use App\Services\FeaturesGatekeeper\Implementations\Feature;
use App\Services\FeaturesGatekeeper\Implementations\FeaturesConfig;
use Phalcon\Config;
use Phalcon\DiInterface;

class FeaturesGatekeeper
{
    public const FEATURE_DIET_PREFERENCES = "dietPreferences";
    public const FEATURE_PRODUCT_FEEDBACK = 'productFeedback';

    public const FEATURES = [
        self::FEATURE_DIET_PREFERENCES,
        self::FEATURE_PRODUCT_FEEDBACK
    ];

    /**
     * @var FeaturesConfigurator $featuresConfigurator
     */
    private $featuresConfigurator;

    public function __construct(FeaturesConfigurator $featuresConfigurator)
    {
        $this->featuresConfigurator = $featuresConfigurator;
    }

    public function load(): void
    {
        $this->featuresConfigurator->load();
    }

    public function enabled(string $feature): bool
    {
        return $this->featuresConfigurator->enabled($feature);
    }

    /**
     * @return Feature[]
     */
    public function getFeatures(): array
    {
        return $this->featuresConfigurator->getFeatures();
    }

    public function getEnabledFeaturesList(): array
    {
        $withRandomKeys = array_filter(self::FEATURES, function ($feature) {
            return $this->enabled($feature);
        });

        return array_values($withRandomKeys);
    }

    public function getFeatureValue(string $target)
    {
        return $this->featuresConfigurator->getFeatureValue($target);
    }

    public function setFeatureValue(string $target, $value)
    {
        return $this->featuresConfigurator->setFeatureValue($target, $value);
    }
}

