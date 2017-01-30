<?php

namespace AppBundle\Controller;

use AppBundle\Entity as EntityDir;
use AppBundle\Exception\DisplayableException;
use AppBundle\Exception\RestClientException;
use AppBundle\Form as FormDir;
use AppBundle\Model\Email;
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
        $orderBy = $request->query->has('order_by') ? $request->query->get('order_by') : 'firstname';
        $sortOrder = $request->query->has('sort_order') ? $request->query->get('sort_order') : 'ASC';
        $limit = $request->query->get('limit') ?: 50;
        $offset = $request->query->get('offset') ?: 0;
        $userCount = $this->getRestClient()->get('user/count/0', 'array');
        $users = $this->getRestClient()->get("user/get-all/{$orderBy}/{$sortOrder}/$limit/$offset/0", 'User[]');
        $newSortOrder = $sortOrder == 'ASC' ? 'DESC' : 'ASC';

        return [
            'users' => $users,
            'userCount' => $userCount,
            'limit' => $limit,
            'offset' => $offset,
            'newSortOrder' => $newSortOrder,
        ];
    }

    /**
     * @Route("/user-add", name="admin_add_user")
     * @Template
     */
    public function addUserAction(Request $request)
    {
        $availableRoles = EntityDir\Role::$availableRoles;
        // non-admin cannot add admin users
        if (!$this->isGranted(EntityDir\Role::ADMIN)) {
            unset($availableRoles[1]);
        }
        $form = $this->createForm(new FormDir\Admin\AddUserType([
            'roleChoices' => $availableRoles,
            'roleIdEmptyValue' => $this->get('translator')->trans('addUserForm.roleId.defaultOption', [], 'admin'),
        ]), new EntityDir\User());

        if ($request->isMethod('POST')) {
            $form->handleRequest($request);
            if ($form->isValid()) {
                // add user
                try {
                    if (!$this->isGranted(EntityDir\Role::ADMIN) && $form->getData()->getRoleId() == 1) {
                        throw new \RuntimeException('Cannot add admin from non-admin user');
                    }
                    $response = $this->getRestClient()->post('user', $form->getData(), ['admin_add_user']);
                    $user = $this->getRestClient()->get('user/'.$response['id'], 'User');

                    $activationEmail = $this->getMailFactory()->createActivationEmail($user);
                    $this->getMailSender()->send($activationEmail, ['text', 'html']);

                    $request->getSession()->getFlashBag()->add(
                        'notice',
                        'An activation email has been sent to the user.'
                    );

                    $this->get('audit_logger')->log(EntityDir\AuditLogEntry::ACTION_USER_ADD, $user);

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
            $user = $this->getRestClient()->get("user/get-one-by/{$what}/{$filter}", 'User', ['user', 'role', 'client', 'report', 'odr']);
        } catch (\Exception $e) {
            return $this->render('AppBundle:Admin:error.html.twig', [
                'error' => 'User not found',
            ]);
        }

        if ($user->getRole()['role'] == EntityDir\Role::ADMIN && !$this->isGranted(EntityDir\Role::ADMIN)) {
            return $this->render('AppBundle:Admin:error.html.twig', [
                'error' => 'Non-admin cannot edit admin users',
            ]);
        }

        $form = $this->createForm(new FormDir\Admin\AddUserType([
            'roleChoices' => EntityDir\Role::$availableRoles,
            'roleIdEmptyValue' => $this->get('translator')->trans('addUserForm.roleId.defaultOption', [], 'admin'),
            'roleIdDisabled' => $user->getId() == $this->getUser()->getId(),
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
                $this->getRestClient()->put('user/'.$user->getId(), $updateUser, ['admin_add_user']);

                $request->getSession()->getFlashBag()->add('notice', 'Your changes were saved');

                $this->redirect($this->generateUrl('admin_editUser', ['what' => 'user_id', 'filter' => $user->getId()]));
            }
        }
        $view = [
            'form' => $form->createView(),
            'action' => 'edit',
            'id' => $user->getId(),
            'user' => $user,
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
        $odr = $this->getRestClient()->get('odr/'.$id, 'Odr\Odr', ['odr', 'client', 'user']);
        $odrForm = $this->createForm(new FormDir\OdrType(), $odr);
        if ($request->getMethod() == 'POST') {
            $odrForm->handleRequest($request);

            if ($odrForm->isValid()) {
                $updateOdr = $odrForm->getData();
                $this->getRestClient()->put('odr/'.$id, $updateOdr, ['start_date']);
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
        $user = $this->getRestClient()->get("user/{$id}", 'User', ['user', 'role', 'client', 'report']);

        $this->get('audit_logger')->log(EntityDir\AuditLogEntry::ACTION_USER_DELETE, $user);

        $this->getRestClient()->delete('user/'.$id);

        return $this->redirect($this->generateUrl('admin_homepage'));
    }

    /**
     * @Route("/upload", name="admin_upload")
     * @Template
     */
    public function uploadUsersAction(Request $request)
    {
        $form = $this->createForm(new FormDir\UploadCsvType(), null, [
            'action' => $this->generateUrl('admin_upload'),
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
                        'Typeofrep'
                    ])
                    ->getData();

                $count = count($data);
                if ($count > 30000) {
                    throw new \RuntimeException("$count records found in the file, only 30000 allowed for each upload.");
                }

                $compressedData = base64_encode(gzcompress(json_encode($data), 9));

                $ret = $this->getRestClient()->setTimeout(600)->post('casrec/bulk-add', $compressedData);
                $request->getSession()->getFlashBag()->add(
                    'notice',
                    sprintf('%d record uploaded, %d failed', $ret['added'], count($ret['errors']))
                );
                foreach ($ret['errors'] as $error) {
                    $request->getSession()->getFlashBag()->add('notice', $error);
                }

                return $this->redirect($this->generateUrl('admin_upload'));
            } catch (\Exception $e) {
                $message = $e->getMessage();
                if ($e instanceof RestClientException && isset($e->getData()['message'])) {
                    $message = $e->getData()['message'];
                }
                $form->get('file')->addError(new FormError($message));
            }
        }

        return [
            'currentRecords' => $this->getRestClient()->get('casrec/count', 'array'),
            'form' => $form->createView(),
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
                'timestamp' => $timestamp
            ]);
        }

        $response = new Response();
        $response->headers->set('Cache-Control', 'private');
        $response->headers->set('Content-type', 'plain/text');
        $response->headers->set('Content-type', 'application/octet-stream');
        $response->headers->set('Content-Disposition', 'attachment; filename="dd-stats-'.date('Y-m-d').'.csv";');
        $response->sendHeaders();
        $response->setContent($rawCsv);

        return $response;
    }
}
