<?php declare(strict_types=1);

namespace AppBundle\Security;

use AppBundle\Entity\Client;
use AppBundle\Entity\Report\Report;
use AppBundle\Entity\User;
use PHPUnit\Framework\TestCase;
use Prophecy\Prophecy\ObjectProphecy;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\Authorization\AccessDecisionManagerInterface;

class UserVoterTest extends TestCase
{
    /**
     * @dataProvider deleteUserProvider
     * @test
     */
    public function determineDeletePermission(User $deletor, User $deletee, int $expectedPermission)
    {
        /** @var AccessDecisionManagerInterface&ObjectProphecy $dm */
        $dm = self::prophesize(AccessDecisionManagerInterface::class);

        /** @var UserVoter $sut */
        $sut = new UserVoter($dm->reveal());

        $token = new UsernamePasswordToken($deletor, 'credentials', 'memory');

        self::assertEquals($expectedPermission, $sut->vote($token, $deletee, [UserVoter::DELETE_USER]));
    }


    public function deleteUserProvider()
    {
        $clientNoReports = new Client();
        $clientWithReport = (new Client())->setReports([new Report()]);

        $layNoReportsOrClients = (new User)->setRoleName('ROLE_LAY_DEPUTY')->setId(1);
        $layNoReportsOneClient = (new User)->setRoleName('ROLE_LAY_DEPUTY')->setClients([$clientNoReports])->setId(2);
        $layReportOneClient = (new User)->setRoleName('ROLE_LAY_DEPUTY')->setClients([$clientWithReport])->setId(3);

        $pa = (new User)->setRoleName('ROLE_PA')->setId(4);
        $paTwo = (new User)->setRoleName('ROLE_PA')->setId(5);

        $paNamed = (new User)->setRoleName('ROLE_PA_NAMED')->setId(6);
        $paNamedTwo = (new User)->setRoleName('ROLE_PA_NAMED')->setId(7);

        $paAdmin = (new User)->setRoleName('ROLE_PA_ADMIN')->setId(8);
        $paAdminTwo = (new User)->setRoleName('ROLE_PA_ADMIN')->setId(9);

        $prof = (new User)->setRoleName('ROLE_PROF')->setId(10);
        $profTwo = (new User)->setRoleName('ROLE_PROF')->setId(11);

        $profNamed = (new User)->setRoleName('ROLE_PROF_NAMED')->setId(12);
        $profNamedTwo = (new User)->setRoleName('ROLE_PROF_NAMED')->setId(13);

        $profAdmin = (new User)->setRoleName('ROLE_PROF_ADMIN')->setId(14);
        $profAdminTwo = (new User)->setRoleName('ROLE_PROF_ADMIN')->setId(15);

        $admin = (new User)->setRoleName('ROLE_ADMIN')->setId(16);
        $adminTwo = (new User)->setRoleName('ROLE_ADMIN')->setId(17);

        $superAdmin = (new User)->setRoleName('ROLE_SUPER_ADMIN')->setId(18);
        $superAdminTwo = (new User)->setRoleName('ROLE_SUPER_ADMIN')->setId(19);

        return [
            'Lay Deputy deletes Lay Deputy' => [$layNoReportsOrClients, $layNoReportsOneClient, -1],
            'Lay Deputy deletes PA Deputy' => [$layNoReportsOrClients, $pa, -1],
            'Lay Deputy deletes PA Named Deputy' => [$layNoReportsOrClients, $paNamed, -1],
            'Lay Deputy deletes PA Admin Deputy' => [$layNoReportsOrClients, $paAdmin, -1],
            'Lay Deputy deletes Prof Deputy' => [$layNoReportsOrClients, $prof, -1],
            'Lay Deputy deletes Prof Named Deputy' => [$layNoReportsOrClients, $profNamed, -1],
            'Lay Deputy deletes Prof Admin Deputy' => [$layNoReportsOrClients, $profAdmin, -1],
            'Lay Deputy deletes Admin user' => [$layNoReportsOrClients, $admin, -1],
            'Lay Deputy deletes Super Admin user' => [$layNoReportsOrClients, $superAdmin, -1],
            'Lay Deputy deletes self' => [$layNoReportsOrClients, $layNoReportsOrClients, -1],

            'PA Deputy deletes Lay Deputy' => [$pa, $layNoReportsOneClient, -1],
            'PA Deputy deletes PA Deputy' => [$pa, $paTwo, -1],
            'PA Deputy deletes PA Named Deputy' => [$pa, $paNamed, -1],
            'PA Deputy deletes PA Admin Deputy' => [$pa, $paAdmin, -1],
            'PA Deputy deletes Prof Deputy' => [$pa, $prof, -1],
            'PA Deputy deletes Prof Named Deputy' => [$pa, $profNamed, -1],
            'PA Deputy deletes Prof Admin Deputy' => [$pa, $profAdmin, -1],
            'PA Deputy deletes Admin user' => [$pa, $admin, -1],
            'PA Deputy deletes Super Admin user' => [$pa, $superAdmin, -1],
            'PA Deputy deletes self' => [$pa, $pa, -1],

            'PA Named Deputy deletes Lay Deputy' => [$paNamed, $layNoReportsOneClient, -1],
            'PA Named Deputy deletes PA Deputy' => [$paNamed, $pa, 1],
            'PA Named Deputy deletes PA Named Deputy' => [$paNamed, $paNamedTwo, 1],
            'PA Named Deputy deletes PA Admin Deputy' => [$paNamed, $paAdmin, 1],
            'PA Named Deputy deletes Prof Deputy' => [$paNamed, $prof, 1],
            'PA Named Deputy deletes Prof Named Deputy' => [$paNamed, $profNamed, 1],
            'PA Named Deputy deletes Prof Admin Deputy' => [$paNamed, $profAdmin, 1],
            'PA Named Deputy deletes Admin user' => [$paNamed, $admin, -1],
            'PA Named Deputy deletes Super Admin user' => [$paNamed, $superAdmin, -1],
            'PA Named Deputy deletes self' => [$paNamed, $paNamed, -1],

            'PA Admin Deputy deletes Lay Deputy' => [$paAdmin, $layNoReportsOneClient, -1],
            'PA Admin Deputy deletes PA Deputy' => [$paAdmin, $pa, 1],
            'PA Admin Deputy deletes PA Named Deputy' => [$paAdmin, $paNamed, 1],
            'PA Admin Deputy deletes PA Admin Deputy' => [$paAdmin, $paAdminTwo, 1],
            'PA Admin Deputy deletes Prof Deputy' => [$paAdmin, $prof, 1],
            'PA Admin Deputy deletes Prof Named Deputy' => [$paAdmin, $profNamed, 1],
            'PA Admin Deputy deletes Prof Admin Deputy' => [$paAdmin, $profAdmin, 1],
            'PA Admin Deputy deletes Admin user' => [$paAdmin, $admin, -1],
            'PA Admin Deputy deletes Super Admin user' => [$paAdmin, $superAdmin, -1],
            'PA Admin Deputy deletes self' => [$paAdmin, $paAdmin, -1],

            'Prof Deputy deletes Lay Deputy' => [$prof, $layNoReportsOneClient, -1],
            'Prof Deputy deletes PA Deputy' => [$prof, $pa, -1],
            'Prof Deputy deletes PA Named Deputy' => [$prof, $paNamed, -1],
            'Prof Deputy deletes PA Admin Deputy' => [$prof, $paAdmin, -1],
            'Prof Deputy deletes Prof Deputy' => [$prof, $profTwo, -1],
            'Prof Deputy deletes Prof Named Deputy' => [$prof, $profNamed, -1],
            'Prof Deputy deletes Prof Admin Deputy' => [$prof, $profAdmin, -1],
            'Prof Deputy deletes Admin user' => [$prof, $admin, -1],
            'Prof Deputy deletes Super Admin user' => [$prof, $superAdmin, -1],
            'Prof Deputy deletes self' => [$prof, $prof, -1],

            'Prof Named Deputy deletes Lay Deputy' => [$profNamed, $layNoReportsOneClient, -1],
            'Prof Named Deputy deletes PA Deputy' => [$profNamed, $pa, 1],
            'Prof Named Deputy deletes PA Named Deputy' => [$profNamed, $paNamedTwo, 1],
            'Prof Named Deputy deletes PA Admin Deputy' => [$profNamed, $paAdmin, 1],
            'Prof Named Deputy deletes Prof Deputy' => [$profNamed, $prof, 1],
            'Prof Named Deputy deletes Prof Named Deputy' => [$profNamed, $profNamedTwo, 1],
            'Prof Named Deputy deletes Prof Admin Deputy' => [$profNamed, $profAdmin, 1],
            'Prof Named Deputy deletes Admin user' => [$profNamed, $admin, -1],
            'Prof Named Deputy deletes Super Admin user' => [$profNamed, $superAdmin, -1],
            'Prof Named Deputy deletes self' => [$profNamed, $profNamed, -1],

            'Prof Admin Deputy deletes Lay Deputy' => [$profAdmin, $layNoReportsOneClient, -1],
            'Prof Admin Deputy deletes PA Deputy' => [$profAdmin, $pa, 1],
            'Prof Admin Deputy deletes PA Named Deputy' => [$profAdmin, $paNamedTwo, 1],
            'Prof Admin Deputy deletes PA Admin Deputy' => [$profAdmin, $paAdmin, 1],
            'Prof Admin Deputy deletes Prof Deputy' => [$profAdmin, $prof, 1],
            'Prof Admin Deputy deletes Prof Named Deputy' => [$profAdmin, $profNamed, 1],
            'Prof Admin Deputy deletes Prof Admin Deputy' => [$profAdmin, $profAdminTwo, 1],
            'Prof Admin Deputy deletes Admin user' => [$profAdmin, $admin, -1],
            'Prof Admin Deputy deletes Super Admin user' => [$profAdmin, $superAdmin, -1],
            'Prof Admin Deputy deletes self' => [$profAdmin, $profAdmin, -1],

            'Admin deletes Lay Deputy with no reports or clients' => [$admin, $layNoReportsOrClients, 1],
            'Admin deletes Lay Deputy with no reports, one client' => [$admin, $layNoReportsOneClient, 1],
            'Admin deletes Lay Deputy with one report and client' => [$admin, $layReportOneClient, -1],
            'Admin deletes PA Deputy' => [$admin, $pa, 1],
            'Admin deletes PA Named Deputy' => [$admin, $paNamedTwo, 1],
            'Admin deletes PA Admin Deputy' => [$admin, $paAdmin, 1],
            'Admin deletes Prof Deputy' => [$admin, $prof, 1],
            'Admin deletes Prof Named Deputy' => [$admin, $profNamed, 1],
            'Admin deletes Prof Admin Deputy' => [$admin, $profAdmin, 1],
            'Admin deletes Admin user' => [$admin, $adminTwo, -1],
            'Admin deletes Super Admin user' => [$admin, $superAdmin, -1],
            'Admin deletes self' => [$admin, $admin, -1],

            'Super Admin deletes Lay Deputy with no reports or clients' => [$superAdmin, $layNoReportsOrClients, 1],
            'Super Admin deletes Lay Deputy with no reports, one client' => [$superAdmin, $layNoReportsOneClient, 1],
            'Super Admin deletes Lay Deputy with one report and client' => [$superAdmin, $layReportOneClient, -1],
            'Super Admin deletes PA Deputy' => [$superAdmin, $pa, 1],
            'Super Admin deletes PA Named Deputy' => [$superAdmin, $paNamedTwo, 1],
            'Super Admin deletes PA Admin Deputy' => [$superAdmin, $paAdmin, 1],
            'Super Admin deletes Prof Deputy' => [$superAdmin, $prof, 1],
            'Super Admin deletes Prof Named Deputy' => [$superAdmin, $profNamed, 1],
            'Super Admin deletes Prof Admin Deputy' => [$superAdmin, $profAdmin, 1],
            'Super Admin deletes Admin user' => [$superAdmin, $admin, 1],
            'Super Admin deletes Super Admin user' => [$superAdmin, $superAdminTwo, 1],
            'Super Admin deletes self' => [$superAdmin, $superAdmin, -1],
        ];
    }
}
