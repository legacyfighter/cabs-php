<?php

namespace LegacyFighter\Cabs\Contracts\Model;

interface DocumentHeaderRepository
{
    public function getOne(int $id): ?DocumentHeader;

    public function save(DocumentHeader $header): void;
}
