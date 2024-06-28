<?php

namespace App\Services\FeaturesGatekeeper\Implementations\Rules;

use Phalcon\Di;

class UserIdListRule extends AbstractRule
{
    private $userId;

    public function __construct(Di $di, string $feature, $properties)
    {
        parent::__construct($di, $feature, $properties);

        $id = $di->get('jwt')->getUserId();
        $this->userId = $id ? (int) $id : null;
    }

    public function satisfied(): bool
    {
        $ids = $this->properties['ids'] ?? [];
        return  in_array($this->userId, $ids);
    }
}