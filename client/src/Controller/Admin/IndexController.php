<?php

namespace App\Controller\Admin;

use App\Controller\AbstractController;
use App\Entity as EntityDir;
use App\Entity\User;
use App\Event\AdminManagerDeletedEvent;
use App\Event\CSVUploadedEvent;
use App\EventDispatcher\ObservableEventDispatcher;
use App\Exception\RestClientException;
use App\Form as FormDir;
use App\Security\UserVoter;
use App\Service\Audit\AuditEvents;
use App\Service\Client\Internal\LayDeputyshipApi;
use App\Service\Client\Internal\PreRegistrationApi;
use App\Service\Client\Internal\UserApi;
use App\Service\Client\RestClient;
use App\Service\CsvUploader;
use App\Service\DataImporter\CsvToArray;
use App\Service\OrgService;
use Predis\ClientInterface;
use Psr\Log\LoggerInterface;
use RuntimeException;
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
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Throwable;

/**
 * @Route("/admin")
 */
class IndexController extends AbstractController
{
    public function __construct(
        private OrgService $orgService,
        private LoggerInterface $logger,
        private RestClient $restClient,
        private UserApi $userApi,
        private ObservableEventDispatcher $eventDispatcher,
        private PreRegistrationApi $preRegistrationApi,
        private LayDeputyshipApi $layDeputyshipApi,
        private TokenStorageInterface $tokenStorage
    ) {
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
                    throw new RuntimeException('Cannot add admin from non-admin user');
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
        } catch (Throwable $e) {
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
     * @throws Throwable
     */
    public function editUserAction(Request $request, TranslatorInterface $translator)
    {
        $filter = $request->get('filter');

        try {
            /* @var User $user */
            $user = $this->getPopulatedUser($filter);
        } catch (Throwable $e) {
            return $this->renderNotFound();
        }

        try {
            $this->denyAccessUnlessGranted('edit-user', $user);
        } catch (Throwable $e) {
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
            } catch (Throwable $e) {
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
     * @return RedirectResponse
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
     * @return RedirectResponse
     */
    public function deleteAction($id)
    {
        $user = $this->userApi->get($id, ['user', 'client', 'client-reports', 'report']);

        try {
            $this->userApi->delete($user, AuditEvents::TRIGGER_ADMIN_BUTTON);

            if (User::ROLE_ADMIN_MANAGER === $user->getRoleName()) {
                $this->dispatchAdminManagerDeletedEvent($user);
            }

            return $this->redirect($this->generateUrl('admin_homepage'));
        } catch (Throwable $e) {
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
                return new RedirectResponse($router->generate('pre_registration_upload'));
            } elseif ('org' === $form->get('type')->getData()) {
                return new RedirectResponse($router->generate('admin_org_upload'));
            }
        }

        return [
            'form' => $form->createView(),
        ];
    }

    /**
     * @Route("/pre-registration-upload", name="pre_registration_upload")
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

        // AjaxController redirects to this page after working through chunks - check if its completed to dispatch event
        if ('1' === $request->get('complete')) {
            $this->dispatchCSVUploadEvent();
        }

        if ($form->isSubmitted() && $form->isValid()) {
            $fileName = $form->get('file')->getData();
            try {
                $csvToArray = new CsvToArray($fileName, false, true);

                $data = $csvToArray
                    ->setOptionalColumns([
                        'Case',
                        'ClientSurname',
                        'DeputyUid',
                        'DeputySurname',
                        'DeputyAddress1',
                        'DeputyAddress2',
                        'DeputyAddress3',
                        'DeputyAddress4',
                        'DeputyAddress5',
                        'DeputyPostcode',
                        'ReportType',
                        'MadeDate',
                        'OrderType',
                        'CoDeputy',
                    ])
                    ->setUnexpectedColumns(['LastReportDay', 'DeputyOrganisation'])
                    ->getData();

                // small amount of data -> immediate posting and redirect (needed for behat)
                if (count($data) < $chunkSize) {
                    $compressedData = CsvUploader::compressData($data);
                    $this->preRegistrationApi->deleteAll();

                    $ret = $this->layDeputyshipApi->uploadLayDeputyShip($compressedData, 'below_2000_rows');

                    $this->addFlash(
                        'notice',
                        sprintf('%d record uploaded, %d error(s), %d skipped', $ret['added'], count($ret['errors']), count($ret['skipped']))
                    );

                    foreach ($ret['errors'] as $err) {
                        $this->logger->warning(
                            sprintf('Error while uploading csv: %s', $err)
                        );

                        $this->addFlash('error', $err);
                    }

                    $this->dispatchCSVUploadEvent();

                    return $this->redirect($this->generateUrl('pre_registration_upload'));
                }

                // big amount of data => store in redis + redirect
                $chunks = array_chunk($data, $chunkSize);

                foreach ($chunks as $k => $chunk) {
                    $compressedData = CsvUploader::compressData($chunk);
                    $redisClient->set('chunk'.$k, $compressedData);
                }

                return $this->redirect($this->generateUrl('pre_registration_upload', ['nOfChunks' => count($chunks)]));
            } catch (Throwable $e) {
                $this->logger->warning('DEBUG CSV UPLOAD...');
                $this->logger->warning($e);

                $message = $e->getMessage();

                if ($e instanceof RestClientException && isset($e->getData()['message'])) {
                    $message = $e->getData()['message'];
                }

                $form->get('file')->addError(new FormError($message));
            }
        }

        return [
            'nOfChunks' => $request->get('nOfChunks'),
            'currentRecords' => $this->preRegistrationApi->count(),
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
                        'Case',
                        'ClientForename',
                        'ClientSurname',
                        'ClientDateOfBirth',
                        'ClientPostcode',
                        'DeputyUid',
                        'DeputyType',
                        'DeputyEmail',
                        'DeputyOrganisation',
                        'DeputyForename',
                        'DeputySurname',
                        'DeputyPostcode',
                        'MadeDate',
                        'LastReportDay',
                        'ReportType',
                        'OrderType',
                    ])
                    ->setOptionalColumns([
                        'ClientAddress1',
                        'ClientAddress2',
                        'ClientAddress3',
                        'ClientAddress4',
                        'ClientAddress5',
                        'DeputyAddress1',
                        'DeputyAddress2',
                        'DeputyAddress3',
                        'DeputyAddress4',
                        'DeputyAddress5',
                    ])
                    ->setUnexpectedColumns([
                        'NDR',
                    ])
                    ->getData();

                $this->orgService->setLogging($outputStreamResponse);

                $redirectUrl = $this->generateUrl('admin_org_upload');

                return $this->orgService->process($data, $redirectUrl);
            } catch (Throwable $e) {
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
        } catch (Throwable $e) {
            $logger->debug($e->getMessage());
        }

        return new Response('[Link sent]');
    }

    private function dispatchCSVUploadEvent()
    {
        $csvUploadedEvent = new CSVUploadedEvent(
            User::TYPE_LAY,
            AuditEvents::EVENT_CSV_UPLOADED
        );

        $this->eventDispatcher->dispatch($csvUploadedEvent, CSVUploadedEvent::NAME);
    }

    private function dispatchAdminManagerDeletedEvent(User $userToDelete)
    {
        $trigger = AuditEvents::TRIGGER_ADMIN_MANAGER_MANUALLY_DELETED;
        $currentUser = $this->tokenStorage->getToken()->getUser();

        $adminManagerDeletedEvent = new AdminManagerDeletedEvent(
            $trigger,
            $currentUser,
            $userToDelete
        );

        $this->eventDispatcher->dispatch($adminManagerDeletedEvent, AdminManagerDeletedEvent::NAME);
    }
}
