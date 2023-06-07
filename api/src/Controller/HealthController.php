<?php

namespace App\Controller;

use App\Service\Formatter\RestFormatter;
use Psr\Log\LoggerInterface;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/health-check")
 */
class HealthController extends RestController
{
    private string $symfonyEnvironment;
    private LoggerInterface $logger;
    private RestFormatter $restFormatter;

    public function __construct(string $symfonyEnvironment, LoggerInterface $logger, RestFormatter $restFormatter)
    {
        $this->symfonyEnvironment = $symfonyEnvironment;
        $this->logger = $logger;
        $this->restFormatter = $restFormatter;
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
     * @param LoggerInterface $logger
     *
     * @return array [boolean healthy, error string]
     */
    private function dbInfo()
    {
        try {
            $this->getDoctrine()->getConnection()->query('select * from migrations LIMIT 1')->fetchAll();

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
