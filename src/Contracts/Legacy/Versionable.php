<?php

namespace LegacyFighter\Cabs\Contracts\Legacy;

interface Versionable
{
    public function recreateTo(int $version): void;
    public function getLastVersion(): int;
}
