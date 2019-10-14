<?php

namespace AppBundle\Controller\Admin;

use AppBundle\Controller\AbstractController;
use AppBundle\Form as FormDir;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;

/**
 * @Route("/admin/settings")
 */
class SettingController extends AbstractController
{
    /**
     * @Route("/service-notification", name="admin_setting_service_notifications")
     * @Security("has_role('ROLE_ADMIN')")
     * @Template("AppBundle:Admin/Setting:serviceNotification.html.twig")
     */
    public function serviceNotificationAction(Request $request)
    {
        $endpoint = 'setting/service-notification';
        $setting = $this->getRestClient()->get($endpoint, 'Setting');
        $form = $this->createForm(
            FormDir\Admin\SettingType::class,
            $setting
        );

        $form->handleRequest($request);

        if ($form->isValid()) {
            $setting = $form->getData();

            $this->getRestClient()->put($endpoint, $setting, ['setting']);
            $request->getSession()->getFlashBag()->add(
                'notice',
                'The setting has been saved'
            );

            return $this->redirectToRoute('admin_setting_service_notifications');
        }

        return [
            'form' => $form->createView(),
        ];
    }
}
