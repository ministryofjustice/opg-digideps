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
        $dimensions = $request->query->get('dimension');
        $startDate = $request->query->get('startDate');
        $endDate = $request->query->get('endDate');

        if ($metric === null) {
            throw new \Exception('Must specify a metric');
        }

        if (!is_array($dimensions)) {
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
                $startDate->sub(new \DateInterval('P30D'));
            }
        } elseif ($endDate === null) {
            $startDate = new \DateTime($startDate);
            $endDate = clone $startDate;
            $endDate->add(new \DateInterval('P30D'));
        }

        $startDate->setTime(0, 0, 0);
        $endDate->setTime(23, 59, 59);

        // Identify the subquery to retrieve the data
        $subqueryMethod = 'getMetricQuery'. ucfirst($metric);
        if (!method_exists($this, $subqueryMethod)) {
            throw new \Exception('Invalid metric');
        }

        // Get an aggregation method and a query which pulls back (date, deputyType, reportType)
        list ($aggregation, $supportedDimensions, $subquery) = $this->$subqueryMethod();

        $em = $this->getEntityManager();
        $rsm = new ResultSetMapping();
        $rsm->addScalarResult('amount', 'amount');
        foreach ($dimensions as $index => $dimensionName) {
            if (!in_array($dimensionName, $supportedDimensions)) {
                throw new \Exception("Metric does not support \"$dimensionName\" dimension");
            }

            $key = "dimension$index";
            $rsm->addScalarResult($key, $dimensionName);
            $dimensions["t.$dimensionName"] = $key;
            $selectDimensions[] = "t.$dimensionName $key";
            $groupDimensions[] = "t.$dimensionName";
        }

        // Retrieve the data, within the date range and grouped by the dimension
        if (count($dimensions)) {
            $select = implode(', ', $selectDimensions);
            $group = implode(', ', $groupDimensions);
            $query = $em->createNativeQuery("SELECT $select, $aggregation amount FROM ($subquery) t WHERE t.date >= :startDate AND t.date <= :endDate GROUP BY $group", $rsm);
        } else {
            $query = $em->createNativeQuery("SELECT 'all' dimension, $aggregation amount FROM ($subquery) t WHERE t.date >= :startDate AND t.date <= :endDate", $rsm);
        }

        $query->setParameter('startDate', $startDate->format('Y-m-d H:i:s'));
        $query->setParameter('endDate', $endDate->format('Y-m-d H:i:s'));
        return $query->getResult();
    }

    /**
     * 'satisfaction' metric
     *
     * @return array $specification
     * $specification[0]    string     The aggregation function
     * $specification[1]    array      Dimensions supported by this metric
     * $specification[2]    string     SQL query to get raw statistic data
     */
    public function getMetricQuerySatisfaction()
    {
        return ["AVG(val)", ['deputyType', 'reportType'], "SELECT
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

    /**
     * 'reportsSubmitted' metric
     *
     * @return array $specification
     * $specification[0]    string     The aggregation function
     * $specification[1]    array      Dimensions supported by this metric
     * $specification[2]    string     SQL query to get raw statistic data
     */
    public function getMetricQueryReportsSubmitted()
    {
        return ["COUNT(1)", ['deputyType', 'reportType'], "SELECT
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
