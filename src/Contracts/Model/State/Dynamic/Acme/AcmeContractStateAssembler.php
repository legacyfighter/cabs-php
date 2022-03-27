<?php

namespace LegacyFighter\Cabs\Contracts\Model\State\Dynamic\Acme;

use LegacyFighter\Cabs\Contracts\Model\State\Dynamic\ChangeCommand;
use LegacyFighter\Cabs\Contracts\Model\State\Dynamic\Config\Actions\ChangeVerifier;
use LegacyFighter\Cabs\Contracts\Model\State\Dynamic\Config\Actions\PublishEvent;
use LegacyFighter\Cabs\Contracts\Model\State\Dynamic\Config\Events\DocumentPublished;
use LegacyFighter\Cabs\Contracts\Model\State\Dynamic\Config\Events\DocumentUnpublished;
use LegacyFighter\Cabs\Contracts\Model\State\Dynamic\Config\Predicates\StateChange\AuthorIsNotAVerifier;
use LegacyFighter\Cabs\Contracts\Model\State\Dynamic\Config\Predicates\StateChange\ContentNotEmptyVerifier;
use LegacyFighter\Cabs\Contracts\Model\State\Dynamic\StateBuilder;
use LegacyFighter\Cabs\Contracts\Model\State\Dynamic\StateConfig;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class AcmeContractStateAssembler
{
    public const VERIFIED  = "verified";
    public const DRAFT     = "draft";
    public const PUBLISHED = "published";
    public const ARCHIVED  = "archived";
    public const PARAM_VERIFIER = ChangeVerifier::PARAM_VERIFIER;

    public function __construct(
        private EventDispatcherInterface $dispatcher
    )
    {
    }

    public function assemble(): StateConfig
    {
        $builder = new StateBuilder();
        $builder->beginWith(self::DRAFT)->check(new ContentNotEmptyVerifier())->check(new AuthorIsNotAVerifier())->to(self::VERIFIED)->action(new ChangeVerifier());
        $builder->from(self::DRAFT)->whenContentChanged()->to(self::DRAFT);
        //name of the "published" state and name of the DocumentPublished event are NOT correlated. These are two different domains, name similarity is just a coincidence
        $builder->from(self::VERIFIED)->check(new ContentNotEmptyVerifier())->to(self::PUBLISHED)->action(new PublishEvent(DocumentPublished::class, $this->dispatcher));
        $builder->from(self::VERIFIED)->whenContentChanged()->to(self::DRAFT);
        $builder->from(self::DRAFT)->to(self::ARCHIVED);
        $builder->from(self::VERIFIED)->to(self::ARCHIVED);
        $builder->from(self::PUBLISHED)->to(self::ARCHIVED)->action(new PublishEvent(DocumentUnpublished::class, $this->dispatcher));
        return $builder;
    }
}
