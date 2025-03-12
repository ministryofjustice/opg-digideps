<?php

declare(strict_types=1);

namespace App\Security;

use App\Entity\Client;
use App\Entity\Report\Report;
use App\Entity\User;
use App\TestHelpers\UserHelpers;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\Authorization\AccessDecisionManagerInterface;

class UserVoterTest extends TestCase
{
    use ProphecyTrait;

    /**
     * @dataProvider deleteUserProvider
     *
     * @test
     */
    public function determineDeletePermission(User $deletor, User $deletee, int $expectedPermission)
    {
        /** @var AccessDecisionManagerInterface&ObjectProphecy $dm */
        $dm = self::prophesize(AccessDecisionManagerInterface::class);

        /** @var UserVoter $sut */
        $sut = new UserVoter($dm->reveal());

        $token = new UsernamePasswordToken($deletor, 'firewall', $deletor->getRoles());

        self::assertEquals($expectedPermission, $sut->vote($token, $deletee, [UserVoter::DELETE_USER]));
    }

    public function deleteUserProvider()
    {
        $clientNoReports = new Client();
        $clientWithReport = (new Client())->setReports([new Report()]);

        $layNoReportsOrClients = (new User())->setRoleName('ROLE_LAY_DEPUTY')->setId(1);
        $layNoReportsOneClient = (new User())->setRoleName('ROLE_LAY_DEPUTY')->setClients([$clientNoReports])->setId(2);
        $layReportOneClient = (new User())->setRoleName('ROLE_LAY_DEPUTY')->setClients([$clientWithReport])->setId(3);

        $pa = (new User())->setRoleName('ROLE_PA')->setId(4);
        $paTwo = (new User())->setRoleName('ROLE_PA')->setId(5);

        $paTeamMember = (new User())->setRoleName('ROLE_PA_TEAM_MEMBER')->setId(6);
        $paTeamMemberTwo = (new User())->setRoleName('ROLE_PA_TEAM_MEMBER')->setId(7);

        $paNamed = (new User())->setRoleName('ROLE_PA_NAMED')->setId(8);
        $paNamedTwo = (new User())->setRoleName('ROLE_PA_NAMED')->setId(9);

        $paAdmin = (new User())->setRoleName('ROLE_PA_ADMIN')->setId(10);
        $paAdminTwo = (new User())->setRoleName('ROLE_PA_ADMIN')->setId(11);

        $prof = (new User())->setRoleName('ROLE_PROF')->setId(12);
        $profTwo = (new User())->setRoleName('ROLE_PROF')->setId(13);

        $profTeamMember = (new User())->setRoleName('ROLE_PROF_TEAM_MEMBER')->setId(14);
        $profTeamMemberTwo = (new User())->setRoleName('ROLE_PROF_TEAM_MEMBER')->setId(15);

        $profNamed = (new User())->setRoleName('ROLE_PROF_NAMED')->setId(16);
        $profNamedTwo = (new User())->setRoleName('ROLE_PROF_NAMED')->setId(17);

        $profAdmin = (new User())->setRoleName('ROLE_PROF_ADMIN')->setId(18);
        $profAdminTwo = (new User())->setRoleName('ROLE_PROF_ADMIN')->setId(19);

        $admin = (new User())->setRoleName('ROLE_ADMIN')->setId(20);
        $adminTwo = (new User())->setRoleName('ROLE_ADMIN')->setId(21);

        $superAdmin = (new User())->setRoleName('ROLE_SUPER_ADMIN')->setId(22);
        $superAdminTwo = (new User())->setRoleName('ROLE_SUPER_ADMIN')->setId(23);

        return [
            'Lay Deputy deletes Lay Deputy' => [$layNoReportsOrClients, $layNoReportsOneClient, -1],
            'Lay Deputy deletes PA Deputy' => [$layNoReportsOrClients, $pa, -1],
            'Lay Deputy deletes PA Team Member' => [$layNoReportsOrClients, $paTeamMember, -1],
            'Lay Deputy deletes PA Named Deputy' => [$layNoReportsOrClients, $paNamed, -1],
            'Lay Deputy deletes PA Admin Deputy' => [$layNoReportsOrClients, $paAdmin, -1],
            'Lay Deputy deletes Prof Deputy' => [$layNoReportsOrClients, $prof, -1],
            'Lay Deputy deletes Prof Team Member' => [$layNoReportsOrClients, $profTeamMember, -1],
            'Lay Deputy deletes Prof Named Deputy' => [$layNoReportsOrClients, $profNamed, -1],
            'Lay Deputy deletes Prof Admin Deputy' => [$layNoReportsOrClients, $profAdmin, -1],
            'Lay Deputy deletes Admin user' => [$layNoReportsOrClients, $admin, -1],
            'Lay Deputy deletes Super Admin user' => [$layNoReportsOrClients, $superAdmin, -1],
            'Lay Deputy deletes self' => [$layNoReportsOrClients, $layNoReportsOrClients, -1],

            'PA Deputy deletes Lay Deputy' => [$pa, $layNoReportsOneClient, -1],
            'PA Deputy deletes PA Deputy' => [$pa, $paTwo, -1],
            'PA Deputy deletes PA Team Member' => [$pa, $paTeamMember, -1],
            'PA Deputy deletes PA Named Deputy' => [$pa, $paNamed, -1],
            'PA Deputy deletes PA Admin Deputy' => [$pa, $paAdmin, -1],
            'PA Deputy deletes Prof Deputy' => [$pa, $prof, -1],
            'PA Deputy deletes Prof Team Member' => [$pa, $profTeamMember, -1],
            'PA Deputy deletes Prof Named Deputy' => [$pa, $profNamed, -1],
            'PA Deputy deletes Prof Admin Deputy' => [$pa, $profAdmin, -1],
            'PA Deputy deletes Admin user' => [$pa, $admin, -1],
            'PA Deputy deletes Super Admin user' => [$pa, $superAdmin, -1],
            'PA Deputy deletes self' => [$pa, $pa, -1],

            'PA Team Member deletes Lay Deputy' => [$paTeamMember, $layNoReportsOneClient, -1],
            'PA Team Member deletes PA Deputy' => [$paTeamMember, $pa, -1],
            'PA Team Member deletes PA Team Member' => [$paTeamMember, $paTeamMemberTwo, -1],
            'PA Team Member deletes PA Named Deputy' => [$paTeamMember, $paNamed, -1],
            'PA Team Member deletes PA Admin Deputy' => [$paTeamMember, $paAdmin, -1],
            'PA Team Member deletes Prof Deputy' => [$paTeamMember, $prof, -1],
            'PA Team Member deletes Prof Team Member' => [$paTeamMember, $profTeamMember, -1],
            'PA Team Member deletes Prof Named Deputy' => [$paTeamMember, $profNamed, -1],
            'PA Team Member deletes Prof Admin Deputy' => [$paTeamMember, $profAdmin, -1],
            'PA Team Member deletes Admin user' => [$paTeamMember, $admin, -1],
            'PA Team Member deletes Super Admin user' => [$paTeamMember, $superAdmin, -1],
            'PA Team Member deletes self' => [$paTeamMember, $paTeamMember, -1],

            'PA Named Deputy deletes Lay Deputy' => [$paNamed, $layNoReportsOneClient, -1],
            'PA Named Deputy deletes PA Deputy' => [$paNamed, $pa, 1],
            'PA Named Deputy deletes PA Team Member' => [$paNamed, $paTeamMember, 1],
            'PA Named Deputy deletes PA Named Deputy' => [$paNamed, $paNamedTwo, 1],
            'PA Named Deputy deletes PA Admin Deputy' => [$paNamed, $paAdmin, 1],
            'PA Named Deputy deletes Prof Deputy' => [$paNamed, $prof, 1],
            'PA Named Deputy deletes Prof Team Member' => [$paNamed, $profTeamMember, 1],
            'PA Named Deputy deletes Prof Named Deputy' => [$paNamed, $profNamed, 1],
            'PA Named Deputy deletes Prof Admin Deputy' => [$paNamed, $profAdmin, 1],
            'PA Named Deputy deletes Admin user' => [$paNamed, $admin, -1],
            'PA Named Deputy deletes Super Admin user' => [$paNamed, $superAdmin, -1],
            'PA Named Deputy deletes self' => [$paNamed, $paNamed, -1],

            'PA Admin Deputy deletes Lay Deputy' => [$paAdmin, $layNoReportsOneClient, -1],
            'PA Admin Deputy deletes PA Deputy' => [$paAdmin, $pa, 1],
            'PA Admin Deputy deletes PA Team Member' => [$paAdmin, $paTeamMember, 1],
            'PA Admin Deputy deletes PA Named Deputy' => [$paAdmin, $paNamed, 1],
            'PA Admin Deputy deletes PA Admin Deputy' => [$paAdmin, $paAdminTwo, 1],
            'PA Admin Deputy deletes Prof Deputy' => [$paAdmin, $prof, 1],
            'PA Admin Deputy deletes Prof Team Member' => [$paAdmin, $profTeamMember, 1],
            'PA Admin Deputy deletes Prof Named Deputy' => [$paAdmin, $profNamed, 1],
            'PA Admin Deputy deletes Prof Admin Deputy' => [$paAdmin, $profAdmin, 1],
            'PA Admin Deputy deletes Admin user' => [$paAdmin, $admin, -1],
            'PA Admin Deputy deletes Super Admin user' => [$paAdmin, $superAdmin, -1],
            'PA Admin Deputy deletes self' => [$paAdmin, $paAdmin, -1],

            'Prof Deputy deletes Lay Deputy' => [$prof, $layNoReportsOneClient, -1],
            'Prof Deputy deletes PA Deputy' => [$prof, $pa, -1],
            'Prof Deputy deletes PA Team Member' => [$prof, $paTeamMember, -1],
            'Prof Deputy deletes PA Named Deputy' => [$prof, $paNamed, -1],
            'Prof Deputy deletes PA Admin Deputy' => [$prof, $paAdmin, -1],
            'Prof Deputy deletes Prof Deputy' => [$prof, $profTwo, -1],
            'Prof Deputy deletes Prof Team Member' => [$prof, $profTeamMember, -1],
            'Prof Deputy deletes Prof Named Deputy' => [$prof, $profNamed, -1],
            'Prof Deputy deletes Prof Admin Deputy' => [$prof, $profAdmin, -1],
            'Prof Deputy deletes Admin user' => [$prof, $admin, -1],
            'Prof Deputy deletes Super Admin user' => [$prof, $superAdmin, -1],
            'Prof Deputy deletes self' => [$prof, $prof, -1],

            'Prof Team Member deletes Lay Deputy' => [$profTeamMember, $layNoReportsOneClient, -1],
            'Prof Team Member deletes PA Deputy' => [$profTeamMember, $pa, -1],
            'Prof Team Member deletes PA Team Member' => [$profTeamMember, $paTeamMember, -1],
            'Prof Team Member deletes PA Named Deputy' => [$profTeamMember, $paNamed, -1],
            'Prof Team Member deletes PA Admin Deputy' => [$profTeamMember, $paAdmin, -1],
            'Prof Team Member deletes Prof Deputy' => [$profTeamMember, $prof, -1],
            'Prof Team Member deletes Prof Team Member' => [$profTeamMember, $profTeamMemberTwo, -1],
            'Prof Team Member deletes Prof Named Deputy' => [$profTeamMember, $profNamed, -1],
            'Prof Team Member deletes Prof Admin Deputy' => [$profTeamMember, $profAdmin, -1],
            'Prof Team Member deletes Admin user' => [$profTeamMember, $admin, -1],
            'Prof Team Member deletes Super Admin user' => [$profTeamMember, $superAdmin, -1],
            'Prof Team Member deletes self' => [$profTeamMember, $profTeamMember, -1],

            'Prof Named Deputy deletes Lay Deputy' => [$profNamed, $layNoReportsOneClient, -1],
            'Prof Named Deputy deletes PA Deputy' => [$profNamed, $pa, 1],
            'Prof Named Deputy deletes PA Team Member' => [$profNamed, $paTeamMember, 1],
            'Prof Named Deputy deletes PA Named Deputy' => [$profNamed, $paNamedTwo, 1],
            'Prof Named Deputy deletes PA Admin Deputy' => [$profNamed, $paAdmin, 1],
            'Prof Named Deputy deletes Prof Deputy' => [$profNamed, $prof, 1],
            'Prof Named Deputy deletes Prof Team Member' => [$profNamed, $profTeamMember, 1],
            'Prof Named Deputy deletes Prof Named Deputy' => [$profNamed, $profNamedTwo, 1],
            'Prof Named Deputy deletes Prof Admin Deputy' => [$profNamed, $profAdmin, 1],
            'Prof Named Deputy deletes Admin user' => [$profNamed, $admin, -1],
            'Prof Named Deputy deletes Super Admin user' => [$profNamed, $superAdmin, -1],
            'Prof Named Deputy deletes self' => [$profNamed, $profNamed, -1],

            'Prof Admin Deputy deletes Lay Deputy' => [$profAdmin, $layNoReportsOneClient, -1],
            'Prof Admin Deputy deletes PA Deputy' => [$profAdmin, $pa, 1],
            'Prof Admin Deputy deletes PA Team Member' => [$profAdmin, $paTeamMember, 1],
            'Prof Admin Deputy deletes PA Named Deputy' => [$profAdmin, $paNamedTwo, 1],
            'Prof Admin Deputy deletes PA Admin Deputy' => [$profAdmin, $paAdmin, 1],
            'Prof Admin Deputy deletes Prof Deputy' => [$profAdmin, $prof, 1],
            'Prof Admin Deputy deletes Prof Team Member' => [$profAdmin, $profTeamMember, 1],
            'Prof Admin Deputy deletes Prof Named Deputy' => [$profAdmin, $profNamed, 1],
            'Prof Admin Deputy deletes Prof Admin Deputy' => [$profAdmin, $profAdminTwo, 1],
            'Prof Admin Deputy deletes Admin user' => [$profAdmin, $admin, -1],
            'Prof Admin Deputy deletes Super Admin user' => [$profAdmin, $superAdmin, -1],
            'Prof Admin Deputy deletes self' => [$profAdmin, $profAdmin, -1],

            'Admin deletes Lay Deputy with no reports or clients' => [$admin, $layNoReportsOrClients, -1],
            'Admin deletes Lay Deputy with no reports, one client' => [$admin, $layNoReportsOneClient, -1],
            'Admin deletes Lay Deputy with one report and client' => [$admin, $layReportOneClient, -1],
            'Admin deletes PA Deputy' => [$admin, $pa, -1],
            'Admin deletes PA Team Member' => [$admin, $paTeamMember, -1],
            'Admin deletes PA Named Deputy' => [$admin, $paNamedTwo, -1],
            'Admin deletes PA Admin Deputy' => [$admin, $paAdmin, -1],
            'Admin deletes Prof Deputy' => [$admin, $prof, -1],
            'Admin deletes Prof Team Member' => [$admin, $profTeamMember, -1],
            'Admin deletes Prof Named Deputy' => [$admin, $profNamed, -1],
            'Admin deletes Prof Admin Deputy' => [$admin, $profAdmin, -1],
            'Admin deletes Admin user' => [$admin, $adminTwo, -1],
            'Admin deletes Super Admin user' => [$admin, $superAdmin, -1],
            'Admin deletes self' => [$admin, $admin, -1],

            'Super Admin deletes Lay Deputy with no reports or clients' => [$superAdmin, $layNoReportsOrClients, 1],
            'Super Admin deletes Lay Deputy with no reports, one client' => [$superAdmin, $layNoReportsOneClient, 1],
            'Super Admin deletes Lay Deputy with one report and client' => [$superAdmin, $layReportOneClient, 1],
            'Super Admin deletes PA Deputy' => [$superAdmin, $pa, 1],
            'Super Admin deletes PA Team Member' => [$superAdmin, $paTeamMember, 1],
            'Super Admin deletes PA Named Deputy' => [$superAdmin, $paNamedTwo, 1],
            'Super Admin deletes PA Admin Deputy' => [$superAdmin, $paAdmin, 1],
            'Super Admin deletes Prof Deputy' => [$superAdmin, $prof, 1],
            'Super Admin deletes Prof Team Member' => [$superAdmin, $profTeamMember, 1],
            'Super Admin deletes Prof Named Deputy' => [$superAdmin, $profNamed, 1],
            'Super Admin deletes Prof Admin Deputy' => [$superAdmin, $profAdmin, 1],
            'Super Admin deletes Admin user' => [$superAdmin, $admin, 1],
            'Super Admin deletes Super Admin user' => [$superAdmin, $superAdminTwo, 1],
            'Super Admin deletes self' => [$superAdmin, $superAdmin, -1],
        ];
    }

    /**
     * @dataProvider addEditUserProvider
     *
     * @test
     */
    public function determineAddEditPermission(User $actor, User $subject, int $expectedPermission)
    {
        /** @var UserVoter $sut */
        $sut = new UserVoter();

        $token = new UsernamePasswordToken($actor, 'firewall', $actor->getRoles());

        self::assertEquals($expectedPermission, $sut->vote($token, $subject, [UserVoter::EDIT_USER]));
        self::assertEquals($expectedPermission, $sut->vote($token, $subject, [UserVoter::ADD_USER]));
    }

    public function addEditUserProvider()
    {
        $admin = UserHelpers::createAdminUser();
        $admin2 = UserHelpers::createAdminUser();
        $superAdmin = UserHelpers::createSuperAdminUser();
        $superAdmin2 = UserHelpers::createSuperAdminUser();
        $adminManager = UserHelpers::createAdminManager();
        $adminManager2 = UserHelpers::createAdminManager();

        $lay = UserHelpers::createLayUser();
        $lay2 = UserHelpers::createLayUser();

        $pa = UserHelpers::createPaDeputyUser();
        $pa2 = UserHelpers::createPaDeputyUser();
        $paNamed = UserHelpers::createPaNamedDeputyUser();
        $paNamed2 = UserHelpers::createPaNamedDeputyUser();
        $paAdmin = UserHelpers::createPaAdminUser();
        $paAdmin2 = UserHelpers::createPaAdminUser();
        $paTeamMember = UserHelpers::createPaTeamMemberUser();
        $paTeamMember2 = UserHelpers::createPaTeamMemberUser();

        $prof = UserHelpers::createProfDeputyUser();
        $prof2 = UserHelpers::createProfDeputyUser();
        $profNamed = UserHelpers::createProfNamedDeputyUser();
        $profNamed2 = UserHelpers::createProfNamedDeputyUser();
        $profAdmin = UserHelpers::createProfAdminUser();
        $profAdmin2 = UserHelpers::createProfAdminUser();
        $profTeamMember = UserHelpers::createProfTeamMemberUser();
        $profTeamMember2 = UserHelpers::createProfTeamMemberUser();

        return [
            'Super Admin adds/edits Lay Deputy' => [$superAdmin, $lay, 1],
            'Super Admin adds/edits PA Deputy' => [$superAdmin, $pa, 1],
            'Super Admin adds/edits PA Named Deputy' => [$superAdmin, $paNamed, 1],
            'Super Admin adds/edits PA Admin Deputy' => [$superAdmin, $paAdmin, 1],
            'Super Admin adds/edits PA Team Member' => [$superAdmin, $paTeamMember, 1],
            'Super Admin adds/edits Prof Deputy' => [$superAdmin, $prof, 1],
            'Super Admin adds/edits Prof Named Deputy' => [$superAdmin, $profNamed, 1],
            'Super Admin adds/edits Prof Admin Deputy' => [$superAdmin, $profAdmin, 1],
            'Super Admin adds/edits Prof Team Member' => [$superAdmin, $profTeamMember, 1],
            'Super Admin adds/edits Admin user' => [$superAdmin, $admin, 1],
            'Super Admin adds/edits Super Admin user' => [$superAdmin, $superAdmin2, 1],
            'Super Admin adds/edits Admin Manager user' => [$superAdmin, $adminManager, 1],
            'Super Admin adds/edits self' => [$superAdmin, $superAdmin, 1],

            'Admin Manager adds/edits Lay Deputy' => [$adminManager, $lay, 1],
            'Admin Manager adds/edits PA Deputy' => [$adminManager, $pa, 1],
            'Admin Manager adds/edits PA Named Deputy' => [$adminManager, $paNamed, 1],
            'Admin Manager adds/edits PA Admin Deputy' => [$adminManager, $paAdmin, 1],
            'Admin Manager adds/edits PA Team Member' => [$adminManager, $paTeamMember, 1],
            'Admin Manager adds/edits Prof Deputy' => [$adminManager, $prof, 1],
            'Admin Manager adds/edits Prof Named Deputy' => [$adminManager, $profNamed, 1],
            'Admin Manager adds/edits Prof Admin Deputy' => [$adminManager, $profAdmin, 1],
            'Admin Manager adds/edits Admin user' => [$adminManager, $admin, 1],
            'Admin Manager adds/edits Super Admin user' => [$adminManager, $superAdmin, -1],
            'Admin Manager adds/edits Prof Team Member' => [$adminManager, $profTeamMember, 1],
            'Admin Manager adds/edits Admin Manager user' => [$adminManager, $adminManager2, -1],
            'Admin Manager adds/edits self' => [$adminManager, $adminManager, 1],

            'Admin adds/edits Lay Deputy' => [$admin, $lay, 1],
            'Admin adds/edits PA Deputy' => [$admin, $pa, 1],
            'Admin adds/edits PA Named Deputy' => [$admin, $paNamed, 1],
            'Admin adds/edits PA Admin Deputy' => [$admin, $paAdmin, 1],
            'Admin adds/edits PA Team Member' => [$admin, $paTeamMember, 1],
            'Admin adds/edits Prof Deputy' => [$admin, $prof, 1],
            'Admin adds/edits Prof Named Deputy' => [$admin, $profNamed, 1],
            'Admin adds/edits Prof Admin Deputy' => [$admin, $profAdmin, 1],
            'Admin adds/edits Prof Team Member' => [$admin, $profTeamMember, 1],
            'Admin adds/edits Admin user' => [$admin, $admin2, 1],
            'Admin adds/edits Super Admin user' => [$admin, $superAdmin, -1],
            'Admin adds/edits Admin Manager user' => [$admin, $adminManager, -1],
            'Admin adds/edits self' => [$admin, $admin, 1],

            'Lay adds/edits Lay Deputy' => [$lay, $lay2, -1],
            'Lay adds/edits PA Deputy' => [$lay, $pa, -1],
            'Lay adds/edits PA Named Deputy' => [$lay, $paNamed, -1],
            'Lay adds/edits PA Admin Deputy' => [$lay, $paAdmin, -1],
            'Lay adds/edits PA Team Member' => [$lay, $paTeamMember, -1],
            'Lay adds/edits Prof Deputy' => [$lay, $prof, -1],
            'Lay adds/edits Prof Named Deputy' => [$lay, $profNamed, -1],
            'Lay adds/edits Prof Admin Deputy' => [$lay, $profAdmin, -1],
            'Lay adds/edits Prof Team Member' => [$lay, $profTeamMember, -1],
            'Lay adds/edits Admin user' => [$lay, $admin, -1],
            'Lay adds/edits Super Admin user' => [$lay, $superAdmin, -1],
            'Lay adds/edits Admin Manager user' => [$lay, $adminManager, -1],
            'Lay adds/edits self' => [$lay, $lay, 1],

            'PA Deputy adds/edits Lay Deputy' => [$pa, $lay, -1],
            'PA Deputy adds/edits PA Deputy' => [$pa, $pa2, 1],
            'PA Deputy adds/edits PA Named Deputy' => [$pa, $paNamed, 1],
            'PA Deputy adds/edits PA Admin Deputy' => [$pa, $paAdmin, 1],
            'PA Deputy adds/edits PA Team Member' => [$pa, $paTeamMember, 1],
            'PA Deputy adds/edits Prof Deputy' => [$pa, $prof, -1],
            'PA Deputy adds/edits Prof Named Deputy' => [$pa, $profNamed, -1],
            'PA Deputy adds/edits Prof Admin Deputy' => [$pa, $profAdmin, -1],
            'PA Deputy adds/edits Prof Team Member' => [$pa, $profTeamMember, -1],
            'PA Deputy adds/edits Admin user' => [$pa, $admin, -1],
            'PA Deputy adds/edits Super Admin user' => [$pa, $superAdmin, -1],
            'PA Deputy adds/edits Admin Manager user' => [$pa, $adminManager, -1],
            'PA Deputy adds/edits self' => [$pa, $pa, 1],

            'PA Named Deputy adds/edits Lay Deputy' => [$paNamed, $lay, -1],
            'PA Named Deputy adds/edits PA Deputy' => [$paNamed, $pa, 1],
            'PA Named Deputy adds/edits PA Named Deputy' => [$paNamed, $paNamed2, 1],
            'PA Named Deputy adds/edits PA Admin Deputy' => [$paNamed, $paAdmin, 1],
            'PA Named Deputy adds/edits PA Team Member' => [$paNamed, $paTeamMember, 1],
            'PA Named Deputy adds/edits Prof Deputy' => [$paNamed, $prof, -1],
            'PA Named Deputy adds/edits Prof Named Deputy' => [$paNamed, $profNamed, -1],
            'PA Named Deputy adds/edits Prof Admin Deputy' => [$paNamed, $profAdmin, -1],
            'PA Named Deputy adds/edits Prof Team Member' => [$paNamed, $profTeamMember, -1],
            'PA Named Deputy adds/edits Admin user' => [$paNamed, $admin, -1],
            'PA Named Deputy adds/edits Super Admin user' => [$paNamed, $superAdmin, -1],
            'PA Named Deputy adds/edits Admin Manager user' => [$paNamed, $adminManager, -1],
            'PA Named Deputy adds/edits self' => [$paNamed, $paNamed, 1],

            'PA Admin adds/edits Lay Deputy' => [$paAdmin, $lay, -1],
            'PA Admin adds/edits PA Deputy' => [$paAdmin, $pa, -1],
            'PA Admin adds/edits PA Named Deputy' => [$paAdmin, $paNamed, -1],
            'PA Admin adds/edits PA Admin Deputy' => [$paAdmin, $paAdmin2, 1],
            'PA Admin adds/edits PA Team Member' => [$paAdmin, $paTeamMember, 1],
            'PA Admin adds/edits Prof Deputy' => [$paAdmin, $prof, -1],
            'PA Admin adds/edits Prof Named Deputy' => [$paAdmin, $profNamed, -1],
            'PA Admin adds/edits Prof Admin Deputy' => [$paAdmin, $profAdmin, -1],
            'PA Admin adds/edits Prof Team Member' => [$paAdmin, $profTeamMember, -1],
            'PA Admin adds/edits Admin user' => [$paAdmin, $admin, -1],
            'PA Admin adds/edits Super Admin user' => [$paAdmin, $superAdmin, -1],
            'PA Admin adds/edits Admin Manager user' => [$paAdmin, $adminManager, -1],
            'PA Admin adds/edits self' => [$paAdmin, $paAdmin, 1],

            'PA Team Member adds/edits Lay Deputy' => [$paTeamMember, $lay, -1],
            'PA Team Member adds/edits PA Deputy' => [$paTeamMember, $pa, -1],
            'PA Team Member adds/edits PA Named Deputy' => [$paTeamMember, $paNamed, -1],
            'PA Team Member adds/edits PA Admin Deputy' => [$paTeamMember, $paAdmin, -1],
            'PA Team Member adds/edits PA Team Member' => [$paTeamMember, $paTeamMember2, -1],
            'PA Team Member adds/edits Prof Deputy' => [$paTeamMember, $prof, -1],
            'PA Team Member adds/edits Prof Named Deputy' => [$paTeamMember, $profNamed, -1],
            'PA Team Member adds/edits Prof Admin Deputy' => [$paTeamMember, $profAdmin, -1],
            'PA Team Member adds/edits Prof Team Member' => [$paTeamMember, $profTeamMember, -1],
            'PA Team Member adds/edits Admin user' => [$paTeamMember, $admin, -1],
            'PA Team Member adds/edits Super Admin user' => [$paTeamMember, $superAdmin, -1],
            'PA Team Member adds/edits Admin Manager user' => [$paTeamMember, $adminManager, -1],
            'PA Team Member adds/edits self' => [$paTeamMember, $paTeamMember, 1],

            'Prof Deputy adds/edits Lay Deputy' => [$prof, $lay, -1],
            'Prof Deputy adds/edits PA Deputy' => [$prof, $pa, -1],
            'Prof Deputy adds/edits PA Named Deputy' => [$prof, $paNamed, -1],
            'Prof Deputy adds/edits PA Admin Deputy' => [$prof, $paAdmin, -1],
            'Prof Deputy adds/edits PA Team Member' => [$prof, $paTeamMember, -1],
            'Prof Deputy adds/edits Prof Deputy' => [$prof, $prof2, 1],
            'Prof Deputy adds/edits Prof Named Deputy' => [$prof, $profNamed, 1],
            'Prof Deputy adds/edits Prof Admin Deputy' => [$prof, $profAdmin, 1],
            'Prof Deputy adds/edits Prof Team Member' => [$prof, $profTeamMember, 1],
            'Prof Deputy adds/edits Admin user' => [$prof, $admin, -1],
            'Prof Deputy adds/edits Super Admin user' => [$prof, $superAdmin, -1],
            'Prof Deputy adds/edits Admin Manager user' => [$prof, $adminManager, -1],
            'Prof Deputy adds/edits self' => [$prof, $prof, 1],

            'Prof Named Deputy adds/edits Lay Deputy' => [$profNamed, $lay, -1],
            'Prof Named Deputy adds/edits PA Deputy' => [$profNamed, $pa, -1],
            'Prof Named Deputy adds/edits PA Named Deputy' => [$profNamed, $paNamed2, -1],
            'Prof Named Deputy adds/edits PA Admin Deputy' => [$profNamed, $paAdmin, -1],
            'Prof Named Deputy adds/edits PA Team Member' => [$profNamed, $paTeamMember, -1],
            'Prof Named Deputy adds/edits Prof Deputy' => [$profNamed, $prof, 1],
            'Prof Named Deputy adds/edits Prof Named Deputy' => [$profNamed, $profNamed2, 1],
            'Prof Named Deputy adds/edits Prof Admin Deputy' => [$profNamed, $profAdmin, 1],
            'Prof Named Deputy adds/edits Prof Team Member' => [$profNamed, $profTeamMember, 1],
            'Prof Named Deputy adds/edits Admin user' => [$profNamed, $admin, -1],
            'Prof Named Deputy adds/edits Super Admin user' => [$profNamed, $superAdmin, -1],
            'Prof Named Deputy adds/edits Admin Manager user' => [$profNamed, $adminManager, -1],
            'Prof Named Deputy adds/edits self' => [$profNamed, $profNamed, 1],

            'Prof Admin adds/edits Lay Deputy' => [$profAdmin, $lay, -1],
            'Prof Admin adds/edits PA Deputy' => [$profAdmin, $pa, -1],
            'Prof Admin adds/edits PA Named Deputy' => [$profAdmin, $paNamed, -1],
            'Prof Admin adds/edits PA Admin Deputy' => [$profAdmin, $paAdmin2, -1],
            'Prof Admin adds/edits PA Team Member' => [$profAdmin, $paTeamMember, -1],
            'Prof Admin adds/edits Prof Deputy' => [$profAdmin, $prof, -1],
            'Prof Admin adds/edits Prof Named Deputy' => [$profAdmin, $profNamed, -1],
            'Prof Admin adds/edits Prof Admin Deputy' => [$profAdmin, $profAdmin2, 1],
            'Prof Admin adds/edits Prof Team Member' => [$profAdmin, $profTeamMember, 1],
            'Prof Admin adds/edits Admin user' => [$profAdmin, $admin, -1],
            'Prof Admin adds/edits Super Admin user' => [$profAdmin, $superAdmin, -1],
            'Prof Admin adds/edits Admin Manager user' => [$profAdmin, $adminManager, -1],
            'Prof Admin adds/edits self' => [$profAdmin, $profAdmin, 1],

            'Prof Team Member adds/edits Lay Deputy' => [$profTeamMember, $lay, -1],
            'Prof Team Member adds/edits PA Deputy' => [$profTeamMember, $pa, -1],
            'Prof Team Member adds/edits PA Named Deputy' => [$profTeamMember, $paNamed, -1],
            'Prof Team Member adds/edits PA Admin Deputy' => [$profTeamMember, $paAdmin, -1],
            'Prof Team Member adds/edits PA Team Member' => [$profTeamMember, $paTeamMember2, -1],
            'Prof Team Member adds/edits Prof Deputy' => [$profTeamMember, $prof, -1],
            'Prof Team Member adds/edits Prof Named Deputy' => [$profTeamMember, $profNamed, -1],
            'Prof Team Member adds/edits Prof Admin Deputy' => [$profTeamMember, $profAdmin, -1],
            'Prof Team Member adds/edits Prof Team Member' => [$profTeamMember, $profTeamMember2, -1],
            'Prof Team Member adds/edits Admin user' => [$profTeamMember, $admin, -1],
            'Prof Team Member adds/edits Super Admin user' => [$profTeamMember, $superAdmin, -1],
            'Prof Team Member adds/edits Admin Manager user' => [$profTeamMember, $adminManager, -1],
            'Prof Team Member adds/edits self' => [$profTeamMember, $profTeamMember, 1],
        ];
    }

    /**
     * @test
     *
     * @dataProvider canAddUserProvider
     */
    public function determineCanAddPermission(User $actor, int $expectedPermission)
    {
        /** @var UserVoter $sut */
        $sut = new UserVoter();

        $token = new UsernamePasswordToken($actor, 'firewall', $actor->getRoles());

        self::assertEquals($expectedPermission, $sut->vote($token, null, [UserVoter::CAN_ADD_USER]));
    }

    public function canAddUserProvider()
    {
        $admin = UserHelpers::createAdminUser();
        $superAdmin = UserHelpers::createSuperAdminUser();
        $adminManager = UserHelpers::createAdminManager();

        $lay = UserHelpers::createLayUser();

        $pa = UserHelpers::createPaDeputyUser();
        $paNamed = UserHelpers::createPaNamedDeputyUser();
        $paAdmin = UserHelpers::createPaAdminUser();
        $paTeamMember = UserHelpers::createPaTeamMemberUser();

        $prof = UserHelpers::createProfDeputyUser();
        $profNamed = UserHelpers::createProfNamedDeputyUser();
        $profAdmin = UserHelpers::createProfAdminUser();
        $profTeamMember = UserHelpers::createProfTeamMemberUser();

        return [
            'Super Admin checks if they can add a user' => [$superAdmin, 1],
            'Admin manager checks if they can add a user' => [$adminManager, 1],
            'Admin checks if they can add a user' => [$admin, 1],
            'Lay checks if they can add a user' => [$lay, -1],
            'PA Deputy checks if they can add a user' => [$pa, 1],
            'PA Named Deputy checks if they can add a user' => [$paNamed, 1],
            'PA Admin checks if they can add a user' => [$paAdmin, 1],
            'PA Team Member checks if they can add a user' => [$paTeamMember, -1],
            'Prof Deputy checks if they can add a user' => [$prof, 1],
            'Prof Named Deputy checks if they can add a user' => [$profNamed, 1],
            'Prof Admin checks if they can add a user' => [$profAdmin, 1],
            'Prof Team Member checks if they can add a user' => [$profTeamMember, -1],
        ];
    }
}
