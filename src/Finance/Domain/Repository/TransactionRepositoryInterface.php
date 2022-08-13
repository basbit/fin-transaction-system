<?php

declare(strict_types=1);

namespace App\Finance\Domain\Repository;

use App\Finance\Domain\Transaction;
use Symfony\Component\Uid\Uuid;
use App\Finance\Domain\Exception\TransactionNotFoundException;

interface TransactionRepositoryInterface
{
    /**
     * @throwable TransactionNotFoundException
     */
    public function findOne(Uuid $uuid): Transaction;

    public function findAll(): array;

    public function store(Transaction $transaction): void;

    public function batchStore(array $transactions): void;
}
