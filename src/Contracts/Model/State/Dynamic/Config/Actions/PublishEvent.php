<?php

namespace LegacyFighter\Cabs\Contracts\Model\State\Dynamic\Config\Actions;

use LegacyFighter\Cabs\Contracts\Model\DocumentHeader;
use LegacyFighter\Cabs\Contracts\Model\State\Dynamic\ChangeCommand;
use LegacyFighter\Cabs\Contracts\Model\State\Dynamic\Config\Events\DocumentEvent;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class PublishEvent implements Action
{
    /**
     * @param class-string<DocumentEvent> $eventClass
     */
    public function __construct(
        private string $eventClass,
        private EventDispatcherInterface $dispatcher
    )
    {
    }

    public function apply(DocumentHeader $documentHeader, ChangeCommand $changeCommand): void
    {
        $eventClass = $this->eventClass;
        $this->dispatcher->dispatch(new $eventClass(
            $documentHeader->getId(),
            $documentHeader->getStateDescriptor(),
            $documentHeader->getContentId(),
            $documentHeader->getDocumentNumber()
        ));
    }

}
