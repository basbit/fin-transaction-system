<?php

declare(strict_types=1);

namespace App\Finance\Infrastructure\Repository;

use App\Finance\Domain\Exception\TransactionNotFoundException;
use App\Finance\Domain\Repository\TransactionRepositoryInterface;
use App\Finance\Domain\Transaction;
use Symfony\Component\Uid\Uuid;

class TransactionRepository implements TransactionRepositoryInterface
{
    /** @var Transaction[] */
    private array $store = [];

    public function findOneOrThrow(Uuid $uuid): Transaction
    {
        if (!isset($this->store[$uuid->toRfc4122()])) {
            throw new TransactionNotFoundException();
        }

        return $this->store[$uuid->toRfc4122()];
    }

    public function findAll(): array
    {
        return $this->store;
    }

    public function store(Transaction $transaction): void
    {
        $this->store[$transaction->getUuid()->toRfc4122()] = $transaction;
    }

    public function batchStore(array $transactions): void
    {
        foreach ($transactions as $transaction) {
            $this->store[$transaction->getUuid()->toRfc4122()] = $transaction;
        }
    }
}
