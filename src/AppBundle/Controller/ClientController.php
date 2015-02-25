<?php
namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use AppBundle\Entity\Client;
use AppBundle\Exception\NotFound;

/**
 * @Route("/client")
 */
class ClientController extends RestController
{
    /**
     * @Route("/add")
     * @Method({"POST"})
     */
    public function addAction()
    {
        $clientData = $this->deserializeBodyContent();
        
        $user = $this->getRepository('User')->find($clientData['users'][0]);
        if(empty($user)){
            throw new \Exception("User with id: $user does not exist");
        }
        
        if (count($user->getClients()) === 0) {
            $client = new Client();
            $client->addUser($user);
        } else {
            // update
            $client = $user->getClients()->first();
        }
        
        $client->setFirstname($clientData['firstname']);
        $client->setLastname($clientData['lastname']);
        $client->setCaseNumber($clientData['case_number']);
        $client->setCourtDate(new \DateTime($clientData['court_date']));
        $client->setAllowedCourtOrderTypes($clientData['allowed_court_order_types']);
        $client->setAddress($clientData['address']);
        $client->setAddress2($clientData['address2']);
        $client->setPostcode($clientData['postcode']);
        $client->setCountry($clientData['country']);
        $client->setCounty($clientData['county']);
        $client->setPhone($clientData['phone']);
        
        $this->getEntityManager()->persist($client);
        $this->getEntityManager()->flush();
        
        return ['client' => $client ];
    }
    

    /**
     * @Route("/find-by-id/{id}", name="client_find_by_id")
     * @Method({"GET"})
     * 
     * @param integer $id
     */
    public function findByIdAction($id)
    {
        $client = $this->getDoctrine()->getRepository('AppBundle:Client')->find($id);
        
        //if client does not exist
        if(empty($client)){
            throw new \Exception("Client with id: $id does not exist");
        }
        return $client;
    }
    
     /**
     * @Route("/get-by-user-id/{userId}")
     * @Method({"GET"})
     */
    public function getByUserId($userId)
    {
        $user = $this->findEntityBy('User', $userId, "User not found");
        
        if (count($user->getClients()) === 0) {
            throw new NotFound("User has no clients");
        }
        
        return $this->findEntityBy('Client', $user->getClients()->first()->getId(), "Client not found");
    }
}
