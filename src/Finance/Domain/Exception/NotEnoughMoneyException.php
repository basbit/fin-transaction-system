<?php

declare(strict_types=1);

namespace App\Finance\Domain\Exception;

class NotEnoughMoneyException extends \DomainException implements \Throwable
{
    public function __construct()
    {
        parent::__construct('Not enough money.');
    }
}
