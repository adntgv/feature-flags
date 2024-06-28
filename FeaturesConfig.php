<?php

namespace App\Services\FeaturesGatekeeper\Implementations;

use Phalcon\Config;
use Phalcon\DiInterface;
use App\Services\FeaturesGatekeeper;
use App\Services\FeaturesGatekeeper\Implementations\Rules\FeatureApplicationRule;
use App\Services\FeaturesGatekeeper\Implementations\Rules\PercentRule;
use App\Services\FeaturesGatekeeper\Implementations\Rules\UserIdListRule;
use App\Services\FeaturesGatekeeper\Implementations\Rules\ValueRule;
use App\Services\FeaturesGatekeeper\Implementations\Storage\FeaturesStorage;

class FeaturesConfig implements FeaturesGatekeeper\FeaturesConfigurator
{
    /**
     * @var DiInterface $di
     */
    private $di;

    /**
     * @var Config
     */
    private $config;

    /**
     * @var FeaturesStorage
     */
    private $storage;

    public function __construct(DiInterface $di, Config $config, FeaturesStorage $storage)
    {
        $this->di = $di;
        $this->config = $config;
        $this->storage = $storage;
    }

    public function enabled(string $feature): bool
    {
        if ($this->hasUserIdListRule($feature)) {
            return $this->featureIsActive($feature);
        }

        $storedFeatures = $this->storage->getFeatures();
        if (!array_key_exists($feature, $storedFeatures)) {
            $this->updateStoredFeatures();
            $storedFeatures = $this->storage->getFeatures();
        }

        return $storedFeatures[$feature]['enabled'] ?? false;
    }

    private function hasUserIdListRule(string $feature): bool
    {
        $rules = $this->getRulesOptions($feature);

        foreach ($rules as $rule) {
            if ($rule['type'] === 'userIdList') {
                return true;
            }
        }

        return  false;
    }

    public function getFeatures(): array
    {
        return $this->storage->getFeatures();
    }

    public function load()
    {
        $this->updateStoredFeatures();
    }


    private function updateStoredFeatures()
    {
        $definedFeatures = $this->getDefinedFeatures();
        $storedFeatures = $this->storage->getFeatures();
        $featuresToStore = $storedFeatures;
        // store new features
        foreach ($definedFeatures as $name => $definedFeature) {
            if (!array_key_exists($name, $storedFeatures)) {
                $featuresToStore[$name] = $definedFeature;
            }
        }

        // update features that have changed hashes
        foreach ($storedFeatures as $name => $storedFeature) {
            if (array_key_exists($name, $definedFeatures)) {
                if ($definedFeatures[$name]['hash'] !== $storedFeature['hash']) {
                    $featuresToStore[$name] = $definedFeatures[$name];
                }
            }
        }

        // only update if there are changes
        if ($featuresToStore != $storedFeatures) {
            $this->storage->setFeatures($featuresToStore);
        }
    }

    private function getDefinedFeatures(): array
    {
        $definedFeatures = [];

        foreach (FeaturesGatekeeper::FEATURES as $feature) {
            $featureRuleOptions = $this->getRulesOptions($feature);
            $hash = md5(json_encode($featureRuleOptions));
            $value = $this->getDefaultFeatureValue($feature);
            $definedFeatures[$feature] = (array) new Feature($feature, $this->featureIsActive($feature), $hash, $value);
        }

        return $definedFeatures;
    }

    /**
     * @param $feature
     * @return bool
     */
    public function featureIsActive($feature): bool
    {
        $enabled = $this->enabledInConfig($feature);
        return (bool)($enabled && $this->satisfiesRules($feature));
    }

    public function enabledInConfig(string $feature): bool
    {
        $featureEnabled = 'features.' .  $feature . ".enabled";
        return $this->equals($featureEnabled, true);
    }

    public function equals(string $featureConfigPath, $value): bool
    {
        return $this->getConfigValue($featureConfigPath) == $value;
    }

    private function getConfigValue(string $featureConfigPath)
    {
        return $this->config->path($featureConfigPath);
    }

    private function satisfiesRules(string $feature): bool
    {
        $rules = $this->getRules($feature);

        if (empty($rules)) {
            return true;
        }

        foreach ($rules as $rule) {
            if ($rule->satisfied()) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return FeatureApplicationRule[]
     */
    private function getRules(string $feature): array
    {
        $rulesOptions = $this->getRulesOptions($feature);
        $rules = [];

        foreach ($rulesOptions as $ruleOptions) {
            try {
                $rules[] = $this->createRule($feature, $ruleOptions);
            } catch (\Exception $e) {
                $this->di->get('log')->exception($e);
            }
        }

        return $rules ?? [];
    }

    /**
     * @param string $feature
     * @return array
     */
    public function getRulesOptions(string $feature): array
    {
        $featureRulesPath = 'features.' . $feature . '.rules';
        $rules = $this->getConfigValue($featureRulesPath);
        return $rules ? $rules->toArray() : [];
    }

    /**
     * @throws \Exception
     */
    private function createRule(string $feature, $options): FeatureApplicationRule
    {
        $typeClassMap = [
            'percent' => PercentRule::class,
            'userIdList' => UserIdListRule::class,
            'value' => ValueRule::class
        ];
        $type = $options['type'] ;
        $class = $typeClassMap[$type];
        if (!$class) {
            throw new \Exception("Rule type $type not supported");
        }

        $properties = $options['properties'];
        $di = $this->di;

        return new $class($di, $feature, $properties);
    }

    public function setFeatureValue(string $target, $value)
    {
        $features = $this->getFeatures();

        foreach ($features as $featureName => &$feature) {
            if (($feature['name'] ?? $featureName) === $target) {
                $feature['value'] = $value;
            }
        }
        unset($feature);

        $this->storage->setFeatures($features);
    }

    public function getFeatureValue(string $target)
    {
        $feature = $this->getFeatureByName($target);
        return $feature['value'] ?? null ;
    }

    private function getFeatureByName(string $target): ?array
    {
        $features = $this->getFeatures();

        foreach ($features as $featureName => $feature) {
            if (($feature['name'] ?? $featureName) == $target) {
                return $feature;
            }
        }

        return null;
    }

    private function getDefaultFeatureValue($feature)
    {
        $rules = $this->getRules($feature);

        foreach ($rules as $rule) {
            if ($value = $rule->value()) {
                return $value;
            }
        }

        return null;
    }
}
