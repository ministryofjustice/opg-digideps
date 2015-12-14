<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\Request;
use AppBundle\Exception as AppExceptions;
use AppBundle\Entity as EntityDir;

/**
 * @Route("/stats")
 */
class StatsController extends RestController
{
    /**
     * @Route("/users")
     * @Method({"GET"})
     */
    public function users(Request $request)
    {
        $this->denyAccessUnlessGranted(EntityDir\Role::ADMIN);

        $connection = $this->get('em')->getConnection();

        $sql = 'SELECT
            u.id as user_id, u.email, u.firstname, u.lastname,
            u.registration_date as created_at,
            u.active as is_active,
            length(u.address1)>0 as has_details,
            COUNT(c.id)>0 as has_client,
            COUNT(r.id) as reports,
            COUNT(r.submitted)>0 as has_report_submitted
            FROM dd_user u
            LEFT JOIN deputy_case dc ON u.id= dc.user_id
            LEFT JOIN client c ON c.id = dc.client_id
            LEFT JOIN report r ON r.client_id=c.id
            WHERE u.role_id=2
            GROUP BY (u.id)
            ORDER BY u.id DESC;';

        $ret = $connection->query($sql)->fetchAll();

        return $ret;


    }
    

    
}
