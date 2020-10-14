<?php declare(strict_types=1);


namespace AppBundle\EventListener;

use AppBundle\Entity\User;
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

    /** @var DateTimeProvider */
    private $dateTimeProvider;
    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(Security $security, DateTimeProvider $dateTimeProvider, LoggerInterface $logger)
    {
        $this->security = $security;
        $this->dateTimeProvider = $dateTimeProvider;
        $this->logger = $logger;
    }

    public function preUpdate(User $user, PreUpdateEventArgs $args)
    {
        $changes = $args->getEntityChangeSet();

        if (array_key_exists('email', $changes)) {
            $this->logEvents[] = [
                'trigger' => 'ADMIN_USER_EDIT',
                'email_changed_from' => $changes['email'][0],
                'email_changed_to' => $changes['email'][1],
                'changed_on' => $this->dateTimeProvider->getDateTime()->format(DateTime::ATOM),
                'changed_by' => $this->security->getUser()->getEmail(),
                'subject_full_name' => $user->getFullName(),
                'subject_role' => $user->getRoleName(),
                'event' => 'USER_EMAIL_CHANGED',
                'type' => 'audit'
            ];
        }
    }

    public function postUpdate(User $user, LifecycleEventArgs $args)
    {
        foreach ($this->logEvents as $event) {
            $this->logger->notice('', $event);
        }

        $this->logEvents = [];
    }
}
