<?php

declare(strict_types=1);

namespace functional\Finance\Application\Commands;

use App\Finance\Application\Command\CreateAccount\CreateAccountCommand;
use App\Finance\Domain\Account;
use App\Finance\Domain\Balance;
use App\Finance\Domain\Enum\Currencies;
use App\Finance\Domain\Repository\AccountRepositoryInterface;
use PHPUnit\Framework\ExpectationFailedException;
use Symfony\Component\Uid\Uuid;
use Tests\functional\ApplicationTestCase;

use function PHPUnit\Framework\assertEquals;
use function PHPUnit\Framework\assertInstanceOf;
use function PHPUnit\Framework\assertNotEmpty;

final class CreateAccountTest extends ApplicationTestCase
{
    protected AccountRepositoryInterface $accountRepository;

    private const USER_LOGIN_1 = 'user_1';

    protected function setUp(): void
    {
        parent::setUp();

        $this->accountRepository = $this->getContainer(AccountRepositoryInterface::class);
    }

    public function commandParams(): array
    {
        return [
            [
                'amount' => 0,
                'currency' => 'RUB',
            ],
            [
                'amount' => 0,
                'currency' => '',
            ],
        ];
    }

    /**
     * @dataProvider commandParams
     */
    public function testValidateDepositCommand(int $amount, string $currency): void
    {
        $this->expectException(ExpectationFailedException::class);
        $this->expectExceptionMessage('Wrong currency');
        new CreateAccountCommand(
            self::USER_LOGIN_1,
            $amount,
            $currency
        );
    }

    public function testSuccessCreateAccount(): void
    {
        $uuid = Uuid::v4();
        $command = new CreateAccountCommand(
            self::USER_LOGIN_1,
            0,
            Currencies::USD->value,
            $uuid
        );

        $this->handle($command);

        $account = $this->accountRepository->findOneOrThrow($uuid);

        assertNotEmpty($account);
        assertInstanceOf(Account::class, $account);
        assertInstanceOf(Balance::class, $account->getBalance());
        assertEquals(Currencies::USD->value, $account->getBalance()->getAmount()->getCurrency()->getCode());
    }
}
