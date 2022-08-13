<?php

declare(strict_types=1);

namespace functional\Finance\Application\Commands;

use App\Finance\Application\Command\Withdraw\WithdrawCommand;
use App\Finance\Domain\Account;
use App\Finance\Domain\Balance;
use App\Finance\Domain\Enum\Currencies;
use App\Finance\Domain\Enum\TransactionType;
use App\Finance\Domain\Exception\IncompatibleCurrenciesException;
use App\Finance\Domain\Exception\NotEnoughMoneyException;
use App\Finance\Domain\Repository\AccountRepositoryInterface;
use App\Finance\Domain\Repository\TransactionRepositoryInterface;
use App\Finance\Domain\Transaction;
use Money\Currency;
use Money\Money;
use PHPUnit\Framework\ExpectationFailedException;
use Symfony\Component\Uid\Uuid;
use Tests\functional\ApplicationTestCase;

use function PHPUnit\Framework\assertEquals;
use function PHPUnit\Framework\assertInstanceOf;
use function PHPUnit\Framework\assertNotEmpty;
use function PHPUnit\Framework\assertTrue;

final class WithdrawlTest extends ApplicationTestCase
{
    protected AccountRepositoryInterface $accountRepository;
    protected TransactionRepositoryInterface $transactionRepository;

    private const DEFAULT_AMOUNT = 100;
    private const DEFAULT_COMMENT = 'test';
    private const USER_LOGIN_1 = 'user_1';
    private Account $toAccount;

    protected function setUp(): void
    {
        parent::setUp();

        $this->accountRepository = $this->getContainer(AccountRepositoryInterface::class);
        $this->transactionRepository = $this->getContainer(TransactionRepositoryInterface::class);
        $this->toAccount = new Account(
            Uuid::v4(),
            self::USER_LOGIN_1,
            new Money(self::DEFAULT_AMOUNT, new Currency(Currencies::USD->name))
        );

        $this->accountRepository->store($this->toAccount);
    }

    public function commandParams(): array
    {
        return [
            [
                'amount' => 0,
                'currency' => 'USD',
                'exceptionMessage' => 'Wrong amount',
            ],
            [
                'amount' => self::DEFAULT_AMOUNT,
                'currency' => '',
                'exceptionMessage' => 'Wrong currency',
            ],
            [
                'amount' => self::DEFAULT_AMOUNT,
                'currency' => 'RUB',
                'exceptionMessage' => 'Wrong currency',
            ],
        ];
    }

    /**
     * @dataProvider commandParams
     */
    public function testValidateWithdrawCommand(int $amount, string $currency, string $exceptionMessage): void
    {
        $this->expectException(ExpectationFailedException::class);
        $this->expectExceptionMessage($exceptionMessage);
        new WithdrawCommand(
            $this->toAccount->getUuid(),
            $amount,
            $currency,
            self::DEFAULT_COMMENT,
            null
        );
    }

    public function testWithdrawIncompatibleCurrencies(): void
    {
        $this->expectException(IncompatibleCurrenciesException::class);
        $command = new WithdrawCommand(
            $this->toAccount->getUuid(),
            self::DEFAULT_AMOUNT,
            Currencies::EUR->value,
            self::DEFAULT_COMMENT,
            null
        );

        $this->handle($command);

        $expectedBalanceAmount = new Money(
            self::DEFAULT_AMOUNT,
            new Currency(Currencies::USD->name)
        );

        assertTrue($this->toAccount->getBalance()->getAmount()->equals($expectedBalanceAmount));

        $transactions = $this->transactionRepository->findAll();

        self::assertCount(0, $transactions);
    }

    public function testWithdrawNotEnoughMoney(): void
    {
        $this->expectException(NotEnoughMoneyException::class);
        $command = new WithdrawCommand(
            $this->toAccount->getUuid(),
            self::DEFAULT_AMOUNT + 1,
            Currencies::USD->value,
            self::DEFAULT_COMMENT,
            null
        );

        $this->handle($command);

        $expectedBalanceAmount = new Money(
            self::DEFAULT_AMOUNT,
            new Currency(Currencies::USD->name)
        );

        assertTrue($this->toAccount->getBalance()->getAmount()->equals($expectedBalanceAmount));

        $transactions = $this->transactionRepository->findAll();

        self::assertCount(0, $transactions);
    }

    public function testSuccessWithdraw(): void
    {
        $command = new WithdrawCommand(
            $this->toAccount->getUuid(),
            self::DEFAULT_AMOUNT,
            Currencies::USD->value,
            self::DEFAULT_COMMENT,
            null
        );

        $this->handle($command);

        $account = $this->accountRepository->findOneOrThrow($this->toAccount->getUuid());

        assertNotEmpty($account);
        assertInstanceOf(Account::class, $account);
        assertInstanceOf(Balance::class, $account->getBalance());
        assertEquals(Currencies::USD->value, $account->getBalance()->getAmount()->getCurrency()->getCode());
        $expectedBalanceAmount = new Money(
            0,
            new Currency(Currencies::USD->name)
        );

        assertTrue($account->getBalance()->getAmount()->equals($expectedBalanceAmount));

        $transactions = $this->transactionRepository->findAll();

        self::assertCount(1, $transactions);
        /** @var Transaction $transaction */
        $transaction = current($transactions);
        self::assertInstanceOf(Transaction::class, $transaction);
        self::assertEquals(TransactionType::DOWN, $transaction->getType());

        $expectedAmount = new Money(
            self::DEFAULT_AMOUNT,
            new Currency(Currencies::USD->name)
        );

        self::assertTrue($transaction->getAmount()->equals($expectedAmount));
        self::assertEquals(self::DEFAULT_COMMENT, $transaction->getComment());
    }
}
