<?php

namespace App\Services\FeaturesGatekeeper\Implementations\Rules;


class ValueRule extends AbstractRule
{

    public function value()
    {
        $value = null;

        for ($i = 0; ($i < count($this->properties)) && (!$value); $i++) {
            $property = $this->properties[$i];
            switch ($property) {
                case 'utm':
                    $utm = $this->di->get('jwt')->getUTM();
                    if ($utm && $utm->utm_campaign == "penny_deal") {
                        $value = $utm->utm_term;
                    }
                    break;
            }
        }

        return $value;
    }
}