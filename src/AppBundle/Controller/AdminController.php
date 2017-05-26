<?php

namespace AppBundle\Controller;

use AppBundle\Entity as EntityDir;
use AppBundle\Exception\DisplayableException;
use AppBundle\Exception\RestClientException;
use AppBundle\Form as FormDir;
use AppBundle\Model\Email;
use AppBundle\Service\CsvUploader;
use AppBundle\Service\DataImporter\CsvToArray;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @Route("/admin")
 */
class AdminController extends AbstractController
{
    /**
     * @Route("/", name="admin_homepage")
     * @Template
     */
    public function indexAction(Request $request)
    {
        $filters = [
            'limit'       => 100,
            'offset'      => $request->get('offset', 'id'),
            'role_name'   => '',
            'q'           => '',
            'odr_enabled' => '',
            'order_by'    => 'id',
            'sort_order'  => 'DESC',
        ];

        $form = $this->createForm(new FormDir\Admin\SearchType(), null, ['method' => 'GET']);
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
     * @Template
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
        }


        $form = $this->createForm(new FormDir\Admin\AddUserType([
            'roleChoices'        => $availableRoles,
            'roleNameEmptyValue' => $this->get('translator')->trans('addUserForm.roleName.defaultOption', [], 'admin'),
        ]), new EntityDir\User());

        if ($request->isMethod('POST')) {
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
        }

        return [
            'form' => $form->createView(),
        ];
    }

    /**
     * @Route("/edit-user", name="admin_editUser")
     * @Method({"GET", "POST"})
     * @Template
     *
     * @param Request $request
     */
    public function editUserAction(Request $request)
    {
        $what = $request->get('what');
        $filter = $request->get('filter');

        try {
            /* @var $user EntityDir\User */
            $user = $this->getRestClient()->get("user/get-one-by/{$what}/{$filter}", 'User', ['user', 'client', 'report', 'odr']);
        } catch (\Exception $e) {
            return $this->render('AppBundle:Admin:error.html.twig', [
                'error' => 'User not found',
            ]);
        }

        if ($user->getRoleName() == EntityDir\User::ROLE_ADMIN && !$this->isGranted(EntityDir\User::ROLE_ADMIN)) {
            return $this->render('AppBundle:Admin:error.html.twig', [
                'error' => 'Non-admin cannot edit admin users',
            ]);
        }

        // no role editing for current user and PA
        $roleNameSetTo = null;
        if ($user->getId() == $this->getUser()->getId() || $user->getRoleName() == EntityDir\User::ROLE_PA) {
            $roleNameSetTo = $user->getRoleName();
        }
        $form = $this->createForm(new FormDir\Admin\AddUserType([
            'roleChoices'        => [
                EntityDir\User::ROLE_ADMIN      => 'OPG Admin',
                EntityDir\User::ROLE_LAY_DEPUTY => 'Lay Deputy',
                EntityDir\User::ROLE_AD         => 'Assisted Digital',
                EntityDir\User::ROLE_PA         => 'Public Authority',
            ],
            'roleNameEmptyValue' => $this->get('translator')->trans('addUserForm.roleName.defaultOption', [], 'admin'),
            'roleNameSetTo'      => $roleNameSetTo, //can't edit current user's role
            'odrEnabledType'     => $user->getRoleName() == EntityDir\User::ROLE_LAY_DEPUTY ? 'checkbox' : 'hidden',
        ]), $user);

        $clients = $user->getClients();
        $odr = null;
        $odrForm = null;
        if (count($clients)) {
            $odr = $clients[0]->getOdr();
            if ($odr) {
                $odrForm = $this->createForm(new FormDir\OdrType(), $odr, [
                    'action' => $this->generateUrl('admin_editOdr', ['id' => $odr->getId()]),
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

                    $this->redirect($this->generateUrl('admin_editUser', ['what' => 'user_id', 'filter' => $user->getId()]));
                } catch (\Exception $e) {
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
            'deputyBaseUrl' => $this->container->getParameter('non_admin_host'),
        ];

        if ($odr && $odrForm) {
            $view['odrForm'] = $odrForm->createView();
        }

        return $view;
    }

    /**
     * @Route("/edit-odr/{id}", name="admin_editOdr")
     * @Method({"POST"})
     *
     * @param Request $request
     */
    public function editOdrAction(Request $request, $id)
    {
        $odr = $this->getRestClient()->get('odr/' . $id, 'Odr\Odr', ['odr', 'client', 'user']);
        $odrForm = $this->createForm(new FormDir\OdrType(), $odr);
        if ($request->getMethod() == 'POST') {
            $odrForm->handleRequest($request);

            if ($odrForm->isValid()) {
                $updateOdr = $odrForm->getData();
                $this->getRestClient()->put('odr/' . $id, $updateOdr, ['start_date']);
                $request->getSession()->getFlashBag()->add('notice', 'Your changes were saved');
            }
        }
        /** @var EntityDir\Client $client */
        $client = $odr->getClient();
        $users = $client->getUsers();

        return $this->redirect($this->generateUrl('admin_editUser', ['what' => 'user_id', 'filter' => $users[0]]));
    }

    /**
     * @Route("/delete-confirm/{id}", name="admin_delete_confirm")
     * @Method({"GET"})
     * @Template()
     *
     * @param type $id
     */
    public function deleteConfirmAction($id)
    {
        $userToDelete = $this->getRestClient()->get("user/{$id}", 'User');

        if (!$this->get('security.context')->isGranted('ROLE_ADMIN')) {
            throw new DisplayableException('Only Admin can delete users');
        }

        if ($this->getUser()->getId() == $userToDelete->getId()) {
            throw new DisplayableException('Cannot delete logged user');
        }

        return ['user' => $userToDelete];
    }

    /**
     * @Route("/delete/{id}", name="admin_delete")
     * @Method({"GET"})
     * @Template()
     *
     * @param int $id
     */
    public function deleteAction($id)
    {
        $user = $this->getRestClient()->get("user/{$id}", 'User', ['user', 'client', 'report']);

        $this->getRestClient()->delete('user/' . $id);

        return $this->redirect($this->generateUrl('admin_homepage'));
    }

    /**
     * @Route("/casrec-upload", name="casrec_upload")
     * @Template
     */
    public function uploadUsersAction(Request $request)
    {
        $chunkSize = 1000;

        $form = $this->createForm(new FormDir\UploadCsvType(), null, [
            'method' => 'POST',
        ]);

        $form->handleRequest($request);

        if ($form->isValid()) {
            $fileName = $form->get('file')->getData();
            try {
                $data = (new CsvToArray($fileName, true))
                    ->setExpectedColumns([
                        'Case',
                        'Surname',
                        'Deputy No',
                        'Dep Surname',
                        'Dep Postcode',
                        'Typeofrep',
                        'Corref',
                    ])
                    ->getData();

                // small amount of data -> immediate posting and redirect (needed for behat)
                if (count($data) < $chunkSize) {
                    $compressedData = CsvUploader::compressData($data);
                    $this->getRestClient()->delete('casrec/truncate');
                    $ret = $this->getRestClient()->setTimeout(600)->post('casrec/bulk-add', $compressedData);
                    $request->getSession()->getFlashBag()->add(
                        'notice',
                        sprintf('%d record uploaded, %d error(s)', $ret['added'], count($ret['errors']))
                    );

                    return $this->redirect($this->generateUrl('casrec_upload'));
                }

                // big amount of data => redirect with nOfChunks for ajax upload in chunks
                $chunks = array_chunk($data, $chunkSize);
                foreach ($chunks as $k => $chunk) {
                    $compressedData = CsvUploader::compressData($chunk);
                    $this->get('snc_redis.default')->set('chunk' . $k, $compressedData);
                }

                return $this->redirect($this->generateUrl('casrec_upload', ['nOfChunks' => count($chunks)]));
            } catch (\Exception $e) {
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
     * @Route("/pa-upload", name="admin_pa_upload")
     * @Template
     */
    public function uploadPaUsersAction(Request $request)
    {
        $chunkSize = 2;

        $form = $this->createForm(new FormDir\UploadCsvType(), null, [
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
                        //'Dep Adrs1', 'Dep Adrs2', 'Dep Adrs3', 'Dep Adrs4', 'Dep Adrs5', // recognised but not mandatory
                        'Email', //mandatory, used as user ID whem uploading
                        'Case', //case number, used as ID when uploading
                        'Forename', 'Surname', //client forename and surname
                        'Corref',
                        'Typeofrep',
                        'Report Due',
                    ])
                    ->setOptionalColumns([
                        'Client Adrs1',
                        'Client Adrs2',
                        'Client Adrs3',
                        'Client Postcode',
                        'Dep Adrs1',
                        'Dep Adrs2',
                        'Dep Adrs3',
                        'Dep Postcode'
                    ])
                    ->getData();

                $added = ['users' => [], 'clients' => [], 'reports' => []];
                $errors = [];
                $warnings = [];
                $chunksProcessed = 0;
                $postData['chunkSize'] = $chunkSize;
                foreach (array_chunk($data, $chunkSize) as $chunk) {
                    $postData['compressedData'] = CsvUploader::compressData($chunk);
                    $postData['line'] = $chunksProcessed * $chunkSize;

                    $ret = $this->getRestClient()->setTimeout(600)->post('pa/bulk-add', $postData);

                    $added['users'] = array_merge($added['users'], $ret['added']['users']);
                    $added['clients'] = array_merge($added['clients'], $ret['added']['clients']);
                    $added['reports'] = array_merge($added['reports'], $ret['added']['reports']);
                    $errors = array_merge($errors, $ret['errors']);
                    $warnings = array_merge($warnings, $ret['warnings']);
                    $chunksProcessed++;
                }

                // notifications
                $request->getSession()->getFlashBag()->add(
                    'notice',
                    sprintf('Added %d PA users, %d clients, %d reports. Go to users tab to enable them',
                        count($added['users']),
                        count($added['clients']),
                        count($added['reports'])
                    )
                );
                if (!empty($errors)) {
                    $request->getSession()->getFlashBag()->add(
                        'error',
                        implode('<br/>', $errors)
                    );
                }

                if (!empty($warnings)) {
                    $request->getSession()->getFlashBag()->add(
                        'warning',
                        implode('<br/>', $warnings)
                    );
                }

                return $this->redirect($this->generateUrl('admin_pa_upload'));
            } catch (\Exception $e) {
                $message = $e->getMessage();
                if ($e instanceof RestClientException && isset($e->getData()['message'])) {
                    $message = $e->getData()['message'];
                }
                $form->get('file')->addError(new FormError($message));
            }
        }

        return [
            'form'          => $form->createView(),
            'maxUploadSize' => min([ini_get('upload_max_filesize'), ini_get('post_max_size')]),
        ];
    }

    /**
     * @Route("/stats", name="admin_stats")
     * @Template
     */
    public function statsAction(Request $request)
    {
        $data = $this->getRestClient()->get('stats/users?limit=100', 'array');

        return [
            'data' => $data,
        ];
    }

    /**
     * @Route("/stats/csv-download/{timestamp}", name="admin_stats_csv")
     * @Template
     */
    public function statsCsvAction(Request $request, $timestamp)
    {
        try {
            $rawCsv = $this->getRestClient()->get("stats/users/csv/{$timestamp}", 'raw');
        } catch (\RuntimeException $e) {
            return $this->render('AppBundle:Admin:stats-wait.html.twig', [
                'timestamp' => $timestamp,
            ]);
        }

        $response = new Response();
        $response->headers->set('Cache-Control', 'private');
        $response->headers->set('Content-type', 'plain/text');
        $response->headers->set('Content-type', 'application/octet-stream');
        $response->headers->set('Content-Disposition', 'attachment; filename="dd-stats-' . date('Y-m-d') . '.csv";');
        $response->sendHeaders();
        $response->setContent($rawCsv);

        return $response;
    }

    /**
     * @Route("/send-activation-link/{email}", name="admin_send_activation_link")
     **/
    public function passwordForgottenAction(Request $request, $email)
    {
        try {
            /* @var $user EntityDir\User */
            $user = $this->getRestClient()->userRecreateToken($email, 'pass-reset');
            $resetPasswordEmail = $this->getMailFactory()->createActivationEmail($user);

            $this->getMailSender()->send($resetPasswordEmail, ['text', 'html']);
        } catch (\Exception $e) {
            $this->get('logger')->debug($e->getMessage());
        }

        return new Response('[Link sent]');
    }
}
