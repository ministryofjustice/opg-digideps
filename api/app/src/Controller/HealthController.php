<?php

namespace App\Controller;

use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/health-check")
 */
class HealthController extends RestController
{
    public function __construct(
        private readonly string $symfonyEnvironment,
        private readonly LoggerInterface $logger,
        private EntityManagerInterface $em
    ) {
        parent::__construct($em);
    }

    /**
     * @Route("", name="health-check", methods={"GET"})
     */
    public function containerHealthAction()
    {
        return 'ok';
    }

    /**
     * @Route("/service", methods={"GET"})
     *
     * @return array
     */
    public function serviceHealthAction()
    {
        list($dbHealthy, $dbError) = $this->dbInfo();

        return [
            'healthy' => $dbHealthy,
            'environment' => $this->symfonyEnvironment,
            'errors' => implode("\n", array_filter([$dbError])),
        ];
    }

    /**
     * @return array [boolean healthy, error string]
     */
    private function dbInfo()
    {
        try {
            $this->em->getConnection()->query('select * from migrations LIMIT 1')->fetchAll();

            return [true, ''];
        } catch (\Throwable $e) {
            // customise error message if possible
            $returnMessage = 'Database generic error';
            if ($e instanceof \PDOException && 7 === $e->getCode()) {
                $returnMessage = 'Database service not reachable ('.$e->getMessage().')';
            }

            $this->logger->error($e->getMessage());

            return [false, $returnMessage];
        }
    }
}
