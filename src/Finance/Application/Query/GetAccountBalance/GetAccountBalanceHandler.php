<?php

declare(strict_types=1);

namespace App\Finance\Application\Query\GetAccountBalance;

use App\Finance\Domain\Repository\AccountRepositoryInterface;
use App\Shared\Application\Query\QueryHandlerInterface;

final class GetAccountBalanceHandler implements QueryHandlerInterface
{
    public function __construct(
        private AccountRepositoryInterface $repository
    ) {
    }

    public function __invoke(GetAccountBalanceQuery $query): array
    {
        $account = $this->repository->findOneOrThrow($query->accountUuid);

        return [
            'currency' => $account->getBalance()->getAmount()->getCurrency(),
            'amount' => $account->getBalance()->getAmount()->getAmount()
        ];
    }
}
