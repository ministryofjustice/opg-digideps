<?php

namespace AppBundle\Controller;

use AppBundle\Entity as EntityDir;
use AppBundle\Form as FormDir;
use AppBundle\Model as ModelDir;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;
use AppBundle\Service\StringUtils;

class IndexController extends AbstractController
{
    /**
     * @Route("/", name="homepage")
     */
    public function indexAction()
    {
        if ($url = $this->get('redirectorService')->getHomepageRedirect()) {
            return $this->redirect($url);
        }

        // deputy homepage with links to register and login
        return $this->render('AppBundle:Index:index.html.twig');
    }

    /**
     * @Route("login", name="login")
     * @Template()
     */
    public function loginAction()
    {
        $request = $this->getRequest();

        $form = $this->createForm(new FormDir\LoginType(), null, [
            'action' => $this->generateUrl('login'),
        ]);
        $form->handleRequest($request);
        $vars = [
            'form' => $form->createView(),
            'isAdmin' => $this->container->getParameter('env') === 'admin',
        ];

        if ($form->isValid()) {
            $data = $form->getData();

            try {
                $user = $this->get('deputyprovider')->login($data);
            } catch (\Exception $e) {
                $error = $e->getMessage();

                if ($e->getCode() == 423) {
                    $lockedFor = ceil(($e->getData()['data'] - time()) / 60);
                    $error = $this->get('translator')->trans('bruteForceLocked', ['%minutes%' => $lockedFor], 'login');
                }

                if ($e->getCode() == 499) {
                    // too-many-attempts warning. captcha ?
                }

                return $this->render('AppBundle:Index:login.html.twig', $vars + ['error' => $error]);
            }
            // manually set session token into security context (manual login)
            $token = new UsernamePasswordToken($user, null, 'secured_area', $user->getRoles());
            $this->get('security.context')->setToken($token);

            $session = $request->getSession();
            $session->set('_security_secured_area', serialize($token));
            $session->set('loggedOutFrom', null);

            // regenerate cookie, otherwise gc_* timeouts might logout out after successful login
            $session->migrate();

            $request = $this->get('request');
            $event = new InteractiveLoginEvent($request, $token);
            $this->get('event_dispatcher')->dispatch('security.interactive_login', $event);

            $session->set('lastLoggedIn', $user->getLastLoggedIn());

            $this->get('auditLogger')->log(EntityDir\AuditLogEntry::ACTION_LOGIN);
        }

        // different page version for timeout and manual logout
        $session = $this->getRequest()->getSession();

        if ($session->get('loggedOutFrom') === 'logoutPage') {
            $session->set('loggedOutFrom', null); //avoid display the message at next page reload
            return $this->render('AppBundle:Index:login-from-logout.html.twig', $vars);
        } elseif ($session->get('loggedOutFrom') === 'timeout' || $request->query->get('from') === 'api') {
            $session->set('loggedOutFrom', null); //avoid display the message at next page reload
            $vars['error'] = $this->get('translator')->trans('sessionTimeoutOutWarning', [
                '%time%' => StringUtils::secondsToHoursMinutes($this->container->getParameter('session_expire_seconds')),
            ], 'login');
        }

        return $this->render('AppBundle:Index:login.html.twig', $vars);
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
    public function error503()
    {
        $request = $this->getRequest();
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
        $progressSteps_arr = array();
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
    public function termsAction()
    {
        return $this->render('AppBundle:Index:terms.html.twig');
    }

    /**
     * @Route("/logout", name="logout")
     * @Template
     */
    public function logoutAction(Request $request)
    {
        f();
        $this->get('security.context')->setToken(null);
        $request->getSession()->invalidate();

        return $this->redirect(
            $this->generateUrl('homepage')
        );
    }
}
