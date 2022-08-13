<?php

declare(strict_types=1);

namespace App\Finance\Application\Command\Deposit;

use App\Finance\Domain\Repository\AccountRepositoryInterface;
use App\Finance\Domain\Repository\TransactionRepositoryInterface;
use App\Finance\Domain\Transaction;
use App\Shared\Application\Command\CommandHandlerInterface;
use Money\Currency;
use Money\Money;

final class DepositHandler implements CommandHandlerInterface
{
    public function __construct(
        private AccountRepositoryInterface $accountRepository,
        private TransactionRepositoryInterface $transactionRepository,
    ) {
    }

    public function __invoke(DepositCommand $command): void
    {
        $account = $this->accountRepository->findOneOrThrow($command->accountUuid);

        $transaction = Transaction::deposit(
            $account,
            new Money((string)$command->amount, new Currency($command->currency->name)),
            $command->comment,
            $command->dueDate
        );

        $this->transactionRepository->store($transaction);
    }
}
