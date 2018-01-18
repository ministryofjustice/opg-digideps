<?php

namespace AppBundle\Controller;

use AppBundle\Entity\User;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

/**
 * @Route("/manage")
 */
class ManageController extends RestController
{
    /**
     * @Route("/availability")
     * @Method({"GET"})
     */
    public function availabilityAction()
    {
        list($dbHealthy, $dbError) = $this->dbInfo();

        $data = [
            'healthy' => $dbHealthy,
            'errors' => implode("\n", array_filter([$dbError])),
        ];

        return $data;
    }

    /**
     * @Route("/elb", name="manage-elb")
     * @Method({"GET"})
     */
    public function elbAction()
    {
        return 'ok';
    }

    /**
     * @return array [boolean healthy, error string]
     */
    private function dbInfo()
    {
        try {
            $this->getDoctrine()->getConnection()->query('select * from migrations LIMIT 1')->fetchAll();

            return [true, ''];
        } catch (\Exception $e) {
            // customise error message if possible
            $returnMessage = 'Database generic error';
            if ($e instanceof \PDOException && $e->getCode() === 7) {
                $returnMessage = 'Database service not reachabe (' . $e->getMessage() . ')';
            }
            if ($e instanceof \Doctrine\DBAL\DBALException) {
                $returnMessage = 'Migrations table missing.';
            }

            // log real error message
            $this->get('logger')->error($e->getMessage());

            return [false, $returnMessage];
        }
    }
}
