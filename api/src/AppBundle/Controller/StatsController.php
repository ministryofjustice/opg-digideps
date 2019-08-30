<?php

namespace AppBundle\Controller;

use Doctrine\ORM\Query\ResultSetMapping;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Request;

class StatsController extends RestController
{
    /**
     * @Route("/stats")
     * @Method({"GET"})
     */
    public function getMetric(Request $request)
    {
        $metric = $request->query->get('metric');
        $dimension = $request->query->get('dimension');
        $startDate = $request->query->get('startDate');
        $endDate = $request->query->get('endDate');

        if ($metric === null) {
            throw new \Exception('Must specify a metric');
        }

        if ($dimension !== null && !in_array($dimension, ['deputyType', 'reportType'])) {
            throw new \Exception('Invalid dimension');
        }

        // Set default start and end dates
        if ($startDate === null) {
            if ($endDate === null) {
                $endDate = new \DateTime();
                $startDate = new \DateTime('-30 days');
            } else {
                $endDate = new \DateTime($endDate);
                $startDate = clone $endDate;
                $startDate->subtract(new \DateInterval('P30D'));
            }
        } elseif ($endDate === null) {
            $startDate = new \DateTime($startDate);
            $endDate = clone $startDate;
            $endDate->add(new \DateInterval('P30D'));
        }

        // Identify the subquery to retrieve the data
        $subqueryMethod = 'getMetricQuery'. ucfirst($metric);
        if (!method_exists($this, $subqueryMethod)) {
            throw new \Exception('Invalid metric');
        }

        // Get an aggregation method and a query which pulls back (date, deputyType, reportType)
        list ($aggregation, $subquery) = $this->$subqueryMethod();

        $em = $this->getEntityManager();
        $rsm = new ResultSetMapping();
        $rsm->addScalarResult('dimension', 'dimension');
        $rsm->addScalarResult('amount', 'amount');

        // Retrieve the data, within the date range and grouped by the dimension
        if ($dimension === null) {
            $query = $em->createNativeQuery("SELECT 'all' dimension, $aggregation amount FROM ($subquery) t WHERE t.date > :startDate AND t.date < :endDate", $rsm);
        } else {
            $query = $em->createNativeQuery("SELECT t.$dimension dimension, $aggregation amount FROM ($subquery) t WHERE t.date > :startDate AND t.date < :endDate GROUP BY t.$dimension", $rsm);
        }

        $query->setParameter('startDate', $startDate->format('Y-m-d'));
        $query->setParameter('endDate', $endDate->format('Y-m-d'));
        return $query->getResult();
    }

    public function getMetricQuerySatisfaction()
    {
        return ["AVG(val)", "SELECT
            s.created_at date,
            CASE
                WHEN s.deputy_role LIKE '%_PROF_%' THEN 'prof'
                WHEN s.deputy_role LIKE '%_PA_%' THEN 'pa'
                ELSE 'lay'
            END deputyType,
            report_type reportType,
            s.score val
        FROM satisfaction s"];
    }

    public function getMetricQueryReportsSubmitted()
    {
        return ["COUNT(1)", "SELECT
            rs.created_on date,
            CASE
                WHEN u.role_name LIKE '%_PROF_%' THEN 'prof'
                WHEN u.role_name LIKE '%_PA_%' THEN 'pa'
                ELSE 'lay'
            END deputyType,
            CASE
                WHEN rs.ndr_id IS NOT NULL THEN 'ndr'
                ELSE r.type
            END reportType
        FROM report_submission rs
        INNER JOIN dd_user u ON u.id = rs.created_by
        LEFT JOIN report r ON r.id = rs.report_id"];
    }
}
