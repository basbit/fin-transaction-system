<?php

declare(strict_types=1);

namespace App\Finance\Infrastructure\ReadModel\Transaction;

use App\Finance\Domain\Repository\TransactionRepositoryInterface;
use App\Finance\Infrastructure\ReadModel\Sort;

final class TransactionFetcher
{
    public function __construct(
        private TransactionRepositoryInterface $transactionRepository,
    ) {
    }

    public function fetchAll(?Sort $sort): array
    {
        /** @var \App\Finance\Domain\Transaction[] $transactions */
        $transactions = $this->transactionRepository->findAll();
        $readModels = [];

        foreach ($transactions as $transaction) {
            $readModels[$transaction->getUuid()->toRfc4122()] = (new TransactionItem(
                $transaction->getUuid()->toRfc4122(),
                $transaction->getAccount()->getUuid()->toRfc4122(),
                $transaction->getAccount()->getLogin(),
                (float)$transaction->getAmount()->getAmount(),
                $transaction->getAmount()->getCurrency()->getCode(),
                $transaction->getType()->name,
                $transaction->getComment(),
                $transaction->getDueDate()->format('Y-m-d H:i:s'),
            ))->toArray();
        }

        if ($sort instanceof Sort) {
            $column = array_column($readModels, $sort->getField());
            array_multisort($column, $sort->getDirection(), $readModels);
        }

        return $readModels;
    }
}
