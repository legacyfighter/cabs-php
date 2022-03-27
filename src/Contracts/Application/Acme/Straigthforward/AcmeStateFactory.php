<?php

namespace LegacyFighter\Cabs\Contracts\Application\Acme\Straigthforward;

use LegacyFighter\Cabs\Contracts\Model\DocumentHeader;
use LegacyFighter\Cabs\Contracts\Model\State\Straightforward\Acme\DraftState;
use LegacyFighter\Cabs\Contracts\Model\State\Straightforward\BaseState;

class AcmeStateFactory
{
    public function create(DocumentHeader $header): BaseState
    {
        //sample impl is based on class names
        //other possibilities: names Dependency Injection Containers, states persisted via ORM Discriminator mechanism, mapper
        $className = $header->getStateDescriptor();
        if($className === null) {
            $state = new DraftState();
            $state->init($header);
            return $state;
        }

        try {
            $state = new $className();
            $state->init($header);
            return $state;
        } catch (\Throwable $exception) {
            throw new \RuntimeException('Invalid class name '.$className, 0, $exception);
        }
    }
}
