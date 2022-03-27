<?php

namespace LegacyFighter\Cabs\Contracts\Model\Content;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Embeddable;

#[Embeddable]
class ContentVersion
{
    public function __construct(
        #[Column]
        private string $contentVersion
    )
    {
    }
}
