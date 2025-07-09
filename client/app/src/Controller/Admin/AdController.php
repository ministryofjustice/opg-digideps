<?php

namespace App\Controller\Admin;

use App\Controller\AbstractController;
use App\Entity as EntityDir;
use App\Exception\RestClientException;
use App\Form as FormDir;
use App\Service\Client\Internal\UserApi;
use App\Service\Client\RestClient;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/ad")
 */
class AdController extends AbstractController
{
    public function __construct(
        private RestClient $restClient,
        private UserApi $userApi,
        private ParameterBagInterface $params,
    ) {
    }

    /**
     * @Route("/", name="ad_homepage")
     *
     * @Security("is_granted('ROLE_AD')")
     *
     * @Template("@App/Admin/Ad/index.html.twig")
     *
     * @return array|RedirectResponse
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
        $users = $this->restClient->get('user/get-all?'.http_build_query($filters), 'User[]');

        // form add
        $form = $this->createForm(FormDir\Ad\AddUserType::class, new EntityDir\User(), [
            'roleChoices' => [EntityDir\User::ROLE_LAY_DEPUTY => 'Lay deputy'],
            'roleNameSetTo' => EntityDir\User::ROLE_LAY_DEPUTY,
        ]);

        if ($request->isMethod('POST')) {
            $form->handleRequest($request);
            if ($form->isSubmitted() && $form->isValid()) {
                // add user
                try {
                    $userToAdd = $form->getData(); /* @var $userToAdd EntityDir\User */
                    // set email (needed to recreate token before login)
                    $userToAdd->setEmail('ad'.$this->getUser()->getId().'-'.time().'@digital.justice.gov.uk');
                    $userToAdd->setAdManaged(true);
                    $response = $this->restClient->post('user', $userToAdd, ['ad_add_user'], 'User');
                    $request->getSession()->getFlashBag()->add(
                        'notice',
                        'User added. '
                    );

                    return $this->redirectToRoute('ad_homepage', [
                        'userAdded' => $response->getId(),
                        // 'order_by'=>'id's,
                        // 'sort_order'=>'DESC',
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
     * @Route("/view-user", methods={"GET", "POST"}, name="ad_view_user")
     *
     * @Security("is_granted('ROLE_AD')")
     *
     * @Template("@App/Admin/Ad/viewUser.html.twig")
     *
     * @return array|Response|null
     */
    public function viewUserAction(Request $request)
    {
        $what = $request->get('what');
        $filter = $request->get('filter');

        try {
            $user = $this->restClient->get("user/get-one-by/{$what}/{$filter}", 'User', ['user', 'client', 'client-reports',
                'report', 'ndr', ]);
        } catch (\Throwable $e) {
            return $this->render('@App/Admin/Ad/error.html.twig', [
                'error' => 'User not found',
            ]);
        }

        if (EntityDir\User::ROLE_LAY_DEPUTY != $user->getRoleName()) {
            return $this->render('@App/Admin/Ad/error.html.twig', [
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
     * @Security("is_granted('ROLE_AD')")
     *
     * @return RedirectResponse|Response|null
     */
    public function adLoginAsDeputyAction($deputyId)
    {
        $adUser = $this->getUser();

        // get user and check it's deputy and NDR
        try {
            /* @var $deputy EntityDir\User */
            $deputy = $this->restClient->get("user/get-one-by/user_id/{$deputyId}", 'User', ['user']);
            if (EntityDir\User::ROLE_LAY_DEPUTY != $deputy->getRoleName()) {
                throw new \RuntimeException('User not a Lay deputy');
            }

            // flag as managed in order to retrieve it later
            $deputy->setAdManaged(true);
            $this->restClient->put('user/'.$deputy->getId(), $deputy, ['ad_managed']);

            // recreate token needed for login
            $deputy = $this->userApi->recreateToken($deputy->getEmail());

            // redirect to deputy area
            $deputyBaseUrl = rtrim($this->params->get('non_admin_host'), '/');
            $redirectUrl = $deputyBaseUrl.$this->generateUrl('ad_login', [
                'adId' => $adUser->getId(),
                'userToken' => $deputy->getRegistrationToken(),
                'adFirstname' => $adUser->getFirstname(),
                'adLastname' => $adUser->getLastname(),
            ]);

            return $this->redirect($redirectUrl);
        } catch (\Throwable $e) {
            return $this->render('@App/Admin/Ad/error.html.twig', [
                'error' => $e->getMessage(),
            ]);
        }
    }
}
