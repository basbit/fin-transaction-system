<?php

declare(strict_types=1);

namespace Tests\unit\Finance\Domain;

use App\Finance\Domain\Account;
use App\Finance\Domain\Enum\Currencies;
use App\Finance\Domain\Exception\IncompatibleCurrenciesException;
use App\Finance\Domain\Exception\NotEnoughMoneyException;
use App\Finance\Domain\Transaction;
use Money\Currency;
use Money\Money;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Uid\Uuid;

final class TransactionTest extends TestCase
{
    private const DEFAULT_AMOUNT = 100;
    private const USER_LOGIN_1 = 'user_1';
    private const USER_LOGIN_2 = 'user_2';
    private const DEFAULT_COMMENT = 'test';
    private Account $toAccount;
    private Account $fromAccount;

    protected function setUp(): void
    {
        $this->toAccount = new Account(
            Uuid::v4(),
            self::USER_LOGIN_1,
            new Money(self::DEFAULT_AMOUNT, new Currency(Currencies::USD->name))
        );

        $this->fromAccount = new Account(
            Uuid::v4(),
            self::USER_LOGIN_2,
            new Money(self::DEFAULT_AMOUNT, new Currency(Currencies::USD->name))
        );
    }

    public function testSuccessDeposit(): void
    {
        $prevAmount = $this->toAccount->getBalance()->getAmount();
        $amount = new Money(self::DEFAULT_AMOUNT, new Currency(Currencies::USD->name));
        $transaction = Transaction::deposit($this->toAccount, $amount, self::DEFAULT_COMMENT, null);

        $this->assertInstanceOf(
            Transaction::class,
            $transaction
        );

        self::assertEquals($amount->add($prevAmount), $this->toAccount->getBalance()->getAmount());
    }

    public function testDepositWrongCurrency(): void
    {
        $amount = new Money(self::DEFAULT_AMOUNT, new Currency(Currencies::EUR->name));
        $this->expectException(IncompatibleCurrenciesException::class);
        Transaction::deposit($this->toAccount, $amount, self::DEFAULT_COMMENT, null);
    }

    public function testSuccessWithdraw(): void
    {
        $amount = new Money(self::DEFAULT_AMOUNT, new Currency(Currencies::USD->name));
        $transaction = Transaction::withdraw($this->toAccount, $amount, self::DEFAULT_COMMENT, null);
        $this->assertInstanceOf(
            Transaction::class,
            $transaction
        );

        self::assertTrue($this->toAccount->getBalance()->getAmount()->isZero());
    }

    public function testWithdrawWrongCurrency(): void
    {
        $amount = new Money(self::DEFAULT_AMOUNT, new Currency(Currencies::EUR->name));
        $this->expectException(IncompatibleCurrenciesException::class);
        Transaction::withdraw($this->toAccount, $amount, self::DEFAULT_COMMENT, null);
    }

    public function testWithdrawNotEnoughMoney(): void
    {
        $amount = new Money(self::DEFAULT_AMOUNT + 1, new Currency(Currencies::USD->name));
        $this->expectException(NotEnoughMoneyException::class);
        Transaction::withdraw($this->toAccount, $amount, self::DEFAULT_COMMENT, null);
    }

    public function testSuccessTransfer(): void
    {
        $prevAmount = $this->toAccount->getBalance()->getAmount();
        $amount = new Money(self::DEFAULT_AMOUNT, new Currency(Currencies::USD->name));
        $transactions = Transaction::transfer(
            $amount,
            $this->fromAccount,
            $this->toAccount,
            self::DEFAULT_COMMENT,
            null
        );
        $this->assertIsArray($transactions);
        $this->assertCount(2, $transactions);

        $this->assertInstanceOf(
            Transaction::class,
            $transactions[0]
        );
        $this->assertInstanceOf(
            Transaction::class,
            $transactions[1]
        );

        self::assertTrue($this->fromAccount->getBalance()->getAmount()->isZero());
        self::assertEquals($amount->add($prevAmount), $this->toAccount->getBalance()->getAmount());
    }

    public function testTransferNotEnoughMoney(): void
    {
        $amount = new Money(self::DEFAULT_AMOUNT + 1, new Currency(Currencies::USD->name));
        $this->expectException(NotEnoughMoneyException::class);
        Transaction::transfer($amount, $this->fromAccount, $this->toAccount, self::DEFAULT_COMMENT, null);
    }
}
