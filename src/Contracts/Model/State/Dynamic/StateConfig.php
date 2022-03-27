<?php

namespace LegacyFighter\Cabs\Contracts\Model\State\Dynamic;

use LegacyFighter\Cabs\Contracts\Model\DocumentHeader;

interface StateConfig
{
    public function begin(DocumentHeader $documentHeader): State;
    public function recreate(DocumentHeader $documentHeader): State;
}
