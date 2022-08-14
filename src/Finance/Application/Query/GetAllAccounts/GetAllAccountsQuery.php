<?php

declare(strict_types=1);

namespace App\Finance\Application\Query\GetAllAccounts;

use App\Finance\Infrastructure\ReadModel\Account\AccountItem;
use App\Finance\Infrastructure\ReadModel\Sort;
use App\Shared\Application\Query\QueryInterface;

use function PHPUnit\Framework\assertContains;

final class GetAllAccountsQuery implements QueryInterface
{
    public ?Sort $sort = null;

    public function __construct(
        ?string $sortingField = null,
        ?string $direction = null,
    ) {
        if (null !== $sortingField) {
            assertContains($sortingField, AccountItem::SORTABLE_FIELDS);
            $this->sort = new Sort($sortingField, $direction);
        }
    }
}
