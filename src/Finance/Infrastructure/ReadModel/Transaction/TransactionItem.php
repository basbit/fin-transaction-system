<?php

declare(strict_types=1);

namespace App\Finance\Infrastructure\ReadModel\Transaction;

use App\Shared\Infrastructure\PropertyAccessorTrait;

final class TransactionItem
{
    use PropertyAccessorTrait;

    public const SORTABLE_FIELDS = ['dueDate', 'comment'];

    public function __construct(
        public string $uuid,
        public string $accountUuid,
        public string $login,
        public float $amount,
        public string $currency,
        public string $type,
        public ?string $comment,
        public ?string $dueDate,
    ) {
    }
}
