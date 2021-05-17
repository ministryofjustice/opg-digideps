<?php

namespace App\Controller\Admin;

use App\Controller\AbstractController;
use App\Entity as EntityDir;
use App\Entity\User;
use App\Exception\RestClientException;
use App\Form as FormDir;
use App\Security\UserVoter;
use App\Service\Audit\AuditEvents;
use App\Service\Client\Internal\UserApi;
use App\Service\Client\RestClient;
use App\Service\CsvUploader;
use App\Service\DataImporter\CsvToArray;
use App\Service\Logger;
use App\Service\OrgService;
use Predis\Client;
use Predis\ClientInterface;
use Psr\Log\LoggerInterface;
use Redis;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @Route("/admin")
 */
class IndexController extends AbstractController
{
    private OrgService $orgService;
    private UserVoter $userVoter;
    private Logger $logger;
    private RestClient $restClient;
    private UserApi$userApi;

    public function __construct(
        OrgService $orgService,
        UserVoter $userVoter,
        Logger $logger,
        RestClient $restClient,
        UserApi $userApi
    ) {
        $this->orgService = $orgService;
        $this->userVoter = $userVoter;
        $this->logger = $logger;
        $this->restClient = $restClient;
        $this->userApi = $userApi;
    }

    /**
     * @Route("/", name="admin_homepage")
     * @Security("is_granted('ROLE_ADMIN') or has_role('ROLE_AD')")
     * @Template("@App/Admin/Index/index.html.twig")
     */
    public function indexAction(Request $request)
    {
        $filters = [
            'limit' => 65,
            'offset' => $request->get('offset', 'id'),
            'role_name' => '',
            'q' => '',
            'ndr_enabled' => '',
            'include_clients' => '',
            'order_by' => 'registrationDate',
            'sort_order' => 'DESC',
        ];

        $form = $this->createForm(FormDir\Admin\SearchType::class, null, ['method' => 'GET']);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $filters = $form->getData() + $filters;
        }

        $users = $this->restClient->get('user/get-all?'.http_build_query($filters), 'User[]');

        return [
            'form' => $form->createView(),
            'users' => $users,
            'filters' => $filters,
        ];
    }

    /**
     * @Route("/user-add", name="admin_add_user")
     * @Security("is_granted('ROLE_ADMIN') or has_role('ROLE_AD')")
     * @Template("@App/Admin/Index/addUser.html.twig")
     *
     * @return array|RedirectResponse
     */
    public function addUserAction(Request $request)
    {
        $form = $this->createForm(FormDir\Admin\AddUserType::class, new EntityDir\User());

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            // add user
            try {
                if (!$this->isGranted(EntityDir\User::ROLE_SUPER_ADMIN) && EntityDir\User::ROLE_SUPER_ADMIN == $form->getData()->getRoleName()) {
                    throw new \RuntimeException('Cannot add admin from non-admin user');
                }

                $this->userApi->createAdminUser($form->getData());

                $this->addFlash(
                    'notice',
                    'An activation email has been sent to the user.'
                );

                return $this->redirect($this->generateUrl('admin_homepage'));
            } catch (RestClientException $e) {
                $form->get('email')->addError(new FormError($e->getData()['message']));
            }
        }

        return [
            'form' => $form->createView(),
        ];
    }

    /**
     * @Route("/user/{id}", name="admin_user_view", requirements={"id":"\d+"})
     * @Security("is_granted('ROLE_ADMIN')")
     * @Template("@App/Admin/Index/viewUser.html.twig")
     *
     * @param $id
     *
     * @return User[]|Response
     */
    public function viewAction($id)
    {
        try {
            return ['user' => $this->getPopulatedUser($id)];
        } catch (\Throwable $e) {
            return $this->renderNotFound();
        }
    }

    /**
     * @Route("/edit-user", name="admin_editUser", methods={"GET", "POST"})
     * @Security("is_granted('ROLE_ADMIN') or has_role('ROLE_AD')")
     * @Template("@App/Admin/Index/editUser.html.twig")
     *
     * @return array|Response
     *
     * @throws \Throwable
     */
    public function editUserAction(Request $request, TranslatorInterface $translator)
    {
        $filter = $request->get('filter');

        try {
            /* @var User $user */
            $user = $this->getPopulatedUser($filter);
        } catch (\Throwable $e) {
            return $this->renderNotFound();
        }

        try {
            $this->denyAccessUnlessGranted('edit-user', $user);
        } catch (\Throwable $e) {
            $accessErrorMessage = 'You do not have permission to edit this user';

            return $this->render('@App/Admin/Index/error.html.twig', [
                'error' => $accessErrorMessage,
            ]);
        }

        $form = $this->createForm(FormDir\Admin\EditUserType::class, $user, ['user' => $this->getUser()]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $updateUser = $form->getData();

            try {
                $this->restClient->put('user/'.$user->getId(), $updateUser, ['admin_edit_user']);
                $this->addFlash('notice', 'Your changes were saved');
                $this->redirectToRoute('admin_editUser', ['filter' => $user->getId()]);
            } catch (\Throwable $e) {
                switch ((int) $e->getCode()) {
                    case 422:
                        $form->get('email')->addError(new FormError($translator->trans('editUserForm.email.existingError', [], 'admin')));
                        break;

                    case 425:
                        $form->get('roleType')->addError(new FormError($translator->trans('editUserForm.roleType.mismatchError', [], 'admin')));
                        break;

                    default:
                        throw $e;
                }
            }
        }

        $view = [
            'form' => $form->createView(),
            'action' => 'edit',
            'id' => $user->getId(),
            'user' => $user,
            'deputyBaseUrl' => $this->container->getParameter('non_admin_host'),
        ];

        if ($user->isLayDeputy()) {
            $view['clientsCount'] = count($user->getClients());
        }

        return $view;
    }

    /**
     * @param $id
     */
    private function getPopulatedUser($id): User
    {
        /* @var User $user */
        $user = $this->restClient->get("user/{$id}", 'User', ['user-rolename']);

        /** @var array $groups */
        $groups = ($user->isDeputyOrg()) ? ['user', 'user-organisations'] : ['user', 'user-clients', 'client', 'client-reports'];

        return $this->restClient->get("user/{$id}", 'User', $groups);
    }

    private function renderNotFound(): Response
    {
        return $this->render('@App/Admin/Index/error.html.twig', [
            'error' => 'User not found',
        ]);
    }

    /**
     * @Route("/edit-ndr/{id}", name="admin_editNdr", methods={"POST"})
     * @Security("is_granted('ROLE_ADMIN') or has_role('ROLE_AD')")
     *
     * @param string $id
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function editNdrAction(Request $request, $id)
    {
        $ndr = $this->restClient->get('ndr/'.$id, 'Ndr\Ndr', ['ndr', 'client', 'client-users', 'user']);
        $ndrForm = $this->createForm(FormDir\NdrType::class, $ndr);
        if ('POST' == $request->getMethod()) {
            $ndrForm->handleRequest($request);

            if ($ndrForm->isSubmitted() && $ndrForm->isValid()) {
                $updateNdr = $ndrForm->getData();
                $this->restClient->put('ndr/'.$id, $updateNdr, ['start_date']);
                $this->addFlash('notice', 'Your changes were saved');
            }
        }
        /** @var EntityDir\Client $client */
        $client = $ndr->getClient();
        $users = $client->getUsers();

        return $this->redirect($this->generateUrl('admin_editUser', ['filter' => $users[0]->getId()]));
    }

    /**
     * @Route("/delete-confirm/{id}", name="admin_delete_confirm", methods={"GET"})
     * @Security("is_granted('ROLE_ADMIN') or has_role('ROLE_AD')")
     * @Template("@App/Admin/Index/deleteConfirm.html.twig")
     *
     * @param int $id
     *
     * @return array
     */
    public function deleteConfirmAction($id)
    {
        /** @var EntityDir\User $userToDelete */
        $userToDelete = $this->restClient->get("user/{$id}", 'User');

        $this->denyAccessUnlessGranted(UserVoter::DELETE_USER, $userToDelete, 'Unable to delete this user');

        return ['user' => $userToDelete];
    }

    /**
     * @Route("/delete/{id}", name="admin_delete", methods={"GET"})
     * @Security("is_granted('ROLE_ADMIN') or has_role('ROLE_AD')")
     *
     * @param int $id
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function deleteAction($id)
    {
        $user = $this->userApi->get($id, ['user', 'client', 'client-reports', 'report']);

        try {
            $this->userApi->delete($user, AuditEvents::TRIGGER_ADMIN_BUTTON);

            return $this->redirect($this->generateUrl('admin_homepage'));
        } catch (\Throwable $e) {
            $this->logger->warning(
                sprintf('Error while deleting deputy: %s', $e->getMessage()),
                ['deputy_email' => $user->getEmail()]
            );

            $this->addFlash('error', 'There was a problem deleting the deputy - please try again later');

            return $this->redirect($this->generateUrl('admin_homepage'));
        }
    }

    /**
     * @Route("/upload", name="admin_upload")
     * @Security("is_granted('ROLE_ADMIN') or has_role('ROLE_AD')")
     * @Template("@App/Admin/Index/upload.html.twig")
     */
    public function uploadAction(Request $request, RouterInterface $router)
    {
        $form = $this->createFormBuilder()
            ->add('type', ChoiceType::class, [
                'choice_translation_domain' => 'admin',
                'expanded' => true,
                'choices' => [
                    'upload.form.type.choices.lay' => 'lay',
                    'upload.form.type.choices.org' => 'org',
                ],
            ])
            ->add('save', SubmitType::class)
            ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if ('lay' === $form->get('type')->getData()) {
                return new RedirectResponse($router->generate('casrec_upload'));
            } elseif ('org' === $form->get('type')->getData()) {
                return new RedirectResponse($router->generate('admin_org_upload'));
            }
        }

        return [
            'form' => $form->createView(),
        ];
    }

    /**
     * @Route("/casrec-upload", name="casrec_upload")
     * @Security("is_granted('ROLE_ADMIN') or has_role('ROLE_AD')")
     * @Template("@App/Admin/Index/uploadUsers.html.twig")
     */
    public function uploadUsersAction(Request $request, ClientInterface $redisClient)
    {
        $chunkSize = 2000;

        $form = $this->createForm(FormDir\UploadCsvType::class, null, [
            'method' => 'POST',
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $fileName = $form->get('file')->getData();
            try {
                $csvToArray = new CsvToArray($fileName, false, true);

                $data = $csvToArray
                    ->setOptionalColumns($csvToArray->getFirstRow())
                    ->setUnexpectedColumns(['Last Report Day'])
                    ->getData();

                $source = isset($data[0]['Source']) ? strtolower($data[0]['Source']) : 'casrec';

                // small amount of data -> immediate posting and redirect (needed for behat)
                if (count($data) < $chunkSize) {
                    $compressedData = CsvUploader::compressData($data);

                    $this->restClient->delete('casrec/delete-by-source/'.$source);
                    $ret = $this->restClient->setTimeout(600)->post('v2/lay-deputyship/upload', $compressedData);
                    $this->addFlash(
                        'notice',
                        sprintf('%d record uploaded, %d error(s)', $ret['added'], count($ret['errors']))
                    );

                    foreach ($ret['errors'] as $err) {
                        $this->addFlash('error', $err);
                    }

                    return $this->redirect($this->generateUrl('casrec_upload'));
                }

                // big amount of data => store in redis + redirect
                $chunks = array_chunk($data, $chunkSize);

                foreach ($chunks as $k => $chunk) {
                    $compressedData = CsvUploader::compressData($chunk);
                    $redisClient->set('chunk'.$k, $compressedData);
                }

                return $this->redirect($this->generateUrl('casrec_upload', ['nOfChunks' => count($chunks), 'source' => $source]));
            } catch (\Throwable $e) {
                $message = $e->getMessage();
                if ($e instanceof RestClientException && isset($e->getData()['message'])) {
                    $message = $e->getData()['message'];
                }
                $form->get('file')->addError(new FormError($message));
            }
        }

        return [
            'nOfChunks' => $request->get('nOfChunks'),
            'source' => $request->get('source'),
            'currentRecords' => $this->restClient->get('casrec/count', 'array'),
            'form' => $form->createView(),
            'maxUploadSize' => min([ini_get('upload_max_filesize'), ini_get('post_max_size')]),
        ];
    }

    /**
     * @Route("/casrec-mld-upgrade", name="casrec_mld_upgrade")
     * @Security("is_granted('ROLE_ADMIN') or has_role('ROLE_AD')")
     * @Template("@App/Admin/Index/upgradeMld.html.twig")
     */
    public function upgradeMldAction(Request $request)
    {
        $form = $this->createForm(FormDir\UploadCsvType::class, null, [
            'method' => 'POST',
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $fileName = $form->get('file')->getData();
            try {
                $data = (new CsvToArray($fileName, true))
                    ->setExpectedColumns([
                        'Deputy No',
                    ])
                    ->getData();
                $compressedData = CsvUploader::compressData($data);
                $ret = $this->restClient->setTimeout(600)->post('codeputy/mldupgrade', $compressedData);
                $this->addFlash(
                    'notice',
                    sprintf('Your file contained %d deputy numbers, %d were updated, with %d error(s)', $ret['requested_mld_upgrades'], $ret['updated'], count($ret['errors']))
                );

                foreach ($ret['errors'] as $err) {
                    $this->addFlash(
                        'error',
                        $err
                    );
                }

                return $this->redirect($this->generateUrl('casrec_mld_upgrade'));
            } catch (\Throwable $e) {
                $message = $e->getMessage();
                if ($e instanceof RestClientException && isset($e->getData()['message'])) {
                    $message = $e->getData()['message'];
                }
                $form->get('file')->addError(new FormError($message));
            }
        }

        return [
            'currentMldUsers' => $this->restClient->get('codeputy/count', 'array'),
            'form' => $form->createView(),
            'maxUploadSize' => min([ini_get('upload_max_filesize'), ini_get('post_max_size')]),
        ];
    }

    /**
     * @Route("/org-csv-upload", name="admin_org_upload")
     * @Security("is_granted('ROLE_ADMIN') or has_role('ROLE_AD')")
     * @Template("@App/Admin/Index/uploadOrgUsers.html.twig")
     */
    public function uploadOrgUsersAction(Request $request)
    {
        $form = $this->createForm(FormDir\UploadCsvType::class, null, [
            'method' => 'POST',
        ]);

        $form->handleRequest($request);

        $outputStreamResponse = isset($_GET['ajax']);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $fileName = $form->get('file')->getData();

                $data = (new CsvToArray($fileName, false))
                    ->setExpectedColumns([
                        'Deputy No',
                        //'Pat Create', 'Dship Create', //should hold reg date / Cour order date, but no specs given yet
                        'Dep Postcode',
                        'Dep Forename',
                        'Dep Surname',
                        'Dep Type', // 23 = PA (but not confirmed)
                        'Dep Adrs1',
                        'Dep Adrs2',
                        'Dep Adrs3',
                        'Dep Adrs4',
                        'Dep Adrs5',
                        'Dep Postcode',
                        'Email', //mandatory, used as user ID whem uploading
                        'Email2',
                        'Email3',
                        'Case', //case number, used as ID when uploading
                        'Forename', 'Surname', //client forename and surname
                        'Corref',
                        'Typeofrep',
                        'Last Report Day',
                        'Made Date',
                    ])
                    ->setOptionalColumns([
                        'Client Adrs1',
                        'Client Adrs2',
                        'Client Adrs3',
                        'Client Postcode',
                        'Client Phone',
                        'Client Email',
                        'Client Date of Birth',
                        'Phone Main',
                        'Phone Alternative',
                        'Fee Payer',
                        'Corres',
                    ])
                    ->setUnexpectedColumns([
                        'NDR',
                    ])
                    ->getData();

                $this->orgService->setLogging($outputStreamResponse);

                $redirectUrl = $this->generateUrl('admin_org_upload');

                return $this->orgService->process($data, $redirectUrl);
            } catch (\Throwable $e) {
                $message = $e->getMessage();

                if ($e instanceof RestClientException && isset($e->getData()['message'])) {
                    $message = $e->getData()['message'];
                }

                if ($outputStreamResponse) {
                    $this->addFlash('error', $message);
                    exit();
                } else {
                    $form->get('file')->addError(new FormError($message));
                }
            }
        }

        return [
            'form' => $form->createView(),
        ];
    }

    /**
     * @Route("/send-activation-link/{email}", name="admin_send_activation_link")
     * @Security("is_granted('ROLE_ADMIN') or has_role('ROLE_AD')")
     **/
    public function sendUserActivationLinkAction(string $email, LoggerInterface $logger)
    {
        try {
            $this->userApi->activate($email, 'pass-reset');
        } catch (\Throwable $e) {
            $logger->debug($e->getMessage());
        }

        return new Response('[Link sent]');
    }
}
