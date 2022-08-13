<?php

declare(strict_types=1);

namespace App\Finance\Application\Query\GetAllTransactions;

use App\Finance\Infrastructure\ReadModel\Transaction\TransactionFetcher;
use App\Shared\Application\Query\QueryHandlerInterface;

final class GetAllTransactionsHandler implements QueryHandlerInterface
{
    public function __construct(private TransactionFetcher $fetcher)
    {
    }

    public function __invoke(GetAllTransactionsQuery $query): array
    {
        return $this->fetcher->fetchAll($query->sort);
    }
}
