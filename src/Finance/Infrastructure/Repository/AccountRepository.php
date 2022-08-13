<?php

declare(strict_types=1);

namespace App\Finance\Infrastructure\Repository;

use App\Finance\Domain\Account;
use App\Finance\Domain\Exception\AccountAlreadyExistException;
use App\Finance\Domain\Exception\AccountNotFoundException;
use App\Finance\Domain\Repository\AccountRepositoryInterface;
use Symfony\Component\Uid\Uuid;

class AccountRepository implements AccountRepositoryInterface
{
    /** @var Account[] */
    private array $store = [];

    public function findOneOrThrow(Uuid $uuid): Account
    {
        if (!isset($this->store[$uuid->toRfc4122()])) {
            throw new AccountNotFoundException();
        }

        return $this->store[$uuid->toRfc4122()];
    }

    public function findAll(): array
    {
        return $this->store;
    }

    public function store(Account $account): void
    {
        if (isset($this->store[$account->getUuid()->toRfc4122()])) {
            throw new AccountAlreadyExistException();
        }

        $this->store[$account->getUuid()->toRfc4122()] = $account;
    }
}
