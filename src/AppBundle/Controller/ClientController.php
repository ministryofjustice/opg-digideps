<?php
namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
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
     * @Route("/upsert")
     * @Method({"POST", "PUT"})
     */
    public function  upsertAction()
    {
        $data = $this->deserializeBodyContent();
        $request = $this->getRequest();
      
        if($request->getMethod() == "POST"){
            $client = $this->add($data['users'][0]);
        }else{
            $client = $this->update( $data['id']);
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
     * 
     * @param integer $userId
     * @return AppBundle\Entity\Client
     */
    private function add($userId)
    {
       $user = $this->findEntityBy('User', $userId, "User with id: {$userId}  does not exist");
        
       $client = new Client();
       $client->addUser($user);
        
       return $client;
    }
    
    /**
     * @param integer $clientId
     * @return AppBundle\Entity\Client
     */
    private function update($clientId)
    {
        return $this->findEntityBy('Client', $clientId, 'Client not found');
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
        //$currentUser = $request->getSession()->get('currentUser');
        
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
        //$user = $request->getSession()->get('currentUser');
        
        if (count($user->getClients()) === 0) {
            throw new NotFound("User has no clients");
        }
        
        return $this->findEntityBy('Client', $user->getClients()->first()->getId(), "Client not found");
    }
}
