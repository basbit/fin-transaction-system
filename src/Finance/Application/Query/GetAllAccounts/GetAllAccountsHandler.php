<?php

declare(strict_types=1);

namespace App\Finance\Application\Query\GetAllAccounts;

use App\Finance\Infrastructure\ReadModel\Account\AccountFetcher;
use App\Shared\Application\Query\QueryHandlerInterface;

final class GetAllAccountsHandler implements QueryHandlerInterface
{
    public function __construct(private AccountFetcher $fetcher)
    {
    }

    public function __invoke(GetAllAccountsQuery $query): array
    {
        return $this->fetcher->fetchAll($query->sort);
    }
}
