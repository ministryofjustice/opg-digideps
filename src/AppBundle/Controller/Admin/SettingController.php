<?php

namespace AppBundle\Controller\Admin;

use AppBundle\Controller\AbstractController;
use AppBundle\Entity as EntityDir;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use AppBundle\Form as FormDir;

/**
 * @Route("/admin/settings")
 */
class SettingController extends AbstractController
{
    /**
     * @Route("/service-notification", name="admin_setting_service_notifications")
     * @Template
     */
    public function serviceNotificationAction(Request $request)
    {
        $endpoint = 'setting/service-notification';
        $setting = $this->getRestClient()->get($endpoint, 'Setting');
        $form = $this->createForm(
            new FormDir\Admin\SettingType(),
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
