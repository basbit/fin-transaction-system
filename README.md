# Financial transactions system

## Table of contents

[1. Project description](#Project-description)

[2. Project files](#Project-files)

[3. Applied patterns](#Applied-patterns)

[4. Stack](#Stack)

[5. Commands](#Commands)

## Project description

Implement a set of classes for managing the financial operations of an account.

There are three types of transactions: deposits, withdrawals and transfer from account to account.
The transaction contains a comment, an amount, and a due date.

Only business logic code.

Methods:

- get all accounts in the system.
- get the balance of a specific account
- perform an operation
- get all account transactions sorted by comment in alphabetical order.
- get all account transactions sorted by date.

:arrow_up:[to table of contents](#table-of-contents)

## Project files

```shell
src
├── Finance
│   ├── Application
│   │   ├── Command
│   │   │   ├── CreateAccount
│   │   │   │   ├── CreateAccountCommand.php
│   │   │   │   └── CreateAccountHandler.php
│   │   │   ├── Deposit
│   │   │   │   ├── DepositCommand.php
│   │   │   │   └── DepositHandler.php
│   │   │   ├── Transfer
│   │   │   │   ├── TransferCommand.php
│   │   │   │   └── TransferHandler.php
│   │   │   └── Withdraw
│   │   │       ├── WithdrawCommand.php
│   │   │       └── WithdrawHandler.php
│   │   └── Query
│   │       ├── GetAccountBalance
│   │       │   ├── GetAccountBalanceHandler.php
│   │       │   └── GetAccountBalanceQuery.php
│   │       ├── GetAllAccounts
│   │       │   ├── GetAllAccountsHandler.php
│   │       │   └── GetAllAccountsQuery.php
│   │       └── GetAllTransactions
│   │           ├── GetAllTransactionsHandler.php
│   │           └── GetAllTransactionsQuery.php
│   ├── Domain
│   │   ├── Account.php
│   │   ├── AggregateRoot.php
│   │   ├── Balance.php
│   │   ├── Enum
│   │   │   ├── Currencies.php
│   │   │   └── TransactionType.php
│   │   ├── Exception
│   │   │   ├── AccountAlreadyExistException.php
│   │   │   ├── AccountNotFoundException.php
│   │   │   ├── IncompatibleCurrenciesException.php
│   │   │   ├── NotEnoughMoneyException.php
│   │   │   ├── TransactionNotFoundException.php
│   │   │   └── WrongAmountException.php
│   │   ├── Repository
│   │   │   ├── AccountRepositoryInterface.php
│   │   │   └── TransactionRepositoryInterface.php
│   │   └── Transaction.php
│   └── Infrastructure
│       ├── ReadModel
│       │   ├── Account
│       │   │   ├── AccountFetcher.php
│       │   │   └── AccountItem.php
│       │   ├── Sort.php
│       │   └── Transaction
│       │       ├── TransactionFetcher.php
│       │       └── TransactionItem.php
│       └── Repository
│           ├── AccountRepository.php
│           └── TransactionRepository.php
└── Shared
    ├── Application
    │   ├── Command
    │   │   ├── CommandBusInterface.php
    │   │   ├── CommandHandlerInterface.php
    │   │   └── CommandInterface.php
    │   └── Query
    │       ├── QueryBusInterface.php
    │       ├── QueryHandlerInterface.php
    │       └── QueryInterface.php
    └── Infrastructure
        ├── Bus
        │   ├── Command
        │   │   └── MessengerCommandBus.php
        │   ├── MessageBusExceptionTrait.php
        │   └── Query
        │       └── MessengerQueryBus.php
        └── TraitEntityPropertyAccess.php

```

:arrow_up:[to table of contents](#table-of-contents)

## Applied patterns

- [X] SOLID
- [X] DDD
- [X] CQRS
- [X] KISS
- [X] YAGNI
- [X] DRY

:arrow_up:[to table of contents](#table-of-contents)

## Stack

- [X] PHP 8.1
- [X] Docker

:arrow_up:[to table of contents](#table-of-contents)

## Commands

| Action           | Command        |
|------------------|----------------|
| Run project      | `make run`       |
| Composer install | `make install` |
| Run Tests        | `make test`    |
| Run Lints        | `make lint`    |

:arrow_up:[to table of contents](#table-of-contents)
