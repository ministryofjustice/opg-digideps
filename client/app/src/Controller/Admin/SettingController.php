<?php

namespace App\Controller\Admin;

use App\Controller\AbstractController;
use App\Form\Admin\SettingType;
use App\Service\Client\RestClient;
use Symfony\Bridge\Twig\Attribute\Template;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route(path: '/admin/settings')]
class SettingController extends AbstractController
{
    public function __construct(private readonly RestClient $restClient)
    {
    }

    #[Route(path: '/service-notification', name: 'admin_setting_service_notifications')]
    #[IsGranted(attribute: 'ROLE_SUPER_ADMIN')]
    #[Template('@App/Admin/Setting/serviceNotification.html.twig')]
    public function serviceNotificationAction(Request $request): RedirectResponse|array
    {
        $endpoint = 'setting/service-notification';
        $setting = $this->restClient->get($endpoint, 'Setting');
        $form = $this->createForm(
            SettingType::class,
            $setting
        );

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $setting = $form->getData();

            $this->restClient->put($endpoint, $setting, ['setting']);
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
