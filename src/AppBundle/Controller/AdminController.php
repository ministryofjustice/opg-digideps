<?php

namespace AppBundle\Controller;

use AppBundle\Entity as EntityDir;
use AppBundle\Exception\DisplayableException;
use AppBundle\Form as FormDir;
use AppBundle\Model\Email;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use AppBundle\Service\DataImporter\CsvToArray;
use Symfony\Component\Form\FormError;
use AppBundle\Exception\RestClientException;

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

        $form = $this->createForm(new FormDir\AddUserType([
            'roleChoices' => EntityDir\Role::$availableRoles,
            'roleIdEmptyValue' => $this->get('translator')->trans('roleId.defaultOption', [], 'admin'),
        ]), new EntityDir\User());

        if ($request->isMethod('POST')) {
            $form->handleRequest($request);
            if ($form->isValid()) {
                // add user
                try {
                    $response = $this->getRestClient()->post('user', $form->getData(), ['admin_add_user']);
                    $user = $this->getRestClient()->get('user/'.$response['id'], 'User');

                    $activationEmail = $this->getMailFactory()->createActivationEmail($user);
                    $this->getMailSender()->send($activationEmail, ['text', 'html']);

                    $request->getSession()->getFlashBag()->add(
                        'notice',
                        'An activation email has been sent to the user.'
                    );

                    $this->get('auditLogger')->log(EntityDir\AuditLogEntry::ACTION_USER_ADD, $user);

                    return $this->redirect($this->generateUrl('admin_homepage'));
                } catch (RestClientException $e) {
                    $form->get('email')->addError(new FormError($e->getData()['message']));
                }
            }
        }

        $limit = $request->query->get('limit') ?: 50;
        $offset = $request->query->get('offset') ?: 0;
        $userCount = $this->getRestClient()->get('user/count', 'array');
        $users = $this->getRestClient()->get("user/get-all/{$orderBy}/{$sortOrder}/$limit/$offset", 'User[]');
        $newSortOrder = $sortOrder == 'ASC' ? 'DESC' : 'ASC';

        return [
            'users' => $users,
            'userCount' => $userCount,
            'limit' => $limit,
            'offset' => $offset,
            'form' => $form->createView(),
            'newSortOrder' => $newSortOrder,
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
            $user = $this->getRestClient()->get("user/get-one-by/{$what}/{$filter}", 'User', ['user', 'role', 'client', 'report']);
        } catch (\Exception $e) {
            return $this->render('AppBundle:Admin:error.html.twig', [
                'error' => 'User not found',
            ]);
        }

        $form = $this->createForm(new FormDir\AddUserType([
            'roleChoices' => EntityDir\Role::$availableRoles,
            'roleIdEmptyValue' => $this->get('translator')->trans('roleId.defaultOption', [], 'admin'),
            'roleIdDisabled' => $user->getId() == $this->getUser()->getId(),
        ]), $user);

        if ($request->getMethod() == 'POST') {
            $form->handleRequest($request);

            if ($form->isValid()) {
                $updateUser = $form->getData();
                $this->getRestClient()->put('user/'.$user->getId(), $updateUser, ['admin_add_user']);

                $request->getSession()->getFlashBag()->add('action', 'action.message');

                $this->redirect($this->generateUrl('admin_editUser', ['what' => 'user_id', 'filter' => $user->getId()]));
            }
        }

        return ['form' => $form->createView(), 'action' => 'edit', 'id' => $user->getId(), 'user' => $user];
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

        $this->get('auditLogger')->log(EntityDir\AuditLogEntry::ACTION_USER_DELETE, $user);

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
                    ->setExpectedColumns(['Case', 'Surname', 'Deputy No', 'Dep Surname', 'Dep Postcode'])
                    ->getData();

                $count = count($data);
                if ($count > 30000) {
                    throw new \RuntimeException("$count records found in the file, only 30000 allowed for each upload.");
                }

                $compressedData = base64_encode(gzcompress(json_encode($data), 9));

                $ret = $this->getRestClient()->setTimeout(600)->post('casrec/bulk-add/1', $compressedData);
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
        $data = $this->getRestClient()->get('stats/users', 'array');

        // if format is specified, create a CSV with the data instead of returning it to the view
        if ($request->query->get('format') == 'csv') {
            $response = new Response();
            $response->headers->set('Cache-Control', 'private');
            $response->headers->set('Content-type', 'plain/text');
            $response->headers->set('Content-type', 'application/octet-stream');
            $response->headers->set('Content-Disposition', 'attachment; filename="dd-stats-'.date('Y-m-d').'.csv";');
            $response->sendHeaders();

            // array to CSV
            $out = fopen('php://memory', 'w');
            fputcsv($out, array_keys($data[0]));
            foreach ($data as $row) {
                fputcsv($out, $row);
            }
            rewind($out);
            $response->setContent(stream_get_contents($out));

            return $response;
        }

        return [
            'data' => $data,
        ];
    }
}
