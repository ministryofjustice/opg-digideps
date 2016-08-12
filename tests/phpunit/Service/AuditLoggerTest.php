<?php

namespace AppBundle\Service;

use Mockery as m;
use AppBundle\Entity as EntityDir;

class AuditLoggerTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->restClient = m::mock('AppBundle\Service\Client\RestClient');
        $this->security = m::mock('Symfony\Component\Security\Core\SecurityContextInterface');
        $container = m::mock('Symfony\Component\DependencyInjection\Container');

        $this->request = m::mock('Symfony\Component\HttpFoundation\Request');
        $this->user = m::mock('AppBundle\Entity\User');

        $container->shouldReceive('get')->with('request')->andReturn($this->request);

        $this->object = new AuditLogger($this->restClient, $this->security, $container);
    }

    public function testLogNonAdmin()
    {
        $this->security->shouldReceive('isGranted')->with('ROLE_ADMIN')->andReturn(false);
        $this->security->shouldReceive('securityContext')->never();

        $this->object->log('whatever');
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testLogActionNotRecognised()
    {
        $this->security->shouldReceive('isGranted')->with('ROLE_ADMIN')->andReturn(true);
        $this->security->shouldReceive('getToken->getUser')->andReturn($this->user);
        $this->request->shouldReceive('getClientIp')->andReturn('123.124.125.126');

        $this->object->log('whatever');
    }

    public function testLogActionLoginSuccess()
    {
        $this->security->shouldReceive('isGranted')->with('ROLE_ADMIN')->andReturn(true);
        $this->security->shouldReceive('getToken->getUser')->andReturn($this->user);
        $this->request->shouldReceive('getClientIp')->andReturn('123.124.125.126');
        $entryChecker = function (EntityDir\AuditLogEntry $entry) {
            if ($entry->getIpAddress() != '123.124.125.126') {
                throw new \Exception('$entry->getIpAddress() expected to return 123.124.125.126');
            }
            if ($entry->getPerformedByUser()->getId() != 1) {
                throw new \Exception('$entry->getPerformedByUser()->getId() expected to return 1');
            }
            if ($entry->getAction() != EntityDir\AuditLogEntry::ACTION_LOGIN) {
                throw new \Exception('$entry->getAction() expected to return login');
            }

            return true;
        };
        $this->user->shouldReceive('getId')->andReturn(1);
        $this->restClient->shouldReceive('post')->with('audit-log', \Mockery::on($entryChecker), ['audit_log_save']);

        $this->object->log(EntityDir\AuditLogEntry::ACTION_LOGIN);
    }

    public function testLogActionAddUserSuccess()
    {
        $this->security->shouldReceive('isGranted')->with('ROLE_ADMIN')->andReturn(true);
        $this->security->shouldReceive('getToken->getUser')->andReturn($this->user);
        $this->request->shouldReceive('getClientIp')->andReturn('123.124.125.126');
        $entryChecker = function (EntityDir\AuditLogEntry $entry) {
            if ($entry->getIpAddress() != '123.124.125.126') {
                throw new \Exception('$entry->getIpAddress() expected to return 123.124.125.126');
            }
            if ($entry->getPerformedByUser()->getId() != 1) {
                throw new \Exception('$entry->getPerformedByUser()->getId() expected to return 1');
            }
            if ($entry->getUserEdited()->getId() != 2) {
                throw new \Exception('$entry->getUserEdited()->getId() expected to return 2');
            }
            if ($entry->getAction() != EntityDir\AuditLogEntry::ACTION_USER_ADD) {
                throw new \Exception('$entry->getAction() expected to return user add');
            }

            return true;
        };
        $userEdited = m::mock('AppBundle\Entity\User')
            ->shouldReceive('getId')->andReturn(2)
            ->getMock();
        $this->user->shouldReceive('getId')->andReturn(1);
        $this->restClient->shouldReceive('post')->with('audit-log', \Mockery::on($entryChecker), ['audit_log_save']);

        $this->object->log(EntityDir\AuditLogEntry::ACTION_USER_ADD, $userEdited);
    }

    public function tearDown()
    {
        m::close();
    }
}
