<?php

namespace App\Services\FeaturesGatekeeper\Implementations\Rules;

use Phalcon\Di;

class PercentRule extends AbstractRule
{

    public function satisfied(): bool
    {
        $amount = $this->properties['amount'];
        return  mt_rand(0, 100) < $amount;
    }
}