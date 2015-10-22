<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\Request;
use AppBundle\Exception as AppExceptions;
use AppBundle\Entity as EntityDir;


/**
 * @Route("/casrec")
 */
class CasRecController extends RestController
{
    /**
     * Bulk insert
     * Max 10k otherwise failing (memory reach 128M)
     * 
     * @Route("/bulk-add")
     * @Method({"POST"})
     */
    public function addBulk(Request $request)
    {
        $this->denyAccessUnlessGranted(EntityDir\Role::ADMIN);
        
        $data = json_decode(gzuncompress(base64_decode($request->getContent())), true);
        
        $this->get('logger')->info('Received ' . count($data) . ' records');
        
        $this->getEntityManager()->clear();
        
        foreach ($data as $index => $row) {
            $casRec = new EntityDir\CasRec($row['Case'], $row['Surname'], $row['Deputy No'], $row['Dep Surname'], $row['Dep Postcode']);
            $this->getEntityManager()->persist($casRec);
        }
        
        $this->getEntityManager()->flush();
        $this->getEntityManager()->clear();
        
        return [];
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
        $qb->from('AppBundle\Entity\CasRec','c');

        $count = $qb->getQuery()->getSingleScalarResult();
        
        return $count;
    }
    
}
