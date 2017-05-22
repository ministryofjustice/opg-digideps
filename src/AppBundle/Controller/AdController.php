<?php

namespace AppBundle\Controller;

use AppBundle\Entity as EntityDir;
use AppBundle\Form as FormDir;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;

/**
 * @Route("/ad")
 */
class AdController extends AbstractController
{
    /**
     * @Route("/", name="ad_homepage")
     * @Template
     */
    public function indexAction(Request $request)
    {
        $filters = [
            'order_by' => $request->get('order_by', 'id'),
            'sort_order' => $request->get('sort_order', 'DESC'),
            'limit' => $request->get('limit', 500),
            'offset' => $request->get('offset', 0),
            'role_name' => EntityDir\User::ROLE_LAY_DEPUTY,
            'ad_managed' => true,
            'q' => $request->get('q'),
        ];
        $users = $this->getRestClient()->get('user/get-all?' . http_build_query($filters), 'User[]');

        // form add
        $form = $this->createForm(new FormDir\Ad\AddUserType([
            'roleChoices' => [EntityDir\User::ROLE_LAY_DEPUTY=>'Lay deputy'],
            'roleNameSetTo' => EntityDir\User::ROLE_LAY_DEPUTY,
            'roleNameEmptyValue' => null,
        ]), new EntityDir\User());

        if ($request->isMethod('POST')) {
            $form->handleRequest($request);
            if ($form->isValid()) {
                // add user
                try {
                    $userToAdd = $form->getData(); /* @var $userToAdd EntityDir\User*/
                    // set email (needed to recreate token before login)
                    $userToAdd->setEmail('ad' . $this->getUser()->getId() . '-' . time() . '@digital.justice.gov.uk');
                    $userToAdd->setAdManaged(true);
                    $response = $this->getRestClient()->post('user', $userToAdd, ['ad_add_user'], 'User');
                    $request->getSession()->getFlashBag()->add(
                        'notice',
                        'User added. '
                    );

                    return $this->redirectToRoute('ad_homepage', [
                        'userAdded'=>$response->getId(),
                        //'order_by'=>'id',
                        //'sort_order'=>'DESC',
                    ]);
                } catch (RestClientException $e) {
                    $form->get('firstname')->addError(new FormError($e->getData()['message']));
                }
            }
        }

        return [
            'users' => $users,
            'form' => $form->createView(),
        ];
    }

    /**
     * @Route("/view-user", name="ad_view_user")
     * @Method({"GET", "POST"})
     * @Template
     *
     * @param Request $request
     */
    public function viewUserAction(Request $request)
    {
        $what = $request->get('what');
        $filter = $request->get('filter');

        try {
            $user = $this->getRestClient()->get("user/get-one-by/{$what}/{$filter}", 'User', ['user', 'client', 'report', 'odr']);
        } catch (\Exception $e) {
            return $this->render('AppBundle:Ad:error.html.twig', [
                'error' => 'User not found',
            ]);
        }

        if ($user->getRoleName() != EntityDir\User::ROLE_LAY_DEPUTY) {
            return $this->render('AppBundle:Ad:error.html.twig', [
                'error' => 'You can only view Lay deputies',
            ]);
        }

        return [
            'action' => 'edit',
            'id' => $user->getId(),
            'user' => $user,
        ];
    }

    /**
     * @Route("/login-as-deputy/{deputyId}", name="ad_deputy_login_redirect")
     *
     * @param Request $request
     */
    public function adLoginAsDeputyAction(Request $request, $deputyId)
    {
        $adUser = $this->getUser();

        // get user and check it's deputy and ODR
        try {
            /* @var $deputy EntityDir\User */
            $deputy = $this->getRestClient()->get("user/get-one-by/user_id/{$deputyId}", 'User', ['user']);
            if ($deputy->getRoleName() != EntityDir\User::ROLE_LAY_DEPUTY) {
                throw new \RuntimeException('User not a Lay deputy');
            }

            // flag as managed in order to retrieve it later
            $deputy->setAdManaged(true);
            $this->getRestClient()->put('user/' . $deputy->getId(), $deputy, ['ad_managed']);

            // recreate token needed for login
            $deputy = $this->getRestClient()->userRecreateToken($deputy->getEmail());

            // redirect to deputy area
            $deputyBaseUrl = rtrim($this->container->getParameter('non_admin_host'), '/');
            $redirectUrl = $deputyBaseUrl . $this->generateUrl('ad_login', [
                    'adId' => $adUser->getId(),
                    'userToken' => $deputy->getRegistrationToken(),
                    'adFirstname' => $adUser->getFirstname(),
                    'adLastname' => $adUser->getLastname(),
                ]);

            return $this->redirect($redirectUrl);
        } catch (\Exception $e) {
            return $this->render('AppBundle:Ad:error.html.twig', [
                'error' => $e->getMessage(),
            ]);
        }
    }
}
