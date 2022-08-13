<?php

declare(strict_types=1);

namespace App\Finance\Application\Command\Transfer;

use App\Finance\Application\Command\Deposit\DepositCommand;
use App\Finance\Domain\Repository\AccountRepositoryInterface;
use App\Finance\Domain\Repository\TransactionRepositoryInterface;
use App\Finance\Domain\Transaction;
use App\Shared\Application\Command\CommandHandlerInterface;
use Money\Currency;
use Money\Money;

final class TransferHandler implements CommandHandlerInterface
{
    public function __construct(
        private AccountRepositoryInterface $accountRepository,
        private TransactionRepositoryInterface $transactionRepository,
    ) {
    }

    public function __invoke(TransferCommand $command): void
    {
        $fromAccount = $this->accountRepository->findOneOrThrow($command->fromAccountUuid);
        $toAccount = $this->accountRepository->findOneOrThrow($command->toAccountUuid);

        $transactions = Transaction::transfer(
            new Money((string)$command->amount, new Currency($command->currency->name)),
            $fromAccount,
            $toAccount,
            $command->comment,
            $command->dueDate
        );

        $this->transactionRepository->batchStore($transactions);
    }
}
