<?php

namespace App\Controller;

use App\Service\Formatter\RestFormatter;
use Psr\Log\LoggerInterface;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/manage")
 */
class ManageController extends RestController
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
     * @Route("/availability", methods={"GET"})
     *
     * @return array
     */
    public function availabilityAction()
    {
        list($dbHealthy, $dbError) = $this->dbInfo();

        return [
            'healthy' => $dbHealthy,
            'environment' => $this->symfonyEnvironment,
            'errors' => implode("\n", array_filter([$dbError])),
        ];
    }

    /**
     * @Route("/elb", name="manage-elb", methods={"GET"})
     */
    public function elbAction()
    {
        return 'ok';
    }

    /**
     * @param LoggerInterface $logger
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
            if ($e instanceof \PDOException && $e->getCode() === 7) {
                $returnMessage = 'Database service not reachabe (' . $e->getMessage() . ')';
            }

            $this->logger->error($e->getMessage());

            return [false, $returnMessage];
        }
    }
}
