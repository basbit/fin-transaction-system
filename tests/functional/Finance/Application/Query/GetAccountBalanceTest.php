<?php

declare(strict_types=1);

namespace functional\Finance\Application\Query;

use App\Finance\Application\Query\GetAccountBalance\GetAccountBalanceQuery;
use App\Finance\Domain\Account;
use App\Finance\Domain\Enum\Currencies;
use App\Finance\Domain\Exception\AccountNotFoundException;
use App\Finance\Domain\Repository\AccountRepositoryInterface;
use Money\Currency;
use Money\Money;
use Symfony\Component\Uid\Uuid;
use Tests\functional\ApplicationTestCase;

use function PHPUnit\Framework\assertEquals;

final class GetAccountBalanceTest extends ApplicationTestCase
{
    public const DEFAULT_AMOUNT = 0;
    protected AccountRepositoryInterface $accountRepository;

    private const USER_LOGIN_1 = 'user_1';
    private const USER_LOGIN_2 = 'user_2';
    private Account $account;

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

        $anotherAccount = new Account(
            Uuid::v4(),
            self::USER_LOGIN_2,
            new Money(0, new Currency(Currencies::USD->name))
        );

        $this->accountRepository->store($anotherAccount);
    }

    public function testSuccessGetAccountBalance(): void
    {
        $command = new GetAccountBalanceQuery(
            $this->account->getUuid()
        );

        $result = $this->ask($command);

        self::assertIsArray($result);
        self::assertArrayHasKey('currency', $result);
        self::assertArrayHasKey('amount', $result);
        assertEquals($this->account->getBalance()->getAmount()->getCurrency()->getCode(), $result['currency']);
        assertEquals($this->account->getBalance()->getAmount()->getAmount(), $result['amount']);
    }

    public function testGetAccountBalanceNoAccount(): void
    {
        $this->expectException(AccountNotFoundException::class);
        $command = new GetAccountBalanceQuery(
            Uuid::v4()
        );

        $this->ask($command);
    }
}
