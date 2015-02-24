<?php
namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use AppBundle\Entity\Client;

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
        
        $user = $this->getRepository('User')->find($clientData['user']);
        
        if(empty($user)){
            throw new \Exception("User with id: $user does not exist");
        }
        
        $client = new Client();
        $client->setFirstname($clientData['firstname']);
        $client->setLastname($clientData['lastname']);
        $client->setCaseNumber($clientData['case_number']);
        $client->setCourtDate(new \DateTime($clientData['court_date']));
        $client->setAllowedCourtOrderTypes($clientData['allowed_court_order_types']);
        $client->setAddress($clientData['address']);
        $client->setAddress2($clientData['address2']);
        $client->setPostcode($clientData['postcode']);
        $client->setCountry($clientData['country']);
        $client->setPhone($clientData['phone']);
        $client->addUser($user);
        
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
}
