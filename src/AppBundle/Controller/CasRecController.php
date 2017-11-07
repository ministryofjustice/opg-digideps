<?php

namespace AppBundle\Controller;

use AppBundle\Entity as EntityDir;
use AppBundle\Service\CsvUploader;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use \Doctrine\Common\Util\Debug as doctrineDebug;
use Symfony\Component\HttpFoundation\Response;

/**
 * @Route("/casrec")
 */
class CasRecController extends RestController
{
    /**
     * @Route("/truncate")
     * @Method({"DELETE"})
     */
    public function truncateTable(Request $request)
    {
        $this->denyAccessUnlessGranted(EntityDir\User::ROLE_ADMIN);

        $em = $this->getEntityManager();
        $em->getConnection()->query('TRUNCATE TABLE casrec');

        return ['truncated'=>true];
    }

    /**
     * Bulk insert
     * To call multiple times in chunks of maximum 10k records, otherwise failing deu to memory reasons.
     * Currently used from admin area via a ajax uploader and multiple requests (after an initial truncate)
     *
     * @Route("/bulk-add")
     * @Method({"POST"})
     */
    public function addBulk(Request $request)
    {
        $this->denyAccessUnlessGranted(EntityDir\User::ROLE_ADMIN);

        ini_set('memory_limit', '1024M');

        $data = CsvUploader::decompressData($request->getContent());

        $ret = $this->get('casrec_service')->addBulk($data);

        return $ret;
    }


    /**
     * Verify Deputy & Client last names, Postcode, and Case Number
     *
     * @Route("/verify")
     * @Method({"POST"})
     */
    public function verify(Request $request)
    {
        $clientData = $this->deserializeBodyContent($request);
        $user = $this->getUser();

        $casrecVerified = $this->container->get('opg_digideps.casrec_verification_service')
            ->validate ( $clientData['case_number']
                       , $clientData['lastname']
                       , $user->getLastname()
                       , $user->getAddressPostcode()
            );

        return ['verified' => $casrecVerified];
    }

    /**
     * @Route("/count")
     * @Method({"GET"})
     */
    public function userCount()
    {
        $this->denyAccessUnlessGranted(EntityDir\User::ROLE_ADMIN);

        $qb = $this->getDoctrine()->getManager()->createQueryBuilder();
        $qb->select('count(c.id)');
        $qb->from('AppBundle\Entity\CasRec', 'c');

        $count = $qb->getQuery()->getSingleScalarResult();

        return $count;
    }

    /**
     * Return CSV file created on the fly
     *
     * @Route("/stats.csv")
     * @Method({"GET"})
     */
    public function getStatsCsv(Request $request)
    {
        $this->denyAccessUnlessGranted(EntityDir\User::ROLE_ADMIN);

        // create CSV if not added by the cron, or the "regenerated" is added
        if (!file_exists(EntityDir\CasRec::STATS_FILE_PATH) || $request->get('regenerate')) {
            $this->get('casrec_service')->saveCsv(EntityDir\CasRec::STATS_FILE_PATH);
        }

        $response = new Response();
        $response->setContent(readfile(EntityDir\CasRec::STATS_FILE_PATH));

        return $response;
    }

}
