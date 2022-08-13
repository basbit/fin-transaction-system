<?php

namespace App\Finance\Domain;

use Money\Money;
use Symfony\Component\Uid\Uuid;

class Account
{
    private Balance $balance;

    public function __construct(
        private Uuid $uuid,
        private string $login,
        private Money $amount,
    ) {
        $this->balance = new Balance(Uuid::v4(), $this->amount);
    }

    public function getBalance(): Balance
    {
        return $this->balance;
    }

    public function getUuid(): Uuid
    {
        return $this->uuid;
    }

    public function getLogin(): string
    {
        return $this->login;
    }
}
