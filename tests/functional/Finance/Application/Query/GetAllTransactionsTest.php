<?php

declare(strict_types=1);

namespace functional\Finance\Application\Query;

use App\Finance\Application\Query\GetAllTransactions\GetAllTransactionsQuery;
use App\Finance\Domain\Account;
use App\Finance\Domain\Enum\Currencies;
use App\Finance\Domain\Enum\TransactionType;
use App\Finance\Domain\Repository\AccountRepositoryInterface;
use App\Finance\Domain\Repository\TransactionRepositoryInterface;
use App\Finance\Domain\Transaction;
use DateTime;
use Money\Currency;
use Money\Money;
use Symfony\Component\Uid\Uuid;
use Tests\functional\ApplicationTestCase;

final class GetAllTransactionsTest extends ApplicationTestCase
{
    public const DEFAULT_AMOUNT = 100;
    protected TransactionRepositoryInterface $transactionRepository;
    protected AccountRepositoryInterface $accountRepository;

    private const USER_LOGIN_1 = 'user_1';
    private const USER_LOGIN_2 = 'user_2';

    private Account $account;
    private Account $anotherAccount;
    private array $transactions;

    protected function setUp(): void
    {
        parent::setUp();

        $this->transactionRepository = $this->getContainer(TransactionRepositoryInterface::class);
        $this->accountRepository = $this->getContainer(AccountRepositoryInterface::class);
        $this->account = new Account(
            Uuid::v4(),
            self::USER_LOGIN_1,
            new Money(self::DEFAULT_AMOUNT, new Currency(Currencies::USD->name))
        );

        $this->anotherAccount = new Account(
            Uuid::v4(),
            self::USER_LOGIN_2,
            new Money(0, new Currency(Currencies::USD->name))
        );

        $this->accountRepository->store($this->anotherAccount);

        $this->transactions = [
            new Transaction(
                $this->account,
                new Money(self::DEFAULT_AMOUNT, new Currency(Currencies::USD->name)),
                TransactionType::UP,
                'test topup 1',
                (new DateTime())->modify('-2 day')
            ),
            new Transaction(
                $this->account,
                new Money(self::DEFAULT_AMOUNT, new Currency(Currencies::USD->name)),
                TransactionType::UP,
                'test topup 2',
                (new DateTime())->modify('-1 day')
            ),
            new Transaction(
                $this->account,
                new Money(self::DEFAULT_AMOUNT, new Currency(Currencies::USD->name)),
                TransactionType::DOWN,
                'test transfer 3',
                (new DateTime())
            ),
            new Transaction(
                $this->anotherAccount,
                new Money(self::DEFAULT_AMOUNT, new Currency(Currencies::USD->name)),
                TransactionType::UP,
                'test transfer 4',
                (new DateTime())
            ),
            new Transaction(
                $this->anotherAccount,
                new Money(self::DEFAULT_AMOUNT, new Currency(Currencies::USD->name)),
                TransactionType::DOWN,
                'test withdraw 5',
                (new DateTime())->modify('+1 minute')
            ),
        ];

        $this->transactionRepository->batchStore($this->transactions);
    }

    public function testSuccessGetAllTransactions(): void
    {
        $command = new GetAllTransactionsQuery();

        $result = $this->ask($command);
        self::assertIsArray($result);
        self::assertCount(count($this->transactions), $result);
    }

    public function testGetAllTransactionsSortByComment(): void
    {
        $command = new GetAllTransactionsQuery('comment', 'DESC');

        $result = $this->ask($command);

        self::assertIsArray($result);
        self::assertCount(count($this->transactions), $result);

        /** @var Transaction $transaction */
        $transaction = end($this->transactions);
        self::assertArrayHasKey('comment', current($result));
        self::assertEquals(current($result)['comment'], $transaction->getComment());
    }

    public function testGetAllTransactionsSortByDate(): void
    {
        $command = new GetAllTransactionsQuery('dueDate', 'DESC');

        $result = $this->ask($command);

        self::assertIsArray($result);
        self::assertCount(count($this->transactions), $result);

        /** @var Transaction $transaction */
        $transaction = end($this->transactions);
        self::assertArrayHasKey('dueDate', current($result));
        self::assertEquals(current($result)['dueDate'], $transaction->getDueDate()->format('Y-m-d H:i:s'));
    }
}
