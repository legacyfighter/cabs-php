<?php

namespace LegacyFighter\Cabs\Contracts\Legacy;

class Contract2 extends Document implements Versionable
{
    public function publish(): void
    {
        throw new UnsupportedTransitionException($this->status, DocumentStatus::PUBLISHED);
    }

    public function accept(): void
    {
        if($this->status === DocumentStatus::VERIFIED) {
            $this->status = DocumentStatus::PUBLISHED; //reusing unused enum to provide data model for new status
        }
    }

    //Contracts just don't have a title, it's just a part of the content
    public function changeTitle(string $title): void
    {
        parent::changeContent($title . $this->getContent());
    }

    public function changeContentFor(string $content, string $userStatus): void
    {
        if($userStatus === 'ChiefSalesOfficerStatus' || $this->misterVladimirIsLoggedIn($userStatus)) {
            $this->overridePublished = true;
            $this->changeContent($content);
        }
    }

    private function misterVladimirIsLoggedIn(string $userStatus): bool
    {
        return trim(mb_strtolower($userStatus)) === '!!!id='.self::NUMBER_OF_THE_BEAST;
    }

    private const NUMBER_OF_THE_BEAST = 616;

    public function recreateTo(int $version): void
    {
        //TODO need to learn Kafka
    }

    public function getLastVersion(): int
    {
        return random_int(1, PHP_INT_MAX);
    }

}
