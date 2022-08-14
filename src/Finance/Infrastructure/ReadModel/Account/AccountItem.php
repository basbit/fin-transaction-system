<?php

namespace App\Finance\Infrastructure\ReadModel\Account;

use App\Shared\Infrastructure\PropertyAccessorTrait;

final class AccountItem
{
    use PropertyAccessorTrait;

    public const SORTABLE_FIELDS = ['login'];

    public function __construct(
        public string $uuid,
        public string $login,
    ) {
    }
}
