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
     * @Route("/bulk-add")
     * @Method({"POST"})
     */
    public function addBulk(Request $request)
    {
        $this->denyAccessUnlessGranted(EntityDir\Role::ADMIN);
        
        
        $data = $this->deserializeBodyContent($request);
        
        foreach ($data as $index => $row) {
            $casRec = new EntityDir\CasRec($row['Case'], $row['Surname'], $row['Deputy No'], $row['Dep Surname'], $row['Dep Postcode']);
            $this->getEntityManager()->persist($casRec);
        }
        
        $this->getEntityManager()->flush();
        
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
