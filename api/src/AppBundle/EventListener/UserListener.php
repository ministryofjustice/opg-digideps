<?php declare(strict_types=1);


namespace AppBundle\EventListener;

use AppBundle\Entity\User;
use AppBundle\Service\Audit\AuditEvents;
use AppBundle\Service\Time\DateTimeProvider;
use DateTime;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Psr\Log\LoggerInterface;
use Symfony\Component\Security\Core\Security;

class UserListener
{
    /** @var Security */
    private $security;

    /** @var array */
    public $logEvents = [];

    /** @var LoggerInterface */
    private $logger;

    /** @var AuditEvents */
    private $auditEvents;

    public function __construct(Security $security, LoggerInterface $logger, AuditEvents $auditEvents)
    {
        $this->security = $security;
        $this->logger = $logger;
        $this->auditEvents = $auditEvents;
    }

    public function preUpdate(User $user, PreUpdateEventArgs $args)
    {
        $changes = $args->getEntityChangeSet();

        if ($this->canLogEmailChange($changes)) {
            $this->logEvents[] = $this->auditEvents->userEmailChanged(
                AuditEvents::TRIGGER_ADMIN_USER_EDIT,
                $changes['email'][0],
                $changes['email'][1],
                $this->security->getUser()->getEmail(),
                $user->getFullName(),
                $user->getRoleName()
            );
        }
    }

    public function postUpdate(User $user, LifecycleEventArgs $args)
    {
        foreach ($this->logEvents as $event) {
            $this->logger->notice('', $event);
        }

        $this->logEvents = [];
    }

    private function canLogEmailChange(array $changes)
    {
        return array_key_exists('email', $changes) &&  $this->security->getUser() instanceof User;
    }
}
