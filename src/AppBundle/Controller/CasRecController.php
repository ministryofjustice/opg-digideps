<?php

namespace AppBundle\Controller;

use AppBundle\Entity as EntityDir;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;

/**
 * @Route("/casrec")
 */
class CasRecController extends RestController
{
    /**
     * Bulk insert
     * Max 10k otherwise failing (memory reach 128M).
     *
     * @Route("/truncate")
     * @Method({"DELETE"})
     */
    public function truncateTable(Request $request)
    {
        $this->denyAccessUnlessGranted(EntityDir\Role::ADMIN);

        $em = $this->getEntityManager();
        $em->getConnection()->query('TRUNCATE TABLE casrec');

        return ['truncated'=>true];
    }

    /**
     * Bulk insert
     * Max 10k otherwise failing (memory reach 128M).
     *
     * @Route("/bulk-add")
     * @Method({"POST"})
     */
    public function addBulk(Request $request)
    {
        $maxRecords = 10000;
        $persistEvery = 5000;

        $this->denyAccessUnlessGranted(EntityDir\Role::ADMIN);

        ini_set('memory_limit', '1024M');
        set_time_limit(600);

        $retErrors = [];
        $data = json_decode(gzuncompress(base64_decode($request->getContent())), true);
        $count = count($data);

        if (!$count) {
            throw new \RuntimeException('No record received from the API');
        }
        if ($count > $maxRecords) {
            throw new \RuntimeException("Max $maxRecords records allowed in a single bulk insert");
        }

        $this->get('logger')->info('Received ' . count($data) . ' records');

        $em = $this->getEntityManager();
        $validator = $this->get('validator');

        try {
            $em->beginTransaction();
            $added = 1;
            foreach ($data as $dataIndex => $row) {
                $casRec = new EntityDir\CasRec(
                    $row['Case'],
                    $row['Surname'],
                    $row['Deputy No'],
                    $row['Dep Surname'],
                    $row['Dep Postcode'],
                    $row['Typeofrep'],
                    $row['Corref']
                );

                $errors = $validator->validate($casRec);
                if (count($errors) > 0) {
                    $retErrors[] = 'ERROR IN LINE ' . ($dataIndex + 2) . ' :' . str_replace('Object(AppBundle\Entity\CasRec).', '', (string) $errors);
                    unset($casRec);
                } else {
                    $em->persist($casRec);
                    if (($added++ % $persistEvery) === 0) {
                        $em->flush();
                        $em->clear();
                    }
                }
            }

            $em->flush();
            $em->commit();
            $em->clear();
        } catch (\Exception $e) {
            return ['added' => $added - 1, 'errors' => [$e->getMessage()]];
        }

        return ['added' => $added - 1, 'errors' => $retErrors];
    }

    /**
     * @Route("/count")
     * @Method({"GET"})
     */
    public function userCount()
    {
        $this->denyAccessUnlessGranted(EntityDir\Role::ADMIN);

        $qb = $this->getDoctrine()->getManager()->createQueryBuilder();
        $qb->select('count(c.id)');
        $qb->from('AppBundle\Entity\CasRec', 'c');

        $count = $qb->getQuery()->getSingleScalarResult();

        return $count;
    }
}
