<?php

namespace App\Finance\Infrastructure\ReadModel\Account;

use App\Shared\Infrastructure\TraitEntityPropertyAccess;

final class AccountItem
{
    use TraitEntityPropertyAccess;

    public const SORTABLE_FIELDS = ['login'];

    public function __construct(
        public string $uuid,
        public string $login,
    ) {
    }
}
