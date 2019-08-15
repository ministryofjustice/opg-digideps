<?php

namespace AppBundle\Controller\Org;

use AppBundle\Controller\AbstractController;
use AppBundle\Entity as EntityDir;
use AppBundle\Exception\RestClientException;
use AppBundle\Form as FormDir;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @Route("/org/settings/organisation")
 */
class OrganisationController extends AbstractController
{
    /**
     * @Route("", name="org_organisation_list")
     * @Template("AppBundle:Org/Organisation:list.html.twig")
     */
    public function listAction(Request $request)
    {
        $user = $this->getUserWithData(['user-organisations', 'organisation']);

        if (count($user->getOrganisations()) === 1) {
            $organisationId = $user->getOrganisations()[0]->getId();
            return $this->redirectToRoute('org_organisation_view', ['id' => $organisationId]);
        }

        return [
            'organisations' => $user->getOrganisations(),
        ];
    }

    /**
     * @Route("/{id}", name="org_organisation_view")
     * @Template("AppBundle:Org/Organisation:view.html.twig")
     */
    public function viewAction(Request $request, $id)
    {
        try {
            $organisation = $this->getRestClient()->get('v2/organisation/' . $id, 'Organisation');
        } catch (RestClientException $e) {
            throw $this->createNotFoundException('Organisation not found');
        }

        return [
            'organisation' => $organisation
        ];
    }

    /**
     * @Route("/{id}/add", name="org_organisation_add_member")
     * @Template("AppBundle:Org/Organisation:add.html.twig")
     */
    public function addAction(Request $request, $id)
    {
        try {
            $organisation = $this->getRestClient()->get('v2/organisation/' . $id, 'Organisation');
        } catch (RestClientException $e) {
            throw $this->createNotFoundException('Organisation not found');
        }

        $form = $this->createForm(FormDir\Org\OrganisationMemberType::class);

        $form->handleRequest($request);

        // If the email belong to a prof user, just add the user to the team
        if ($form->isSubmitted()) {
            $email = $form->getData()->getEmail();
            try {
                $userInfo = $this->getRestClient()->get('user/get-one-by/email/' . $email, 'User');

                if ($userInfo->isDeputyProf()) {
                    $this->getRestClient()->put('team/add-to-team/' . $userInfo->getId(), []);
                    return $this->redirectToRoute('org_organisation_view', ['id' => $organisation->getId()]);
                } else {
                    $error = new FormError($this->get('translator')->trans('form.email.existingError', [], 'org-organisation'));
                    $form->get('email')->addError($error);
                }
            } catch (\Throwable $e) {

            }
        }

        if ($form->isValid()) {
            /** @var $user EntityDir\User */
            $user = $form->getData();

            if ($this->isGranted(EntityDir\User::ROLE_PA)) {
                $user->setRoleName(EntityDir\User::ROLE_PA_ADMIN);
            }

            if ($this->isGranted(EntityDir\User::ROLE_PROF)) {
                $user->setRoleName(EntityDir\User::ROLE_PROF_ADMIN);
            }

            try {
                $user = $this->getRestClient()->post('user', $user, ['org_team_add'], 'User');
                $this->getRestClient()->put('v2/organisation/' . $organisation->getId() . '/user/' . $user->getId(), '');

                $activationEmail = $this->getMailFactory()->createActivationEmail($user);
                $this->getMailSender()->send($activationEmail, ['text', 'html']);

                return $this->redirectToRoute('org_organisation_view', ['id' => $organisation->getId()]);
            } catch (\Throwable $e) {
                switch ((int) $e->getCode()) {
                    case 422:
                        $form->get('email')->addError(new FormError($this->get('translator')->trans('form.email.existingError', [], 'org-team')));
                        break;

                    default:
                        throw $e;
                }
            }
        }

        return [
            'organisation' => $organisation,
            'form' => $form->createView()
        ];
    }
}
