<?php

namespace App\Finance\Domain;

use App\Finance\Domain\Exception\IncompatibleCurrenciesException;
use App\Finance\Domain\Exception\NotEnoughMoneyException;
use Money\Money;
use Symfony\Component\Uid\Uuid;

class Balance
{
    public function __construct(
        private Uuid $uuid,
        private Money $amount,
    ) {
    }

    public function getAmount(): Money
    {
        return $this->amount;
    }

    public function topup(Money $amount): void
    {
        if (!$this->amount->getCurrency()->equals($amount->getCurrency())) {
            throw new IncompatibleCurrenciesException();
        }

        $this->amount = $this->amount->add($amount);
    }

    public function withdraw(Money $amount): void
    {
        if (!$this->amount->getCurrency()->equals($amount->getCurrency())) {
            throw new IncompatibleCurrenciesException();
        }

        if ($this->amount->lessThan($amount)) {
            throw new NotEnoughMoneyException();
        }

        $this->amount = $this->amount->subtract($amount);
    }
}
