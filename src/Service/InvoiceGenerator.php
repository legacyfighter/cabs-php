<?php

namespace LegacyFighter\Cabs\Service;

use LegacyFighter\Cabs\Entity\Invoice;
use LegacyFighter\Cabs\Repository\InvoiceRepository;

class InvoiceGenerator
{
    private InvoiceRepository $invoiceRepository;

    public function __construct(InvoiceRepository $invoiceRepository)
    {
        $this->invoiceRepository = $invoiceRepository;
    }

    public function generate(float $amount, string $subjectName): Invoice
    {
        return $this->invoiceRepository->save(new Invoice($amount, $subjectName));
    }
}
