<?php

declare(strict_types=1);

namespace App\Finance\Application\Query\GetAllAccounts;

use App\Finance\Infrastructure\ReadModel\Sort;
use App\Shared\Application\Query\QueryInterface;

final class GetAllAccountsQuery implements QueryInterface
{
    public ?Sort $sort = null;

    public function __construct()
    {
    }
}
