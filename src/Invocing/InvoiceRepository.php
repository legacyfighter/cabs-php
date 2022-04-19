<?php

namespace LegacyFighter\Cabs\Invocing;

use Doctrine\ORM\EntityManagerInterface;

class InvoiceRepository
{
    public function __construct(private EntityManagerInterface $em) {}

    public function save(Invoice $invoice): Invoice
    {
        $this->em->persist($invoice);
        $this->em->flush();
        return $invoice;
    }
}
