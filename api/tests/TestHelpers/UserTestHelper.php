<?php


namespace Tests\TestHelpers;


use AppBundle\Entity\User;
use PHPUnit\Framework\TestCase;

class UserTestHelper extends TestCase
{
    public function createUserMock(string $roleName, bool $hasReports, bool $hasClients, int $id)
    {
        $clientTestHelper = new ClientTestHelper();

        $clients = $hasClients ? [$clientTestHelper->createClientMock(1, $hasReports)] : null;

        $user = self::prophesize(User::class);
        $user->getRoleName()->willReturn($roleName);
        $user->getClients()->willReturn($clients);
        $user->hasReports()->willReturn($hasReports);
        $user->getId()->willReturn($id);

        return $user->reveal();
    }
}
