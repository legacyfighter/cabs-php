<?php

namespace LegacyFighter\Cabs\Repository;

use Doctrine\ORM\EntityManagerInterface;
use LegacyFighter\Cabs\Entity\Invoice;

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
