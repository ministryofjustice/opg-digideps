<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Client;
use AppBundle\Entity\Report\Report;
use AppBundle\Entity\User;
use AppBundle\Service\Client\Internal\ClientApi;
use AppBundle\Service\Client\Internal\UserApi;
use AppBundle\Form\ClientType;
use AppBundle\Service\Client\RestClient;
use AppBundle\Service\Mailer\MailFactory;
use AppBundle\Service\Mailer\MailSender;
use AppBundle\Service\Redirector;
use Psr\Log\LoggerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Translation\TranslatorInterface;

class ClientController extends AbstractController
{
    /** @var UserApi */
    private $userApi;

    /** @var ClientApi */
    private $clientApi;

    /** @var RestClient */
    private $restClient;

    /** @var MailSender */
    private $mailSender;

    /** @var MailFactory */
    private $mailFactory;

    /** @var RouterInterface */
    private $router;

    public function __construct(
        UserApi $userApi,
        ClientApi $clientApi,
        RestClient $restClient,
        MailSender $mailSender,
        MailFactory $mailFactory,
        RouterInterface $router
    ) {
        $this->userApi = $userApi;
        $this->clientApi = $clientApi;
        $this->restClient = $restClient;
        $this->mailSender = $mailSender;
        $this->mailFactory = $mailFactory;
        $this->router = $router;
    }

    /**
     * @Route("/deputyship-details/your-client", name="client_show")
     * @Template("AppBundle:Client:show.html.twig")
     */
    public function showAction(Redirector $redirector)
    {
        // redirect if user has missing details or is on wrong page
        $user = $this->userApi->getUserWithData();

        $route = $redirector->getCorrectRouteIfDifferent($user, 'client_show');

        if (is_string($route)) {
            return $this->redirectToRoute($route);
        }

        $client = $this->clientApi->getFirstClient();

        return [
            'client' => $client,
        ];
    }

    /**
     * @Route("/deputyship-details/your-client/edit", name="client_edit")
     * @Template("AppBundle:Client:edit.html.twig")
     *
     * @param Request $request
     *
     * @return array|RedirectResponse
     */
    public function editAction(Request $request)
    {
        $from = $request->get('from');
        $client = $this->clientApi->getFirstClient();

        if (is_null($client)) {
            /** @var User $user */
            $user = $this->getUser();
            $userId = $user->getId();
            throw new \RuntimeException("User $userId does not have a client");
        }

        $form = $this->createForm(ClientType::class, $client, [
            'action' => $this->generateUrl('client_edit', ['action' => 'edit', 'from' => $from]),
            'validation_groups' => ['lay-deputy-client-edit']
        ]);

        $form->handleRequest($request);

        // edit client form
        if ($form->isSubmitted() && $form->isValid()) {
            $clientUpdated = $form->getData();
            $clientUpdated->setId($client->getId());
            $this->restClient->put('client/upsert', $clientUpdated, ['edit']);
            $this->addFlash('notice', htmlentities($client->getFirstname()) . "'s data edited");

            $user = $this->userApi->getUserWithData(['user-clients', 'client']);

            if ($user->isLayDeputy()) {
                $updateClientDetailsEmail = $this->mailFactory->createUpdateClientDetailsEmail($clientUpdated);
                $this->mailSender->send($updateClientDetailsEmail);
            }

            $activeReport = $client->getActiveReport();

            if ($from === 'declaration' && $activeReport instanceof Report) {
                return $this->redirect($this->generateUrl('report_declaration', ['reportId' => $activeReport->getId()]));
            }

            return $this->redirect($this->generateUrl('client_show'));
        }

        return [
            'client' => $client,
            'form' => $form->createView(),
        ];
    }

    /**
     * @Route("/client/add", name="client_add")
     * @Template("AppBundle:Client:add.html.twig")
     */
    public function addAction(Request $request, Redirector $redirector)
    {
        // redirect if user has missing details or is on wrong page
        $user = $this->userApi->getUserWithData();

        $route = $redirector->getCorrectRouteIfDifferent($user, 'client_add');

        if (is_string($route)) {
            return $this->redirectToRoute($route);
        }

        $client = $this->clientApi->getFirstClient();
        if (!empty($client)) {
            // update existing client
            $client = $this->restClient->get('client/' . $client->getId(), 'Client', ['client', 'report-id', 'current-report']);
            $method = 'put';
            $client_validated = true;
        } else {
            // new client
            $client = new Client();
            $method = 'post';
            $client_validated = false;
        }

        $form = $this->createForm(ClientType::class, $client);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            try {
                // validate against casRec
                $this->restClient->apiCall('post', 'casrec/verify', $client, 'array', []);

                // $method is set above to either post or put
                $response =  $this->restClient->$method('client/upsert', $form->getData());

                /** @var User $currentUser */
                $currentUser = $this->getUser();

                $url = $currentUser->isNdrEnabled()
                    ? $this->generateUrl('ndr_index')
                    : $this->generateUrl('report_create', ['clientId' => $response['id']]);
                return $this->redirect($url);
            } catch (\Throwable $e) {
                /** @var TranslatorInterface $translator */
                $translator = $this->get('translator');

                /** @var LoggerInterface $logger */
                $logger = $this->get('logger');

                switch ((int) $e->getCode()) {
                    case 400:
                        $form->addError(new FormError($translator->trans('formErrors.matching', [], 'register')));
                        break;

                    default:
                        $form->addError(new FormError($translator->trans('formErrors.generic', [], 'register')));
                }

                $logger->error(__METHOD__ . ': ' . $e->getMessage() . ', code: ' . $e->getCode());
            }
        }

        return [
            'form' => $form->createView(),
            'client_validated' => $client_validated,
            'client' => $client
        ];
    }
}
