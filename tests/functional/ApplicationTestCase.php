<?php

declare(strict_types=1);

namespace Tests\functional;

use App\Finance\Application\Command\CreateAccount\CreateAccountCommand;
use App\Finance\Application\Command\CreateAccount\CreateAccountHandler;
use App\Finance\Application\Command\Deposit\DepositCommand;
use App\Finance\Application\Command\Deposit\DepositHandler;
use App\Finance\Application\Command\Transfer\TransferCommand;
use App\Finance\Application\Command\Transfer\TransferHandler;
use App\Finance\Application\Command\Withdraw\WithdrawCommand;
use App\Finance\Application\Command\Withdraw\WithdrawHandler;
use App\Finance\Application\Query\GetAccountBalance\GetAccountBalanceHandler;
use App\Finance\Application\Query\GetAccountBalance\GetAccountBalanceQuery;
use App\Finance\Application\Query\GetAllAccounts\GetAllAccountsHandler;
use App\Finance\Application\Query\GetAllAccounts\GetAllAccountsQuery;
use App\Finance\Application\Query\GetAllTransactions\GetAllTransactionsHandler;
use App\Finance\Application\Query\GetAllTransactions\GetAllTransactionsQuery;
use App\Finance\Domain\Repository\AccountRepositoryInterface;
use App\Finance\Domain\Repository\TransactionRepositoryInterface;
use App\Finance\Infrastructure\ReadModel\Account\AccountFetcher;
use App\Finance\Infrastructure\ReadModel\Transaction\TransactionFetcher;
use App\Finance\Infrastructure\Repository\AccountRepository;
use App\Finance\Infrastructure\Repository\TransactionRepository;
use App\Shared\Application\Command\CommandBusInterface;
use App\Shared\Application\Command\CommandInterface;
use App\Shared\Application\Query\QueryBusInterface;
use App\Shared\Application\Query\QueryInterface;
use App\Shared\Infrastructure\Bus\Command\MessengerCommandBus;
use App\Shared\Infrastructure\Bus\Query\MessengerQueryBus;
use DI\Container;
use DI\ContainerBuilder;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Messenger\Handler\HandlersLocator;
use Symfony\Component\Messenger\MessageBus;
use Symfony\Component\Messenger\Middleware\HandleMessageMiddleware;
use Throwable;

use function DI\autowire;
use function DI\get;

abstract class ApplicationTestCase extends TestCase
{
    private Container $container;

    private ?CommandBusInterface $commandBus;

    private ?QueryBusInterface $queryBus;

    public function getContainer(string $class)
    {
        return $this->container->get($class);
    }

    /**
     * init DI
     */
    private function buildContainers(): void
    {
        $builder = new ContainerBuilder();
        $builder->useAnnotations(false);
        $builder->useAutowiring(true);

        $builder->addDefinitions([
            AccountRepository::class => autowire(),
            AccountRepositoryInterface::class => get(AccountRepository::class),
            TransactionRepository::class => autowire(),
            TransactionRepositoryInterface::class => get(TransactionRepository::class),
            AccountFetcher::class => autowire(),
            TransactionFetcher::class => autowire(),
            DepositHandler::class => autowire(),
            WithdrawHandler::class => autowire(),
            TransferHandler::class => autowire(),
            GetAccountBalanceHandler::class => autowire(),
            GetAllAccountsHandler::class => autowire(),
            GetAllTransactionsHandler::class => autowire(),
        ]);

        $this->container = $builder->build();
    }

    /**
     * init CQRS
     */
    private function getCommands(): array
    {
        return [
            CreateAccountCommand::class => [$this->getContainer(CreateAccountHandler::class)],
            DepositCommand::class => [$this->getContainer(DepositHandler::class)],
            WithdrawCommand::class => [$this->getContainer(WithdrawHandler::class)],
            TransferCommand::class => [$this->getContainer(TransferHandler::class)],
        ];
    }

    /**
     * init CQRS
     */
    private function getQueries(): array
    {
        return [
            GetAccountBalanceQuery::class => [$this->getContainer(GetAccountBalanceHandler::class)],
            GetAllAccountsQuery::class => [$this->getContainer(GetAllAccountsHandler::class)],
            GetAllTransactionsQuery::class => [$this->getContainer(GetAllTransactionsHandler::class)],
        ];
    }

    protected function setUp(): void
    {
        $this->buildContainers();

        $this->commandBus = new MessengerCommandBus(new MessageBus([
            new HandleMessageMiddleware(new HandlersLocator($this->getCommands())),
        ]));
        $this->queryBus = new MessengerQueryBus(new MessageBus([
            new HandleMessageMiddleware(new HandlersLocator($this->getQueries()))
        ]));
    }

    /**
     * @return mixed
     *
     * @throws Throwable
     */
    protected function ask(QueryInterface $query)
    {
        return $this->queryBus->ask($query);
    }

    /**
     * @throws Throwable
     */
    protected function handle(CommandInterface $command): void
    {
        $this->commandBus->handle($command);
    }
}
