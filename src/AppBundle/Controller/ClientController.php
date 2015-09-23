<?php
namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\Request;
use AppBundle\Entity\Client;
use AppBundle\Exception as AppExceptions;


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
    public function  upsertAction(Request $request)
    {
        $data = $this->deserializeBodyContent();
      
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
     * @Route("/find-by-id/{id}/{userId}", name="client_find_by_id" )
     * @Method({"GET"})
     * 
     * @param integer $id
     * @param integer $userId to check the record is accessible by this user
     */
    public function findByIdAction(Request $request, $id, $userId)
    {
        if ($request->query->has('groups')) {
            $this->setJmsSerialiserGroups($request->query->get('groups'));
        }
        
        $client = $this->getRepository('Client')->find($id);
        
        //  throw exception if the client does not exist or it's not accessible by the given user
        if(empty($client) || !in_array($userId, $client->getUserIds())) {
            throw new \Exception("Client with id: $id does not exist", 404);
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
            throw new AppExceptions\NotFound("User has no clients", 404);
        }
        
        return $this->findEntityBy('Client', $user->getClients()->first()->getId(), "Client not found");
    }
}
