<?php

namespace AppBundle\Controller\Admin;

use AppBundle\Controller\AbstractController;
use AppBundle\Entity as EntityDir;
use AppBundle\Entity\User;
use AppBundle\Exception\RestClientException;
use AppBundle\Form as FormDir;
use AppBundle\Security\UserVoter;
use AppBundle\Service\Audit\AuditEvents;
use AppBundle\Service\Client\RestClient;
use AppBundle\Service\CsvUploader;
use AppBundle\Service\DataImporter\CsvToArray;
use AppBundle\Service\Logger;
use AppBundle\Service\Mailer\MailFactory;
use AppBundle\Service\Mailer\MailSenderInterface;
use AppBundle\Service\OrgService;
use AppBundle\Service\Time\DateTimeProvider;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Translation\Translator;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\RouterInterface;

/**
 * @Route("/admin")
 */
class IndexController extends AbstractController
{
    /** @var OrgService */
    private $orgService;

    /** @var UserVoter */
    private $userVoter;

    /** @var Logger */
    private $logger;

    /** @var DateTimeProvider */
    private $dateTimeProvider;

    public function __construct(OrgService $orgService, UserVoter $userVoter, Logger $logger, DateTimeProvider $dateTimeProvider)
    {
        $this->orgService = $orgService;
        $this->userVoter = $userVoter;
        $this->logger = $logger;
        $this->dateTimeProvider = $dateTimeProvider;
    }

    /**
     * @Route("/", name="admin_homepage")
     * @Security("has_role('ROLE_ADMIN') or has_role('ROLE_AD')")
     * @Template("AppBundle:Admin/Index:index.html.twig")
     */
    public function indexAction(Request $request)
    {
        $filters = [
            'limit'           => 100,
            'offset'          => $request->get('offset', 'id'),
            'role_name'       => '',
            'q'               => '',
            'ndr_enabled'     => '',
            'include_clients' => '',
            'order_by'        => 'registrationDate',
            'sort_order'      => 'DESC',
        ];

        $form = $this->createForm(FormDir\Admin\SearchType::class, null, ['method' => 'GET']);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $filters = $form->getData() + $filters;
        }

        $users = $this->getRestClient()->get('user/get-all?' . http_build_query($filters), 'User[]');

        return [
            'form'    => $form->createView(),
            'users'   => $users,
            'filters' => $filters,
        ];
    }

    /**
     * @Route("/user-add", name="admin_add_user")
     * @Security("has_role('ROLE_ADMIN') or has_role('ROLE_AD')")
     * @Template("AppBundle:Admin/Index:addUser.html.twig")
     */
    public function addUserAction(
        Request $request,
        RestClient $restClient,
        MailFactory $mailFactory,
        MailSenderInterface $mailSender
    )
    {
        $form = $this->createForm(FormDir\Admin\AddUserType::class, new EntityDir\User());

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            // add user
            try {
                if (!$this->isGranted(EntityDir\User::ROLE_SUPER_ADMIN) && $form->getData()->getRoleName() == EntityDir\User::ROLE_SUPER_ADMIN) {
                    throw new \RuntimeException('Cannot add admin from non-admin user');
                }

                /** @var EntityDir\User $user */
                $user = $restClient->post('user', $form->getData(), ['admin_add_user'], 'User');

                $activationEmail = $mailFactory->createActivationEmail($user);
                $mailSender->send($activationEmail);

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
     * @Security("has_role('ROLE_ADMIN')")
     * @Template("AppBundle:Admin/Index:viewUser.html.twig")
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
     * @Security("has_role('ROLE_ADMIN') or has_role('ROLE_AD')")
     * @Template("AppBundle:Admin/Index:editUser.html.twig")
     *
     * @param Request $request
     * @return array|Response
     * @throws \Throwable
     */
    public function editUserAction(Request $request)
    {
        $filter = $request->get('filter');

        try {
            /* @var User $user */
            $user = $this->getPopulatedUser($filter);
        } catch (\Throwable $e) {
            return $this->renderNotFound();
        }

        if ($user->getRoleName() == EntityDir\User::ROLE_ADMIN && !$this->isGranted(EntityDir\User::ROLE_ADMIN)) {
            return $this->render('AppBundle:Admin/Index:error.html.twig', [
                'error' => 'Non-admin cannot edit admin users',
            ]);
        }

        $form = $this->createForm(FormDir\Admin\EditUserType::class, $user, ['user' => $this->getUser()]);

        $oldEmail = $user->getEmail();
        $fullName = $user->getFullName();

        $form->handleRequest($request);

        $newEmail = $user->getEmail();

        if ($form->isSubmitted() && $form->isValid()) {
            $updateUser = $form->getData();

            try {
                $this->getRestClient()->put('user/' . $user->getId(), $updateUser, ['admin_edit_user']);

                if ($oldEmail !== $newEmail) {
                    $event = (new AuditEvents($this->dateTimeProvider))->userEmailChanged(
                        AuditEvents::TRIGGER_ADMIN_USER_EDIT,
                        $oldEmail,
                        $newEmail,
                        $this->getUser()->getEmail(),
                        $fullName,
                        $updateUser->getRoleName()
                    );

                    $this->logger->notice('', $event);
                }

                $this->addFlash('notice', 'Your changes were saved');

                $this->redirectToRoute('admin_editUser', ['filter' => $user->getId()]);
            } catch (\Throwable $e) {
                /** @var Translator $translator */
                $translator = $this->get('translator');
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
            'form'          => $form->createView(),
            'action'        => 'edit',
            'id'            => $user->getId(),
            'user'          => $user,
            'deputyBaseUrl' => $this->container->getParameter('non_admin_host'),
        ];

        if ($user->isLayDeputy()) {
            $view['clientsCount'] = count($user->getClients());
        }

        return $view;
    }

    /**
     * @param $id
     * @return User
     */
    private function getPopulatedUser($id): User
    {
        /* @var User $user */
        $user = $this->getRestClient()->get("user/{$id}", "User", ["user-rolename"]);

        /** @var array $groups */
        $groups = ($user->isDeputyOrg()) ? ["user", "user-organisations"] : ["user", "user-clients", "client", "client-reports"];

        return $this->getRestClient()->get("user/{$id}", "User", $groups);
    }

    /**
     * @return Response
     */
    private function renderNotFound(): Response
    {
        return $this->render('AppBundle:Admin/Index:error.html.twig', [
            'error' => 'User not found',
        ]);
    }

    /**
     * @Route("/edit-ndr/{id}", name="admin_editNdr", methods={"POST"})
     * @Security("has_role('ROLE_ADMIN') or has_role('ROLE_AD')")
     *
     * @param Request $request
     * @param string $id
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function editNdrAction(Request $request, $id)
    {
        $ndr = $this->getRestClient()->get('ndr/' . $id, 'Ndr\Ndr', ['ndr', 'client', 'client-users', 'user']);
        $ndrForm = $this->createForm(FormDir\NdrType::class, $ndr);
        if ($request->getMethod() == 'POST') {
            $ndrForm->handleRequest($request);

            if ($ndrForm->isSubmitted() && $ndrForm->isValid()) {
                $updateNdr = $ndrForm->getData();
                $this->getRestClient()->put('ndr/' . $id, $updateNdr, ['start_date']);
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
     * @Security("has_role('ROLE_ADMIN') or has_role('ROLE_AD')")
     * @Template("AppBundle:Admin/Index:deleteConfirm.html.twig")
     *
     * @param int $id
     *
     * @return array
     */
    public function deleteConfirmAction($id)
    {
        /** @var EntityDir\User $userToDelete */
        $userToDelete = $this->getRestClient()->get("user/{$id}", 'User');

        $this->denyAccessUnlessGranted(UserVoter::DELETE_USER, $userToDelete, 'Unable to delete this user');

        return ['user' => $userToDelete];
    }

    /**
     * @Route("/delete/{id}", name="admin_delete", methods={"GET"})
     * @Security("has_role('ROLE_ADMIN') or has_role('ROLE_AD')")
     *
     * @param int $id
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function deleteAction($id)
    {
        $deputy = $this->getRestClient()->get("user/{$id}", 'User', ['user', 'client', 'client-reports', 'report']);

        try {
            $this->getRestClient()->delete('user/' . $id);

            $event = (new AuditEvents($this->dateTimeProvider))->deputyDeleted(
                AuditEvents::TRIGGER_ADMIN_BUTTON,
                $this->getUser()->getEmail(),
                $deputy->getFullName(),
                $deputy->getEmail(),
                $deputy->getRoleName(),
            );

            $this->logger->notice('', $event);
            return $this->redirect($this->generateUrl('admin_homepage'));
        } catch (\Throwable $e) {
            $this->logger->warning(
                sprintf('Error while deleting deputy: %s', $e->getMessage()), ['deputy_email' => $deputy->getEmail()]
            );
        }
    }

    /**
     * @Route("/upload", name="admin_upload")
     * @Security("has_role('ROLE_ADMIN') or has_role('ROLE_AD')")
     * @Template("AppBundle:Admin/Index:upload.html.twig")
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
                ]
            ])
            ->add('save', SubmitType::class)
            ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if ($form->get('type')->getData() === 'lay') {
                return new RedirectResponse($router->generate('casrec_upload'));
            } else if ($form->get('type')->getData() === 'org') {
                return new RedirectResponse($router->generate('admin_org_upload'));
            }
        }

        return [
            'form' => $form->createView(),
        ];
    }

    /**
     * @Route("/casrec-upload", name="casrec_upload")
     * @Security("has_role('ROLE_ADMIN') or has_role('ROLE_AD')")
     * @Template("AppBundle:Admin/Index:uploadUsers.html.twig")
     */
    public function uploadUsersAction(Request $request)
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

                    $this->getRestClient()->delete('casrec/delete-by-source/'.$source);
                    $ret = $this->getRestClient()->setTimeout(600)->post('v2/lay-deputyship/upload', $compressedData);
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

                /** @var \Redis $redis */
                $redis = $this->get('snc_redis.default');
                foreach ($chunks as $k => $chunk) {
                    $compressedData = CsvUploader::compressData($chunk);
                    $redis->set('chunk' . $k, $compressedData);
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
            'nOfChunks'      => $request->get('nOfChunks'),
            'source'         => $request->get('source'),
            'currentRecords' => $this->getRestClient()->get('casrec/count', 'array'),
            'form'           => $form->createView(),
            'maxUploadSize'  => min([ini_get('upload_max_filesize'), ini_get('post_max_size')]),
        ];
    }

    /**
     * @Route("/casrec-mld-upgrade", name="casrec_mld_upgrade")
     * @Security("has_role('ROLE_ADMIN') or has_role('ROLE_AD')")
     * @Template("AppBundle:Admin/Index:upgradeMld.html.twig")
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
                        'Deputy No'
                    ])
                    ->getData();
                $compressedData = CsvUploader::compressData($data);
                $ret = $this->getRestClient()->setTimeout(600)->post('codeputy/mldupgrade', $compressedData);
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
            'currentMldUsers' => $this->getRestClient()->get('codeputy/count', 'array'),
            'form'            => $form->createView(),
            'maxUploadSize'   => min([ini_get('upload_max_filesize'), ini_get('post_max_size')]),
        ];
    }

    /**
     * @Route("/org-csv-upload", name="admin_org_upload")
     * @Security("has_role('ROLE_ADMIN') or has_role('ROLE_AD')")
     * @Template("AppBundle:Admin/Index:uploadOrgUsers.html.twig")
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
                        'Made Date'
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
                        'Corres'
                    ])
                    ->setUnexpectedColumns([
                        'NDR'
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
                    die();
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
     * @Security("has_role('ROLE_ADMIN') or has_role('ROLE_AD')")
     **/
    public function sendUserActivationLinkAction(
        $email,
        MailFactory $mailFactory,
        MailSenderInterface $mailSender,
        LoggerInterface $logger,
        RestClient $restClient
    )
    {
        try {
            $user = $restClient->userRecreateToken($email, 'pass-reset');
            $resetPasswordEmail = $mailFactory->createActivationEmail($user);

            $mailSender->send($resetPasswordEmail);
        } catch (\Throwable $e) {
            $logger->debug($e->getMessage());
        }

        return new Response('[Link sent]');
    }
}
