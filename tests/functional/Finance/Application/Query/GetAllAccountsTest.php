<?php

declare(strict_types=1);

namespace functional\Finance\Application\Query;

use App\Finance\Application\Query\GetAllAccounts\GetAllAccountsQuery;
use App\Finance\Domain\Account;
use App\Finance\Domain\Enum\Currencies;
use App\Finance\Domain\Repository\AccountRepositoryInterface;
use Money\Currency;
use Money\Money;
use Symfony\Component\Uid\Uuid;
use Tests\functional\ApplicationTestCase;

final class GetAllAccountsTest extends ApplicationTestCase
{
    public const DEFAULT_AMOUNT = 0;
    protected AccountRepositoryInterface $accountRepository;

    private const USER_LOGIN_1 = 'user_1';
    private const USER_LOGIN_2 = 'user_2';
    private Account $account;
    private Account $anotherAccount;

    protected function setUp(): void
    {
        parent::setUp();

        $this->accountRepository = $this->getContainer(AccountRepositoryInterface::class);
        $this->account = new Account(
            Uuid::v4(),
            self::USER_LOGIN_1,
            new Money(self::DEFAULT_AMOUNT, new Currency(Currencies::USD->name))
        );

        $this->accountRepository->store($this->account);

        $this->anotherAccount = new Account(
            Uuid::v4(),
            self::USER_LOGIN_2,
            new Money(0, new Currency(Currencies::USD->name))
        );

        $this->accountRepository->store($this->anotherAccount);
    }

    public function testSuccessGetAllAccounts(): void
    {
        $command = new GetAllAccountsQuery();

        $result = $this->ask($command);

        self::assertIsArray($result);
        self::assertArrayHasKey($this->account->getUuid()->toRfc4122(), $result);
        self::assertArrayHasKey($this->anotherAccount->getUuid()->toRfc4122(), $result);
    }
}
