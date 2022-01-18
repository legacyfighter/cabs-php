<?php

namespace LegacyFighter\Cabs\Service\Symfony;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\KernelEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class RequestTransactionSubscriber implements EventSubscriberInterface
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::CONTROLLER => ['startTransaction', 10],
            KernelEvents::RESPONSE => ['commitTransaction', 10],
            KernelEvents::EXCEPTION => ['rollbackTransaction', 11],
        ];
    }

    public function startTransaction(KernelEvent $event): void
    {
        if(in_array($event->getRequest()->getMethod(), [Request::METHOD_POST, Request::METHOD_DELETE], true)) {
            $this->entityManager->beginTransaction();
        }
    }

    public function commitTransaction(): void
    {
        if($this->entityManager->getConnection()->isTransactionActive()) {
            $this->entityManager->flush();
            $this->entityManager->commit();
        }
    }

    public function rollbackTransaction(): void
    {
        if($this->entityManager->getConnection()->isTransactionActive()) {
            $this->entityManager->rollback();
        }
    }
}
