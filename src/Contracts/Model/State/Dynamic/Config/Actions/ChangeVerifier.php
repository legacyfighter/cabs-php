<?php

namespace LegacyFighter\Cabs\Contracts\Model\State\Dynamic\Config\Actions;

use LegacyFighter\Cabs\Contracts\Model\DocumentHeader;
use LegacyFighter\Cabs\Contracts\Model\State\Dynamic\ChangeCommand;

class ChangeVerifier implements Action
{
    public const PARAM_VERIFIER = 'verifier';

    public function apply(DocumentHeader $documentHeader, ChangeCommand $changeCommand): void
    {
        $documentHeader->setVerifierId($changeCommand->getParam(self::PARAM_VERIFIER));
    }
}
