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
     * Add client
     * 
     * @Route("/add")
     * @Method({"POST"})
     */
    public function addAction()
    {
        $data = $this->deserializeBodyContent();
      
        // read user
        $user = $this->findEntityBy('User', $data['users'][0], "User with id: {$data['users'][0]}  does not exist");

        // create client if the ID it nos specified, otherwise create one and add the user
        if (empty($data['id'])) {
            $client = new Client();
            $client->addUser($user);
        } else {
            $client = $this->findEntityBy('Client', $data['id'], 'Client not found');
        }
        
        $this->hydrateEntityWithArrayData($client, $data, [
            'firstname' => 'setFirstname', 
            'lastname' => 'setLastname', 
            'case_number' => 'setCaseNumber', 
            'allowed_court_order_types' => 'setAllowedCourtOrderTypes', 
            'address' => 'setAddress', 
            'address2' => 'setAddress2', 
            'postcode' => 'setPostcode', 
            'country' => 'setCountry', 
            'county' => 'setCounty', 
            'phone' => 'setPhone', 
        ]);
        $client->setCourtDate(new \DateTime($data['court_date']));
        
        $this->getEntityManager()->persist($client);
        $this->getEntityManager()->flush();
        
        return ['id' => $client->getId() ];
    }
    

    /**
     * @Route("/find-by-id/{id}", name="client_find_by_id" )
     * @Method({"GET"})
     * 
     * @param integer $id
     */
    public function findByIdAction($id)
    {
        $request = $this->getRequest();
        
        $serialisedGroups = null;
        
        if($request->query->has('groups')){
            $serialisedGroups = $request->query->get('groups');
        }
        
        $this->setJmsSerialiserGroup($serialisedGroups);
        
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
