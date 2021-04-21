<?php declare(strict_types=1);

namespace App\Security;

use App\Entity\Client;
use App\Entity\User;
use App\TestHelpers\ClientHelper;
use App\TestHelpers\UserHelper;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\Security;

class ClientVoterTest extends KernelTestCase
{
    /**
     * @dataProvider deleteClientProvider
     * @test
     */
    public function determineDeletePermission(User $user, Client $client, int $expectedPermission)
    {
        $security =  self::prophesize(Security::class);

        /** @var ClientVoter() $sut */
        $sut = new ClientVoter($security->reveal());

        $token = new UsernamePasswordToken($user, 'credentials', 'memory');

        self::assertEquals($expectedPermission, $sut->vote($token, $client, [ClientVoter::DELETE]));
    }

    public function deleteClientProvider()
    {
        $client = ClientHelper::createClient();

        $admin = UserHelper::createAdminUser();
        $superAdmin = UserHelper::createSuperAdminUser();
        $elevatedAdmin = UserHelper::createElevatedAdminUser();

        $lay = UserHelper::createLayUser();

        $pa = UserHelper::createPaDeputyUser();
        $paNamed = UserHelper::createPaNamedDeputyUser();
        $paAdmin = UserHelper::createPaAdminUser();
        $paTeamMember = UserHelper::createPaTeamMemberUser();

        $prof = UserHelper::createProfDeputyUser();
        $profNamed = UserHelper::createProfNamedDeputyUser();
        $profAdmin = UserHelper::createProfAdminUser();
        $profTeamMember = UserHelper::createProfTeamMemberUser();

        return [
            'Lay Deputy deletes Client' => [$lay, $client, -1],
            'PA Deputy deletes Client' => [$pa, $client, -1],
            'PA Team Member deletes Client' => [$paTeamMember, $client, -1],
            'PA Named Deputy deletes Client' => [$paNamed, $client, -1],
            'PA Admin Deputy deletes Client' => [$paAdmin, $client, -1],
            'Prof Deputy deletes Client' => [$prof, $client, -1],
            'Prof Team Member deletes Client' => [$profTeamMember, $client, -1],
            'Prof Named Deputy deletes Client' => [$profNamed, $client, -1],
            'Prof Admin Deputy deletes Client' => [$profAdmin, $client, -1],
            'Admin deletes Client' => [$admin, $client, -1],
            'Elevated Admin deletes Client' => [$elevatedAdmin, $client, 1],
            'Super Admin deletes Client' => [$superAdmin, $client, 1],
        ];
    }
}
