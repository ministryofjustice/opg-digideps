<?php declare(strict_types=1);

namespace AppBundle\Security;

use AppBundle\Entity\User;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Tests\TestHelpers\UserTestHelper;

class UserVoterTest extends TestCase
{
    /**
     * @dataProvider deleteUserProvider
     * @test
     */
    public function determineDeletePermission(User $deletor, User $deletee, int $expectedPermission)
    {
        /** @var UserVoter $sut */
        $sut = new UserVoter();

        $token = new UsernamePasswordToken($deletor, 'credentials', 'memory');

        self::assertEquals($expectedPermission, $sut->vote($token, $deletee, [UserVoter::DELETE_USER]));
    }

    public function deleteUserProvider()
    {
        $userTestHelper = new UserTestHelper();

        $layNoReportsOrClients = $userTestHelper->createUserMock('ROLE_LAY_DEPUTY', false, false, 1);
        $layNoReportsOneClient = $userTestHelper->createUserMock('ROLE_LAY_DEPUTY', false, true, 2);
        $layReportOneClient = $userTestHelper->createUserMock('ROLE_LAY_DEPUTY', true, true, 3);

        $pa = $userTestHelper->createUserMock('ROLE_PA', false, false, 4);
        $paTwo = $userTestHelper->createUserMock('ROLE_PA', false, false, 5);

        $paNamed = $userTestHelper->createUserMock('ROLE_PA_NAMED', false, false, 6);
        $paNamedTwo = $userTestHelper->createUserMock('ROLE_PA_NAMED', false, false, 7);

        $paAdmin = $userTestHelper->createUserMock('ROLE_PA_ADMIN', false, false, 8);
        $paAdminTwo = $userTestHelper->createUserMock('ROLE_PA_ADMIN', false, false, 9);

        $prof = $userTestHelper->createUserMock('ROLE_PROF', false, false, 10);
        $profTwo = $userTestHelper->createUserMock('ROLE_PROF', false, false, 11);

        $profNamed = $userTestHelper->createUserMock('ROLE_PROF_NAMED', false, false, 12);
        $profNamedTwo = $userTestHelper->createUserMock('ROLE_PROF_NAMED', false, false, 13);

        $profAdmin = $userTestHelper->createUserMock('ROLE_PROF_ADMIN', false, false, 14);
        $profAdminTwo = $userTestHelper->createUserMock('ROLE_PROF_ADMIN', false, false, 15);

        $admin = $userTestHelper->createUserMock('ROLE_ADMIN', false, false, 16);
        $adminTwo = $userTestHelper->createUserMock('ROLE_ADMIN', false, false, 17);

        $superAdmin = $userTestHelper->createUserMock('ROLE_SUPER_ADMIN', false, false, 18);
        $superAdminTwo = $userTestHelper->createUserMock('ROLE_SUPER_ADMIN', false, false, 19);

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
