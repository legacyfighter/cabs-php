<?php

declare(strict_types=1);

namespace LegacyFighter\Cabs\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\OneToMany;
use LegacyFighter\Cabs\Common\BaseEntity;
use LegacyFighter\Cabs\Crm\Claims\Claim;

#[Entity]
class Client extends BaseEntity
{
    public const TYPE_NORMAL = 'normal';
    public const TYPE_VIP = 'vip';

    public const CLIENT_TYPE_INDIVIDUAL = 'individual';
    public const CLIENT_TYPE_COMPANY = 'company';

    public const PAYMENT_TYPE_PRE_PAID = 'pre-paid';
    public const PAYMENT_TYPE_POST_PAID = 'post-paid';
    public const PAYMENT_TYPE_MONTHLY_INVOICE = 'monthly-invoice';

    #[Column(type: 'string')]
    private string $type;

    #[Column(type: 'string')]
    private string $name;

    #[Column(type: 'string')]
    private string $lastName;

    #[Column(type: 'string')]
    private string $defaultPaymentType;

    #[Column(type: 'string')]
    private string $clientType = self::CLIENT_TYPE_INDIVIDUAL;

    public function __construct()
    {

    }

    public function getType(): string
    {
        return $this->type;
    }

    public function setType(string $type): void
    {
        if(!in_array($type, [self::TYPE_VIP, self::TYPE_NORMAL], true)) {
            throw new \InvalidArgumentException('Invalid client type value');
        }
        $this->type = $type;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function getLastName(): string
    {
        return $this->lastName;
    }

    public function setLastName(string $lastName): void
    {
        $this->lastName = $lastName;
    }

    public function getDefaultPaymentType(): string
    {
        return $this->defaultPaymentType;
    }

    public function setDefaultPaymentType(string $defaultPaymentType): void
    {
        if(!in_array($defaultPaymentType, [self::PAYMENT_TYPE_MONTHLY_INVOICE, self::PAYMENT_TYPE_POST_PAID, self::PAYMENT_TYPE_PRE_PAID], true)) {
            throw new \InvalidArgumentException('Invalid payment type value');
        }
        $this->defaultPaymentType = $defaultPaymentType;
    }

    public function getClientType(): string
    {
        return $this->clientType;
    }

    public function setClientType(string $clientType): void
    {
        if(!in_array($clientType, [self::CLIENT_TYPE_COMPANY, self::CLIENT_TYPE_INDIVIDUAL], true)) {
            throw new \InvalidArgumentException('Invalid client type value');
        }
        $this->clientType = $clientType;
    }
}
