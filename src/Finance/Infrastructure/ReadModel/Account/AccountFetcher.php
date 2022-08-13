<?php

declare(strict_types=1);

namespace App\Finance\Infrastructure\ReadModel\Account;

use App\Finance\Domain\Repository\AccountRepositoryInterface;
use App\Finance\Infrastructure\ReadModel\Sort;

final class AccountFetcher
{
    public function __construct(
        private AccountRepositoryInterface $accountRepository,
    ) {
    }

    public function fetchAll(?Sort $sort): array
    {
        /** @var \App\Finance\Domain\Account[] $transactions */
        $accounts = $this->accountRepository->findAll();
        $readModels = [];

        foreach ($accounts as $account) {
            $readModels[$account->getUuid()->toRfc4122()] = (new AccountItem(
                $account->getUuid()->toRfc4122(),
                $account->getLogin(),
            ))->toArray();
        }

        if ($sort instanceof Sort) {
            $column = array_column($readModels, $sort->getField());
            array_multisort($column, $sort->getDirection(), $readModels);
        }

        return $readModels;
    }
}
