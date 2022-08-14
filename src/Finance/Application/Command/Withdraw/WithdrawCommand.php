<?php

declare(strict_types=1);

namespace App\Finance\Application\Command\Withdraw;

use App\Finance\Domain\Enum\Currencies;
use App\Shared\Application\Command\CommandInterface;
use DateTime;
use Symfony\Component\Uid\Uuid;

use function PHPUnit\Framework\assertNotEmpty;
use function PHPUnit\Framework\assertNotNull;

final class WithdrawCommand implements CommandInterface
{
    public Currencies $currency;

    public function __construct(
        public Uuid $accountUuid,
        public float $amount,
        string $currency,
        public ?string $comment,
        public ?DateTime $dueDate
    ) {
        assertNotEmpty($amount, 'Wrong amount');
        assertNotEmpty($currency, 'Wrong currency');
        assertNotNull(Currencies::tryFrom($currency), 'Wrong currency');

        $this->currency = Currencies::tryFrom($currency);
    }
}
