<?php

namespace LegacyFighter\Cabs\Contracts\Model\State\Dynamic\Config\Actions;

use LegacyFighter\Cabs\Contracts\Model\DocumentHeader;
use LegacyFighter\Cabs\Contracts\Model\State\Dynamic\ChangeCommand;

interface Action
{
    public function apply(DocumentHeader $documentHeader, ChangeCommand $changeCommand): void;
}
