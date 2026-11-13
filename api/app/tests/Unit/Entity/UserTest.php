<?php

declare(strict_types=1);

namespace Tests\OPG\Digideps\Backend\Unit\Entity;

use OPG\Digideps\Backend\Entity\CourtOrder;
use OPG\Digideps\Backend\Entity\Deputy;
use OPG\Digideps\Backend\Entity\User;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class UserTest extends TestCase
{
    /**
     * @return array<string, array{?string}>
     */
    public static function passwordsNotSetProvider(): array
    {
        return [
            'null password' => [null],
            'empty password' => [''],
            'whitespace password' => [" \t\n"],
        ];
    }

    #[DataProvider('passwordsNotSetProvider')]
    public function testActiveStatusIsInactiveWhenPasswordIsNotSet(?string $password): void
    {
        $user = new User('', '', '')->setPassword($password);

        self::assertSame('inactive_password_not_set', $user->isActiveStatus());
    }

    public function testActiveStatusIsActiveWithoutDeputy(): void
    {
        $user = new User('', '', '')->setPassword('password');

        self::assertSame('active_without_deputy', $user->isActiveStatus());
    }

    /**
     * @return array<string, array{array<array{bool, bool}>, string}>
     */
    public static function courtOrderStatusesProvider(): array
    {
        return [
            'active court order' => [[[true, true]], 'active_with_court_order'],
            'no court orders' => [[], 'inactive_no_active_court_order'],
            'inactive deputy relationship' => [[[false, true]], 'inactive_no_active_court_order'],
            'inactive court order' => [[[true, false]], 'inactive_no_active_court_order'],
            'inactive order before active order' => [
                [[false, true], [true, true]],
                'active_with_court_order',
            ],
        ];
    }

    /**
     * @param array<array{bool, bool}> $courtOrderStatuses
     */
    #[DataProvider('courtOrderStatusesProvider')]
    public function testActiveStatusWithDeputyCourtOrders(array $courtOrderStatuses, string $expectedStatus): void
    {
        $courtOrdersWithStatus = [];
        foreach ($courtOrderStatuses as [$isDeputyActive, $isCourtOrderActive]) {
            $courtOrder = $this->createStub(CourtOrder::class);
            $courtOrder->method('isActive')->willReturn($isCourtOrderActive);
            $courtOrdersWithStatus[] = [
                'courtOrder' => $courtOrder,
                'isActive' => $isDeputyActive,
            ];
        }

        $deputy = $this->createStub(Deputy::class);
        $deputy->method('getCourtOrdersWithStatus')->willReturn($courtOrdersWithStatus);

        $user = new User('', '', '')
            ->setPassword('password')
            ->setDeputy($deputy);

        self::assertSame($expectedStatus, $user->isActiveStatus());
    }
}
