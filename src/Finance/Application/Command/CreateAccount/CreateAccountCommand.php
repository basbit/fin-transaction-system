<?php

declare(strict_types=1);

namespace App\Finance\Application\Command\CreateAccount;

use App\Finance\Domain\Enum\Currencies;
use App\Shared\Application\Command\CommandInterface;
use Symfony\Component\Uid\Uuid;

use function PHPUnit\Framework\assertNotEmpty;
use function PHPUnit\Framework\assertNotNull;

final class CreateAccountCommand implements CommandInterface
{
    public Currencies $currency;

    public function __construct(
        public string $login,
        public float $amount,
        string $currency,
        public ?Uuid $uuid = null,
    ) {
        assertNotEmpty($currency, 'Wrong currency');
        assertNotNull(Currencies::tryFrom($currency), 'Wrong currency');

        $this->currency = Currencies::tryFrom($currency);

        if (!$uuid instanceof Uuid) {
            $this->uuid = Uuid::v4();
        }
    }
}
