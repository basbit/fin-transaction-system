<?php

declare(strict_types=1);

namespace App\Finance\Domain\Exception;

class AccountNotFoundException extends \DomainException implements \Throwable
{
    public function __construct()
    {
        parent::__construct('Account not found.');
    }
}
