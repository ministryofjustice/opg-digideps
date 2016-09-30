<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\Request;
use AppBundle\Model\SelfRegisterData;
use AppBundle\Entity\User;

/**
 * @Route("/selfregister")
 */
class SelfRegisterController extends RestController
{
    /**
     * @Route("")
     * @Method({"POST"})
     */
    public function register(Request $request)
    {
        if (!$this->getAuthService()->isSecretValid($request)) {
            throw new \RuntimeException('client secret not accepted.', 403);
        }

        $data = $this->deserializeBodyContent($request);

        $selfRegisterData = new SelfRegisterData();

        $this->populateSelfReg($selfRegisterData, $data);

        $validator = $this->get('validator');
        $errors = $validator->validate($selfRegisterData);

        if (count($errors) > 0) {
            throw new \RuntimeException('Invalid registration data: '.$errors);
        }

        try {
            $user = $this->container->get('user.selfRegistration')->selfRegisterUser($selfRegisterData);
            $this->get('logger')->warning('CasRec register success: ', ['extra' => ['page' => 'user_registration', 'success' => true] + $selfRegisterData->toArray()]);
        } catch (\Exception $e) {
            $this->get('logger')->warning('CasRec register failed:', ['extra' => ['page' => 'user_registration', 'success' => false] + $selfRegisterData->toArray()]);
            throw $e;
        }

        return $user;
    }

    /*
     * @param SelfRegisterData $selfRegisterData
     * @param array $data
     */
    public function populateSelfReg(SelfRegisterData $selfRegisterData, array $data)
    {
        $this->hydrateEntityWithArrayData($selfRegisterData, $data, [
            'firstname' => 'setFirstname',
            'lastname' => 'setLastname',
            'email' => 'setEmail',
            'postcode' => 'setPostcode',
            'client_firstname' => 'setClientFirstname',
            'client_lastname' => 'setClientLastname',
            'case_number' => 'setCaseNumber',
        ]);
    }
}
