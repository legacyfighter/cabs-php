<?php

namespace LegacyFighter\Cabs\Tests\Unit\Contracts\Model\State\Dynamic;

use PHPUnit\Framework\Assert;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class FakeDocumentPublisher implements EventDispatcherInterface
{
    private array $events;

    public function dispatch(object $event, string $eventName = null): object
    {
        $this->events[] = $event;
        return $event;
    }

    public function contains(string $eventClass): void
    {
        $events = array_filter($this->events, fn($event) => get_class($event) === $eventClass);
        Assert::assertNotEmpty($events);
    }

    public function noEvents(): void
    {
        Assert::assertEmpty($this->events);
    }

    public function reset(): void
    {
        $this->events = [];
    }
}
