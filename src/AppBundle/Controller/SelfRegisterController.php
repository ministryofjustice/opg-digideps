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
        $data = $this->deserializeBodyContent($request);

        $selfRegisterData = new SelfRegisterData();

        $this->populateSelfReg($selfRegisterData, $data);

        $validator = $this->get('validator');
        $errors = $validator->validate($selfRegisterData);

        if (count($errors) > 0) {
            throw new \RuntimeException("Invalid registration data");
        }

        $userRegistrationService = $this->container->get("user.selfRegistration");

        /** @var User $user */
        $user = $userRegistrationService->selfRegisterUser($selfRegisterData);

        return ['id'=>$user->getId()];
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
            'client_lastname' => 'setClientLastname',
            'case_number' => 'setCaseNumber',
        ]);
    }

}
