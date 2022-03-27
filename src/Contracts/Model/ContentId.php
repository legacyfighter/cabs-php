<?php

namespace LegacyFighter\Cabs\Contracts\Model;

use Symfony\Component\Uid\Uuid;

class ContentId
{
    private Uuid $contentId;

    public function __construct(Uuid $contentId)
    {
        $this->contentId = $contentId;
    }
}
