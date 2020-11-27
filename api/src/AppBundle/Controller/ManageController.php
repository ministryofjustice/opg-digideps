<?php

namespace AppBundle\Controller;

use Psr\Log\LoggerInterface;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/manage")
 */
class ManageController extends RestController
{
    /**
     * @Route("/availability", methods={"GET"})
     * @param string $symfonyEnvironment
     * @return array
     */
    public function availabilityAction(string $symfonyEnvironment)
    {
        list($dbHealthy, $dbError) = $this->dbInfo();

        return [
            'healthy' => $dbHealthy,
            'environment' => $symfonyEnvironment,
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
    private function dbInfo(LoggerInterface $logger)
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
            if ($e instanceof \Doctrine\DBAL\DBALException) {
                $returnMessage = 'Migrations table missing.';
            }

            $logger->error($e->getMessage());

            return [false, $returnMessage];
        }
    }
}
