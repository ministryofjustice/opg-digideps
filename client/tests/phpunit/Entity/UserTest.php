<?php declare(strict_types=1);


use AppBundle\Entity\Client;
use AppBundle\Entity\Report\Report;
use AppBundle\Entity\User;
use PHPUnit\Framework\TestCase;

class UserTest extends TestCase
{

    /**
     * @test
     * @dataProvider deleteUserProvider
     */
    public function canDeleteUser(User $deletor, User $deletee, bool $expectedPermission)
    {
        self::assertEquals($expectedPermission, $deletor->canDeleteUser($deletee));
    }

    public function deleteUserProvider()
    {
        $clientNoReports = new Client();
        $clientWithReport = (new Client())->setReports([new Report()]);

        $admin = (new User)->setRoleName('ROLE_ADMIN')->setId(1);
        $adminTwo = (new User)->setRoleName('ROLE_ADMIN')->setId(2);

        $superAdmin = (new User)->setRoleName('ROLE_SUPER_ADMIN')->setId(3);
        $superAdminTwo = (new User)->setRoleName('ROLE_SUPER_ADMIN')->setId(4);

        $layNoReportsOrClients = (new User)->setRoleName('ROLE_LAY_DEPUTY');
        $layNoReportsOneClient = (new User)->setRoleName('ROLE_LAY_DEPUTY')->setClients([$clientNoReports]);
        $layReportOneClient = (new User)->setRoleName('ROLE_LAY_DEPUTY')->setClients([$clientWithReport]);

        $pa = (new User)->setRoleName('ROLE_PA');
        $prof = (new User)->setRoleName('ROLE_PROF');

        return [
            'Admin deletes Lay Deputy with no reports or clients' => [$admin, $layNoReportsOrClients, true],
            'Admin deletes Lay Deputy with no reports, one client' => [$admin, $layNoReportsOneClient, true],
            'Admin deletes Lay Deputy with one reports and client' => [$admin, $layReportOneClient, false],
            'Admin deletes PA Deputy' => [$admin, $pa, false],
            'Admin deletes Prof Deputy' => [$admin, $prof, false],
            'Admin deletes Admin' => [$admin, $adminTwo, false],
            'Admin deletes self' => [$admin, $admin, false],
            'Super Admin deletes Lay Deputy with no reports or clients' => [$superAdmin, $layNoReportsOrClients, true],
            'Super Admin deletes Lay Deputy with no reports, one client' => [$superAdmin, $layNoReportsOneClient, true],
            'Super Admin deletes Lay Deputy with one reports and client' => [$superAdmin, $layReportOneClient, false],
            'Super Admin deletes PA Deputy' => [$superAdmin, $pa, false],
            'Super Admin deletes Prof Deputy' => [$superAdmin, $prof, false],
            'Super Admin deletes Admin' => [$superAdmin, $admin, true],
            'Super Admin deletes Super Admin' => [$superAdmin, $superAdminTwo, true],
            'Super Admin deletes self' => [$superAdmin, $superAdmin, false],
        ];
    }
}
