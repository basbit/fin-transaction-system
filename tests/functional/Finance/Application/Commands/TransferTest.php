<?php

declare(strict_types=1);

namespace functional\Finance\Application\Commands;

use App\Finance\Application\Command\Transfer\TransferCommand;
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

final class TransferTest extends ApplicationTestCase
{
    protected AccountRepositoryInterface $accountRepository;
    protected TransactionRepositoryInterface $transactionRepository;

    private const DEFAULT_AMOUNT = 100;
    private const DEFAULT_COMMENT = 'test';
    private const USER_LOGIN_1 = 'user_1';
    private const USER_LOGIN_2 = 'user_2';
    private Account $toAccount;
    private Account $fromAccount;

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
        $this->fromAccount = new Account(
            Uuid::v4(),
            self::USER_LOGIN_2,
            new Money(self::DEFAULT_AMOUNT, new Currency(Currencies::USD->name))
        );

        $this->accountRepository->store($this->toAccount);
        $this->accountRepository->store($this->fromAccount);
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
    public function testValidateTransferCommand(int $amount, string $currency, string $exceptionMessage): void
    {
        $this->expectException(ExpectationFailedException::class);
        $this->expectExceptionMessage($exceptionMessage);
        new TransferCommand(
            $this->fromAccount->getUuid(),
            $this->toAccount->getUuid(),
            $amount,
            $currency,
            self::DEFAULT_COMMENT,
            null
        );
    }

    public function testTransferIncompatibleCurrencies(): void
    {
        $this->expectException(IncompatibleCurrenciesException::class);
        $command = new TransferCommand(
            $this->fromAccount->getUuid(),
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
        assertTrue($this->fromAccount->getBalance()->getAmount()->equals($expectedBalanceAmount));

        $transactions = $this->transactionRepository->findAll();

        self::assertCount(0, $transactions);
    }

    public function testTransferNotEnoughMoney(): void
    {
        $this->expectException(NotEnoughMoneyException::class);
        $command = new TransferCommand(
            $this->fromAccount->getUuid(),
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
        assertTrue($this->fromAccount->getBalance()->getAmount()->equals($expectedBalanceAmount));

        $transactions = $this->transactionRepository->findAll();

        self::assertCount(0, $transactions);
    }

    public function testSuccessTransfer(): void
    {
        $command = new TransferCommand(
            $this->fromAccount->getUuid(),
            $this->toAccount->getUuid(),
            self::DEFAULT_AMOUNT,
            Currencies::USD->value,
            self::DEFAULT_COMMENT,
            null
        );

        $this->handle($command);

        $toAccount = $this->accountRepository->findOneOrThrow($this->toAccount->getUuid());

        assertNotEmpty($toAccount);
        assertInstanceOf(Account::class, $toAccount);
        assertInstanceOf(Balance::class, $toAccount->getBalance());
        assertEquals(Currencies::USD->value, $toAccount->getBalance()->getAmount()->getCurrency()->getCode());
        $expectedBalanceAmountRecipient = new Money(
            self::DEFAULT_AMOUNT + self::DEFAULT_AMOUNT,
            new Currency(Currencies::USD->name)
        );

        assertTrue($toAccount->getBalance()->getAmount()->equals($expectedBalanceAmountRecipient));

        $fromAccount = $this->accountRepository->findOneOrThrow($this->fromAccount->getUuid());

        assertNotEmpty($fromAccount);
        assertInstanceOf(Account::class, $fromAccount);
        assertInstanceOf(Balance::class, $fromAccount->getBalance());
        assertEquals(Currencies::USD->value, $fromAccount->getBalance()->getAmount()->getCurrency()->getCode());
        $expectedBalanceAmountSender = new Money(
            0,
            new Currency(Currencies::USD->name)
        );

        assertTrue($fromAccount->getBalance()->getAmount()->equals($expectedBalanceAmountSender));

        $transactions = $this->transactionRepository->findAll();

        self::assertCount(2, $transactions);
        /** @var Transaction $transaction */
        $transaction = current($transactions);
        self::assertInstanceOf(Transaction::class, $transaction);
        self::assertEquals(TransactionType::DOWN, $transaction->getType());

        $expectedAmountForWithdrawl = new Money(
            self::DEFAULT_AMOUNT,
            new Currency(Currencies::USD->name)
        );

        self::assertTrue($transaction->getAmount()->equals($expectedAmountForWithdrawl));
        self::assertEquals(self::DEFAULT_COMMENT, $transaction->getComment());


        /** @var Transaction $transaction */
        $transaction = next($transactions);
        self::assertInstanceOf(Transaction::class, $transaction);
        self::assertEquals(TransactionType::UP, $transaction->getType());

        $expectedAmountForTopup = new Money(
            self::DEFAULT_AMOUNT,
            new Currency(Currencies::USD->name)
        );

        self::assertTrue($transaction->getAmount()->equals($expectedAmountForTopup));
        self::assertEquals(self::DEFAULT_COMMENT, $transaction->getComment());
    }
}
