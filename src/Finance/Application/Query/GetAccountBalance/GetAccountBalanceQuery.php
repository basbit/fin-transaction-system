<?php

declare(strict_types=1);

namespace App\Finance\Application\Query\GetAccountBalance;

use App\Shared\Application\Query\QueryInterface;
use Symfony\Component\Uid\Uuid;

final class GetAccountBalanceQuery implements QueryInterface
{
    public function __construct(public Uuid $accountUuid)
    {
    }
}
