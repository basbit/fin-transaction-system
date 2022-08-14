<?php

declare(strict_types=1);

namespace App\Finance\Domain\Repository;

use App\Finance\Domain\Transaction;
use Symfony\Component\Uid\Uuid;

interface TransactionRepositoryInterface
{
    /**
     * @throwable TransactionNotFoundException
     */
    public function findOneOrThrow(Uuid $uuid): Transaction;

    public function findAll(): array;

    public function store(Transaction $transaction): void;

    public function batchStore(array $transactions): void;
}
