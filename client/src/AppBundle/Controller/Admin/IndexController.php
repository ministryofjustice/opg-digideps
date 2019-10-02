<?php

namespace AppBundle\Controller\Admin;

use AppBundle\Controller\AbstractController;
use AppBundle\Entity as EntityDir;
use AppBundle\Exception\DisplayableException;
use AppBundle\Exception\RestClientException;
use AppBundle\Form as FormDir;
use AppBundle\Model\Email;
use AppBundle\Service\CsvUploader;
use AppBundle\Service\DataImporter\CsvToArray;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @Route("/admin")
 */
class IndexController extends AbstractController
{
    /**
     * @Route("/", name="admin_homepage")
     * @Security("has_role('ROLE_ADMIN') or has_role('ROLE_AD')")
     * @Template("AppBundle:Admin/Index:index.html.twig")
     */
    public function indexAction(Request $request)
    {
        $filters = [
            'limit'       => 100,
            'offset'      => $request->get('offset', 'id'),
            'role_name'   => '',
            'q'           => '',
            'ndr_enabled' => '',
            'order_by'    => 'registrationDate',
            'sort_order'  => 'DESC',
        ];

        $form = $this->createForm(FormDir\Admin\SearchType::class, null, ['method' => 'GET']);
        $form->handleRequest($request);
        if ($form->isValid()) {
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
    public function addUserAction(Request $request)
    {
        $availableRoles = [
            EntityDir\User::ROLE_LAY_DEPUTY => 'Lay Deputy',
            EntityDir\User::ROLE_AD         => 'Assisted Digital',
        ];
        // only admins can add other admins
        if ($this->isGranted(EntityDir\User::ROLE_ADMIN)) {
            $availableRoles[EntityDir\User::ROLE_ADMIN] = 'OPG Admin';
            $availableRoles[EntityDir\User::ROLE_CASE_MANAGER] = 'Case manager';
        }

        $form = $this->createForm(FormDir\Admin\AddUserType::class,
            new EntityDir\User(), [
                'options' => [
                    'roleChoices' => $availableRoles,
                    'roleNameEmptyValue' => $this->get('translator')->trans('addUserForm.roleName.defaultOption', [], 'admin')
                ]
            ]
        );

        $form->handleRequest($request);
        if ($form->isValid()) {
            // add user
            try {
                if (!$this->isGranted(EntityDir\User::ROLE_ADMIN) && $form->getData()->getRoleName() == EntityDir\User::ROLE_ADMIN) {
                    throw new \RuntimeException('Cannot add admin from non-admin user');
                }
                $user = $this->getRestClient()->post('user', $form->getData(), ['admin_add_user'], 'User');

                $activationEmail = $this->getMailFactory()->createActivationEmail($user);
                $this->getMailSender()->send($activationEmail, ['text', 'html']);

                $request->getSession()->getFlashBag()->add(
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
     * @Route("/edit-user", name="admin_editUser", methods={"GET", "POST"})
     * @Security("has_role('ROLE_ADMIN') or has_role('ROLE_AD')")
     * @Template("AppBundle:Admin/Index:editUser.html.twig")
     *
     * @param Request $request
     */
    public function editUserAction(Request $request)
    {
        $filter = $request->get('filter');

        try {
            /* @var $user EntityDir\User */
            $user = $this->getRestClient()->get("v2/deputy/{$filter}", 'User');
        } catch (\Throwable $e) {
            return $this->render('AppBundle:Admin/Index:error.html.twig', [
                'error' => 'User not found',
            ]);
        }

        if ($user->getRoleName() == EntityDir\User::ROLE_ADMIN && !$this->isGranted(EntityDir\User::ROLE_ADMIN)) {
            return $this->render('AppBundle:Admin/Index:error.html.twig', [
                'error' => 'Non-admin cannot edit admin users',
            ]);
        }

        // no role editing for current user and PA
        $roleNameSetTo = null;
        if ($user->getId() == $this->getUser()->getId() || $user->getRoleName() == EntityDir\User::ROLE_PA_NAMED) {
            $roleNameSetTo = $user->getRoleName();
        }
        $form = $this->createForm(FormDir\Admin\AddUserType::class, $user, ['options' => [
            'roleChoices'        => [
                EntityDir\User::ROLE_ADMIN      => 'OPG Admin',
                EntityDir\User::ROLE_CASE_MANAGER   => 'Case manager',
                EntityDir\User::ROLE_LAY_DEPUTY => 'Lay Deputy',
                EntityDir\User::ROLE_AD         => 'Assisted Digital',
                EntityDir\User::ROLE_PA_NAMED   => 'Public Authority (named)',
                EntityDir\User::ROLE_PROF_NAMED => 'Professional Deputy (named)',
            ],
            'roleNameEmptyValue' => $this->get('translator')->trans('addUserForm.roleName.defaultOption', [], 'admin'),
            'roleNameSetTo'      => $roleNameSetTo, //can't edit current user's role
            'ndrEnabledType'     => $user->getRoleName() == EntityDir\User::ROLE_LAY_DEPUTY ? 'checkbox' : 'hidden',
        ]]);

        $clients = $user->getClients();
        $ndr = null;
        $ndrForm = null;
        if (count($clients)) {
            $ndr = $clients[0]->getNdr();
            if ($ndr) {
                $ndrForm = $this->createForm(FormDir\NdrType::class, $ndr, [
                    'action' => $this->generateUrl('admin_editNdr', ['id' => $ndr->getId()]),
                ]);
            }
        }

        if ($request->getMethod() == 'POST') {
            $form->handleRequest($request);

            if ($form->isValid()) {
                $updateUser = $form->getData();

                try {
                    $this->getRestClient()->put('user/' . $user->getId(), $updateUser, ['admin_add_user']);

                    $request->getSession()->getFlashBag()->add('notice', 'Your changes were saved');

                    $this->redirect($this->generateUrl('admin_editUser', ['filter' => $user->getId()]));
                } catch (\Throwable $e) {
                    switch ((int) $e->getCode()) {
                        case 422:
                            $form->get('email')->addError(new FormError($this->get('translator')->trans('editUserForm.email.existingError', [], 'admin')));
                            break;

                        default:
                            throw $e;
                    }
                }
            }
        }
        $view = [
            'form'          => $form->createView(),
            'action'        => 'edit',
            'id'            => $user->getId(),
            'user'          => $user,
            'clientsCount'  => count($clients),
            'deputyBaseUrl' => $this->container->getParameter('non_admin_host'),
        ];

        if ($ndr && $ndrForm) {
            $view['ndrForm'] = $ndrForm->createView();
        }

        return $view;
    }

    /**
     * @Route("/edit-ndr/{id}", name="admin_editNdr", methods={"POST"})
     * @Security("has_role('ROLE_ADMIN') or has_role('ROLE_AD')")
     *
     * @param Request $request
     * @param $id
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function editNdrAction(Request $request, $id)
    {
        $ndr = $this->getRestClient()->get('ndr/' . $id, 'Ndr\Ndr', ['ndr', 'client', 'client-users', 'user']);
        $ndrForm = $this->createForm(FormDir\NdrType::class, $ndr);
        if ($request->getMethod() == 'POST') {
            $ndrForm->handleRequest($request);

            if ($ndrForm->isValid()) {
                $updateNdr = $ndrForm->getData();
                $this->getRestClient()->put('ndr/' . $id, $updateNdr, ['start_date']);
                $request->getSession()->getFlashBag()->add('notice', 'Your changes were saved');
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
        $userToDelete = $this->getRestClient()->get("user/{$id}", 'User');

        if (!$this->get('security.authorization_checker')->isGranted('ROLE_ADMIN')) {
            throw new DisplayableException('Only Admin can delete users');
        }

        if ($this->getUser()->getId() == $userToDelete->getId()) {
            throw new DisplayableException('Cannot delete logged user');
        }

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
        $user = $this->getRestClient()->get("user/{$id}", 'User', ['user', 'client', 'client-reports', 'report']);

        $this->getRestClient()->delete('user/' . $id);

        return $this->redirect($this->generateUrl('admin_homepage'));
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

        if ($form->isValid()) {
            $fileName = $form->get('file')->getData();
            try {
                $csvToArray = new CsvToArray($fileName, false, true);

                $data = $csvToArray->setExpectedColumns([
                        'Case',
                        'Surname',
                        'Deputy No',
                        'Dep Surname',
                        'Dep Postcode',
                        'Typeofrep',
                        'Corref',
                        'NDR', // if not present, would indicate a prof/PA CSV is being used incorrectly here
                        'Dep Type',
                        'Dep Adrs1',
                        'Dep Adrs2',
                        'Dep Adrs3'
                    ])
                    ->setOptionalColumns($csvToArray->getFirstRow())
                    ->setUnexpectedColumns([
                        //'Pat Create', 'Dship Create', //should hold reg date / Cour order date, but no specs given yet
                        'Last Report Day'
                    ])
                    ->getData();

                // small amount of data -> immediate posting and redirect (needed for behat)
                if (count($data) < $chunkSize) {
                    $compressedData = CsvUploader::compressData($data);

                    $this->getRestClient()->delete('casrec/truncate');
                    $ret = $this->getRestClient()->setTimeout(600)->post('v2/lay-deputyship/upload', $compressedData);
                    $request->getSession()->getFlashBag()->add(
                        'notice',
                        sprintf('%d record uploaded, %d error(s)', $ret['added'], count($ret['errors']))
                    );

                    foreach ($ret['errors'] as $err) {
                        $request->getSession()->getFlashBag()->add(
                            'error',
                            $err
                        );
                    }

                    return $this->redirect($this->generateUrl('casrec_upload'));
                }

                // big amount of data => store in redis + redirect
                $chunks = array_chunk($data, $chunkSize);
                foreach ($chunks as $k => $chunk) {
                    $compressedData = CsvUploader::compressData($chunk);
                    $this->get('snc_redis.default')->set('chunk' . $k, $compressedData);
                }


                return $this->redirect($this->generateUrl('casrec_upload', ['nOfChunks' => count($chunks)]));
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

        if ($form->isValid()) {
            $fileName = $form->get('file')->getData();
            try {
                $data = (new CsvToArray($fileName, true))
                    ->setExpectedColumns([
                        'Deputy No'
                    ])
                    ->getData();
                $compressedData = CsvUploader::compressData($data);
                $ret = $this->getRestClient()->setTimeout(600)->post('codeputy/mldupgrade', $compressedData);
                $request->getSession()->getFlashBag()->add(
                    'notice',
                    sprintf('Your file contained %d deputy numbers, %d were updated, with %d error(s)', $ret['requested_mld_upgrades'], $ret['updated'], count($ret['errors']))
                );

                foreach ($ret['errors'] as $err) {
                    $request->getSession()->getFlashBag()->add(
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
        $chunkSize = 100;

        $form = $this->createForm(FormDir\UploadCsvType::class, null, [
            'method' => 'POST',
        ]);

        $form->handleRequest($request);

        if ($form->isValid()) {
            $fileName = $form->get('file')->getData();
            try {
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
                        'Dep Postcode',
                        'Email', //mandatory, used as user ID whem uploading
                        'Case', //case number, used as ID when uploading
                        'Forename', 'Surname', //client forename and surname
                        'Corref',
                        'Typeofrep',
                        'Last Report Day',
                    ])
                    ->setOptionalColumns([
                        'Client Adrs1',
                        'Client Adrs2',
                        'Client Adrs3',
                        'Client Postcode',
                        'Client Phone',
                        'Client Email',
                        'Client Date of Birth',
                    ])
                    ->setUnexpectedColumns([
                        'NDR'
                    ])
                    ->getData();

                // small chunk => upload in same request
                if (count($data) < $chunkSize) {
                    $compressedData = CsvUploader::compressData($data);
                    $this->get('org_service')->uploadAndSetFlashMessages($compressedData, $request->getSession()->getFlashBag());
                    return $this->redirect($this->generateUrl('admin_org_upload'));
                }

                // big amount of data => save data into redis and redirect with nOfChunks param so that JS can do the upload with small AJAX calls
                $chunks = array_chunk($data, $chunkSize);

                foreach ($chunks as $k => $chunk) {

                    $compressedData = CsvUploader::compressData($chunk);
                    $this->get('snc_redis.default')->set('org_chunk' . $k, $compressedData);
                }
                return $this->redirect($this->generateUrl('admin_org_upload', ['nOfChunks' => count($chunks)]));
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
            'form'          => $form->createView(),
            'maxUploadSize' => min([ini_get('upload_max_filesize'), ini_get('post_max_size')]),
        ];
    }

    /**
     * @Route("/send-activation-link/{email}", name="admin_send_activation_link")
     * @Security("has_role('ROLE_ADMIN') or has_role('ROLE_AD')")
     **/
    public function sendUserActivationLinkAction(Request $request, $email)
    {
        try {
            /* @var $user EntityDir\User */
            $user = $this->getRestClient()->userRecreateToken($email, 'pass-reset');
            $resetPasswordEmail = $this->getMailFactory()->createActivationEmail($user);

            $this->getMailSender()->send($resetPasswordEmail, ['text', 'html']);
        } catch (\Throwable $e) {
            $this->get('logger')->debug($e->getMessage());
        }

        return new Response('[Link sent]');
    }
}
