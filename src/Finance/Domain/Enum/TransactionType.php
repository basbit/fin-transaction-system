<?php

namespace App\Finance\Domain\Enum;

enum TransactionType: int
{
    case UP = 1;
    case DOWN = 2;
}
