<?php

declare(strict_types=1);

namespace App\Finance\Application\Query\GetAllTransactions;

use App\Finance\Infrastructure\ReadModel\Sort;
use App\Finance\Infrastructure\ReadModel\Transaction\TransactionItem;
use App\Shared\Application\Query\QueryInterface;

use function PHPUnit\Framework\assertContains;

final class GetAllTransactionsQuery implements QueryInterface
{
    public ?Sort $sort = null;

    public function __construct(
        ?string $sortingField = null,
        ?string $direction = null,
    ) {
        if (null !== $sortingField) {
            assertContains($sortingField, TransactionItem::SORTABLE_FIELDS);
            $this->sort = new Sort($sortingField, $direction);
        }
    }
}
