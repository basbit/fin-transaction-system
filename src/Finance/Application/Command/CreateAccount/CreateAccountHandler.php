<?php

declare(strict_types=1);

namespace App\Finance\Application\Command\CreateAccount;

use App\Shared\Application\Command\CommandHandlerInterface;
use App\Finance\Domain\Account;
use App\Finance\Domain\Repository\AccountRepositoryInterface;
use Money\Currency;
use Money\Money;

final class CreateAccountHandler implements CommandHandlerInterface
{
    public function __construct(
        private AccountRepositoryInterface $accountRepository,
    ) {
    }

    public function __invoke(CreateAccountCommand $command): void
    {
        $account = new Account(
            $command->uuid,
            $command->login,
            new Money((string)$command->amount, new Currency($command->currency->name))
        );
        $this->accountRepository->store($account);
    }
}
