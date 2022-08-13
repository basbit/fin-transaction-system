<?php

declare(strict_types=1);

namespace App\Finance\Domain\Repository;

use App\Finance\Domain\Account;
use Symfony\Component\Uid\Uuid;

interface AccountRepositoryInterface
{
    /**
     * @throwable AccountNotFoundException
     */
    public function findOneOrThrow(Uuid $uuid): Account;

    public function findAll(): array;

    public function store(Account $account): void;
}
