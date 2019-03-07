<?php

namespace AppBundle\v2\DTO;

class DeputyDto implements \JsonSerializable
{
    private $id;
    private $firstName;
    private $lastName;
    private $email;
    private $roleName;
    private $postcode;
    private $ndrEnabled;
    private $clients;

    /**
     * @param $id
     * @param $firstName
     * @param $lastName
     * @param $email
     * @param $roleName
     * @param $postcode
     * @param $ndrEnabled
     */
    public function __construct($id, $firstName, $lastName, $email, $roleName, $postcode, $ndrEnabled)
    {
        $this->id = $id;
        $this->firstName = $firstName;
        $this->lastName = $lastName;
        $this->email = $email;
        $this->roleName = $roleName;
        $this->postcode = $postcode;
        $this->ndrEnabled = $ndrEnabled;
    }

    /**
     * @return array|mixed
     */
    public function jsonSerialize()
    {
        return [
            'id' => $this->id,
            'firstname' => $this->firstName,
            'lastname' => $this->lastName,
            'email' => $this->email,
            'role_name' => $this->roleName,
            'address_postcode' => $this->postcode,
            'ndr_enabled' => $this->ndrEnabled,
            'clients' => $this->serializeClients()
        ];
    }

    /**
     * @return array
     */
    private function serializeClients()
    {
        if (empty($this->clients)) {
            return [];
        }

        $serializedClients = [];

        foreach ($this->clients as $client) {
            if ($client instanceof ClientDto) {
                $serializedClients[] = $client->jsonSerialize();
            }
        }

        return $serializedClients;
    }

    /**
     * @param mixed $clients
     */
    public function setClients($clients)
    {
        $this->clients = $clients;
    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return mixed
     */
    public function getFirstName()
    {
        return $this->firstName;
    }

    /**
     * @return mixed
     */
    public function getLastName()
    {
        return $this->lastName;
    }

    /**
     * @return mixed
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * @return mixed
     */
    public function getRoleName()
    {
        return $this->roleName;
    }

    /**
     * @return mixed
     */
    public function getPostcode()
    {
        return $this->postcode;
    }

    /**
     * @return mixed
     */
    public function getNdrEnabled()
    {
        return $this->ndrEnabled;
    }

    /**
     * @return mixed
     */
    public function getClients()
    {
        return $this->clients;
    }
}

