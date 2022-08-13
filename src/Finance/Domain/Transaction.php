<?php

namespace App\Finance\Domain;

use App\Finance\Domain\Enum\TransactionType;
use App\Finance\Domain\Exception\IncompatibleCurrenciesException;
use App\Finance\Domain\Exception\WrongAmountException;
use DateTime;
use Money\Money;
use Symfony\Component\Uid\Uuid;

class Transaction implements AggregateRoot
{
    private Uuid $uuid;
    private DateTime $createdAt;

    public function __construct(
        private Account $account,
        private Money $amount,
        private TransactionType $type,
        private ?string $comment,
        private ?DateTime $dueDate,
    ) {
        if (!$account->getBalance()->getAmount()->getCurrency()->equals($amount->getCurrency())) {
            throw new IncompatibleCurrenciesException();
        }

        if ($amount->isZero()) {
            throw new WrongAmountException();
        }

        $this->uuid = Uuid::v4();
        $this->createdAt = new DateTime();
    }

    public static function deposit(
        Account $account,
        Money $amount,
        ?string $comment,
        ?DateTime $dueDate
    ): self {
        $transaction = new self($account, $amount, TransactionType::UP, $comment, $dueDate);
        $account->getBalance()->topup($amount);

        return $transaction;
    }

    public static function withdraw(
        Account $account,
        Money $amount,
        ?string $comment,
        ?DateTime $dueDate
    ): self {
        $transaction = new self($account, $amount, TransactionType::DOWN, $comment, $dueDate);
        $account->getBalance()->withdraw($amount);

        return $transaction;
    }

    public static function transfer(
        Money $amount,
        Account $fromAccount,
        Account $toAccount,
        ?string $comment,
        ?DateTime $dueDate
    ): array {
        return [
            self::withdraw($fromAccount, $amount, $comment, $dueDate),
            self::deposit($toAccount, $amount, $comment, $dueDate)
        ];
    }

    public function getUuid(): Uuid
    {
        return $this->uuid;
    }

    public function getAccount(): Account
    {
        return $this->account;
    }

    public function getAmount(): Money
    {
        return $this->amount;
    }

    public function getType(): TransactionType
    {
        return $this->type;
    }

    public function getComment(): ?string
    {
        return $this->comment;
    }

    public function getDueDate(): ?DateTime
    {
        return $this->dueDate;
    }
}
