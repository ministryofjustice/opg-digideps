<?php

namespace AppBundle\Controller;

use AppBundle\Entity as EntityDir;
use AppBundle\Form as FormDir;
use AppBundle\Service\StringUtils;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;

class IndexController extends AbstractController
{
    /**
     * @Route("/", name="homepage")
     */
    public function indexAction()
    {
        if ($url = $this->get('redirector_service')->getHomepageRedirect()) {
            return $this->redirect($url);
        }

        // deputy homepage with links to register and login
        return $this->render('AppBundle:Index:index.html.twig');
    }

    /**
     * @Route("login", name="login")
     * @Template()
     */
    public function loginAction(Request $request)
    {
        $form = $this->createForm(FormDir\LoginType::class, null, [
            'action' => $this->generateUrl('login'),
        ]);
        $form->handleRequest($request);
        $vars = [
            'isAdmin' => $this->container->getParameter('env') === 'admin',
        ];

        if ($form->isValid()) {
            try {
                $this->logUserIn($form->getData(), $request, [
                    '_adId' => null,
                    '_adFirstname' =>  null,
                    '_adLastname' => null,
                    'loggedOutFrom' => null,
                ]);
            } catch (\Exception $e) {
                $error = $e->getMessage();

                if ($e->getCode() == 423) {
                    $lockedFor = ceil(($e->getData()['data'] - time()) / 60);
                    $error = $this->get('translator')->trans('bruteForceLocked', ['%minutes%' => $lockedFor], 'signin');
                }

                if ($e->getCode() == 499) {
                    // too-many-attempts warning. captcha ?
                }

                $form->addError(new FormError($error));

                return $this->render('AppBundle:Index:login.html.twig', [
                        'form' => $form->createView(),
                    ] + $vars);
            }

        }

        // different page version for timeout and manual logout
        $session = $request->getSession();

        if ($session->get('loggedOutFrom') === 'logoutPage') {
            $session->set('loggedOutFrom', null); //avoid display the message at next page reload
            return $this->render('AppBundle:Index:login-from-logout.html.twig', [
                    'form' => $form->createView()
                ] + $vars);
        } elseif ($session->get('loggedOutFrom') === 'timeout' || $request->query->get('from') === 'api') {
            $session->set('loggedOutFrom', null); //avoid display the message at next page reload
            $vars['error'] = $this->get('translator')->trans('sessionTimeoutOutWarning', [
                '%time%' => StringUtils::secondsToHoursMinutes($this->container->getParameter('session_expire_seconds')),
            ], 'signin');
        }

        $snSetting = $this->getRestClient()->get('setting/service-notification', 'Setting', [], ['addAuthToken'=>false]);

        return $this->render('AppBundle:Index:login.html.twig', [
                'form' => $form->createView(),
                'serviceNotificationContent' => $snSetting->isEnabled() ? $snSetting->getContent() : null

        ] + $vars);
    }

    /**
     * @Route("login-ad/{userToken}/{adId}/{adFirstname}/{adLastname}", name="ad_login")
     */
    public function adLoginAction(Request $request, $userToken, $adId, $adFirstname, $adLastname)
    {
        // logout first
//        $this->get('security.token_storage')->setToken(null);
//        $request->getSession()->invalidate();

        $this->logUserIn(['token' => $userToken], $request, [
            '_adId' => $adId,
            '_adFirstname' =>  $adFirstname,
            '_adLastname' => $adLastname,
            'loggedOutFrom' => null,
        ]);

//        if failing on feature branch, just render a page that does a JS redirect.
//        behat should open the page later and test you don't get redirected

        return $this->redirectToRoute('user_details');
    }

    /**
     * @param array $data
     * @param Request $request
     * @param $sessionVars
     */
    private function logUserIn($data, Request $request, $sessionVars)
    {
        $user = $this->get('deputy_provider')->login($data);
        // manually set session token into security context (manual login)
        $token = new UsernamePasswordToken($user, null, 'secured_area', $user->getRoles());
        $this->get('security.token_storage')->setToken($token);

        $session = $request->getSession();
        $session->set('_security_secured_area', serialize($token));
        foreach($sessionVars as $k=>$v) {
            $session->set($k, $v);
        }

        // regenerate cookie, otherwise gc_* timeouts might logout out after successful login
        $session->migrate();

        $event = new InteractiveLoginEvent($request, $token);
        $this->get('event_dispatcher')->dispatch('security.interactive_login', $event);

        $session->set('lastLoggedIn', $user->getLastLoggedIn());
    }

    /**
     * @Route("login_check", name="login_check")
     */
    public function loginCheckAction()
    {
    }

    /**
     * @Route("error-503", name="error-503")
     */
    public function error503(Request $request)
    {
        $vars = [];
        $vars['request'] = $request;

        return $this->render('AppBundle:Index:error-503.html.twig', $vars);
    }

    /**
     * keep session alive. Called from session timeout dialog.
     *
     * @Route("session-keep-alive", name="session-keep-alive")
     * @Method({"GET"})
     */
    public function sessionKeepAliveAction(Request $request)
    {
        $request->getSession()->set('refreshedAt', time());

        return new Response('session refreshed successfully');
    }

    /**
     * @Route("/access-denied", name="access_denied")
     */
    public function accessDeniedAction()
    {
        throw new AccessDeniedException();
    }

    private function initProgressIndicator($array, $currentStep)
    {
        $currentStep = $currentStep - 1;
        $progressSteps_arr = [];
        if (is_array($array)) {
            $soa = count($array);

            for ($i = 0; $i < $soa; ++$i) {
                $item = $array[$i];
                $classes = [];
                if ($i == $currentStep) {
                    $classes[] = 'progress--active';
                }
                if ($i < $currentStep) {
                    $classes[] = 'progress--completed';
                }
                if ($i == ($currentStep - 1)) {
                    $classes[] = 'progress--previous';
                }
                $item['class'] = implode(' ', $classes);

                $progressSteps_arr[] = $item;
            }
        }

        return $progressSteps_arr;
    }

    /**
     * @Route("/terms", name="terms")
     */
    public function termsAction(Request $request)
    {
        return $this->render('AppBundle:Index:terms.html.twig', [
            'backlink' => $this->getRefererUrlSafe($request, ['terms'])
        ]);
    }

    /**
     * @Route("/privacy", name="privacy")
     */
    public function privacyAction(Request $request)
    {
        return $this->render('AppBundle:Index:privacy.html.twig', [
            'backlink' => $this->getRefererUrlSafe($request, ['privacy'])
        ]);
    }

    /**
     * @Route("/logout", name="logout")
     * @Template
     */
    public function logoutAction(Request $request)
    {
        $this->get('security.token_storage')->setToken(null);
        $request->getSession()->invalidate();
        return $this->redirect(
            $this->generateUrl('homepage')
        );
    }
}
