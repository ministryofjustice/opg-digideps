<?php

namespace AppBundle\Controller;

use Doctrine\ORM\EntityManager;
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
        $params = new StatsQuery($request);
        $query = (new StatsQueryInterfaceFactory($this->getEntityManager()))->create($params);

        return $query->execute($params);
    }
}

class StatsQuery
{
    public $metric;
    public $dimensions;
    public $startDate;
    public $endDate;

    public function __construct(Request $request)
    {
        $this->metric = $request->query->get('metric');
        $this->dimensions = $request->query->get('dimension');
        $this->startDate = $request->query->get('startDate');
        $this->endDate = $request->query->get('endDate');

        if ($this->metric === null) {
            throw new \Exception('Must specify a metric');
        }

        if (!is_array($this->dimensions) && !is_null($this->dimensions)) {
            throw new \Exception('Invalid dimension');
        }

        if ($this->startDate === null) {
            if ($this->endDate === null) {
                $this->endDate = new \DateTime();
                $this->startDate = new \DateTime('-30 days');
            } else {
                $endDate = new \DateTime($this->endDate);
                $this->startDate = clone $endDate;
                $this->startDate->sub(new \DateInterval('P30D'));
            }
        } elseif ($this->endDate === null) {
            $this->startDate = new \DateTime($this->startDate);
            $this->endDate = clone $this->startDate;
            $this->endDate->add(new \DateInterval('P30D'));
        }

        $this->startDate->setTime(0, 0, 0);
        $this->endDate->setTime(23, 59, 59);
    }
}

interface StatsQueryInterface
{
    public function execute(StatsQuery $sq);
}

abstract class MetricQuery implements StatsQueryInterface
{
    private $em;

    abstract protected function getSubQuery();

    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }

    /**
     * @param StatsQuery $sq
     * @return mixed
     * @throws \Exception
     */
    public function execute(StatsQuery $sq)
    {
        $rsm = new ResultSetMapping();
        $rsm->addScalarResult('amount', 'amount');

        if (!is_null($sq->dimensions)) {
            foreach ($sq->dimensions as $index => $dimensionName) {
                if (!in_array($dimensionName, $this->supportedDimensions)) {
                    throw new \Exception("Metric does not support \"$dimensionName\" dimension");
                }

                $key = "dimension$index";
                $rsm->addScalarResult($key, $dimensionName);
                $dimensions["t.$dimensionName"] = $key;
                $selectDimensions[] = "t.$dimensionName $key";
                $groupDimensions[] = "t.$dimensionName";
            }
        }

        // Retrieve the data, within the date range and grouped by the dimension
        $subQuery = $this->getSubquery();
        //$aggregation = $this->aggregation;
        if (!is_null($dimensions)) {
            $select = implode(', ', $selectDimensions);
            $group = implode(', ', $groupDimensions);
            $query = $this->em->createNativeQuery("SELECT $select, $this->aggregation amount FROM ($subQuery) t WHERE t.date >= :startDate AND t.date <= :endDate GROUP BY $group", $rsm);
        } else {
            $query = $this->em->createNativeQuery("SELECT 'all' dimension, $this->aggregation amount FROM ($subQuery) t WHERE t.date >= :startDate AND t.date <= :endDate", $rsm);
        }

        $query->setParameter('startDate', $sq->startDate->format('Y-m-d H:i:s'));
        $query->setParameter('endDate', $sq->endDate->format('Y-m-d H:i:s'));

        return $query->getResult();
    }

}

class MetricSatisfactionQuery extends MetricQuery
{
    protected $aggregation = 'AVG(val)';
    protected $supportedDimensions = ['deputyType', 'reportType'];

    /**
     * @return string
     */
    protected function getSubQuery()
    {
        return "SELECT
            s.created_at date,
            CASE
                WHEN s.deputy_role LIKE '%_PROF_%' THEN 'prof'
                WHEN s.deputy_role LIKE '%_PA_%' THEN 'pa'
                ELSE 'lay'
            END deputyType,
            report_type reportType,
            s.score val
        FROM satisfaction s";
    }

}

class MetricReportsSubmittedQuery extends MetricQuery
{
    protected $aggregation = 'COUNT(1)';
    protected $supportedDimensions = ['deputyType', 'reportType'];

    /**
     * @return string
     */
    protected function getSubQuery()
    {
        return "SELECT
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
        LEFT JOIN report r ON r.id = rs.report_id";
    }
}

class StatsQueryInterfaceFactory
{
    private $em;

    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }

    public function create(StatsQuery $sq)
    {
        $className = 'AppBundle\Controller\Metric'. ucfirst($sq->metric) . 'Query';

        if (!class_exists($className)) {
            throw new \InvalidArgumentException('Bad');
        }

        return new $className($this->em);
    }
}
